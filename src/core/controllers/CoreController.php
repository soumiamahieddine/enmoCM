<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Core Controller
 * @author dev@maarch.org
 * @ingroup Core
 */

namespace SrcCore\controllers;

use Core\Models\ServiceModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;

class CoreController
{
    public function initialize(Request $request, Response $response)
    {
        $customId = CoreConfigModel::getCustomId();

        $data = $request->getParams();

        $aInit = [];
        $aInit['coreUrl'] = str_replace('rest/', '', \Url::coreurl());
        $aInit['applicationName'] = CoreConfigModel::getApplicationName();
        $aInit['lang'] = CoreConfigModel::getLanguage();

        if (!empty($data['views'])) {
            foreach ($data['views'] as $view) {
                $aInit[$view . 'View'] = 'Views/' . $view . '.component.html';
                if (file_exists("custom/{$customId}/apps/maarch_entreprise/Views/{$view}.component.html")) {
                    $aInit[$view . 'View'] = "../../custom/{$customId}/apps/maarch_entreprise/Views/{$view}.component.html";
                }
            }
        }

        return $response->withJson($aInit);
    }

    public static function getAdministration(Request $request, Response $response)
    {
        if ($GLOBALS['userId'] == 'superadmin') {
            $administration = [];
            $administration['menu'] = ServiceModel::getApplicationAdministrationMenuByXML();
            $administration['application'] = ServiceModel::getApplicationAdministrationServicesByXML();
            $administration['modules'] = ServiceModel::getModulesAdministrationServicesByXML();
        } else {
            $administration = ServiceModel::getAdministrationServicesByUserId(['userId' => $GLOBALS['userId']]);
        }

        return $response->withJson($administration);
    }
}
