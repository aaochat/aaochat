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
use OCP\User\Events\UserChangedEvent;
use OCP\IUserManager;
use OCP\IUser;
use OCP\IUserSession;
use OCA\AaoChat\Service\AaochatService;
use OCP\IConfig;
use OCP\Authentication\LoginCredentials\IStore;

/**
 * Class UserChangedListener
 *
 * @package OCA\AaoChat\Listener
 */
class UserChangedListener implements IEventListener {

    /** @var IStore */
    private $credentialStore;

    /**
     * @var OC\AllConfig
     */
    protected $config;

    private $aaochatService;

    public function __construct(AaochatService $aaochatService) {
        $this->aaochatService = $aaochatService;
    }

	/**
	 * @inheritDoc
	 */
	public function handle(Event $event): void {
		if (!($event instanceof UserChangedEvent)) {
			// Unrelated
			return;
		}

        $user = $event->getUser();
        $userId = $user->getUID();
        $userEmail = $user->getEMailAddress();
		$userDisplayName = $user->getDisplayName();
        $avatarImage = $user->getAvatarImage(100);

        $this->credentialStore =  \OC::$server[IStore::class];
        $credentials = $this->credentialStore->getLoginCredentials();
        $userName = $credentials->getLoginName();
        $password = $credentials->getPassword();
        

		$users = array();
        $user = array();
        $user['user_name'] = $userId;
        $user['user_email'] = $userEmail;
        $user['display_name'] = $userDisplayName;
        $user['avatar_image'] = $avatarImage;

        array_push($users,$user);

        $userData = array();
        $userData['users'] = $users;

        //$response = $this->aaochatService->sendWebhookData('userUpdated',$userData);
        $response = $this->aaochatService->syncUserdataToAaochat($userData);

        if($this->aaochatService->isAaochatApiLogEnable()) {
            $aaochat_log_dir = $this->aaochatService->getAaochatLogPath();
		    $userData = json_encode($userData);
            $myfile = file_put_contents($aaochat_log_dir.'userstatus_changed.txt', $userData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }
	}
}
