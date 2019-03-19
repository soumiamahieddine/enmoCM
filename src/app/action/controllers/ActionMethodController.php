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

use Attachment\models\AttachmentModel;
use Convert\controllers\ConvertPdfController;
use Docserver\models\DocserverModel;
use Entity\controllers\ListInstanceController;
use History\controllers\HistoryController;
use Note\models\NoteModel;
use Resource\models\ResModel;
use Action\models\ResMarkAsReadModel;
use Action\models\BasketPersistenceModel;
use Action\models\ActionModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;
use SrcCore\models\CurlModel;
use AcknowledgementReceipt\models\AcknowledgementReceiptModel;
use MessageExchange\controllers\MessageExchangeReviewController;
use User\models\UserModel;

class ActionMethodController
{
    use AcknowledgementReceiptTrait;

    const COMPONENTS_ACTIONS = [
        'confirmAction'                         => null,
        'closeMailAction'                       => 'closeMailAction',
        'redirectAction'                        => 'redirect',
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

    public static function redirect(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId', 'data']);
        ValidatorModel::intVal($args, ['resId']);
        ValidatorModel::arrayType($args, ['data']);

        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $controller = ListInstanceController::updateListInstance(['data' => $args['data'], 'userId' => $currentUser['id']]);
        if (!empty($controller['errors'])) {
            return ['errors' => $controller['errors']];
        }

        return true;
    }

    public static function maileva(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        $curlAuth = CurlModel::execSimple([
            'url'           => 'https://api.sandbox.aws.maileva.net/authentication/oauth2/token',
            'basicAuth'     => ['user' => 'd80cafd2bf3b42c79dc84f4dc8e6acea', 'password' => 'aaa7833f3e9e48e68bbe3e727b5b5360'],
            'headers'       => ['Content-Type: application/x-www-form-urlencoded'],
            'method'        => 'POST',
            'queryParams'   => ['grant_type' => 'password', 'username' => 'sandbox.562', 'password' => 'lgileb']
        ]);

        $token = $curlAuth['access_token'];
        $sendingName = CoreConfigModel::uniqueId();

        $curl = CurlModel::execSimple([
            'url'           => 'https://api.sandbox.aws.maileva.net/mail/v1/sendings',
            'bearerAuth'    => ['token' => $token],
            'headers'       => ['Content-Type: application/json'],
            'method'        => 'POST',
            'body'          => ['name' => $sendingName]
        ]);
        $curl = CurlModel::execSimple([
            'url'           => 'https://api.sandbox.aws.maileva.net/mail/v1/sendings',
            'bearerAuth'    => ['token' => $token],
            'method'        => 'GET'
        ]);

        foreach ($curl['sendings'] as $sending) {
            if ($sending['name'] == $sendingName) {
                $sendingId = $sending['id'];
            }
        }

        $attachments = AttachmentModel::getOnView(['select' => ['res_id', 'res_id_version'], 'where' => ['res_id_master = ?'], 'data' => [$args['resId']]]);
        $convertedDocument = ConvertPdfController::getConvertedPdfById(['resId' => $attachments[0]['res_id'], 'collId' => 'attachment_coll', 'isVersion' => false]);

        $docserver = DocserverModel::getByDocserverId(['docserverId' => $convertedDocument['docserver_id'], 'select' => ['path_template']]);
        if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
            //TODO
            return ['errors' => 'Docserver does not exist'];
        }

        $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $convertedDocument['path']) . $convertedDocument['filename'];
        if (!file_exists($pathToDocument)) {
            //TODO
            return ['errors' => 'Document not found on docserver'];
        }

        $curl = CurlModel::execSimple([
            'url'           => "https://api.sandbox.aws.maileva.net/mail/v1/sendings/{$sendingId}/documents",
            'bearerAuth'    => ['token' => $token],
            'headers'       => ['Content-Type: multipart/form-data'],
            'method'        => 'POST',
            'body'          => ['document' => CurlModel::makeCurlFile(['path' => $pathToDocument]), 'metadata' => ['priority' => 0, 'name' => 'tata']],
            'toto'          => 1
        ]);
        $curl = CurlModel::execSimple([
            'url'           => "https://api.sandbox.aws.maileva.net/mail/v1/sendings/{$sendingId}/documents",
            'bearerAuth'    => ['token' => $token],
            'method'        => 'GET'
        ]);

        $curl = CurlModel::execSimple([
            'url'           => "https://api.sandbox.aws.maileva.net/mail/v1/sendings/{$sendingId}/recipients",
            'bearerAuth'    => ['token' => $token],
            'headers'       => ['Content-Type: application/json'],
            'method'        => 'POST',
            'body'          => [
                "address_line_1"    => "La Poste",
                "address_line_2"    => "Me Eva DUPONT",
                "address_line_3"    => "Résidence des Peupliers",
                "address_line_4"    => "33 avenue de Paris",
                "address_line_5"    => "BP 356",
                "address_line_6"    => "75000 Paris",
                "country_code"      => "FR"
            ],
        ]);
        $curl = CurlModel::execSimple([
            'url'           => "https://api.sandbox.aws.maileva.net/mail/v1/sendings/{$sendingId}/options",
            'bearerAuth'    => ['token' => $token],
            'headers'       => ['Content-Type: application/json'],
            'method'        => 'PATCH',
            'body'          => [
                'postage_type'              => "FAST", //ECONOMIC
                'color_printing'            => true,
                'duplex_printing'           => true,
                'optional_address_sheet'    => false
            ],
        ]);
        $curl = CurlModel::execSimple([
            'url'           => "https://api.sandbox.aws.maileva.net/mail/v1/sendings/{$sendingId}/submit",
            'bearerAuth'    => ['token' => $token],
            'headers'       => ['Content-Type: application/json'],
            'method'        => 'POST'
        ]);


        return true;
    }
}
