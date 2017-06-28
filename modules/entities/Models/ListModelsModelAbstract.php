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

require_once 'apps/maarch_entreprise/services/Table.php';

class ListModelsModelAbstract extends \Apps_Table_Service
{
    public static function update(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['set', 'where', 'data']);
        static::checkArray($aArgs, ['set', 'where', 'data']);

        $aReturn = parent::update([
            'table'     => 'listmodels',
            'set'       => $aArgs['set'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data']
        ]);
        return $aReturn;
    }

    public static function getDiffListByUsersId(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['users_id']);
        static::checkRequired($aArgs, ['object_type']);
        static::checkRequired($aArgs, ['item_mode']);

        static::checkArray($aArgs, ['users_id']);
        static::checkString($aArgs, ['object_type']);
        static::checkString($aArgs, ['item_mode']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listmodels'],
            'where'     => ['item_id in (?)', 'object_type = ?', 'item_mode = ?'],
            'data'      => [$aArgs['users_id'], $aArgs['object_type'], $aArgs['item_mode']],
        ]);

        return $aReturn;
    }

    public static function getDiffListByUserId(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['itemId']);
        static::checkRequired($aArgs, ['objectType']);
        static::checkRequired($aArgs, ['itemMode']);

        static::checkString($aArgs, ['itemId']);
        static::checkString($aArgs, ['objectType']);
        static::checkString($aArgs, ['itemMode']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listmodels'],
            'where'     => ['item_id = ?', 'object_type = ?', 'item_mode = ?'],
            'data'      => [$aArgs['itemId'], $aArgs['objectType'], $aArgs['itemMode']],
        ]);

        return $aReturn;
    }
}
