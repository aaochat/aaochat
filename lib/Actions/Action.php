<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2016 Lukas Reschke <lukas@statuscode.ch>
 *
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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
namespace OCA\AaoChat\Actions;
use OCP\IUser;
use OCP\IUserManager;
use OCA\AaoChat\Service\AaochatService;
use OCA\AaoChat\Service\ApiauthService;
use OCP\Authentication\LoginCredentials\IStore;


class Action {

	private $aaochatService;
	private $apiauthService;
	/** @var IStore */
    private $credentialStore;

	public function __construct(AaochatService $aaochatService, ApiauthService $apiauthService) {
		$this->aaochatService = $aaochatService;
		$this->apiauthService = $apiauthService;
	}

	public function userChanged($params) {
		if(!empty($params['value']) && $params['value'] != 'default') {
			$user = $params['user'];

			$userId = $user->getUID();
	        $userEmail = $user->getEMailAddress();
			$userDisplayName = $user->getDisplayName();
	        $avatarImage = $user->getAvatarImage(100);

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
				$userJsonData = json_encode($userData);
        		$myfile = file_put_contents($aaochat_log_dir.'user_changed.txt', $userJsonData.PHP_EOL , FILE_APPEND | LOCK_EX);
			}
		}
	}

	public function fileMovePostDetails($params) {
		$oldpath = $params['oldpath'];
		$newpath = $params['newpath'];

		$info = \OC\Files\Filesystem::getFileInfo($newpath);
		$fileData     = $info->getData();
		$fileData['oldpath'] = $oldpath;
		$fileData['newpath'] = $newpath;
		$fileId = $fileData['fileid'];

		$oldBasePath = pathinfo($oldpath,PATHINFO_DIRNAME);
	    $newBasePath = pathinfo($newpath,PATHINFO_DIRNAME);

		if($oldBasePath == $newBasePath) {
			//File rename
			$sharedFilePath = \OC\Files\Filesystem::getPath($fileId);
			$target = $newpath;

			$aaochatGroup = $this->aaochatService->getAaochatGroup($fileId);
			$aaochatGroup = json_decode($aaochatGroup,true);
			if(isset($aaochatGroup['status']) && $aaochatGroup['status']=='success') {
				$response = $this->apiauthService->getObjectShareData($fileId);
			}
		} else {
		//File moved

		}

		if($this->aaochatService->isAaochatApiLogEnable()) {
			$aaochat_log_dir = $this->aaochatService->getAaochatLogPath();
			//$hooksJsonData = json_encode($params);
			//$myfile = file_put_contents($aaochat_log_dir.'rename_file_hook_data.txt', $hooksJsonData.PHP_EOL , FILE_APPEND | LOCK_EX);
		}
	}
}
