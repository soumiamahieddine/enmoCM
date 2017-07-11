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

class DocserverTypeModelAbstract
{
    public static function getList()
    {
        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['docserver_types'],
        ]);

        return $aReturn;
    }

    public static function getById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['docserver_type_id']);
        ValidatorModel::stringType($aArgs, ['docserver_type_id']);


        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['docserver_types'],
            'where'     => ['docserver_type_id = ?'],
            'data'      => [$aArgs['docserver_type_id']]
        ]);

        return $aReturn;
    }

    public static function create(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['docserver_type_id']);
        ValidatorModel::stringType($aArgs, ['docserver_type_id']);

        DatabaseModel::insert([
            'table'         => 'docserver_types',
            'columnsValues' => $aArgs
        ]);

        return true;
    }

    public static function update(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['docserver_type_id']);
        ValidatorModel::stringType($aArgs, ['docserver_type_id']);

        DatabaseModel::update([
            'table'     => 'docserver_types',
            'set'       => $aArgs,
            'where'     => ['docserver_type_id = ?'],
            'data'      => [$aArgs['docserver_type_id']]
        ]);

        return true;
    }

    public static function delete(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['docserver_type_id']);
        ValidatorModel::stringType($aArgs, ['docserver_type_id']);

        DatabaseModel::delete([
                'table' => 'docserver_types',
                'where' => ['docserver_type_id = ?'],
                'data'  => [$aArgs['docserver_type_id']]
        ]);

        return true;
    }
}
