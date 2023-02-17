<?php
namespace OCA\AaoChat\Service;

use Exception;

use OCP\L10N\IFactory;
use OCP\IConfig;
use OC\Authentication\TwoFactorAuth\Manager;
use OCP\IURLGenerator;
use OCA\AaoChat\AppInfo\Application;
use OCA\AaoChat\Service\ConfigProxy;

use OCP\IUserManager;
use OCP\IUser;
use OCP\IUserSession;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\Mail\IMailer;
use OCP\IDBConnection;
use OCP\Security\ISecureRandom;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\Node;
use OCP\Share\IManager;
use OCP\Share\IShare;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCA\AaoChat\Service\AaochatService;

use OCA\AaoChat\Db\Apiauth;
use OCA\AaoChat\Db\ApiauthMapper;


class ApiauthService {

    /** @var IURLGenerator */
    private $urlGenerator;
    /** @var ISession */
    private $session;
    /** @var IDBConnection */
    protected $db;

    protected $headerAuthBase;

    /** @var IUserSession */
    private $userSession;

    /** @var IUserManager */
    protected $userManager;

    /** @var \OCP\Files\Folder */
    private $userFolder;

    private $rootFolder;

    private $rootGroupFolder;

    private $l;

    protected $apiauth_mapper;

    /** @var IManager */
    private $shareManager;

    /**
     * @var ConfigProxy
     */
    private $config;
    private $aaochatService;

    private $apiKey = 'nKs63gTh8vE52uVl';


    public function __construct(IUserSession $userSession, ConfigProxy $config, IUserManager $userManager, ApiauthMapper $apiauthMapper, IManager $shareManager, IRootFolder $rootFolder, AaochatService $aaochatService){
        $this->userSession  = $userSession;

        $url_generator   = \OC::$server->getURLGenerator();
        $host_url       =  $url_generator->getAbsoluteURL('');

        $this->config = $config;
        $this->userManager = $userManager;
        $this->apiauth_mapper = $apiauthMapper;

        $this->api_base_url =  $host_url;
        $this->l = \OC::$server->getL10N('aaochat');

        $this->shareManager = $shareManager;

        $this->rootFolder = $rootFolder;
        $this->aaochatService = $aaochatService;
    }
   

    public function generateAuthToken($userName, $password) {
        return md5($userName.' '.$password);
    }

    public function setHeaderAuthBase($headerAuthBase) {
        $this->headerAuthBase = $headerAuthBase;
    }

    public function getHeaderAuthBase() {
        return $this->headerAuthBase;
    }

    public function setRootGroupFolder($rootGroupFolder) {
        $this->rootGroupFolder = $rootGroupFolder;
    }

    public function getRootGroupFolder() {
        return $this->rootGroupFolder;
    }

    public function getAaochatLogPath() {
        return $this->aaochatService->getAaochatLogPath();
    }

    public function isAaochatApiLogEnable() {
        return $this->aaochatService->isAaochatApiLogEnable();
    }

    public function curlPost($type=0, $header=null, $url=null, $data=null) {
        $apiUrl = $this->api_base_url.$url;
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $apiUrl);
        
