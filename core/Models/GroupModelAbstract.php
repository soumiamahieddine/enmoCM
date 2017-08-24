<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Group Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

class GroupModelAbstract
{
    public static function get(array $aArgs = [])
    {
        $aGroups = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['usergroups'],
            'where'     => ['enabled = ?'],
            'data'      => ['Y'],
            'order_by'  => ['group_desc']
        ]);

        return $aGroups;
    }

    public static function getById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['groupId']);
        ValidatorModel::stringType($aArgs, ['groupId']);

        $aGroups = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['usergroups'],
            'where'     => ['group_id = ?'],
            'data'      => [$aArgs['groupId']]
        ]);

        if (empty($aGroups[0])) {
            return [];
        }

        return $aGroups[0];
    }

    public static function getAvailableGroupsByUserId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $rawUserGroups = UserModel::getGroupsByUserId(['userId' => $aArgs['userId']]);

        $userGroups = [];
        foreach ($rawUserGroups as $value) {
            $userGroups[] = $value['group_id'];
        }

        $allGroups = GroupModel::get(['select' => ['group_id', 'group_desc']]);

        foreach ($allGroups as $key => $value) {
            if (in_array($value['group_id'], $userGroups)) {
                $allGroups[$key]['disabled'] = true;
            } else {
                $allGroups[$key]['disabled'] = false;
            }
        }

        return $allGroups;
    }

}
