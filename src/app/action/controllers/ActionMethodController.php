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

use Entity\controllers\ListInstanceController;
use Entity\models\ListInstanceModel;
use History\controllers\HistoryController;
use Note\models\NoteModel;
use Resource\models\ResModel;
use Action\models\ResMarkAsReadModel;
use Action\models\BasketPersistenceModel;
use Action\models\ActionModel;
use SrcCore\models\ValidatorModel;
use SrcCore\models\CurlModel;
use AcknowledgementReceipt\models\AcknowledgementReceiptModel;
use MessageExchange\controllers\MessageExchangeReviewController;
use User\models\UserModel;

class ActionMethodController
{
    use AcknowledgementReceiptTrait;
    use ShippingTrait;
    use ExternalSignatoryBookTrait;

    const COMPONENTS_ACTIONS = [
        'confirmAction'                         => null,
        'closeMailAction'                       => 'closeMailAction',
        'redirectAction'                        => 'redirect',
        'closeAndIndexAction'                   => 'closeAndIndexAction',
        'updateDepartureDateAction'             => 'updateDepartureDateAction',
        'enabledBasketPersistenceAction'        => 'enabledBasketPersistenceAction',
        'disabledBasketPersistenceAction'       => 'disabledBasketPersistenceAction',
        'resMarkAsReadAction'                   => 'resMarkAsReadAction',
        'sendExternalSignatoryBookAction'       => 'sendExternalSignatoryBookAction',
        'createAcknowledgementReceiptsAction'   => 'createAcknowledgementReceipts',
        'updateAcknowledgementSendDateAction'   => 'updateAcknowledgementSendDateAction',
        'sendShippingAction'                    => 'createMailevaShippings'
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

    public static function redirect(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId', 'data']);
        ValidatorModel::intVal($args, ['resId']);
        ValidatorModel::arrayType($args, ['data']);

        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $listInstances = [];
        if (!empty($args['data']['onlyRedirectDest'])) {
            if (count($args['data']['listInstances']) == 1) {
                $listInstances = ListInstanceModel::get(['select' => ['*'], 'where' => ['res_id = ?', 'difflist_type = ?', 'item_mode != ?'], 'data' => [$args['resId'], 'entity_id', 'dest']]);
            }
        }

        $listInstances = array_merge($listInstances, $args['data']['listInstances']);
        $controller = ListInstanceController::updateListInstance(['data' => [['resId' => $args['resId'], 'listInstances' => $listInstances]], 'userId' => $currentUser['id']]);
        if (!empty($controller['errors'])) {
            return ['errors' => [$controller['errors']]];
        }

        return true;
    }
}
