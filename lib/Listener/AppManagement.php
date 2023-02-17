<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2020, Georg Ehrke
 *
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


/**
 * Class AppManagementListener
 *
 * @package OCA\AaoChat\Listener
 */
class AppManagement {
	

	public function __construct() {
		
	}

	/**
	 * @param string $appName
	 */
	public function enableApp(string $appName): void {
		
		$userData = "app enable $appName \n".$appName;
        $myfile = file_put_contents('/var/www/html/nextcloud_23/data/aaochat_created.txt', $userData.PHP_EOL , FILE_APPEND | LOCK_EX);
	}

	/**
	 * @param string $appName
	 * @param string[] $groups
	 */
	public function enableAppForGroups(string $appName, array $groups): void {
		
	}

	/**
	 * @param string $appName
	 */
	public function disableApp(string $appName): void {
		$userData = "app disable $appName \n";
        $myfile = file_put_contents('/var/www/html/nextcloud_23/data/aaochat_created.txt', $userData.PHP_EOL , FILE_APPEND | LOCK_EX);
	}

	private function mail(string $string, array $params): void {
		/*
		$text =
			vsprintf(
				$string,
				$params
			);

		$mailTemplate = $this->mailer->createEMailTemplate('app_mail_notifications.mail');
		$mailTemplate->setSubject($text);
		$mailTemplate->addBodyText($text);

		$mailer = \OC::$server->getMailer();
		$message = $this->mailer->createMessage();
		$message->setFrom(['wnd@xiller.com' => 'Nextcloud Notifier']);
		$message->setTo(['njiandzebewilfriedjunior.com' => 'Recipient']);
		$message->useTemplate($mailTemplate);

		$this->mailer->send($message);
		*/
	}
}
