<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Password Controller
 *
 * @author dev@maarch.org
 */

namespace SrcCore\controllers;

use Group\models\ServiceModel;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\PasswordModel;

class PasswordController
{
    public function getRules(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_password_rules', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson(['rules' => PasswordModel::getRules()]);
    }

    public function updateRule(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_password_rules', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $rule = PasswordModel::getRuleById(['id' => $aArgs['id'], 'select' => [1]]);
        if (empty($rule)) {
            return $response->withStatus(400)->withJson(['errors' => 'Rule does not exist']);
        }

        $data = $request->getParams();
        $check = Validator::intVal()->validate($data['value']);
        $check = $check && Validator::boolType()->validate($data['enabled']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $data['id'] = $aArgs['id'];
        PasswordModel::updateRule($data);

        HistoryController::add([
            'tableName' => 'password_rules',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _PASSWORD_RULE_UPDATED . " : {$data['label']}",
            'moduleId'  => 'core',
            'eventId'   => 'passwordRuleModification',
        ]);

        return $response->withJson(['success' => 'success']);
    }
}
