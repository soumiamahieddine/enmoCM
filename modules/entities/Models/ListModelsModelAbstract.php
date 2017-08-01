<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ListModelsModelAbstract
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Entities\Models;

use Core\Models\DatabaseModel;
use Core\Models\ValidatorModel;

class ListModelsModelAbstract
{
    public static function update(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['set', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table'     => 'listmodels',
            'set'       => $aArgs['set'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data']
        ]);

        return true;
    }

    public static function getDiffListByUsersId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['users_id', 'object_type', 'item_mode']);
        ValidatorModel::arrayType($aArgs, ['users_id']);
        ValidatorModel::stringType($aArgs, ['object_type', 'item_mode']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listmodels'],
            'where'     => ['item_id in (?)', 'object_type = ?', 'item_mode = ?'],
            'data'      => [$aArgs['users_id'], $aArgs['object_type'], $aArgs['item_mode']],
        ]);

        return $aReturn;
    }

    public static function getDiffListByUserId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['itemId', 'objectType', 'itemMode']);
        ValidatorModel::arrayType($aArgs, ['users_id']);
        ValidatorModel::stringType($aArgs, ['itemId', 'objectType', 'itemMode']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listmodels'],
            'where'     => ['item_id = ?', 'object_type = ?', 'item_mode = ?'],
            'data'      => [$aArgs['itemId'], $aArgs['objectType'], $aArgs['itemMode']],
        ]);

        return $aReturn;
    }
}
