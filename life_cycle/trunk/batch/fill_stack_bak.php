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
 *  4 : SQL Query Error
 *  5 : SQL insert Error
 *  6 : Problem with php include path
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

//error_reporting(0);
try {
	include("Maarch_CLITools/ArgsParser.php");
	include("Maarch_CLITools/Logger.php");
	include("Maarch_CLITools/FileHandler.php");
	include("Maarch_CLITools/ConsoleHandler.php");
} catch (IncludeFileError $e) {
	echo "Maarch_CLITools required ! \n (pear.maarch.org)\n";
	exit();
}

// Globals variables definition
$config_file = '';
$table = '';
$policy = '';
$cycle = '';
$step = '';
$databasetype = '';
$running_date = date('Y-m-d H:i:s');

function do_query($db_conn, $query_txt) {
	$db_conn->connect();
	$res = $db_conn->query($query_txt, true);
	if (!$res) {
		$GLOBALS['logger']->write('SQL Query error : ' . $query_txt, 'ERROR', 13);
		exit(4);
	}
	$GLOBALS['logger']->write('SQL query: ' . $query_txt, 'DEBUG');
	return true;
}

function main($state, $resourcesArray = array()) {
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
			$argsparser->add_arg("step", array (
				'short' => "s",
				'long' => "step",
				'mandatory' => true,
				'help' => "Step is mandatory."
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
				if ($e->arg_name == "step") {
					$GLOBALS['logger']->write('Step missing', 'ERROR', 1);
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
			$GLOBALS['config_file'] = $options['config'];
			$GLOBALS['table'] = $options['table'];
			$GLOBALS['collection'] = $options['collection'];
			$GLOBALS['policy'] = $options['policy'];
			$GLOBALS['cycle'] = $options['cycle'];
			$GLOBALS['step'] = $options['step'];
			$GLOBALS['logger']->write($txt, 'INFO');
			// Tests existence of config file
			if (!file_exists($GLOBALS['config_file'])) {
				$GLOBALS['logger']->write('Configuration file ' . $GLOBALS['config_file'] . ' does not exist', 'ERROR', 3);
				exit(3);
			}
			// Loading config file
			$GLOBALS['logger']->write('Load xml config file : ' . $GLOBALS['config_file'], 'INFO');
			$xmlconfig = simplexml_load_file($GLOBALS['config_file']);
			if ($xmlconfig == FALSE) {
				$GLOBALS['logger']->write('Error on loading config file : ' . $GLOBALS['config_file'], 'ERROR', 5);
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
				MyInclude($MaarchDirectory . "modules/life_cycle/life_cycle_tables_definition.php");
				MyInclude($MaarchDirectory . "core/core_tables.php");
			} catch (IncludeFileError $e) {
				$GLOBALS['logger']->write('Problem with the php include path : ' . get_include_path(), 'ERROR', 15);
				exit(15);
			}
			core_tools::load_lang($lang, $MaarchDirectory, $MaarchApps);
			main("PARAM_OK");
		/**********************************************************************************************/
		case "PARAM_OK" :
			$db = new dbquery($GLOBALS['config_file']);
			$db->connect();
			$db2 = new dbquery($GLOBALS['config_file']);
			$db2->connect();
			$query = "select * from "._LC_CYCLE_STEPS_TABLE_NAME." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."' and cycle_step_id = '".$GLOBALS['step']."'";
			do_query($db, $query);
			$cycleStepsArray = array();
			// verif if cycle step exists
			if ($db->nb_result() > 0) {
				$cycleStepsRecordset = $db->fetch_object();
				//echo "available cycle steps : ".$cycleStepsRecordset->cycle_step_id."\r\n";
				array_push($cycleStepsArray, array("policy_id" => $cycleStepsRecordset->policy_id, "cycle_id" => $cycleStepsRecordset->cycle_id, "cycle_step_id" => $cycleStepsRecordset->cycle_step_id, "sequence_number" => $cycleStepsRecordset->sequence_number));
				// get the where clause of the cycle
				$query = "select * from "._LC_CYCLES_TABLE_NAME." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."'";
				do_query($db2, $query);
				if ($db2->nb_result() > 0) {
					$cycleRecordset = $db2->fetch_object();
					//echo "available cycle : ".$cycleRecordset->cycle_id."\r\n";
				} else {
					$GLOBALS['logger']->write('cycle not found for policy:'.$GLOBALS['policy'].', cycle:'.$GLOBALS['cycle'], 'INFO');
					$db->disconnect();
					$db2->disconnect();
					exit(3);
				}
				// compute the previous step
				$query = "select * from "._LC_CYCLES_TABLE_NAME." where policy_id = '".$GLOBALS['policy']."' and sequence_number = ".($cycleRecordset->sequence_number - 1);
				//echo $query."\r\n";
				do_query($db2, $query);
				if ($db2->nb_result() > 0) {
					$cyclePreviousRecordset = $db2->fetch_object();
				} else {
					$GLOBALS['logger']->write('previous cycle not found for policy:'.$GLOBALS['policy'].', cycle:'.$GLOBALS['cycle'], 'INFO');
					$db->disconnect();
					$db2->disconnect();
					exit(3);
				}
				// select resources
				if($cycleRecordset->break_key <> "") {
					$ordeBy = " order by ".$cycleRecordset->break_key;
				}
				$query = "select res_id from ".$GLOBALS['table']." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$cyclePreviousRecordset->cycle_id."' and ".$cycleRecordset->where_clause.$ordeBy;
				//echo $query."\r\n";
				do_query($db2, $query);
				$resourcesArray = array();
				if ($db2->nb_result() > 0) {
					while($resoucesRecordset = $db2->fetch_object()) {
						array_push($resourcesArray, array("res_id" => $resoucesRecordset->res_id));
					}
				} else {
					$GLOBALS['logger']->write('no resource found for policy:'.$GLOBALS['policy'].', cycle:'.$GLOBALS['cycle'], 'INFO');
					$db->disconnect();
					$db2->disconnect();
					exit(3);
				}
			} else {
				$GLOBALS['logger']->write('cycle step not found for policy:'.$GLOBALS['policy'].', cycle:'.$GLOBALS['cycle'].', step:'.$GLOBALS['step'], 'INFO');
				$db->disconnect();
				$db2->disconnect();
				exit(3);
			}
			$db->disconnect();
			$db2->disconnect();
			main("RESOURCES_SELECTED", $resourcesArray);
		/**********************************************************************************************/
		case "RESOURCES_SELECTED" :
			$db = new dbquery($GLOBALS['config_file']);
			$db->connect();
			for($cptRes=0;$cptRes<count($resourcesArray);$cptRes++) {
				$query = "select * from "._LC_STACK_TABLE_NAME." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."' and cycle_step_id = '".$GLOBALS['step']."' and coll_id = '".$GLOBALS['collection']."' and res_id = ".$resourcesArray[$cptRes]["res_id"];
				//echo $query."\r\n";exit;
				do_query($db, $query);
				if ($db->nb_result() > 0) {
					$GLOBALS['logger']->write('WARNING resource in stack found for policy:'.$GLOBALS['policy'].', cycle:'.$GLOBALS['cycle'].', step:'.$GLOBALS['step'].', collection:'.$GLOBALS['collection'].', table:'.$GLOBALS['table'].', res_id:'.$resourcesArray[$cptRes]["res_id"], 'INFO');
					$db->disconnect();
					exit(3);
				}
			}
			$db->disconnect();
			main("STACK_CONTROLED", $resourcesArray);
		/**********************************************************************************************/
		case "STACK_CONTROLED" :
			$db = new dbquery($GLOBALS['config_file']);
			$db->connect();
			for($cptRes=0;$cptRes<count($resourcesArray);$cptRes++) {
				$query = "insert into "._LC_STACK_TABLE_NAME."  (policy_id, cycle_id, cycle_step_id, coll_id, res_id, status) values('".$GLOBALS['policy']."', '".$GLOBALS['cycle']."', '".$GLOBALS['step']."', '".$GLOBALS['collection']."', ".$resourcesArray[$cptRes]["res_id"].", 'I')";
				do_query($db, $query);
			}
			$db->disconnect();
			main("STACK_FILED");
		/**********************************************************************************************/
		case "STACK_FILED" :
			$GLOBALS['logger']->write("End of process", 'INFO');
			exit();
	}
}

main("INIT");

?>
