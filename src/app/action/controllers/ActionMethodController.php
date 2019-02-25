<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ActionController
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Action\controllers;

use History\controllers\HistoryController;
use Resource\models\ResModel;
use Action\models\ActionModel;
use SrcCore\models\ValidatorModel;

class ActionMethodController
{
    const COMPONENTS_ACTIONS = [
        'confirmAction' => null
    ];

    public static function terminateAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'resources']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['resources']);

        $set = ['locker_user_id' => null, 'locker_time' => null, 'modification_date' => 'CURRENT_TIMESTAMP'];

        $action = ActionModel::getById(['id' => $aArgs['id'], 'select' => ['label_action', 'id_status', 'history']]);
        if (!empty($action['id_status']) && $action['id_status'] != '_NOSTATUS_') {
            $set['status'] = $action['id_status'];
        }

        ResModel::update([
            'set'   => $set,
            'where' => ['res_id in (?)'],
            'data'  => [$aArgs['resources']]
        ]);

        if ($action['history'] == 'Y') {
            foreach ($aArgs['resources'] as $resource) {
                HistoryController::add([
                    'tableName' => 'actions',
                    'recordId'  => $resource,
                    'eventType' => 'ACTION#' . $resource,
                    'eventId'   => $aArgs['id'],
                    'info'      => $action['label_action']
                ]);

                //TODO M2M
            }
        }

        return true;
    }
}
