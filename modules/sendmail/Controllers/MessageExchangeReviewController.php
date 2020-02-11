<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Message Exchange Review Controller
 *
 * @author dev@maarch.org
 * @ingroup core
 */

namespace Sendmail\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;

require_once __DIR__.'/../../export_seda/Controllers/ReceiveMessage.php';
require_once 'modules/export_seda/RequestSeda.php';
require_once 'modules/export_seda/Controllers/SendMessage.php';
require_once 'modules/sendmail/Controllers/SendMessageExchangeController.php';

class MessageExchangeReviewController
{
    public function saveMessageExchangeReview(Request $request, Response $response)
    {
        if (empty($GLOBALS['userId'])) {
            return $response->withStatus(401)->withJson(['errors' => 'User Not Connected']);
        }

        $data = $request->getParams();

        if (!ReceiveMessageExchangeController::checkNeededParameters(['data' => $data, 'needed' => ['type']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $tmpName = ReceiveMessageExchangeController::createFile(['base64' => $data['base64'], 'extension' => $data['extension'], 'size' => $data['size']]);
        if (!empty($tmpName['errors'])) {
            return $response->withStatus(400)->withJson($tmpName);
        }

        $receiveMessage = new \ReceiveMessage();
        $tmpPath = CoreConfigModel::getTmpPath();
        $res = $receiveMessage->receive($tmpPath, $tmpName, $data['type']);

        $sDataObject = $res['content'];
        $dataObject = json_decode($sDataObject);
        $RequestSeda = new \RequestSeda();

        $dataObject->TransferringAgency = $dataObject->OriginatingAgency;

        $messageExchange = $RequestSeda->getMessageByReference($dataObject->UnitIdentifier->value);

        if (empty($messageExchange->operation_date)) {
            $RequestSeda->updateOperationDateMessage(['operation_date' => $dataObject->Date, 'message_id' => $messageExchange->message_id]);
        }

        $messageExchangeSaved = \SendMessageExchangeController::saveMessageExchange(['dataObject' => $dataObject, 'res_id_master' => $messageExchange->res_id_master, 'type' => 'ArchiveModificationNotification']);

        return $response->withJson([
            'messageId' => $messageExchangeSaved['messageId'],
        ]);
    }
}
