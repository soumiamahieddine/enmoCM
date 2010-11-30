<?php

/*
 *    Copyright 2008, 2009, 2010 Maarch
 *
 *  This file is part of Maarch Framework.
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
 *    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @brief Batch to process the stack
 *
 * @file
 * @author  Laurent Giovannoni  <dev@maarch.org>
 * @date $date$
 * @version $Revision$
 * @ingroup life_cycle
 */

/**
 * Errors :
 *  1  : Configuration file missing
 *  2  : Configuration file does not exist
 *  3  : Error on loading config file
 *  4  : SQL Query Error
 *  5  : SQL insert Error
 *  6  : Problem with php include path
 *  7  : Stack empty
 *  8  : Cycle not found
 *  9  : Previous cycle not found
 *  10 : No resource found
 *  11 : Cycle step not found
 *  12 : Docserver type not found
 *  13 : Docserver not found
 *  14 : Problem with the php include path
 *  15 : Problem with the include of step operation file
 */

class IncludeFileError extends Exception {
	public function __construct($file) {
		$this->file = $file;
		parent :: __construct("Include File \"$file\" is missing!", 1);
	}
}

function MyInclude($file) {
	if (file_exists($file)) {
		include_once ($file);
	} else {
		throw new IncludeFileError($file);
	}
}

try {
	include("Maarch_CLITools/ArgsParser.php");
	include("Maarch_CLITools/Logger.php");
	include("Maarch_CLITools/FileHandler.php");
	include("Maarch_CLITools/ConsoleHandler.php");
} catch (IncludeFileError $e) {
	echo "Maarch_CLITools required ! \n (pear.maarch.org)\n";
	exit(6);
}

// Globals variables definition
$configFile = '';
$table = '';
$adrTable = '';
$policy = '';
$cycle = '';
$steps = Array();
$docservers = Array();
$databasetype = '';
$exitCode = 0;
$running_date = date('Y-m-d H:i:s');

function do_query($db_conn, $query_txt) {
	$db_conn->connect();
	$res = $db_conn->query($query_txt, true);
	if (!$res) {
		$GLOBALS['logger']->write('SQL Query error:' . $query_txt, 'ERROR', 4);
		exit(4);
	}
	$GLOBALS['logger']->write('SQL query:' . $query_txt, 'DEBUG');
	return true;
}

