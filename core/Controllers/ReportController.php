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
* @ingroup core
*/

namespace Core\Controllers;

use Core\Models\GroupModel;
use Core\Models\ServiceModel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Core\Models\ReportModel;


class ReportController
{
    public function getByGroupId(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_reports', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $group = GroupModel::getById(['groupId' => $aArgs['groupId']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        $reports = ReportModel::getByGroupId(['groupId' => $aArgs['groupId']]);

        return $response->withJson(['reports' => $reports]);
    }

     public function updateForGroupId(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_reports', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $group = GroupModel::getById(['groupId' => $aArgs['groupId']]);
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

        return $response->withJson(['success' => _SAVED_CHANGE]);
    }
}

