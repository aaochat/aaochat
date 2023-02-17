<?php
namespace OCA\AaoChat\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use \OCP\AppFramework\Http\FeaturePolicy;
use OCP\AppFramework\Http\RedirectResponse;
use OCA\AaoChat\AppInfo\Application;
use OCP\IURLGenerator;
use OCA\AaoChat\Service\ConfigProxy;
use OCA\AaoChat\Service\AaochatService;
use OCP\IConfig;
use OCP\IUserSession;

use OCA\AaoChat\Service\ApiauthService;
//use OCA\AaoChat\Db\Apiauth;
//use OCA\AaoChat\Db\ApiauthMapper;
use OCP\Security\ICredentialsManager;
use OCP\Authentication\LoginCredentials\IStore;
use OCP\Util;

class PageController extends Controller {
	private $userId;
	private $is_license_valid = false;
	private $aaochatService;
	/** @var IUserSession */
    private $userSession;
    /** @var ICredentialsManager */
    protected $credentialsManager;
    /** @var IStore */
	private $credentialStore;

	private $apiAuthService;

	public function __construct($AppName, IRequest $request, $UserId, IConfig $config,
        IURLGenerator $urlGenerator, AaochatService $aaochatService, IUserSession $userSession, ICredentialsManager $credentialsManager, IStore $credentialStore, ApiauthService $apiAuthService){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->config = $config;
		$this->aaochatService = $aaochatService;
		$this->userSession  = $userSession;
		$this->credentialsManager = $credentialsManager;
		$this->credentialStore = $credentialStore;
		$this->apiAuthService = $apiAuthService;

		$isLicenseValid = $this->config->getAppValue(Application::APP_ID, 'aaochat_is_license_valid', '');
		if(!empty($isLicenseValid) && $isLicenseValid === 'yes') {
			$this->is_license_valid = true;
		}
		$this->l = \OC::$server->getL10N('aaochat');
	}