// Defines scripts arguments
$argsparser = new ArgsParser();
$argsparser->add_arg("config", array (
	'short' => "c",
	'long' => "config",
	'mandatory' => true,
	'help' => "Config file path is mandatory."
));
$argsparser->add_arg("table", array (
	'short' => "t",
	'long' => "table",
	'mandatory' => true,
	'help' => "Table target is mandatory."
));
$argsparser->add_arg("adrTable", array (
	'short' => "ad",
	'long' => "adr",
	'mandatory' => true,
	'help' => "Address table target is mandatory."
));
$argsparser->add_arg("collection", array (
	'short' => "coll",
	'long' => "collection",
	'mandatory' => true,
	'help' => "Collection target is mandatory."
));
$argsparser->add_arg("policy", array (
	'short' => "p",
	'long' => "policy",
	'mandatory' => true,
	'help' => "Policy is mandatory."
));
$argsparser->add_arg("cycle", array (
	'short' => "cy",
	'long' => "cycle",
	'mandatory' => true,
	'help' => "Cycle is mandatory."
));
// Log management
$GLOBALS['logger'] = new Logger();
$GLOBALS['logger']->set_threshold_level('DEBUG');
$console = new ConsoleHandler();
$GLOBALS['logger']->add_handler($console);
$file = new FileHandler("logs/log.txt");
$GLOBALS['logger']->add_handler($file);
$GLOBALS['logger']->write("STATE:INIT", 'INFO');
// Parsing script options
try {
	$options = $argsparser->parse_args($GLOBALS["argv"]);
	// If option = help then options = false and the script continues ...
	if ($options == false) {
		exit(0);
	}
} catch (MissingArgumentError $e) {
	if ($e->arg_name == "config") {
		$GLOBALS['logger']->write('Configuration file missing', 'ERROR', 1);
		exit(1);
	}
	if ($e->arg_name == "table") {
		$GLOBALS['logger']->write('Table missing', 'ERROR', 1);
		exit(1);
	}
	if ($e->arg_name == "adrTable") {
		$GLOBALS['logger']->write('Address table missing', 'ERROR', 1);
		exit(1);
	}
	if ($e->arg_name == "collection") {
		$GLOBALS['logger']->write('Collection missing', 'ERROR', 1);
		exit(1);
	}
	if ($e->arg_name == "policy") {
		$GLOBALS['logger']->write('Policy missing', 'ERROR', 1);
		exit(1);
	}
	if ($e->arg_name == "cycle") {
		$GLOBALS['logger']->write('Cycle missing', 'ERROR', 1);
		exit(1);
	}
}
$txt = "";
foreach (array_keys($options) as $key) {
	if (isset($options[$key]) && $options[$key] == false) {
		$txt .= $key . '=false,';
	} else {
		$txt .= $key . '=' . $options[$key] . ',';
	}
}
$GLOBALS['logger']->write($txt, 'DEBUG');
$GLOBALS['configFile'] = $options['config'];
$GLOBALS['table'] = $options['table'];
$GLOBALS['AdrTable'] = $options['adrTable'];
$GLOBALS['collection'] = $options['collection'];
$GLOBALS['policy'] = $options['policy'];
$GLOBALS['cycle'] = $options['cycle'];
$GLOBALS['logger']->write($txt, 'INFO');
// Tests existence of config file
if (!file_exists($GLOBALS['configFile'])) {
	$GLOBALS['logger']->write('Configuration file ' . $GLOBALS['configFile'] . ' does not exist', 'ERROR', 3);
	exit(3);
}
// Loading config file
$GLOBALS['logger']->write('Load xml config file:' . $GLOBALS['configFile'], 'INFO');
$xmlconfig = simplexml_load_file($GLOBALS['configFile']);
if ($xmlconfig == FALSE) {
	$GLOBALS['logger']->write('Error on loading config file:' . $GLOBALS['configFile'], 'ERROR', 5);
	exit(5);
}
$CONFIG = $xmlconfig->CONFIG;
$lang = (string) $CONFIG->lang;
$MaarchDirectory = (string) $CONFIG->MaarchDirectory;
$MaarchApps = (string) $CONFIG->MaarchApps;
$log_level = (string) $CONFIG->LogLevel;
$DisplayedLogLevel = (string) $CONFIG->DisplayedLogLevel;
$GLOBALS['databasetype'] = (string) $xmlconfig->CONFIG_BASE->databasetype;
$databasename = (string) $xmlconfig->CONFIG_BASE->databasename;
$databaseserver = (string) $xmlconfig->CONFIG_BASE->databaseserver;
$databaseserverport = (string) $xmlconfig->CONFIG_BASE->databaseserverport;
$databaseuser = (string) $xmlconfig->CONFIG_BASE->databaseuser;
$databasepassword = (string) $xmlconfig->CONFIG_BASE->databasepassword;
if ($log_level == 'DEBUG') {
	error_reporting(E_ALL);
}
$GLOBALS['logger']->change_handler_log_level($file, $log_level);
$GLOBALS['logger']->change_handler_log_level($console, $DisplayedLogLevel);
unset($xmlconfig);
set_include_path(get_include_path() . PATH_SEPARATOR . $MaarchDirectory);
try {
	MyInclude($MaarchDirectory . "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_functions.php");
	MyInclude($MaarchDirectory . "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_db.php");
	MyInclude($MaarchDirectory . "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_core_tools.php");
	MyInclude($MaarchDirectory . "core" . DIRECTORY_SEPARATOR . "core_tables.php");
	MyInclude($MaarchDirectory . "modules" .DIRECTORY_SEPARATOR . "life_cycle" .DIRECTORY_SEPARATOR . "life_cycle_tables_definition.php");
} catch (IncludeFileError $e) {
	$GLOBALS['logger']->write('Problem with the php include path:' . get_include_path(), 'ERROR', 14);
	exit(14);
}
core_tools::load_lang($lang, $MaarchDirectory, $MaarchApps);
$func = new functions();
$db = new dbquery($GLOBALS['configFile']);
$db2 = new dbquery($GLOBALS['configFile']);

