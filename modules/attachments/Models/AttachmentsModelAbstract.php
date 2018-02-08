<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

namespace Attachments\Models;

use Core\Models\ValidatorModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;

class AttachmentsModelAbstract
{
    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'isVersion']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['isVersion']);

        if ($aArgs['isVersion'] == 'true') {
            $table = 'res_version_attachments';
        } else {
            $table = 'res_attachments';
        }

        $aAttachment = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => [$table],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        if (empty($aAttachment[0])) {
            return [];
        }

        return $aAttachment[0];
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['format', 'typist', 'creation_date', 'docserver_id', 'path', 'filename', 'fingerprint', 'filesize', 'status']);
        ValidatorModel::stringType($aArgs, ['format', 'typist', 'creation_date', 'docserver_id', 'path', 'filename', 'fingerprint', 'status']);
        ValidatorModel::intVal($aArgs, ['filesize']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'res_attachment_res_id_seq']);
        $aArgs['res_id'] = $nextSequenceId;

        DatabaseModel::insert([
            'table'         => 'res_attachments',
            'columnsValues' => $aArgs
        ]);

        return $nextSequenceId;
    }

    public static function getAttachmentsTypesByXML()
    {
        $customId = CoreConfigModel::getCustomId();

        if (file_exists("custom/{$customId}/apps/maarch_entreprise/xml/entreprise.xml")) {
            $path = "custom/{$customId}/apps/maarch_entreprise/xml/entreprise.xml";
        } else {
            $path = 'apps/maarch_entreprise/xml/entreprise.xml';
        }

        $xmlfile = simplexml_load_file($path);
        $attachmentTypes = [];
        $attachmentTypesXML = $xmlfile->attachment_types;
        if (count($attachmentTypesXML) > 0) {
            foreach ($attachmentTypesXML->type as $value) {
                $label = defined((string) $value->label) ? constant((string) $value->label) : (string) $value->label;
                $attachmentTypes[(string) $value->id] = [
                    'label' => $label,
                    'icon' => (string)$value['icon'],
                    'sign' => (empty($value['sign']) || (string)$value['sign'] == 'true') ? true : false
                ];
            }
        }

        return $attachmentTypes;
    }

    public static function getAttachmentsWithOptions(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['where', 'data']);
        ValidatorModel::arrayType($aArgs, ['where', 'data', 'orderBy']);

        $select = [
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['res_view_attachments'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data'],
        ];
        if (!empty($aArgs['orderBy'])) {
            $select['order_by'] = $aArgs['orderBy'];
        }

        $aReturn = DatabaseModel::select($select);

        return $aReturn;
    }

    public static function getAvailableAttachmentsInByResIdMaster(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['resIdMaster', 'in']);
        ValidatorModel::intVal($aArgs, ['resIdMaster']);
        ValidatorModel::arrayType($aArgs, ['in']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['res_view_attachments'],
            'where'     => ['res_id_master = ?', 'attachment_type in (?)', "status not in ('DEL', 'TMP', 'OBS')"],
            'data'      => [$aArgs['resIdMaster'], $aArgs['in']]
        ]);

        return $aReturn;
    }

    public static function getAvailableAndTemporaryAttachmentsNotInByResIdMaster(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['resIdMaster', 'notIn']);
        ValidatorModel::intVal($aArgs, ['resIdMaster']);
        ValidatorModel::arrayType($aArgs, ['notIn', 'orderBy']);

        $select = [
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['res_view_attachments'],
            'where'     => ['res_id_master = ?', 'attachment_type not in (?)', "status not in ('DEL', 'OBS')", 'in_signature_book = TRUE'],
            'data'      => [$aArgs['resIdMaster'], $aArgs['notIn']],
        ];
        if (!empty($aArgs['orderBy'])) {
            $select['order_by'] = $aArgs['orderBy'];
        }

        $aReturn = DatabaseModel::select($select);

        return $aReturn;
    }

    public static function getObsAttachmentsNotInByResIdMaster(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['resIdMaster', 'notIn']);
        ValidatorModel::intVal($aArgs, ['resIdMaster']);
        ValidatorModel::arrayType($aArgs, ['notIn', 'orderBy']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['res_view_attachments'],
            'where'     => ['res_id_master = ?', 'attachment_type not in (?)', 'status = ?'],
            'data'      => [$aArgs['resIdMaster'], $aArgs['notIn'], 'OBS'],
            'order_by'  => ['relation ASC']
        ]);

        return $aReturn;
    }

    public static function unsignAttachment(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['table', 'resId']);
        ValidatorModel::stringType($aArgs, ['table']);
        ValidatorModel::intVal($aArgs, ['resId']);

        DatabaseModel::update([
            'table'     => $aArgs['table'],
            'set'       => ['status' => 'A_TRA', 'signatory_user_serial_id' => NULL],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['resId']]
        ]);

        DatabaseModel::update([
            'table'     => $aArgs['table'],
            'set'       => ['status' => 'DEL'],
            'where'     => ['origin = ?', 'status != ?'],
            'data'      => ["{$aArgs['resId']},{$aArgs['table']}", 'DEL']
        ]);

        return true;
    }

    public static function setInSignatureBook(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'isVersion']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['isVersion']);
        ValidatorModel::boolType($aArgs, ['inSignatureBook']);

        if ($aArgs['isVersion'] == 'true') {
            $table = 'res_version_attachments';
        } else {
            $table = 'res_attachments';
        }
        if ($aArgs['inSignatureBook']) {
            $aArgs['inSignatureBook'] =  'true';
        } else {
            $aArgs['inSignatureBook'] =  'false';
        }

        DatabaseModel::update([
            'table'     => $table,
            'set'       => [
                'in_signature_book'   => $aArgs['inSignatureBook']
            ],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        return true;
    }

    public static function hasAttachmentsSignedForUserById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'user_serial_id', 'isVersion']);
        ValidatorModel::intVal($aArgs, ['id', 'user_serial_id']);
        ValidatorModel::stringType($aArgs, ['isVersion']);

        if ($aArgs['isVersion'] == 'true') {
            $table = 'res_version_attachments';
        } else {
            $table = 'res_attachments';
        }

        $attachment = DatabaseModel::select([
            'select'    => ['res_id_master'],
            'table'     => [$table],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        $attachments = DatabaseModel::select([
            'select'    => ['res_id_master'],
            'table'     => ['res_view_attachments'],
            'where'     => ['res_id_master = ?', 'signatory_user_serial_id = ?'],
            'data'      => [$attachment[0]['res_id_master'], $aArgs['user_serial_id']],
        ]);

        if (empty($attachments)) {
            return false;
        }

        return true;
    }
}
