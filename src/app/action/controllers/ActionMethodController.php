<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ActionController
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Action\controllers;

use Basket\models\BasketModel;
use Basket\models\GroupBasketRedirectModel;
use Entity\models\EntityModel;
use Group\models\GroupModel;
use History\controllers\HistoryController;
use Note\models\NoteModel;
use Resource\controllers\ResourceListController;
use Resource\models\ResModel;
use Action\models\ResMarkAsReadModel;
use Action\models\BasketPersistenceModel;
use Action\models\ActionModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use SrcCore\models\ValidatorModel;
use SrcCore\models\CurlModel;
use AcknowledgementReceipt\models\AcknowledgementReceiptModel;
use MessageExchange\controllers\MessageExchangeReviewController;
use User\models\UserEntityModel;
use User\models\UserModel;

class ActionMethodController
{
    use AcknowledgementReceiptTrait;

    const COMPONENTS_ACTIONS = [
        'confirmAction'                         => null,
        'closeMailAction'                       => 'closeMailAction',
        'closeAndIndexAction'                   => 'closeAndIndexAction',
        'updateDepartureDateAction'             => 'updateDepartureDateAction',
        'enabledBasketPersistenceAction'        => 'enabledBasketPersistenceAction',
        'disabledBasketPersistenceAction'       => 'disabledBasketPersistenceAction',
        'resMarkAsReadAction'                   => 'resMarkAsReadAction',
        'createAcknowledgementReceiptsAction'   => 'createAcknowledgementReceipts',
        'updateAcknowledgementSendDateAction'   => 'updateAcknowledgementSendDateAction'
    ];

