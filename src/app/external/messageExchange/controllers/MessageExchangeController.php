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

    public static function getById(Request $request, Response $response, array $args)
    {
        if (!Validator::stringType()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query param id is not a string']);
        }

        $message = MessageExchangeModel::getMessageByIdentifier([
            'select'    => ['*'],
            'messageId' => $args['id']
        ]);

        if (empty($message[0])) {
            return $response->withStatus(404)->withJson(['errors' => 'Message not found']);
        }
        $message = $message[0];

        if (!ResController::hasRightByResId(['resId' => [$message['res_id_master']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $type = $message['type'];
        if (!empty($message['receptionDate'])) {
            $reference = $message['reference'] . '_Reply';
            $type = 'ArchiveTransfer';
        } elseif ($type == 'ArchiveTransferReply') {
            $reference = $message['reference'];
            $type = 'ArchiveTransferReplySent';
        }

        $operationComments = null;
        if (!empty($reference)) {
            $reply = MessageExchangeModel::getMessageByReference(['reference' => $reference]);
            $replyData = json_decode($reply['data'], true);
            $operationComments = $replyData['Comment'];
        }

        $messageData = json_decode($message['data'], true);

        $transferringAgencyMetaData = $messageData['TransferringAgency']['OrganizationDescriptiveMetadata'];
        $from = $transferringAgencyMetaData['Contact'][0]['PersonName'] . ' (' . $transferringAgencyMetaData['Name'] . ')';

        $archivalAgency         = $messageData['ArchivalAgency'];
        $archivalAgencyMetaData = $archivalAgency['OrganizationDescriptiveMetadata'] ?? null;
        $communicationType = $archivalAgencyMetaData['Communication'][0]['value'] ?? null;
        $contactInfo       = $archivalAgencyMetaData['Name'] . ' - <b>' . $archivalAgency['Identifier']['value'] . '</b> - ' . $archivalAgencyMetaData['Contact'][0]['PersonName'];

        if (!empty($archivalAgencyMetaData['Contact'][0]['Address'][0])) {
            $addressInfo = $archivalAgencyMetaData['Contact'][0]['Address'][0]['PostOfficeBox']
                . ' ' . $archivalAgencyMetaData['Contact'][0]['Address'][0]['StreetName']
                . ' ' . $archivalAgencyMetaData['Contact'][0]['Address'][0]['Postcode']
                . ' ' . $archivalAgencyMetaData['Contact'][0]['Address'][0]['CityName']
                . ' ' . $archivalAgencyMetaData['Contact'][0]['Address'][0]['Country'];
            $contactInfo .= ', ' . $addressInfo;
        }

        $body        = $messageData['Comment'][0]['value'] ?? null;
        $object      = $messageData['DataObjectPackage']['DescriptiveMetadata']['ArchiveUnit'][0]['Content']['Title'][0] ?? null;

        $unitIdentifier = MessageExchangeModel::getUnitIdentifierByMessageId(['messageId' => $args['id']]);

        $notes = [];
        $attachments = [];
        $resMasterAttached = false;
        $disposition = [];
        foreach ($unitIdentifier as $unit) {
            if ($unit['tablename'] == 'notes') {
                $notes[] = $unit['res_id'];
            }
            if ($unit['tablename'] == 'res_attachments') {
                $attachments[] = $unit['res_id'];
            }
            if ($unit['tablename'] == 'res_letterbox') {
                $resMasterAttached = true;
            }
            if ($unit['disposition'] == 'body') {
                $disposition = $unit;
            }
        }

        $messageType = 'm2m_' . strtoupper($type);
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

        $messageExchange = [
            'messageId'         => $message['message_id'],
            'creationDate'      => $message['date'],
            'type'              => $messageType,
            'sender'            => $sender,
            'recipient'         => $recipient,
            'receptionDate'     => $message['reception_date'],
            'operationDate'     => $message['operation_date'],
            'status'            => $status,
            'operationComments' => $operationComments,
            'from'              => $from,
            'communicationType' => $communicationType,
            'contactInfo'       => $contactInfo,
            'body'              => $body,
            'object'            => $object,
            'notes'             => $notes,
            'attachments'       => $attachments,
            'resMasterAttached' => $resMasterAttached,
            'disposition'       => $disposition
        ];

        return $response->withJson(['messageExchange' => $messageExchange]);
    }
}