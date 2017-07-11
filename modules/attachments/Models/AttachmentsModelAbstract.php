<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

namespace Attachments\Models;

use Core\Models\DatabaseModel;
use Core\Models\ValidatorModel;

class AttachmentsModelAbstract
{
    public static function getAttachmentsTypesByXML()
    {
        if (file_exists('custom/' .$_SESSION['custom_override_id']. '/apps/maarch_entreprise/xml/entreprise.xml')) {
            $path = 'custom/' .$_SESSION['custom_override_id']. '/apps/maarch_entreprise/xml/entreprise.xml';
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
            'where'     => ['res_id_master = ?', 'attachment_type not in (?)', "status not in ('DEL', 'OBS')"],
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
            'set'       => ['status' => 'A_TRA'],
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
}
