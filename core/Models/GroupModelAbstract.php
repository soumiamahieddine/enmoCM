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
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);

        $aGroups = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['usergroups'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        if (empty($aGroups[0])) {
            return [];
        }

        return $aGroups[0];
    }

    public static function getByGroupId(array $aArgs = [])
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

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'description', 'clause', 'comment']);
        ValidatorModel::stringType($aArgs, ['groupId', 'description', 'clause', 'comment']);

        DatabaseModel::insert([
            'table'     => 'usergroups',
            'columnsValues'     => [
                'group_id'      => $aArgs['groupId'],
                'group_desc'    => $aArgs['description']
            ]
        ]);

        DatabaseModel::insert([
            'table'     => 'security',
            'columnsValues'         => [
                'group_id'          => $aArgs['groupId'],
                'coll_id'           => 'letterbox_coll',
                'where_clause'      => $aArgs['clause'],
                'maarch_comment'    => $aArgs['comment'],
                'where_target'      => 'DOC',
            ]
        ]);

        return true;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'description', 'clause', 'comment']);
        ValidatorModel::stringType($aArgs, ['id', 'description', 'clause', 'comment']);

        DatabaseModel::update([
            'table'     => 'usergroups',
            'set'       => [
                'group_desc'    => $aArgs['description']
            ],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        $group = GroupModel::getById(['id' => $aArgs['id'], 'select' => ['group_id']]);

        DatabaseModel::update([
            'table'     => 'security',
            'set'       => [
                'where_clause'      => $aArgs['clause'],
                'maarch_comment'    => $aArgs['comment'],
            ],
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);

        $group = GroupModel::getById(['id' => $aArgs['id'], 'select' => ['group_id']]);

        DatabaseModel::delete([
            'table'     => 'usergroups',
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table'     => 'usergroup_content',
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);
        DatabaseModel::delete([
            'table'     => 'usergroups_reports',
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);
        DatabaseModel::delete([
            'table'     => 'usergroups_services',
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);
        DatabaseModel::delete([
            'table'     => 'security',
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);
        DatabaseModel::delete([
            'table'     => 'groupbasket',
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);
        DatabaseModel::delete([
            'table'     => 'groupbasket_redirect',
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);
        DatabaseModel::delete([
            'table'     => 'groupbasket_status',
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);

        return true;
    }

    public static function getUsersByGroupId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['groupId']);
        ValidatorModel::stringType($aArgs, ['groupId']);

        $aUsers = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['usergroup_content'],
            'where'     => ['group_id = ?'],
            'data'      => [$aArgs['groupId']]
        ]);

        return $aUsers;
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

    public static function getSecurityByGroupId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['groupId']);
        ValidatorModel::stringType($aArgs, ['groupId']);

        $aData = DatabaseModel::select([
            'select'    => ['where_clause', 'maarch_comment', 'mr_start_date', 'mr_stop_date'],
            'table'     => ['security'],
            'where'     => ['group_id = ?'],
            'data'      => [$aArgs['groupId']]
        ]);

        return $aData[0];
    }

    public static function getServiceById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'serviceId']);
        ValidatorModel::stringType($aArgs, ['groupId', 'serviceId']);

        $service = DatabaseModel::select([
            'select'    => ['group_id', 'service_id'],
            'table'     => ['usergroups_services'],
            'where'     => ['group_id = ?', 'service_id = ?'],
            'data'      => [$aArgs['groupId'], $aArgs['serviceId']]
        ]);

        return $service;
    }

    public static function getServicesById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['groupId']);
        ValidatorModel::stringType($aArgs, ['groupId']);

        $aServices = DatabaseModel::select([
            'select'    => ['service_id'],
            'table'     => ['usergroups_services'],
            'where'     => ['group_id = ?'],
            'data'      => [$aArgs['groupId']]
        ]);

        return $aServices;
    }

    public static function getAllServicesByGroupId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['groupId']);
        ValidatorModel::stringType($aArgs, ['groupId']);


        $rawCheckedServices = GroupModel::getServicesById(['groupId' => $aArgs['groupId']]);
        $checkedServices = [];
        foreach ($rawCheckedServices as $value) {
            $checkedServices[] = $value['service_id'];
        }

        $allServices = ServiceModel::getServicesByXML();

        $services = [];
        foreach ($allServices as $key => $value) {
            $menu = [];
            $administration = [];
            $use = [];
            foreach ($value as $value2) {
                if (!$value2['system_service']) {
                    if (in_array($value2['id'], $checkedServices)) {
                        $value2['checked'] = true;
                    } else {
                        $value2['checked'] = false;
                    }
                    $value2['location'] = $key;
                    if ($value2['servicetype'] == 'menu') {
                        $menu[] = $value2;
                    } elseif ($value2['servicetype'] == 'admin') {
                        $administration[] = $value2;
                    } elseif ($value2['servicetype'] == 'use') {
                        $use[] = $value2;
                    }
                }
            }
            if (!empty($menu)) {
                $services['menu'][] = $menu;
            }
            if (!empty($administration)) {
                $services['administration'][] = $administration;
            }
            if (!empty($use)) {
                $services['use'][] = $use;
            }
        }

        return $services;
    }

    public static function updateServiceById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'serviceId']);
        ValidatorModel::stringType($aArgs, ['groupId', 'serviceId']);
        ValidatorModel::boolType($aArgs, ['checked']);

        if ($aArgs['checked']) {
            DatabaseModel::insert([
                'table'         => 'usergroups_services',
                'columnsValues' => [
                    'group_id'      => $aArgs['groupId'],
                    'service_id'    => $aArgs['serviceId']
                ]
            ]);
        } else {
            DatabaseModel::delete([
                'table'     => 'usergroups_services',
                'where'     => ['group_id = ?', 'service_id = ?'],
                'data'      => [$aArgs['groupId'], $aArgs['serviceId']]
            ]);
        }

        return true;
    }

    public static function reassignUsers(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'newGroupId']);
        ValidatorModel::stringType($aArgs, ['groupId', 'newGroupId']);

        DatabaseModel::update([
            'table'     => 'usergroup_content',
            'set'       => [
                'group_id'  => $aArgs['newGroupId']
            ],
            'where'     => ['group_id = ?'],
            'data'      => [$aArgs['groupId']]
        ]);

        return true;
    }
}
