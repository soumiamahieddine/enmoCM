<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Message Exchange Controller
 * @author dev@maarch.org
 */

namespace MessageExchange\controllers;

use MessageExchange\models\MessageExchangeModel;
use Resource\controllers\ResController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use User\models\UserModel;

class MessageExchangeController
{
    public static function getByResId(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['resId']) || !ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $messagesModel = MessageExchangeModel::get([
            'select' => [
                'message_id', 'date', 'reference', 'type', 'sender_org_name', 'account_id', 'recipient_org_identifier', 'recipient_org_name',
                'reception_date', 'operation_date', 'data', 'res_id_master', 'filename', 'status'
            ],
            'where'  => ['res_id_master = ?', "(type = 'ArchiveTransfer' or reference like '%_ReplySent')"],
            'data'   => [$args['resId']]
        ]);

        $messages = [];
        foreach ($messagesModel as $message) {
            $messageType = 'm2m_' . strtoupper($message['type']);

            $user = UserModel::getLabelledUserById(['login' => $message['account_id']]);
            $sender = $user . ' (' . $message['sender_org_name'] . ')';

            $recipient = $message['recipient_org_name'] . ' (' . $message['recipient_org_identifier'] . ')';

            if ($message['status'] == 'S') {
                $status = 'sent';
            } elseif ($message['status'] == 'E') {
                $status = 'error';
            } elseif ($message['status'] == 'W') {
                $status = 'wait';
            } else {
                $status = 'draft';
            }

            $messages[] = [
                'messageId'     => $message['message_id'],
                'creationDate'  => $message['date'],
                'type'          => $messageType,
                'sender'        => $sender,
                'recipient'     => $recipient,
                'receptionDate' => $message['reception_date'],
                'operationDate' => $message['operation_date'],
                'status'        => $status
            ];
        }

        return $response->withJson(['messageExchanges' => $messages]);
    }
}
