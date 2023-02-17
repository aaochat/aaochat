<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2016 Lukas Reschke <lukas@statuscode.ch>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author Bjoern Schiessle <bjoern@schiessle.org>
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Joas Schilling <coding@schilljs.com>
 * @author John Molakvoæ <skjnldsv@protonmail.com>
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

/**
 * Class UserManagement logs all user management related actions.
 *
 * @package OCA\AaoChat\Actions
 */
class UserManagement extends Action {

	/**
	 * Log creation of users
	 *
	 * @param array $params
	 */
	public function create(array $params): void {
		
	}

	/**
	 * Log assignments of users (typically user backends)
	 *
	 * @param string $uid
	 */
	public function assign(string $uid): void {
		
	}

	/**
	 * Log deletion of users
	 *
	 * @param array $params
	 */
	public function delete(array $params): void {
		
	}

	/**
	 * Log unassignments of users (typically user backends, no data removed)
	 *
	 * @param string $uid
	 */
	public function unassign(string $uid): void {
		
	}

	/**
	 * Log enabling of users
	 *
	 * @param array $params
	 */
	public function change(array $params): void {


		switch ($params['feature']) {
			case 'avatar':
				if(!empty($params['value']) && $params['value'] != 'default') {
					$this->userChanged($params);
				}
				break;
			case 'displayName':
				$user = $params['user'];
				if(isset($params['old_value']) && !empty($params['old_value'])) {
					$userId = $params['old_value'];
					$user_manager   = \OC::$server->getUserManager();
        			$user = $user_manager->get($userId);
				}
				if(empty($user)) {
					if(!empty($params['value']) && $params['value'] != 'default') {
						$this->userChanged($params);
					}
				}
				break;
			case 'eMailAddress':
				if(!empty($params['value']) || !empty($params['old_value'])) {
					$this->userChanged($params);
				}
				break;
		}
	}

	/**
	 * Logs changing of the user scope
	 *
	 * @param IUser $user
	 */
	public function setPassword(IUser $user): void {
		
	}
}
