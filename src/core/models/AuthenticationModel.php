<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Authentication Model
* @author dev@maarch.org
*/

namespace SrcCore\models;

class AuthenticationModel
{
    public static function authentication(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'password']);
        ValidatorModel::stringType($args, ['userId', 'password']);

        $aReturn = DatabaseModel::select([
            'select'    => ['password'],
            'table'     => ['users'],
            'where'     => ['user_id = ?', 'status != ?', '(locked_until is null OR locked_until < CURRENT_TIMESTAMP)'],
            'data'      => [$args['userId'], 'DEL']
        ]);

        if (empty($aReturn[0])) {
            return false;
        }

        return password_verify($args['password'], $aReturn[0]['password']);
    }

    public static function resetFailedAuthentication(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        DatabaseModel::update([
            'table'     => 'users',
            'set'       => [
                'failed_authentication' => 0,
                'locked_until'          => null,
            ],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        return true;
    }

    public static function increaseFailedAuthentication(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'tentatives']);
        ValidatorModel::stringType($aArgs, ['userId']);
        ValidatorModel::intVal($aArgs, ['tentatives']);

        DatabaseModel::update([
            'table'     => 'users',
            'set'       => [
                'failed_authentication' => $aArgs['tentatives']
            ],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        return true;
    }

    public static function lockUser(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'lockedUntil']);
        ValidatorModel::stringType($aArgs, ['userId']);

        DatabaseModel::update([
            'table' => 'users',
            'set'   => [
                'locked_until'  => date('Y-m-d H:i:s', $aArgs['lockedUntil'])
            ],
            'where' => ['user_id = ?'],
            'data'  => [$aArgs['userId']]
        ]);

        return true;
    }
}
