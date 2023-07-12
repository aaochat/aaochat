<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Aao Business Chat <info@aaochat.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\AaoChat\AppInfo;

use OCP\App\ManagerEvent;
use OCA\AaoChat\Capabilities;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

use OCP\AppFramework\App;
use OCA\AaoChat\Actions\UserManagement;
use OCA\AaoChat\Actions\Files;
use OCP\Log\Audit\CriticalActionPerformedEvent;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Notification\IManager;


use OCP\Util;
use OCP\INavigationManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use OCP\IServerContainer;
use OCP\AppFramework\Http\RedirectResponse;
use Psr\Container\ContainerInterface;
use OCP\IURLGenerator;
use OCA\AaoChat\Service\AaochatService;
use OCA\AaoChat\Service\ApiauthService;
use OCP\IConfig;
use OCP\IUserManager;
use OCP\IUser;
use OCP\IUserSession;
use OC\Files\Filesystem;
use OC\Files\Node\File;

use OCA\Files\Event\LoadSidebar;
use OCP\User\Events\UserCreatedEvent;
use OCP\User\Events\UserChangedEvent;
use OCP\User\Events\BeforeUserDeletedEvent;
use OCP\User\Events\UserLoggedInEvent;
use OCP\User\Events\UserLoggedOutEvent;
use OCP\Share\Events\ShareCreatedEvent;
use OCP\Share\Events\ShareDeletedEvent;

use OCA\AaoChat\Listener\LoadSidebarListener;
use OCA\AaoChat\Listener\UserCreatedListener;
use OCA\AaoChat\Listener\UserChangedListener;
use OCA\AaoChat\Listener\UserDeletedListener;
use OCA\AaoChat\Listener\AppEnableListener;
use OCA\AaoChat\Listener\AppDisableListener;
use OCA\AaoChat\Listener\AppEnableforgroupsListener;
use OCA\AaoChat\Listener\FileShareListener;
use OCA\AaoChat\Listener\FileUnshareListener;
use OCA\AaoChat\Listener\UserLoggedInListener;
use OCA\AaoChat\Listener\UserLoggedOutListener;

class Application extends App implements IBootstrap {
	public const APP_ID = 'aaochat';
    public const APP_VERSION = 'v01';
    public const APP_NAME = 'Aao';

	/**
     * @var OC\AllConfig
     */
    protected $config;

    /** @var IUserSession */
    private $userSession;

    /** @var INavigationManager */
    private $navigationManager;

    /** @var IURLGenerator */
    private $urlGenerator;

    private $isLicenseValid;
    private $aaochatService;
    private $apiauthService;

	public function __construct() {
		parent::__construct(self::APP_ID);

		/** @var INavigationManager */
        //$this->navigationManager = \OC::$server->get(INavigationManager::class);
        $this->urlGenerator = \OC::$server->get(IURLGenerator::class);
        $this->config = \OC::$server[IConfig::class];
        
	}

	public function register(IRegistrationContext $context): void {
        $this->addEventListener($context);
    }

