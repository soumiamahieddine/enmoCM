<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Password Model
* @author dev@maarch.org
*/

namespace SrcCore\models;

class PasswordModel
{
    public static function getRules(array $aArgs = [])
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data']);

        $aRules = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['password_rules'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data'],
        ]);

        return $aRules;
    }

    public static function getRuleById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aRules = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['password_rules'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        return $aRules;
    }

    public static function updateRule(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id', 'value']);
        ValidatorModel::boolType($aArgs, ['enabled']);

        DatabaseModel::update([
            'table'     => 'password_rules',
            'set'       => [
                '"value"'   => $aArgs['value'],
                'enabled'   => $aArgs['enabled'],
            ],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return true;
    }
}
