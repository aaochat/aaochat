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

use OCA\AaoChat\AppInfo\Application;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\App\ManagerEvent;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Util;

/**
 * Class UserDeletedListener
 *
 * @package OCA\AaoChat\Listener
 */
class AppEnableListener implements IEventListener {

	public function __construct() {
		
	}

	/**
	 * @inheritDoc
	 */
    public function handle(Event $event): void {
		if (!$event instanceof ManagerEvent) {
            return;
        }

        $url_generator   = \OC::$server->getURLGenerator();
        $host_url       =  $url_generator->getAbsoluteURL('');
        $site_url =  $host_url . 'index.php';

        $settingPageUrl = $site_url.'/settings/admin/'.Application::APP_ID;

        /*
        try {
            $f = fopen(".htaccess", "a+");
            fwrite($f, "Header unset Content-Security-Policy");
            fclose($f);
        } catch(Exception $e) {
          
        }*/

		//$userData = "app enable Listener \n".$settingPageUrl;
        //$myfile = file_put_contents('/var/www/html/nextcloud_23/data/aaochat_created.txt', $userData.PHP_EOL , FILE_APPEND | LOCK_EX);

	}
}
