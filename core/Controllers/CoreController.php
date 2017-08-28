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
 * @ingroup core
 */

namespace Core\Controllers;

use Core\Models\CoreConfigModel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\ServiceModel;

class CoreController
{
    public function initialize(RequestInterface $request, ResponseInterface $response)
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

    public static function getAdministration(RequestInterface $request, ResponseInterface $response)
    {
        if ($_SESSION['user']['UserId'] == 'superadmin') { //TODO session
            $administration = [];
            $administration['menu'] = ServiceModel::getApplicationAdministrationMenuByXML();
            $administration['application'] = ServiceModel::getApplicationAdministrationServicesByXML();
            $administration['modules'] = ServiceModel::getModulesAdministrationServicesByXML();
        } else {
            $administration = ServiceModel::getAdministrationServicesByUserId(['userId' => $_SESSION['user']['UserId']]); //TODO session
        }

        return $response->withJson($administration);
    }
}
