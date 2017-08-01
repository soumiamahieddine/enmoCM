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

class DocserverModelAbstract
{
    public static function getList()
    {
        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['docservers'],
        ]);

        return $aReturn;
    }

    public static function getById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['docserver_id']);
        ValidatorModel::stringType($aArgs, ['docserver_id']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['docservers'],
            'where'     => ['docserver_id = ?'],
            'data'      => [$aArgs['docserver_id']]
        ]);

        return $aReturn;
    }

    public static function getByTypeId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['docserver_type_id']);
        ValidatorModel::stringType($aArgs, ['docserver_type_id']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['docservers'],
            'where'     => ['docserver_type_id = ?'],
            'data'      => [$aArgs['docserver_type_id']]
        ]);

        return $aReturn[0];
    }

    public static function create(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['docserver_id']);
        ValidatorModel::stringType($aArgs, ['docserver_id']);

        DatabaseModel::insert([
            'table'         => 'docservers',
            'columnsValues' => $aArgs
        ]);

        return true;
    }

    public static function update(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['docserver_id']);
        ValidatorModel::stringType($aArgs, ['docserver_id']);

        DatabaseModel::update([
            'table'     => 'docservers',
            'set'       => $aArgs,
            'where'     => ['docserver_id = ?'],
            'data'      => [$aArgs['docserver_id']]
        ]);

        return true;
    }

    public static function delete(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['docserver_id']);
        ValidatorModel::stringType($aArgs, ['docserver_id']);

        DatabaseModel::delete([
            'table'     => 'docservers',
            'where'     => ['docserver_id = ?'],
            'data'      => [$aArgs['docserver_id']]
        ]);

        return true;
    }

    public static function getDocserverToInsert(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['collId']);
        ValidatorModel::stringType($aArgs, ['collId']);

        $aReturn = DatabaseModel::select([
            'select'    => ['*'],
            'table'     => ['docservers'],
            'where'     => ["is_readonly = 'N' and enabled = 'Y' and coll_id = ?"],
            'data'      => [$aArgs['collId']],
            'order_by'  => ['priority_number'],
            'limit'     => 1,
        ]);

        return $aReturn;
    }
}
