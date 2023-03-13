<?php
/**
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
 */

namespace OCA\AaoChat\Controller;

use OCA\AaoChat\AppInfo\Application;
use OCA\AaoChat\Service\ConfigProxy;
use OCA\AaoChat\Service\AaochatService;
use OCA\AaoChat\Service\ApiauthService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Response;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\IURLGenerator;
use OCA\AaoChat\Db\Apiauth;
use OCA\AaoChat\Db\ApiauthMapper;

class AaochatSettingController extends Controller
{
    /**
     * @var IConfig
     */
    protected $config;

    /**
     * @var ConfigProxy
     */
    protected $configProxy;

    /**
     * @var IUserSession
     */
    protected $userSession;

    private $aaochatService;
    protected $apiAuthMapper;

    public function __construct($appName, IRequest $request, IConfig $config, ConfigProxy $configProxy, IUserSession $userSession,
        IURLGenerator $urlGenerator, AaochatService $aaochatService, ApiauthService $apiAuthService, ApiauthMapper $apiAuthMapper)
    {
        parent::__construct($appName, $request);

        $this->config = $config;
        $this->configProxy = $configProxy;
        $this->userSession = $userSession;
        $this->aaochatService = $aaochatService;
        $this->apiAuthService = $apiAuthService;
        $this->apiAuthMapper = $apiAuthMapper;
    }

    
    /**
     * @NoAdminRequired
     *
     * @param mixed $name
     * @param mixed $value
     *
     * @return Response
     */
    public function valueSet($name, $value)
    {
        $doSave = false;

        if ('aaochat_license_key' === $name) {
            $doSave = true;

            if (empty($value)) {
                $value = '';
            }
        }

        $this->config->setAppValue(Application::APP_ID, $name, $value);

        return [
            'name' => $name,
            'value' => $value,
        ];

    }

    /**
     * @NoAdminRequired
     *
     * @param mixed $licenseKey
     *
     * @return Response
     */
    public function createLead($aaochat_lead_name,
    $aaochat_lead_email,
    $aaochat_lead_phone_contry_code,
    $aaochat_lead_phone,
    $aaochat_lead_country,
    $aaochat_lead_organization,
    $aaochat_lead_organization_address,
    $aaochat_lead_organization_siteurl)
    {
        $response = array();
        $response['status'] = 'error';
        $response['data'] = array();
        $response['message'] = '';
        $isJsonRes = false;
        if(!empty($aaochat_lead_name) && 
        !empty($aaochat_lead_email) && 
        !empty($aaochat_lead_phone_contry_code) && 
        !empty($aaochat_lead_phone) && 
        !empty($aaochat_lead_country) && 
        !empty($aaochat_lead_organization) &&
        !empty($aaochat_lead_organization_address) &&
        !empty($aaochat_lead_organization_siteurl)) {
            $leadData = array();
            $leadData['name'] = $aaochat_lead_name;
            $leadData['email'] = $aaochat_lead_email;
            $leadData['countryCode'] = $aaochat_lead_phone_contry_code;
            $leadData['phoneNo'] = $aaochat_lead_phone;
            $leadData['country'] = $aaochat_lead_country;
            $leadData['organization'] = $aaochat_lead_organization;
            $leadData['companyAddress'] = $aaochat_lead_organization_address;
            $leadData['siteUrl'] = $aaochat_lead_organization_siteurl;
            $responseJson = $this->aaochatService->createLead($leadData);
            $response = json_decode($responseJson, true);
            if(isset($response['status']) && $response['status']=='success') {
                //Update setting in Nextcloud DB
                $this->aaochatService->updateAaochatLeadData($response);
            } else {
                $response['message'] = 'Registration failed. Please try after sometime.';
            }
            $isJsonRes = true;
        } else {
            $response['message'] = 'Please provide your all required information.';
        }

        if($isJsonRes == false) {
            $response = json_encode($response);
        }
        return $response;
    }

