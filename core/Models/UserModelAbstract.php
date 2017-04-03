<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief User Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

require_once 'apps/maarch_entreprise/services/Table.php';

class UserModelAbstract extends \Apps_Table_Service
{
    public static function getByEmail(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['mail']);
        static::checkString($aArgs, ['mail']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => ['mail = ? and status = ?'],
            'data'      => [$aArgs['mail'], 'OK'],
            'limit'     => 1,
        ]);

        return $aReturn;
    }

    public static function getById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']],
        ]);

        return $aReturn[0];
    }

    public static function getSignaturesById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['user_signatures'],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']],
        ]);

        return $aReturn;
    }

    public static function getEmailSignaturesById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users_email_signatures'],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']],
        ]);

        return $aReturn;
    }
}
