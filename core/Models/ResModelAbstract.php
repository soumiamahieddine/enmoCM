<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Res Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

require_once 'apps/maarch_entreprise/services/Table.php';

class ResModelAbstract extends \Apps_Table_Service
{
    /**
     * Retrieve info of resId
     * @param  $resId integer
     * @param  $table string
     * @param  $select string
     * @return array $res
     */
    public static function getById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['resId']);
        static::checkNumeric($aArgs, ['resId']);

        if (!empty($aArgs['table'])) {
            $table = $aArgs['table'];
        } else {
            $table = 'res_letterbox';
        }

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => [$table],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['resId']],
            'order_by'  => [$aArgs['orderBy']]
        ]);

        return $aReturn;
    }

    /**
     * Retrieve info of last resId
     * @param  $table string
     * @param  $select string
     * @return array $res
     */
    public static function getLastId(array $aArgs = [])
    {
        if (!empty($aArgs['table'])) {
            $table = $aArgs['table'];
        } else {
            $table = 'res_letterbox';
        }

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => [$table],
            'data'      => [$aArgs['resId']],
            'order_by'  => ['res_id desc'],
            'limit'     => 1,
        ]);

        return $aReturn;
    }

    /**
     * Retrieve info of resId by path
     * @param  $docserverId string
     * @param  $path string
     * @param  $filename string
     * @param  $table string
     * @param  $select string
     * @return array $res
     */
    public static function getByPath(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['docserverId']);
        static::checkRequired($aArgs, ['path']);
        static::checkRequired($aArgs, ['filename']);

        if (!empty($aArgs['table'])) {
            $table = $aArgs['table'];
        } else {
            $table = 'res_letterbox';
        }

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => [$table],
            'where'     => ['docserver_id = ? and path = ? and filename = ?'],
            'data'      => [$aArgs['docserverId'], $aArgs['path'], $aArgs['filename']],
            'order_by'  => ['res_id desc'],
        ]);

        return $aReturn;
    }

    /**
     * insert into a resTable
     * @param  $resId integer
     * @param  $table string
     * @return boolean $status
     */
    public static function create(array $aArgs = [])
    {
        if (empty($aArgs['table'])) {
            $aArgs['table'] = 'res_letterbox';
        }

        $aReturn = static::insertInto($aArgs['data'], $aArgs['table']);

        return $aReturn;
    }

    /**
     * deletes into a resTable
     * @param  $resId integer
     * @param  $table string
     * @return boolean $status
     */
    public static function delete(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkNumeric($aArgs, ['id']);

        if (empty($aArgs['table'])) {
            $aArgs['table'] = 'res_letterbox';
        }

        $aReturn = static::deleteFrom([
                'table' => $aArgs['table'],
                'where' => ['res_id = ?'],
                'data'  => [$aArgs['id']]
            ]);

        return $aReturn;
    }

    public static function isLockForCurrentUser(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['resId']);
        static::checkNumeric($aArgs, ['resId']);


        $aReturn = static::select([
            'select'    => ['locker_user_id', 'locker_time'],
            'table'     => ['res_letterbox'],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['resId']]
        ]);

        if (empty($aReturn[0]['locker_user_id'] || empty($aReturn[0]['locker_time']))) {
            return false;
        } elseif ($aReturn[0]['locker_user_id'] == $_SESSION['user']['UserId']) {
            return false;
        } elseif (strtotime($aReturn[0]['locker_time']) < time()) {
            return false;
        }

        return true;
    }
}