/******************************************************************************************************/
/* beginning */
$state = "CONTROL_STACK";
while ($state <> "END") {
	if (isset($GLOBALS['logger'])) {
		$GLOBALS['logger']->write("STATE:".$state, 'INFO');
	}
	switch($state) {
		/**********************************************************************************************/
		case "CONTROL_STACK" :
			$db->connect();
			$query = "select * from "._LC_STACK_TABLE_NAME;
			do_query($db, $query);
			if ($db->nb_result() == 0) {
				$GLOBALS['logger']->write('WARNING stack is empty', 'ERROR', 7);
				$db->disconnect();
				$GLOBALS['exitCode'] = 7;
				$state = "END";break;
			}
			$db->disconnect();
			$state = "GET_STEPS";
			break;
		/**********************************************************************************************/
		case "GET_STEPS" :
			$db->connect();
			$query = "select * from "._LC_CYCLE_STEPS_TABLE_NAME." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."'";
			do_query($db, $query);
			if ($db->nb_result() == 0) {
				$GLOBALS['logger']->write('Cycle Steps not found', 'ERROR', 11);
				$db->disconnect();
				$GLOBALS['exitCode'] = 11;
				$state = "END";break;
			} else {
				while ($stepsRecordset = $db->fetch_object()) {
					$GLOBALS['steps'][$stepsRecordset->cycle_step_id] = $func->object2array($stepsRecordset);
					array_push($GLOBALS['steps'][$stepsRecordset->cycle_step_id], "KO");
				}
			}
			$db->disconnect();
			$state = "GET_DOCSERVERS";
			break;
		/**********************************************************************************************/
		case "GET_DOCSERVERS" :
			$db->connect();
			$db2->connect();
			$query = "select * from "._LC_CYCLE_STEPS_TABLE_NAME." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."'";
			do_query($db, $query);
			if ($db->nb_result() == 0) {
				$GLOBALS['logger']->write('Cycle Steps not found', 'ERROR', 11);
				$db->disconnect();
				$GLOBALS['exitCode'] = 11;
				$state = "END";break;
			} else {
				while ($stepsRecordset = $db->fetch_object()) {
					$query = "select * from "._DOCSERVER_TYPES_TABLE_NAME." where docserver_type_id = '".$stepsRecordset->docserver_type_id."'";
					do_query($db2, $query);
					if ($db2->nb_result() == 0) {
						$GLOBALS['logger']->write('Docserver type not found', 'ERROR', 12);
						$db->disconnect();
						$db2->disconnect();
						$GLOBALS['exitCode'] = 12;
						$state = "END";break;
					} else {
						$docserverTypesRecordset = $db2->fetch_object();
						$GLOBALS['docservers'][$stepsRecordset->cycle_step_id] = $func->object2array($docserverTypesRecordset);
					}
					$query = "select * from "._DOCSERVERS_TABLE_NAME." where docserver_type_id = '".$stepsRecordset->docserver_type_id."' order by priority_number";
					do_query($db2, $query);
					if ($db2->nb_result() == 0) {
						$GLOBALS['logger']->write('Docserver not found', 'ERROR', 13);
						$db->disconnect();
						$db2->disconnect();
						$GLOBALS['exitCode'] = 13;
						$state = "END";break;
					} else {
						$docserversRecordset = $db2->fetch_object();
						$GLOBALS['docservers'][$stepsRecordset->cycle_step_id]['docserver'] = $func->object2array($docserversRecordset);
					}
				}
			}
			$db->disconnect();
			$state = "A_STEP";
			break;
		/**********************************************************************************************/
		case "A_STEP" :
			$state = "EMPTY_STACK";
			foreach ($GLOBALS['steps'] as $key=>$value) {
				if ($GLOBALS['steps'][$key][0] == "KO") {
					$currentStep = $GLOBALS['steps'][$key]['cycle_step_id'];
					$GLOBALS['logger']->write("current step:".$currentStep, 'INFO');
					$GLOBALS['logger']->write("current operation:".$GLOBALS['steps'][$key]['step_operation'], 'INFO');
					$cptRecordsInStep = 0;
					$resInContainer = 0;
					$theLastRecordInStep = false;
					$query = "select * from "._LC_STACK_TABLE_NAME." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."' and cycle_step_id = '".$currentStep."' and status = 'I' and coll_id = '".$GLOBALS['collection']."'";
					do_query($db, $query);
					$cptRecordsTotalInStep = $db->nb_result();
					$GLOBALS['logger']->write("Total res in the step:".$cptRecordsTotalInStep, 'INFO');
					$db->disconnect();
					$state = "A_RECORD";break;
				}
			}
			break;
		/**********************************************************************************************/
		case "A_RECORD" :
			$cptRecordsInStep++;
			$db->connect();
			$query = "select * from "._LC_STACK_TABLE_NAME." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."' and cycle_step_id = '".$currentStep."' and status = 'I' and coll_id = '".$GLOBALS['collection']."'";
			do_query($db, $query);
			if ($db->nb_result() == 0) {
				foreach ($GLOBALS['steps'] as $key=>$value) {
					if ($key == $currentStep) {
						$GLOBALS['steps'][$key][0] = "OK";break;
					}
				}
				$db->disconnect();
				$state = "A_STEP";break;
			} else {
				if ($cptRecordsInStep == ($cptRecordsTotalInStep)) {
					$GLOBALS['logger']->write("The last record of the step", 'INFO');
					$theLastRecordInStep = true;
				}
				$stackRecordset = $db->fetch_object();
				$currentRecordInStack = array();
				$currentRecordInStack = $func->object2array($stackRecordset);
				$db->disconnect();
				$GLOBALS['logger']->write("current record:".$currentRecordInStack['res_id'], 'INFO');
				// if NEW operation we have to add new states
				if ($GLOBALS['steps'][$currentStep]['step_operation'] == "COPY" || $GLOBALS['steps'][$currentStep]['step_operation'] == "MOVE") {
					$state = "COPY_OR_MOVE";
				} else {
					$state = "END";
				}
			}
			break;
		/**********************************************************************************************/
		case "COPY_OR_MOVE" :
			if ($GLOBALS['docservers'][$currentStep]['is_container'] == "t") {
				$state = "CONTAINER";
			} else {
				$state = "SINGLE";
			}
			break;
		/**********************************************************************************************/
		case "SINGLE" :
			$state = "DO_COPY_OR_MOVE";break;
		/**********************************************************************************************/
		case "CONTAINER" :
			if (!$isAContainerOpened) {
				$state = "OPEN_CONTAINER";
			} else {
				$state = "ADD_RECORD";
			}
			break;
		/**********************************************************************************************/
		case "OPEN_CONTAINER" :
			$isAContainerOpened = true;
			$cptResInContainer = 0;
			$resInContainer = array();
			$state = "ADD_RECORD";break;
		/**********************************************************************************************/
		case "ADD_RECORD" :
			$cptResInContainer++;
			array_push($resInContainer, $currentRecordInStack['res_id']);
			$query = "update "._LC_STACK_TABLE_NAME." set status = 'A' where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."' and cycle_step_id = '".$currentStep."' and coll_id = '".$GLOBALS['collection']."' and res_id = ".$currentRecordInStack['res_id'];
			do_query($db, $query);
			if ($cptResInContainer >= $GLOBALS['docservers'][$currentStep]['container_max_number'] || $theLastRecordInStep) {
				$state = "CLOSE_CONTAINER";
			} else {
				$state = "A_RECORD";
			}
			break;
		/**********************************************************************************************/
		case "CLOSE_CONTAINER" :
			$isAContainerOpened = false;
			$cptResInContainer = 0;
			$state = "DO_COPY_OR_MOVE";break;
		/**********************************************************************************************/
		case "DO_COPY_OR_MOVE" :
			$state = "UPDATE_DATABASE";break;
		/**********************************************************************************************/
		case "UPDATE_DATABASE" :
			$db->connect();
			if (is_array($resInContainer) && count($resInContainer) > 0) {
				for ($cptRes = 0;$cptRes<count($resInContainer);$cptRes++) {
					$query = "update "._LC_STACK_TABLE_NAME." set status = 'P' where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."' and cycle_step_id = '".$currentStep."' and coll_id = '".$GLOBALS['collection']."' and res_id = ".$resInContainer[$cptRes];
					do_query($db, $query);
				}
			} else {
				$query = "update "._LC_STACK_TABLE_NAME." set status = 'P' where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."' and cycle_step_id = '".$currentStep."' and coll_id = '".$GLOBALS['collection']."' and res_id = ".$currentRecordInStack['res_id'];
				do_query($db, $query);
			}
			$db->disconnect();
			$state = "A_RECORD";break;
		/**********************************************************************************************/
		case "EMPTY_STACK" :
			$db->connect();
			$query = "truncate table "._LC_STACK_TABLE_NAME;
			do_query($db, $query);
			$db->disconnect();
			$state = "END";break;
	}
}
$GLOBALS['logger']->write("End of process", 'INFO');
exit($GLOBALS['exitCode']);
?>
