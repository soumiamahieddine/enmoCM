<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Indexing Controller
* @author dev@maarch.org
*/

namespace Resource\controllers;

use Action\models\ActionModel;
use Group\models\GroupModel;
use Slim\Http\Request;
use Slim\Http\Response;

class IndexingController
{
    public function getIndexingActions(Request $request, Response $response, array $aArgs)
    {
        $group = GroupModel::getGroupByLogin(['login' => $GLOBALS['userId'], 'groupId' => $aArgs['groupId'], 'select' => ['can_index', 'indexation_parameters']]);
        if (empty($group)) {
            return $response->withStatus(403)->withJson(['errors' => 'This user is not in this group']);
        }
        if (!$group[0]['can_index']) {
            return $response->withStatus(403)->withJson(['errors' => 'This group can not index document']);
        }

        $group[0]['indexation_parameters'] = json_decode($group[0]['indexation_parameters'], true);

        $actions = [];
        foreach ($group[0]['indexation_parameters']['actions'] as $value) {
            $actions[] = ActionModel::getById(['id' => $value, 'select' => ['id', 'label_action', 'component']]);
        }

        return $response->withJson(['actions' => $actions]);
    }
}
