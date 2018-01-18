<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Basket Controller
* @author dev@maarch.org
*/

namespace Basket\controllers;

use Basket\models\BasketModel;
use Core\Models\ActionModel;
use Core\Models\GroupModel;
use Core\Models\ServiceModel;
use Core\Models\ValidatorModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class BasketController
{
    public function get(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_baskets', 'userId' => $GLOBALS['userId'], 'location' => 'basket', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson(['baskets' => BasketModel::get()]);
    }

    public function getById(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_baskets', 'userId' => $GLOBALS['userId'], 'location' => 'basket', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getById(['id' => $aArgs['id']]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }

        return $response->withJson(['basket'  => $basket]);
    }

    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_baskets', 'userId' => $GLOBALS['userId'], 'location' => 'basket', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['id']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['name']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['description']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['clause']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $existingBasket = BasketModel::getById(['id' => $data['id'], 'select' => ['1']]);
        if (!empty($existingBasket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket already exists']);
        }

        $data['isVisible'] = empty($data['isSearchBasket']) ? 'Y' : 'N';
        $data['isFolderBasket'] = empty($data['isFolderBasket']) ? 'N' : 'Y';
        $data['flagNotif'] = empty($data['flagNotif']) ? 'N' : 'Y';
        BasketModel::create($data);

        return $response->withJson(['basket' => $data['id']]);
    }

    public function update(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_baskets', 'userId' => $GLOBALS['userId'], 'location' => 'basket', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getById(['id' => $aArgs['id'], 'select' => [1]]);

        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }

        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['name']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['description']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['clause']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $data['isVisible'] = empty($data['isSearchBasket']) ? 'Y' : 'N';
        $data['isFolderBasket'] = empty($data['isFolderBasket']) ? 'N' : 'Y';
        $data['flagNotif'] = empty($data['flagNotif']) ? 'N' : 'Y';
        $data['id'] = $aArgs['id'];
        BasketModel::update($data);

        return $response->withJson(['success' => 'success']);
    }

    public function delete(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_baskets', 'userId' => $GLOBALS['userId'], 'location' => 'basket', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getById(['id' => $aArgs['id'], 'select' => [1]]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket does not exist']);
        }

        BasketModel::delete(['id' => $aArgs['id']]);

        return $response->withJson(['baskets' => BasketModel::get()]);
    }

    public function getSorted(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_baskets', 'userId' => $GLOBALS['userId'], 'location' => 'basket', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $baskets = BasketModel::get([
            'select'    => ['basket_id', 'basket_name', 'basket_desc', 'basket_order'],
            'where'     => ['is_visible = ?'],
            'data'      => ['Y'],
            'orderBy'   => ['basket_order']
        ]);

        return $response->withJson(['baskets' => $baskets]);
    }

    public function updateSort(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_baskets', 'userId' => $GLOBALS['userId'], 'location' => 'basket', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $theBasket = BasketModel::getById(['id' => $aArgs['id'], 'select' => ['basket_order']]);
        if (empty($theBasket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }

        $data = $request->getParams();

        $allowedMethods = ['UP', 'DOWN'];
        $allowedPowers = ['ONE', 'ALL'];
        $check = Validator::stringType()->notEmpty()->validate($data['method']) && in_array($data['method'], $allowedMethods);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['power']) && in_array($data['power'], $allowedPowers);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $baskets = BasketModel::get([
            'select'    => ['basket_id'],
            'where'     => ['is_visible = ?'],
            'data'      => ['Y'],
            'orderBy'   => ['basket_order']
        ]);
        if (($data['method'] == 'UP' && $baskets[0]['basket_id'] == $aArgs['id']) || ($data['method'] == 'DOWN' && $baskets[count($baskets) - 1]['basket_id'] == $aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket is already sorted']);
        }

        $basketsToUpdate = [];
        foreach ($baskets as $key => $basket) {
            if ($basket['basket_id'] == $aArgs['id'])
                continue;
            if ($data['method'] == 'UP' && $data['power'] == 'ALL') {
                if ($key == 0) {
                    $basketsToUpdate[] = $aArgs['id'];
                }
                $basketsToUpdate[] = $basket['basket_id'];
            } elseif ($data['method'] == 'UP' && $data['power'] == 'ONE') {
                if (!empty($baskets[$key + 1]) && $baskets[$key + 1]['basket_id'] == $aArgs['id']) {
                    $basketsToUpdate[] = $aArgs['id'];
                }
                $basketsToUpdate[] = $basket['basket_id'];
            } elseif ($data['method'] == 'DOWN' && $data['power'] == 'ALL') {
                $basketsToUpdate[] = $basket['basket_id'];
                if (count($baskets) == $key + 1) {
                    $basketsToUpdate[] = $aArgs['id'];
                }
            } elseif ($data['method'] == 'DOWN' && $data['power'] == 'ONE') {
                $basketsToUpdate[] = $basket['basket_id'];
                if (!empty($baskets[$key - 1]) && $baskets[$key - 1]['basket_id'] == $aArgs['id']) {
                    $basketsToUpdate[] = $aArgs['id'];
                }
            }
        }

        foreach ($basketsToUpdate as $key => $basketToUpdate) {
            BasketModel::updateOrder(['id' => $basketToUpdate, 'order' => $key + 1]);
        }

        $baskets = BasketModel::get([
            'select'    => ['basket_id', 'basket_name', 'basket_desc', 'basket_order'],
            'where'     => ['is_visible = ?'],
            'data'      => ['Y'],
            'orderBy'   => ['basket_order']
        ]);

        return $response->withJson(['baskets' => $baskets]);
    }

    public function getGroups(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_baskets', 'userId' => $GLOBALS['userId'], 'location' => 'basket', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getById(['id' => $aArgs['id'], 'select' => [1]]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }

        $groups = BasketModel::getGroups(['id' => $aArgs['id']]);

        foreach ($groups as $key => $group) {
            $actions = BasketModel::getActionsForGroupById([
                'id'        => $aArgs['id'],
                'groupId'   => $group['group_id'],
                'select'    => ['id_action', 'where_clause', 'used_in_basketlist', 'used_in_action_page', 'default_action_list']
            ]);
            $actionIds = [];
            foreach ($actions as $action) {
                $actionIds[] = $action['id_action'];
            }
            $statuses = [];
            $redirects = [];
            if (!empty($actionIds)) {
                $statuses = BasketModel::getGroupBasketStatusesIn([
                    'id'            => $aArgs['id'],
                    'groupId'       => $group['group_id'],
                    'actionIds'     => $actionIds,
                    'select'        => ['status_id', 'action_id']
                ]);
                $redirects = BasketModel::getGroupBasketRedirectIn([
                    'id'            => $aArgs['id'],
                    'groupId'       => $group['group_id'],
                    'actionIds'     => $actionIds,
                    'select'        => ['entity_id', 'action_id', 'keyword', 'redirect_mode']
                ]);
            }
            foreach ($actions as $actionKey => $action) {
                $actions[$actionKey]['statuses'] = [];
                $actions[$actionKey]['redirects'] = [];
                foreach ($statuses as $status) {
                    if ($status['action_id'] == $action['id_action']) {
                        $actions[$actionKey]['statuses'][] = $status['status_id'];
                    }
                }
                foreach ($redirects as $redirect) {
                    if ($redirect['action_id'] == $action['id_action']) {
                        $actions[$actionKey]['redirects'][] = $redirect;
                    }
                }
            }

            $groups[$key]['groupActions'] = $actions;
        }

        return $response->withJson(['groups' => $groups]);
    }

    public function createGroup(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_baskets', 'userId' => $GLOBALS['userId'], 'location' => 'basket', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getById(['id' => $aArgs['id'], 'select' => [1]]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }

        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['group_id']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['result_page']);
        $check = $check && Validator::arrayType()->notEmpty()->validate($data['groupActions']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }
        $data['groupActions'] = BasketController::checkGroupActions(['groupActions' => $data['groupActions']]);
        if (!empty($data['groupActions']['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $data['groupActions']['errors']]);
        }

        if (BasketModel::hasGroup(['id' => $aArgs['id'], 'groupId' => $data['group_id']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Group already exist for this basket']);
        }

        BasketModel::createGroup(['id' => $aArgs['id'], 'groupId' => $data['group_id'], 'resultPage' => $data['result_page']]);
        foreach ($data['groupActions'] as $groupAction) {
            BasketModel::createGroupAction([
                'id'                => $aArgs['id'],
                'groupId'           => $data['group_id'],
                'actionId'          => $groupAction['id_action'],
                'whereClause'       => $groupAction['where_clause'],
                'usedInBasketlist'  => $groupAction['used_in_basketlist'],
                'usedInActionPage'  => $groupAction['used_in_action_page'],
                'defaultActionList' => $groupAction['default_action_list']
            ]);

            if (!empty($groupAction['statuses'])) {
                foreach ($groupAction['statuses'] as $status) {
                    BasketModel::createGroupActionStatus([
                        'id'        => $aArgs['id'],
                        'groupId'   => $data['group_id'],
                        'actionId'  => $groupAction['id_action'],
                        'statusId'  => $status
                    ]);
                }
            }
            if (!empty($groupAction['redirects'])) {
                foreach ($groupAction['redirects'] as $redirect) {
                    BasketModel::createGroupActionRedirect([
                        'id'            => $aArgs['id'],
                        'groupId'       => $data['group_id'],
                        'actionId'      => $groupAction['id_action'],
                        'entityId'      => $redirect['entity_id'],
                        'keyword'       => $redirect['keyword'],
                        'redirectMode'  => $redirect['redirect_mode']
                    ]);
                }
            }
        }

        return $response->withJson(['success' => 'success']);
    }

    public function updateGroup(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_baskets', 'userId' => $GLOBALS['userId'], 'location' => 'basket', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getById(['id' => $aArgs['id'], 'select' => [1]]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }

        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['result_page']);
        $check = $check && Validator::arrayType()->notEmpty()->validate($data['groupActions']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }
        $data['groupActions'] = BasketController::checkGroupActions(['groupActions' => $data['groupActions']]);
        if (!empty($data['groupActions']['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $data['groupActions']['errors']]);
        }

        if (!BasketModel::hasGroup(['id' => $aArgs['id'], 'groupId' => $aArgs['groupId']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Group does not exist for this basket']);
        }

        BasketModel::deleteGroup(['id' => $aArgs['id'], 'groupId' => $aArgs['groupId']]);

        BasketModel::createGroup(['id' => $aArgs['id'], 'groupId' => $aArgs['groupId'], 'resultPage' => $data['result_page']]);
        foreach ($data['groupActions'] as $groupAction) {
            BasketModel::createGroupAction([
                'id'                => $aArgs['id'],
                'groupId'           => $aArgs['groupId'],
                'actionId'          => $groupAction['id_action'],
                'whereClause'       => $groupAction['where_clause'],
                'usedInBasketlist'  => $groupAction['used_in_basketlist'],
                'usedInActionPage'  => $groupAction['used_in_action_page'],
                'defaultActionList' => $groupAction['default_action_list']
            ]);

            if (!empty($groupAction['statuses'])) {
                foreach ($groupAction['statuses'] as $status) {
                    BasketModel::createGroupActionStatus([
                        'id'        => $aArgs['id'],
                        'groupId'   => $aArgs['groupId'],
                        'actionId'  => $groupAction['id_action'],
                        'statusId'  => $status
                    ]);
                }
            }
            if (!empty($groupAction['redirects'])) {
                foreach ($groupAction['redirects'] as $redirect) {
                    BasketModel::createGroupActionRedirect([
                        'id'            => $aArgs['id'],
                        'groupId'       => $aArgs['groupId'],
                        'actionId'      => $groupAction['id_action'],
                        'entityId'      => $redirect['entity_id'],
                        'keyword'       => $redirect['keyword'],
                        'redirectMode'  => $redirect['redirect_mode']
                    ]);
                }
            }
        }

        return $response->withJson(['success' => 'success']);
    }

    public function getDataForGroupById(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_baskets', 'userId' => $GLOBALS['userId'], 'location' => 'basket', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getById(['id' => $aArgs['id']]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }

        $basketGroups = BasketModel::getGroups(['id' => $aArgs['id']]);
        $groups = GroupModel::get(['select' => ['group_id', 'group_desc']]);

        foreach ($groups as $key => $group) {
            $found = false;
            foreach ($basketGroups as $basketGroup) {
                if ($basketGroup['group_id'] == $group['group_id']) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
                unset($groups[$key]);
            }
        }
        $groups = array_values($groups);

        $basketPages = BasketModel::getBasketPages();
        $actions = ActionModel::get();

        return $response->withJson(['groups' => $groups, 'pages' => $basketPages, 'actions' => $actions]);
    }

    public function deleteGroup(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_baskets', 'userId' => $GLOBALS['userId'], 'location' => 'basket', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getById(['id' => $aArgs['id']]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }

        BasketModel::deleteGroup(['id' => $aArgs['id'], 'groupId' => $aArgs['groupId']]);

        return $response->withJson(['success' => 'success']);
    }

    private static function checkGroupActions(array $aArgs) {
        ValidatorModel::notEmpty($aArgs, ['groupActions']);
        ValidatorModel::arrayType($aArgs, ['groupActions']);

        $defaultAction = false;
        $actions = ActionModel::get(['select' => ['id']]);

        foreach ($aArgs['groupActions'] as $key => $groupAction) {
            $actionExists = false;
            foreach ($actions as $action) {
                if ($action['id'] == $groupAction['id_action']) {
                    $actionExists = true;
                }
            }
            if (!$actionExists) {
                return ['errors' => 'Action does not exist'];
            }
            if ($groupAction['default_action_list'] === true) {
                $defaultAction = true;
            }
            $aArgs['groupActions'][$key]['where_clause'] = empty($groupAction['where_clause']) ? '' : $groupAction['where_clause'];
            $aArgs['groupActions'][$key]['used_in_basketlist'] = empty($groupAction['used_in_basketlist']) ? 'N' : 'Y';
            $aArgs['groupActions'][$key]['used_in_action_page'] = empty($groupAction['used_in_action_page']) ? 'N' : 'Y';
            $aArgs['groupActions'][$key]['default_action_list'] = empty($groupAction['default_action_list']) ? 'N' : 'Y';
        }
        if (!$defaultAction) {
            return ['errors' => 'Default action needed'];
        }

        return $aArgs['groupActions'];
    }
}
