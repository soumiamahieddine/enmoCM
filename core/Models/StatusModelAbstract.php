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

require_once 'apps/maarch_entreprise/services/Table.php';

class StatusModelAbstract extends \Apps_Table_Service
{
    public static function getList()
    {
        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['status'],
            'order_by'  => 'identifier'
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
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['status'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return $aReturn;
    }

    public static function getByIdentifier(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['identifier']);
        static::checkNumeric($aArgs, ['identifier']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['status'],
            'where'     => ['identifier = ?'],
            'data'      => [$aArgs['identifier']]
        ]);

        return $aReturn;
    }

    public static function create(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id', 'label_status']);
        static::checkString($aArgs, ['id']);

        $aReturn = static::insertInto($aArgs, 'status');

        return $aReturn;
    }

    public static function update(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['label_status', 'identifier']);
        static::checkNumeric($aArgs, ['identifier']);

        $where['identifier'] = $aArgs['identifier'];
        unset($aArgs['id']);
        unset($aArgs['identifier']);

        $aReturn = static::updateTable(
            $aArgs,
            'status',
            $where
        );

        return $aReturn;
    }

    public static function delete(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['identifier']);
        static::checkNumeric($aArgs, ['identifier']);

        $aReturn = static::deleteFrom([
                'table' => 'status',
                'where' => ['identifier = ?'],
                'data'  => [$aArgs['identifier']]
            ]);

        return $aReturn;
    }

    public static function getStatusImages(array $aArgs = [])
    {
        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['status_images'],
            'order_by'  => 'id'
        ]);

        return $aReturn;
    }
}
