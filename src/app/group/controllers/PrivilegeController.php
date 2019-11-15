<?php

namespace Group\controllers;

use Group\models\GroupModel;
use Group\models\PrivilegeModel;
use Respect\Validation\Validator;
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

        if (PrivilegeModel::groupHasPrivilege(['privilegeId' => $args['privilegeId'], 'groupId' => $group['group_id']])) {
            return $response->withStatus(204);
        }

        PrivilegeModel::addPrivilegeToGroup(['privilegeId' => $args['privilegeId'], 'groupId' => $group['group_id']]);

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

        if (!PrivilegeModel::groupHasPrivilege(['privilegeId' => $args['privilegeId'], 'groupId' => $group['group_id']])) {
            return $response->withStatus(204);
        }

        PrivilegeModel::removePrivilegeToGroup(['privilegeId' => $args['privilegeId'], 'groupId' => $group['group_id']]);

        return $response->withStatus(204);
    }

    public static function updateParameters(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_groups', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $group = GroupModel::getById(['id' => $args['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        $data = $request->getParams();

        if (!Validator::arrayType()->validate($data['parameters'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body parameters is not an array']);
        }

        $parameters = json_encode($data['parameters']);

        PrivilegeModel::updateParameters(['groupId' => $group['group_id'], 'privilegeId' => $args['privilegeId'], 'parameters' => $parameters]);

        return $response->withStatus(204);
    }

    public static function hasPrivilege(array $args)
    {
        ValidatorModel::notEmpty($args, ['privilegeId', 'userId']);
        ValidatorModel::stringType($args, ['privilegeId']);
        ValidatorModel::intVal($args, ['userId']);

        $user = UserModel::getById([
            'select'    => ['user_id'],
            'id'        => $args['userId']
        ]);
        if ($user['user_id'] == 'superadmin') {
            return true;
        }

        $hasPrivilege = DatabaseModel::select([
            'select'    => [1],
            'table'     => ['usergroup_content, usergroups_services, usergroups'],
            'where'     => [
                'usergroup_content.group_id = usergroups.id',
                'usergroups.group_id = usergroups_services.group_id',
                'usergroup_content.user_id = ?',
                'usergroups_services.service_id = ?'
            ],
            'data'      => [$args['userId'], $args['privilegeId']]
        ]);

        return !empty($hasPrivilege);
    }

    public static function getPrivilegesByUser(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId']);
        ValidatorModel::intVal($args, ['userId']);

        $user = UserModel::getById([
            'select'    => ['user_id'],
            'id'        => $args['userId']
        ]);
        if ($user['user_id'] == 'superadmin') {
            return PrivilegeController::PRIVILEGES;
        }

        $privilegesStoredInDB = PrivilegeModel::getByUser(['id' => $args['userId']]);
        $privilegesStoredInDB = array_column($privilegesStoredInDB, 'service_id');

        return $privilegesStoredInDB;
    }

    public static function getAssignableGroups(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId']);
        ValidatorModel::intVal($args, ['userId']);

        $rawUserGroups = UserModel::getGroupsByUser(['id' => $args['userId']]);
        $userGroups = array_column($rawUserGroups, 'group_id');

        $assignable = [];
        foreach ($userGroups as $userGroup) {
            $groups = PrivilegeModel::getParametersFromGroupPrivilege(['groupId' => $userGroup, 'privilegeId' => 'admin_users']);
            if (isset($groups) && isset($groups->groups)) {
                $groups = $groups->groups;
                $assignable = array_merge($assignable, $groups);
            }
        }

        foreach ($assignable as $key => $group) {
            $assignable[$key] = GroupModel::getById(['id' => $group, 'select' => ['group_id', 'group_desc']]);
        }

        return $assignable;
    }

    public static function canAssignGroup(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'groupId']);
        ValidatorModel::intVal($args, ['userId', 'groupId']);

        $user = UserModel::getById([
            'select'    => ['user_id'],
            'id'        => $args['userId']
        ]);
        if ($user['user_id'] == 'superadmin') {
            return true;
        }

        $privileges = PrivilegeModel::getByUserAndPrivilege(['userId' => $args['userId'], 'privilegeId' => 'admin_users']);
        $privileges = array_column($privileges, 'parameters');

        if (empty($privileges)) {
            return false;
        }
        $assignable = [];

        foreach ($privileges as $groups) {
            $groups = json_decode($groups);
            $groups = $groups->groups;
            if ($groups != null) {
                $assignable = array_merge($assignable, $groups);
            }
        }

        if (count($assignable) == 0) {
            return false;
        }

        return in_array($args['groupId'], $assignable);
    }
}