    public static function terminateAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'resources', 'basketName']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['resources']);
        ValidatorModel::stringType($aArgs, ['basketName', 'note']);

        $set = ['locker_user_id' => null, 'locker_time' => null, 'modification_date' => 'CURRENT_TIMESTAMP'];

        $action = ActionModel::getById(['id' => $aArgs['id'], 'select' => ['label_action', 'id_status', 'history']]);
        if (!empty($action['id_status']) && $action['id_status'] != '_NOSTATUS_') {
            $set['status'] = $action['id_status'];
        }

        ResModel::update([
            'set'   => $set,
            'where' => ['res_id in (?)'],
            'data'  => [$aArgs['resources']]
        ]);

        foreach ($aArgs['resources'] as $resource) {
            if (!empty(trim($aArgs['note']))) {
                NoteModel::create([
                    'resId'     => $resource,
                    'login'     => $GLOBALS['userId'],
                    'note_text' => $aArgs['note']
                ]);
            }

            if ($action['history'] == 'Y') {
                HistoryController::add([
                    'tableName' => 'res_view_letterbox',
                    'recordId'  => $resource,
                    'eventType' => 'ACTION#' . $resource,
                    'eventId'   => $aArgs['id'],
                    'info'      => "{$aArgs['basketName']} : {$action['label_action']}"
                ]);

                MessageExchangeReviewController::sendMessageExchangeReview(['res_id' => $resource, 'action_id' => $aArgs['id'], 'userId' => $GLOBALS['userId']]);
            }
        }

        return true;
    }

    public static function closeMailAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);
        ValidatorModel::stringType($aArgs, ['note']);

        ResModel::updateExt(['set' => ['closing_date' => 'CURRENT_TIMESTAMP'], 'where' => ['res_id = ?', 'closing_date is null'], 'data' => [$aArgs['resId']]]);

        if (CurlModel::isEnabled(['curlCallId' => 'closeResource'])) {
            $bodyData = [];
            $config = CurlModel::getConfigByCallId(['curlCallId' => 'closeResource']);
            $configResource = CurlModel::getConfigByCallId(['curlCallId' => 'sendResourceToExternalApplication']);

            $resource = ResModel::getOnView(['select' => ['doc_' . $configResource['return']['value']], 'where' => ['res_id = ?'], 'data' => [$aArgs['resId']]]);

            if (!empty($resource[0]['doc_' . $configResource['return']['value']])) {
                if (!empty($config['inObject'])) {
                    foreach ($config['objects'] as $object) {
                        $select = [];
                        $tmpBodyData = [];
                        foreach ($object['rawData'] as $value) {
                            if ($value == $configResource['return']['value']) {
                                $select[] = 'doc_' . $configResource['return']['value'];
                            } elseif ($value != 'note') {
                                $select[] = $value;
                            }
                        }

                        $document = ResModel::getOnView(['select' => $select, 'where' => ['res_id = ?'], 'data' => [$aArgs['resId']]]);
                        if (!empty($document[0])) {
                            foreach ($object['rawData'] as $key => $value) {
                                if ($value == 'note') {
                                    $tmpBodyData[$key] = empty($aArgs['note']) ? '' : $aArgs['note'];
                                } elseif ($value == $configResource['return']['value']) {
                                    $tmpBodyData[$key] = $document[0]['doc_' . $value];
                                } else {
                                    $tmpBodyData[$key] = $document[0][$value];
                                }
                            }
                        }

                        if (!empty($object['data'])) {
                            $tmpBodyData = array_merge($tmpBodyData, $object['data']);
                        }

                        $bodyData[$object['name']] = $tmpBodyData;
                    }
                }

                CurlModel::exec(['curlCallId' => 'closeResource', 'bodyData' => $bodyData, 'multipleObject' => true, 'noAuth' => true]);
            }
        }

        return true;
    }

    public static function closeAndIndexAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        ResModel::updateExt(['set' => ['closing_date' => 'CURRENT_TIMESTAMP'], 'where' => ['res_id = ?', 'closing_date is null'], 'data' => [$aArgs['resId']]]);

        return true;
    }

    public static function updateAcknowledgementSendDateAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'data']);
        ValidatorModel::intVal($aArgs, ['resId']);

        AcknowledgementReceiptModel::updateSendDate(['send_date' => date('Y-m-d H:i:s', $aArgs['data']['send_date']), 'res_id' => $aArgs['resId']]);

        return true;
    }

    public static function updateDepartureDateAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        ResModel::update(['set' => ['departure_date' => 'CURRENT_TIMESTAMP'], 'where' => ['res_id = ?', 'departure_date is null'], 'data' => [$aArgs['resId']]]);

        return true;
    }

    public static function disabledBasketPersistenceAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        BasketPersistenceModel::delete([
            'where' => ['res_id = ?',  'user_id = ?'],
            'data'  => [$aArgs['resId'], $GLOBALS['userId']]
        ]);

        BasketPersistenceModel::create([
            'res_id'        => $aArgs['resId'],
            'user_id'       => $GLOBALS['userId'],
            'is_persistent' => 'N'
        ]);

        return true;
    }

    public static function enabledBasketPersistenceAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        BasketPersistenceModel::delete([
            'where' => ['res_id = ?', 'user_id = ?'],
            'data'  => [$aArgs['resId'], $GLOBALS['userId']]
        ]);

        BasketPersistenceModel::create([
            'res_id'        => $aArgs['resId'],
            'user_id'       => $GLOBALS['userId'],
            'is_persistent' => 'Y'
        ]);

        return true;
    }

    public static function resMarkAsReadAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'data']);
        ValidatorModel::intVal($aArgs, ['resId']);

        ResMarkAsReadModel::delete([
            'where' => ['res_id = ?', 'user_id = ?', 'basket_id = ?'],
            'data'  => [$aArgs['resId'], $GLOBALS['userId'], $aArgs['data']['basketId']]
        ]);

        ResMarkAsReadModel::create([
            'res_id'    => $aArgs['resId'],
            'user_id'   => $GLOBALS['userId'],
            'basket_id' => $aArgs['data']['basketId']
        ]);

        return true;
    }

    public static function getRedirectInformations(Request $request, Response $response, array $args)
    {
        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $errors = ResourceListController::listControl(['groupId' => $args['groupId'], 'userId' => $args['userId'], 'basketId' => $args['basketId'], 'currentUserId' => $currentUser['id']]);
        if (!empty($errors['errors'])) {
            return $response->withStatus($errors['code'])->withJson(['errors' => $errors['errors']]);
        }

        $basket = BasketModel::getById(['id' => $args['basketId'], 'select' => ['basket_clause', 'basket_id']]);
        $group = GroupModel::getById(['id' => $args['groupId'], 'select' => ['group_id']]);
        $user = UserModel::getById(['id' => $args['userId'], 'select' => ['user_id']]);


        $keywords = [
            'ALL_ENTITIES'          => '@all_entities',
            'ENTITIES_JUST_BELOW'   => '@immediate_children[@my_primary_entity]',
            'ENTITIES_BELOW'        => '@subentities[@my_entities]',
            'ALL_ENTITIES_BELOW'    => '@subentities[@my_primary_entity]',
            'ENTITIES_JUST_UP'      => '@parent_entity[@my_primary_entity]',
            'MY_ENTITIES'           => '@my_entities',
            'MY_PRIMARY_ENTITY'     => '@my_primary_entity',
            'SAME_LEVEL_ENTITIES'   => '@sisters_entities[@my_primary_entity]'
        ];

        $entityRedirects = GroupBasketRedirectModel::get([
            'select'    => ['entity_id', 'keyword'],
            'where'     => ['basket_id = ?', 'group_id = ?', 'action_id = ?', 'redirect_mode = ?'],
            'data'      => [$basket['basket_id'], $group['group_id'], $args['actionId'], 'ENTITY']
        ]);

        $allowedEntities = [];
        $clauseToProcess = '';
        foreach ($entityRedirects as $entityRedirect) {
            if (!empty($entityRedirect['entity_id'])) {
                $allowedEntities[] = $entityRedirect['entity_id'];
            } elseif (!empty($entityRedirect['keyword'])) {
                if (!empty($keywords[$entityRedirect['keyword']])) {
                    if (!empty($clauseToProcess)) {
                        $clauseToProcess .= ', ';
                    }
                    $clauseToProcess .= $keywords[$entityRedirect['keyword']];
                }
            }
        }

        if (!empty($clauseToProcess)) {
            $preparedClause = PreparedClauseController::getPreparedClause(['clause' => $clauseToProcess, 'login' => $user['user_id']]);
            $preparedEntities = EntityModel::get(['select' => ['entity_id'], 'where' => ['enabled = ?', "entity_id in {$preparedClause}"], 'data' => ['Y']]);
            foreach ($preparedEntities as $preparedEntity) {
                $allowedEntities[] = $preparedEntity['entity_id'];
            }
        }

        $allowedEntities = array_unique($allowedEntities);

        $allEntities = EntityModel::get(['select' => ['id', 'entity_id', 'entity_label', 'parent_entity_id'], 'where' => ['enabled = ?'], 'data' => ['Y'], 'orderBy' => ['parent_entity_id']]);
        foreach ($allEntities as $key => $value) {
            $allEntities[$key]['id'] = $value['entity_id'];
            $allEntities[$key]['serialId'] = $value['id'];
            if (empty($value['parent_entity_id'])) {
                $allEntities[$key]['parent'] = '#';
                $allEntities[$key]['icon'] = "fa fa-building";
            } else {
                $allEntities[$key]['parent'] = $value['parent_entity_id'];
                $allEntities[$key]['icon'] = "fa fa-sitemap";
            }
            if (in_array($value['entity_id'], $allowedEntities)) {
                $allEntities[$key]['allowed'] = true;
                $allEntities[$key]['state']['opened'] = true;
            } else {
                $allEntities[$key]['allowed'] = false;
                $allEntities[$key]['state']['disabled'] = true;
                $allEntities[$key]['state']['opened'] = false;
            }
            $allEntities[$key]['text'] = $value['entity_label'];
        }

        $entityRedirects = GroupBasketRedirectModel::get([
            'select'    => ['entity_id', 'keyword'],
            'where'     => ['basket_id = ?', 'group_id = ?', 'action_id = ?', 'redirect_mode = ?'],
            'data'      => [$basket['basket_id'], $group['group_id'], $args['actionId'], 'USERS']
        ]);

        $allowedEntities = [];
        $clauseToProcess = '';
        foreach ($entityRedirects as $entityRedirect) {
            if (!empty($entityRedirect['entity_id'])) {
                $allowedEntities[] = $entityRedirect['entity_id'];
            } elseif (!empty($entityRedirect['keyword'])) {
                if (!empty($keywords[$entityRedirect['keyword']])) {
                    if (!empty($clauseToProcess)) {
                        $clauseToProcess .= ', ';
                    }
                    $clauseToProcess .= $keywords[$entityRedirect['keyword']];
                }
            }
        }

        if (!empty($clauseToProcess)) {
            $preparedClause = PreparedClauseController::getPreparedClause(['clause' => $clauseToProcess, 'login' => $user['user_id']]);
            $preparedEntities = EntityModel::get(['select' => ['entity_id'], 'where' => ['enabled = ?', "entity_id in {$preparedClause}"], 'data' => ['Y']]);
            foreach ($preparedEntities as $preparedEntity) {
                $allowedEntities[] = $preparedEntity['entity_id'];
            }
        }

        $allowedEntities = array_unique($allowedEntities);

        $users = UserEntityModel::getUsersByEntities(['select' => ['DISTINCT id', 'users.user_id', 'firstname', 'lastname'], 'entities' => $allowedEntities]);

        return $response->withJson(['entities' => $allEntities, 'users' => $users]);
    }
}
