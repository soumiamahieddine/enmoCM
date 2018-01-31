<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Rest Routes File
* @author dev@maarch.org
*/

require '../vendor/autoload.php';

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
    } elseif (isset($_SESSION['config']['corepath'])
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
    echo $_SESSION['error'];
    exit();
}

if (strpos(getcwd(), '/rest')) {
    chdir('..');
}

if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
    if (\Core\Models\SecurityModel::authentication(['userId' => $_SERVER['PHP_AUTH_USER'], 'password' => $_SERVER['PHP_AUTH_PW']])) {
        $userId = $_SERVER['PHP_AUTH_USER'];
    }
} else {
    $cookie = \Core\Models\SecurityModel::getCookieAuth();
    if (!empty($cookie) &&\Core\Models\SecurityModel::cookieAuthentication($cookie)) {
        \Core\Models\SecurityModel::setCookieAuth(['userId' => $cookie['userId']]);
        $userId = $cookie['userId'];
    }
}

if (empty($userId)) {
    echo 'Authentication Failed';
    exit();
}

$language = \SrcCore\models\CoreConfigModel::getLanguage();
require_once("src/core/lang/lang-{$language}.php");


$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);


//Initialize
$app->post('/initialize', \Core\Controllers\CoreController::class . ':initialize');

//Administration
$app->get('/administration', \Core\Controllers\CoreController::class . ':getAdministration');
$app->get('/administration/users', \Core\Controllers\UserController::class . ':getUsersForAdministration');
$app->get('/administration/users/new', \Core\Controllers\UserController::class . ':getNewUserForAdministration');
$app->get('/administration/users/{id}', \Core\Controllers\UserController::class . ':getUserForAdministration');
$app->get('/administration/notifications/new', \Notifications\Controllers\NotificationController::class . ':getNewNotificationForAdministration');
$app->get('/administration/notifications/{id}', \Notifications\Controllers\NotificationController::class . ':getNotificationForAdministration');

//Baskets
$app->get('/baskets', \Basket\controllers\BasketController::class . ':get');
$app->post('/baskets', \Basket\controllers\BasketController::class . ':create');
$app->get('/baskets/{id}', \Basket\controllers\BasketController::class . ':getById');
$app->put('/baskets/{id}', \Basket\controllers\BasketController::class . ':update');
$app->delete('/baskets/{id}', \Basket\controllers\BasketController::class . ':delete');
$app->get('/baskets/{id}/groups', \Basket\controllers\BasketController::class . ':getGroups');
$app->post('/baskets/{id}/groups', \Basket\controllers\BasketController::class . ':createGroup');
$app->put('/baskets/{id}/groups/{groupId}', \Basket\controllers\BasketController::class . ':updateGroup');
$app->delete('/baskets/{id}/groups/{groupId}', \Basket\controllers\BasketController::class . ':deleteGroup');
$app->get('/baskets/{id}/groups/data', \Basket\controllers\BasketController::class . ':getDataForGroupById');
$app->get('/sortedBaskets', \Basket\controllers\BasketController::class . ':getSorted');
$app->put('/sortedBaskets/{id}', \Basket\controllers\BasketController::class . ':updateSort');

//statuses
$app->get('/statuses', \Status\controllers\StatusController::class . ':get');
$app->post('/statuses', \Status\controllers\StatusController::class . ':create');
$app->get('/statuses/{identifier}', \Status\controllers\StatusController::class . ':getByIdentifier');
$app->put('/statuses/{identifier}', \Status\controllers\StatusController::class . ':update');
$app->delete('/statuses/{identifier}', \Status\controllers\StatusController::class . ':delete');
$app->get('/administration/statuses/new', \Status\controllers\StatusController::class . ':getNewInformations');

//groups
$app->get('/groups', \Core\Controllers\GroupController::class . ':get');
$app->post('/groups', \Core\Controllers\GroupController::class . ':create');
$app->put('/groups/{id}', \Core\Controllers\GroupController::class . ':update');
$app->delete('/groups/{id}', \Core\Controllers\GroupController::class . ':delete');
$app->get('/groups/{id}/details', \Core\Controllers\GroupController::class . ':getDetailledById');
$app->put('/groups/{id}/services/{serviceId}', \Core\Controllers\GroupController::class . ':updateService');
$app->put('/groups/{id}/reassign/{newGroupId}', \Core\Controllers\GroupController::class . ':reassignUsers');