    public function boot(IBootContext $context): void {
        $serverContainer = $context->getServerContainer();

        $this->registerCollaborationResourceProvider($serverContainer);

        /** @var IManager $manager */
        $manager = $context->getAppContainer()->query(IManager::class);
        $this->userSession = $serverContainer->get(IUserSession::class);

        /** @var AaochatService $aaochatService */
        $this->aaochatService = $context->getAppContainer()->get(AaochatService::class);
        $this->apiauthService = $context->getAppContainer()->get(ApiauthService::class);
        
        $this->registerHooks($this->aaochatService, $this->apiauthService, $context->getServerContainer());

        $licenseKey = $this->config->getAppValue(Application::APP_ID, 'aaochat_license_key', '');
        $this->isLicenseValid = $this->config->getAppValue(Application::APP_ID, 'aaochat_is_license_valid', '');
        if($this->isLicenseValid === 'yes') {
            \OC::$server->getNavigationManager()->add(array(
                'id'    => self::APP_ID,
                'order' => 76,
                'href'  => 'javascript:void(0)',
                'data-href'  => $this->urlGenerator->linkToRoute('aaochat.page.index'),
                'icon' => \OC::$server->getURLGenerator()->imagePath(self::APP_ID, 'app.svg'),
                'name'  => self::APP_NAME
            ));
        }

        $aaochat_server_url = $this->aaochatService->getAaochatServerUrl();
        $contentApiDomain = $this->aaochatService->removeHttp($aaochat_server_url); //business2.aaochat.com

        $managerPolicy = \OC::$server->getContentSecurityPolicyManager();
        $policy = new \OCP\AppFramework\Http\EmptyContentSecurityPolicy();
        $policy->addAllowedScriptDomain($contentApiDomain);
        $policy->addAllowedChildSrcDomain($contentApiDomain);
        $policy->addAllowedConnectDomain($contentApiDomain);
        $policy->addAllowedConnectDomain('*');
        $policy->addAllowedConnectDomain('wss:');
        $policy->addAllowedFrameDomain($contentApiDomain);
        $policy->addAllowedImageDomain($contentApiDomain);
        $policy->addAllowedImageDomain('*');
        $policy->addAllowedMediaDomain($contentApiDomain);
        $policy->addAllowedMediaDomain('*');
        $policy->addAllowedObjectDomain($contentApiDomain);
        $policy->addAllowedWorkerSrcDomain($contentApiDomain);
        $policy->addAllowedFormActionDomain($contentApiDomain);
        $policy->allowInlineScript(true);
        $policy->allowInlineStyle(true);
        $policy->allowEvalScript(true);
        $managerPolicy->addDefaultPolicy($policy);

        //\OC::$server->getAppManager()->disableApp(self::APP_ID);
        if($this->isLicenseValid === 'yes') {
            if(!isset($_COOKIE['ncUserAuthKey'])) {
                /*
                $currentUser = $this->userSession->getUser();
                $userId = $currentUser->getUID();

                $user_manager   = \OC::$server->getUserManager();
                $user = $user_manager->get($userId);
                */
                $currentUser = $this->userSession->getUser();
                if ($currentUser instanceof IUser) {
                    $userId = $currentUser->getUID();

                    $userName = $userId;
                    $userEmail = $currentUser->getEMailAddress();
                    $userDisplayName = $currentUser->getDisplayName();
                    $avatarImage = $currentUser->getAvatarImage(100);

                    $userData = array();
                    $userData['user_name'] = $userId;
                    $userData['user_email'] = $userEmail;
                    $userData['display_name'] = $userDisplayName;
                    $userData['avatar_image'] = $avatarImage;

                    $userToken = $this->apiauthService->generateAuthToken($userName, $userEmail);
                    $userData['token'] = $userToken;
                    $aaochatUserToken = '"123"';
                    $response = $this->aaochatService->sendUserdataToAaochat($userData);

                    $response = json_decode($response);
                    if(isset($response->status) && $response->status=='success') {
                        $responseData = $response->data;
                        $aaochatUserToken = '"'.$responseData->auth_key.'"';
                    }
                    $aaochatServerUrl = $this->aaochatService->getAaochatServerUrl();
                    $aaochatFileServerUrl = $this->aaochatService->getAaochatFileServerUrl();

                    unset($_COOKIE['ncUserAuthKey']);
                    setcookie('ncUserAuthKey', $aaochatUserToken, time() + 3600000*24*7, '/');

                    unset($_COOKIE['aaochatServerUrl']);
                    setcookie('aaochatServerUrl', $aaochatServerUrl, time() + 3600000*24*7, '/');

                    unset($_COOKIE['aaochatFileServerUrl']);
                    setcookie('aaochatFileServerUrl', $aaochatFileServerUrl, time() + 3600000*24*7, '/');
                }
            }
        } else {
            unset($_COOKIE['ncUserAuthKey']);
            setcookie('ncUserAuthKey', '', -1, '/'); 
            unset($_COOKIE['aaochatServerUrl']);
            setcookie('aaochatServerUrl', '', -1, '/');
            unset($_COOKIE['aaochatFileServerUrl']);
            setcookie('aaochatFileServerUrl', '', -1, '/');
        }
    }


