<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Aao Chat <info@aaochat.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\AaoChat\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#getAuthKey', 'url' => '/getauthkey', 'verb' => 'POST'],
		['name' => 'page#setting', 'url' => '/setting', 'verb' => 'GET'],
		['name' => 'page#doEcho', 'url' => '/doecho', 'verb' => 'GET'],
		['name' => 'page#getGroup', 'url' => '/getgroup', 'verb' => 'GET'],
		['name' => 'page#getSharedInfo', 'url' => '/syncshareinfo', 'verb' => 'GET'],
		['name' => 'aaochatSetting#valueSet', 'url' => '/adminsetting', 'verb' => 'POST'],
		['name' => 'aaochatSetting#activateLicense', 'url' => '/activatelicense', 'verb' => 'POST'],
		['name' => 'aaochatSetting#surrenderLicense', 'url' => '/surrenderlicense', 'verb' => 'POST'],
		['name' => 'aaochatSetting#validateLicense', 'url' => '/validatelicense', 'verb' => 'POST'],
		['name' => 'aaochatSetting#createLead', 'url' => '/createlead', 'verb' => 'POST'],
		['name' => 'aaochatSetting#leadStatus', 'url' => '/leadstatus', 'verb' => 'POST'],
		[
			'name' => 'api#index',
			'url' => '/api/v1/index',
			'verb' => 'GET'
		],
		[
			'name' => 'api#tokenVerify',
			'url' => '/api/v1/tokenverify',
			'verb' => 'GET'
		],
		[
			'name' => 'api#authenticate',
			'url' => '/api/v1/authenticate',
			'verb' => 'POST'
		],
		[
			'name' => 'api#getuser',
			'url' => '/api/v1/getuser',
			'verb' => 'POST'
		],
		[
			'name' => 'api#getsharedata',
			'url' => '/api/v1/getsharedata',
			'verb' => 'GET'
		]
	]
];
