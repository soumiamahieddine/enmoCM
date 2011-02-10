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
 * @brief Library to fill the stack
 *
 * @file
 * @author  Laurent Giovannoni  <dev@maarch.org>
 * @date $date$
 * @version $Revision$
 * @ingroup life_cycle
 */

/**
* @brief  Class to include the file error
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

/**
 * Include the file requested if exists
 * 
 * @param string $file path of the file to include
 * @return nothing
 */
function MyInclude($file) 
{
    if (file_exists($file)) {
        include_once ($file);
    } else {
        throw new IncludeFileError($file);
    }
}

try {
    include('Maarch_CLITools/ArgsParser.php');
    include('Maarch_CLITools/Logger.php');
    include('Maarch_CLITools/FileHandler.php');
    include('Maarch_CLITools/ConsoleHandler.php');
} catch (IncludeFileError $e) {
    echo 'Maarch_CLITools required ! \n (pear.maarch.org)\n';
    exit(6);
}

// Globals variables definition
$configFile = '';
$table = '';
$policy = '';
$cycle = '';
$steps = Array();
$databasetype = '';
$exitCode = 0;
$collections = array();
$wb = '';
$stackSizeLimit = '';

/**
 * Execute a sql query
 * 
 * @param object $dbConn connection object to the database
 * @param string $queryTxt path of the file to include
 * @return true if ok, exit if ko
 */
function do_query($dbConn, $queryTxt) 
{
    $dbConn->connect();
    $res = $dbConn->query($queryTxt, true);
    if (!$res) {
        $GLOBALS['logger']->write('SQL Query error:' . $queryTxt, 'ERROR', 4);
        exit(4);
    }
    $GLOBALS['logger']->write('SQL query:' . $queryTxt, 'DEBUG');
    return true;
}

/**
 * Get the batch if of the fill stack process
 * 
 * @param object $db connection object to the database
 * @return nothing
 */
function getWorkBatch($db) 
{
    $req = "select param_value_int from parameters where id = "
         . "'lc_fill_stack_id'";
    $db->connect();
    $db->query($req);
    while ($reqResult = $db->fetch_array()) {
        $GLOBALS['wb'] = $reqResult[0] + 1;
    }
    if ($GLOBALS['wb'] == '') {
        $req = "insert into parameters(id, param_value_int) "
             . "values ('lc_fill_stack_id', 1)";
        $db->query($req);
        $GLOBALS['wb'] = 1;
    }
    $db->disconnect();
}

/**
 * Update the database with the new batch id of the fill stack process
 * 
 * @param object $db connection object to the database
 * @return nothing
 */
function updateWorkBatch($db) 
{
    $db->connect();
    $req = "update parameters set param_value_int  = " . $GLOBALS['wb'] 
         . " where id = 'lc_fill_stack_id'";
    $db->query($req);
    $db->disconnect();
}