	public function addEventListener(IRegistrationContext $context) 
    {
		$context->registerCapability(Capabilities::class);
        $context->registerEventListener(LoadSidebar::class, LoadSidebarListener::class);

        $context->registerEventListener(UserCreatedEvent::class, UserCreatedListener::class);
        //Listener called 2 times so that used hooks form Action
        //$context->registerEventListener(UserChangedEvent::class, UserChangedListener::class);
        $context->registerEventListener(BeforeUserDeletedEvent::class, UserDeletedListener::class);

        $context->registerEventListener(ShareCreatedEvent::class, FileShareListener::class);
        //Deprecated in 27
        $context->registerEventListener(ShareDeletedEvent::class, FileUnshareListener::class);

        $context->registerEventListener(UserLoggedInEvent::class, UserLoggedInListener::class);
        $context->registerEventListener(UserLoggedOutEvent::class, UserLoggedOutListener::class);
        
        $context->registerEventListener(ManagerEvent::EVENT_APP_ENABLE, AppEnableListener::class);
        $context->registerEventListener(ManagerEvent::EVENT_APP_ENABLE_FOR_GROUPS, AppEnableforgroupsListener::class);
        $context->registerEventListener(ManagerEvent::EVENT_APP_DISABLE, AppDisableListener::class);
        

    }

    /**
     * Register hooks in order to log them
     */
    private function registerHooks(AaochatService $aaochatService, ApiauthService $apiauthService, IServerContainer $serverContainer): void {

        $this->userManagementHooks($aaochatService, $apiauthService, $serverContainer->get(IUserSession::class));
        $this->fileManagementHooks($aaochatService, $apiauthService, $serverContainer->get(IUserSession::class));

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $serverContainer->get(EventDispatcherInterface::class);

        $this->appHooks($eventDispatcher);
    }

    private function appHooks(EventDispatcherInterface $eventDispatcher): void {

    }

    private function userManagementHooks(AaochatService $aaochatService, ApiauthService $apiauthService, IUserSession $userSession): void {
        $userActions = new UserManagement($aaochatService, $apiauthService);

        Util::connectHook('OC_User', 'changeUser', $userActions, 'change');
    }

    private function fileManagementHooks(AaochatService $aaochatService, ApiauthService $apiauthService, IUserSession $userSession): void {
        $fileActions = new Files($aaochatService, $apiauthService);

        Util::connectHook(
            Filesystem::CLASSNAME,
            Filesystem::signal_post_create,
            $fileActions,
            'create'
        );

        Util::connectHook(
            Filesystem::CLASSNAME,
            Filesystem::signal_post_copy,
            $fileActions,
            'copy'
        );

        Util::connectHook(
            Filesystem::CLASSNAME,
            Filesystem::signal_post_update,
            $fileActions,
            'update'
        );

        Util::connectHook(
            Filesystem::CLASSNAME,
            Filesystem::signal_delete,
            $fileActions,
            'delete'
        );

        Util::connectHook(Filesystem::CLASSNAME, Filesystem::signal_post_rename, $fileActions, 'fileMovePost');
    }

    protected function registerCollaborationResourceProvider(IServerContainer $server): void {

        Util::addScript(self::APP_ID, 'jquery_cookie.min');
        Util::addScript(self::APP_ID, 'jquery_fancybox');
        Util::addScript(self::APP_ID, 'adminsetting');
        Util::addScript(self::APP_ID, 'script');

		$server->getEventDispatcher()->addListener('\OCP\Collaboration\Resources::loadAdditionalScripts', function () {
            if($this->isLicenseValid === 'yes') {
                Util::addScript(self::APP_ID, 'authkey');
                //Util::addScript(self::APP_ID, 'aaochat.tabview'.self::APP_VERSION);
                //Util::addScript(self::APP_ID, 'aaochattab.plugin'.self::APP_VERSION);
            }
            Util::addStyle(self::APP_ID, 'aaochat');
            Util::addStyle(self::APP_ID, 'aaochat-icons');
            Util::addStyle(self::APP_ID, 'emoji');
            Util::addStyle(self::APP_ID, 'jquery_fancybox');
		});
	}

	/**
     * Checks if this app is enabled.
     */
    protected function isEnabled(): bool
    {
        $enabled = true;
        $isForced = (bool) $this->config->getAppValue(self::APP_ID, 'force', '0');

        if (null !== $this->user && !$isForced) {
            $enabled = (bool) $this->config->getUserValue(
                $this->user->getUid(),
                self::APP_ID,
                'enabled',
                $this->config->getAppValue(
                    self::APP_ID,
                    'default-enabled',
                    '1'
                )
            );
        }

        return $enabled;
    }
}
