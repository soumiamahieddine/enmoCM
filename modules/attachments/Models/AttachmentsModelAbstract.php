<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

namespace Attachments\Models;

require_once 'apps/maarch_entreprise/services/Table.php';

class AttachmentsModelAbstract extends \Apps_Table_Service
{

    public static function getList()
    {
        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['res_attachments'],
        ]);

        return $aReturn;
    }

    public static function getById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['res_attachments'],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return $aReturn;
    }

    public static function create(array $aArgs = [])
    {
        if (empty($aArgs['status'])) {
            $aArgs['status'] = 'NEW';
        }

        if (empty($aArgs['creation_date'])) {
            $aArgs['creation_date'] = date('c');
        }

        if (empty($aArgs['typist'])) {
            $aArgs['typist'] = empty($_SESSION['user']['UserId'])?'auto':$_SESSION['user']['UserId'];
        }

        if (empty($aArgs['fingerprint'])) {
            throw new \Exception('fingerprint empty');
        }

        if (empty($aArgs['type_id'])) {
            $aArgs['type_id'] = 0;
        }

        $aArgs['title'] = strtolower($aArgs['title']);

        $aReturn = static::insertInto($aArgs, 'status');

        return $aReturn;
    }

    public static function update(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        $where['id'] = $aArgs['id'];

        $aReturn = static::updateTable(
            $aArgs, 
            'status',
            $where
        );

        return $aReturn;
    }

    public static function delete(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        $aReturn = static::deleteFrom([
                'table' => 'status',
                'where' => ['id = ?'],
                'data'  => [$aArgs['id']]
            ]);

        return $aReturn;
    }
}
