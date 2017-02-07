<?php
/**
*   Copyright 2017 Maarch
*
*   This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @file
* @author Florian Azizian
* @date $date$
* @version $Revision$
* @ingroup core
* @doc https://fr.wikipedia.org/wiki/Representational_State_Transfer
*/
header('Content-Type: text/html; charset=utf-8');

//create session if NO SESSION
if ( empty($_SESSION['user']) ) {
	require_once('core/class/class_functions.php');
	include_once('core/init.php');
	require_once('core/class/class_portal.php');
	require_once('core/class/class_db.php');
	require_once('core/class/class_request.php');
	require_once('core/class/class_core_tools.php');
	require_once('core/class/web_service/class_web_service.php');
	require_once('core/services/CoreConfig.php');

	//load Maarch session vars
	$portal = new portal();
	$portal->unset_session();
	$portal->build_config();
	$coreTools = new core_tools();
	$_SESSION['custom_override_id'] = $coreTools->get_custom_id();
	if (isset($_SESSION['custom_override_id'])
	    && ! empty($_SESSION['custom_override_id'])
	    && isset($_SESSION['config']['corepath'])
	    && ! empty($_SESSION['config']['corepath'])
	) {
	    $path = $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
	        . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR;
	    set_include_path(
	        $path . PATH_SEPARATOR . $_SESSION['config']['corepath']
	        . PATH_SEPARATOR . get_include_path()
	    );
	} else if (isset($_SESSION['config']['corepath'])
	    && ! empty($_SESSION['config']['corepath'])
	) {
	    set_include_path(
	        $_SESSION['config']['corepath'] . PATH_SEPARATOR . get_include_path()
	    );
	}
	// Load configuration from xml into session
	Core_CoreConfig_Service::buildCoreConfig('core' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'config.xml');
	$_SESSION['config']['app_id'] = $_SESSION['businessapps'][0]['appid'];
	require_once('apps' . DIRECTORY_SEPARATOR . $_SESSION['businessapps'][0]['appid'] 
	    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR 
	    . 'class_business_app_tools.php'
	);
	Core_CoreConfig_Service::buildBusinessAppConfig();
	
	// Load Modules configuration from xml into session
	Core_CoreConfig_Service::loadModulesConfig($_SESSION['modules']);
	Core_CoreConfig_Service::loadAppServices();
	Core_CoreConfig_Service::loadModulesServices($_SESSION['modules']);
}

require_once('apps/maarch_entreprise/define.php');
require_once('core/services/Rest.php');
require_once('core/services/Session.php');
$lifetime=3600;
setcookie(session_name(),session_id(),time()+$lifetime);

// Rest :
$oApi = new Core_Rest_Service();
if (!file_exists('installed.lck') && is_dir('install')) {
    $oApi->returnError(['errors'=>'Not installed']);
}

$userSSOHeader = '';
if (!empty($_SERVER['HTTP_'.HEADER_USER_UID])) {
	$userSSOHeader = $_SERVER['HTTP_' .HEADER_USER_UID];
} else if (!empty($_SERVER['HTTP_' .HEADER_USER_NIGEND])) {
	$userSSOHeader = $_SERVER['HTTP_' .HEADER_USER_NIGEND];
}
$timestart_authentification = microtime(true);
if ( empty($_SESSION['user']) || (!empty($userSSOHeader) && $_SESSION['user']['UserId'] != $userSSOHeader )) {
	$oSessionService = new \Core_Session_Service();
	if (!empty($userSSOHeader)) {
		$auth = $oSessionService->authentication($userSSOHeader);
	} else {
		$auth = false;
	}

	if (!$auth) {
		$oApi->returnWarning(['errors'=>'User Not connected']);
	} else {
		$_SESSION['user'] = $auth['user'];
	}
}

try {
	// Vérification du module :
	if ( empty($_GET['module']) ) {
		$oApi->returnError([
			'errors'=>'module arg missing',
			'debug'=>[
				'module args : $_GET[module]',
				],
			]);
	}
	require_once('core/services/Modules.php');

	$aModules = Core_Modules_Service::getList();
	$aModules['apps'] = 'apps';
	$aModules['core'] = 'core';
	if ( !isset($aModules[$_GET['module']]) ) {
		$oApi->returnError([
			'errors'=>'module Not installed',
			'debug'=>[
				'module asked : '.$_GET['module'],
				],
			]);
	}
	$sModule = $aModules[$_GET['module']];
	
	// Vérification du service :
	if ( empty($_GET['service']) ) {
		$oApi->returnError([
			'errors'=>'service arg missing',
			'debug'=>[
				'service args : $_GET[service]',
				],
			]);
	}
	$aServices = Core_Modules_Service::getServicesList(['require'=>'once']);
	if ( !isset($aServices[$sModule][$_GET['service']]) ) {
		$oApi->returnError([
			'errors'=>'service Not exists',
			'debug'=>[
				'service asked : '.$_GET['service'],
				'service exist : '.var_export($aServices[$sModule],true),
				],
			]);
	}
	$oService = new $aServices[$sModule][$_GET['service']]();

	// Vérification de la methode
	if ( empty($_GET['method']) ) {
		$oApi->returnError([
			'errors'=>'method arg missing',
			'debug'=>[
				'method args : $_GET[method]',
				],
			]);
	}

	$aMethodes = $oService::getApiMethod();
	if ( !isset($aMethodes[$_GET['method']]) ) {
		$oApi->returnError([
			'errors'=>'method Not installed',
			'debug'=>[
				'method asked : '.$_GET['method'],
				],
			]);
	}
	$sMethode = $aMethodes[$_GET['method']];

	$resultMethode = $oService->{$sMethode}($_POST);

	if ( isset($resultMethode['result']) ) {
		// Renvoi du résultat avec le résult spécifié dans la réponse du service
		$oApi->returnSuccess($resultMethode);
	}else{
		// Renvoi du résultat en tant que result
		$oApi->returnSuccess([
			'result' => $resultMethode
		]);
	}
} catch (\Exception $e) {
	$oApi->returnError(['errors'=>'Exception : '.$e->getMessage(), 'status'=>$e->getCode(), 'debug'=>$e->getTraceAsString(), ]);
}
exit;
