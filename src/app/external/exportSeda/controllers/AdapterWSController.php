<?php

namespace ExportSeda\controllers;

use MessageExchange\models\MessageExchangeModel;

class AdapterWSController
{
    public function send($messageId, $type)
    {
        $message = MessageExchangeModel::getMessageByIdentifier(['messageId' => $messageId]);
        $res     = TransferController::transfer('maarchcourrier', $message[0]['reference'], $type);

        if ($res['status'] == 1) {
            MessageExchangeModel::updateStatusMessage(['reference' => $message->reference, 'status' => 'E']);
            return $res;
        }

        MessageExchangeModel::updateStatusMessage(['reference' => $message->reference, 'status' => 'S']);
    }
}
