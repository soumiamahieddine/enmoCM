<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Status Controller
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Controllers;

use Core\Models\CoreConfigModel;
use Core\Models\DatabasePDO;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\HistoryModel;
use Core\Models\ValidatorModel;
use Notifications\Controllers\NotificationsEventsController;

require_once('core/class/class_functions.php');

class HistoryController
{

    /**
    * Inserts a record in the history table
    *
    * @param $aArgs array
    */
    public static function add(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['table_name', 'info', 'event_id', 'event_type', 'record_id']);

        $db = new DatabasePDO();

        if(empty($aArgs['id_module'])){
            $aArgs['id_module'] = 'admin';
        }
        if(empty($aArgs['isTech'])){
            $aArgs['isTech'] = false;
        }
        if(empty($aArgs['result'])){
            $aArgs['result'] = _OK;
        }
        if(empty($aArgs['level'])){
            $aArgs['level'] = _LEVEL_DEBUG;
        }

        $remote_ip = $_SERVER['REMOTE_ADDR'];

        $user = '';
        if (isset($_SESSION['user']['UserId'])) {
            $user = $_SESSION['user']['UserId'];
        }

        $table_name   = $aArgs['table_name'];
        $info         = $aArgs['info'];
        $record_id    = $aArgs['record_id'];
        $event_type   = $aArgs['event_type'];
        $event_id     = $aArgs['event_id'];
        $id_module    = $aArgs['id_module'];
        $result       = $aArgs['result'];
        $level        = $aArgs['level'];
        $databasetype = $db->getType();

        $traceInformations = array(
            'WHERE'         => $table_name,
            'ID'            => $record_id,
            'HOW'           => $event_type,
            'USER'          => $user,
            'WHAT'          => $event_id,
            'ID_MODULE'     => $id_module,
            'REMOTE_IP'     => $remote_ip,
            'DATABASE_TYPE' => $databasetype,
            'RESULT'        => $result,
            'LEVEL'         => $level
        );

        $loggingMethods = HistoryModel::build_logging_method();

        foreach ($loggingMethods as $logging_method) {
            if ($logging_method['ACTIVATED'] == true) {
                if ($logging_method['ID'] == 'log4php') {
                    if ($logging_method['LOGGER_NAME_TECH'] == "") {
                        $logging_method['LOGGER_NAME_TECH'] = 'loggerTechnique';
                    }
                    if ($logging_method['LOGGER_NAME_FUNC'] == "") {
                        $logging_method['LOGGER_NAME_FUNC'] = 'loggerFonctionnel';
                    }
                    self::addToLog4php([
                        'traceInformations' => $traceInformations,
                        'logging_method'    => $logging_method,
                        'isTech'            => $isTech,
                    ]);
                }
            }
        }

        if (!$isTech) {
            HistoryModel::create([
                'table_name' => $table_name,
                'record_id'  => $record_id,
                'event_type' => $event_type,
                'event_id'   => $event_id,
                'user_id'    => $user,
                'info'       => $info,
                'id_module'  => $id_module,
                'remote_ip'  => $remote_ip,
            ]);
        } else {
            //write on a log
            echo $info;
            exit;
        }

        $core = new \core_tools();
        if ($core->is_module_loaded("notifications")) {
            NotificationsEventsController::fill_event_stack($event_id, $table_name, $record_id, $user, $info);
        }
    }

    /**
    * Insert a log line into log4php
    *
    * @param  $traceInformations (array) => Informations to trace
    * @param  $logging_method (string) => Array of XML attributes
    * @param  $isTech (boolean) => Says if the log is technical (true) or functional (false)
    */
    private function addToLog4php(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['traceInformations', 'logging_method']);

        $traceInformations = $aArgs['traceInformations'];
        $logging_method    = $aArgs['logging_method'];
        $isTech            = $aArgs['isTech'];

        $configFileLog4PHP = self::getXmlFilePath(['filePath' => 'apps/maarch_entreprise/xml/log4php.xml']);
        if (!$configFileLog4PHP) {
            $configFileLog4PHP = "apps/maarch_entreprise/xml/log4php.default.xml";
        }

        \Logger::configure($configFileLog4PHP);

        if ($isTech) {
            $logger = \Logger::getLogger($logging_method['LOGGER_NAME_TECH']);
        } else {
            $logger = \Logger::getLogger($logging_method['LOGGER_NAME_FUNC']);
        }

        $searchPatterns = array(
            '%RESULT%',
            '%CODE_METIER%',
            '%WHERE%',
            '%ID%',
            '%HOW%',
            '%USER%',
            '%WHAT%',
            '%ID_MODULE%',
            '%REMOTE_IP%'
        );

        $replacePatterns = array(
            $traceInformations['RESULT'],
            $logging_method['CODE_METIER'],
            $traceInformations['WHERE'],
            $traceInformations['ID'],
            $traceInformations['HOW'],
            $traceInformations['USER'],
            $traceInformations['WHAT'],
            $traceInformations['ID_MODULE'],
            $traceInformations['REMOTE_IP']
        );

        $logLine = str_replace($searchPatterns, $replacePatterns, $logging_method['LOG_FORMAT']);

        $formatter = new \functions();

        $logLine = $formatter->wash_html($logLine, '');
        $logLine = self::wd_remove_accents(['string' => $logLine]);

        HistoryModel::writeLog([
            'logger'  => $logger,
            'logLine' => $logLine,
            'level'   => $traceInformations['LEVEL']
        ]);
    }

    /**
    * Delete accents
    *
    * @param  $str string
    * @param  $charset = 'utf-8' (string)
    *
    * @return  string $str
    */
    public static function wd_remove_accents(array $aArgs = [])
    {
        if(empty($aArgs['charset'])){
            $aArgs['charset'] = 'utf-8';
        }
        $charset = $aArgs['charset'];

        $str = htmlentities($aArgs['string'], ENT_NOQUOTES, $charset);

        $str = preg_replace(
            '#\&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring)\;#',
            '\1',
            $str
        );
        $str = preg_replace(
            '#\&([A-za-z]{2})(?:lig)\;#',
            '\1',
            $str
        );
        $str = preg_replace(
            '#\&[^;]+\;#',
            '',
            $str
        );

        return $str;
    }

    public static function getXmlFilePath(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['filePath']);

        $filePath = $aArgs['filePath'];
        $customId = CoreConfigModel::getCustomId();

        if (file_exists("custom/{$customId}/{$filePath}")) {
            $pathToXml = "custom/{$customId}/{$filePath}";
        } elseif (file_exists($filePath)) {
            $pathToXml = $filePath;
        } else {
            $pathToXml = false;
        }

        return $pathToXml;
    }
}
