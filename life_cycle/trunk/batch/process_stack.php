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
 *  16 : Collection unknow
 *  17 : Tmp dir not exists
 *  18 : Batch already exists
 *  19 : Tmp dir not empty
 *  20 : There are still documents to be processed
 *  21 : Problem to create directory on the docserver
 *  22 : Problem during transfert of file (fingerprint control)
 *  23 : Problem with compression
 *  24 : Problem with extract
 *  25 : Pb with fingerprint of the source
 */

include("load_process_stack.inc");
include("resources.inc");
include("docservers.inc");
include("oais.inc");
include("custom.inc");

/******************************************************************************************************/
/* beginning */
$GLOBALS['docserverControler']->washTmp($GLOBALS['TmpDirectory'], true);
$GLOBALS['state'] = "CONTROL_STACK";
while ($GLOBALS['state'] <> "END") {
	if (isset($GLOBALS['logger'])) {
		$GLOBALS['logger']->write("STATE:".$GLOBALS['state'], 'INFO');
	}
	switch($GLOBALS['state']) {
		/**********************************************************************************************/
		case "CONTROL_STACK" :
			$query = "select * from "._LC_STACK_TABLE_NAME;
			do_query($GLOBALS['db'], $query);
			if ($GLOBALS['db']->nb_result() == 0) {
				$GLOBALS['logger']->write('WARNING stack is empty', 'ERROR', 7);
				$GLOBALS['exitCode'] = 7;
				$GLOBALS['state'] = "END";break;
			}
			updateWorkBatch();
			$GLOBALS['logger']->write("Batch number:".$GLOBALS['wb'], 'INFO');
			$query = "update " . _LC_STACK_TABLE_NAME . " set status = 'I' where status = 'W'";
			do_query($GLOBALS['db'], $query);
			$GLOBALS['state'] = "GET_STEPS";
			break;
		/**********************************************************************************************/
		case "GET_STEPS" :
			$query = "select * from "._LC_CYCLE_STEPS_TABLE_NAME." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."'";
			do_query($GLOBALS['db'], $query);
			if ($GLOBALS['db']->nb_result() == 0) {
				$GLOBALS['logger']->write('Cycle Steps not found', 'ERROR', 11);
				$GLOBALS['exitCode'] = 11;
				$GLOBALS['state'] = "END";break;
			} else {
				while ($stepsRecordset = $GLOBALS['db']->fetch_object()) {
					$GLOBALS['steps'][$stepsRecordset->cycle_step_id] = $GLOBALS['func']->object2array($stepsRecordset);
					array_push($GLOBALS['steps'][$stepsRecordset->cycle_step_id], "KO");
				}
			}
			$GLOBALS['state'] = "GET_DOCSERVERS";
			break;
		/**********************************************************************************************/
		case "GET_DOCSERVERS" :
			$query = "select * from "._LC_CYCLE_STEPS_TABLE_NAME." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."'";
			do_query($GLOBALS['db'], $query);
			$GLOBALS['state'] = "A_STEP";
			if ($GLOBALS['db']->nb_result() == 0) {
				$GLOBALS['logger']->write('Cycle Steps not found', 'ERROR', 11);
				$GLOBALS['exitCode'] = 11;
				$GLOBALS['state'] = "END";break;
			} else {
				while ($stepsRecordset = $GLOBALS['db']->fetch_object()) {
					$query = "select * from "._DOCSERVER_TYPES_TABLE_NAME." where docserver_type_id = '".$stepsRecordset->docserver_type_id."'";
					do_query($GLOBALS['db2'], $query);
					if ($GLOBALS['db2']->nb_result() == 0) {
						$GLOBALS['logger']->write('Docserver type not found', 'ERROR', 12);
						$GLOBALS['exitCode'] = 12;
						$GLOBALS['state'] = "END";break;
					} else {
						$docserverTypesRecordset = $GLOBALS['db2']->fetch_object();
						$GLOBALS['docservers'][$stepsRecordset->cycle_step_id] = $GLOBALS['func']->object2array($docserverTypesRecordset);
					}
					$query = "select * from "._DOCSERVERS_TABLE_NAME." where docserver_type_id = '".$stepsRecordset->docserver_type_id."' order by priority_number";
					do_query($GLOBALS['db2'], $query);
					if ($GLOBALS['db2']->nb_result() == 0) {
						$GLOBALS['logger']->write('Docserver not found', 'ERROR', 13);
						$GLOBALS['exitCode'] = 13;
						$GLOBALS['state'] = "END";break;
					} else {
						$docserversRecordset = $GLOBALS['db2']->fetch_object();
						$GLOBALS['docservers'][$stepsRecordset->cycle_step_id]['docserver'] = $GLOBALS['func']->object2array($docserversRecordset);
					}
				}
			}
			break;
		/**********************************************************************************************/
		case "A_STEP" :
			$GLOBALS['state'] = "EMPTY_STACK";
			foreach ($GLOBALS['steps'] as $key=>$value) {
				if ($GLOBALS['steps'][$key][0] == "KO") {
					$GLOBALS['currentStep'] = $GLOBALS['steps'][$key]['cycle_step_id'];
					$GLOBALS['logger']->write("current step:".$GLOBALS['currentStep'], 'INFO');
					$GLOBALS['logger']->write("current operation:".$GLOBALS['steps'][$key]['step_operation'], 'INFO');
					$cptRecordsInStep = 0;
					$resInContainer = 0;
					$totalSizeToAdd = 0;
					$theLastRecordInStep = false;
					$query = "select * from "._LC_STACK_TABLE_NAME." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."' and cycle_step_id = '".$GLOBALS['currentStep']."' and status = 'I' and coll_id = '".$GLOBALS['collection']."'";
					do_query($GLOBALS['db'], $query);
					$cptRecordsTotalInStep = $GLOBALS['db']->nb_result();
					$GLOBALS['logger']->write("total res in the step:".$cptRecordsTotalInStep, 'INFO');
					if ($cptRecordsTotalInStep <> 0) {
						$resultPath = array();
						$resultPath = $GLOBALS['docserverControler']->createPathOnDocServer($GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template']);
						if($resultPath['error'] <> "") {
							$GLOBALS['logger']->write($resultPath['error'], 'ERROR', 18);
							exit(18);
						}
						$pathOnDocserver = $resultPath['destinationDir'];
						$GLOBALS['logger']->write("target path on docserver:".$pathOnDocserver, 'INFO');
					}
					$GLOBALS['state'] = "A_RECORD";break;
				}
			}
			break;
		/**********************************************************************************************/
		case "A_RECORD" :
			$cptRecordsInStep++;
			$query = "select * from "._LC_STACK_TABLE_NAME." where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."' and cycle_step_id = '".$GLOBALS['currentStep']."' and status = 'I' and coll_id = '".$GLOBALS['collection']."'";
			do_query($GLOBALS['db'], $query);
			if ($GLOBALS['db']->nb_result() == 0) {
				foreach ($GLOBALS['steps'] as $key=>$value) {
					if ($key == $GLOBALS['currentStep']) {
						if ($totalSizeToAdd <> 0) {
							$totalSizeToAdd = $totalSizeToAdd + $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['actual_size_number'];
							$query  = "update "._DOCSERVERS_TABLE_NAME." set actual_size_number=".$totalSizeToAdd." where docserver_id='".$GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['docserver_id']."'";
							do_query($GLOBALS['db'], $query);
						}
						$GLOBALS['steps'][$key][0] = "OK";break;
					}
				}
				$GLOBALS['state'] = "A_STEP";break;
			} else {
				if ($cptRecordsInStep == $cptRecordsTotalInStep) {
					$GLOBALS['logger']->write("The last record of the step", 'INFO');
					$theLastRecordInStep = true;
				}
				$stackRecordset = $GLOBALS['db']->fetch_object();
				$currentRecordInStack = array();
				$currentRecordInStack = $GLOBALS['func']->object2array($stackRecordset);
				controlIntegrityOfSource($currentRecordInStack['res_id']);
				$sourceFilePath = getSourceResourcePath($currentRecordInStack['res_id']);
				if (!file_exists($sourceFilePath)) {
					$GLOBALS['logger']->write('Resource not found:' . $sourceFilePath, 'ERROR', 16);
					$GLOBALS['exitCode'] = 16;
					$GLOBALS['state'] = "END";break;
				}
				$GLOBALS['logger']->write("current record:".$currentRecordInStack['res_id'], 'INFO');
				// if NEW operation we have to add new states
				if ($GLOBALS['steps'][$GLOBALS['currentStep']]['step_operation'] == "COPY" || $GLOBALS['steps'][$GLOBALS['currentStep']]['step_operation'] == "MOVE") {
					$GLOBALS['state'] = "COPY_OR_MOVE";
				} else {
					$GLOBALS['state'] = "END";
				}
			}
			break;
		/**********************************************************************************************/
		case "COPY_OR_MOVE" :
			if ($GLOBALS['docservers'][$GLOBALS['currentStep']]['is_container'] == "t") {
				$GLOBALS['state'] = "CONTAINER";
			} else {
				$GLOBALS['state'] = "DO_COPY_OR_MOVE";
			}
			break;
		/**********************************************************************************************/
		case "CONTAINER" :
			if (!$isAContainerOpened) {
				$GLOBALS['state'] = "OPEN_CONTAINER";
			} else {
				$GLOBALS['state'] = "ADD_RECORD";
			}
			break;
		/**********************************************************************************************/
		case "OPEN_CONTAINER" :
			$isAContainerOpened = true;
			$cptResInContainer = 0;
			$resInContainer = array();
			$GLOBALS['state'] = "ADD_RECORD";break;
		/**********************************************************************************************/
		case "ADD_RECORD" :
			$cptResInContainer++;
			array_push($resInContainer, array("res_id" => $currentRecordInStack['res_id'], "source_path" => $sourceFilePath));
			$offsetDoc = "";
			$query = "update " . _LC_STACK_TABLE_NAME . " set status = 'W' where policy_id = '" . $GLOBALS['policy'] . "' and cycle_id = '" . $GLOBALS['cycle'] . "' and cycle_step_id = '" . $GLOBALS['currentStep'] . "' and coll_id = '" . $GLOBALS['collection'] . "' and res_id = " . $currentRecordInStack['res_id'];
			do_query($GLOBALS['db'], $query);
			if ($cptResInContainer >= $GLOBALS['docservers'][$GLOBALS['currentStep']]['container_max_number'] || $theLastRecordInStep) {
				$GLOBALS['state'] = "CLOSE_CONTAINER";
			} else {
				$GLOBALS['state'] = "A_RECORD";
			}
			break;
		/**********************************************************************************************/
		case "CLOSE_CONTAINER" :
			$resultAip = array();
			$resultAip = createAip($resInContainer);
			$sourceFilePath = $resultAip['newSourceFilePath'];
			$resInContainer = $resultAip['resInContainer'];
			$isAContainerOpened = false;
			$cptResInContainer = 0;
			$GLOBALS['state'] = "DO_COPY_OR_MOVE";break;
		/**********************************************************************************************/
		case "DO_COPY_OR_MOVE" :
			$infoFileNameInTargetDocserver = array();
			$infoFileNameInTargetDocserver = $GLOBALS['docserverControler']->getNextFileNameInDocserver($pathOnDocserver);
			if($infoFileNameInTargetDocserver['error'] <> "") {
				$GLOBALS['logger']->write($infoFileNameInTargetDocserver['error'], 'ERROR', 21);
				exit(21);
			}
			$copyResultArray = array();
			$infoFileNameInTargetDocserver['fileDestinationName'] .= "." . strtolower($GLOBALS['func']->extractFileExt($sourceFilePath));
			$copyResultArray = $GLOBALS['docserverControler']->copyOnDocserver($sourceFilePath, $infoFileNameInTargetDocserver);
			if($copyResultArray['error'] <> "") {
				$GLOBALS['logger']->write('error to copy file on docserver:' . $copyResultArray['error'] . " " . $sourceFilePath . " " . $infoFileNameInTargetDocserver['destinationDir'] . $infoFileNameInTargetDocserver['fileDestinationName'], 'ERROR', 17);
				$GLOBALS['exitCode'] = 17;
				$GLOBALS['state'] = "END";break;
			}
			$destinationDir = $copyResultArray['destinationDir'];
			$fileDestinationName = $copyResultArray['fileDestinationName'];
			$totalSizeToAdd = $totalSizeToAdd + $copyResultArray['fileSize'];
			$GLOBALS['state'] = "UPDATE_DATABASE";break;
		/**********************************************************************************************/
		case "UPDATE_DATABASE" :
			controlIntegrityOfTransfert($currentRecordInStack, $resInContainer, $destinationDir, $fileDestinationName, $fileOffsetDoc);
			updateDatabase($currentRecordInStack, $resInContainer, $destinationDir, $fileDestinationName, $fileOffsetDoc);
			$GLOBALS['state'] = "A_RECORD";break;
		/**********************************************************************************************/
		case "EMPTY_STACK" :
			$query = "select * from " . _LC_STACK_TABLE_NAME . " where status <> 'P'";
			do_query($GLOBALS['db'], $query);
			if ($GLOBALS['db']->nb_result() == 0) {
				$query = "truncate table " . _LC_STACK_TABLE_NAME;
				do_query($GLOBALS['db'], $query);
			} else {
				$GLOBALS['logger']->write('there are still documents to be processed', 'ERROR', 20);
				$GLOBALS['exitCode'] = 20;
				$query = "delete from " . _LC_STACK_TABLE_NAME . " where status = 'P'";
				do_query($GLOBALS['db'], $query);
			}
			$GLOBALS['state'] = "END";break;
	}
}
$GLOBALS['logger']->write("End of process", 'INFO');
$GLOBALS['db']->disconnect();
$GLOBALS['db2']->disconnect();
$GLOBALS['db3']->disconnect();
exit($GLOBALS['exitCode']);

?>
