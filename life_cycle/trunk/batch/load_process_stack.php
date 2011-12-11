<?php

/*
 *   Copyright 2008-2011 Maarch
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
 *   along with Maarch Framework. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @brief Library to process the stack
 *
 * @file
 * @author  Laurent Giovannoni  <dev@maarch.org>
 * @date $date$
 * @version $Revision$
 * @ingroup life_cycle
 */

/**
* @brief Class to include the file error
*
* @ingroup life_cycle
*/
class IncludeFileError extends Exception
{
    public function __construct($file) 
    {
        $this->file = $file;
        parent :: __construct('Include File \'$file\' is missing!', 1);
    }
}

try {
    include('Maarch_CLITools/ArgsParser.php');
    //include('Maarch_CLITools/Logger.php');
    include('LoggerLog4php.php');
    include('Maarch_CLITools/FileHandler.php');
    include('Maarch_CLITools/ConsoleHandler.php');
} catch (IncludeFileError $e) {
    echo 'Maarch_CLITools required ! \n (pear.maarch.org)\n';
    exit(106);
}
include('batch_tools.php');
// Globals variables definition
$state = '';
$configFile = '';
$MaarchDirectory = '';
$batchDirectory = '';
$batchName = 'process_stack';
$TmpDirectory = '';
$customPath = '';
$customLang = '';
$table = '';
$adrTable = '';
$view = '';
$coll = '';
$policy = '';
$cycle = '';
$steps = Array();
$currentStep = '';
$docservers = Array();
$docserverSourcePath = '';
$docserverSourceFingerprint = '';
$databasetype = '';
$exitCode = 0;
$running_date = date('Y-m-d H:i:s');
$func = '';
$db = '';
$db2 = '';
$db3 = '';
$docserverControler = '';
$wb = '';
$docserversFeatures = array();
$isAContainerOpened = false;
$lckFile = '';
$errorLckFile = '';
$totalProcessedResources = 0;
$apacheUserAndGroup =  '';
$breakKey = '';
$breakKeyValue = '';
$log4PhpEnabled = false;

// Defines scripts arguments
$argsparser = new ArgsParser();
// The config file
$argsparser->add_arg(
    'config', 
    array(
        'short' => 'c',
        'long' => 'config',
        'mandatory' => true,
        'help' => 'Config file path is mandatory.',
    )
);
// The res collection target
$argsparser->add_arg(
    'collection', 
    array(
        'short' => 'coll',
        'long' => 'collection',
        'mandatory' => true,
        'help' => 'Collection target is mandatory.',
    )
);
// The life cycle policy
$argsparser->add_arg(
    'policy', 
    array(
        'short' => 'p',
        'long' => 'policy',
        'mandatory' => true,
        'help' => 'Policy is mandatory.',
    )
);
// The cycle of the policy
$argsparser->add_arg(
    'cycle', 
    array(
        'short' => 'cy',
        'long' => 'cycle',
        'mandatory' => true,
        'help' => 'Cycle is mandatory.',
    )
);
// Log management
//$GLOBALS['logger'] = new Logger();
$GLOBALS['logger'] = new Logger4Php();
$GLOBALS['logger']->set_threshold_level('DEBUG');
$console = new ConsoleHandler();
$GLOBALS['logger']->add_handler($console);
$file = new FileHandler('logs' . DIRECTORY_SEPARATOR . 'process_stack' 
                        . DIRECTORY_SEPARATOR . date('Y-m-d_H-i-s') . '.log');
$GLOBALS['logger']->add_handler($file);
$GLOBALS['logger']->write('STATE:INIT', 'INFO');
// Parsing script options
try {
    $options = $argsparser->parse_args($GLOBALS['argv']);
    // If option = help then options = false and the script continues ...
    if ($options == false) {
        exit(0);
    }
} catch (MissingArgumentError $e) {
    if ($e->arg_name == 'config') {
        $GLOBALS['logger']->write('Configuration file missing', 'ERROR', 101);
        exit(101);
    }
    if ($e->arg_name == 'collection') {
        $GLOBALS['logger']->write('Collection missing', 'ERROR', 1);
        exit(105);
    }
    if ($e->arg_name == 'policy') {
        $GLOBALS['logger']->write('Policy missing', 'ERROR', 1);
        exit(105);
    }
    if ($e->arg_name == 'cycle') {
        $GLOBALS['logger']->write('Cycle missing', 'ERROR', 1);
        exit(105);
    }
}
$txt = '';
foreach (array_keys($options) as $key) {
    if (isset($options[$key]) && $options[$key] == false) {
        $txt .= $key . '=false,';
    } else {
        $txt .= $key . '=' . $options[$key] . ',';
    }
}
$GLOBALS['logger']->write($txt, 'DEBUG');
$GLOBALS['configFile'] = $options['config'];
$GLOBALS['collection'] = $options['collection'];
$GLOBALS['policy'] = $options['policy'];
$GLOBALS['cycle'] = $options['cycle'];
$GLOBALS['logger']->write($txt, 'INFO');
// Tests existence of config file
if (!file_exists($GLOBALS['configFile'])) {
    $GLOBALS['logger']->write('Configuration file ' . $GLOBALS['configFile'] 
                              . ' does not exist', 'ERROR', 102);
    exit(102);
}
// Loading config file
$GLOBALS['logger']->write('Load xml config file:' . $GLOBALS['configFile'], 
                          'INFO');
