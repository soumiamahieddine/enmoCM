<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief DocserverType Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

require_once 'apps/maarch_entreprise/services/Table.php';

class DocserverTypeModelAbstract extends \Apps_Table_Service
{
    public static function getList()
    {
        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['docserver_types'],
        ]);

        return $aReturn;
    }

    public static function getById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['docserver_type_id']);
        static::checkString($aArgs, ['docserver_type_id']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['docserver_types'],
            'where'     => ['docserver_type_id = ?'],
            'data'      => [$aArgs['docserver_type_id']]
        ]);

        return $aReturn;
    }

    public static function create(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['docserver_type_id']);
        static::checkString($aArgs, ['docserver_type_id']);

        $aReturn = static::insertInto($aArgs, 'docserver_types');

        return $aReturn;
    }

    public static function update(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['docserver_type_id']);
        static::checkString($aArgs, ['docserver_type_id']);

        $where['docserver_type_id'] = $aArgs['docserver_type_id'];

        $aReturn = static::updateTable(
            $aArgs,
            'docserver_types',
            $where
        );

        return $aReturn;
    }

    public static function delete(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['docserver_type_id']);
        static::checkString($aArgs, ['docserver_type_id']);

        $aReturn = static::deleteFrom([
                'table' => 'docserver_types',
                'where' => ['docserver_type_id = ?'],
                'data'  => [$aArgs['docserver_type_id']]
            ]);

        return $aReturn;
    }
}
