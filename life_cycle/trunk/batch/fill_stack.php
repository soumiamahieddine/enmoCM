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
 * @brief Batch to fill the stack
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
 *  7  : Stack full
 *  8  : Cycle not found
 *  9  : Previous cycle not found
 *  10 : No resource found
 *  11 : Cycle step not found
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
$policy = '';
$cycle = '';
$steps = Array();
$databasetype = '';
$exitCode = 0;
$running_date = date('Y-m-d H:i:s');

function do_query($db_conn, $query_txt) {
	$db_conn->connect();
	$res = $db_conn->query($query_txt, true);
	if (!$res) {
		$GLOBALS['logger']->write('SQL Query error : ' . $query_txt, 'ERROR', 4);
		exit(4);
	}
	$GLOBALS['logger']->write('SQL query: ' . $query_txt, 'DEBUG');
	return true;
}

$state = "INIT";
while($state <> "END") {
	if(isset($GLOBALS['logger'])) {
		$GLOBALS['logger']->write("STATE:".$state, 'INFO');
	}
	switch($state) {
		/**********************************************************************************************/
		case "INIT" :
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
					$GLOBALS['exitCode'] = 0;
					exit(0);
				}
			} catch (MissingArgumentError $e) {
				if ($e->arg_name == "config") {
					$GLOBALS['logger']->write('Configuration file missing', 'ERROR', 1);
					$GLOBALS['exitCode'] = 1;
					$state = "END";break;
				}
				if ($e->arg_name == "table") {
					$GLOBALS['logger']->write('Table missing', 'ERROR', 1);
					$GLOBALS['exitCode'] = 1;
					$state = "END";break;
				}
				if ($e->arg_name == "collection") {
					$GLOBALS['logger']->write('Collection missing', 'ERROR', 1);
					$GLOBALS['exitCode'] = 1;
					$state = "END";break;
				}
				if ($e->arg_name == "policy") {
					$GLOBALS['logger']->write('Policy missing', 'ERROR', 1);
					$GLOBALS['exitCode'] = 1;
					$state = "END";break;
				}
				if ($e->arg_name == "cycle") {
					$GLOBALS['logger']->write('Cycle missing', 'ERROR', 1);
					$GLOBALS['exitCode'] = 1;
					$state = "END";break;
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
			$GLOBALS['collection'] = $options['collection'];
			$GLOBALS['policy'] = $options['policy'];
			$GLOBALS['cycle'] = $options['cycle'];
			$GLOBALS['logger']->write($txt, 'INFO');
			// Tests existence of config file
			if (!file_exists($GLOBALS['configFile'])) {
				$GLOBALS['logger']->write('Configuration file ' . $GLOBALS['configFile'] . ' does not exist', 'ERROR', 3);
				$GLOBALS['exitCode'] = 3;
				$state = "END";break;
			}
			// Loading config file
			$GLOBALS['logger']->write('Load xml config file : ' . $GLOBALS['configFile'], 'INFO');
			$xmlconfig = simplexml_load_file($GLOBALS['configFile']);
			if ($xmlconfig == FALSE) {
				$GLOBALS['logger']->write('Error on loading config file : ' . $GLOBALS['configFile'], 'ERROR', 5);
				$GLOBALS['exitCode'] = 5;
				$state = "END";break;
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
				MyInclude($MaarchDirectory . "modules/life_cycle/life_cycle_tables_definition.php");
				MyInclude($MaarchDirectory . "core/core_tables.php");
			} catch (IncludeFileError $e) {
				$GLOBALS['logger']->write('Problem with the php include path : ' . get_include_path(), 'ERROR', 15);
				$GLOBALS['exitCode'] = 15;
				$state = "END";break;
			}
			core_tools::load_lang($lang, $MaarchDirectory, $MaarchApps);
			$db = new dbquery($GLOBALS['configFile']);
			$db2 = new dbquery($GLOBALS['configFile']);
			$state = "CONTROL_STACK";
			break;
		/**********************************************************************************************/
		case "CONTROL_STACK" :
			$db->connect();
			$query = "select * from "._LC_STACK_TABLE_NAME;
			//echo $query."\r\n";exit;
			do_query($db, $query);
			if ($db->nb_result() > 0) {
				$GLOBALS['logger']->write('WARNING stack is full', 'ERROR', 7);
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
			//echo $query."\r\n";exit;
			do_query($db, $query);
			if ($db->nb_result() == 0) {
				$GLOBALS['logger']->write('Cycle Steps not found', 'ERROR', 11);
				$db->disconnect();
				$GLOBALS['exitCode'] = 11;
				$state = "END";break;
			} else {
				while($stepsRecordset = $db->fetch_object()) {
					array_push($GLOBALS['steps'], array("cycle_step_id" => $stepsRecordset->cycle_step_id));
				}
			}
			$db->disconnect();
			$state = "SELECT_RES";
			break;
		/**********************************************************************************************/
		case "SELECT_RES" :
			$db->connect();
			// get the where clause of the cycle
			$query = "select * from "._LC_CYCLES_TABLE_NAME." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."'";
			do_query($db, $query);
			if ($db->nb_result() > 0) {
				$cycleRecordset = $db->fetch_object();
				//echo "available cycle : ".$cycleRecordset->cycle_id."\r\n";
			} else {
				$GLOBALS['logger']->write('cycle not found for policy:'.$GLOBALS['policy'].', cycle:'.$GLOBALS['cycle'], 'ERROR', 8);
				$db->disconnect();
				$GLOBALS['exitCode'] = 8;
				$state = "END";break;
			}
			// compute the previous step
			$query = "select * from "._LC_CYCLES_TABLE_NAME." where policy_id = '".$GLOBALS['policy']."' and sequence_number = ".($cycleRecordset->sequence_number - 1);
			//echo $query."\r\n";
			do_query($db, $query);
			if ($db->nb_result() > 0) {
				$cyclePreviousRecordset = $db->fetch_object();
			} else {
				$GLOBALS['logger']->write('previous cycle not found for policy:'.$GLOBALS['policy'].', cycle:'.$GLOBALS['cycle'], 'ERROR', 9);
				$db->disconnect();
				$GLOBALS['exitCode'] = 9;
				$state = "END";break;
			}
			// select resources
			if ($cycleRecordset->break_key <> "") {
				$ordeBy = " order by ".$cycleRecordset->break_key;
			}
			//$query = "select res_id from ".$GLOBALS['table']." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$cyclePreviousRecordset->cycle_id."' and ".$cycleRecordset->where_clause.$ordeBy;
			$query = "select res_id from ".$GLOBALS['table']." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$cyclePreviousRecordset->cycle_id."' and ".$cycleRecordset->where_clause.$ordeBy." LIMIT 100";
			//echo $query."\r\n";
			do_query($db, $query);
			$resourcesArray = array();
			if ($db->nb_result() > 0) {
				while($resoucesRecordset = $db->fetch_object()) {
					array_push($resourcesArray, array("res_id" => $resoucesRecordset->res_id));
				}
			} else {
				$GLOBALS['logger']->write('no resource found for policy:'.$GLOBALS['policy'].', cycle:'.$GLOBALS['cycle'], 'ERROR', 10);
				$db->disconnect();
				$GLOBALS['exitCode'] = 10;
				$state = "END";break;
			}
			$db->disconnect();
			$state = "FILL_STACK";
			break;
		/**********************************************************************************************/
		case "FILL_STACK" :
			$db->connect();
			for($cptSteps=0;$cptSteps<count($GLOBALS['steps']);$cptSteps++) {
				for($cptRes=0;$cptRes<count($resourcesArray);$cptRes++) {
					$query = "insert into "._LC_STACK_TABLE_NAME."  (policy_id, cycle_id, cycle_step_id, coll_id, res_id, status) values('".$GLOBALS['policy']."', '".$GLOBALS['cycle']."', '".$GLOBALS['steps'][$cptSteps]['cycle_step_id']."', '".$GLOBALS['collection']."', ".$resourcesArray[$cptRes]["res_id"].", 'I')";
					do_query($db, $query);
				}
			}
			$db->disconnect();
			$state = "END";
			break;
	}
}
$GLOBALS['logger']->write("End of process", 'INFO');
exit($GLOBALS['exitCode']);
?>
