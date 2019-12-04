<?php

namespace Group\controllers;

use Basket\models\BasketModel;
use Basket\models\GroupBasketModel;
use Group\models\GroupModel;
use Group\models\PrivilegeModel;
use Resource\controllers\ResController;
use Resource\controllers\ResourceListController;
use Resource\models\ResModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use User\models\UserGroupModel;
use User\models\UserModel;

class PrivilegeController
{
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

        if ($args['privilegeId'] == 'admin_users') {
            $groups = GroupModel::get(['select' => ['id']]);
            $groups = array_column($groups, 'id');

            $parameters = json_encode(['groups' => $groups]);

            PrivilegeModel::updateParameters(['groupId' => $group['group_id'], 'privilegeId' => $args['privilegeId'], 'parameters' => $parameters]);
        }

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

    public static function getParameters(Request $request, Response $response, array $args)
    {
        $group = GroupModel::getById(['id' => $args['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        $queryParams = $request->getQueryParams();

        $parameters = PrivilegeModel::getParametersFromGroupPrivilege(['groupId' => $group['group_id'], 'privilegeId' => $args['privilegeId']]);

        if (!empty($queryParams['parameter'])) {
            if (!isset($parameters[$queryParams['parameter']])) {
                return $response->withStatus(400)->withJson(['errors' => 'Parameter not found']);
            }

            $parameters = $parameters[$queryParams['parameter']];
        }

        return $response->withJson($parameters);
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
            return ['ALL_PRIVILEGES'];
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
            if (!empty($groups)) {
                $groups = $groups['groups'];
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

    public static function canIndex(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId']);
        ValidatorModel::intVal($args, ['userId']);

        $canIndex = UserGroupModel::getWithGroups([
            'select'    => [1],
            'where'     => ['usergroup_content.user_id = ?', 'usergroups.can_index = ?'],
            'data'      => [$args['userId'], true]
        ]);

        return !empty($canIndex);
    }

    public static function canUpdateResource(array $args)
    {
        ValidatorModel::notEmpty($args, ['currentUserId', 'resId']);
        ValidatorModel::intVal($args, ['currentUserId', 'resId']);
        ValidatorModel::arrayType($args, ['queryParams']);

        if (!empty($args['queryParams']['userId']) && !empty($args['queryParams']['groupId']) && !empty($args['queryParams']['basketId'])) {
            $errors = ResourceListController::listControl(['groupId' => $args['queryParams']['groupId'], 'userId' => $args['queryParams']['userId'], 'basketId' => $args['queryParams']['basketId'], 'currentUserId' => $args['currentUserId']]);
            if (!empty($errors['errors'])) {
                return ['errors' => $errors['errors']];
            }

            $user   = UserModel::getById(['id' => $args['queryParams']['userId'], 'select' => ['user_id']]);
            $basket = BasketModel::getById(['id' => $args['queryParams']['basketId'], 'select' => ['basket_id', 'basket_clause']]);
            $group  = GroupModel::getById(['id' => $args['queryParams']['groupId'], 'select' => ['group_id']]);

            $groupBasket = GroupBasketModel::get(['select' => ['list_event_data', 'list_event'], 'where' => ['basket_id = ?', 'group_id = ?'], 'data' => [$basket['basket_id'], $group['group_id']]]);
            $listEventData = json_decode($groupBasket[0]['list_event_data'], true);
            if ($groupBasket[0]['list_event'] != 'processDocument' || !$listEventData['canUpdate']) {
                return ['errors' => 'Basket can not update resources'];
            }

            $whereClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'login' => $user['user_id']]);
            $resource = ResModel::getOnView([
                'select'    => [1],
                'where'     => [$whereClause, 'res_view_letterbox.res_id = ?'],
                'data'      => [$args['resId']]
            ]);
            if (empty($resource)) {
                return ['errors' => 'Resource does not belong to this basket'];
            }
        } else {
            if (!Validator::intVal()->validate($args['resId']) || !ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $args['currentUserId']])) {
                return ['errors' => 'Resource out of perimeter'];
            } elseif (!PrivilegeController::hasPrivilege(['privilegeId' => 'edit_resource', 'userId' => $args['currentUserId']])) {
                return ['errors' => 'Service forbidden'];
            }
        }

        return true;
    }
}
