<?php
/**
 * @copyright Copyright (c) 2017 Robin Appelman <robin@icewind.nl>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\AaoChat\Settings;

use OCA\AaoChat\AppInfo\Application;
use OCA\AaoChat\Service\ConfigProxy;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\ILogger;
use OCP\Settings\ISettings;

class AaochatAdminSetting implements ISettings {

	private IL10N $l;
    private IConfig $config;

	public function __construct(
        IConfig $config, IL10N $l
    ) {
        $this->config = $config;
        $this->l = $l;
    }

	/**
	 * @return TemplateResponse
	 */
	public function getForm() {

		//echo "<pre>".print_r($this->config,true)."</pre>";
		//echo $this->config->getAppValue(Application::APP_ID,'aaochat-api-url','');

        $parameters = [
            'aaochat_client_id' => $this->config->getAppValue(Application::APP_ID, 'aaochat_client_id', ''),
            'aaochat_license_key' => $this->config->getAppValue(Application::APP_ID, 'aaochat_license_key', ''),
            'aaochat_is_license_valid' => $this->config->getAppValue(Application::APP_ID, 'aaochat_is_license_valid', ''),

            'aaochat_ser_instance_type' => $this->config->getAppValue(Application::APP_ID, 'aaochat_ser_instance_type', ''),
            'aaochat_ser_url' => $this->config->getAppValue(Application::APP_ID, 'aaochat_ser_url', ''),
            'aaochat_ser_file_server_url' => $this->config->getAppValue(Application::APP_ID, 'aaochat_ser_file_server_url', ''),
            'aaochat_ser_storage_size' => $this->config->getAppValue(Application::APP_ID, 'aaochat_ser_storage_size', ''),
            'aaochat_ser_id' => $this->config->getAppValue(Application::APP_ID, 'aaochat_ser_id', ''),

            'aaochat_license_status' => $this->config->getAppValue(Application::APP_ID, 'aaochat_license_status', ''),
            'aaochat_license_id' => $this->config->getAppValue(Application::APP_ID, 'aaochat_license_id', ''),
            'aaochat_activation_date' => $this->config->getAppValue(Application::APP_ID, 'aaochat_activation_date', ''),
            'aaochat_ipaddress' => $this->config->getAppValue(Application::APP_ID, 'aaochat_ipaddress', ''),
            
            'aaochat_lead_id' => $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_id', ''),
            'aaochat_lead_name' => $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_name', ''),
            'aaochat_lead_email' => $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_email', ''),
            'aaochat_lead_phone' => $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_phone', ''),
            'aaochat_lead_organization' => $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_organization', ''),
            'aaochat_lead_status' => $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_status', ''),
        ];

        return new TemplateResponse(Application::APP_ID, 'settings/admin-form', $parameters, '');

	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection() {
		return Application::APP_ID;
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 * the admin section. The forms are arranged in ascending order of the
	 * priority values. It is required to return a value between 0 and 100.
	 *
	 * E.g.: 70
	 */
	public function getPriority() {
		return 90;
	}
}