//Docservers
$app->get('/docservers', \Core\Controllers\DocserverController::class . ':get');
$app->get('/docservers/{id}', \Core\Controllers\DocserverController::class . ':getById');

//DocserverTypes
$app->get('/docserverTypes', \core\Controllers\DocserverTypeController::class . ':get');
$app->get('/docserverTypes/{id}', \core\Controllers\DocserverTypeController::class . ':getById');

//ListModels
$app->get('/listModels/itemId/{itemId}/itemMode/{itemMode}/objectType/{objectType}', \Entities\Controllers\ListModelsController::class . ':getListModelsDiffListDestByUserId');
$app->put('/listModels/itemId/{itemId}/itemMode/{itemMode}/objectType/{objectType}', \Entities\Controllers\ListModelsController::class . ':updateListModelsDiffListDestByUserId');

//Visa
$app->get('/{basketId}/signatureBook/resList', \Visa\Controllers\VisaController::class . ':getResList');
$app->get('/{basketId}/signatureBook/resList/details', \Visa\Controllers\VisaController::class . ':getDetailledResList');
$app->get('/groups/{groupId}/baskets/{basketId}/signatureBook/{resId}', \Visa\Controllers\VisaController::class . ':getSignatureBook');
$app->get('/signatureBook/{resId}/attachments', \Visa\Controllers\VisaController::class . ':getAttachmentsById');
$app->get('/signatureBook/{resId}/incomingMailAttachments', \Visa\Controllers\VisaController::class . ':getIncomingMailAndAttachmentsById');
$app->put('/{collId}/{resId}/unsign', \Visa\Controllers\VisaController::class . ':unsignFile');
$app->put('/attachments/{id}/inSignatureBook', \Attachments\Controllers\AttachmentsController::class . ':setInSignatureBook');

//Res
$app->post('/res', \Resource\controllers\ResController::class . ':create');
$app->post('/resExt', \Resource\controllers\ResController::class . ':createExt');
$app->put('/res/resource/status', \Resource\controllers\ResController::class . ':updateStatus');
$app->get('/res/{resId}/lock', \Resource\controllers\ResController::class . ':isLock');
$app->get('/res/{resId}/notes/count', \Resource\controllers\ResController::class . ':getNotesCountForCurrentUserById');

//Users
$app->get('/users/autocompleter', \Core\Controllers\UserController::class . ':getUsersForAutocompletion');
$app->post('/users', \Core\Controllers\UserController::class . ':create');
$app->get('/users/{id}/details', \Core\Controllers\UserController::class . ':getDetailledById');
$app->put('/users/{id}', \Core\Controllers\UserController::class . ':update');
$app->put('/users/{id}/password', \Core\Controllers\UserController::class . ':resetPassword');
$app->put('/users/{id}/status', \Core\Controllers\UserController::class . ':updateStatus');
$app->delete('/users/{id}', \Core\Controllers\UserController::class . ':delete');
$app->post('/users/{id}/groups', \Core\Controllers\UserController::class . ':addGroup');
$app->put('/users/{id}/groups/{groupId}', \Core\Controllers\UserController::class . ':updateGroup');
$app->delete('/users/{id}/groups/{groupId}', \Core\Controllers\UserController::class . ':deleteGroup');
$app->post('/users/{id}/entities', \Core\Controllers\UserController::class . ':addEntity');
$app->put('/users/{id}/entities/{entityId}', \Core\Controllers\UserController::class . ':updateEntity');
$app->put('/users/{id}/entities/{entityId}/primaryEntity', \Core\Controllers\UserController::class . ':updatePrimaryEntity');
$app->delete('/users/{id}/entities/{entityId}', \Core\Controllers\UserController::class . ':deleteEntity');
$app->post('/users/{id}/signatures', \Core\Controllers\UserController::class . ':addSignature');
$app->put('/users/{id}/signatures/{signatureId}', \Core\Controllers\UserController::class . ':updateSignature');
$app->delete('/users/{id}/signatures/{signatureId}', \Core\Controllers\UserController::class . ':deleteSignature');
$app->post('/users/{id}/redirectedBaskets', \Core\Controllers\UserController::class . ':setRedirectedBaskets');
$app->delete('/users/{id}/redirectedBaskets/{basketId}', \Core\Controllers\UserController::class . ':deleteRedirectedBaskets');

