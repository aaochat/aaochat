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

        if(strpos($url, 'http') === false) {
            $apiUrl = $this->api_base_url.$url;
        } else {
            $apiUrl = $url;
        }
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

    public function curlGet($requestUrl, $header=null) {
        if(strpos($requestUrl, 'http') === false) {
            $apiUrl = $this->api_base_url.$requestUrl;
        } else {
            $apiUrl = $requestUrl;
        }
        
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

    public function updateAaochatLeadData($response) {
        if(!empty($response)) {
            $responseData = $response['data'];
            $id = $responseData['_id'];
            $name = $responseData['name'];
            $email = $responseData['email'];
            $phoneCountryCode = $responseData['countryCode'];
            $phoneNo = $responseData['phoneNo'];
            $country = $responseData['country'];
            $organization = $responseData['organization'];
            $organizationAddress = $responseData['companyAddress'];
            $organizationSiteURL = $responseData['siteUrl'];
            $status = $responseData['status'];

            $isLeadCreated = 'no';
            if(!empty($id)) {
                $isLeadCreated = 'yes';
            }

            if($isLeadCreated == 'yes') {
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_id', $id);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_name', $name);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_email', $email);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_phone_contry_code', $phoneCountryCode);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_phone', $phoneNo);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_country', $country);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_organization', $organization);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_organization_address', $organizationAddress);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_organization_siteurl', $organizationSiteURL);
                $this->config->setAppValue(Application::APP_ID, 'aaochat_lead_status', $status);
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
            $logData['headers'] = $header;
            $logData['data'] = $data;
            $logData['apiResponse'] = json_decode($apiResponse);
            $logData = json_encode($logData);
            $myfile = file_put_contents($this->aaochat_log_dir.'activate_licencekey_aaochat.txt', $logData.PHP_EOL , FILE_APPEND | LOCK_EX);
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
            $logData['headers'] = $header;
            $logData['data'] = $data;
            $logData['apiResponse'] = json_decode($apiResponse);
            $logData = json_encode($logData);
            $myfile = file_put_contents($this->aaochat_log_dir.'surrender_licencekey_aaochat.txt', $logData.PHP_EOL , FILE_APPEND | LOCK_EX);
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

            $this->init();
        }
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
}
