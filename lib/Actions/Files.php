<?php

declare(strict_types=1);

/**
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\AaoChat\Actions;

/**
 * Class Files logs the actions to files
 *
 * @package OCA\AaoChat\Actions
 */
class Files extends Action {

	/**
	 * Action for creation of files
	 *
	 * @param array $params
	 */
	public function create(array $params) {
		if ($params['path'] === '/' || $params['path'] === '' || $params['path'] === null) {
			return;
		}

	}

	/**
	 * Action for copying of files
	 *
	 * @param array $params
	 */
	public function copy(array $params) {
		//implement later if require
	}

	/**
	 * Action for update of files
	 *
	 * @param array $params
	 */
	public function update(array $params) {
		//implement later if require
	}

	/**
	 * Action for deletions of files
	 *
	 * @param array $params
	 */
	public function delete(array $params) {
		//implement later if require
	}

		/**
	 * Action for fileMovePost
	 *
	 * @param array $params
	 */
	public function fileMovePost(array $params) {
		//implement later if require
		$this->fileMovePostDetails($params);
	}

}