        if($type ==1)
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PROPFIND');
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        if($data!=null)
        {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /**
     * get user meta using nextcloud core API
     */
    private function getUserMata($userName, $authBase) {
        $header = array(
            "Accept:application/json",
            "Authorization: Basic ".$authBase,
            "OCS-APIRequest: true"
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->api_base_url.'ocs/v1.php/cloud/users/'.$userName);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $getoutput = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($getoutput,true);

        return $result;
    }

    public function validateApiKey($apiKey) {
        $response = $this->prepareResponse('failed',401,$this->l->t('Unauthorized'));
        
        /*$wordAddIn = $this->config->getAppValue('aaochat-auth', '1');
        if($wordAddIn == 0) {
            $response = $this->prepareResponse('failed',401,$this->l->t('Aaochat Auth API is not enabled. Kindly contact administrator.'));
        } else {*/
            if($this->apiKey==$apiKey) {
                $response = $this->prepareResponse('success',200,$this->l->t('You have validated your API key successfully.'));
            } else {
                $response = $this->prepareResponse('failed',401,$this->l->t('Your API key is invalid.'));
            }
        //}
        return $response;
    }

    public function validateApiToken($apiToken) {
        $response = $this->prepareResponse('failed',401,$this->l->t('Unauthorized'));
        if($this->apiauth_mapper->isTokenExists($apiToken)) {
            $aipauthData = $this->apiauth_mapper->getApiAuthByToken($apiToken);
            if(!empty($aipauthData)) {
                    $user = $this->userManager->get($aipauthData->getUserId());
                    if ($user instanceof IUser) {
                        $this->userSession->setUser($user);
                    }                    

                    $userData = array();
                    $userData['display_name'] = $user->getDisplayName();
                    $userData['token'] = $apiToken;
                    $userData['user_name'] = $aipauthData->getUserId();
                    $userData['user_email'] = $user->getEMailAddress();
                    $userData['avatar_image'] = $user->getAvatarImage(100);
                    $response = $this->prepareResponse('success',200,'Valid',$userData);
            } else {
                $response = $this->prepareResponse('failed',404,$this->l->t('Your API token is invalid.'));
            }
            
        } else {
            $response = $this->prepareResponse('failed',401,$this->l->t('Your token is expired or invalid. Please login again.'));
        }
        return $response;
    }

    /**
     * Authenticate user using usermeta API and generate api auth token. Sending api auth token in response for other api call validation.
     * Currently not using
    */
    public function authenticate($userName, $password) {
        $response = $this->prepareResponse('failed',401,$this->l->t('Unauthorized'));

        //STATUS_UNAUTHORIZED
        $user = $this->userSession->getUser();
        if (!$user instanceof IUser) {
            $userEmail = '';
            $avatarImage = '';
            $user = $this->userManager->get($userName);
            if ($user instanceof IUser) {
                $userEmail = $user->getEMailAddress();
                $avatarImage = $user->getAvatarImage(100);
            }

            $authBase = base64_encode($userName.':'.$password);
            $result = $this->getUserMata($userName, $authBase);

            if(isset($result['ocs']['meta']['status']) && $result['ocs']['meta']['status']!='failure')
            {
                $authBaseEmail = base64_encode($userName.':'.$userEmail);
                $userToken = $this->generateAuthToken($userName, $userEmail);
                if($this->apiauth_mapper->isApiUserExists($userName)) {
                    $res= $this->apiauth_mapper->updateApiAuth($userName, $authBase, $userToken);
                } else {
                    
                    $apiauth = new Apiauth();
                    $apiauth->setUserId($userName);
                    $apiauth->setAuthBase($authBase);
                    $apiauth->setAuthToken($userToken);
                    $apiauth->setTimestamp(time());
                    $res = $this->apiauth_mapper->insert($apiauth);
                }
                $userData = array();
                $userData['display_name'] = $result['ocs']['data']['displayname'];
                $userData['token'] = $userToken;
                $userData['user_name'] = $userName;
                $userData['user_email'] = $userEmail;
                $userData['avatar_image'] = $avatarImage;
                
                //Need token at root lavel
                $response = array('status' => 'success','error' => 0, 'code' => 200, 'message' => 'Success', 'data' => $userData);

            }
            else
            {
                if(isset($result['ocs'])) {
                    $response = $this->prepareResponse('failed',200,$result['ocs']['meta']['message']);
                } else {
                    $response = $this->prepareResponse('failed',200,'Login failed.');
                }
            }
        }

        return $response;
    }
    
    /**
     *  Get User Data using API
     */
    public function getuser($userName) {
        $response = $this->prepareResponse('failed',401,$this->l->t('Username should not be blank.'));

        if (!empty($userName)) {
            $userEmail = '';
            $user = $this->userManager->get($userName);
            if ($user instanceof IUser) {

                $userId = $user->getUID();
                $userEmail = $user->getEMailAddress();
                $userDisplayName = $user->getDisplayName();
                $avatarImage = $user->getAvatarImage(100);
             
                $userData = array();
                $userData['display_name'] = $userDisplayName;
                $userData['user_name'] = $userName;
                $userData['user_email'] = $userEmail;
                $userData['avatar_image'] = $avatarImage;
                //Need token at root lavel
                $response = array('status' => 'success','error' => 0, 'code' => 200, 'message' => 'Success', 'data' => $userData);

            }
            else
            {
                $response = $this->prepareResponse('failed',200,'No matching user found.');
            }
        }

        return $response;
    }

    public function prepareResponse($status, $response_code, $error_message, $data=array()) {
        if($status == 'success') {
            if(!empty($data)) {
                $response = array('status' => $status,'error' => 0, 'code' => $response_code, 'message' => $error_message, 'data'=>$data);
            } else {
                $response = array('status' => $status,'error' => 0, 'code' => $response_code, 'message' => $error_message);
            }
            
        } else {
            $response = array('status' => $status,'error' => 1,'code' => $response_code,'message' => $error_message);
        }
        return $response;
    }

    /**
     *  Get Share Data using API
     */
    public function getObjectShareData($objectId) {
        $response = $this->prepareResponse('failed',401,$this->l->t('Object id should not be empty.'));

        if (!empty($objectId)) {
            
            //Mount current user file system to make it working for API
            $currentUser = $this->userSession->getUser();
            $currentUserId = $currentUser->getUID();
            \OC\Files\Filesystem::initMountPoints($currentUserId);

            $sharedFilePath = \OC\Files\Filesystem::getPath($objectId);
            $info = \OC\Files\Filesystem::getFileInfo($sharedFilePath);
            $fileData     = $info->getData();
            
            $this->userFolder = $this->rootFolder->getUserFolder($currentUserId);
            $sharedNode = $this->userFolder->get($sharedFilePath);
            $sharedData = $this->shareManager->getAccessList($sharedNode,false);
            $fileName = $sharedNode->getName();
            $fileType = \OC\Files\Filesystem::filetype($sharedFilePath);
            $fileOwner = \OC\Files\Filesystem::getOwner($sharedFilePath);
            

            $shareInfo = array();
            $shareInfo['objectId'] = $objectId;
            $shareInfo['objectName'] = trim($fileName,"/");
            $shareInfo['objectType'] = $fileType;
            $shareInfo['filePath'] = $sharedFilePath;
            $shareInfo['creator'] = $fileOwner;

            $shareUserInfo = array();
            $dir_path = $sharedFilePath;
            //$userid = \OC_User::getUser();
            //$userhome = \OC_User::getHome($userid);
            //$shareInfo['userid'] = $userid;
            if (\OC\Files\Filesystem::file_exists($dir_path)) {
                $sharedData = $this->shareManager->getAccessList($sharedNode,false);
                //$shareInfo['sharedData'] = $sharedData;
                if(isset($sharedData['users']) && !empty($sharedData['users'])) {
                    foreach ($sharedData['users'] as $key => $sharedUserInfo) {
                        $shareUserInfo[$sharedUserInfo]['id'] = $sharedUserInfo;
                        $shareUserInfo[$sharedUserInfo]['permissions'] = 1;
                    }
                }
            }
            
            $shareInfo['shareInfo'] = $shareUserInfo;

            $response = $this->aaochatService->manageGroupToAaochat($shareInfo);
            if(!is_array($response)) {
                $response = json_decode($response);
            }
        }
            
        return $response;
    }
    

    /**
     * Currently not in use
     */
    private function getRequestStatus($code) {
        $status = array(  
            100 => 'Continue',  
            101 => 'Switching Protocols',  
            200 => 'OK',
            201 => 'Created',  
            202 => 'Accepted',  
            203 => 'Non-Authoritative Information',  
            204 => 'No Content',  
            205 => 'Reset Content',  
            206 => 'Partial Content',  
            300 => 'Multiple Choices',  
            301 => 'Moved Permanently',  
            302 => 'Found',  
            303 => 'See Other',  
            304 => 'Not Modified',  
            305 => 'Use Proxy',  
            306 => '(Unused)',  
            307 => 'Temporary Redirect',  
            400 => 'Bad Request',  
            401 => 'Unauthorized',  
            402 => 'Payment Required',  
            403 => 'Forbidden',  
            404 => 'Not Found',  
            405 => 'Method Not Allowed',  
            406 => 'Not Acceptable',  
            407 => 'Proxy Authentication Required',  
            408 => 'Request Timeout',  
            409 => 'Conflict',  
            410 => 'Gone',  
            411 => 'Length Required',  
            412 => 'Precondition Failed',  
            413 => 'Request Entity Too Large',  
            414 => 'Request-URI Too Long',  
            415 => 'Unsupported Media Type',  
            416 => 'Requested Range Not Satisfiable',  
            417 => 'Expectation Failed',  
            500 => 'Internal Server Error',  
            501 => 'Not Implemented',  
            502 => 'Bad Gateway',  
            503 => 'Service Unavailable',  
            504 => 'Gateway Timeout',  
            505 => 'HTTP Version Not Supported'
        ); 
        return ($status[$code])?$status[$code]:$status[500]; 
    }


}