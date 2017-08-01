<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Status Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

use Core\Controllers\HistoryController;

require_once('apps/maarch_entreprise/tools/log4php/Logger.php');

class HistoryModelAbstract
{

    /**
    * Get the logging method in the configuration file
    */
    public static function build_logging_method()
    {
        $logging_methods = [];

        $pathToXmlLogin = HistoryController::getXmlFilePath(['filePath' => 'apps/maarch_entreprise/xml/logging_method.xml']);

        if (!$pathToXmlLogin) {
            $noXml = true;
            $logging_methods[0]['ID']               = 'database';
            $logging_methods[0]['ACTIVATED']        = true;
            $logging_methods[1]['ID']               = 'log4php';
            $logging_methods[1]['ACTIVATED']        = true;
            $logging_methods[1]['LOGGER_NAME_TECH'] = 'loggerTechnique';
            $logging_methods[1]['LOGGER_NAME_FUNC'] = 'loggerFonctionnel';
            $logging_methods[1]['LOG_FORMAT']       = '[%RESULT%][%CODE_METIER%][%WHERE%][%ID%][%HOW%][%USER%][%WHAT%][%ID_MODULE%][%REMOTE_IP%]';
            $logging_methods[1]['CODE_METIER']      = 'MAARCH';
        }

        if (!isset($noXml)) {
            $xmlConfig = simplexml_load_file($pathToXmlLogin);

            if ($xmlConfig) {
                foreach ($xmlConfig->METHOD as $METHOD) {
                    $logging_methods[] = [
                        'ID'               => (string)$METHOD->ID,
                        'ACTIVATED'        => (boolean)$METHOD->ENABLED,
                        'LOGGER_NAME_TECH' => (string)$METHOD->LOGGER_NAME_TECH,
                        'LOGGER_NAME_FUNC' => (string)$METHOD->LOGGER_NAME_FUNC,
                        'LOG_FORMAT'       => (string)$METHOD->APPLI_LOG_FORMAT,
                        'CODE_METIER'      => (string)$METHOD->CODE_METIER
                    ];
                }
            }
        }

        return $logging_methods;
    }

    /**
    * Write a log entry with a specific log level
    *
    * @param  $logger (object) => Log4php logger
    * @param  $logLine (string) => Line we want to trace
    * @param  $level (enum) => Log level
    */
    public static function writeLog(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['logger', 'logLine', 'level']);
        ValidatorModel::stringType($aArgs, ['logLine']);

        $logger  = $aArgs['logger'];
        $logLine = $aArgs['logLine'];

        switch ($aArgs['level']) {
            case _LEVEL_DEBUG:
                $logger->debug($logLine);
                break;

             case _LEVEL_INFO:
                $logger->info($logLine);
                break;

            case _LEVEL_WARN:
                $logger->warn($logLine);
                break;

            case _LEVEL_ERROR:
                $logger->error($logLine);
                break;

            case _LEVEL_FATAL:
                $logger->fatal($logLine);
                break;
        }
    }

    public static function create(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['event_type', 'user_id']);
        ValidatorModel::stringType($aArgs, ['event_type', 'user_id']);

        $db = new \Database();
        $aArgs['event_date'] = $db->current_datetime();
        $aReturn = DatabaseModel::insert([
            'table'         => 'history',
            'columnsValues' => $aArgs
        ]);

        return $aReturn;
    }
}