$xmlconfig = simplexml_load_file($GLOBALS['configFile']);
if ($xmlconfig == FALSE) {
    $GLOBALS['logger']->write('Error on loading config file:' 
                              . $GLOBALS['configFile'], 'ERROR', 103);
    exit(103);
}
// Load the config vars
$CONFIG = $xmlconfig->CONFIG;
$lang = (string) $CONFIG->Lang;
$GLOBALS['MaarchDirectory'] = (string) $CONFIG->MaarchDirectory;
$GLOBALS['batchDirectory'] = $GLOBALS['MaarchDirectory'] . 'modules' 
                           . DIRECTORY_SEPARATOR . 'life_cycle' 
                           . DIRECTORY_SEPARATOR . 'batch';
$GLOBALS['tmpDirectoryRoot'] = (string) $CONFIG->TmpDirectory;
$GLOBALS['docserversFeatures']['DOCSERVERS']['PATHTOCOMPRESSTOOL'] =
                                           (string) $CONFIG->PathToCompressTool;
$MaarchApps = (string) $CONFIG->MaarchApps;
$log_level = (string) $CONFIG->LogLevel;
$GLOBALS['logger']->set_threshold_level($logLevel);
$DisplayedLogLevel = (string) $CONFIG->DisplayedLogLevel;
$GLOBALS['customPath'] = (string) $CONFIG->CustomPath;
$GLOBALS['customLang'] = (string) $CONFIG->CustomLang;
$GLOBALS['databasetype'] = (string) $xmlconfig->CONFIG_BASE->databasetype;
$GLOBALS['apacheUserAndGroup'] = (string) $CONFIG->ApacheUserAndGroup;
$i = 0;
foreach ($xmlconfig->COLLECTION as $col) {
    $GLOBALS['collections'][$i] = array(
        'id' => (string) $col->Id, 
        'table' => (string) $col->Table, 
        'view' => (string) $col->View, 
        'adr' => (string) $col->Adr,
    );
    if ($GLOBALS['collections'][$i]['id'] == $GLOBALS['collection']) {
        $GLOBALS['table'] = $GLOBALS['collections'][$i]['table'];
        $GLOBALS['adrTable'] = $GLOBALS['collections'][$i]['adr'];
        $GLOBALS['view'] = $GLOBALS['collections'][$i]['view'];
    }
    $i++;
}
set_include_path(get_include_path() . PATH_SEPARATOR 
	. $GLOBALS['MaarchDirectory']);
