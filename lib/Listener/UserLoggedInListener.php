<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2020, Georg Ehrke
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\AaoChat\Listener;

use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\User\Events\UserLoggedInEvent;
use OCP\App\ManagerEvent;
use OCP\IUserManager;
use OCA\AaoChat\Service\ApiauthService;
use OCA\AaoChat\Service\AaochatService;
use OCA\AaoChat\Db\Apiauth;
use OCA\AaoChat\Db\ApiauthMapper;

use OCP\Security\ICredentialsManager;
use OCP\Authentication\LoginCredentials\IStore;
use OCP\IUserSession;
use OCA\AaoChat\Service\ConfigProxy;
use OCA\AaoChat\Exception\ApiSeerverException;

/**
 * Class UserLoggedInListener
 *
 * @package OCA\AaoChat\Listener
 */
class UserLoggedInListener implements IEventListener {


    /** @var IStore */
    private $credentialStore;

    private $apiAuthService;
    private $aaochatService;

    protected $apiAuthMapper;

	public function __construct(ApiauthService $apiAuthService, AaochatService $aaochatService, ApiauthMapper $apiAuthMapper) {
        $this->apiAuthService = $apiAuthService;
        $this->aaochatService = $aaochatService;
        $this->apiAuthMapper = $apiAuthMapper;
	}

	/**
	 * @inheritDoc
	 */
	public function handle(Event $event): void {
		if (!($event instanceof UserLoggedInEvent)) {
            //$myfile = file_put_contents('/var/www/html/nextcloud_23/data/user_logged_in.txt', "testing...".PHP_EOL , FILE_APPEND | LOCK_EX);
			// Unrelated
			return;
		}

        //$this->credentialStore =  \OC::$server[IStore::class];
        //$myfile = file_put_contents('/var/www/html/nextcloud_23/data/user_loggedin_1.txt', 'works'.PHP_EOL , FILE_APPEND | LOCK_EX);

		$user = $event->getUser();
        $userName = $event->getLoginName();
        $userId = $user->getUID();
        $userEmail = $user->getEMailAddress();
        $userDisplayName = $user->getDisplayName();
        $avatarImage = $user->getAvatarImage(100);
        $password = $event->getPassword();

        $userData = array();
        $userData['user_name'] = $userId;
        $userData['user_email'] = $userEmail;
        $userData['display_name'] = $userDisplayName;
        $userData['avatar_image'] = $avatarImage;
        //$userData['password'] = $password;

        try {

            $authBase = base64_encode($userName.':'.$password);
            $userToken = $this->apiAuthService->generateAuthToken($userName, $userEmail);
            $userData['token'] = $userToken;
            if($this->apiAuthMapper->isApiUserExists($userName)) {
                $res= $this->apiAuthMapper->updateApiAuth($userName, $authBase, $userToken);
            } else {
                $apiauth = new Apiauth();
                $apiauth->setUserId($userName);
                $apiauth->setAuthBase($authBase);
                $apiauth->setAuthToken($userToken);
                $apiauth->setTimestamp(time());
                $res = $this->apiAuthMapper->insert($apiauth);
            }

            $aaochatUserToken = '"123"';
            $response = $this->aaochatService->sendUserdataToAaochat($userData);
            if(!empty($response)) {
                $response = json_decode($response);
            }
            if(isset($response->status) && $response->status=='success') {
                $responseData = $response->data;
                $aaochatUserToken = '"'.$responseData->auth_key.'"';

                $this->apiAuthMapper->updateAuthKey($userName, $responseData->auth_key);
            }
            $aaochatServerUrl = $this->aaochatService->getAaochatServerUrl();
            $aaochatFileServerUrl = $this->aaochatService->getAaochatFileServerUrl();

            unset($_COOKIE['ncUserAuthKey']);
            setcookie('ncUserAuthKey', $aaochatUserToken, time() + 3600000*24*7, '/');

            unset($_COOKIE['aaochatServerUrl']);
            setcookie('aaochatServerUrl', $aaochatServerUrl, time() + 3600000*24*7, '/');

            unset($_COOKIE['aaochatFileServerUrl']);
            setcookie('aaochatFileServerUrl', $aaochatFileServerUrl, time() + 3600000*24*7, '/');
                
            if($this->aaochatService->isAaochatApiLogEnable()) {
                $aaochat_log_dir = $this->aaochatService->getAaochatLogPath();
                if(!empty($userData)) {
                    $userData = json_encode($userData);
                }
                $myfile = file_put_contents($aaochat_log_dir.'user_loggedin.txt', $userData.PHP_EOL , FILE_APPEND | LOCK_EX);
            }
        }
        catch(ApiSeerverException $e) {
           
        }
	}
}
