<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Entity Controller
* @author dev@maarch.org
*/

namespace Entity\controllers;

use Basket\models\BasketModel;
use Core\Models\ServiceModel;
use Entity\models\EntityModel;
use Entity\models\UserEntityModel;
use Resource\models\ResModel;
use Slim\Http\Request;
use Slim\Http\Response;

class EntityController
{
    public function get(Request $request, Response $response)
    {
        $entities = EntityModel::getAllowedEntitiesByUserId(['userId' => $GLOBALS['userId']]);
        foreach ($entities as $key => $entity) {
            $entities[$key]['users'] = EntityModel::getUsersById(['id' => $entity['entity_id'], 'select' => ['users.user_id', 'users.firstname', 'users.lastname']]);
        }

        return $response->withJson(['entities' => $entities]);
    }

    public function reassignEntity(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'manage_entities', 'userId' => $GLOBALS['userId'], 'location' => 'entities', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        ResModel::update(['set' => ['destination' => $aArgs['newEntityId']], 'where' => ['destination = ?', 'status != ?'], 'data' => [$aArgs['id'], 'DEL']]);

        $users = UserEntityModel::get(['select' => ['user_id', 'entity_id', 'primary_entity'], 'where' => ['entity_id = ? OR entity_id = ?'], 'data' => [$aArgs['id'], $aArgs['newEntityId']]]);
        $tmpUsers = [];
        $doubleUsers = [];
        foreach ($users as $user) {
            if (in_array($user['user_id'], $tmpUsers)) {
                $doubleUsers[] = $user['user_id'];
            }
            $tmpUsers[] = $user['user_id'];
        }
        foreach ($users as $user) {
            if (in_array($user['user_id'], $doubleUsers)) {
                if ($user['entity_id'] == $aArgs['id'] && $user['primary_entity'] == 'N') {
                    UserEntityModel::delete(['where' => ['user_id = ?', 'entity_id = ?'], 'data' => [$user['user_id'], $aArgs['id']]]);
                } elseif ($user['entity_id'] == $aArgs['id'] && $user['primary_entity'] == 'Y') {
                    UserEntityModel::delete(['where' => ['user_id = ?', 'entity_id = ?'], 'data' => [$user['user_id'], $aArgs['newEntityId']]]);
                }
            }
        }

        UserEntityModel::update(['set' => ['entity_id = ?'], 'where' => ['entity_id = ?'], 'data' => [$aArgs['newEntityId'], $aArgs['id']]]);


        $entities = EntityModel::get(['select' => ['entity_id', 'parent_entity_id'], 'where' => ['parent_entity_id = ?'], 'data' => [$aArgs['id']]]);
        foreach ($entities as $entity) {
            if ($entity['entity_id'] = $aArgs['newEntityId']) {
                $entityToReplace = EntityModel::getById(['entityId' => $aArgs['id'], 'select' => ['parent_entity_id']]);
                EntityModel::update(['set' => ['parent_entity_id' => $entityToReplace['parent_entity_id']], 'where' => ['entity_id = ?'], 'data' => [$aArgs['newEntityId']]]);
            } else {
                EntityModel::update(['set' => ['parent_entity_id' => $aArgs['newEntityId']], 'where' => ['entity_id = ?'], 'data' => [$entity['entity_id']]]);
            }
        }

        BasketModel::updateGroupActionRedirect(['set' => ['entity_id' => $aArgs['newEntityId']], 'where' => ['entity_id = ?'], 'data' => [$aArgs['id']]]);
        // TODO listinstance

        return $response->withJson(['success' => 'success']);
    }
}