//CurrentUser
$app->get('/currentUser/profile', \Core\Controllers\UserController::class . ':getProfile');
$app->put('/currentUser/profile', \Core\Controllers\UserController::class . ':updateProfile');
$app->put('/currentUser/password', \Core\Controllers\UserController::class . ':updateCurrentUserPassword');
$app->post('/currentUser/emailSignature', \Core\Controllers\UserController::class . ':createCurrentUserEmailSignature');
$app->put('/currentUser/emailSignature/{id}', \Core\Controllers\UserController::class . ':updateCurrentUserEmailSignature');
$app->delete('/currentUser/emailSignature/{id}', \Core\Controllers\UserController::class . ':deleteCurrentUserEmailSignature');
$app->put('/currentUser/groups/{groupId}/baskets/{basketId}', \Core\Controllers\UserController::class . ':updateBasketPreference');

//Parameters
$app->get('/parameters', \Parameter\controllers\ParameterController::class . ':get');
$app->post('/parameters', \Parameter\controllers\ParameterController::class . ':create');
$app->get('/parameters/{id}', \Parameter\controllers\ParameterController::class . ':getById');
$app->put('/parameters/{id}', \Parameter\controllers\ParameterController::class . ':update');
$app->delete('/parameters/{id}', \Parameter\controllers\ParameterController::class . ':delete');

//Priorities
$app->get('/priorities', \Priority\controllers\PriorityController::class . ':get');
$app->post('/priorities', \Priority\controllers\PriorityController::class . ':create');
$app->get('/priorities/{id}', \Priority\controllers\PriorityController::class . ':getById');
$app->put('/priorities/{id}', \Priority\controllers\PriorityController::class . ':update');
$app->delete('/priorities/{id}', \Priority\controllers\PriorityController::class . ':delete');

//History
$app->get('/administration/history/eventDate/{date}', \History\controllers\HistoryController::class . ':getForAdministration'); //TODO No date
$app->get('/histories/users/{userSerialId}', \History\controllers\HistoryController::class . ':getByUserId');

//HistoryBatch
$app->get('/administration/historyBatch/eventDate/{date}', \History\controllers\HistoryController::class . ':getBatchForAdministration');//TODO No date

//actions
$app->get('/actions', \Action\controllers\ActionController::class . ':get');
$app->get('/initAction', \Action\controllers\ActionController::class . ':initAction');
$app->get('/actions/{id}', \Action\controllers\ActionController::class . ':getById');
$app->post('/actions', \Action\controllers\ActionController::class . ':create');
$app->put('/actions/{id}', \Action\controllers\ActionController::class . ':update');
$app->delete('/actions/{id}', \Action\controllers\ActionController::class . ':delete');

//Notifications
$app->get('/notifications', \Notifications\Controllers\NotificationController::class . ':get');
$app->post('/notifications', \Notifications\Controllers\NotificationController::class . ':create');
$app->get('/notifications/{id}', \Notifications\Controllers\NotificationController::class . ':getById');
$app->put('/notifications/{id}', \Notifications\Controllers\NotificationController::class . ':update');
$app->delete('/notifications/{id}', \Notifications\Controllers\NotificationController::class . ':delete');

//Reports
$app->get('/reports/groups', \Report\controllers\ReportController::class . ':getGroups');
$app->get('/reports/groups/{groupId}', \Report\controllers\ReportController::class . ':getByGroupId');
$app->put('/reports/groups/{groupId}', \Report\controllers\ReportController::class . ':updateForGroupId');

//Listinstance
$app->get('/listinstance/{id}', \Core\Controllers\ListinstanceController::class . ':getById');

//Contacts
$app->post('/contacts', \Contact\controllers\ContactController::class . ':create');

//Templates
$app->post('/templates/{id}/duplicate', \Templates\Controllers\TemplateController::class . ':duplicate');

//Links
$app->get('/links/resId/{resId}', \Core\Controllers\LinkController::class . ':getByResId');

//liste documents
$app->get('/res/listDocs/{clause}/{select}', \Resource\controllers\ResController::class . ':getListDocs');//TODO No clause

$app->run();