	private function settingPage() {
		$url_generator   = \OC::$server->getURLGenerator();
        $host_url       =  $url_generator->getAbsoluteURL('');
        $site_url =  $host_url . 'index.php';

        $settingPageUrl = $site_url.'/settings/admin/'.Application::APP_ID;
        return new RedirectResponse($settingPageUrl);  
	}

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		if($this->is_license_valid == false) {
			return $this->settingPage();
		} else {
			
			$content_api_base_url = $this->aaochatService->content_api_base_url;
			$content_api_url = $content_api_base_url.$this->aaochatService->getAaochatLicenseKey();

			$aaochat_content = file_get_contents($content_api_url);

			$nonceValue = \OC::$server->getContentSecurityPolicyNonceManager()->getNonce();
			$aaochat_content = str_replace('<script', '<script nonce="'.$nonceValue.'"', $aaochat_content); 

			$content_data = array();
			$content_data['aaochat_content'] = $aaochat_content;
			$content_data['content_api_url'] = $content_api_url;

			//Util::addStyle(Application::APP_ID, 'https://fonts.googleapis.com/css?family=Roboto:300,400,700,900');
			//Util::addStyle(Application::APP_ID, 'https://business2.aaochat.com/public/css/font-awesome/css/font-awesome.min.css?v=v27');
			//Util::addScript(Application::APP_ID, 'https://business2.aaochat.com/public/js/plugins/jquery.min.js');

			//echo "<pre>".print_r($aaochat_content,true)."</pre>";
			//exit;
			$aaochat_server_url = $this->aaochatService->getAaochatServerUrl();
			$aaochat_file_server_url = $this->aaochatService->getAaochatFileServerUrl();
			$contentApiDomain = $this->aaochatService->removeHttp($aaochat_server_url); //'business2.aaochat.com';
			$aaochat_file_server_url = $this->aaochatService->getAaochatFileServerUrl();
			$contentFileApiDomain = $this->aaochatService->removeHttp($aaochat_file_server_url); //'business2.aaochat.com';

			$response = new TemplateResponse('aaochat', 'index', ['data' => $content_data],'blank');  // templates/index.php

			//$policy = new \OCP\AppFramework\Http\EmptyContentSecurityPolicy();

			//$csp = new ContentSecurityPolicy();
			$csp = new \OCP\AppFramework\Http\EmptyContentSecurityPolicy();
			//$csp->disallowConnectDomain('self');
			//$csp->disallowFormActionDomain('self');

	        $csp->addAllowedFormActionDomain($contentApiDomain);
	        //$csp->addAllowedFrameDomain($contentApiDomain);
	        $csp->addAllowedFrameDomain('*');
	        $csp->addAllowedConnectDomain($contentApiDomain);
	        $csp->addAllowedConnectDomain('*');
	        //$csp->addAllowedConnectDomain('fonts.googleapis.com');
	        //$csp->addAllowedConnectDomain($contentFileApiDomain);
	        $csp->addAllowedFontDomain('fonts.googleapis.com');
	        $csp->addAllowedFontDomain($contentApiDomain);
	        $csp->addAllowedFontDomain('fonts.gstatic.com');
	        $csp->addAllowedMediaDomain($contentApiDomain);
	        $csp->addAllowedMediaDomain('*');
			$csp->addAllowedImageDomain($contentApiDomain);
	        $csp->addAllowedImageDomain('*');
	        $csp->addAllowedImageDomain('data:');	        
	        $csp->addAllowedImageDomain('blob:');	        
	        $csp->addAllowedStyleDomain($contentApiDomain);
	        $csp->addAllowedStyleDomain('fonts.googleapis.com');
	        $csp->addAllowedScriptDomain($contentApiDomain);
	        $csp->addAllowedScriptDomain('cdnjs.cloudflare.com');
	        $csp->addAllowedScriptDomain($contentApiDomain);
	        $csp->addAllowedChildSrcDomain($contentApiDomain);
	        $csp->addAllowedObjectDomain($contentApiDomain);
	        $csp->addAllowedConnectDomain('wss:');

			$csp->allowInlineScript(true);
			$csp->allowInlineStyle(true);
			$csp->allowEvalScript(true);

	        $response->setContentSecurityPolicy($csp);

			return $response;
		}
		
	}

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getAuthKey() {

		$currentUser = $this->userSession->getUser();
        $userId = $currentUser->getUID();

		$user_manager   = \OC::$server->getUserManager();
        $user = $user_manager->get($userId);

        $userName = $user->getUID();
        $userEmail = $user->getEMailAddress();
        $userDisplayName = $user->getDisplayName();
        $avatarImage = $user->getAvatarImage(100);

		$userData = array();
		$userData['user_name'] = $userName;
		$userData['user_email'] = $userEmail;
		$userData['display_name'] = $userDisplayName;
		$userData['avatar_image'] = $avatarImage;

		$userAuthkeyResponse = $this->aaochatService->sendUserdataToAaochat($userData);
		$userAuthkeyResponse = json_decode($userAuthkeyResponse);
		if(isset($userAuthkeyResponse->status) && $userAuthkeyResponse->status=='success') {
			$responseData = $userAuthkeyResponse->data;
            $aaochatUserToken = '"'.$responseData->auth_key.'"';

			$localStorageData = array();

			$aaochatServerUrl = $this->aaochatService->getAaochatServerUrl();
			$aaochatFileServerUrl = $this->aaochatService->getAaochatFileServerUrl();

			unset($_COOKIE['ncUserAuthKey']);
			setcookie('ncUserAuthKey', $aaochatUserToken, time() + 3600000*24*7, '/');

			unset($_COOKIE['aaochatServerUrl']);
			setcookie('aaochatServerUrl', $aaochatServerUrl, time() + 3600000*24*7, '/');

			unset($_COOKIE['aaochatFileServerUrl']);
			setcookie('aaochatFileServerUrl', $aaochatFileServerUrl, time() + 3600000*24*7, '/');

			$localStorageData['ncUserAuthKey'] = $aaochatUserToken;
			$localStorageData['aaochatServerUrl'] = $aaochatServerUrl;
			$localStorageData['aaochatFileServerUrl'] = $aaochatFileServerUrl;

			$userAuthkeyResponse->data->localStorageData = $localStorageData;
		}

		return new JSONResponse($userAuthkeyResponse, Http::STATUS_OK);
	}

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function setting() {
		//Testing purpose
		echo 'checking...<br/>';


		//$user = $event->getUser();

		$currentUser = $this->userSession->getUser();
        $userId = $currentUser->getUID();

		$user_manager   = \OC::$server->getUserManager();
        $user = $user_manager->get($userId);

        $userId = $user->getUID();
        $userEmail = $user->getEMailAddress();
        $userDisplayName = $user->getDisplayName();
        $avatarImage = $user->getAvatarImage(100);
        //$password = $user->

        $userData = array();
        $userData['userId'] = $userId;
        $userData['userEmail'] = $userEmail;
        $userData['userDisplayName'] = $userDisplayName;
        //$userData['avatarImage'] = $avatarImage;

        $userToken = $this->apiAuthService->generateAuthToken($userId, $userEmail);

        //$credentials = $this->credentialsManager->retrieve($userId, LoginCredentials::CREDENTIALS_IDENTIFIER);
        $credentials = $this->credentialStore->getLoginCredentials();
        $loginName = $credentials->getLoginName();
        $password = $credentials->getPassword();

        $userData['credentials'] = $credentials;
        $userData['userToken'] = $userToken;

		echo "Response:<pre>".print_r($userData,true)."</pre>";
		exit;

		/*
		$licenseKey = '8eb2b2f2ac3ad4ba67a68ffaf12191f2'; //$this->config->getAppValue(Application::APP_ID, 'aaochat_licence_key', '');
		if(!empty($licenseKey)) {
			$response = $this->aaochatService->validateLicenseKey($licenseKey);

			echo "Response:<pre>".print_r($response,true)."</pre>";
			exit;
		}*/


		return;
		/*
		$url_generator   = \OC::$server->getURLGenerator();
        $host_url       =  $url_generator->getAbsoluteURL('');
        $site_url =  $host_url . 'index.php';

        $settingPageUrl = $site_url.'/settings/admin/'.Application::APP_ID;
        return new RedirectResponse($settingPageUrl);
        */ 
	}

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function doEcho() {
		//Testing purpose
		echo 'echo...';
		//$groupData = $this->aaochatService->getAaochatGroup(33);

		$requestData = array();
		$requestData['channel_id'] = '6343c397fb15d74513f4ba82';
		$requestData['user_id'] = 'parth';
		$requestData['message_id'] = '';
		$groupData = $this->aaochatService->getConversation($requestData);

		echo "<pre>".print_r($groupData,true)."</pre>";

		exit;
	}

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getGroup($fileid) {
        $response = $this->apiAuthService->prepareResponse('failed',401,$this->l->t('Invalid object id'));
        if(!empty($fileid)) {
        	$response = $this->aaochatService->getAaochatGroup($fileid);
        }
        return new JSONResponse($response, Http::STATUS_OK);
    }

    /**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function getSharedInfo($fileid) {
        $response = $this->apiAuthService->prepareResponse('failed',401,$this->l->t('Invalid object id'));
        if(!empty($fileid)) {
        	$response = $this->apiAuthService->getObjectShareData($fileid);
        }
        return new JSONResponse($response, Http::STATUS_OK);
    }

}
