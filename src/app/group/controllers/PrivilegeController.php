<?php

namespace Group\controllers;

use Group\models\GroupModel;
use Group\models\ServiceModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class PrivilegeController
{
    const PRIVILEGES = [
        "admin",
        "adv_search_mlb",
        "entities_print_sep_mlb",
        "reports",
        "save_numeric_package",
        "admin_users",
        "admin_groups",
        "manage_entities",
        "admin_listmodels",
        "admin_architecture",
        "admin_tag",
        "admin_baskets",
        "admin_status",
        "admin_actions",
        "admin_contacts",
        "admin_priorities",
        "admin_templates",
        "admin_indexing_models",
        "admin_custom_fields",
        "admin_notif",
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

    public static function addPrivilege(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_groups', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $group = GroupModel::getById(['id' => $args['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        if (ServiceModel::groupHasPrivilege(['privilegeId' => $args['privilegeId'], 'groupId' => $group['group_id']])) {
            return $response->withStatus(204);
        }

        ServiceModel::addPrivilegeToGroup(['privilegeId' => $args['privilegeId'], 'groupId' => $group['group_id']]);

        return $response->withStatus(204);
    }

    public static function removePrivilege(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_groups', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $group = GroupModel::getById(['id' => $args['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        if (!ServiceModel::groupHasPrivilege(['privilegeId' => $args['privilegeId'], 'groupId' => $group['group_id']])) {
            return $response->withStatus(204);
        }

        ServiceModel::removePrivilegeToGroup(['privilegeId' => $args['privilegeId'], 'groupId' => $group['group_id']]);

        return $response->withStatus(204);
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

    public static function getPrivilegesByUser(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId']);
        ValidatorModel::intVal($args, ['userId']);

        $user = UserModel::getById([
            'select' => ['user_id'],
            'id' => $args['userId']
        ]);
        if ($user['user_id'] == 'superadmin') {
            return PrivilegeController::PRIVILEGES;
        }

        $rawPrivilegesStoredInDB = ServiceModel::getByUser(['id' => $args['userId']]);
        $privilegesStoredInDB = array_column($rawPrivilegesStoredInDB, 'service_id');

        return $privilegesStoredInDB;
    }
}
