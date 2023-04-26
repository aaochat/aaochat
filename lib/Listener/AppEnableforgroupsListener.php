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
use OCP\App\ManagerEvent;
use OCA\AaoChat\Service\AaochatService;

/**
 * Class UserDeletedListener
 *
 * @package OCA\AaoChat\Listener
 */
class AppEnableforgroupsListener implements IEventListener {

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
		if (!$event instanceof ManagerEvent) {
            return;
        }

		if($this->aaochatService->isAaochatApiLogEnable()) {
			$aaochat_log_dir = $this->aaochatService->getAaochatLogPath();
			$userData = "app enable Listener for groups \n";
            $myfile = file_put_contents($aaochat_log_dir.'aaochat_created.txt', $userData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }
	}
}
