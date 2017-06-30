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

class EntityModelAbstract extends \Apps_Table_Service
{
    public static function get(array $aArgs = [])
    {
        $aEntities = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['entities'],
            'where'     => ['enabled'],
            'data'      => ['Y']
        ]);

        return $aEntities;
    }

    public static function getById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['entityId']);
        static::checkString($aArgs, ['entityId']);

        $aEntity = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['entities'],
            'where'     => ['entity_id = ?'],
            'data'      => [$aArgs['entityId']]
        ]);

        if (empty($aEntity[0])) {
            return [];
        }

        return $aEntity[0];
    }

    public static function getByEmail(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['email']);
        static::checkString($aArgs, ['email']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['entities'],
            'where'     => ['email = ?', 'enabled = ?'],
            'data'      => [$aArgs['email'], 'Y'],
            'limit'     => 1,
        ]);

        return $aReturn;
    }

    public static function getByUserId(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users_entities'],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']]
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
