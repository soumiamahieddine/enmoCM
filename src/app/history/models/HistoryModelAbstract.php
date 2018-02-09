<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief History Model
* @author dev@maarch.org
*/

namespace History\models;


use Core\Models\ValidatorModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;

require_once('apps/maarch_entreprise/tools/log4php/Logger.php'); //TODO composer

class HistoryModelAbstract
{
    public static function buildLoggingMethod()
    {
        $loggingMethods = [];

        $customId = CoreConfigModel::getCustomId();
        if (file_exists("custom/{$customId}/apps/maarch_entreprise/xml/logging_method.xml")) {
            $path = "custom/{$customId}/apps/maarch_entreprise/xml/logging_method.xml";
        } else {
            $path = 'apps/maarch_entreprise/xml/logging_method.xml';
        }

        if (file_exists($path)) {
            $xmlConfig = simplexml_load_file($path);

            if ($xmlConfig) {
                foreach ($xmlConfig->METHOD as $METHOD) {
                    $loggingMethods[] = [
                        'ID'               => (string)$METHOD->ID,
                        'ACTIVATED'        => (boolean)$METHOD->ENABLED,
                        'LOGGER_NAME_TECH' => (string)$METHOD->LOGGER_NAME_TECH,
                        'LOGGER_NAME_FUNC' => (string)$METHOD->LOGGER_NAME_FUNC,
                        'LOG_FORMAT'       => (string)$METHOD->APPLI_LOG_FORMAT,
                        'CODE_METIER'      => (string)$METHOD->CODE_METIER
                    ];
                }
            }
        } else {
            $loggingMethods[0]['ID']               = 'database';
            $loggingMethods[0]['ACTIVATED']        = true;
            $loggingMethods[1]['ID']               = 'log4php';
            $loggingMethods[1]['ACTIVATED']        = true;
            $loggingMethods[1]['LOGGER_NAME_TECH'] = 'loggerTechnique';
            $loggingMethods[1]['LOGGER_NAME_FUNC'] = 'loggerFonctionnel';
            $loggingMethods[1]['LOG_FORMAT']       = '[%RESULT%][%CODE_METIER%][%WHERE%][%ID%][%HOW%][%USER%][%WHAT%][%ID_MODULE%][%REMOTE_IP%]';
            $loggingMethods[1]['CODE_METIER']      = 'MAARCH';
        }

        return $loggingMethods;
    }

    public static function writeLog(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['logLine', 'level', 'loggerName']);
        ValidatorModel::stringType($aArgs, ['logLine', 'level', 'loggerName']);

        $customId = CoreConfigModel::getCustomId();
        if (file_exists("custom/{$customId}/apps/maarch_entreprise/xml/log4php.xml")) {
            $path = "custom/{$customId}/apps/maarch_entreprise/xml/log4php.xml";
        } else if (file_exists('apps/maarch_entreprise/xml/log4php.xml')) {
            $path = 'apps/maarch_entreprise/xml/log4php.xml';
        } else {
            $path = 'apps/maarch_entreprise/xml/log4php.default.xml';
        }

        \Logger::configure($path);
        $logger = \Logger::getLogger($aArgs['loggerName']);

        switch ($aArgs['level']) {
            case 'DEBUG':
                $logger->debug($aArgs['logLine']);
                break;
            case 'INFO':
                $logger->info($aArgs['logLine']);
                break;
            case 'WARN':
                $logger->warn($aArgs['logLine']);
                break;
            case 'ERROR':
                $logger->error($aArgs['logLine']);
                break;
            case 'FATAL':
                $logger->fatal($aArgs['logLine']);
                break;
        }
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['tableName', 'recordId', 'eventType', 'userId', 'info', 'moduleId', 'eventId']);
        ValidatorModel::stringType($aArgs, ['tableName', 'eventType', 'userId', 'info', 'moduleId', 'eventId']);

        DatabaseModel::insert([
            'table'         => 'history',
            'columnsValues' => [
                'table_name'    => $aArgs['tableName'],
                'record_id'     => $aArgs['recordId'],
                'event_type'    => $aArgs['eventType'],
                'user_id'       => $aArgs['userId'],
                'event_date'    => 'CURRENT_TIMESTAMP',
                'info'          => $aArgs['info'],
                'id_module'     => $aArgs['moduleId'],
                'remote_ip'     => $_SERVER['REMOTE_ADDR'],
                'event_id'      => $aArgs['eventId'],
            ]
        ]);

        return true;
    }

    public static function getHistoryList(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['event_date']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['history'],
            'where'     => ["event_date >= date '".$aArgs['event_date']."'","event_date < date '".$aArgs['event_date']."' + interval '1 month'"],
            'order_by'  => ['event_date DESC']
        ]);

        return $aReturn;
    }

    public static function getHistoryBatchList(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['event_date']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['history_batch'],
            'where'     => ["event_date >= date '".$aArgs['event_date']."'","event_date < date '".$aArgs['event_date']."' + interval '1 month'"],
            'order_by'  => ['event_date DESC']
        ]);

        return $aReturn;
    }

    public static function getByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $aHistories = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['history'],
            'where'     => ['user_id = ?', 'event_date > (CURRENT_TIMESTAMP - interval \'7 DAYS\')'],
            'data'      => [$aArgs['userId']],
            'order_by'  => ['event_date DESC'],
            'limit'     => 200
        ]);

        return $aHistories;
    }

    public static function getFilter(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['select','event_date']);
        ValidatorModel::stringType($aArgs, ['select']);

        $aReturn = DatabaseModel::select(
            [
            'select'    => ['DISTINCT('.$aArgs['select'].')'],
            'table'     => ['history'],
            'where'     => ["event_date >= date '".$aArgs['event_date']."'","event_date < date '".$aArgs['event_date']."' + interval '1 month'"],
            'order_by'  => [$aArgs['select'].' ASC']
            ]
        );

        return $aReturn;
    }

    public static function getBatchFilter(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['select','event_date']);
        ValidatorModel::stringType($aArgs, ['select']);

        $aReturn = DatabaseModel::select(
            [
            'select'    => ['DISTINCT('.$aArgs['select'].')'],
            'table'     => ['history_batch'],
            'where'     => ["event_date >= date '".$aArgs['event_date']."'","event_date < date '".$aArgs['event_date']."' + interval '1 month'"],
            'order_by'  => [$aArgs['select'].' ASC']
            ]
        );

        return $aReturn;
    }
}
