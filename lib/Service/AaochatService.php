<?php

namespace OCA\AaoChat\Service;

use OCP\L10N\IFactory;
use OCP\IURLGenerator;
use OCA\AaoChat\AppInfo\Application;
use OCA\AaoChat\Service\ConfigProxy;
use OCP\IConfig;
use OCP\IUserManager;
use OCP\IUser;
use OCP\IUserSession;

use OCA\AaoChat\Db\Apiauth;
use OCA\AaoChat\Db\ApiauthMapper;


/**
 * class AppRepository.
 *
 * @author Simon Vieille <simon@deblan.fr>
 */
class AaochatService
{
    /**
     * @var \OC_App
     */
    protected $ocApp;

    /**
     * @var IFactory
     */
    protected $l10nFactory;

    protected $headerAuthBase;

    protected $apiauth_mapper;

    /** @var IUserSession */
    private $userSession;

    private $l;
    private $apiRequestUrl;

    /**
     * @var ConfigProxy
     */
    protected $config;

    /** @var IURLGenerator */
    private $urlGenerator;

    private $host_url;
    public $content_api_base_url = 'https://business2.aaochat.com/next-cloud/';
    private $api_base_url = 'https://master.aaochat.com'; 
    private $aaochat_instance_type;
    private $aaochat_server_url;
    private $aaochat_file_server_url;
    private $aaochat_license_key;
    private $aaochat_log_dir;
    private $is_aaochat_api_log_enable = false;
    /** @var IUserManager */
    private $userManager;

    public function __construct(
        IFactory $l10nFactory,
        IConfig $config,
        IURLGenerator $urlGenerator,
        IUserManager $userManager,
        IUserSession $userSession,
        ApiauthMapper $apiauthMapper
    )
    {
        //$this->ocApp = $ocApp;
        $this->l10nFactory = $l10nFactory;
        $this->config = $config;
        $this->urlGenerator = $urlGenerator;

        $aaochat_log_dir = \OC::$SERVERROOT."/data";//\OC::$server->getAppDataDir('aaochat');
        $this->aaochat_log_dir = $aaochat_log_dir."/aaochat_logs/";
        if (!file_exists($this->aaochat_log_dir))
        {
            mkdir($this->aaochat_log_dir,0777,true);  
        }

        $url_generator   = \OC::$server->getURLGenerator();
        $this->host_url       =  $url_generator->getAbsoluteURL('');
        
        $this->userManager = $userManager;
        $this->userSession  = $userSession;
        $this->apiauth_mapper = $apiauthMapper;

        $this->l = \OC::$server->getL10N('aaochat');

        $this->init();
    }

    public function init() {
        $this->aaochat_instance_type = $this->config->getAppValue(Application::APP_ID, 'aaochat_ser_instance_type', '');
        $this->aaochat_server_url = $this->config->getAppValue(Application::APP_ID, 'aaochat_ser_url', '');
        $this->aaochat_file_server_url = $this->config->getAppValue(Application::APP_ID, 'aaochat_ser_file_server_url', '');
        $this->aaochat_license_key = $this->config->getAppValue(Application::APP_ID, 'aaochat_license_key', '');
        $this->content_api_base_url = $this->aaochat_server_url.'/next-cloud/';
    }

    public function setHeaderAuthBase($headerAuthBase) {
        $this->headerAuthBase = $headerAuthBase;
    }

    public function getHeaderAuthBase() {
        return $this->headerAuthBase;
    }

    public function getAaochatServerUrl() {
        return $this->aaochat_server_url;
    }

    public function getContentAPIBaseUrl() {
        return $this->content_api_base_url;
    }

    public function getAaochatFileServerUrl() {
        return $this->aaochat_file_server_url;
    }

    public function getAaochatLicenseKey() {
        return $this->aaochat_license_key;
    }

    public function getAaochatLogPath() {
        return $this->aaochat_log_dir;
    }

    public function isAaochatApiLogEnable() {
        return $this->is_aaochat_api_log_enable;
    }

    public function removeHttp($url) {
       $disallowed = array('http://', 'https://');
       foreach($disallowed as $d) {
          if(strpos($url, $d) === 0) {
             return str_replace($d, '', $url);
          }
       }
       return $url;
    }

    public function curlPost($type=0, $header=null, $url=null, $data=null) {
        //$output = '';
        if(strpos($url, 'http') === false) {
            $apiUrl = $this->api_base_url.$url;
        } else {
            $apiUrl = $url;
        }
        $this->apiRequestUrl = $apiUrl;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $apiUrl);
        
        if($type ==1)
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PROPFIND');
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        if($data!=null)
        {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);

