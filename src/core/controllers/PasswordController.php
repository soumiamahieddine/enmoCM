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

    public function updateRules(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_password_rules', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $check = Validator::arrayType()->notEmpty()->validate($data['rules']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        foreach ($data['rules'] as $rule) {
            $existingRule = PasswordModel::getRuleById(['id' => $rule['id'], 'select' => [1]]);
            if (empty($existingRule)) {
                continue;
            }

            $check = Validator::intVal()->validate($rule['value']);
            $check = $check && Validator::boolType()->validate($rule['enabled']);
            if (!$check) {
                continue;
            }

            PasswordModel::updateRule($rule);
        }

        HistoryController::add([
            'tableName' => 'password_rules',
            'recordId'  => 'rules',
            'eventType' => 'UP',
            'info'      => _PASSWORD_RULES_UPDATED,
            'moduleId'  => 'core',
            'eventId'   => 'passwordRulesModification',
        ]);

        return $response->withJson(['success' => 'success']);
    }
}
