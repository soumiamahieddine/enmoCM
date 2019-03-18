<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief List Instance Controller
 * @author dev@maarch.org
 */

namespace Entity\controllers;

use Entity\models\ListInstanceHistoryDetailModel;
use Entity\models\ListInstanceHistoryModel;
use Entity\models\ListInstanceModel;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator;
use Resource\controllers\ResController;
use Entity\models\EntityModel;
use SrcCore\models\DatabaseModel;
use User\models\UserEntityModel;
use User\models\UserModel;
use Resource\models\ResModel;

class ListInstanceController
{
    public function getById(Request $request, Response $response, array $aArgs)
    {
        $listinstance = ListInstanceModel::getById(['id' => $aArgs['id']]);

        return $response->withJson($listinstance);
    }

    public function getByResId(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['resId']) || !ResController::hasRightByResId(['resId' => $args['resId'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $listInstances = ListInstanceModel::get(['select' => ['*'], 'where' => ['res_id = ?', 'difflist_type = ?'], 'data' => [$args['resId'], 'entity_id']]);
        foreach ($listInstances as $key => $value) {
            if ($value['item_type'] == 'entity_id') {
                $listInstances[$key]['labelToDisplay'] = Entitymodel::getByEntityId(['entityId' => $value['item_id'], 'select' => ['entity_label']])['entity_label'];
                $listInstances[$key]['descriptionToDisplay'] = '';
            } else {
                $listInstances[$key]['labelToDisplay'] = UserModel::getLabelledUserById(['login' => $value['item_id']]);
                $listInstances[$key]['descriptionToDisplay'] = UserModel::getPrimaryEntityByUserId(['userId' => $value['item_id']])['entity_label'];
            }
        }

        return $response->withJson(['listInstance' => $listInstances]);
    }

    public function getVisaCircuitByResId(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }
        $listinstances = ListInstanceModel::getVisaCircuitByResId(['select' => ['listinstance_id', 'sequence', 'item_id', 'item_type', 'firstname as item_firstname', 'lastname as item_lastname', 'entity_label as item_entity', 'viewed', 'process_date', 'process_comment', 'signatory', 'requested_signature'], 'id' => $aArgs['resId']]);
        
        return $response->withJson($listinstances);
    }

    public function getAvisCircuitByResId(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }
        $listinstances = ListInstanceModel::getAvisCircuitByResId(['select' => ['listinstance_id', 'sequence', 'item_id', 'item_type', 'firstname as item_firstname', 'lastname as item_lastname', 'entity_label as item_entity', 'viewed', 'process_date', 'process_comment'], 'id' => $aArgs['resId']]);
        
        return $response->withJson($listinstances);
    }

    public function update(Request $request, Response $response)
    {
        $body = $request->getParsedBody();
        if (!Validator::arrayType()->notEmpty()->validate($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is not set or not an array']);
        }

        DatabaseModel::beginTransaction();

        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId']]);

        foreach ($body as $ListInstanceByRes) {
            if (empty($ListInstanceByRes['resId'])) {
                DatabaseModel::rollbackTransaction();
                return $response->withStatus(400)->withJson(['errors' => 'resId is empty']);
            }

            if (!Validator::intVal()->validate($ListInstanceByRes['resId']) || !ResController::hasRightByResId(['resId' => $ListInstanceByRes['resId'], 'userId' => $GLOBALS['userId']])) {
                DatabaseModel::rollbackTransaction();
                return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
            }

            if (empty($ListInstanceByRes['listInstances'])) {
                continue;
            }

            $listInstances = ListInstanceModel::get([
                'select'    => ['*'],
                'where'     => ['res_id = ?', 'difflist_type = ?'],
                'data'      => [$ListInstanceByRes['resId'], $ListInstanceByRes['listInstances'][0]['difflist_type']]
            ]);
            ListInstanceModel::delete([
                'where' => ['res_id = ?', 'difflist_type = ?'],
                'data'  => [$ListInstanceByRes['resId'], $ListInstanceByRes['listInstances'][0]['difflist_type']]
            ]);

            foreach ($ListInstanceByRes['listInstances'] as $instance) {
                $listControl = ['res_id', 'item_id', 'item_type', 'item_mode', 'difflist_type'];
                foreach($listControl as $itemControl){
                    if (empty($instance[$itemControl])) {
                        return $response->withStatus(400)->withJson(['errors' => $itemControl . ' are empty']);
                    }
                }

                unset($instance['listinstance_id']);
                unset($instance['requested_signature']);
                unset($instance['signatory']);

                if ($instance['item_type'] == 'user_id') {
                    $user = UserModel::getByLogin(['login' => $instance['item_id']]);
                    if (empty($user) || $user['status'] != "OK") {
                        DatabaseModel::rollbackTransaction();
                        return $response->withStatus(400)->withJson(['errors' => 'User not found or not active']);
                    }
                } elseif ($instance['item_type'] == 'entity_id') {
                    $entity = EntityModel::getByEntityId(['entityId' => $instance['item_id']]);
                    if (empty($entity) || $entity['enabled'] != "Y") {
                        DatabaseModel::rollbackTransaction();
                        return $response->withStatus(400)->withJson(['errors' => 'Entity not found or not active']);
                    }
                }

                ListInstanceModel::create($instance);

                if ($instance['item_mode'] == 'dest') {
                    $set = ['dest_user' => $instance['item_id']];
                    $changeDestination = true;
                    $entities = UserEntityModel::get(['select' => ['entity_id', 'primary_entity'], 'where' => ['user_id = ?'], 'data' => [$instance['item_id']]]);
                    $resource = ResModel::getById(['select' => ['destination'], 'resId' => $instance['res_id']]);
                    foreach ($entities as $entity) {
                        if ($entity['entity_id'] == $resource['destination']) {
                            $changeDestination = false;
                        }
                        if ($entity['primary_entity'] == 'Y') {
                            $primaryEntity = $entity['entity_id'];
                        }
                    }
                    if ($changeDestination && !empty($primaryEntity)) {
                        $set['destination'] = $primaryEntity;
                    }

                    ResModel::update([
                        'set'   => $set,
                        'where' => ['res_id = ?'],
                        'data'  => [$instance['res_id']]
                    ]);
                }
            }

            $listInstanceHistoryId = ListInstanceHistoryModel::create(['resId' => $ListInstanceByRes['resId'], 'userId' => $currentUser['id']]);
            foreach ($listInstances as $listInstance) {
                ListInstanceHistoryDetailModel::create([
                    'listinstance_history_id'   => $listInstanceHistoryId,
                    'res_id'                    => $listInstance['res_id'],
                    'sequence'                  => $listInstance['sequence'],
                    'item_id'                   => $listInstance['item_id'],
                    'item_type'                 => $listInstance['item_type'],
                    'item_mode'                 => $listInstance['item_mode'],
                    'added_by_user'             => $listInstance['added_by_user'],
                    'added_by_entity'           => $listInstance['added_by_entity'],
                    'difflist_type'             => $listInstance['difflist_type'],
                    'process_date'              => $listInstance['process_date'],
                    'process_comment'           => $listInstance['process_comment']
                ]);
            }
        }

        DatabaseModel::commitTransaction();

        return $response->withStatus(204);
    }
}
