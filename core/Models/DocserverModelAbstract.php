<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Docserver Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

require_once 'apps/maarch_entreprise/services/Table.php';

class DocserverModelAbstract extends \Apps_Table_Service
{
    public static function getList()
    {
        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['docservers'],
        ]);

        return $aReturn;
    }

    public static function getById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['docservers'],
            'where'     => ['docserver_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return $aReturn;
    }

    public static function create(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['docserver_id']);
        static::checkString($aArgs, ['docserver_id']);

        $aReturn = static::insertInto($aArgs, 'docservers');

        return $aReturn;
    }

    public static function update(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['docserver_id']);
        static::checkString($aArgs, ['docserver_id']);

        $where['docserver_id'] = $aArgs['docserver_id'];

        $aReturn = static::updateTable(
            $aArgs,
            'docservers',
            $where
        );

        return $aReturn;
    }

    public static function delete(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        $aReturn = static::deleteFrom([
                'table' => 'docservers',
                'where' => ['id = ?'],
                'data'  => [$aArgs['id']]
            ]);

        return $aReturn;
    }

    public static function getDocserverToInsert(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['collId']);
        static::checkString($aArgs, ['collId']);

        $aReturn = static::select([
            'select'    => ['*'],
            'table'     => ['docservers'],
            'where'     => ["is_readonly = 'N' and enabled = 'Y' and coll_id = ?"],
            'data'      => [$aArgs['collId']],
            'order_by'  => ['priority_number'],
            'limit'     => 1,
        ]);

        return $aReturn;
    }

    public static function setSize(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        $where['id'] = $aArgs['id'];

        $aReturn = static::updateTable(
            $aArgs,
            'docservers',
            $where
        );

        return $aReturn;
    }
}
