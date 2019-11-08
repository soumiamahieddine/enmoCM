<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief History Controller
* @author dev@maarch.org
*/

namespace History\controllers;

use Group\controllers\PrivilegeController;
use Resource\controllers\ResController;
use Respect\Validation\Validator;
use SrcCore\controllers\LogsController;
use SrcCore\models\ValidatorModel;
use History\models\HistoryModel;
use Notification\controllers\NotificationsEventsController;
use Slim\Http\Request;
use Slim\Http\Response;
use User\models\UserModel;

class HistoryController
{
    public function get(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'view_history', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getQueryParams();

        $check = Validator::floatVal()->notEmpty()->validate($data['startDate']);
        $check = $check && Validator::floatVal()->notEmpty()->validate($data['endDate']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $maxRequestSize = 25000;

        $histories = HistoryModel::get([
            'select'    => ['event_date', 'event_type', 'user_id', 'info', 'remote_ip'],
            'where'     => ['event_date > ?', 'event_date < ?'],
            'data'      => [date('Y-m-d H:i:s', $data['startDate']), date('Y-m-d H:i:s', $data['endDate'])],
            'orderBy'   => ['event_date DESC'],
            'limit'     => $maxRequestSize
        ]);

        $limitExceeded = (count($histories) == $maxRequestSize);

        return $response->withJson(['histories' => $histories, 'limitExceeded' => $limitExceeded]);
    }

    public static function add(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['tableName', 'recordId', 'eventType', 'info', 'eventId']);
        ValidatorModel::stringType($aArgs, ['tableName', 'eventType', 'info', 'eventId', 'moduleId', 'level', 'userId']);

        if (empty($aArgs['isTech'])) {
            $aArgs['isTech'] = false;
        }
        if (empty($aArgs['moduleId'])) {
            $aArgs['moduleId'] = 'admin';
        }
        if (empty($aArgs['level'])) {
            $aArgs['level'] = 'DEBUG';
        }

        LogsController::add($aArgs);

        if (empty($aArgs['userId'])) {
            $aArgs['userId'] = $GLOBALS['userId'];
        }

        HistoryModel::create([
            'tableName' => $aArgs['tableName'],
            'recordId'  => $aArgs['recordId'],
            'eventType' => $aArgs['eventType'],
            'userId'    => $aArgs['userId'],
            'info'      => $aArgs['info'],
            'moduleId'  => $aArgs['moduleId'],
            'eventId'   => $aArgs['eventId'],
        ]);

        NotificationsEventsController::fillEventStack([
            "eventId"   => $aArgs['eventId'],
            "tableName" => $aArgs['tableName'],
            "recordId"  => $aArgs['recordId'],
            "userId"    => $aArgs['userId'],
            "info"      => $aArgs['info'],
        ]);
    }

    public function getByUserId(Request $request, Response $response, array $aArgs)
    {
        $user = UserModel::getById(['id' => $aArgs['userSerialId'], 'select' => ['user_id']]);
        if ($user['user_id'] != $GLOBALS['userId'] && !PrivilegeController::hasPrivilege(['privilegeId' => 'view_history', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $aHistories = HistoryModel::getByUserId(['userId' => $user['user_id'], 'select' => ['info', 'event_date']]);

        return $response->withJson(['histories' => $aHistories]);
    }

    public function getByResourceId(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['resId']) || !ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $history = HistoryModel::getByResourceId(['resId' => $args['resId'], 'select' => ['info', 'event_date']]);

        return $response->withJson(['history' => $history]);
    }

    public function getWorkflowByResourceId(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['resId']) || !ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $queryParams = $request->getQueryParams();
        if (!empty($queryParams['limit']) && !Validator::intVal()->validate($queryParams['limit'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Query limit is not an int val']);
        }

        $history = HistoryModel::getWorkflowByResourceId(['resId' => $args['resId'], 'select' => ['info', 'event_date'], 'limit' => (int)$queryParams['limit']]);

        return $response->withJson(['history' => $history]);
    }
}
