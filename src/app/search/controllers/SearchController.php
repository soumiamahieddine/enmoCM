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

use Basket\models\BasketModel;
use Basket\models\RedirectBasketModel;
use Resource\models\ResModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\AutoCompleteController;
use SrcCore\controllers\PreparedClauseController;
use SrcCore\models\DatabaseModel;
use User\models\UserModel;

class SearchController
{
    public static function get(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();


        $entities = UserModel::getEntitiesByLogin(['login' => $GLOBALS['userId']]);
        $entities = array_column($entities, 'id');
        $entities = empty($entities) ? [0] : $entities;

        $foldersClause = 'res_id in (select res_id from folders LEFT JOIN entities_folders ON folders.id = entities_folders.folder_id LEFT JOIN resources_folders ON folders.id = resources_folders.folder_id ';
        $foldersClause .= 'WHERE entities_folders.entity_id in (?) OR folders.user_id = ?)';

        $whereClause = "(res_id in (select res_id from users_followed_resources where user_id = ?)) OR ({$foldersClause})";
        $dataClause = [$GLOBALS['id'], $entities, $GLOBALS['id']];

        $groups = UserModel::getGroupsByLogin(['login' => $GLOBALS['userId']]);
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
            'select'    => ['res_id as "resId"', 'alt_identifier as "chrono"', 'subject'],
            'where'     => $searchWhere,
            'data'      => $searchData,
            'orderBy'   => ['creation_date'],
            'limit'     => $limit,
            'offset'    => $offset
        ]);

        return $response->withJson($resources);
    }
}
