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

use Core\Models\DatabaseModel;
use Core\Models\UserModel;
use Core\Models\ValidatorModel;

class EntityModelAbstract
{
    public static function get(array $aArgs = [])
    {
        $aEntities = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['entities'],
            'where'     => ['enabled = ?'],
            'data'      => ['Y']
        ]);

        return $aEntities;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['entityId']);

        if (is_array($aArgs['entityId'])) {
            $where = ['entity_id in (?)'];
        } else {
            ValidatorModel::stringType($aArgs, ['entityId']);
            $where = ['entity_id = ?'];
        }

        $aEntities = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['entities'],
            'where'     => $where,
            'data'      => [$aArgs['entityId']]
        ]);

        if (empty($aEntities[0])) {
            return [];
        } elseif (is_array($aArgs['entityId'])) {
            return $aEntities;
        } else {
            return $aEntities[0];
        }
    }

    public static function getByEmail(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['email']);
        ValidatorModel::stringType($aArgs, ['email']);

        $aReturn = DatabaseModel::select([
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
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users_entities'],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        return $aReturn;
    }

    private static function getEntityChilds(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['entityId']);
        ValidatorModel::stringType($aArgs, ['entityId']);

        $aReturn = DatabaseModel::select([
            'select'    => ['entity_id'],
            'table'     => ['entities'],
            'where'     => ['parent_entity_id = ?'],
            'data'      => [$aArgs['entityId']]
        ]);

        $entities = [$aArgs['entityId']];
        foreach ($aReturn as $value) {
            $entities = array_merge($entities, EntityModel::getEntityChilds(['entityId' => $value['entity_id']]));
        }

        return $entities;
    }

    public static function getAllEntitiesByUserId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $aReturn = UserModel::getEntitiesById(['userId' => $aArgs['userId']]);

        $entities = [];
        foreach ($aReturn as $value) {
            $entities = array_merge($entities, EntityModel::getEntityChilds(['entityId' => $value['entity_id']]));
        }
        
        return array_unique($entities);
    }

    public static function getAvailableEntitiesForAdministratorByUserId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'administratorUserId']);
        ValidatorModel::stringType($aArgs, ['userId', 'administratorUserId']);

        if ($aArgs['administratorUserId'] == 'superadmin') {
            $rawEntitiesAllowedForAdministrator = EntityModel::get(['select' => ['entity_id']]);
            $entitiesAllowedForAdministrator = [];
            foreach ($rawEntitiesAllowedForAdministrator as $value) {
                $entitiesAllowedForAdministrator[] = $value['entity_id'];
            }
        } else {
            $entitiesAllowedForAdministrator = EntityModel::getAllEntitiesByUserId(['userId' => $aArgs['administratorUserId']]);
        }

        $rawUserEntities = EntityModel::getByUserId(['userId' => $aArgs['userId'], 'select' => ['entity_id']]);

        $userEntities = [];
        foreach ($rawUserEntities as $value) {
            $userEntities[] = $value['entity_id'];
        }

        $allEntities = EntityModel::get(['select' => ['entity_id', 'entity_label']]);

        foreach ($allEntities as $key => $value) {
            if (in_array($value['entity_id'], $userEntities) || !in_array($value['entity_id'], $entitiesAllowedForAdministrator)) {
                $allEntities[$key]['disabled'] = true;
            } else {
                $allEntities[$key]['disabled'] = false;
            }
        }

        return $allEntities;
    }

}
