<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Batch History Controller
* @author dev@maarch.org
*/

namespace History\controllers;

use Group\controllers\PrivilegeController;
use History\models\BatchHistoryModel;
use Slim\Http\Request;
use Slim\Http\Response;

class BatchHistoryController
{
    public function get(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'view_history_batch', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $queryParams = $request->getQueryParams();

        $limit = 25;
        if (!empty($queryParams['limit']) && is_numeric($queryParams['limit'])) {
            $limit = (int)$queryParams['limit'];
        }
        $offset = 0;
        if (!empty($queryParams['offset']) && is_numeric($queryParams['offset'])) {
            $offset = (int)$queryParams['offset'];
        }

        $where = [];
        $data = [];

        if (!empty($queryParams['startDate'])) {
            $where[] = 'event_date > ?';
            $data[] = date('Y-m-d H:i:s', $queryParams['startDate']);
        }
        if (!empty($queryParams['endDate'])) {
            $where[] = 'event_date < ?';
            $data[] = date('Y-m-d H:i:s', $queryParams['endDate']);
        }

        $order = !in_array($queryParams['order'], ['asc', 'desc']) ? '' : $queryParams['order'];
        $orderBy = !in_array($queryParams['orderBy'], ['event_date', 'module_name', 'total_processed', 'total_errors', 'info']) ? ['event_date DESC'] : ["{$queryParams['orderBy']} {$order}"];

        $history = BatchHistoryModel::get([
            'select'    => ['event_date', 'module_name', 'total_processed', 'total_errors', 'info', 'count(1) OVER()'],
            'where'     => $where,
            'data'      => $data,
            'orderBy'   => $orderBy,
            'offset'    => $offset,
            'limit'     => $limit
        ]);

        $total = $history[0]['count'] ?? 0;
        foreach ($history as $key => $value) {
            unset($history[$key]['count']);
        }

        return $response->withJson(['history' => $history, 'count' => $total]);
    }
}
