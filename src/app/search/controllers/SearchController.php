<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Search Controller
* @author dev@maarch.org
*/

namespace Search\controllers;

use Attachment\models\AttachmentModel;
use Basket\models\BasketModel;
use Basket\models\RedirectBasketModel;
use Priority\models\PriorityModel;
use Resource\models\ResModel;
use Resource\models\ResourceContactModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\AutoCompleteController;
use SrcCore\controllers\PreparedClauseController;
use SrcCore\models\DatabaseModel;
use Status\models\StatusModel;
use User\models\UserModel;

class SearchController
{
    public static function get(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        $entities = UserModel::getEntitiesByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $entities = array_column($entities, 'id');
        $entities = empty($entities) ? [0] : $entities;

        $foldersClause = 'res_id in (select res_id from folders LEFT JOIN entities_folders ON folders.id = entities_folders.folder_id LEFT JOIN resources_folders ON folders.id = resources_folders.folder_id ';
        $foldersClause .= 'WHERE entities_folders.entity_id in (?) OR folders.user_id = ?)';

        $whereClause = "(res_id in (select res_id from users_followed_resources where user_id = ?)) OR ({$foldersClause})";
        $dataClause = [$GLOBALS['id'], $entities, $GLOBALS['id']];

        $groups = UserModel::getGroupsByLogin(['login' => $GLOBALS['userId'], 'select' => ['where_clause']]);
        $groupsClause = '';
        foreach ($groups as $key => $group) {
            if (!empty($group['where_clause'])) {
                $groupClause = PreparedClauseController::getPreparedClause(['clause' => $group['where_clause'], 'login' => $GLOBALS['userId']]);
                if ($key > 0) {
                    $groupsClause .= ' or ';
                }
                $groupsClause .= "({$groupClause})";
            }
        }
        if (!empty($groupsClause)) {
            $whereClause .= " OR ({$groupsClause})";
        }

        $baskets = BasketModel::getBasketsByLogin(['login' => $GLOBALS['userId']]);
        $basketsClause = '';
        foreach ($baskets as $basket) {
            if (!empty($basket['basket_clause'])) {
                $basketClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'login' => $GLOBALS['userId']]);
                if (!empty($basketsClause)) {
                    $basketsClause .= ' or ';
                }
                $basketsClause .= "({$basketClause})";
            }
        }
        $assignedBaskets = RedirectBasketModel::getAssignedBasketsByUserId(['userId' => $GLOBALS['id']]);
        foreach ($assignedBaskets as $basket) {
            if (!empty($basket['basket_clause'])) {
                $basketOwner = UserModel::getById(['id' => $basket['owner_user_id'], 'select' => ['user_id']]);
                $basketClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'login' => $basketOwner['user_id']]);
                if (!empty($basketsClause)) {
                    $basketsClause .= ' or ';
                }
                $basketsClause .= "({$basketClause})";
            }
        }
        if (!empty($basketsClause)) {
            $whereClause .= " OR ({$basketsClause})";
        }


        $searchWhere = ["({$whereClause})"];
        $searchData = $dataClause;

        if (!empty($queryParams['resourceField'])) {
            $fields = ['subject', 'alt_identifier'];
            $fields = AutoCompleteController::getUnsensitiveFieldsForRequest(['fields' => $fields]);
            $requestData = AutoCompleteController::getDataForRequest([
                'search'        => $queryParams['resourceField'],
                'fields'        => $fields,
                'where'         => [],
                'data'          => [],
                'fieldsNumber'  => 2
            ]);
            $searchWhere = array_merge($searchWhere, $requestData['where']);
            $searchData = array_merge($searchData, $requestData['data']);
        }
        if (!empty($queryParams['contactField'])) {
            $fields = ['company', 'firstname', 'lastname'];
            $fields = AutoCompleteController::getUnsensitiveFieldsForRequest(['fields' => $fields]);
            $requestData = AutoCompleteController::getDataForRequest([
                'search'        => $queryParams['contactField'],
                'fields'        => $fields,
                'where'         => ['type = ?'],
                'data'          => ['contact'],
                'fieldsNumber'  => 3
            ]);

            $contactsMatch = DatabaseModel::select([
                'select'    => ['res_id'],
                'table'     => ['resource_contacts', 'contacts'],
                'left_join' => ['resource_contacts.item_id = contacts.id'],
                'where'     => $requestData['where'],
                'data'      => $requestData['data']
            ]);
            if (!empty($contactsMatch)) {
                $contactsMatch = array_column($contactsMatch, 'res_id');
                $searchWhere[] = 'res_id in (?)';
                $searchData[] = $contactsMatch;
            }
        }

        $limit = 25;
        if (!empty($queryParams['limit']) && is_numeric($queryParams['limit'])) {
            $limit = (int)$queryParams['limit'];
        }
        $offset = 0;
        if (!empty($queryParams['offset']) && is_numeric($queryParams['offset'])) {
            $offset = (int)$queryParams['offset'];
        }

        $resources = ResModel::getOnView([
            'select'    => [
                'res_id as "resId"', 'alt_identifier as "chrono"', 'subject', 'barcode', 'filename', 'creation_date as "creationDate"',
                'type_id as "type"', 'priority', 'status', 'dest_user as "destUser"'
            ],
            'where'     => $searchWhere,
            'data'      => $searchData,
            'orderBy'   => ['creation_date  DESC'],
            'limit'     => $limit,
            'offset'    => $offset
        ]);

        if (empty($resources)) {
            return $response->withJson([]);
        }

        $resourcesIds = array_column($resources, 'resId');
        $attachments = AttachmentModel::get(['select' => ['count(1)', 'res_id_master'], 'where' => ['res_id_master in (?)', 'status not in (?)'], 'data' => [$resourcesIds, ['DEL']], 'groupBy' => ['res_id_master']]);

        $prioritiesIds = array_column($resources, 'priority');
        $priorities = PriorityModel::get(['select' => ['id', 'color'], 'where' => ['id in (?)'], 'data' => [$prioritiesIds]]);

        $statusesIds = array_column($resources, 'status');
        $statuses = StatusModel::get(['select' => ['id', 'label_status', 'img_filename'], 'where' => ['id in (?)'], 'data' => [$statusesIds]]);

        $correspondents = ResourceContactModel::get([
            'select'    => ['item_id as id', 'type', 'mode', 'res_id'],
            'where'     => ['res_id in (?)'],
            'data'      => [$resourcesIds]
        ]);

        foreach ($resources as $key => $resource) {
            if (!empty($resource['priority'])) {
                foreach ($priorities as $priority) {
                    if ($priority['id'] == $resource['priority']) {
                        $resources[$key]['priorityColor'] = $priority['color'];
                    }
                }
            }
            if (!empty($resource['status'])) {
                foreach ($statuses as $status) {
                    if ($status['id'] == $resource['status']) {
                        $resources[$key]['statusLabel'] = $status['label_status'];
                        $resources[$key]['statusImage'] = $status['img_filename'];
                    }
                }
            }
            if (!empty($resource['destUser'])) {
                $resources[$key]['destUserLabel'] = UserModel::getLabelledUserById(['login' => $resource['destUser']]);
            }
            $resources[$key]['hasDocument'] = !empty($resource['filename']);

            $resources[$key]['senders'] = [];
            $resources[$key]['recipients'] = [];
            foreach ($correspondents as $correspondent) {
                if ($correspondent['res_id'] == $resource['resId']) {
                    unset($correspondent['res_id']);
                    $resources[$key]["{$correspondent['mode']}s"] = $correspondent;
                }
            }

            $resources[$key]['attachments'] = 0;
            foreach ($attachments as $attachment) {
                if ($attachment['res_id_master'] == $resource['resId']) {
                    $resources[$key]['attachments'] = $attachment['count'];
                }
            }
        }

        return $response->withJson($resources);
    }
}
