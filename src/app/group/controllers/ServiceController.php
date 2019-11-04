<?php

namespace Group\controllers;

use Group\models\ServiceModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class ServiceController
{
    const PRIVILEGE_MENU = [
        "admin",
        "adv_search_mlb",
        "entities_print_sep_mlb",
        "reports",
        "save_numeric_package"
    ];

    const PRIVILEGE_ADMIN_ORGANIZATION = [
        "admin_users",
        "admin_groups",
        "manage_entities",
        "admin_listmodels"
    ];

    const PRIVILEGE_ADMIN_CLASSIFYING = [
        "admin_architecture",
        "admin_tag"
    ];

    const PRIVILEGE_ADMIN_PRODUCTION = [
        "admin_baskets",
        "admin_status",
        "admin_actions",
        "admin_contacts",
        "admin_priorities",
        "admin_templates",
        "admin_indexing_models",
        "admin_custom_fields",
        "admin_notif"
    ];

    const PRIVILEGE_ADMIN_SUPERVISION = [
        "update_status_mail",
        "admin_docservers",
        "admin_parameters",
        "admin_password_rules",
        "admin_email_server",
        "admin_shippings",
        "admin_reports",
        "view_history",
        "view_history_batch",
        "admin_update_control",
    ];

    public static function getMenuServicesByUserIdByXml(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $rawServicesStoredInDB = ServiceModel::getByUserId(['userId' => $aArgs['userId']]);
        $servicesStoredInDB = [];
        foreach ($rawServicesStoredInDB as $value) {
            $servicesStoredInDB[] = $value['service_id'];
        }

        $menu = [];
        if (!empty($servicesStoredInDB)) {
            $menu = ServiceModel::getApplicationServicesByUserServices(['userServices' => $servicesStoredInDB, 'type' => 'menu']);
            $menuModules = ServiceModel::getModulesServicesByUserServices(['userServices' => $servicesStoredInDB, 'type' => 'menu']);
            $menu = array_merge($menu, $menuModules);
        }

        $userGroups = UserModel::getGroupsByLogin(['login' => $aArgs['userId']]);
        $indexingGroups = [];
        foreach ($userGroups as $group) {
            if ($group['can_index']) {
                $indexingGroups[] = ['id' => $group['id'], 'label' => $group['group_desc']];
            }
        }
        if (!empty($indexingGroups)) {
            $menu[] = [
                'id'        => 'indexing',
                'style'     => 'fa fa-file-medical',
                'name'      => _INDEXING_MLB,
                'groups'    => $indexingGroups
            ];
        }

        return $menu;
    }

    public static function getMenuPrivilegesByUserId(array $args)
    {
        ValidatorModel::notEmpty($args, ['id']);
        ValidatorModel::intVal($args, ['id']);

        $rawPrivilegesStoredInDB = ServiceModel::getByUser(['id' => $args['id']]);
        $privilegesStoredInDB = array_column($rawPrivilegesStoredInDB, 'service_id');

        $menu = [];
        if (!empty($privilegesStoredInDB)) {
            foreach ($privilegesStoredInDB as $privilege) {
                if (in_array($privilege, ServiceController::PRIVILEGE_MENU)) {
                    $menu[] = $privilege;
                }
            }
        }

        $userGroups = UserModel::getGroupsByUser(['id' => $args['id']]);
        $indexingGroups = [];
        foreach ($userGroups as $group) {
            if ($group['can_index']) {
                $indexingGroups[] = ['id' => $group['id'], 'label' => $group['group_desc']];
            }
        }
        if (!empty($indexingGroups)) {
            $menu[] = 'indexing';
        }

        return $menu;
    }

    public static function getAdministrationPrivilegesByUserId(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId']);
        ValidatorModel::intVal($args, ['userId']);

        $rawPrivilegesStoredInDB = ServiceModel::getByUser(['id' => $args['userId']]);
        $privilegesStoredInDB = array_column($rawPrivilegesStoredInDB, 'service_id');

        $organization = [];
        $classifying = [];
        $production = [];
        $supervision = [];

        if (!empty($privilegesStoredInDB)) {
            foreach ($privilegesStoredInDB as $privilege) {
                if (in_array($privilege, ServiceController::PRIVILEGE_ADMIN_ORGANIZATION)) {
                    $organization[] = $privilege;
                } else if (in_array($privilege, ServiceController::PRIVILEGE_ADMIN_CLASSIFYING)) {
                    $classifying[] = $privilege;
                } else if (in_array($privilege, ServiceController::PRIVILEGE_ADMIN_PRODUCTION)) {
                    $production[] = $privilege;
                } else if (in_array($privilege, ServiceController::PRIVILEGE_ADMIN_SUPERVISION)) {
                    $supervision[] = $privilege;
                }
            }
        }

        $administration = [
            "administration" => [
                "organisation" => $organization,
                "classement" => $classifying,
                "production" => $production,
                "supervision" => $supervision
           ]
        ];

        return $administration;
    }

    public static function hasPrivilege(array $args)
    {
        ValidatorModel::notEmpty($args, ['privilegeId', 'userId']);
        ValidatorModel::stringType($args, ['privilegeId']);
        ValidatorModel::intVal($args, ['userId']);

        $user = UserModel::getById([
            'select' => ['user_id'],
            'id' => $args['userId']
        ]);
        if ($user['user_id'] == 'superadmin') {
            return true;
        }

        $aServices = DatabaseModel::select([
            'select'    => ['usergroups_services.service_id'],
            'table'     => ['usergroup_content, usergroups_services, usergroups'],
            'where'     => [
                'usergroup_content.group_id = usergroups.id',
                'usergroups.group_id = usergroups_services.group_id',
                'usergroup_content.user_id = ?',
                'usergroups_services.service_id = ?'
            ],
            'data'      => [$args['userId'], $args['privilegeId']]
        ]);

        return !empty($aServices);
    }

    public static function getPrivilegesByUser(array $args) {
        ValidatorModel::notEmpty($args, ['userId']);
        ValidatorModel::intVal($args, ['userId']);

        $user = UserModel::getById([
            'select' => ['user_id'],
            'id' => $args['userId']
        ]);
        if ($user['user_id'] == 'superadmin') {
            $allPrivileges = array_merge(
                ServiceController::PRIVILEGE_ADMIN_SUPERVISION,
                ServiceController::PRIVILEGE_ADMIN_PRODUCTION,
                ServiceController::PRIVILEGE_ADMIN_CLASSIFYING,
                ServiceController::PRIVILEGE_ADMIN_ORGANIZATION,
                ServiceController::PRIVILEGE_MENU);

            return $allPrivileges;
        }

        $rawPrivilegesStoredInDB = ServiceModel::getByUser(['id' => $args['userId']]);
        $privilegesStoredInDB = array_column($rawPrivilegesStoredInDB, 'service_id');

        return $privilegesStoredInDB;
    }
}