    /**
     * @NoAdminRequired
     *
     * @param mixed $licenseKey
     *
     * @return Response
     */
    public function leadStatus($aaochat_lead_id)
    {
        $response = array();
        $response['status'] = 'error';
        $response['data'] = array();
        $response['message'] = '';
        $isJsonRes = false;
        if(!empty($aaochat_lead_id)) {
            $responseJson = $this->aaochatService->getLeadStatus($aaochat_lead_id);
            $response = json_decode($responseJson, true);
            if(isset($response['status']) && $response['status']=='success') {
                //Update setting in Nextcloud DB
                $this->aaochatService->updateAaochatLeadStatus($response);
            } else {
                $response['message'] = 'Something went wrong. Please try after sometime.';
            }
            $isJsonRes = true;
        } else {
            $response['message'] = 'Id should not be empty.';
        }

        if($isJsonRes == false) {
            $response = json_encode($response);
        }
        return $response;
    }

    /**
     * @NoAdminRequired
     *
     * @param mixed $licenseKey
     *
     * @return Response
     */
    public function activateLicense($licenseKey)
    {
        $response = array();
        $response['status'] = 'error';
        $response['data'] = array();
        $response['message'] = '';
        $isJsonRes = false;
        if(!empty($licenseKey)) {
            $responseJson = $this->aaochatService->activateLicenseKey($licenseKey);
            $response = json_decode($responseJson, true);
            if(isset($response['status']) && $response['status']=='success') {
                //Update setting in Nextcloud DB
                $this->aaochatService->updateAaochatConfigSetting($response);

                //Send API base URL to aaochat
                $this->aaochatService->syncApiSettings();

                $nc_users = $this->aaochatService->getAllUsers();
                $users = array();                
                foreach ($nc_users as $nc_user) {
                    try {
                        $user = array();

                        $userId = $nc_user->getUID();
                        $userEmail = $nc_user->getEMailAddress();
                        $userDisplayName = $nc_user->getDisplayName();
                        $avatarImage = $nc_user->getAvatarImage(100);

                        $user['user_name'] = $userId;
                        $user['user_email'] = $userEmail;
                        $user['display_name'] = $userDisplayName;
                        $user['avatar_image'] = $avatarImage;

                        array_push($users,$user);
                    } catch (Exception $e) {
                    }
                }

                $userData = array();
                $userData['users'] = $users;
                //Sync all users with aaochat
                $this->aaochatService->syncUserdataToAaochat($userData);


                //Get Auth Key
                $currentUser = $this->userSession->getUser();
                $userName = $currentUser->getUID();
                $userEmail = $currentUser->getEMailAddress();
                $userDisplayName = $currentUser->getDisplayName();
                $avatarImage = $currentUser->getAvatarImage(100);

                $userData = array();
                $userData['user_name'] = $userName;
                $userData['user_email'] = $userEmail;
                $userData['display_name'] = $userDisplayName;
                $userData['avatar_image'] = $avatarImage;
                $userToken = $this->apiAuthService->generateAuthToken($userName, $userEmail);
                $userData['token'] = $userToken;

                $aaochatUserToken = '"123"';
                $localStorageData = array();

                $aaochat_server_url = $this->config->getAppValue(Application::APP_ID, 'aaochat_ser_url', '');
                $aaochatServerUrl =  $aaochat_server_url;
                $aaochat_file_server_url = $this->config->getAppValue(Application::APP_ID, 'aaochat_ser_file_server_url', '');
                $aaochatFileServerUrl =  $aaochat_file_server_url;

                $userLoginResponse = $this->aaochatService->sendUserdataToAaochat($userData);
                $userLoginResponse = json_decode($userLoginResponse);
                if(isset($userLoginResponse->status) && $userLoginResponse->status=='success') {
                    
                    //Update lead status
                    //$this->config->setAppValue(Application::APP_ID, 'aaochat_lead_status', 'approved');
                    $aaochat_lead_id = $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_id', '');
                    $this->leadStatus($aaochat_lead_id);

                    $responseData = $userLoginResponse->data;
                    $aaochatUserToken = '"'.$responseData->auth_key.'"';
                    
                    $this->apiAuthMapper->updateAuthKey($userName, $responseData->auth_key);

                    unset($_COOKIE['ncUserAuthKey']);
                    setcookie('ncUserAuthKey', $aaochatUserToken, time() + 3600000*24*7, '/');

                    unset($_COOKIE['aaochatServerUrl']);
                    setcookie('aaochatServerUrl', $aaochatServerUrl, time() + 3600000*24*7, '/');

                    unset($_COOKIE['aaochatFileServerUrl']);
                    setcookie('aaochatFileServerUrl', $aaochatFileServerUrl, time() + 3600000*24*7, '/');

                    $localStorageData['ncUserAuthKey'] = $aaochatUserToken;
                    $localStorageData['aaochatServerUrl'] = $aaochatServerUrl;
                    $localStorageData['aaochatFileServerUrl'] = $aaochatFileServerUrl;

                    $response['data']['localStorageData'] = $localStorageData;
                } else {
                    $localStorageData['ncUserAuthKey'] = $aaochatUserToken;
                    $localStorageData['aaochatServerUrl'] = $aaochatServerUrl;
                    $localStorageData['aaochatFileServerUrl'] = $aaochatFileServerUrl;
                    
                    $response['data']['localStorageData'] = $localStorageData;
                }

            }
            $isJsonRes = true;
        } else {
            $response['message'] = 'License key is require';
        }

        if($isJsonRes == false) {
            $response = json_encode($response);
        }
        return $response;
    }

