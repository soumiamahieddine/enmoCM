<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief List Instance Model Abstract
 * @author dev@maarch.org
 */

namespace Entity\models;

use Core\Models\DatabaseModel;
use Core\Models\ValidatorModel;

class ListInstanceModelAbstract
{
    public static function get(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data']);

        $aListInstances = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['listinstance'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data']
        ]);

        return $aListInstances;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aListinstance = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance'],
            'where'     => ['listinstance_id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        if (empty($aListinstance[0])) {
            return [];
        }

        return $aListinstance[0];
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['set', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['set', 'where', 'data']);

        DatabaseModel::delete([
            'table' => 'listinstance',
            'set'   => $aArgs['set'],
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }

    public static function setSignatory(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'signatory', 'userId']);
        ValidatorModel::intVal($aArgs, ['resId']);
        ValidatorModel::stringType($aArgs, ['signatory', 'userId']);

        DatabaseModel::update([
            'table'     => 'listinstance',
            'set'       => [
                'signatory' => $aArgs['signatory']
            ],
            'where'     => ['res_id = ?', 'item_id = ?', 'difflist_type = ?'],
            'data'      => [$aArgs['resId'], $aArgs['userId'], 'VISA_CIRCUIT'],
        ]);

        return true;
    }

    public static function getCurrentStepByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aListinstance = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance'],
            'where'     => ['res_id = ?', 'difflist_type = ?', 'process_date is null'],
            'data'      => [$aArgs['resId'], 'VISA_CIRCUIT'],
            'order_by'  => ['listinstance_id ASC'],
            'limit'     => 1
        ]);

        if (empty($aListinstance[0])) {
            return [];
        }

        return $aListinstance[0];
    }
}
