<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Adr Model
 * @author dev@maarch.org
 */

namespace Convert\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class AdrModel
{
    public static function getConvertedDocumentById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'type', 'collId']);
        ValidatorModel::intVal($aArgs, ['resId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        if ($aArgs['collId'] == 'letterbox_coll') {
            $table = "adr_letterbox";
        } else {
            $table = "adr_attachments";
        }

        $document = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => [$table],
            'where'     => ['res_id = ?', 'type = ?'],
            'data'      => [$aArgs['resId'], $aArgs['type']],
        ]);

        if (empty($document[0])) {
            return [];
        }

        return $document[0];
    }

    public static function getTypedDocumentAdrByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'type']);
        ValidatorModel::intVal($aArgs, ['resId']);
        ValidatorModel::stringType($aArgs, ['type']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $adr = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['adr_letterbox'],
            'where'     => ['res_id = ?', 'type = ?'],
            'data'      => [$aArgs['resId'], $aArgs['type']]
        ]);

        if (empty($adr[0])) {
            return [];
        }

        return $adr[0];
    }

    public static function getTypedAttachAdrByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'type']);
        ValidatorModel::intVal($aArgs, ['resId']);
        ValidatorModel::stringType($aArgs, ['type']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $adr = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['adr_attachments'],
            'where'     => ['res_id = ?', 'type = ?'],
            'data'      => [$aArgs['resId'], $aArgs['type']]
        ]);

        if (empty($adr[0])) {
            return [];
        }

        return $adr[0];
    }

    public static function createDocumentAdr(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'docserverId', 'path', 'filename', 'type']);
        ValidatorModel::stringType($aArgs, ['docserverId', 'path', 'filename', 'type', 'fingerprint']);
        ValidatorModel::intVal($aArgs, ['resId']);

        DatabaseModel::insert([
            'table'         => 'adr_letterbox',
            'columnsValues' => [
                'res_id'        => $aArgs['resId'],
                'type'          => $aArgs['type'],
                'docserver_id'  => $aArgs['docserverId'],
                'path'          => $aArgs['path'],
                'filename'      => $aArgs['filename'],
                'fingerprint'   => empty($aArgs['fingerprint']) ? null : $aArgs['fingerprint'],
            ]
        ]);

        return true;
    }

    public static function createAttachAdr(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'docserverId', 'path', 'filename', 'type']);
        ValidatorModel::stringType($aArgs, ['docserverId', 'path', 'filename', 'type', 'fingerprint']);
        ValidatorModel::intVal($aArgs, ['resId']);

        DatabaseModel::insert([
            'table'         => 'adr_attachments',
            'columnsValues' => [
                'res_id'        => $aArgs['resId'],
                'type'          => $aArgs['type'],
                'docserver_id'  => $aArgs['docserverId'],
                'path'          => $aArgs['path'],
                'filename'      => $aArgs['filename'],
                'fingerprint'   => empty($aArgs['fingerprint']) ? null : $aArgs['fingerprint'],
            ]
        ]);
        return true;
    }

    public static function deleteDocumentAdr(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['where', 'data']);
        ValidatorModel::arrayType($aArgs, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'adr_letterbox',
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }

    public static function deleteAttachAdr(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        DatabaseModel::delete([
            'table' => 'adr_attachments',
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['resId']]
        ]);

        return true;
    }
}
