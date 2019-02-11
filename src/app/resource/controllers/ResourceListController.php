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
use Contact\models\ContactModel;
use Entity\models\EntityModel;
use Entity\models\ListInstanceModel;
use Group\models\GroupModel;
use Note\models\NoteModel;
use Priority\models\PriorityModel;
use Resource\models\ResModel;
use Resource\models\ResourceContactModel;
use Resource\models\ResourceListModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\AutoCompleteController;
use SrcCore\controllers\PreparedClauseController;
use SrcCore\models\DatabaseModel;
use SrcCore\models\TextFormatModel;
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

        $basket = BasketModel::getById(['id' => $aArgs['basketId'], 'select' => ['basket_clause', 'basket_res_order', 'basket_name', 'basket_id']]);
        $user = UserModel::getById(['id' => $aArgs['userId'], 'select' => ['user_id']]);
        $group = GroupModel::getById(['id' => $aArgs['groupId'], 'select' => ['group_id']]);

        $data = $request->getQueryParams();
        $data['offset'] = (empty($data['offset']) || !is_numeric($data['offset']) ? 0 : (int)$data['offset']);
        $data['limit'] = (empty($data['limit']) || !is_numeric($data['limit']) ? 10 : (int)$data['limit']);

        $allQueryData = ResourceListController::getResourcesListQueryData(['data' => $data, 'basketClause' => $basket['basket_clause'], 'login' => $user['user_id']]);
        if (!empty($allQueryData['order'])) {
            $data['order'] = $allQueryData['order'];
        }

        $rawResources = ResourceListModel::getOnView([
            'select'    => ['res_id'],
            'table'     => $allQueryData['table'],
            'leftJoin'  => $allQueryData['leftJoin'],
            'where'     => $allQueryData['where'],
            'data'      => $allQueryData['queryData'],
            'orderBy'   => empty($data['order']) ? [$basket['basket_res_order']] : [$data['order']]
        ]);
        $count = count($rawResources);

        $resIds = [];
        if (!empty($rawResources[$data['offset']])) {
            $start = $data['offset'];
            $i = 0;
            while ($i < $data['limit'] && !empty($rawResources[$start])) {
                $resIds[] = $rawResources[$start]['res_id'];
                ++$start;
                ++$i;
            }
        }
        $allResources = [];
        foreach ($rawResources as $resource) {
            $allResources[] = $resource['res_id'];
        }

        $formattedResources = [];
        if (!empty($resIds)) {
            $attachments = AttachmentModel::getOnView([
                'select'    => ['COUNT(res_id)', 'res_id_master'],
                'where'     => ['res_id_master in (?)', 'status not in (?)'],
                'data'      => [$resIds, ['DEL', 'OBS']],
                'groupBy'   => ['res_id_master']
            ]);

            $listDisplay = GroupBasketModel::get(['select' => ['list_display'], 'where' => ['basket_id = ?', 'group_id = ?'], 'data' => [$basket['basket_id'], $group['group_id']]]);
            $listDisplay = json_decode($listDisplay[0]['list_display']);

            $select = [
                'res_letterbox.res_id', 'res_letterbox.subject', 'res_letterbox.barcode', 'mlb_coll_ext.alt_identifier',
                'status.label_status AS "status.label_status"', 'status.img_filename AS "status.img_filename"'
            ];
            $tableFunction = ['status', 'mlb_coll_ext'];
            $leftJoinFunction = ['res_letterbox.status = status.id', 'res_letterbox.res_id = mlb_coll_ext.res_id'];
            foreach ($listDisplay as $value) {
                $value = (array)$value;
                if ($value['value'] == 'getPriority') {
                    $select[] = 'priorities.label AS "priorities.label"';
                    $tableFunction[] = 'priorities';
                    $leftJoinFunction[] = 'res_letterbox.priority = priorities.id';
                } elseif ($value['value'] == 'getCategory') {
                    $select[] = 'mlb_coll_ext.category_id';
                } elseif ($value['value'] == 'getDoctype') {
                    $select[] = 'doctypes.description AS "doctypes.description"';
                    $tableFunction[] = 'doctypes';
                    $leftJoinFunction[] = 'res_letterbox.type_id = doctypes.type_id';
                } elseif ($value['value'] == 'getCreationAndProcessLimitDates') {
                    $select[] = 'res_letterbox.creation_date';
                    $select[] = 'mlb_coll_ext.process_limit_date AS "mlb_coll_ext.process_limit_date"';
                }
            }

            $order = 'CASE res_letterbox.res_id ';
            foreach ($resIds as $key => $resId) {
                $order .= "WHEN {$resId} THEN {$key} ";
            }
            $order .= 'END';

            $resources = ResourceListModel::getOnResource([
                'select'    => $select,
                'table'     => $tableFunction,
                'leftJoin'  => $leftJoinFunction,
                'where'     => ['res_letterbox.res_id in (?)'],
                'data'      => [$resIds],
                'order'     => [$order]
            ]);

            foreach ($resources as $key => $resource) {
                $formattedResources[$key]['res_id'] = $resource['res_id'];
                $formattedResources[$key]['alt_identifier'] = $resource['alt_identifier'];
                $formattedResources[$key]['barcode'] = $resource['barcode'];
                $formattedResources[$key]['subject'] = $resource['subject'];
                $formattedResources[$key]['statusLabel'] = $resource['status.label_status'];
                $formattedResources[$key]['statusImage'] = $resource['status.img_filename'];
                $formattedResources[$key]['countAttachments'] = 0;
                foreach ($attachments as $attachment) {
                    if ($attachment['res_id_master'] == $resource['res_id']) {
                        $formattedResources[$key]['countAttachments'] = $attachment['count'];
                    }
                }
                $formattedResources[$key]['countNotes'] = NoteModel::countByResId(['resId' => $resource['res_id'], 'login' => $GLOBALS['userId']]);

                $display = [];
                foreach ($listDisplay as $value) {
                    $value = (array)$value;
                    if ($value['value'] == 'getPriority') {
                        $value['displayValue'] = $resource['priorities.label'];
                        $display[] = $value;
                    } elseif ($value['value'] == 'getCategory') {
                        $value['displayValue'] = ResModel::getCategoryLabel(['categoryId' => $resource['category_id']]);
                        $display[] = $value;
                    } elseif ($value['value'] == 'getDoctype') {
                        $value['displayValue'] = $resource['doctypes.description'];
                        $display[] = $value;
                    } elseif ($value['value'] == 'getAssignee') {
                        $value['displayValue'] = ResourceListController::getAssignee(['resId' => $resource['res_id']]);
                        $display[] = $value;
                    } elseif ($value['value'] == 'getSenders') {
                        $value['displayValue'] = ResourceListController::getSenders(['resId' => $resource['res_id']]);
                        $display[] = $value;
                    } elseif ($value['value'] == 'getRecipients') {
                        $value['displayValue'] = ResourceListController::getRecipients(['resId' => $resource['res_id']]);
                        $display[] = $value;
                    } elseif ($value['value'] == 'getVisaWorkflow') {
                        $value['displayValue'] = ResourceListController::getVisaWorkflow(['resId' => $resource['res_id']]);
                        $display[] = $value;
                    } elseif ($value['value'] == 'getOpinionWorkflow') {
                    } elseif ($value['value'] == 'getCreationAndProcessLimitDates') {
                        $value['displayValue'] = "{$resource['creation_date']} - {$resource['process_limit_date']}";
                        $display[] = $value;
                    }
                }
                $formattedResources[$key]['display'] = $display;
            }
        }

        return $response->withJson(['resources' => $formattedResources, 'count' => $count, 'basketLabel' => $basket['basket_name'], 'allResources' => $allResources]);
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

        if (isset($data['priorities'])) {
            if (empty($data['priorities'])) {
                $tmpWhere = 'priority is null';
            } else {
                $replace = preg_replace('/(^,)|(,$)/', '', $data['priorities']);
                $replace = preg_replace('/(,,)/', ',', $replace);
                if ($replace != $data['priorities']) {
                    $tmpWhere = '(priority is null OR priority in (?))';
                } else {
                    $tmpWhere = 'priority in (?)';
                }
                $dataCategories[] = explode(',', $replace);
                $dataStatuses[] = explode(',', $replace);
                $dataEntities[] = explode(',', $replace);
            }

            $whereCategories[] = $tmpWhere;
            $whereStatuses[] = $tmpWhere;
            $whereEntities[] = $tmpWhere;
        }
        if (isset($data['categories'])) {
            if (empty($data['categories'])) {
                $tmpWhere = 'category_id is null';
            } else {
                $replace = preg_replace('/(^,)|(,$)/', '', $data['categories']);
                $replace = preg_replace('/(,,)/', ',', $replace);
                if ($replace != $data['categories']) {
                    $tmpWhere = '(category_id is null OR category_id in (?))';
                } else {
                    $tmpWhere = 'category_id in (?)';
                }
                $dataPriorities[] = explode(',', $replace);
                $dataStatuses[] = explode(',', $replace);
                $dataEntities[] = explode(',', $replace);
            }

            $wherePriorities[] = $tmpWhere;
            $whereStatuses[] = $tmpWhere;
            $whereEntities[] = $tmpWhere;
        }
        if (!empty($data['statuses'])) {
            $wherePriorities[] = 'status in (?)';
            $dataPriorities[] = explode(',', $data['statuses']);
            $whereCategories[] = 'status in (?)';
            $dataCategories[] = explode(',', $data['statuses']);
            $whereEntities[] = 'status in (?)';
            $dataEntities[] = explode(',', $data['statuses']);
        }
        if (isset($data['entities'])) {
            if (empty($data['entities'])) {
                $tmpWhere = 'destination is null';
            } else {
                $replace = preg_replace('/(^,)|(,$)/', '', $data['entities']);
                $replace = preg_replace('/(,,)/', ',', $replace);
                if ($replace != $data['entities']) {
                    $tmpWhere = '(destination is null OR destination in (?))';
                } else {
                    $tmpWhere = 'destination in (?)';
                }
                $dataPriorities[] = explode(',', $replace);
                $dataCategories[] = explode(',', $replace);
                $dataStatuses[] = explode(',', $replace);
            }

            $wherePriorities[] = $tmpWhere;
            $whereCategories[] = $tmpWhere;
            $whereStatuses[] = $tmpWhere;
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
                $dataPriorities[] = $entitiesChildren;
                $whereCategories[] = 'destination in (?)';
                $dataCategories[] = $entitiesChildren;
                $whereStatuses[] = 'destination in (?)';
                $dataStatuses[] = $entitiesChildren;
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
            if (!empty($entity['entityId'])) {
                $children = EntityModel::getEntityChildren(['entityId' => $entity['entityId']]);
                $count = 0;
                foreach ($entities as $value) {
                    if (in_array($value['entityId'], $children)) {
                        $count += $value['count'];
                    }
                }
            } else {
                $count = $entity['count'];
            }
            $entitiesChildren[] = [
                'entityId'  => $entity['entityId'],
                'label'     => $entity['label'],
                'count'     => $count
            ];
        }

        return $response->withJson(['entities' => $entities, 'priorities' => $priorities, 'categories' => $categories, 'statuses' => $statuses, 'entitiesChildren' => $entitiesChildren]);
    }

    public static function getResourcesListQueryData(array $args)
    {
        ValidatorModel::notEmpty($args, ['basketClause', 'login']);
        ValidatorModel::stringType($args, ['basketClause', 'login']);
        ValidatorModel::arrayType($args, ['data']);

        $table = [];
        $leftJoin = [];
        $whereClause = PreparedClauseController::getPreparedClause(['clause' => $args['basketClause'], 'login' => $args['login']]);
        $where = [$whereClause];
        $queryData = [];
        $order = null;

        if (!empty($args['data']['delayed']) && $args['data']['delayed'] == 'true') {
            $where[] = 'process_limit_date < CURRENT_TIMESTAMP';
        }
        if (!empty($args['data']['search']) && mb_strlen($args['data']['search']) >= 2) {
            $where[] = '(alt_identifier ilike ? OR translate(subject, \'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ\', \'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyrr\') ilike translate(?, \'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ\', \'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyrr\'))';
            $queryData[] = "%{$args['data']['search']}%";
            $queryData[] = "%{$args['data']['search']}%";
        }
        if (isset($args['data']['priorities'])) {
            if (empty($args['data']['priorities'])) {
                $where[] = 'priority is null';
            } else {
                $replace = preg_replace('/(^,)|(,$)/', '', $args['data']['priorities']);
                $replace = preg_replace('/(,,)/', ',', $replace);
                if ($replace != $args['data']['priorities']) {
                    $where[] = '(priority is null OR priority in (?))';
                } else {
                    $where[] = 'priority in (?)';
                }
                $queryData[] = explode(',', $replace);
            }
        }
        if (isset($args['data']['categories'])) {
            if (empty($args['data']['categories'])) {
                $where[] = 'category_id is null';
            } else {
                $replace = preg_replace('/(^,)|(,$)/', '', $args['data']['categories']);
                $replace = preg_replace('/(,,)/', ',', $replace);
                if ($replace != $args['data']['categories']) {
                    $where[] = '(category_id is null OR category_id in (?))';
                } else {
                    $where[] = 'category_id in (?)';
                }
                $queryData[] = explode(',', $replace);
            }
        }
        if (!empty($args['data']['statuses'])) {
            $where[] = 'status in (?)';
            $queryData[] = explode(',', $args['data']['statuses']);
        }
        if (isset($args['data']['entities'])) {
            if (empty($args['data']['entities'])) {
                $where[] = 'destination is null';
            } else {
                $replace = preg_replace('/(^,)|(,$)/', '', $args['data']['entities']);
                $replace = preg_replace('/(,,)/', ',', $replace);
                if ($replace != $args['data']['entities']) {
                    $where[] = '(destination is null OR destination in (?))';
                } else {
                    $where[] = 'destination in (?)';
                }
                $queryData[] = explode(',', $replace);
            }
        }
        if (!empty($args['data']['entitiesChildren'])) {
            $entities = explode(',', $args['data']['entitiesChildren']);
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

        if (!empty($args['data']['order']) && strpos($args['data']['order'], 'alt_identifier') !== false) {
            $order = 'order_alphanum(alt_identifier) ' . explode(' ', $args['data']['order'])[1];
        }
        if (!empty($args['data']['order']) && strpos($args['data']['order'], 'priority') !== false) {
            $order = 'priorities.order ' . explode(' ', $args['data']['order'])[1];
            $table = ['priorities'];
            $leftJoin = ['res_view_letterbox.priority = priorities.id'];
        }

        return ['table' => $table, 'leftJoin' => $leftJoin, 'where' => $where, 'queryData' => $queryData, 'order' => $order];
    }

    public static function listControl(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'userId', 'basketId', 'currentUserId']);
        ValidatorModel::intVal($aArgs, ['groupId', 'userId', 'basketId', 'currentUserId']);

        $group = GroupModel::getById(['id' => $aArgs['groupId'], 'select' => ['group_id']]);
        $basket = BasketModel::getById(['id' => $aArgs['basketId'], 'select' => ['basket_id', 'basket_clause', 'basket_res_order', 'basket_name']]);
        if (empty($group) || empty($basket)) {
            return ['errors' => 'Group or basket does not exist', 'code' => 400];
        }

        if ($aArgs['userId'] == $aArgs['currentUserId']) {
            $redirectedBasket = RedirectBasketModel::get([
                'select'    => [1],
                'where'     => ['owner_user_id = ?', 'basket_id = ?', 'group_id = ?'],
                'data'      => [$aArgs['userId'], $basket['basket_id'], $aArgs['groupId']]
            ]);
            if (!empty($redirectedBasket[0])) {
                return ['errors' => 'Basket out of perimeter (redirected)', 'code' => 403];
            }
        } else {
            $redirectedBasket = RedirectBasketModel::get([
                'select'    => ['actual_user_id'],
                'where'     => ['owner_user_id = ?', 'basket_id = ?', 'group_id = ?'],
                'data'      => [$aArgs['userId'], $basket['basket_id'], $aArgs['groupId']]
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

        $isBasketLinked = GroupBasketModel::get(['select' => [1], 'where' => ['basket_id = ?', 'group_id = ?'], 'data' => [$basket['basket_id'], $group['group_id']]]);
        if (empty($isBasketLinked)) {
            return ['errors' => 'Group is not linked to this basket', 'code' => 400];
        }

        return ['success' => 'success'];
    }

    private static function getAssignee(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        $listInstances = ListInstanceModel::get([
            'select'    => ['item_id'],
            'where'     => ['difflist_type = ?', 'res_id = ?', 'item_mode = ?'],
            'data'      => ['entity_id', $args['res_id'], 'dest']
        ]);

        if (empty($listInstances[0])) {
            return '';
        }

        return UserModel::getLabelledUserById(['login' => $listInstances[0]['item_id']]);
    }

    private static function getVisaWorkflow(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        $listInstances = ListInstanceModel::get([
            'select'    => ['item_id', 'requested_signature', 'process_date'],
            'where'     => ['difflist_type = ?', 'res_id = ?'],
            'data'      => ['VISA_CIRCUIT', $args['res_id']],
            'orderBy'   => ['listinstance_id']
        ]);

        $users = [];
        foreach ($listInstances as $listInstance) {
            $users[] = [
                'user'  => UserModel::getLabelledUserById(['login' => $listInstance['item_id']]),
                'mode'  => $listInstance['requested_signature'] ? 'Signataire' : 'Viseur',
                'date'  => TextFormatModel::formatDate($listInstance['process_date']),
            ];
        }

        return $users;
    }

    private static function getSenders(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        $ext = ResModel::getExtById(['select' => ['category_id', 'address_id', 'exp_user_id', 'dest_user_id', 'is_multicontacts'], 'resId' => $args['res_id']]);

        $senders = [];
        if (!empty($ext)) {
            if ($ext['category_id'] == 'outgoing') {
                $resourcesContacts = ResourceContactModel::getFormattedByResId(['resId' => $args['res_id']]);
                foreach ($resourcesContacts as $resourcesContact) {
                    $senders[] = $resourcesContact['restrictedFormat'];
                }
            } else {
                $rawContacts = [];
                if ($ext['is_multicontacts'] == 'Y') {
                    $multiContacts = DatabaseModel::select([
                        'select'    => ['contact_id', 'address_id'],
                        'table'     => ['contacts_res'],
                        'where'     => ['res_id = ?', 'mode = ?'],
                        'data'      => [$args['res_id'], 'multi']
                    ]);
                    foreach ($multiContacts as $multiContact) {
                        $rawContacts[] = [
                            'login'         => $multiContact['contact_id'],
                            'address_id'    => $multiContact['address_id'],
                        ];
                    }
                } else {
                    $rawContacts[] = [
                        'login'         => $ext['dest_user_id'],
                        'address_id'    => $ext['address_id'],
                    ];
                }
                foreach ($rawContacts as $rawContact) {
                    if (!empty($rawContact['address_id'])) {
                        $contact = ContactModel::getOnView([
                            'select'    => ['is_corporate_person', 'ca_id', 'society', 'contact_firstname', 'contact_lastname'],
                            'where'     => ['ca_id = ?'],
                            'data'      => [$rawContact['address_id']]
                        ]);
                        if (isset($contact[0])) {
                            $contact = AutoCompleteController::getFormattedContact(['contact' => $contact[0]]);
                            $senders[] = $contact['contact']['restrictedFormat'];

                        }
                    } else {
                        $senders[] = UserModel::getLabelledUserById(['login' => $rawContact['login']]);
                    }
                }
            }
        }

        return $senders;
    }

    private static function getRecipients(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        $ext = ResModel::getExtById(['select' => ['category_id', 'address_id', 'exp_user_id', 'dest_user_id', 'is_multicontacts'], 'resId' => $args['res_id']]);

        $recipients = [];
        if (!empty($ext)) {
            if ($ext['category_id'] == 'outgoing') {
                $rawContacts = [];
                if ($ext['is_multicontacts'] == 'Y') {
                    $multiContacts = DatabaseModel::select([
                        'select'    => ['contact_id', 'address_id'],
                        'table'     => ['contacts_res'],
                        'where'     => ['res_id = ?', 'mode = ?'],
                        'data'      => [$args['res_id'], 'multi']
                    ]);
                    foreach ($multiContacts as $multiContact) {
                        $rawContacts[] = [
                            'login'         => $multiContact['contact_id'],
                            'address_id'    => $multiContact['address_id'],
                        ];
                    }
                } else {
                    $rawContacts[] = [
                        'login'         => $ext['dest_user_id'],
                        'address_id'    => $ext['address_id'],
                    ];
                }
                foreach ($rawContacts as $rawContact) {
                    if (!empty($rawContact['address_id'])) {
                        $contact = ContactModel::getOnView([
                            'select'    => ['is_corporate_person', 'ca_id', 'society', 'contact_firstname', 'contact_lastname'],
                            'where'     => ['ca_id = ?'],
                            'data'      => [$rawContact['address_id']]
                        ]);
                        if (isset($contact[0])) {
                            $contact = AutoCompleteController::getFormattedContact(['contact' => $contact[0]]);
                            $recipients[] = $contact['contact']['restrictedFormat'];

                        }
                    } else {
                        $recipients[] = UserModel::getLabelledUserById(['login' => $rawContact['login']]);
                    }
                }
            } else {
                $resourcesContacts = ResourceContactModel::getFormattedByResId(['resId' => $args['res_id']]);
                foreach ($resourcesContacts as $resourcesContact) {
                    $recipients[] = $resourcesContact['restrictedFormat'];
                }
            }
        }

        return $recipients;
    }
}
