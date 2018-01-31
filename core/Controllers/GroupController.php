<?php

namespace Core\Controllers;

use Core\Models\GroupModel;
use Core\Models\ServiceModel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class GroupController
{
    public function get(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_groups', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $groups = GroupModel::get();
        foreach ($groups as $key => $value) {
            $groups[$key]['users'] = GroupModel::getUsersByGroupId(['groupId' => $value['group_id'], 'select' => ['users.user_id']]);
        }

        return $response->withJson(['groups' => $groups]);
    }

    public function create(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_groups', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['group_desc']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['group_id']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['security']['where_clause']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['security']['maarch_comment']);

        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        GroupModel::create(['groupId' => $data['group_id'], 'description' => $data['group_desc'], 'clause' => $data['security']['where_clause'], 'comment' => $data['security']['maarch_comment']]);

        $group = GroupModel::getByGroupId(['groupId' => $data['group_id']]);
        if (!Validator::intType()->notEmpty()->validate($group['id'])) {
            return $response->withStatus(500)->withJson(['errors' => 'Group Creation Error']);
        }

        return $response->withJson(['group' => $group]);
    }

    public function update(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_groups', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $group = GroupModel::getById(['id' => $aArgs['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['description']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['security']['where_clause']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['security']['maarch_comment']);

        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        GroupModel::update(['id' => $aArgs['id'], 'description' => $data['description'], 'clause' => $data['security']['where_clause'], 'comment' => $data['security']['maarch_comment']]);

        return $response->withJson(['success' => 'success']);
    }

    public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_groups', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $group = GroupModel::getById(['id' => $aArgs['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        GroupModel::delete(['id' => $aArgs['id']]);

        $groups = GroupModel::get();
        foreach ($groups as $key => $value) {
            $groups[$key]['users'] = GroupModel::getUsersByGroupId(['groupId' => $value['group_id'], 'select' => ['users.user_id']]);
        }

        return $response->withJson(['groups' => $groups]);
    }

    public function getDetailledById(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_groups', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $group = GroupModel::getById(['id' => $aArgs['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        $group['users']     = GroupModel::getUsersByGroupId(['groupId' => $group['group_id'], 'select' => ['users.user_id', 'users.firstname', 'users.lastname']]);
        $group['security']  = GroupModel::getSecurityByGroupId(['groupId' => $group['group_id']]);
        $group['services']  = GroupModel::getAllServicesByGroupId(['groupId' => $group['group_id']]);

        return $response->withJson(['group' => $group]);
    }

    public function updateService(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_groups', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        $group = GroupModel::getById(['id' => $aArgs['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        if ($data['checked'] === true && !empty(GroupModel::getServiceById(['groupId' => $group['group_id'], 'serviceId' => $aArgs['serviceId']]))) {
            return $response->withStatus(400)->withJson(['errors' => 'Service is already linked to this group']);
        }

        GroupModel::updateServiceById(['groupId' => $group['group_id'], 'serviceId' => $aArgs['serviceId'], 'checked' => $data['checked']]);

        return $response->withJson(['success' => 'success']);
    }

    public function reassignUsers(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_groups', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $group = GroupModel::getById(['id' => $aArgs['id'], 'select' => ['group_id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }
        $newGroup = GroupModel::getById(['id' => $aArgs['newGroupId'], 'select' => ['group_id']]);
        if (empty($newGroup)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        GroupModel::reassignUsers(['groupId' => $group['group_id'], 'newGroupId' => $newGroup['group_id']]);

        return $response->withJson(['success' => 'success']);
    }
}
