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

class ActionsModelAbstract extends \Apps_Table_Service
{
    public static function getList()
    {
        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['actions'],
        ]);

        return $aReturn;
    }

    public static function getById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['actions'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return $aReturn;
    }

    public static function create(array $aArgs = [])
    {
       
        $aReturn = static::insertInto($aArgs,'actions');
      

        return $aReturn;
    }

    public static function update(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        
        $aReturn = parent::update([
            'table'     => 'actions',
            'set'       => [
                'keyword' => $aArgs['keyword'],          
                'label_action' => $aArgs['label_action'],
                'id_status' => $aArgs['id_status'],
                'action_page' => $aArgs['action_page'],
                'history' => $aArgs['history'],
                'is_folder_action' => $aArgs['is_folder_action'],
                'history' => $aArgs['history']



            ],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

 
        return $aReturn;
    }

    public static function delete(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);

        $aReturn = static::deleteFrom([
                'table' => 'actions',
                'where' => ['id = ?'],
                'data'  => [$aArgs['id']]
            ]);
        return $aReturn;
    }
}

