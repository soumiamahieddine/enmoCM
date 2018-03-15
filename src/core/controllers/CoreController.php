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
 * @ingroup Core
 */

namespace SrcCore\controllers;

use Group\models\ServiceModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;

class CoreController
{
    public function initialize(Request $request, Response $response)
    {
        $aInit = [];
        $aInit['coreUrl'] = str_replace('rest/', '', \Url::coreurl());
        $aInit['lang'] = CoreConfigModel::getLanguage();
        $aInit['scriptsToinject'] = [];

        $scriptsToInject =  scandir('dist');
        foreach ($scriptsToInject as $key => $value) {
            if (strstr($value, 'inline.') !== false || strstr($value, 'main.') !== false || strstr($value, 'vendor.') !== false) {
                if (strstr($value, '.js.map') === false) {
                    $aInit['scriptsToinject'][] = $value;
                }
            }
        }

        if (!empty($aInit['scriptsToinject'][2]) && strstr($aInit['scriptsToinject'][2], 'vendor.') !== false) {
            $tmp = $aInit['scriptsToinject'][1];
            $aInit['scriptsToinject'][1] = $aInit['scriptsToinject'][2];
            $aInit['scriptsToinject'][2] = $tmp;
        }

        return $response->withJson($aInit);
    }

    public static function getAdministration(Request $request, Response $response)
    {
        if ($GLOBALS['userId'] == 'superadmin') {
            $administration = [];
            $administrationMenu = ServiceModel::getApplicationAdministrationMenuByXML();
            $administrationApplication = ServiceModel::getApplicationAdministrationServicesByXML();
            $administrationModule = ServiceModel::getModulesAdministrationServicesByXML();
            $administration['administrations'] = array_merge_recursive($administrationApplication, $administrationModule);
            $administration = array_merge_recursive($administration, $administrationMenu);
        } else {
            $administration = ServiceModel::getAdministrationServicesByUserId(['userId' => $GLOBALS['userId']]);
        }

        return $response->withJson($administration);
    }
}
