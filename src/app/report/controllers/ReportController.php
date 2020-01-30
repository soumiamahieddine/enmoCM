<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Report Controller
* @author dev@maarch.org
*/

namespace Report\controllers;

use Group\controllers\PrivilegeController;
use Group\models\GroupModel;
use History\controllers\HistoryController;
use Report\models\ReportModel;
use Slim\Http\Request;
use Slim\Http\Response;

class ReportController
{
    public function getGroups(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_reports', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson(['groups' => GroupModel::get(['orderBy' => ['group_desc']])]);
    }

    public function getByGroupId(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_reports', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $group = GroupModel::getByGroupId(['groupId' => $aArgs['groupId']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        $reports = ReportModel::getByGroupId(['groupId' => $aArgs['groupId']]);

        return $response->withJson(['reports' => $reports]);
    }

    public function updateForGroupId(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_reports', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $group = GroupModel::getByGroupId(['groupId' => $aArgs['groupId']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        $data = $request->getParams();

        $reports = ReportModel::getByGroupId(['groupId' => $aArgs['groupId']]);

        $selectedReports = [];
        foreach ($data as $value) {
            if (!empty($value['checked'])) {
                $selectedReports[] = $value['id'];
            }
        }

        $reportIdsToDelete = [];
        foreach ($reports as $value) {
            if (!$value['checked'] && in_array($value['id'], $selectedReports)) {
                ReportModel::addForGroupId(['groupId' => $aArgs['groupId'], 'reportId' => $value['id']]);
            } elseif ($value['checked'] && !in_array($value['id'], $selectedReports)) {
                $reportIdsToDelete[] = $value['id'];
            }
        }

        if (!empty($reportIdsToDelete)) {
            ReportModel::deleteForGroupId(['groupId' => $aArgs['groupId'], 'reportIds' => $reportIdsToDelete]);
        }

        HistoryController::add([
            'tableName' => 'usergroups_reports',
            'recordId'  => $aArgs['groupId'],
            'eventType' => 'UP',
            'info'      => _REPORT_MODIFICATION,
            'moduleId'  => 'report',
            'eventId'   => 'reportModification',
        ]);
        
        return $response->withJson(['success' => 'success']);
    }
}

