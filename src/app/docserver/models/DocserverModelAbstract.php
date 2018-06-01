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

namespace Docserver\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class DocserverModelAbstract
{
    public static function get(array $aArgs = [])
    {
        ValidatorModel::arrayType($aArgs, ['select']);

        $aDocservers = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['docservers'],
        ]);

        return $aDocservers;
    }

    public static function getById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);

        $aDocserver = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['docservers'],
            'where'     => ['docserver_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        if (empty($aDocserver[0])) {
            return [];
        }

        return $aDocserver[0];
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

    public static function getByCollId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['collId']);
        ValidatorModel::stringType($aArgs, ['collId']);
        ValidatorModel::boolType($aArgs, ['priority']);

        $data = [
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['docservers'],
            'where'     => ['coll_id = ?'],
            'data'      => [$aArgs['collId']]
        ];
        if (!empty($aArgs['priority'])) {
            $data['order_by'] = ['priority_number'];
        }
        $aReturn = DatabaseModel::select($data);

        if (!empty($aArgs['priority'])) {
            return $aReturn[0];
        }

        return $aReturn;
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['docserver_id', 'docserver_type_id', 'device_label', 'path_template', 'coll_id', 'size_limit_number', 'priority_number', 'adr_priority_number', 'is_readonly']);
        ValidatorModel::stringType($aArgs, ['docserver_id', 'docserver_type_id', 'device_label', 'path_template', 'coll_id', 'is_readonly']);
        ValidatorModel::intVal($aArgs, ['size_limit_number', 'priority_number', 'adr_priority_number']);

        DatabaseModel::insert([
            'table'         => 'docservers',
            'columnsValues' => [
                'docserver_id'          => $aArgs['docserver_id'],
                'docserver_type_id'     => $aArgs['docserver_type_id'],
                'device_label'          => $aArgs['device_label'],
                'path_template'         => $aArgs['path_template'],
                'coll_id'               => $aArgs['coll_id'],
                'size_limit_number'     => $aArgs['size_limit_number'],
                'priority_number'       => $aArgs['priority_number'],
                'adr_priority_number'   => $aArgs['adr_priority_number'],
                'is_readonly'           => $aArgs['is_readonly'],
                'creation_date'         => 'CURRENT_TIMESTAMP'
            ]
        ]);

        return true;
    }

    public static function update(array $aArgs)
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

    public static function delete(array $aArgs)
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

    public static function getDocserverToInsert(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['collId']);
        ValidatorModel::stringType($aArgs, ['collId', 'typeId']);

        if (empty($aArgs['typeId'])) {
            $aArgs['typeId'] = 'DOC';
        }

        $aDocserver = DatabaseModel::select([
            'select'    => ['*'],
            'table'     => ['docservers'],
            'where'     => ['is_readonly = ?', 'coll_id = ?', 'docserver_type_id = ?'],
            'data'      => ['N', $aArgs['collId'], $aArgs['typeId']],
            'order_by'  => ['priority_number'],
            'limit'     => 1,
        ]);

        if (empty($aDocserver[0])) {
            return [];
        }

        return $aDocserver[0];
    }
}
