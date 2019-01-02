<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Configuration Model
* @author dev@maarch.org
*/

namespace Email\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class EmailModel
{
    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $email = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['emails'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        if (empty($email[0])) {
            return [];
        }

        return $email[0];
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'userId', 'sender', 'to', 'cc', 'cci', 'object', 'attachmentsId', 'versionAttachmentsId', 'notesId', 'documentLinked', 'isHtml']);
        ValidatorModel::intVal($aArgs, ['resId', 'userId']);
        ValidatorModel::stringType($aArgs, ['sender', 'to', 'cc', 'cci', 'object', 'body', 'attachmentsId', 'versionAttachmentsId', 'notesId', 'messageExchangeId', 'documentLinked', 'isHtml']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'emails_id_seq']);

        DatabaseModel::insert([
            'table'         => 'emails',
            'columnsValues' => [
                'id'                        => $nextSequenceId,
                'res_id'                    => $aArgs['resId'],
                'user_id'                   => $aArgs['userId'],
                'sender'                    => $aArgs['sender'],
                'to'                        => $aArgs['to'],
                'cc'                        => $aArgs['cc'],
                'cci'                       => $aArgs['cci'],
                'object'                    => $aArgs['object'],
                'body'                      => empty($aArgs['body']) ? null : $aArgs['body'],
                'document_linked'           => $aArgs['documentLinked'],
                'attachments_id'            => $aArgs['attachmentsId'],
                'version_attachments_id'    => $aArgs['versionAttachmentsId'],
                'notes_id'                  => $aArgs['notesId'],
                'is_html'                   => $aArgs['isHtml'],
                'status'                    => 'W',
                'message_exchange_id'       => empty($aArgs['messageExchangeId']) ? null : $aArgs['messageExchangeId'],
                'creation_date'             => 'CURRENT_TIMESTAMP'
            ]
        ]);

        return $nextSequenceId;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['set', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'emails',
            'set'   => $aArgs['set'],
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }
}
