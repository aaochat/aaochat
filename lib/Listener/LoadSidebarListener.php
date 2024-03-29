<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2019, Roeland Jago Douma <roeland@famdouma.nl>
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

use OCA\AaoChat\AppInfo\Application;
use OCA\Files\Event\LoadSidebar;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Util;
use OCP\IConfig;

class LoadSidebarListener implements IEventListener {

	private $aaochatService;

    public function __construct() {
        $this->config = \OC::$server[IConfig::class];
    }

	public function handle(Event $event): void {
		if (!($event instanceof LoadSidebar)) {
			return;
		}

		$isLicenseValid = $this->config->getAppValue(Application::APP_ID, 'aaochat_is_license_valid', '');
		if($isLicenseValid === 'yes') {
			// TODO: make sure to only include the sidebar script when
			// we properly split it between files list and sidebar
			Util::addScript(Application::APP_ID, 'aaochat_sidebar','files');
		}
	}
}
