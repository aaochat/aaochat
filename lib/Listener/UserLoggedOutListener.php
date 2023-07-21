<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2020, Georg Ehrke
 *
 * @author Georg Ehrke <oc.list@georgehrke.com>
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
use OCP\User\Events\BeforeUserLoggedOutEvent;
use OCA\AaoChat\Service\AaochatService;

/*
use OCP\IUserManager;
use OCP\App\ManagerEvent;
use OCP\IUserSession;
*/

/**
 * Class UserLoggedOutListener
 *
 * @package OCA\AaoChat\Listener
 */
class UserLoggedOutListener implements IEventListener {

	private $aaochatService;

	public function __construct(AaochatService $aaochatService) {
		$this->aaochatService = $aaochatService;
	}

	/**
	 * @inheritDoc
	 */
	public function handle(Event $event): void {
		if (!($event instanceof BeforeUserLoggedOutEvent)) {
			// Unrelated
			return;
		}

		$user = $event->getUser();
        $userId = $user->getUID();
        $userEmail = $user->getEMailAddress();
        $userDisplayName = $user->getDisplayName();
        $avatarImage = $user->getAvatarImage(100);

		unset($_COOKIE['ncUserAuthKey']);
		setcookie('ncUserAuthKey', '', -1, '/'); 
		unset($_COOKIE['aaochatServerUrl']);
		setcookie('aaochatServerUrl', '', -1, '/');
		unset($_COOKIE['aaochatFileServerUrl']);
		setcookie('aaochatFileServerUrl', '', -1, '/');

        $userData = array();
        $userData['userId'] = $userId;
        $userData['userEmail'] = $userEmail;
        $userData['userDisplayName'] = $userDisplayName;
        $userData['avatarImage'] = $avatarImage;

		if($this->aaochatService->isAaochatApiLogEnable()) {
			$aaochat_log_dir = $this->aaochatService->getAaochatLogPath();
			$userData = json_encode($userData);
        	$myfile = file_put_contents($aaochat_log_dir.'user_loggedout.txt', $userData.PHP_EOL , FILE_APPEND | LOCK_EX);
		}
	}
}