// Defines scripts arguments
$argsparser = new ArgsParser();
// The config file
$argsparser->add_arg(
    'config', array (
    'short' => 'c',
    'long' => 'config',
    'mandatory' => true,
    'help' => 'Config file path is mandatory.'
    )
);
// The res collection target
$argsparser->add_arg(
    'collection', array (
    'short' => 'coll',
    'long' => 'collection',
    'mandatory' => true,
    'help' => 'Collection target is mandatory.'
    )
);
// The life cycle policy
$argsparser->add_arg(
    'policy', array (
    'short' => 'p',
    'long' => 'policy',
    'mandatory' => true,
    'help' => 'Policy is mandatory.'
    )
);
// The cycle of the policy
$argsparser->add_arg(
    'cycle', array (
    'short' => 'cy',
    'long' => 'cycle',
    'mandatory' => true,
    'help' => 'Cycle is mandatory.'
    )
);
// Log management
$GLOBALS['logger'] = new Logger();
$GLOBALS['logger']->set_threshold_level('DEBUG');
$console = new ConsoleHandler();
$GLOBALS['logger']->add_handler($console);
$file = new FileHandler('logs/log.txt');
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
        $GLOBALS['logger']->write('Configuration file missing', 'ERROR', 1);
        exit(1);
    }
    if ($e->arg_name == 'collection') {
        $GLOBALS['logger']->write('Collection missing', 'ERROR', 1);
        exit(1);
    }
    if ($e->arg_name == 'policy') {
        $GLOBALS['logger']->write('Policy missing', 'ERROR', 1);
        exit(1);
    }
    if ($e->arg_name == 'cycle') {
        $GLOBALS['logger']->write('Cycle missing', 'ERROR', 1);
        exit(1);
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
    $GLOBALS['logger']->write(
        'Configuration file ' . $GLOBALS['configFile'] 
        . ' does not exist', 'ERROR', 3
    );
    exit(3);
}
// Loading config file
$GLOBALS['logger']->write(
    'Load xml config file:' . $GLOBALS['configFile'], 
    'INFO'
);
$xmlconfig = simplexml_load_file($GLOBALS['configFile']);
if ($xmlconfig == FALSE) {
    $GLOBALS['logger']->write(
        'Error on loading config file:' 
        . $GLOBALS['configFile'], 'ERROR', 5
    );
    exit(5);
}
// Load the config vars
$config = $xmlconfig->CONFIG;
$lang = (string) $config->Lang;
$maarchDirectory = (string) $config->MaarchDirectory;
$MaarchApps = (string) $config->MaarchApps;
$logLevel = (string) $config->LogLevel;
$DisplayedLogLevel = (string) $config->DisplayedLogLevel;
$GLOBALS['stackSizeLimit'] = (string) $config->StackSizeLimit;
$GLOBALS['databasetype'] = (string) $xmlconfig->CONFIG_BASE->DatabaseType;
$i = 0;
foreach ($xmlconfig->COLLECTION as $col) {
    $GLOBALS['collections'][$i] = array(
                                    'id' => (string) $col->Id, 
                                    'table' => (string) $col->Table, 
                                    'view' => (string) $col->View, 
                                    'adr' => (string) $col->Adr
    );
    if ($GLOBALS['collections'][$i]['id'] == $GLOBALS['collection']) {
        $GLOBALS['table'] = $GLOBALS['collections'][$i]['table'];
    }
    $i++;
}
if ($GLOBALS['table'] == '') {
    $GLOBALS['logger']->write(
        'Collection:' . $GLOBALS['collection'] 
        . ' unknow', 'ERROR', 13
    );
    exit(13);
}
if ($logLevel == 'DEBUG') {
    error_reporting(E_ALL);
}
$GLOBALS['logger']->change_handler_log_level($file, $logLevel);
$GLOBALS['logger']->change_handler_log_level($console, $DisplayedLogLevel);
unset($xmlconfig);
set_include_path(get_include_path() . PATH_SEPARATOR . $maarchDirectory);
// Include library
try {
    MyInclude(
        $maarchDirectory . 'core' . DIRECTORY_SEPARATOR . 'class' 
        . DIRECTORY_SEPARATOR . 'class_functions.php'
    );
    MyInclude(
        $maarchDirectory . 'core' . DIRECTORY_SEPARATOR . 'class' 
        . DIRECTORY_SEPARATOR . 'class_db.php'
    );
    MyInclude(
        $maarchDirectory . 'core' . DIRECTORY_SEPARATOR . 'class' 
        . DIRECTORY_SEPARATOR . 'class_core_tools.php'
    );
    MyInclude(
        $maarchDirectory . 'core' . DIRECTORY_SEPARATOR . 'class' 
        . DIRECTORY_SEPARATOR . 'class_history.php'
    );
    MyInclude(
        $maarchDirectory . 'core' . DIRECTORY_SEPARATOR . 'core_tables.php'
    );
    MyInclude(
        $maarchDirectory . 'modules' .DIRECTORY_SEPARATOR . 'life_cycle' 
        .DIRECTORY_SEPARATOR . 'life_cycle_tables_definition.php'
    );
} catch (IncludeFileError $e) {
    $GLOBALS['logger']->write(
        'Problem with the php include path:' . get_include_path(), 
        'ERROR', 
        12
    );
    exit(12);
}
core_tools::load_lang($lang, $maarchDirectory, $MaarchApps);
$db = new dbquery($GLOBALS['configFile']);
$dbBis = new dbquery($GLOBALS['configFile']);
getWorkBatch($db);

