<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief HistoryBatch Controller
* @author dev@maarch.org
*/

namespace History\controllers;

use Core\Models\ServiceModel;
use History\models\HistoryBatchModel;
use Slim\Http\Request;
use Slim\Http\Response;

class HistoryBatchController
{
    public function get(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'view_history_batch', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $historyList = HistoryBatchModel::get(['event_date' => $aArgs['date']]);
        $historyListFilters['modules'] = HistoryBatchModel::getFilter(['select' => 'module_name', 'event_date' => $aArgs['date']]);
        
        return $response->withJson(['filters' => $historyListFilters, 'historyList' => $historyList]);
    }
}
