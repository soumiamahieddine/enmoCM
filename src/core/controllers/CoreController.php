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
use User\models\UserModel;

require_once 'core/class/Url.php';

class CoreController
{
    public function initialize(Request $request, Response $response)
    {
        $aInit = [];
        $aInit['coreUrl'] = str_replace('rest/', '', \Url::coreurl());
        $aInit['applicationName'] = CoreConfigModel::getApplicationName();
        $aInit['applicationMinorVersion'] = CoreConfigModel::getApplicationVersion()['applicationMinorVersion'];
        $aInit['lang'] = CoreConfigModel::getLanguage();
        $aInit['user'] = UserModel::getByUserId(['userId' => $GLOBALS['userId'], 'select' => ['id', 'user_id', 'firstname', 'lastname']]);
//        $aInit['user']['groups'] = UserModel::getGroupsByUserId(['userId' => $GLOBALS['userId']]);
//        $aInit['user']['entities'] = UserModel::getEntitiesById(['userId' => $GLOBALS['userId']]);

        $aInit['scriptsToinject'] = [];
        $scriptsToInject = [];
        $scripts = scandir('dist');
        foreach ($scripts as $value) {
            if (strstr($value, 'runtime.') !== false || strstr($value, 'main.') !== false || strstr($value, 'vendor.') !== false || strstr($value, 'scripts.') !== false) {
                if (strstr($value, '.js.map') === false) {
                    $scriptsToInject[] = $value;
                }
            }
        }

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
        $user = UserModel::getByUserId(['userId' => $GLOBALS['userId'], 'select' => ['id', 'user_id', 'firstname', 'lastname']]);
        $user['groups'] = UserModel::getGroupsByUserId(['userId' => $GLOBALS['userId']]);
        $user['entities'] = UserModel::getEntitiesById(['userId' => $GLOBALS['userId']]);

        if ($GLOBALS['userId'] == 'superadmin') {
            $menu = ServiceModel::getApplicationServicesByXML(['type' => 'menu']);
            $menuModules = ServiceModel::getModulesServicesByXML(['type' => 'menu']);
            $menu = array_merge($menu, $menuModules);
        } else {
            $menu = ServiceController::getMenuServicesByUserId(['userId' => $GLOBALS['userId']]);
        }

        return $response->withJson([
            'user'  => $user,
            'menu'  => $menu
        ]);
    }

    public static function getAdministration(Request $request, Response $response)
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
}
