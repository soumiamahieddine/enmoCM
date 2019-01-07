<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Configuration Controller
 * @author dev@maarch.org
 */

namespace Configuration\controllers;

use Configuration\models\ConfigurationModel;
use Group\models\ServiceModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class ConfigurationController
{
    public function getByService(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => $aArgs['service'], 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $configuration = ConfigurationModel::getByService(['service' => $aArgs['service']]);
        $configuration['value'] = (array)json_decode($configuration['value']);

        return $response->withJson(['configuration' => $configuration]);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => $aArgs['service'], 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (empty(ConfigurationModel::getByService(['service' => $aArgs['service'], 'select' => [1]]))) {
            return $response->withStatus(400)->withJson(['errors' => 'Configuration does not exist']);
        }

        $data = $request->getParams();

        if ($aArgs['service'] == 'admin_email_server') {
            $check = ConfigurationController::checkMailer($data);
            if (!empty($check['errors'])) {
                return $response->withStatus($check['code'])->withJson(['errors' => $check['errors']]);
            }
        }

        $data = json_encode($data);
        ConfigurationModel::update(['set' => ['value' => $data], 'where' => ['service = ?'], 'data' => [$aArgs['service']]]);

        return $response->withJson(['success' => 'success']);
    }

    private static function checkMailer(array $aArgs)
    {
        $check = Validator::stringType()->notEmpty()->validate($aArgs['type']);
        if (!$check) {
            return ['errors' => "configuration mode is missing", 'code' => 400];
        }

        
        if ($aArgs['type'] == 'smtp') {
            $check = Validator::stringType()->notEmpty()->validate($aArgs['host']);
            $check = $check && Validator::intVal()->notEmpty()->validate($aArgs['port']);
            $check = $check && Validator::stringType()->notEmpty()->validate($aArgs['user']);
            $check = $check && Validator::stringType()->notEmpty()->validate($aArgs['password']);
            $check = $check && Validator::boolType()->validate($aArgs['auth']);
            $check = $check && Validator::stringType()->validate($aArgs['secure']);
            $check = $check && Validator::stringType()->validate($aArgs['from']);
            if (!$check) {
                return ['errors' => "{$aArgs['mode']} configuration data is missing", 'code' => 400];
            }
        }

        return ['success' => 'success'];
    }
}
