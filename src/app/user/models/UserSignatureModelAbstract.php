<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief User Signature Model Abstract
 * @author dev@maarch.org
 */

namespace User\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class UserSignatureModelAbstract
{
    public static function get(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data']);

        $signatures = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['user_signatures'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data']
        ]);

        return $signatures;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $signature = DatabaseModel::select([
            'select'    => ['id', 'user_serial_id', 'signature_label'],
            'table'     => ['user_signatures'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        return $signature[0];
    }

    public static function getByUserSerialId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userSerialid']);
        ValidatorModel::intVal($aArgs, ['userSerialid']);

        $signatures = DatabaseModel::select([
            'select'    => ['id', 'user_serial_id', 'signature_label'],
            'table'     => ['user_signatures'],
            'where'     => ['user_serial_id = ?'],
            'data'      => [$aArgs['userSerialid']],
            'order_by'  => ['id']
        ]);

        return $signatures;
    }
}
