<?php
namespace OCA\AaoChat\Controller;

use Exception;
use OC\Files\Node\Node;
use OC\Authentication\TwoFactorAuth\Manager;
use OC\Files\Filesystem;
use OC\Files\Mount\MoveableMount;
use OC\Files\View;

use OCA\Viewer\Event\LoadViewer;
use OCP\IConfig;
use OCP\ILogger;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;
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
use OCP\User;
use OCA\AaoChat\Service\ApiauthService;




 class ApiController extends Controller {
    /** @var IURLGenerator */
    private $urlGenerator;
    /** @var ISession */
    private $session;
    /** @var IDBConnection */
    protected $db;   

    private $userId;

    private $img_url;
    private $site_url;
    protected $uploaderService;

    /** @var IUserSession */
    private $userSession;

    private $l;
    private $postData;
    private $getData;
    private $postFiles;
    private $apiauth;

    /** @var string */
    private $token;

    
    public function __construct(
    string $AppName, 
    IRequest $request,
    IURLGenerator $urlGenerator,
    IDBConnection $db,
    ISession $session,
    IUserSession $userSession,
    ApiauthService $apiauth,
    $UserId){

        parent::__construct($AppName, $request);
        $this->db = $db;        
        $this->session = $session;
        $this->userId = $UserId;
        $this->userSession  = $userSession;
        $this->request = $request;
        $this->apiauth = $apiauth;

        $url_generator   = \OC::$server->getURLGenerator();
        $host_url       =  $url_generator->getAbsoluteURL('');
        $this->img_url  =  $host_url;
        $this->site_url =  $host_url . 'index.php';
        $this->l = \OC::$server->getL10N('aaochat');
        
        $this->init();
    }

    private function init() {
        $this->requestHeader = apache_request_headers();

        $requestURI = $_SERVER['REQUEST_URI'];
        $requestURIArr = explode('/', $requestURI);
        $requestMethod = $requestURIArr[count($requestURIArr)-1];
        $requestURIDataArr = explode('?', $requestURI);

        //echo "<pre>".print_r($this->requestHeader,true)."</pre>";


        $apiKey = 'nKs63gTh8vE52uVl';//@$this->requestHeader['apiKey'];
        if(!empty($apiKey)) {
            $response = $this->apiauth->validateApiKey($apiKey);
            if($response['error'] != 1) {
                if($requestMethod != 'authenticate') {
                    $apiToken = $this->requestHeader['apiToken'];
                    if(!empty($apiToken)) {
                        $response = $this->apiauth->validateApiToken($apiToken);
                    } else {
                        $response = $this->apiauth->prepareResponse('failed',401,$this->l->t('Invalid API token'));
                    }
                }
            }
        } else {
            $response = $this->apiauth->prepareResponse('failed',401,$this->l->t('Invalid API key'));
        }

        if($response['error'] == 1) {
            echo json_encode($response);
            exit;           
        }

        $contentPosition = false;
        if(isset($this->requestHeader['Content-Type']) && !empty($this->requestHeader['Content-Type'])) {
            $contentPosition = strpos($this->requestHeader['Content-Type'], 'multipart/form-data');
        }        

        if($contentPosition !== false) {
            $this->postData = $_POST;
            $this->postFiles = $_FILES;
        } else {
            $json = file_get_contents('php://input');
            $this->postData = json_decode($json,true);
        }

        if(isset($requestURIDataArr[1]) && !empty($requestURIDataArr[1])) {
            $getData = array();
            $requestURIParametersArr = explode('&', $requestURIDataArr[1]);
            if(count($requestURIParametersArr) > 0) {
                foreach($requestURIParametersArr as $requestURIParameters) {
                    if(!empty($requestURIParameters)) {
                        $requestURIParameter = explode('=', $requestURIParameters);
                        $getData[$requestURIParameter[0]] = $requestURIParameter[1];
                    }
                }
                $this->getData = $getData;
            }
        }
       
        if($this->apiauth->isAaochatApiLogEnable()) {
            $requestData = array();
            $requestData['request'] = $this->request;
            $requestData['requestURIArr'] = $requestURIArr;
            $requestData['postData'] = $this->postData;
            $requestData['getData'] = $this->getData;
            
            $aaochat_log_dir = $this->apiauth->getAaochatLogPath();
            $requestData = json_encode($requestData);
            $myfile = file_put_contents($aaochat_log_dir.'request_data.txt', $requestData.PHP_EOL , FILE_APPEND | LOCK_EX);
        }
    }

    /**
    * @NoCSRFRequired
    * @UseSession
    * @PublicPage
    * 
    */
    public function index(): JSONResponse {
        $response = $this->apiauth->prepareResponse('failed',401,$this->l->t('Unauthorized'));
        $user = $this->userSession->getUser();
        if (!$user instanceof IUser) {

            $response = $this->apiauth->prepareResponse('sucess',200,$this->l->t('Works'));
        }
        return new JSONResponse($response, Http::STATUS_OK);
    }

    /**
    * @NoCSRFRequired
    * @UseSession
    * @PublicPage
    * 
    */
    public function tokenVerify(): JSONResponse {
        $response = $this->apiauth->prepareResponse('failed',401,$this->l->t('Unauthorized'));
        $apiToken = $this->requestHeader['apiToken'];
        if(!empty($apiToken)) {
            $response = $this->apiauth->validateApiToken($apiToken);
        } else {
            $response = $this->apiauth->prepareResponse('failed',401,$this->l->t('Invalid API token'));
        }
        return new JSONResponse($response, Http::STATUS_OK);
    }

    /**
    * @NoCSRFRequired
    * @UseSession
    * @PublicPage
    */
    public function authenticate(): JSONResponse {
        $response = $this->apiauth->prepareResponse('failed',401,$this->l->t('Unauthorized'));

        //STATUS_UNAUTHORIZED
        $user = $this->userSession->getUser();
        if (!$user instanceof IUser) {
            $userName = $this->postData['user'];
            $password = $this->postData['password'];

            $response = $this->apiauth->authenticate($userName, $password);
        }

        return new JSONResponse($response, Http::STATUS_OK);
    }

    /**
    * @NoCSRFRequired
    * @UseSession
    * @PublicPage
    */
    public function getuser(): JSONResponse {
        $response = $this->apiauth->prepareResponse('failed',401,$this->l->t('Unauthorized'));

        //STATUS_UNAUTHORIZED
        $user = $this->userSession->getUser();
        if (!$user instanceof IUser) {
            $userName = $this->postData['user_name'];

            $response = $this->apiauth->getuser($userName);
        }

        return new JSONResponse($response, Http::STATUS_OK);
    }

    /**
    * @NoCSRFRequired
    * @UseSession
    * @PublicPage
    */
    public function getsharedata(): JSONResponse {
        $response = $this->apiauth->prepareResponse('failed',401,$this->l->t('Unauthorized'));
        $objectId = $this->getData['fileid'];

        if(!empty($objectId)) {
            $response = $this->apiauth->getObjectShareData($objectId);
        } else {
            $response = $this->apiauth->prepareResponse('failed',401,$this->l->t('Object id should not be empty.'));
        }
        
        return new JSONResponse($response, Http::STATUS_OK);
    }

    /**
    * @NoCSRFRequired
    * @UseSession
    * @PublicPage
    */
    public function getfiledata(): JSONResponse {
        $response = $this->apiauth->prepareResponse('failed',401,$this->l->t('Unauthorized'));

        
        return new JSONResponse($response, Http::STATUS_OK);
    }


 }
