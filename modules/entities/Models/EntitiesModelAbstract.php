<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Entities Model
* @author dev@maarch.org
* @ingroup entities
*/

namespace Entities\Models;

use Core\Models\UserModel;

require_once 'apps/maarch_entreprise/services/Table.php';

class EntitiesModelAbstract extends \Apps_Table_Service
{

    public static function getByEmail(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['email']);
        static::checkString($aArgs, ['email']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['entities'],
            'where'     => ['email = ? and enabled = ?'],
            'data'      => [$aArgs['email'], 'Y'],
            'limit'     => 1,
        ]);

        return $aReturn;
    }

    public static function getByUserId(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['user_id']);
        static::checkString($aArgs, ['user_id']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users_entities'],
            'where'     => ['user_id = ? and primary_entity = ?'],
            'data'      => [$aArgs['user_id'], 'Y'],
            'limit'     => 1,
        ]);

        return $aReturn;
    }

    private static function getEntityChilds(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['entityId']);
        static::checkString($aArgs, ['entityId']);

        $aReturn = static::select([
            'select'    => ['entity_id'],
            'table'     => ['entities'],
            'where'     => ['parent_entity_id = ?'],
            'data'      => [$aArgs['entityId']]
        ]);

        $entities = [$aArgs['entityId']];
        foreach ($aReturn as $value) {
            $entities = array_merge($entities, static::getEntityChilds(['entityId' => $value['entity_id']]));
        }

        return $entities;
    }

    public static function getAllEntitiesByUserId(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);

        $aReturn = UserModel::getEntitiesById(['userId' => $aArgs['userId']]);

        $entities = [];
        foreach ($aReturn as $value) {
            $entities = array_merge($entities, static::getEntityChilds(['entityId' => $value['entity_id']]));
        }
        
        return array_unique($entities);
    }
}
