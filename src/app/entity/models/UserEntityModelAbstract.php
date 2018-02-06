<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief User Entity Model Abstract
* @author dev@maarch.org
*/

namespace Entity\models;

use Core\Models\DatabaseModel;
use Core\Models\UserModel;
use Core\Models\ValidatorModel;

class UserEntityModelAbstract
{
    public static function get(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['select', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data']);

        $users = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => 'users_entities',
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data']
        ]);

        return $users;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['set', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['set', 'where', 'data']);

        DatabaseModel::delete([
            'table' => 'users_entities',
            'set'   => $aArgs['set'],
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['where', 'data']);
        ValidatorModel::arrayType($aArgs, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'users_entities',
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }
}
