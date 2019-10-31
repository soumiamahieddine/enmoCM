<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Core Controller
 *
 * @author dev@maarch.org
 */

namespace SrcCore\controllers;

use Group\controllers\ServiceController;
use Group\models\ServiceModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class CoreController
{
    public function initialize(Request $request, Response $response)
    {
        $aInit = [];
        $aInit['coreUrl']            = str_replace('rest/', '', UrlController::getCoreUrl());
        $aInit['applicationName']    = CoreConfigModel::getApplicationName();
        $aInit['applicationVersion'] = CoreConfigModel::getApplicationVersion();
        $aInit['lang']               = CoreConfigModel::getLanguage();
        $aInit['user']               = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id', 'user_id', 'firstname', 'lastname']]);
        $aInit['customLanguage']     = CoreConfigModel::getCustomLanguage(['lang' => $aInit['lang']]);

        $aInit['scriptsToinject'] = [];
        $aInit['scriptsInjected'] = [];

        $scriptsToInject = [];
        $scripts = scandir('dist');
        foreach ($scripts as $value) {
            if (strstr($value, 'runtime.') !== false || strstr($value, 'main.') !== false || strstr($value, 'vendor.') !== false || strstr($value, 'scripts.') !== false) {
                if (strstr($value, '.js.map') === false) {
                    $scriptName          = explode(".", $value);
                    $modificationDate    = filemtime(realpath("dist/" . $value));
                    $idArrayTime         = $scriptName[0] . "." . pathinfo($value, PATHINFO_EXTENSION);

                    if (!isset($aInit['scriptsInjected'][$idArrayTime]) || $modificationDate > $aInit['scriptsInjected'][$idArrayTime][0]) {
                        if (isset($aInit['scriptsInjected'][$idArrayTime])) {
                            array_pop($scriptsToInject);
                        }
                        $aInit['scriptsInjected'][$idArrayTime][0] = filemtime(realpath("dist/" . $value));
                        $aInit['scriptsInjected'][$idArrayTime][1] = $value;

                        $scriptsToInject[] = $value;
                    }
                }
            }
        }
        unset($aInit['scriptsInjected']);

        for ($i = 0; $i < count($scriptsToInject); $i++) {
            foreach ($scriptsToInject as $value) {
                if ($i == 0 && strstr($value, 'scripts.') !== false) {
                    $aInit['scriptsToinject'][] = $value;
                } elseif ($i == 1 && strstr($value, 'main.') !== false) {
                    $aInit['scriptsToinject'][] = $value;
                } elseif ($i == 2 && strstr($value, 'runtime.') !== false) {
                    $aInit['scriptsToinject'][] = $value;
                } elseif ($i == 3 && strstr($value, 'vendor.') !== false) {
                    $aInit['scriptsToinject'][] = $value;
                }
            }
        }

        return $response->withJson($aInit);
    }

    public function getHeader(Request $request, Response $response)
    {
        $user = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id', 'user_id', 'firstname', 'lastname']]);
        $user['groups'] = UserModel::getGroupsByLogin(['login' => $GLOBALS['userId']]);
        $user['entities'] = UserModel::getEntitiesByLogin(['login' => $GLOBALS['userId']]);

//        if ($GLOBALS['userId'] == 'superadmin') {
//            $menu = ServiceController::getServicesMenu();
//        } else {
//            $menu = ServiceController::getMenuServicesByUserId(['userId' => $GLOBALS['userId']]);
//        }

        if ($GLOBALS['userId'] == 'superadmin') {
            $menu = ServiceModel::getApplicationServicesByXML(['type' => 'menu']);
            $menuModules = ServiceModel::getModulesServicesByXML(['type' => 'menu']);
            $menu = array_merge($menu, $menuModules);
        } else {
            $menu = ServiceController::getMenuServicesByUserIdByXml(['userId' => $GLOBALS['userId']]);
        }

        return $response->withJson([
            'user'      => $user,
            'menu'      => $menu
        ]);
    }

    public function getShortcuts(Request $request, Response $response)
    {
        $shortcuts = [
            ['id' => 'home']
        ];

        if (ServiceModel::hasService2(['serviceId' => 'admin', 'userId' => $GLOBALS['id']])) {
            $shortcuts[] = ['id' => 'administration'];
        }
        if (ServiceModel::hasService2(['serviceId' => 'adv_search_mlb', 'userId' => $GLOBALS['id']])) {
            $shortcuts[] = ['id' => 'search'];
        }

        $indexingGroups = [];
        $userGroups = UserModel::getGroupsByLogin(['login' => $GLOBALS['userId']]);
        foreach ($userGroups as $group) {
            if ($group['can_index']) {
                $indexingGroups[] = ['id' => $group['id'], 'label' => $group['group_desc']];
            }
        }
        if (!empty($indexingGroups)) {
            $shortcuts[] = [
                'id'        => 'indexing',
                'groups'    => $indexingGroups
            ];
        }

        return $response->withJson(['shortcuts' => $shortcuts]);
    }

    public function getAdministration(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'menu'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if ($GLOBALS['userId'] == 'superadmin') {
            $administration                    = [];
            $administrationApplication         = ServiceModel::getApplicationServicesByXML(['type' => 'admin']);
            $administrationModule              = ServiceModel::getModulesServicesByXML(['type' => 'admin']);
            $administration['administrations'] = array_merge_recursive($administrationApplication, $administrationModule);
        } else {
            $administration = ServiceModel::getAdministrationServicesByUserId(['userId' => $GLOBALS['userId']]);
        }

        return $response->withJson($administration);
    }

    public static function setGlobals(array $args)
    {
        ValidatorModel::notEmpty($args, ['login']);
        ValidatorModel::stringType($args, ['login']);

        $user = UserModel::getByLogin(['login' => $args['login'], 'select' => ['id']]);
        $GLOBALS['userId'] = $args['login'];
        $GLOBALS['id'] = $user['id'];
    }
}
