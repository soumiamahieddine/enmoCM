<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Resource List Controller
* @author dev@maarch.org
*/

namespace Resource\controllers;

use Attachment\models\AttachmentModel;
use Basket\models\BasketModel;
use Basket\models\GroupBasketModel;
use Basket\models\RedirectBasketModel;
use Entity\models\EntityModel;
use Group\models\GroupModel;
use Note\models\NoteModel;
use Priority\models\PriorityModel;
use Resource\models\ResModel;
use Resource\models\ResourceListModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use SrcCore\models\ValidatorModel;
use Status\models\StatusModel;
use User\models\UserModel;

class ResourceListController
{
    public function get(Request $request, Response $response, array $aArgs)
    {
        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $errors = ResourceListController::listControl(['groupId' => $aArgs['groupId'], 'userId' => $aArgs['userId'], 'basketId' => $aArgs['basketId'], 'currentUserId' => $currentUser['id']]);
        if (!empty($errors['errors'])) {
            return $response->withStatus($errors['code'])->withJson(['errors' => $errors['errors']]);
        }

        $basket = BasketModel::getById(['id' => $aArgs['basketId'], 'select' => ['basket_clause', 'basket_res_order', 'basket_name']]);
        $user = UserModel::getById(['id' => $aArgs['userId'], 'select' => ['user_id']]);

        $data = $request->getQueryParams();
        $data['offset'] = (empty($data['offset']) || !is_numeric($data['offset']) ? 0 : $data['offset']);
        $data['limit'] = (empty($data['limit']) || !is_numeric($data['limit']) ? 0 : $data['limit']);

        $whereClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'login' => $user['user_id']]);
        $where = [$whereClause];
        $queryData = [];

        if (!empty($data['delayed']) && $data['delayed'] == 'true') {
            $where[] = 'process_limit_date < CURRENT_TIMESTAMP';
        }
        if (!empty($data['search']) && mb_strlen($data['search']) >= 2) {
            $where[] = '(alt_identifier ilike ? OR translate(subject, \'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ\', \'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyrr\') ilike translate(?, \'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ\', \'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyrr\'))';
            $queryData[] = "%{$data['search']}%";
            $queryData[] = "%{$data['search']}%";
        }
        if (!empty($data['priorities'])) {
            $where[] = 'priority in (?)';
            $queryData[] = explode(',', $data['priorities']);
        }
        if (!empty($data['categories'])) {
            $where[] = 'category_id in (?)';
            $queryData[] = explode(',', $data['categories']);
        }
        if (!empty($data['statuses'])) {
            $where[] = 'status in (?)';
            $queryData[] = explode(',', $data['statuses']);
        }
        if (!empty($data['entities'])) {
            $where[] = 'destination in (?)';
            $queryData[] = explode(',', $data['entities']);
        }
        if (!empty($data['entitiesChildren'])) {
            $entities = explode(',', $data['entitiesChildren']);
            $entitiesChildren = [];
            foreach ($entities as $entity) {
                $children = EntityModel::getEntityChildren(['entityId' => $entity]);
                $entitiesChildren = array_merge($entitiesChildren, $children);
            }
            if (!empty($entitiesChildren)) {
                $where[] = 'destination in (?)';
                $queryData[] = $entitiesChildren;
            }
        }

        if (!empty($data['order']) && strpos($data['order'], 'alt_identifier') !== false) {
            $data['order'] = 'order_alphanum(alt_identifier) ' . explode(' ', $data['order'])[1];
        }

        $rawResources = ResModel::getOnView([
            'select'    => ['count(1) OVER()', 'res_id'],
            'where'     => $where,
            'data'      => $queryData,
            'orderBy'   => empty($data['order']) ? [$basket['basket_res_order']] : [$data['order']],
            'offset'    => (int)$data['offset'],
            'limit'     => (int)$data['limit']
        ]);
        $count = empty($rawResources[0]['count']) ? 0 : $rawResources[0]['count'];
        $resIds = [];
        foreach ($rawResources as $resource) {
            $resIds[] = $resource['res_id'];
        }

        $resources = [];
        if (!empty($resIds)) {
            $attachments = AttachmentModel::getOnView([
                'select'    => ['COUNT(res_id)', 'res_id_master'],
                'where'     => ['res_id_master in (?)', 'status not in (?)'],
                'data'      => [$resIds, ['DEL', 'OBS']],
                'groupBy'   => ['res_id_master']
            ]);

            $resources = ResourceListModel::get(['resIds' => $resIds]);

            foreach ($resources as $key => $resource) {
                $resources[$key]['countAttachments'] = 0;
                foreach ($attachments as $attachment) {
                    if ($attachment['res_id_master'] == $resource['res_id']) {
                        $resources[$key]['countAttachments'] = $attachment['count'];
                    }
                }
                $resources[$key]['countNotes'] = NoteModel::countByResId(['resId' => $resource['res_id'], 'login' => $GLOBALS['userId']]);
            }
        }

        return $response->withJson(['resources' => $resources, 'count' => $count, 'basketLabel' => $basket['basket_name']]);
    }

    public function getFilters(Request $request, Response $response, array $aArgs)
    {
        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $errors = ResourceListController::listControl(['groupId' => $aArgs['groupId'], 'userId' => $aArgs['userId'], 'basketId' => $aArgs['basketId'], 'currentUserId' => $currentUser['id']]);
        if (!empty($errors['errors'])) {
            return $response->withStatus($errors['code'])->withJson(['errors' => $errors['errors']]);
        }

        $basket = BasketModel::getById(['id' => $aArgs['basketId'], 'select' => ['basket_clause']]);
        $user = UserModel::getById(['id' => $aArgs['userId'], 'select' => ['user_id']]);
        $whereClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'login' => $user['user_id']]);
        $where = [$whereClause];
        $queryData = [];

        $data = $request->getQueryParams();
        if (!empty($data['delayed']) && $data['delayed'] == 'true') {
            $where[] = 'process_limit_date < CURRENT_TIMESTAMP';
        }
        if (!empty($data['search']) && mb_strlen($data['search']) >= 2) {
            $where[] = '(alt_identifier ilike ? OR translate(subject, \'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ\', \'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyrr\') ilike translate(?, \'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ\', \'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyrr\'))';
            $queryData[] = "%{$data['search']}%";
            $queryData[] = "%{$data['search']}%";
        }

        $wherePriorities = $where;
        $whereCategories = $where;
        $whereStatuses = $where;
        $whereEntities = $where;
        $dataPriorities = $queryData;
        $dataCategories = $queryData;
        $dataStatuses = $queryData;
        $dataEntities = $queryData;

        if (!empty($data['priorities'])) {
            $whereCategories[] = 'priority in (?)';
            $dataCategories[] = explode(',', $data['priorities']);
            $whereStatuses[] = 'priority in (?)';
            $dataStatuses[] = explode(',', $data['priorities']);
            $whereEntities[] = 'priority in (?)';
            $dataEntities[] = explode(',', $data['priorities']);
        }
        if (!empty($data['categories'])) {
            $wherePriorities[] = 'category_id in (?)';
            $dataPriorities[] = explode(',', $data['categories']);
            $whereStatuses[] = 'category_id in (?)';
            $dataStatuses[] = explode(',', $data['categories']);
            $whereEntities[] = 'category_id in (?)';
            $dataEntities[] = explode(',', $data['categories']);
        }
        if (!empty($data['statuses'])) {
            $wherePriorities[] = 'status in (?)';
            $dataPriorities[] = explode(',', $data['statuses']);
            $whereCategories[] = 'status in (?)';
            $dataCategories[] = explode(',', $data['statuses']);
            $whereEntities[] = 'status in (?)';
            $dataEntities[] = explode(',', $data['statuses']);
        }
        if (!empty($data['entities'])) {
            $wherePriorities[] = 'destination in (?)';
            $dataPriorities[] = explode(',', $data['entities']);
            $whereCategories[] = 'destination in (?)';
            $dataCategories[] = explode(',', $data['entities']);
            $whereStatuses[] = 'destination in (?)';
            $dataStatuses[] = explode(',', $data['entities']);
        }
        if (!empty($data['entitiesChildren'])) {
            $entities = explode(',', $data['entitiesChildren']);
            $entitiesChildren = [];
            foreach ($entities as $entity) {
                $children = EntityModel::getEntityChildren(['entityId' => $entity]);
                $entitiesChildren = array_merge($entitiesChildren, $children);
            }
            if (!empty($entitiesChildren)) {
                $wherePriorities[] = 'destination in (?)';
                $dataPriorities[] = explode(',', $data['entities']);
                $whereCategories[] = 'destination in (?)';
                $dataCategories[] = explode(',', $data['entities']);
                $whereStatuses[] = 'destination in (?)';
                $dataStatuses[] = explode(',', $data['entities']);
            }
        }

        $priorities = [];
        $rawPriorities = ResModel::getOnView([
            'select'    => ['count(res_id)', 'priority'],
            'where'     => $wherePriorities,
            'data'      => $dataPriorities,
            'groupBy'   => ['priority']
        ]);
        
        foreach ($rawPriorities as $key => $value) {
            $priority = null;
            if (!empty($value['priority'])) {
                $priority = PriorityModel::getById(['select' => ['label'], 'id' => $value['priority']]);
            }
            $priorities[] = [
                'id'        => empty($value['priority']) ? null : $value['priority'],
                'label'     => empty($priority['label']) ? '_UNDEFINED' : $priority['label'],
                'count'     => $value['count']
            ];
        }

        $categories = [];
        $allCategories = ResModel::getCategories();
        $rawCategories = ResModel::getOnView([
            'select'    => ['count(res_id)', 'category_id'],
            'where'     => $whereCategories,
            'data'      => $dataCategories,
            'groupBy'   => ['category_id']
        ]);
        foreach ($rawCategories as $key => $value) {
            $label = null;
            if (!empty($value['category_id'])) {
                foreach ($allCategories as $category) {
                    if ($value['category_id'] == $category['id']) {
                        $label = $category['label'];
                    }
                }
            }
            $categories[] = [
                'id'        => empty($value['category_id']) ? null : $value['category_id'],
                'label'     => empty($label) ? '_UNDEFINED' : $label,
                'count'     => $value['count']
            ];
        }

        $statuses = [];
        $rawStatuses = ResModel::getOnView([
            'select'    => ['count(res_id)', 'status'],
            'where'     => $whereStatuses,
            'data'      => $dataStatuses,
            'groupBy'   => ['status']
        ]);
        foreach ($rawStatuses as $key => $value) {
            $status = StatusModel::getById(['select' => ['label_status'], 'id' => $value['status']]);
            $statuses[] = [
                'id'        => $value['status'],
                'label'     => empty($status['label_status']) ? '_UNDEFINED' : $status['label_status'],
                'count'     => $value['count']
            ];
        }

        $entities = [];
        $rawEntities = ResModel::getOnView([
            'select'    => ['count(res_id)', 'destination'],
            'where'     => $whereEntities,
            'data'      => $dataEntities,
            'groupBy'   => ['destination']
        ]);
        foreach ($rawEntities as $key => $value) {
            $entity = null;
            if (!empty($value['destination'])) {
                $entity = EntityModel::getByEntityId(['select' => ['entity_label'], 'entityId' => $value['destination']]);
            }
            $entities[] = [
                'entityId'  => empty($value['destination']) ? null : $value['destination'],
                'label'     => empty($entity['entity_label']) ? '_UNDEFINED' : $entity['entity_label'],
                'count'     => $value['count']
            ];
        }

        $priorities = (count($priorities) >= 2) ? $priorities : [];
        $categories = (count($categories) >= 2) ? $categories : [];
        $statuses = (count($statuses) >= 2) ? $statuses : [];
        $entities = (count($entities) >= 2) ? $entities : [];

        $entitiesChildren = [];
        foreach ($entities as $entity) {
            $children = EntityModel::getEntityChildren(['entityId' => $entity['entityId']]);
            $count = 0;
            foreach ($entities as $value) {
                if (in_array($value['entityId'], $children)) {
                    $count += $value['count'];
                }
            }
            $entitiesChildren[] = [
                'entityId'  => $entity['entityId'],
                'label'     => $entity['label'],
                'count'     => $count
            ];
        }

        return $response->withJson(['entities' => $entities, 'priorities' => $priorities, 'categories' => $categories, 'statuses' => $statuses, 'entitiesChildren' => $entitiesChildren]);
    }

    private static function listControl(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'userId', 'basketId', 'currentUserId']);
        ValidatorModel::intVal($aArgs, ['groupId', 'userId', 'currentUserId']);
        ValidatorModel::stringType($aArgs, ['basketId']);

        $group = GroupModel::getById(['id' => $aArgs['groupId'], 'select' => ['group_id']]);
        $basket = BasketModel::getById(['id' => $aArgs['basketId'], 'select' => ['basket_clause', 'basket_res_order', 'basket_name']]);
        if (empty($group) || empty($basket)) {
            return ['errors' => 'Group or basket does not exist', 'code' => 400];
        }

        if ($aArgs['userId'] == $aArgs['currentUserId']) {
            $redirectedBasket = RedirectBasketModel::get([
                'select'    => [1],
                'where'     => ['owner_user_id = ?', 'basket_id = ?', 'group_id = ?'],
                'data'      => [$aArgs['userId'], $aArgs['basketId'], $aArgs['groupId']]
            ]);
            if (!empty($redirectedBasket[0])) {
                return ['errors' => 'Basket out of perimeter (redirected)', 'code' => 403];
            }
        } else {
            $redirectedBasket = RedirectBasketModel::get([
                'select'    => ['actual_user_id'],
                'where'     => ['owner_user_id = ?', 'basket_id = ?', 'group_id = ?'],
                'data'      => [$aArgs['userId'], $aArgs['basketId'], $aArgs['groupId']]
            ]);
            if (empty($redirectedBasket[0]) || $redirectedBasket[0]['actual_user_id'] != $aArgs['currentUserId']) {
                return ['errors' => 'Basket out of perimeter', 'code' => 403];
            }
        }

        $user = UserModel::getById(['id' => $aArgs['userId'], 'select' => ['user_id']]);
        $groups = UserModel::getGroupsByUserId(['userId' => $user['user_id']]);
        $groupFound = false;
        foreach ($groups as $value) {
            if ($value['id'] == $aArgs['groupId']) {
                $groupFound = true;
            }
        }
        if (!$groupFound) {
            return ['errors' => 'Group is not linked to this user', 'code' => 400];
        }

        $isBasketLinked = GroupBasketModel::get(['select' => [1], 'where' => ['basket_id = ?', 'group_id = ?'], 'data' => [$aArgs['basketId'], $group['group_id']]]);
        if (empty($isBasketLinked)) {
            return ['errors' => 'Group is not linked to this basket', 'code' => 400];
        }

        return ['success' => 'success'];
    }
}
