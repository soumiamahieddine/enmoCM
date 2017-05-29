<?php

/**
*   @copyright 2017 Maarch
*
*   This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once 'core'. DIRECTORY_SEPARATOR . 'services'. DIRECTORY_SEPARATOR . 'Abstract.php';
require_once 'core'. DIRECTORY_SEPARATOR . 'services'. DIRECTORY_SEPARATOR . 'String.php';
if (!defined('_LOG4PHP'))
    define(
        '_LOG4PHP',
        'log4php'
    );

if (!defined('_BDD'))
    define(
        '_BDD',
        'database'
    );

if (!defined('_LOGGER_NAME_TECH_DEFAULT'))
    define(
        '_LOGGER_NAME_TECH_DEFAULT',
        'loggerTechnique'
    );

if (!defined('_LOGGER_NAME_FUNC_DEFAULT'))
    define(
        '_LOGGER_NAME_FUNC_DEFAULT',
        'loggerFonctionnel'
    );
require_once 'apps'. DIRECTORY_SEPARATOR . 'maarch_entreprise'. DIRECTORY_SEPARATOR . 'tools'. DIRECTORY_SEPARATOR . 'log4php'. DIRECTORY_SEPARATOR . 'Logger.php';

class Core_LogsAbstract_Service extends Core_Abstract_Service {

    protected static function getLoggingMethodConfFile()
    {
        $xmlFileName = 'logging_method.xml';
        if (file_exists($sLoggingMethodConfFile = 'custom'
            .DIRECTORY_SEPARATOR.$_SESSION['custom_override_id']
            .DIRECTORY_SEPARATOR . 'apps'
            .DIRECTORY_SEPARATOR.$_SESSION['config']['app_id']
            .DIRECTORY_SEPARATOR.'xml'
            .DIRECTORY_SEPARATOR.$xmlFileName
        )) {
            return $sLoggingMethodConfFile;
        }
        if (file_exists($sLoggingMethodConfFile =
            'apps'
            .DIRECTORY_SEPARATOR.$_SESSION['config']['app_id']
            .DIRECTORY_SEPARATOR.'xml'
            .DIRECTORY_SEPARATOR.$xmlFileName
        )) {
            return $sLoggingMethodConfFile;
        }
        return null;
    }
    public static function getLoggingFormat(array $aArgs = []) {
    	self::checkRequired($aArgs,['key']);
    	self::checkNumeric($aArgs,['key']);
    	$logging_methods = self::getLoggingMethod($aArgs);
    	return empty($logging_methods[$aArgs['key']]['LOG_FORMAT']) ? '[%RESULT%][%CODE_METIER%][%WHERE%][%ID%][%HOW%][%USER%][%WHAT%][%ID_MODULE%][%REMOTE_IP%]' : $logging_methods[$aArgs['key']]['LOG_FORMAT'];
    }
    public static function getLoggingCodeMetier(array $aArgs = []) {
    	self::checkRequired($aArgs,['key']);
    	self::checkNumeric($aArgs,['key']);
    	$logging_methods = self::getLoggingFormat($aArgs);
    	return empty($logging_methods[$aArgs['key']]['CODE_METIER']) ? '[%RESULT%][%CODE_METIER%][%WHERE%][%ID%][%HOW%][%USER%][%WHAT%][%ID_MODULE%][%REMOTE_IP%]' : $logging_methods[$aArgs['key']]['CODE_METIER'];
    }
    /**
    * Get the logging method in the configuration file
    */
    protected static function getLoggingMethod(array $aArgs = []) {
        $sLoggingMethodConfFile = self::getLoggingMethodConfFile();

        $logging_methods = [];
    	if ( ! $sLoggingMethodConfFile = self::getLoggingMethodConfFile() ) {
            $logging_methods[0]['ID']               = 'database';
            $logging_methods[0]['ACTIVATED']        = true;
            $logging_methods[1]['ID']               = 'log4php';
            $logging_methods[1]['ACTIVATED']        = true;
            $logging_methods[1]['LOGGER_NAME_TECH'] = 'loggerTechnique';
            $logging_methods[1]['LOGGER_NAME_FUNC'] = 'loggerFonctionnel';
            $logging_methods[1]['LOG_FORMAT']       = '[%RESULT%][%CODE_METIER%][%WHERE%][%ID%][%HOW%][%USER%][%WHAT%][%ID_MODULE%][%REMOTE_IP%]';
            $logging_methods[1]['CODE_METIER']      = 'MAARCH';
            return $logging_methods;
        }

        if (! file_exists($sLoggingMethodConfFile) ) {
            throw new \Exception('not file_exists : '.$sLoggingMethodConfFile);
        }
        $xmlConfig = @simplexml_load_file($sLoggingMethodConfFile);
        if (! $xmlConfig) {
            throw new \Exception('simplexml_load_file failed : '.$sLoggingMethodConfFile);
        }
        if (! $xmlConfig->METHOD) {
            throw new \Exception('no data METHOD found : '.$sLoggingMethodConfFile);
        }

        foreach ($xmlConfig->METHOD as $METHOD) {
            $id = ((string)$METHOD->ID);
            $activated = ((boolean)$METHOD->ENABLED);
            $loggerNameTech = ((string)$METHOD->LOGGER_NAME_TECH);
            $loggerNameFunc = ((string)$METHOD->LOGGER_NAME_FUNC);
            $logFormat = ((string)$METHOD->APPLI_LOG_FORMAT);
            $codeMetier = ((string)$METHOD->CODE_METIER);

            array_push(
                $logging_methods,
                array(
                    'ID'         => $id,
                    'ACTIVATED'  => $activated,
                    'LOGGER_NAME_TECH' => $loggerNameTech,
                    'LOGGER_NAME_FUNC' => $loggerNameFunc,
                    'LOG_FORMAT'    => $logFormat,
                    'CODE_METIER'   => $codeMetier
                )
            );
        }

        return $logging_methods;
    }

	protected static function getConfFile(array $aArgs = []) {
		if ( empty($_SESSION['config']['app_id']) ) {
			$_SESSION['config']['app_id'] = 'maarch_entreprise';
		}
        if (
        	!empty($_SESSION['config']['corepath']) && !empty($_SESSION['custom_override_id'])
        	&& file_exists($configFileLog4PHP =
            $_SESSION['config']['corepath']. DIRECTORY_SEPARATOR . 'custom'
            . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
            . DIRECTORY_SEPARATOR . 'apps'
            . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
            . DIRECTORY_SEPARATOR . 'xml'
            . DIRECTORY_SEPARATOR . 'log4php.xml'
        )) {
            return $configFileLog4PHP;
        }
        if (file_exists($configFileLog4PHP =
            'apps'
            . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
            . DIRECTORY_SEPARATOR . 'xml'
            . DIRECTORY_SEPARATOR . 'log4php.xml'
        )) {
            return $configFileLog4PHP;
        }
        return 'apps'
            . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
            . DIRECTORY_SEPARATOR . 'xml'
            . DIRECTORY_SEPARATOR . 'log4php.xml.default';
	}

	protected static function format_message(array &$aArgs) {
		$aArgs['message'] = @$aArgs['message'];
		switch (true) {
			case is_object($aArgs['message']):
                if ( $aArgs['message'] instanceof \Exception ) {
                    $e = $aArgs['message'];
                    $aArgs['code'] = $e->getCode();
                    $aArgs['message'] = 'Exception: '.$e->getMessage();
                    $aArgs['debug'] = $e->getTraceAsString();
                    break;
                }
				$aArgs['message'] = '--object--';
				break;
			case is_array($aArgs['message']):
				$aArgs['message'] = '--array--';
				break;
			default:
				$aArgs['message'] = (string)$aArgs['message'];
				break;
		}
		$aArgs['message'] = str_replace("\n", '\n', $aArgs['message']);

		// Old method :
        $formatter = new functions();
        $aArgs['message'] = $formatter->wash_html(
            $aArgs['message'],
            ''
        );
        $aArgs['message'] = Core_String_Service::wd_remove_accents($aArgs['message']);

        if(!empty($_SESSION['user']['UserId'])){
            $aArgs['message'] = '[' . $_SESSION['user']['UserId'] . '] ' . $aArgs['message'];
        }

		return $aArgs;
	}
	protected static function logs(array $aArgs = []) {
		// Initialisation du Logger :
		Logger::configure(
            self::getConfFile()
        );
        if ( @$aArgs['class'] ) {
            Logger::getLogger($aArgs['class']);
        }
        $aLoggingMethods = self::getLoggingMethod();

        $aArgs = self::format_message($aArgs);
            if ( @$aArgs['class'] ) {
                $sLog .= "[class:{$aArgs['class']}]";
                Logger::getLogger($aArgs['class']);
            }

        foreach ($aLoggingMethods as $logging_method) {
            if ( ! $logging_method['ACTIVATED'] ) {
                continue;
            }

            if (isset($aArgs['isTech']) && $aArgs['isTech']) {
                $logger = Logger::getLogger(
                    $logging_method['LOGGER_NAME_TECH']
                );
            } else {
                if(!isset($logging_method['LOGGER_NAME_FUNC'])){
                    $logging_method['LOGGER_NAME_FUNC'] = 'loggerFonctionnel';
                }
                $logger = Logger::getLogger(
                    $logging_method['LOGGER_NAME_FUNC']
                );
            }
            if ( empty($logger) ) {
            	throw new \Exception('logger not-loading', 1);
            }
            // Format :
    		$sLog = '';
    		if ( @$aArgs['file'] ) {
    			$sLog .= "[file:{$aArgs['file']}]";
    		}
    		if ( @$aArgs['class'] ) {
    			$sLog .= "[class:{$aArgs['class']}]";
    		}
    		if ( @$aArgs['function'] ) {
    			$sLog .= "[function:{$aArgs['function']}]";
    		}
    		if ( @$aArgs['code'] ) {
    			$aArgs['code'] = (int)$aArgs['code'];
    			$sLog .= "[code:{$aArgs['code']}]";
    		}

            if(!isset($logging_method['CODE_METIER'])){
                $logging_method['CODE_METIER'] = 'SIPol';
            }
            $sLog = str_replace('%CODE_METIER%', $logging_method['CODE_METIER'], "{$sLog}{$aArgs['message']}");

            // Log :
            switch ($aArgs['type']) {
            	case 'debug':
                case _LEVEL_DEBUG:
                    $logger->debug($sLog);
                    break;

            	case 'info':
                case _LEVEL_INFO:
                    $logger->info($sLog);
                    break;

            	case 'warning':
                case _LEVEL_WARN:
                    $logger->warn($sLog);
                    break;

            	case 'error':
                case _LEVEL_ERROR:
                    $logger->error($sLog);
                    break;

                case _LEVEL_FATAL:
                    $logger->fatal($sLog);
                    break;

                default:
                    $logger->error($sLog);
            }
        }
		return true;
	}
	public static function debug(array $aArgs = []) {
		$aArgs['type'] = 'debug';
		return self::logs($aArgs);
	}
	public static function info(array $aArgs = []) {
		$aArgs['type'] = 'info';
		return self::logs($aArgs);
	}
	public static function warning(array $aArgs = []) {
		$aArgs['type'] = 'warning';
		return self::logs($aArgs);
	}
    public static function error(array $aArgs = []) {
        $aArgs['type'] = 'error';
        return self::logs($aArgs);
    }
    public static function fatal(array $aArgs = []) {
        $aArgs['type'] = _LEVEL_FATAL;
        return self::logs($aArgs);
    }

    /*
    timestart : timestamp Debut
    timeend : timestamp Fin
    level : level log4php
    message : message dans les logs
    */
    public static function executionTimeLog($timestart, $timeend, $level, $message){
        if(empty($timeend)){
            $timeend = microtime(true);
        }
        $time = $timeend - $timestart;

        self::$level(['message' => $message.'. Done in ' . number_format($time, 3) . ' secondes.']);

    }
}