    /**
     * @NoAdminRequired
     *
     * @param mixed $licenseKey
     *
     * @return Response
     */
    public function surrenderLicense($licenseKey)
    {
        $response = array();
        $response['status'] = 'error';
        $response['data'] = array();
        $response['message'] = '';
        $isJsonRes = false;
        if(!empty($licenseKey)) {
            $responseJson = $this->aaochatService->surrenderLicenseKey($licenseKey);
            $response = json_decode($responseJson, true);
            if(isset($response['status']) && $response['status']=='success') {
                $this->aaochatService->updateAaochatConfigSetting($response);
            }
            $isJsonRes = true;
        } else {
            $response['message'] = 'License key is require';
        }

        if($isJsonRes == false) {
            $response = json_encode($response);
        }
        return $response;
    }

     /**
     * @NoAdminRequired
     *
     * @param mixed $licenseKey
     *
     * @return Response
     */
    public function validateLicense($licenseKey)
    {
        $response = array();
        $response['status'] = 'error';
        $response['data'] = array();
        $response['message'] = '';
        $isJsonRes = false;
        if(!empty($licenseKey)) {
            $responseJson = $this->aaochatService->validateLicenseKey($licenseKey);
            $response = json_decode($responseJson, true);
            if(isset($response['status']) && $response['status']=='success') {
                $responseData = $response['data'];
                $id = $responseData['_id'];
                $clientId = $responseData['clientId'];
                $licenseKey = $responseData['licenseKey'];
                $serverDetails = $responseData['serverDetails'];
                $serverInstanceType = $serverDetails['serverInstanceType'];
                $url = $serverDetails['url'];
                $fileServerUrl = $serverDetails['fileServerUrl'];
                $storageSize = $serverDetails['storageSize'];
                $serverDetailsId = $serverDetails['_id'];
                $status = $responseData['status'];
                $activationDate = $responseData['activationDate'];
                $ipAddress = $responseData['ipAddress'];

                $isLicenseValid = 'no';
                if($status == 'active') {
                    $isLicenseValid = 'yes';
                }

                $this->config->setAppValue(Application::APP_ID, 'aaochat_is_license_valid', $isLicenseValid);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_license_status', $status);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_activation_date', $activationDate);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_ipaddress', $ipAddress);
            }
            $isJsonRes = true;
        } else {
            $response['message'] = 'License key is require';
        }

        if($isJsonRes == false) {
            $response = json_encode($response);
        }
        return $response;
    }
}
