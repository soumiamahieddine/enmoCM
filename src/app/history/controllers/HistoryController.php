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

use Core\Models\TextFormatModel;
use Core\Models\UserModel;
use Core\Models\ServiceModel;
use SrcCore\models\ValidatorModel;
use History\models\HistoryModel;
use Notification\controllers\NotificationsEventsController;
use Slim\Http\Request;
use Slim\Http\Response;

class HistoryController
{

    public static function add(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['tableName', 'recordId', 'eventType', 'info', 'eventId']);
        ValidatorModel::stringType($aArgs, ['tableName', 'eventType', 'info', 'eventId', 'moduleId', 'level']);

        if(empty($aArgs['isTech'])){
            $aArgs['isTech'] = false;
        }
        if(empty($aArgs['moduleId'])){
            $aArgs['moduleId'] = 'admin';
        }
        if(empty($aArgs['level'])){
            $aArgs['level'] = 'DEBUG';
        }

        $traceInformations = [
            'WHERE'         => $aArgs['tableName'],
            'ID'            => $aArgs['recordId'],
            'HOW'           => $aArgs['eventType'],
            'USER'          => $GLOBALS['userId'],
            'WHAT'          => $aArgs['eventId'],
            'ID_MODULE'     => $aArgs['moduleId'],
            'REMOTE_IP'     => $_SERVER['REMOTE_ADDR'],
            'LEVEL'         => $aArgs['level']
        ];

        $loggingMethods = HistoryModel::buildLoggingMethod();

        foreach ($loggingMethods as $loggingMethod) {
            if ($loggingMethod['ACTIVATED'] == true) {
                if ($loggingMethod['ID'] == 'log4php') {
                    if (empty($loggingMethod['LOGGER_NAME_TECH'])) {
                        $loggingMethod['LOGGER_NAME_TECH'] = 'loggerTechnique';
                    }
                    if (empty($loggingMethod['LOGGER_NAME_FUNC'])) {
                        $loggingMethod['LOGGER_NAME_FUNC'] = 'loggerFonctionnel';
                    }
                    HistoryController::addToLog4php([
                        'traceInformations' => $traceInformations,
                        'loggingMethod'     => $loggingMethod,
                        'isTech'            => $aArgs['isTech'],
                    ]);
                }
            }
        }

        HistoryModel::create([
            'tableName' => $aArgs['tableName'],
            'recordId'  => $aArgs['recordId'],
            'eventType' => $aArgs['eventType'],
            'userId'    => $GLOBALS['userId'],
            'info'      => $aArgs['info'],
            'moduleId'  => $aArgs['moduleId'],
            'eventId'   => $aArgs['eventId'],
        ]);

       NotificationsEventsController::fill_event_stack([
        "eventId"   => $aArgs['eventId'],
        "tableName" => $aArgs['tableName'],
        "recordId"  => $aArgs['recordId'],
        "userId"    => $GLOBALS['userId'],
        "info"      => $aArgs['info'],
       ]);

    }

    private static function addToLog4php(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['traceInformations', 'loggingMethod']);
        ValidatorModel::arrayType($aArgs, ['traceInformations', 'loggingMethod']);

        $logLine = str_replace(
            [
                '%RESULT%',
                '%CODE_METIER%',
                '%WHERE%',
                '%ID%',
                '%HOW%',
                '%USER%',
                '%WHAT%',
                '%ID_MODULE%',
                '%REMOTE_IP%'
            ],
            [
                'OK',
                $aArgs['loggingMethod']['CODE_METIER'],
                $aArgs['traceInformations']['WHERE'],
                $aArgs['traceInformations']['ID'],
                $aArgs['traceInformations']['HOW'],
                $aArgs['traceInformations']['USER'],
                $aArgs['traceInformations']['WHAT'],
                $aArgs['traceInformations']['ID_MODULE'],
                $aArgs['traceInformations']['REMOTE_IP']
            ],
            $aArgs['loggingMethod']['LOG_FORMAT']
        );

        $loggerName = (empty($aArgs['isTech']) ? $aArgs['loggingMethod']['LOGGER_NAME_FUNC'] : $aArgs['loggingMethod']['LOGGER_NAME_TECH']);
        $logLine = TextFormatModel::htmlWasher($logLine);
        $logLine = TextFormatModel::removeAccent(['string' => $logLine]);

        HistoryModel::writeLog([
            'loggerName'    => $loggerName,
            'logLine'       => $logLine,
            'level'         => $aArgs['traceInformations']['LEVEL']
        ]);
    }

    public function getByUserId(Request $request, Response $response, array $aArgs)
    {
        $user = UserModel::getById(['id' => $aArgs['userSerialId'], 'select' => ['user_id']]);
        if ($user['user_id'] != $GLOBALS['userId'] && !ServiceModel::hasService(['id' => 'view_history', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $aHistories = HistoryModel::getByUserId(['userId' => $user['user_id'], 'select' => ['info', 'event_date']]);

        return $response->withJson(['histories' => $aHistories]);
    }

    public function getForAdministration(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'view_history', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $historyList = HistoryModel::getHistoryList(['event_date' => $aArgs['date']]);
        $historyListFilters['users'] = HistoryModel::getFilter(['select' => 'user_id', 'event_date' => $aArgs['date']]);
        $historyListFilters['eventType'] = HistoryModel::getFilter(['select' => 'event_type', 'event_date' => $aArgs['date']]);

        return $response->withJson(['filters' => $historyListFilters, 'historyList' => $historyList]);
    }

    public function getBatchForAdministration(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'view_history_batch', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $historyList = HistoryModel::getHistoryBatchList(['event_date' => $aArgs['date']]);
        $historyListFilters['modules'] = HistoryModel::getBatchFilter(['select' => 'module_name', 'event_date' => $aArgs['date']]);
        
        return $response->withJson(['filters' => $historyListFilters, 'historyList' => $historyList]);
    }
}