//log4php params
$log4phpParams = $xmlconfig->LOG4PHP;
if ((string) $log4phpParams->enabled == 'true') {
	$GLOBALS['logger']->set_log4PhpLibrary(
		$GLOBALS['MaarchDirectory'] 
			. 'apps/maarch_entreprise/tools/log4php/Logger.php'
	);
	$GLOBALS['logger']->set_log4PhpLogger((string) $log4phpParams->Log4PhpLogger);
	$GLOBALS['logger']->set_log4PhpBusinessCode((string) $log4phpParams->Log4PhpBusinessCode);
	$GLOBALS['logger']->set_log4PhpConfigPath((string) $log4phpParams->Log4PhpConfigPath);
	$GLOBALS['logger']->set_log4PhpBatchName('life_cycle_fill_stack');
}
if ($GLOBALS['table'] == '' 
    || $GLOBALS['adrTable'] == '' 
    || $GLOBALS['view'] == ''
) {
    $GLOBALS['logger']->write('Collection:' . $GLOBALS['collection'].' unknow'
                              , 'ERROR', 110);
    exit(110);
}
if (file_exists($GLOBALS['MaarchDirectory'] . 'modules' .DIRECTORY_SEPARATOR 
                . 'life_cycle' . DIRECTORY_SEPARATOR . 'lang' 
                . DIRECTORY_SEPARATOR 
                . $lang . '.php')
) {
    include($GLOBALS['MaarchDirectory'] . 'modules' .DIRECTORY_SEPARATOR 
    . 'life_cycle' .DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR 
    . $lang . '.php');
}
if ($log_level == 'DEBUG') {
    error_reporting(E_ALL);
}
$GLOBALS['logger']->change_handler_log_level($file, $log_level);
$GLOBALS['logger']->change_handler_log_level($console, $DisplayedLogLevel);
unset($xmlconfig);
/*
set_include_path(get_include_path() . PATH_SEPARATOR 
                 . $GLOBALS['MaarchDirectory']);
*/
// Include library
try {
    Bt_myInclude($GLOBALS['MaarchDirectory'] . 'core' . DIRECTORY_SEPARATOR 
                 . 'class' . DIRECTORY_SEPARATOR . 'class_functions.php');
    Bt_myInclude($GLOBALS['MaarchDirectory'] . 'core' . DIRECTORY_SEPARATOR 
                 . 'class' . DIRECTORY_SEPARATOR . 'class_db.php');
    Bt_myInclude($GLOBALS['MaarchDirectory'] . 'core' . DIRECTORY_SEPARATOR 
                 . 'class' . DIRECTORY_SEPARATOR . 'class_request.php');
    Bt_myInclude($GLOBALS['MaarchDirectory'] . 'core' . DIRECTORY_SEPARATOR 
                 . 'class' . DIRECTORY_SEPARATOR . 'class_core_tools.php');
    Bt_myInclude($GLOBALS['MaarchDirectory'] . 'core' . DIRECTORY_SEPARATOR 
                 . 'core_tables.php');
    Bt_myInclude($GLOBALS['MaarchDirectory'] . 'modules' .DIRECTORY_SEPARATOR 
                 . 'life_cycle' .DIRECTORY_SEPARATOR 
                 . 'life_cycle_tables_definition.php');
    Bt_myInclude($GLOBALS['MaarchDirectory'] . 'core' . DIRECTORY_SEPARATOR 
                 . 'class' . DIRECTORY_SEPARATOR . 'docservers_controler.php');
    Bt_myInclude($GLOBALS['MaarchDirectory'] . 'core' . DIRECTORY_SEPARATOR 
                 . 'docservers_tools.php');
    Bt_myInclude($GLOBALS['MaarchDirectory'] . 'core' . DIRECTORY_SEPARATOR 
                 . 'class' . DIRECTORY_SEPARATOR 
                 . 'docserver_types_controler.php');
} catch (IncludeFileError $e) {
    $GLOBALS['logger']->write(
        'Problem with the php include path:' 
        . get_include_path(), 'ERROR', 111
    );
    exit(111);
}
if (!is_dir($GLOBALS['tmpDirectoryRoot'])) {
    $GLOBALS['logger']->write(
        'Problem with the tmp dir:' . $GLOBALS['tmpDirectory'], 'ERROR', 17
    );
    exit(17);
}
$coreTools = new core_tools();
$coreTools->load_lang($lang, $GLOBALS['MaarchDirectory'], $MaarchApps);
session_start();
$_SESSION['modules_loaded'] = array();
$GLOBALS['func'] = new functions();
$GLOBALS['db'] = new dbquery($GLOBALS['configFile']);
$GLOBALS['db2'] = new dbquery($GLOBALS['configFile']);
$GLOBALS['db3'] = new dbquery($GLOBALS['configFile']);
$GLOBALS['db']->connect();
$GLOBALS['db2']->connect();
$GLOBALS['db3']->connect();
$GLOBALS['docserverControler'] = new docservers_controler();
$GLOBALS['errorLckFile'] = $GLOBALS['batchDirectory'] . DIRECTORY_SEPARATOR 
                         . $GLOBALS['batchName'] . '_' . $GLOBALS['policy'] 
                         . '_' . $GLOBALS['cycle'] . '_error.lck';
$GLOBALS['lckFile'] = $GLOBALS['batchDirectory'] . DIRECTORY_SEPARATOR 
                    . $GLOBALS['batchName'] . '_' . $GLOBALS['policy'] 
                    . '_' . $GLOBALS['cycle'] . '.lck';
if (file_exists($GLOBALS['errorLckFile'])) {
    $GLOBALS['logger']->write(
        'Error persists, please solve this before launching a new batch', 
        'ERROR', 29
    );
    exit(29);
}
if (file_exists($GLOBALS['lckFile'])) {
    $GLOBALS['logger']->write(
        'An instance of the batch for policy:' . $GLOBALS['policy'] 
        . ' and the cycle:' . $GLOBALS['cycle'] . ' is already in progress',
        'ERROR', 109
    );
    exit(109);
}
$semaphore = fopen($GLOBALS['lckFile'], 'a');
fwrite($semaphore, '1');
fclose($semaphore);
Bt_getWorkBatch();
$GLOBALS['tmpDirectory'] = $GLOBALS['tmpDirectoryRoot'] . DIRECTORY_SEPARATOR 
                         . $GLOBALS['wb'] . DIRECTORY_SEPARATOR;
if (!is_dir($GLOBALS['tmpDirectory'])) {
    mkdir($GLOBALS['tmpDirectory'], 0777);
}
