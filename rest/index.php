<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/
/**
* @brief Maarch rest root file
*
* @file
* @author dev@maarch.org
* @date $date$
* @version $Revision$
* @ingroup core
*/

require '../vendor/autoload.php';

header('Content-Type: text/html; charset=utf-8');

//create session if NO SESSION
if (empty($_SESSION['user'])) {
    require_once('../core/class/class_functions.php');
    include_once('../core/init.php');
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
    require_once 'apps/' .$_SESSION['businessapps'][0]['appid']. '/class/class_business_app_tools.php';

    Core_CoreConfig_Service::buildBusinessAppConfig();

    // Load Modules configuration from xml into session
    Core_CoreConfig_Service::loadModulesConfig($_SESSION['modules']);
    Core_CoreConfig_Service::loadAppServices();
    Core_CoreConfig_Service::loadModulesServices($_SESSION['modules']);
}

//login management
if (empty($_SESSION['user'])) {
    require_once('apps/maarch_entreprise/class/class_login.php');
    $loginObj = new login();
    $loginMethods = $loginObj->build_login_method();
    require_once('core/services/Session.php');
    $oSessionService = new \Core_Session_Service();

    $loginObj->execute_login_script($loginMethods, true);
}

if ($_SESSION['error']) {
    //TODO : return http bad authent error
    echo $_SESSION['error'];exit();
}

//$lifetime = 3600;
//setcookie(session_name(),session_id(),time()+$lifetime);

//exit;

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

//status
$app->get('/status', \Core\Controllers\StatusController::class . ':getList');
$app->get('/status/{id}', \Core\Controllers\StatusController::class . ':getById');
$app->post('/status', \Core\Controllers\StatusController::class . ':create');
$app->put('/status', \Core\Controllers\StatusController::class . ':update');
$app->delete('/status/{id}', \Core\Controllers\StatusController::class . ':delete');

$app->get('/{basketId}/signatureBook/{resId}', \Visa\Controllers\VisaController::class . ':getSignatureBook');
$app->put('/{collId}/{resId}/unsign', \Visa\Controllers\VisaController::class . ':unsignFile');

$app->run();
