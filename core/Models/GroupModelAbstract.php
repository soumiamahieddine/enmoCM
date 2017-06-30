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

require_once 'apps/maarch_entreprise/services/Table.php';

class GroupModelAbstract extends \Apps_Table_Service
{
    public static function get(array $aArgs = [])
    {
        $aGroups = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['usergroups'],
            'where'     => ['enabled = ?'],
            'data'      => ['Y']
        ]);

        return $aGroups;
    }

    public static function getById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['groupId']);
        static::checkString($aArgs, ['groupId']);

        $aGroups = static::select([
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
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);

        $rawUserGroups = UserModel::getGroupsById(['userId' => $aArgs['userId']]);

        $userGroups = [];
        foreach ($rawUserGroups as $value) {
            $userGroups[] = $value['group_id'];
        }

        $allGroups = self::get(['select' => ['group_id', 'group_desc']]);

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
