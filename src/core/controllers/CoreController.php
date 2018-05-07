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
        $aInit['lang'] = CoreConfigModel::getLanguage();
        $aInit['user'] = UserModel::getByUserId(['userId' => $GLOBALS['userId'], 'select' => ['id', 'user_id', 'firstname', 'lastname']]);
        $aInit['user']['groups'] = UserModel::getGroupsByUserId(['userId' => $GLOBALS['userId']]);
        $aInit['user']['entities'] = UserModel::getEntitiesById(['userId' => $GLOBALS['userId']]);

        $aInit['scriptsToinject'] = [];

        $scriptsToInject = scandir('dist');
        foreach ($scriptsToInject as $key => $value) {
            if (strstr($value, 'runtime.') !== false || strstr($value, 'main.') !== false || strstr($value, 'vendor.') !== false || strstr($value, 'scripts.') !== false) {
                if (strstr($value, '.js.map') === false) {
                    $aInit['scriptsToinject'][] = $value;
                }
            }
        }

        if (!empty($aInit['scriptsToinject'][3]) && strstr($aInit['scriptsToinject'][3], 'vendor.') !== false) {
            $tmp = $aInit['scriptsToinject'][1];
            $aInit['scriptsToinject'][1] = $aInit['scriptsToinject'][2];
            $aInit['scriptsToinject'][2] = $tmp;
        }

        return $response->withJson($aInit);
    }

    public static function getAdministration(Request $request, Response $response)
    {
        if ($GLOBALS['userId'] == 'superadmin') {
            $administration                    = [];
//            $administrationMenu                = ServiceModel::getApplicationAdministrationMenuByXML();
            $administrationApplication         = ServiceModel::getApplicationAdministrationServicesByXML();
            $administrationModule              = ServiceModel::getModulesAdministrationServicesByXML();
            $administration['administrations'] = array_merge_recursive($administrationApplication, $administrationModule);
//            $administration                    = array_merge_recursive($administration, $administrationMenu);
        } else {
            $administration = ServiceModel::getAdministrationServicesByUserId(['userId' => $GLOBALS['userId']]);
        }

        return $response->withJson($administration);
    }

    public function renderJnlp(Request $request, Response $response)
    {
        $data = $request->getQueryParams();

        if (explode('.', $data['fileName'])[1] != 'jnlp') {
            return $response->withStatus(403)->withJson(['errors' => 'File extension forbidden']);
        } elseif (strpos($data['fileName'], "{$GLOBALS['userId']}_maarchCM_") === false) {
            return $response->withStatus(403)->withJson(['errors' => 'File name forbidden']);
        }

        $tmpPath = CoreConfigModel::getTmpPath();
        $jnlp = file_get_contents($tmpPath . $data['fileName']);
        if ($jnlp === false) {
            return $response->withStatus(404)->withJson(['errors' => 'Jnlp file not found on ' . $tmpPath]);
        }

        $response->write($jnlp);

        return $response->withHeader('Content-Type', 'application/x-java-jnlp-file');
    }
}
