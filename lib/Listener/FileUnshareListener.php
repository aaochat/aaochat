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
use OCP\Share\Events\ShareDeletedEvent;
use OCP\IUserManager;
use OCP\IUser;
use OCP\IUserSession;
use OCA\AaoChat\Service\AaochatService;
use OCP\IConfig;
use OCP\Share\IManager;
use OCP\Share\IShare;

/**
 * Class FileUnshareListener
 *
 * @package OCA\AaoChat\Listener
 */
class FileUnshareListener implements IEventListener {


	/**
     * @var OC\AllConfig
     */
    protected $config;

    private $aaochatService;

    /** @var IManager */
    private $shareManager;

    public function __construct(AaochatService $aaochatService, IManager $shareManager) {
        $this->aaochatService = $aaochatService;
        $this->shareManager = $shareManager;
    }

	/**
	 * @inheritDoc
	 */
	public function handle(Event $event): void {
		if (!($event instanceof ShareDeletedEvent)) {
			// Unrelated
			return;
		}
        $shareData = array();

        $share = $event->getShare();
        $sharedNode = $share->getNode();
        $fileInfo = $sharedNode->getFileInfo();
        //$fileData     = $fileInfo->getData();
        $filePath     = $fileInfo->getPath();
        //$nodePath = $sharedNode->getPath();

        $fileId = $share->getNodeId();
        if(empty($fileId)) {
            $fileId = $fileInfo->getId();
        }
        $fileType = $share->getNodeType();
        $shareWith = $share->getSharedWith();
        $shareDisplayName = $share->getSharedWithDisplayName();
        $permissions = $share->getPermissions();
        $shareBy = $share->getSharedBy();
        $shareOwner = $share->getShareOwner();
        $target = $share->getTarget();
        $sharedFilePath = \OC\Files\Filesystem::getPath($fileId);
        $fileOwner = \OC\Files\Filesystem::getOwner($sharedFilePath);

        $shareData = array();
        $shareData['fileId'] = $fileId;
        $shareData['fileType'] = $fileType;
        $shareData['shareWith'] = $shareWith;
        $shareData['shareDisplayName'] = $shareDisplayName;
        $shareData['permissions'] = $permissions;
        $shareData['shareBy'] = $shareBy;
        $shareData['shareOwner'] = $shareOwner;
        $shareData['target'] = $target;


        $aaochatGroup = $this->aaochatService->getAaochatGroup($fileId);
        $aaochatGroup = json_decode($aaochatGroup,true);
        if(isset($aaochatGroup['status']) && $aaochatGroup['status']=='success') {
            $shareInfo = array();
            $shareInfo['objectId'] = $fileId;
            $shareInfo['objectName'] = trim($target,"/");
            $shareInfo['objectType'] = $fileType;
            $shareInfo['filePath'] = $sharedFilePath;
            $shareInfo['creator'] = $fileOwner;            

            $shareUserInfo = array();
            $dir_path = $sharedFilePath;
            $userid = \OC_User::getUser();
            $userhome = \OC_User::getHome($userid);
            //$shareInfo['userid'] = $userid;
            if (\OC\Files\Filesystem::file_exists($dir_path)) {
                //$shareInfo['dir_path'] = $dir_path;

                $sharedData = $this->shareManager->getAccessList($sharedNode,false);
                //$shareInfo['sharedData'] = $sharedData;
                if(isset($sharedData['users']) && !empty($sharedData['users'])) {
                    foreach ($sharedData['users'] as $key => $sharedUserInfo) {
                        $shareUserInfo[$sharedUserInfo]['id'] = $sharedUserInfo;
                        $shareUserInfo[$sharedUserInfo]['permissions'] = 1;
                    }
                }
            }
            $shareInfo['shareInfo'] = $shareUserInfo;

            $response = $this->aaochatService->manageGroupToAaochat($shareInfo);
            //$response = $this->aaochatService->sendWebhookData('removeFromGroup',$shareData);
        }

        if($this->aaochatService->isAaochatApiLogEnable()) {
            $aaochat_log_dir = $this->aaochatService->getAaochatLogPath();
            $shareData = json_encode($shareData);
            $myfile = file_put_contents($aaochat_log_dir.'share_deleted.txt', $shareData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }
	}

}