        return $output;
    }

    public function curlPut($type=0, $header=null, $url=null, $data=null) {

        if(strpos($url, 'http') === false) {
            $apiUrl = $this->api_base_url.$url;
        } else {
            $apiUrl = $url;
        }
        $this->apiRequestUrl = $apiUrl;
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $apiUrl);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        if($data!=null)
        {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);

        return $output;
    }

    public function curlGet($requestUrl, $header=null) {
        if(strpos($requestUrl, 'http') === false) {
            $apiUrl = $this->api_base_url.$requestUrl;
        } else {
            $apiUrl = $requestUrl;
        }
        $this->apiRequestUrl = $apiUrl;
        
        $ch   = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);

        return $server_output;
    }



    public function createLead($leadData) {

        $header = array(
            "Accept:application/json",
            "Content-Type:application/json"
        );
        $url = '/lead';
        $data = $leadData;

        //$data = json_encode($data);
        $apiResponse = $this->curlPost(0,$header,$url,$data);

        return $apiResponse;
    }

    public function updateLead($aaochat_license_key,$leadData) {

        $header = array(
            "Accept:application/json",
            "Content-Type:application/json"
        );
        $url = '/clients/license/'.$aaochat_license_key;
        $data = $leadData;

        //$data = json_encode($data);
        $apiResponse = $this->curlPut(0,$header,$url,$data);

        if($this->isAaochatApiLogEnable()) {
            $logData = array();
            $logData['url'] = $url;
            $logData['apiRequestUrl'] = $this->apiRequestUrl;
            $logData['headers'] = $header;
            $logData['data'] = $data;
            $logData['apiResponse'] = json_decode($apiResponse);
            $logData = json_encode($logData);
            $myfile = file_put_contents($this->aaochat_log_dir.'update_lead_aaochat.txt', $logData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }
        return $apiResponse;
    }

    public function updateAaochatLeadData($action,$response) {
        if(!empty($response)) {
            $responseData = $response['data'];
            $id = '';
            if($action == 'add') {
                $id = $responseData['_id'];
            }
            $name = $responseData['name'];
            $email = $responseData['email'];
            $phoneCountryCode = $responseData['countryCode'];
            $phoneNo = $responseData['phoneNo'];
            $country = $responseData['country'];
            $organization = $responseData['organization'];
            $organizationAddress = $responseData['companyAddress'];
            $organizationSiteURL = $responseData['siteUrl'];
            $status = $responseData['status'];

            if($action == 'add') {
                $isLeadCreated = 'no';
                if(!empty($id)) {
                    $isLeadCreated = 'yes';
                }
            } else if($action == 'update') {
                $isLeadCreated = 'yes';
            }

            if($isLeadCreated == 'yes') {
                if($action == 'add') {
                    $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_id', $id);
                }
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_name', $name);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_email', $email);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_phone_contry_code', $phoneCountryCode);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_phone', $phoneNo);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_country', $country);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_organization', $organization);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_organization_address', $organizationAddress);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_organization_siteurl', $organizationSiteURL);
                if($action == 'add') {
                    $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_status', $status);
                } else if($action == 'update') {
                    $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_status', 'active');
                }
            }
        }
    }

    public function getLeadStatus($aaochat_lead_id) {

        $header = array(
            "Accept:application/json",
            "Content-Type:application/json"
        );
        $url = '/lead/'.$aaochat_lead_id;
        $data = array();

        //$data = json_encode($data);
        $apiResponse = $this->curlGet($url,$header);

        return $apiResponse;
    }

    public function updateAaochatLeadStatus($response) {
        if(!empty($response)) {
            $responseData = $response['data'];
            $status = $responseData['status'];

            $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_status', $status);
        }
    }

    public function validateLicenseKey($licenseKey) {
        /*
        $userName = 'admin@gmail.com';
        $userName = 'admin';
        $authBase = base64_encode($userName.':'.$password);
        $this->setHeaderAuthBase($authBase);
        $headerAuthBase = $this->getHeaderAuthBase();
        */

        $header = array(
            "Accept:application/json",
            "Content-Type:application/json"
        );
        $url = '/licenses/validate';
        $data = array(
            'licenseKey' => $licenseKey
        );

        //$data = json_encode($data);
        $apiResponse = $this->curlPost(0,$header,$url,$data);

        return $apiResponse;
    }

    public function activateLicenseKey($licenseKey) {

        $header = array(
            "Accept:application/json",
            "Content-Type:application/json"
        );
        $url = '/licenses/activate';
        $data = array(
            'licenseKey' => $licenseKey
        );

        //$data = json_encode($data);
        $apiResponse = $this->curlPost(0,$header,$url,$data);

        if($this->isAaochatApiLogEnable()) {
            $logData = array();
            $logData['url'] = $url;
            $logData['apiRequestUrl'] = $this->apiRequestUrl;
            $logData['headers'] = $header;
            $logData['data'] = $data;
            $logData['apiResponse'] = json_decode($apiResponse);
            $logData = json_encode($logData);
            $myfile = file_put_contents($this->aaochat_log_dir.'activate_licensekey_aaochat.txt', $logData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }

        return $apiResponse;
    }

    public function surrenderLicenseKey($licenseKey) {

        $header = array(
            "Accept:application/json",
            "Content-Type:application/json"
        );
        $url = '/licenses/surrender';
        $data = array(
            'licenseKey' => $licenseKey
        );

        //$data = json_encode($data);
        $apiResponse = $this->curlPost(0,$header,$url,$data);

        if($this->isAaochatApiLogEnable()) {
            $logData = array();
            $logData['url'] = $url;
            $logData['apiRequestUrl'] = $this->apiRequestUrl;
            $logData['headers'] = $header;
            $logData['data'] = $data;
            $logData['apiResponse'] = json_decode($apiResponse);
            $logData = json_encode($logData);
            $myfile = file_put_contents($this->aaochat_log_dir.'surrender_licensekey_aaochat.txt', $logData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }

        return $apiResponse;
    }

    public function updateAaochatConfigSetting($response) {
        if(!empty($response)) {
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

            $this->config->setAppValue(Application::APP_ID, 'aaochat_client_id', $clientId);
            $this->config->setAppValue(Application::APP_ID, 'aaochat_license_key', $licenseKey);
            $this->config->setAppValue(Application::APP_ID, 'aaochat_is_license_valid', $isLicenseValid);

            $this->config->setAppValue(Application::APP_ID, 'aaochat_ser_instance_type', $serverInstanceType);
            $this->config->setAppValue(Application::APP_ID, 'aaochat_ser_url', $url);
            $this->config->setAppValue(Application::APP_ID, 'aaochat_ser_file_server_url', $fileServerUrl);
            $this->config->setAppValue(Application::APP_ID, 'aaochat_ser_storage_size', $storageSize);
            $this->config->setAppValue(Application::APP_ID, 'aaochat_ser_id', $serverDetailsId);

            $this->config->setAppValue(Application::APP_ID, 'aaochat_license_status', $status);
            $this->config->setAppValue(Application::APP_ID, 'aaochat_license_id', $id);
            $this->config->setAppValue(Application::APP_ID, 'aaochat_activation_date', $activationDate);
            $this->config->setAppValue(Application::APP_ID, 'aaochat_ipaddress', $ipAddress);

            if(isset($responseData['client']) && !empty($responseData['client'])) {
                $clientData = $responseData['client'];

                $client_name = isset($clientData['name'])?$clientData['name']:'';
                $client_email = isset($clientData['email'])?$clientData['email']:'';
                $client_phoneCountryCode = isset($clientData['countryCode'])?$clientData['countryCode']:'';
                $client_phoneNo = isset($clientData['phoneNo'])?$clientData['phoneNo']:'';
                $client_country = isset($clientData['country'])?$clientData['country']:'';
                $client_organization = isset($clientData['organization'])?$clientData['organization']:'';
                $client_organizationAddress = isset($clientData['companyAddress'])?$clientData['companyAddress']:'';
                $client_organizationSiteURL = isset($clientData['siteUrl'])?$clientData['siteUrl']:'';
                $client_status = isset($clientData['status'])?$clientData['status']:'';

                //$this->config->setAppValue(Application::APP_ID, 'aaochat_lead_id', $id);
                //$aaochat_lead_name = $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_name', '');
                if(!empty($client_name)) {
                    $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_name', $client_name);
                }
                //$aaochat_lead_email = $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_email', '');
                if(!empty($client_email)) {
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_email', $client_email);
                }
                //$aaochat_lead_phone_contry_code = $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_phone_contry_code', '');
                if(!empty($client_phoneCountryCode)) {
                    $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_phone_contry_code', $client_phoneCountryCode);
                }
                //$aaochat_lead_phone = $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_phone', '');
                if(!empty($client_phoneNo)) {
                    $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_phone', $client_phoneNo);
                }
                //$aaochat_lead_country = $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_country', '');
                if(!empty($client_country)) {
                    $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_country', $client_country);
                }
                //$aaochat_lead_organization = $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_organization', '');
                if(!empty($client_organization)) {
                    $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_organization', $client_organization);
                }
                //$aaochat_lead_organization_address = $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_organization_address', '');
                if(!empty($client_organizationAddress)) {
                    $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_organization_address', $client_organizationAddress);
                }
                //$aaochat_lead_organization_siteurl = $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_organization_siteurl', '');
                if(!empty($client_organizationSiteURL)) {
                    $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_organization_siteurl', $client_organizationSiteURL);
                }
                //$aaochat_lead_status = $this->config->getAppValue(Application::APP_ID, 'aaochat_lead_status', '');
                if(!empty($client_status)) {
                    $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_status', $client_status);
                }

            }
            $this->init();
        }
    }

    public function cleanAaochatConfigSetting() {        

        $this->config->setAppValue(Application::APP_ID, 'aaochat_client_id', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_license_key', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_is_license_valid', '');

        $this->config->setAppValue(Application::APP_ID, 'aaochat_ser_instance_type', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_ser_url', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_ser_file_server_url', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_ser_storage_size', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_ser_id', '');

        $this->config->setAppValue(Application::APP_ID, 'aaochat_license_status', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_license_id', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_activation_date', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_ipaddress', '');


        $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_name', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_email', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_phone_contry_code', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_phone', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_country', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_organization', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_organization_address', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_organization_siteurl', '');
        $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_status', '');

        $this->init();
    }

    public function syncApiSettings() {

        $header = array(
            "Accept:application/json",
            "Content-Type:application/json",
            "Authorization:".$this->aaochat_license_key
        );
        $url = $this->content_api_base_url.'settings';
        $data = array(
            'base_url' => $this->host_url
        );

        //$data = json_encode($data);
        $apiResponse = $this->curlPost(0,$header,$url,$data);

        if($this->isAaochatApiLogEnable()) {
            $logData = array();
            $logData['url'] = $url;
            $logData['headers'] = $header;
            $logData['data'] = $data;
            $logData['apiResponse'] = json_decode($apiResponse);
            $logData = json_encode($logData);
            $myfile = file_put_contents($this->aaochat_log_dir.'setting_aaochat.txt', $logData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }

        return $apiResponse;
    }

    public function sendWebhookData($action, $data) {
        $webhookUrl = $this->aaochat_server_url."/webhook";

        $postData = array();
        $postData['action'] = $action;
        $postData['data'] = $data;

        $header = array(
            "Accept:application/json",
            "Content-Type:application/json"
        );

        //$webhookResponse = $this->curlPost(0,$header,$webhookUrl,$postData);
        //$webhookResponseData = json_decode($webhookResponse,true);

        //$apiResponseData = $webhookResponseData;
        if($this->isAaochatApiLogEnable()) {
            $apiResponseData = array();
            $apiResponseData['header'] = $header;
            $apiResponseData['request'] = $postData;

            $apiResponseJsonData = json_encode($apiResponseData);
            $myfile = file_put_contents($this->aaochat_log_dir.'webhook_response.txt', $apiResponseJsonData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }

        return true;//$webhookResponseData;

    }

    public function sendUserdataToAaochat($data) {

        $header = array(
            "Accept:application/json",
            "Content-Type:application/json",
            "Authorization:".$this->aaochat_license_key
        );
        $url = $this->content_api_base_url.'login';
        
        //$data = json_encode($data);
        $apiResponse = $this->curlPost(0,$header,$url,$data);

        if($this->isAaochatApiLogEnable()) {
            $logData = array();
            $logData['url'] = $url;
            $logData['headers'] = $header;
            $logData['data'] = $data;
            $logData['apiResponse'] = json_decode($apiResponse);
            $logData = json_encode($logData);
            $myfile = file_put_contents($this->aaochat_log_dir.'login_aaochat.txt', $logData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }

        return $apiResponse;
    }

    public function syncUserdataToAaochat($data) {

        $header = array(
            "Accept:application/json",
            "Content-Type:application/json",
            "Authorization:".$this->aaochat_license_key
        );
        $url = $this->content_api_base_url.'users/sync';
        
        //$data = json_encode($data);
        $apiResponse = $this->curlPost(0,$header,$url,$data);

        if($this->isAaochatApiLogEnable()) {
            $logData = array();
            $logData['url'] = $url;
            $logData['headers'] = $header;
            $logData['data'] = $data;
            $logData['apiResponse'] = $apiResponse;
            $logData = json_encode($logData);
            $myfile = file_put_contents($this->aaochat_log_dir.'sync_aaochat.txt', $logData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }

        return $apiResponse;
    }

    public function deleteUserdataFromAaochat($data) {

        $header = array(
            "Accept:application/json",
            "Content-Type:application/json",
            "Authorization:".$this->aaochat_license_key
        );
        $url = $this->content_api_base_url.'users/delete';
        
        //$data = json_encode($data);
        $apiResponse = $this->curlPost(0,$header,$url,$data);

        if($this->isAaochatApiLogEnable()) {
            $logData = array();
            $logData['url'] = $url;
            $logData['headers'] = $header;
            $logData['data'] = $data;
            $logData['apiResponse'] = $apiResponse;
            $logData = json_encode($logData);
            $myfile = file_put_contents($this->aaochat_log_dir.'user_delete_aaochat.txt', $logData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }

        return $apiResponse;
    }

    public function getAllUsers() {
        $users = $this->userManager->search('');

        return $users;
    }

    public function manageGroupToAaochat($data) {

        $header = array(
            "Accept:application/json",
            "Content-Type:application/json",
            "Authorization:".$this->aaochat_license_key
        );
        $url = $this->content_api_base_url.'managegroups';
        
        //$data = json_encode($data);
        $apiResponse = $this->curlPost(0,$header,$url,$data);

        if($this->isAaochatApiLogEnable()) {
            $logData = array();
            $logData['url'] = $url;
            $logData['headers'] = $header;
            $logData['data'] = $data;
            $logData['apiResponse'] = $apiResponse;
            $logData = json_encode($logData);
            $myfile = file_put_contents($this->aaochat_log_dir.'groups_aaochat.txt', $logData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }

        return $apiResponse;
    }

    /*Header Request*/
    private function getheaderdata() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /*
    For Get Share Data
    */
    public function getShareData($requestData)
    {
        $result_data = array();
        $response = array('status' => 'failed','error' => 1,'message' => $this->l->t('no data found.'));
        if(!empty($requestData) && isset($requestData['path']) &&$requestData['path']!='')
        {
            $path = $requestData['path'];
            $response = array('status' => 'failed','error' => 1,'message' => 'ok');

            //$header_auth = $this->getheaderdata()["Authorization"];
            $user = $this->userSession->getUser();
            $userId = $user->getUID();
            $apiAuth = $this->apiauth_mapper->getApiAuthByUsername($userId);
            $logData = array();

            $header_auth = '123';
            if(!empty($apiAuth)) {
                $header_auth = $apiAuth['auth_base'];
                $logData['header_auth'] = $header_auth;
            }  
            
            $logData['user'] = $userId;
            $logData['apiAuth'] = $apiAuth;

            $header = array(
                "Accept:application/json",
                "Authorization: Basic $header_auth",
                "OCS-APIRequest: true"
            );

            $logData['header'] = $header;
            $logData = json_encode($logData);
            $myfile = file_put_contents($this->aaochat_log_dir.'sharedata_aaochat.txt', $logData.PHP_EOL , FILE_APPEND | LOCK_EX);


            $surl  = $this->host_url.'ocs/v2.php/apps/files_sharing/api/v1/shares';

            if(isset($path) && !empty($path)) {
                $surl  .= "?path=".urlencode($path);
            }

            $server_output = $this->curlGet($surl, $header);

            $result = json_decode($server_output,true);
            if( $result['ocs']['meta']['status'] === 'ok' )
            {
                $result_data = $result['ocs']['data'];
                $response = array('status' => 'success','error' => 0,'message' => 'Success','data'=>$result_data);
            }
            else
            {
                $result_data = $result['ocs']['meta']['message'];
                $response = array('status' => 'failed','error' => 0,'message' => 'Failed','data'=>$result_data);
            }

        }
        return $response;
        exit;
    }

    public function getConversation($data) {
        //{"channel_id":"6343c397fb15d74513f4ba82","user_id":"1663917724555","message_id":""}
        $header = array(
            "Accept:application/json",
            "Content-Type:application/json",
            "Authorization:".$this->aaochat_license_key
        );
        $url = $this->aaochat_server_url.'/api/channel/messages';
        
        //$data = json_encode($data);
        $apiResponse = $this->curlPost(0,$header,$url,$data);

        if($this->isAaochatApiLogEnable()) {
            $logData = array();
            $logData['url'] = $url;
            $logData['headers'] = $header;
            $logData['data'] = $data;
            $logData['apiResponse'] = $apiResponse;
            $logData = json_encode($logData);
            $myfile = file_put_contents($this->aaochat_log_dir.'get_conversation.txt', $logData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }

        return $apiResponse;
    }

    public function getAaochatGroup($objectId) {

        $header = array(
            "Accept:application/json",
            "Content-Type:application/json",
            "Authorization:".$this->aaochat_license_key
        );
        $url = $this->content_api_base_url.'channel/find?object_id='.$objectId;
        
        //$data = json_encode($data);
        $apiResponse = $this->curlGet($url, $header);

        if($this->isAaochatApiLogEnable()) {
            $logData = array();
            $logData['url'] = $url;
            $logData['headers'] = $header;
            $logData['objectId'] = $objectId;
            $logData['apiResponse'] = $apiResponse;
            $logData = json_encode($logData);
            $myfile = file_put_contents($this->aaochat_log_dir.'get_group_details.txt', $logData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }

        return $apiResponse;
    }

    public static function getCountries() {
        $countries = array();
        $countries = array("Afghanistan" => "Afghanistan",
        "Aland Islands" => "Aland Islands",
        "Albania"=>"Albania",
        "Algeria"=>"Algeria",
        "American Samoa"=>"American Samoa",
        "Andorra"=>"Andorra",
        "Angola"=>"Angola",
        "Anguilla"=>"Anguilla",
        "Antarctica"=>"Antarctica",
        "Antigua and Barbuda"=>"Antigua and Barbuda",
        "Argentina"=>"Argentina",
        "Armenia"=>"Armenia",
        "Aruba"=>"Aruba",
        "Australia"=>"Australia",
        "Austria"=>"Austria",
        "Azerbaijan"=>"Azerbaijan",
        "Bahamas"=>"Bahamas",
        "Bahrain"=>"Bahrain",
        "Bangladesh"=>"Bangladesh",
        "Barbados"=>"Barbados",
        "Belarus"=>"Belarus",
        "Belgium"=>"Belgium",
        "Belize"=>"Belize",
        "Benin"=>"Benin",
        "Bermuda"=>"Bermuda",
        "Bhutan"=>"Bhutan",
        "Bolivia"=>"Bolivia",
        "Bosnia and Herzegovina"=>"Bosnia and Herzegovina",
        "Botswana"=>"Botswana",
        "Bouvet Island"=>"Bouvet Island",
        "Brazil"=>"Brazil",
        "British Indian Ocean Territory"=>"British Indian Ocean Territory",
        "Brunei Darussalam"=>"Brunei Darussalam",
        "Bulgaria"=>"Bulgaria",
        "Burkina Faso"=>"Burkina Faso",
        "Burundi"=>"Burundi",
        "Cambodia"=>"Cambodia",
        "Cameroon"=>"Cameroon",
        "Canada"=>"Canada",
        "Cape Verde"=>"Cape Verde",
        "Cayman Islands"=>"Cayman Islands",
        "Central African Republic"=>"Central African Republic",
        "Chad"=>"Chad",
        "Chile"=>"Chile",
        "China"=>"China",
        "Christmas Island"=>"Christmas Island",
        "Cocos (Keeling) Islands"=>"Cocos (Keeling) Islands",
        "Colombia"=>"Colombia",
        "Comoros"=>"Comoros",
        "Congo"=>"Congo",
        "Congo, The Democratic Republic of The"=>"Congo, The Democratic Republic of The",
        "Cook Islands"=>"Cook Islands",
        "Costa Rica"=>"Costa Rica",
        "Cote D'ivoire"=>"Cote D'ivoire",
        "Croatia"=>"Croatia",
        "Cuba"=>"Cuba",
        "Curaçao"=>"Curaçao",
        "Cyprus"=>"Cyprus",
        "Czech Republic"=>"Czech Republic",
        "Denmark"=>"Denmark",
        "Djibouti"=>"Djibouti",
        "Dominica"=>"Dominica",
        "Dominican Republic"=>"Dominican Republic",
        "Ecuador"=>"Ecuador",
        "Egypt"=>"Egypt",
        "El Salvador"=>"El Salvador",
        "Equatorial Guinea"=>"Equatorial Guinea",
        "Eritrea"=>"Eritrea",
        "Estonia"=>"Estonia",
        "Ethiopia"=>"Ethiopia",
        "Falkland Islands (Malvinas)"=>"Falkland Islands (Malvinas)",
        "Faroe Islands"=>"Faroe Islands",
        "Fiji"=>"Fiji",
        "Finland"=>"Finland",
        "France"=>"France",
        "French Guiana"=>"French Guiana",
        "French Polynesia"=>"French Polynesia",
        "French Southern Territories"=>"French Southern Territories",
        "Gabon"=>"Gabon",
        "Gambia"=>"Gambia",
        "Georgia"=>"Georgia",
        "Germany"=>"Germany",
        "Ghana"=>"Ghana",
        "Gibraltar"=>"Gibraltar",
        "Greece"=>"Greece",
        "Greenland"=>"Greenland",
        "Grenada"=>"Grenada",
        "Guadeloupe"=>"Guadeloupe",
        "Guam"=>"Guam",
        "Guatemala"=>"Guatemala",
        "Guernsey"=>"Guernsey",
        "Guinea"=>"Guinea",
        "Guinea-bissau"=>"Guinea-bissau",
        "Guyana"=>"Guyana",
        "Haiti"=>"Haiti",
        "Heard Island and Mcdonald Islands"=>"Heard Island and Mcdonald Islands",
        "Holy See (Vatican City State)"=>"Holy See (Vatican City State)",
        "Honduras"=>"Honduras",
        "Hong Kong"=>"Hong Kong",
        "Hungary"=>"Hungary",
        "Iceland"=>"Iceland",
        "India"=>"India",
        "Indonesia"=>"Indonesia",
        "Iran, Islamic Republic of"=>"Iran, Islamic Republic of",
        "Iraq"=>"Iraq",
        "Ireland"=>"Ireland",
        "Isle of Man"=>"Isle of Man",
        "Israel"=>"Israel",
        "Italy"=>"Italy",
        "Jamaica"=>"Jamaica",
        "Japan"=>"Japan",
        "Jersey"=>"Jersey",
        "Jordan"=>"Jordan",
        "Kazakhstan"=>"Kazakhstan",
        "Kenya"=>"Kenya",
        "Kiribati"=>"Kiribati",
        "Korea, Democratic People's Republic of"=>"Korea, Democratic People's Republic of",
        "Korea, Republic of"=>"Korea, Republic of",
        "Kuwait"=>"Kuwait",
        "Kyrgyzstan"=>"Kyrgyzstan",
        "Lao People's Democratic Republic"=>"Lao People's Democratic Republic",
        "Latvia"=>"Latvia",
        "Lebanon"=>"Lebanon",
        "Lesotho"=>"Lesotho",
        "Liberia"=>"Liberia",
        "Libyan Arab Jamahiriya"=>"Libyan Arab Jamahiriya",
        "Liechtenstein"=>"Liechtenstein",
        "Lithuania"=>"Lithuania",
        "Luxembourg"=>"Luxembourg",
        "Macao"=>"Macao",
        "Macedonia, The Former Yugoslav Republic of"=>"Macedonia, The Former Yugoslav Republic of",
        "Madagascar"=>"Madagascar",
        "Malawi"=>"Malawi",
        "Malaysia"=>"Malaysia",
        "Maldives"=>"Maldives",
        "Mali"=>"Mali",
        "Malta"=>"Malta",
        "Marshall Islands"=>"Marshall Islands",
        "Martinique"=>"Martinique",
        "Mauritania"=>"Mauritania",
        "Mauritius"=>"Mauritius",
        "Mayotte"=>"Mayotte",
        "Mexico"=>"Mexico",
        "Micronesia, Federated States of"=>"Micronesia, Federated States of",
        "Moldova, Republic of"=>"Moldova, Republic of",
        "Monaco"=>"Monaco",
        "Mongolia"=>"Mongolia",
        "Montenegro"=>"Montenegro",
        "Montserrat"=>"Montserrat",
        "Morocco"=>"Morocco",
        "Mozambique"=>"Mozambique",
        "Myanmar"=>"Myanmar",
        "Namibia"=>"Namibia",
        "Nauru"=>"Nauru",
        "Nepal"=>"Nepal",
        "Netherlands"=>"Netherlands",
        "New Caledonia"=>"New Caledonia",
        "New Zealand"=>"New Zealand",
        "Nicaragua"=>"Nicaragua",
        "Niger"=>"Niger",
        "Nigeria"=>"Nigeria",
        "Niue"=>"Niue",
        "Norfolk Island"=>"Norfolk Island",
        "Northern Mariana Islands"=>"Northern Mariana Islands",
        "Norway"=>"Norway",
        "Oman"=>"Oman",
        "Pakistan"=>"Pakistan",
        "Palau"=>"Palau",
        "Palestinian Territory, Occupied"=>"Palestinian Territory, Occupied",
        "Panama"=>"Panama",
        "Papua New Guinea"=>"Papua New Guinea",
        "Paraguay"=>"Paraguay",
        "Peru"=>"Peru",
        "Philippines"=>"Philippines",
        "Pitcairn"=>"Pitcairn",
        "Poland"=>"Poland",
        "Portugal"=>"Portugal",
        "Puerto Rico"=>"Puerto Rico",
        "Qatar"=>"Qatar",
        "Reunion"=>"Reunion",
        "Romania"=>"Romania",
        "Russia"=>"Russia",
        "Rwanda"=>"Rwanda",
        "Saint Helena"=>"Saint Helena",
        "Saint Kitts and Nevis"=>"Saint Kitts and Nevis",
        "Saint Lucia"=>"Saint Lucia",
        "Saint Pierre and Miquelon"=>"Saint Pierre and Miquelon",
        "Saint Vincent and The Grenadines"=>"Saint Vincent and The Grenadines",
        "Samoa"=>"Samoa",
        "San Marino"=>"San Marino",
        "Sao Tome and Principe"=>"Sao Tome and Principe",
        "Saudi Arabia"=>"Saudi Arabia",
        "Senegal"=>"Senegal",
        "Serbia"=>"Serbia",
        "Seychelles"=>"Seychelles",
        "Sierra Leone"=>"Sierra Leone",
        "Singapore"=>"Singapore",
        "Slovakia"=>"Slovakia",
        "Slovenia"=>"Slovenia",
        "Solomon Islands"=>"Solomon Islands",
        "Somalia"=>"Somalia",
        "South Africa"=>"South Africa",
        "South Georgia and The South Sandwich Islands"=>"South Georgia and The South Sandwich Islands",
        "Spain"=>"Spain",
        "Sri Lanka"=>"Sri Lanka",
        "Sudan"=>"Sudan",
        "Suriname"=>"Suriname",
        "Svalbard and Jan Mayen"=>"Svalbard and Jan Mayen",
        "Eswatini"=>"Eswatini",
        "Sweden"=>"Sweden",
        "Switzerland"=>"Switzerland",
        "Syrian Arab Republic"=>"Syrian Arab Republic",
        "Taiwan (ROC)"=>"Taiwan (ROC)",
        "Tajikistan"=>"Tajikistan",
        "Tanzania, United Republic of"=>"Tanzania, United Republic of",
        "Thailand"=>"Thailand",
        "Timor-leste"=>"Timor-leste",
        "Togo"=>"Togo",
        "Tokelau"=>"Tokelau",
        "Tonga"=>"Tonga",
        "Trinidad and Tobago"=>"Trinidad and Tobago",
        "Tunisia"=>"Tunisia",
        "Turkey"=>"Turkey",
        "Turkmenistan"=>"Turkmenistan",
        "Turks and Caicos Islands"=>"Turks and Caicos Islands",
        "Tuvalu"=>"Tuvalu",
        "Uganda"=>"Uganda",
        "Ukraine"=>"Ukraine",
        "United Arab Emirates"=>"United Arab Emirates",
        "United Kingdom"=>"United Kingdom",
        "United States"=>"United States",
        "United States Minor Outlying Islands"=>"United States Minor Outlying Islands",
        "Uruguay"=>"Uruguay",
        "Uzbekistan"=>"Uzbekistan",
        "Vanuatu"=>"Vanuatu",
        "Venezuela"=>"Venezuela",
        "Vietnam"=>"Vietnam",
        "Virgin Islands, British"=>"Virgin Islands, British",
        "Virgin Islands, U.S."=>"Virgin Islands, U.S.",
        "Wallis and Futuna"=>"Wallis and Futuna",
        "Western Sahara"=>"Western Sahara",
        "Yemen"=>"Yemen",
        "Zambia"=>"Zambia",
        "Zimbabwe"=>"Zimbabwe");

        return $countries;
    }

    public static function phoneCountryCode()
    {
        return [
            'AD'=>['name'=>'ANDORRA','code'=>'+376'],
            'AE'=>['name'=>'UNITED ARAB EMIRATES','code'=>'+971'],
            'AF'=>['name'=>'AFGHANISTAN','code'=>'+93'],
            'AG'=>['name'=>'ANTIGUA AND BARBUDA','code'=>'+1268'],
            'AI'=>['name'=>'ANGUILLA','code'=>'+1264'],
            'AL'=>['name'=>'ALBANIA','code'=>'+355'],
            'AM'=>['name'=>'ARMENIA','code'=>'+374'],
            'AN'=>['name'=>'NETHERLANDS ANTILLES','code'=>'+599'],
            'AO'=>['name'=>'ANGOLA','code'=>'+244'],
            'AQ'=>['name'=>'ANTARCTICA','code'=>'+672'],
            'AR'=>['name'=>'ARGENTINA','code'=>'+54'],
            'AS'=>['name'=>'AMERICAN SAMOA','code'=>'+1684'],
            'AT'=>['name'=>'AUSTRIA','code'=>'+43'],
            'AU'=>['name'=>'AUSTRALIA','code'=>'+61'],
            'AW'=>['name'=>'ARUBA','code'=>'+297'],
            'AX'=>['name'=>'ÅLAND ISLANDS','code'=>'+358'],
            'AZ'=>['name'=>'AZERBAIJAN','code'=>'+994'],
            'BA'=>['name'=>'BOSNIA AND HERZEGOVINA','code'=>'+387'],
            'BB'=>['name'=>'BARBADOS','code'=>'+1246'],
            'BD'=>['name'=>'BANGLADESH','code'=>'+880'],
            'BE'=>['name'=>'BELGIUM','code'=>'+32'],
            'BF'=>['name'=>'BURKINA FASO','code'=>'+226'],
            'BG'=>['name'=>'BULGARIA','code'=>'+359'],
            'BH'=>['name'=>'BAHRAIN','code'=>'+973'],
            'BI'=>['name'=>'BURUNDI','code'=>'+257'],
            'BJ'=>['name'=>'BENIN','code'=>'+229'],
            //'BL'=>['name'=>'SAINT BARTHELEMY','code'=>'+590'],
            'BM'=>['name'=>'BERMUDA','code'=>'+1441'],
            'BN'=>['name'=>'BRUNEI DARUSSALAM','code'=>'+673'],
            'BO'=>['name'=>'BOLIVIA','code'=>'+591'],
            //'BQ'=>['name'=>'CARIBEAN NETHERLANDS','code'=>'+599'],
            'BR'=>['name'=>'BRAZIL','code'=>'+55'],
            'BS'=>['name'=>'BAHAMAS','code'=>'+1242'],
            'BT'=>['name'=>'BHUTAN','code'=>'+975'],
            //'BV'=>['name'=>'BOUVET ISLAND','code'=>'+55'],
            'BW'=>['name'=>'BOTSWANA','code'=>'+267'],
            'BY'=>['name'=>'BELARUS','code'=>'+375'],
            'BZ'=>['name'=>'BELIZE','code'=>'+501'],
            //'CA'=>['name'=>'CANADA','code'=>'+1'],
            //'CC'=>['name'=>'COCOS (KEELING) ISLANDS','code'=>'+61'],
            'CD'=>['name'=>'CONGO, THE DEMOCRATIC REPUBLIC OF THE','code'=>'+243'],
            'CF'=>['name'=>'CENTRAL AFRICAN REPUBLIC','code'=>'+236'],
            //'CG'=>['name'=>'CONGO','code'=>'+242'],
            'CH'=>['name'=>'SWITZERLAND','code'=>'+41'],
            'CI'=>['name'=>'COTE D IVOIRE','code'=>'+225'],
            'CK'=>['name'=>'COOK ISLANDS','code'=>'+682'],
            'CL'=>['name'=>'CHILE','code'=>'+56'],
            'CM'=>['name'=>'CAMEROON','code'=>'+237'],
            'CN'=>['name'=>'CHINA','code'=>'+86'],
            'CO'=>['name'=>'COLOMBIA','code'=>'+57'],
            'CR'=>['name'=>'COSTA RICA','code'=>'+506'],
            'CU'=>['name'=>'CUBA','code'=>'+53'],
            'CV'=>['name'=>'CAPE VERDE','code'=>'+238'],
            //'CW'=>['name'=>'CURAÇAO','code'=>'+599'],
            //'CX'=>['name'=>'CHRISTMAS ISLAND','code'=>'+61'],
            'CY'=>['name'=>'CYPRUS','code'=>'+357'],
            'CZ'=>['name'=>'CZECH REPUBLIC','code'=>'+420'],
            'DE'=>['name'=>'GERMANY','code'=>'+49'],
            'DJ'=>['name'=>'DJIBOUTI','code'=>'+253'],
            'DK'=>['name'=>'DENMARK','code'=>'+45'],
            'DM'=>['name'=>'DOMINICA','code'=>'+1767'],
            'DO'=>['name'=>'DOMINICAN REPUBLIC','code'=>'+1809'],
            'DZ'=>['name'=>'ALGERIA','code'=>'+213'],
            'EC'=>['name'=>'ECUADOR','code'=>'+593'],
            'EE'=>['name'=>'ESTONIA','code'=>'+372'],
            'EG'=>['name'=>'EGYPT','code'=>'+20'],
            'EH'=>['name'=>'WESTERN SAHARA','code'=>'+212'],
            'ER'=>['name'=>'ERITREA','code'=>'+291'],
            'ES'=>['name'=>'SPAIN','code'=>'+34'],
            'ET'=>['name'=>'ETHIOPIA','code'=>'+251'],
            //'FI'=>['name'=>'FINLAND','code'=>'+358'],
            'FJ'=>['name'=>'FIJI','code'=>'+679'],
            'FK'=>['name'=>'FALKLAND ISLANDS (MALVINAS)','code'=>'+500'],
            'FM'=>['name'=>'MICRONESIA, FEDERATED STATES OF','code'=>'+691'],
            'FO'=>['name'=>'FAROE ISLANDS','code'=>'+298'],
            'FR'=>['name'=>'FRANCE','code'=>'+33'],
            'GA'=>['name'=>'GABON','code'=>'+241'],
            'GB'=>['name'=>'UNITED KINGDOM','code'=>'+44'],
            'GD'=>['name'=>'GRENADA','code'=>'+1473'],
            'GE'=>['name'=>'GEORGIA','code'=>'+995'],
            'GF'=>['name'=>'FRENCH GUIANA','code'=>'+594'],
            //'GG'=>['name'=>'GUERNSEY','code'=>'+44'],
            'GH'=>['name'=>'GHANA','code'=>'+233'],
            'GI'=>['name'=>'GIBRALTAR','code'=>'+350'],
            'GL'=>['name'=>'GREENLAND','code'=>'+299'],
            'GM'=>['name'=>'GAMBIA','code'=>'+220'],
            'GN'=>['name'=>'GUINEA','code'=>'+224'],
            'GP'=>['name'=>'GUADELOUPE','code'=>'+590'],
            'GQ'=>['name'=>'EQUATORIAL GUINEA','code'=>'+240'],
            'GR'=>['name'=>'GREECE','code'=>'+30'],
            'GS'=>['name'=>'SOUTH GEORGIA & SOUTH SANDWICH ISLANDS','code'=>'+500'],
            'GT'=>['name'=>'GUATEMALA','code'=>'+502'],
            'GU'=>['name'=>'GUAM','code'=>'+1671'],
            'GW'=>['name'=>'GUINEA-BISSAU','code'=>'+245'],
            'GY'=>['name'=>'GUYANA','code'=>'+592'],
            'HK'=>['name'=>'HONG KONG','code'=>'+852'],
            //'HM'=>['name'=>'HEARD & MCDONALD ISLANDS','code'=>'+672'],
            'HN'=>['name'=>'HONDURAS','code'=>'+504'],
            'HR'=>['name'=>'CROATIA','code'=>'+385'],
            'HT'=>['name'=>'HAITI','code'=>'+509'],
            'HU'=>['name'=>'HUNGARY','code'=>'+36'],
            'ID'=>['name'=>'INDONESIA','code'=>'+62'],
            'IE'=>['name'=>'IRELAND','code'=>'+353'],
            'IL'=>['name'=>'ISRAEL','code'=>'+972'],
            'IM'=>['name'=>'ISLE OF MAN','code'=>'+44'],
            'IN'=>['name'=>'INDIA','code'=>'+91'],
            'IO'=>['name'=>'BRITISH INDIAN OCEAN TERRITORY','code'=>'+246'],
            'IQ'=>['name'=>'IRAQ','code'=>'+964'],
            'IR'=>['name'=>'IRAN, ISLAMIC REPUBLIC OF','code'=>'+98'],
            'IS'=>['name'=>'ICELAND','code'=>'+354'],
            'IT'=>['name'=>'ITALY','code'=>'+39'],
            'JE'=>['name'=>'JERSEY','code'=>'+44'],
            'JM'=>['name'=>'JAMAICA','code'=>'+1876'],
            'JO'=>['name'=>'JORDAN','code'=>'+962'],
            'JP'=>['name'=>'JAPAN','code'=>'+81'],
            'KE'=>['name'=>'KENYA','code'=>'+254'],
            'KG'=>['name'=>'KYRGYZSTAN','code'=>'+996'],
            'KH'=>['name'=>'CAMBODIA','code'=>'+855'],
            'KI'=>['name'=>'KIRIBATI','code'=>'+686'],
            'KM'=>['name'=>'COMOROS','code'=>'+269'],
            'KN'=>['name'=>'SAINT KITTS AND NEVIS','code'=>'+1869'],
            'KP'=>['name'=>'KOREA DEMOCRATIC PEOPLES REPUBLIC OF','code'=>'+850'],
            'KR'=>['name'=>'KOREA REPUBLIC OF','code'=>'+82'],
            'KW'=>['name'=>'KUWAIT','code'=>'+965'],
            'KY'=>['name'=>'CAYMAN ISLANDS','code'=>'+1345'],
            'KZ'=>['name'=>'KAZAKSTAN','code'=>'+7'],
            'LA'=>['name'=>'LAO PEOPLES DEMOCRATIC REPUBLIC','code'=>'+856'],
            'LB'=>['name'=>'LEBANON','code'=>'+961'],
            'LC'=>['name'=>'SAINT LUCIA','code'=>'+1758'],
            'LI'=>['name'=>'LIECHTENSTEIN','code'=>'+423'],
            'LK'=>['name'=>'SRI LANKA','code'=>'+94'],
            'LR'=>['name'=>'LIBERIA','code'=>'+231'],
            'LS'=>['name'=>'LESOTHO','code'=>'+266'],
            'LT'=>['name'=>'LITHUANIA','code'=>'+370'],
            'LU'=>['name'=>'LUXEMBOURG','code'=>'+352'],
            'LV'=>['name'=>'LATVIA','code'=>'+371'],
            'LY'=>['name'=>'LIBYAN ARAB JAMAHIRIYA','code'=>'+218'],
            'MA'=>['name'=>'MOROCCO','code'=>'+212'],
            'MC'=>['name'=>'MONACO','code'=>'+377'],
            'MD'=>['name'=>'MOLDOVA, REPUBLIC OF','code'=>'+373'],
            'ME'=>['name'=>'MONTENEGRO','code'=>'+382'],
            'MF'=>['name'=>'SAINT MARTIN','code'=>'+1599'],
            'MG'=>['name'=>'MADAGASCAR','code'=>'+261'],
            'MH'=>['name'=>'MARSHALL ISLANDS','code'=>'+692'],
            'MK'=>['name'=>'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF','code'=>'+389'],
            'ML'=>['name'=>'MALI','code'=>'+223'],
            'MM'=>['name'=>'MYANMAR','code'=>'+95'],
            'MN'=>['name'=>'MONGOLIA','code'=>'+976'],
            'MO'=>['name'=>'MACAU','code'=>'+853'],
            'MP'=>['name'=>'NORTHERN MARIANA ISLANDS','code'=>'+1670'],
            'MQ'=>['name'=>'MARTINIQUE','code'=>'+596'],
            'MR'=>['name'=>'MAURITANIA','code'=>'+222'],
            'MS'=>['name'=>'MONTSERRAT','code'=>'+1664'],
            'MT'=>['name'=>'MALTA','code'=>'+356'],
            'MU'=>['name'=>'MAURITIUS','code'=>'+230'],
            'MV'=>['name'=>'MALDIVES','code'=>'+960'],
            'MW'=>['name'=>'MALAWI','code'=>'+265'],
            'MX'=>['name'=>'MEXICO','code'=>'+52'],
            'MY'=>['name'=>'MALAYSIA','code'=>'+60'],
            'MZ'=>['name'=>'MOZAMBIQUE','code'=>'+258'],
            'NA'=>['name'=>'NAMIBIA','code'=>'+264'],
            'NC'=>['name'=>'NEW CALEDONIA','code'=>'+687'],
            'NE'=>['name'=>'NIGER','code'=>'+227'],
            //'NF'=>['name'=>'NORFOLK ISLAND','code'=>'+672'],
            'NG'=>['name'=>'NIGERIA','code'=>'+234'],
            'NI'=>['name'=>'NICARAGUA','code'=>'+505'],
            'NL'=>['name'=>'NETHERLANDS','code'=>'+31'],
            'NO'=>['name'=>'NORWAY','code'=>'+47'],
            'NP'=>['name'=>'NEPAL','code'=>'+977'],
            'NR'=>['name'=>'NAURU','code'=>'+674'],
            'NU'=>['name'=>'NIUE','code'=>'+683'],
            'NZ'=>['name'=>'NEW ZEALAND','code'=>'+64'],
            'OM'=>['name'=>'OMAN','code'=>'+968'],
            'PA'=>['name'=>'PANAMA','code'=>'+507'],
            'PE'=>['name'=>'PERU','code'=>'+51'],
            'PF'=>['name'=>'FRENCH POLYNESIA','code'=>'+689'],
            'PG'=>['name'=>'PAPUA NEW GUINEA','code'=>'+675'],
            'PH'=>['name'=>'PHILIPPINES','code'=>'+63'],
            'PK'=>['name'=>'PAKISTAN','code'=>'+92'],
            'PL'=>['name'=>'POLAND','code'=>'+48'],
            'PM'=>['name'=>'SAINT PIERRE AND MIQUELON','code'=>'+508'],
            'PN'=>['name'=>'PITCAIRN','code'=>'+870'],
            //'PR'=>['name'=>'PUERTO RICO','code'=>'+1'],
            'PS'=>['name'=>'PALESTINE','code'=>'+970'],
            'PT'=>['name'=>'PORTUGAL','code'=>'+351'],
            'PW'=>['name'=>'PALAU','code'=>'+680'],
            'PY'=>['name'=>'PARAGUAY','code'=>'+595'],
            'QA'=>['name'=>'QATAR','code'=>'+974'],
            //'RE'=>['name'=>'RÉUNION','code'=>'+262'],
            'RO'=>['name'=>'ROMANIA','code'=>'+40'],
            'RS'=>['name'=>'SERBIA','code'=>'+381'],
            //'RU'=>['name'=>'RUSSIAN FEDERATION','code'=>'+7'],
            'RW'=>['name'=>'RWANDA','code'=>'+250'],
            'SA'=>['name'=>'SAUDI ARABIA','code'=>'+966'],
            'SB'=>['name'=>'SOLOMON ISLANDS','code'=>'+677'],
            'SC'=>['name'=>'SEYCHELLES','code'=>'+248'],
            'SD'=>['name'=>'SUDAN','code'=>'+249'],
            'SE'=>['name'=>'SWEDEN','code'=>'+46'],
            'SG'=>['name'=>'SINGAPORE','code'=>'+65'],
            'SH'=>['name'=>'SAINT HELENA','code'=>'+290'],
            'SI'=>['name'=>'SLOVENIA','code'=>'+386'],
            'SJ'=>['name'=>'SVALBARD & JAN MAYEN','code'=>'+47'],
            'SK'=>['name'=>'SLOVAKIA','code'=>'+421'],
            'SL'=>['name'=>'SIERRA LEONE','code'=>'+232'],
            'SM'=>['name'=>'SAN MARINO','code'=>'+378'],
            'SN'=>['name'=>'SENEGAL','code'=>'+221'],
            'SO'=>['name'=>'SOMALIA','code'=>'+252'],
            'SR'=>['name'=>'SURINAME','code'=>'+597'],
            'SS'=>['name'=>'SOUTH SUDAN','code'=>'+211'],
            'ST'=>['name'=>'SAO TOME AND PRINCIPE','code'=>'+239'],
            'SV'=>['name'=>'EL SALVADOR','code'=>'+503'],
            'SX'=>['name'=>'SINT MAARTEN','code'=>'+1721'],
            'SY'=>['name'=>'SYRIAN ARAB REPUBLIC','code'=>'+963'],
            'SZ'=>['name'=>'SWAZILAND','code'=>'+268'],
            'TC'=>['name'=>'TURKS AND CAICOS ISLANDS','code'=>'+1649'],
            'TD'=>['name'=>'CHAD','code'=>'+235'],
            'TF'=>['name'=>'FRENCH SOUTHERN TERRITORIES ','code'=>'+262'],
            'TG'=>['name'=>'TOGO','code'=>'+228'],
            'TH'=>['name'=>'THAILAND','code'=>'+66'],
            'TJ'=>['name'=>'TAJIKISTAN','code'=>'+992'],
            'TK'=>['name'=>'TOKELAU','code'=>'+690'],
            'TL'=>['name'=>'TIMOR-LESTE','code'=>'+670'],
            'TM'=>['name'=>'TURKMENISTAN','code'=>'+993'],
            'TN'=>['name'=>'TUNISIA','code'=>'+216'],
            'TO'=>['name'=>'TONGA','code'=>'+676'],
            'TR'=>['name'=>'TURKEY','code'=>'+90'],
            'TT'=>['name'=>'TRINIDAD AND TOBAGO','code'=>'+1868'],
            'TV'=>['name'=>'TUVALU','code'=>'+688'],
            'TW'=>['name'=>'TAIWAN, PROVINCE OF CHINA','code'=>'+886'],
            'TZ'=>['name'=>'TANZANIA, UNITED REPUBLIC OF','code'=>'+255'],
            'UA'=>['name'=>'UKRAINE','code'=>'+380'],
            'UG'=>['name'=>'UGANDA','code'=>'+256'],
            //'UM'=>['name'=>'U.S. OUTLYING ISLANDS','code'=>'+1'],
            'US'=>['name'=>'UNITED STATES','code'=>'+1'],
            'UY'=>['name'=>'URUGUAY','code'=>'+598'],
            'UZ'=>['name'=>'UZBEKISTAN','code'=>'+998'],
            'VA'=>['name'=>'HOLY SEE (VATICAN CITY STATE)','code'=>'+39'],
            'VC'=>['name'=>'SAINT VINCENT AND THE GRENADINES','code'=>'+1784'],
            'VE'=>['name'=>'VENEZUELA','code'=>'+58'],
            'VG'=>['name'=>'VIRGIN ISLANDS, BRITISH','code'=>'+1284'],
            'VI'=>['name'=>'VIRGIN ISLANDS, U.S.','code'=>'+1340'],
            'VN'=>['name'=>'VIETNAM','code'=>'+84'],
            'VU'=>['name'=>'VANUATU','code'=>'+678'],
            'WF'=>['name'=>'WALLIS AND FUTUNA','code'=>'+681'],
            'WS'=>['name'=>'SAMOA','code'=>'+685'],
            'XK'=>['name'=>'KOSOVO','code'=>'+383'],
            'YE'=>['name'=>'YEMEN','code'=>'+967'],
            //'YT'=>['name'=>'MAYOTTE','code'=>'+262'],
            'ZA'=>['name'=>'SOUTH AFRICA','code'=>'+27'],
            'ZM'=>['name'=>'ZAMBIA','code'=>'+260'],
            'ZW'=>['name'=>'ZIMBABWE','code'=>'+263'],
        ];   
    }
}
