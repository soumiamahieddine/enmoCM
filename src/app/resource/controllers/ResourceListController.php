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
use Resource\models\ResModel;
use Resource\models\ResourceListModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use User\models\UserModel;

class ResourceListController
{
    public function get(Request $request, Response $response, array $aArgs)
    {
        $group = GroupModel::getById(['id' => $aArgs['groupSerialId'], 'select' => ['group_id']]);
        $basket = BasketModel::getById(['id' => $aArgs['basketId'], 'select' => ['basket_clause', 'basket_res_order', 'basket_name']]);
        if (empty($group) || empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group or basket does not exist']);
        }

        $user = UserModel::getById(['id' => $aArgs['userId'], 'select' => ['user_id']]);
        if ($user['user_id'] == $GLOBALS['userId']) {
            $redirectedBasket = RedirectBasketModel::get([
                'select'    => [1],
                'where'     => ['owner_user_id = ?', 'basket_id = ?', 'group_id = ?'],
                'data'      => [$aArgs['userId'], $aArgs['basketId'], $aArgs['groupSerialId']]
            ]);
            if (!empty($redirectedBasket[0])) {
                return $response->withStatus(403)->withJson(['errors' => 'Basket out of perimeter (redirected)']);
            }
        } else {
            $currentUser = UserModel::getByUserId(['userId' => $GLOBALS['userId'], 'select' => ['id']]);
            $redirectedBasket = RedirectBasketModel::get([
                'select'    => ['actual_user_id'],
                'where'     => ['owner_user_id = ?', 'basket_id = ?', 'group_id = ?'],
                'data'      => [$aArgs['userId'], $aArgs['basketId'], $aArgs['groupSerialId']]
            ]);
            if (empty($redirectedBasket[0]) || $redirectedBasket[0]['actual_user_id'] != $currentUser['id']) {
                return $response->withStatus(403)->withJson(['errors' => 'Basket out of perimeter']);
            }
        }

        $groups = UserModel::getGroupsByUserId(['userId' => $user['user_id']]);
        $groupFound = false;
        foreach ($groups as $value) {
            if ($value['id'] == $aArgs['groupSerialId']) {
                $groupFound = true;
            }
        }
        if (!$groupFound) {
            return $response->withStatus(400)->withJson(['errors' => 'Group is not linked to this user']);
        }

        $isBasketLinked = GroupBasketModel::get(['select' => [1], 'where' => ['basket_id = ?', 'group_id = ?'], 'data' => [$aArgs['basketId'], $group['group_id']]]);
        if (empty($isBasketLinked)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group is not linked to this basket']);
        }
        //END OF CONTROL

        $data = $request->getQueryParams();
        $data['offset'] = (empty($data['offset']) || !is_numeric($data['offset']) ? 0 : $data['offset']);
        $data['limit'] = (empty($data['limit']) || !is_numeric($data['limit']) ? 0 : $data['limit']);

        $whereClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'login' => $user['user_id']]);
        $where = [$whereClause];
        $queryData = [];

        if (!empty($data['delayed']) && $data['delayed'] == 'true') {
            $where[] = 'process_limit_date < CURRENT_TIMESTAMP';
        }
        if (!empty($data['reference'])) {
            $where[] = 'alt_identifier ilike ?';
            $queryData[] = "%{$data['reference']}%";
        }
        if (!empty($data['subject']) && mb_strlen($data['subject']) >= 3) {
            $where[] = 'subject ilike ?';
            $queryData[] = "%{$data['subject']}%";
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
        $entities = [];
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

            $rawEntities = ResModel::getOnView([
                'select'    => ['count(res_id)', 'destination'],
                'where'     => [$whereClause],
                'groupBy'   => ['destination']
            ]);

            foreach ($rawEntities as $key => $value) {
                $entity = EntityModel::getByEntityId(['select' => ['entity_label'], 'entityId' => $value['destination']]);
                $entities[] = [
                    'entityid'  => $value['destination'],
                    'label'     => $entity['entity_label'],
                    'count'     => $value['count']
                ];
            }
        }

        return $response->withJson(['resources' => $resources, 'count' => $count, 'entities' => $entities, 'basketLabel' => $basket['basket_name']]);
    }
}
