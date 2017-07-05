<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Status Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

class StatusModelAbstract
{
    public static function getList()
    {
        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['status'],
            'order_by'  => ['identifier']
        ]);

        return $aReturn;
    }

    public static function getStatusLang()
    {
        $aLang = LangModel::getStatusLang();
        return $aLang;
    }

    public static function getById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['status'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return $aReturn;
    }

    public static function getByIdentifier(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['identifier']);
        ValidatorModel::intVal($aArgs, ['identifier']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['status'],
            'where'     => ['identifier = ?'],
            'data'      => [$aArgs['identifier']]
        ]);

        return $aReturn;
    }

    public static function create(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'label_status']);
        ValidatorModel::stringType($aArgs, ['id', 'label_status']);

        $aReturn = DatabaseModel::insert([
            'table'         => 'status',
            'columnsValues' => $aArgs
            ]);

        return $aReturn;
    }

    public static function update(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['label_status', 'identifier']);
        ValidatorModel::intVal($aArgs, ['identifier']);

        $where['identifier'] = $aArgs['identifier'];
        unset($aArgs['id']);
        unset($aArgs['identifier']);

        $aReturn = DatabaseModel::update([
            'table' => 'status',
            'set'   => $aArgs,
            'where' => ['identifier = ?'],
            'data'  => [$where['identifier']]
        ]);

        return $aReturn;
    }

    public static function delete(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['identifier']);
        ValidatorModel::intVal($aArgs, ['identifier']);

        $aReturn = DatabaseModel::delete([
                'table' => 'status',
                'where' => ['identifier = ?'],
                'data'  => [$aArgs['identifier']]
            ]);

        return $aReturn;
    }

}
