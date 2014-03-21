<?php

/*
 *  Copyright 2008-2011 Maarch
 *
 *  This file is part of Maarch Framework.
 *
 *  Maarch Framework is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Maarch Framework is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
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
 * *****   LIGHT PROBLEMS without an error semaphore
 *  101 : Configuration file missing
 *  102 : Configuration file does not exist
 *  103 : Error on loading config file
 *  104 : SQL Query Error
 *  105 : a parameter is missing
 *  106 : Maarch_CLITools is missing
 *  107 : Stack empty for the request
 *  108 : There are still documents to be processed
 *  109 : An instance of the batch for the required policy and cyle is already
 *        in progress
 *  110 : Problem with collection parameter
 *  111 : Problem with the php include path
 *  112 : Problem with the setup of esign
 * ****   HEAVY PROBLEMS with an error semaphore
 *  11   : Problem with policy administration : Cycle step not found
 *  12  : Docserver type not found
 *  13  : Docserver not found
 *  14  : ...
 *  15  : Error to copy file on docserver
 *  16  : ...
 *  17  : Tmp dir not exists
 *  18  : Problem to create path on docserver, maybe batch number 
 *        already exists
 *  19  : Tmp dir not empty
 *  20  : ...
 *  21  : Problem to create directory on the docserver
 *  22  : Problem during transfert of file (fingerprint control)
 *  23  : Problem with compression
 *  24  : Problem with extract
 *  25  : Pb with fingerprint of the source
 *  26  : File deletion impossible
 *  27  : Resource not found
 *  28  : The docserver will be full at 95 percent
 *  29  : Error persists
 *  30  : Esign problem
 */

date_default_timezone_set('Europe/Paris');
try {
    include('load_process_stack.php');
    include('resources.php');
    include('docservers.php');
    include('oais.php');
    if ($GLOBALS['customPath'] <> "") {
        include($GLOBALS['customPath']);
    } else {
        include('custom.php');
    }
    if ($GLOBALS['customLang'] <> "" && file_exists($GLOBALS['customLang'])) {
        include($GLOBALS['customLang']);
    }
} catch (IncludeFileError $e) {
    echo "Maarch_CLITools required ! \n (pear.maarch.org)\n";
    exit(106);
}

/******************************************************************************/
/* beginning */
$GLOBALS['state'] = "CONTROL_STACK";
while ($GLOBALS['state'] <> "END") {
    if (isset($GLOBALS['logger'])) {
        $GLOBALS['logger']->write("STATE:" . $GLOBALS['state'], 'DEBUG');
    }
    switch($GLOBALS['state']) {
        /**********************************************************************/
        /*                          CONTROL_STACK                             */
        /* Checking the stack is empty for the required parameters            */
        /**********************************************************************/
        case "CONTROL_STACK" :
            $query = "select * from " . _LC_STACK_TABLE_NAME 
                   . " where policy_id = '" . $GLOBALS['policy'] 
                   . "' and cycle_id = '" . $GLOBALS['cycle'] . "'";
            Bt_doQuery($GLOBALS['db'], $query);
            if ($GLOBALS['db']->nb_result() == 0) {
                Bt_exitBatch(107, 'stack empty for your request');
                break;
            }
            Bt_updateWorkBatch();
            $GLOBALS['logger']->write("Batch number:".$GLOBALS['wb'], 'INFO');
            $query = "update " . _LC_STACK_TABLE_NAME 
                   . " set status = 'I' where status = 'W'";
            Bt_doQuery($GLOBALS['db'], $query);
            $GLOBALS['state'] = "GET_STEPS";
            break;
        /**********************************************************************/
        /*                          GET_STEPS                                 */
        /* Get the list of cycle steps                                        */
        /**********************************************************************/
        case "GET_STEPS" :
            $query = "select * from " . _LC_CYCLE_STEPS_TABLE_NAME 
                   . " where policy_id = '" . $GLOBALS['policy'] 
                   . "' and cycle_id = '" . $GLOBALS['cycle'] . "'";
            Bt_doQuery($GLOBALS['db'], $query);
            if ($GLOBALS['db']->nb_result() == 0) {
                Bt_exitBatch(11, 'Cycle Steps not found');
                break;
            } else {
                while ($stepsRecordset = $GLOBALS['db']->fetch_object()) {
                    $GLOBALS['steps'][$stepsRecordset->cycle_step_id] =
                        $GLOBALS['func']->object2array($stepsRecordset);
                    array_push(
                        $GLOBALS['steps'][$stepsRecordset->cycle_step_id], 
                        "KO"
                    );
                }
                //get the cycle break key if exists
                $query = "select break_key from " . _LC_CYCLES_TABLE_NAME 
                   . " where policy_id = '" . $GLOBALS['policy'] 
                   . "' and cycle_id = '" . $GLOBALS['cycle'] . "'";
                Bt_doQuery($GLOBALS['db'], $query);
                if ($GLOBALS['db']->nb_result() > 0) {
                    $breakKeyRecordset = $GLOBALS['db']->fetch_object();
                    $GLOBALS['breakKey'] = $breakKeyRecordset->break_key;
                }
            }
            $GLOBALS['state'] = "GET_DOCSERVERS";
            break;
        /**********************************************************************/
        /*                          GET_DOCSERVERS                            */
        /* Get the list of the docservers of each steps                       */
        /**********************************************************************/
        case "GET_DOCSERVERS" :
            $query = "select * from " . _LC_CYCLE_STEPS_TABLE_NAME 
                   . " where policy_id = '" . $GLOBALS['policy'] 
                   . "' and cycle_id = '" . $GLOBALS['cycle'] . "'";
            Bt_doQuery($GLOBALS['db'], $query);
            $GLOBALS['state'] = "A_STEP";
            if ($GLOBALS['db']->nb_result() == 0) {
                Bt_exitBatch(11, 'Cycle Steps not found');
                break;
            } else {
                while ($stepsRecordset = $GLOBALS['db']->fetch_object()) {
                    $query = "select * from " . _DOCSERVER_TYPES_TABLE_NAME 
                           . " where docserver_type_id = '" 
                           . $stepsRecordset->docserver_type_id . "'";
                    Bt_doQuery($GLOBALS['db2'], $query);
                    if ($GLOBALS['db2']->nb_result() == 0) {
                        Bt_exitBatch(12, 'Docserver type not found');
                        break;
                    } else {
                        $docserverTypesRecordset =
                            $GLOBALS['db2']->fetch_object();
                        $GLOBALS['docservers'][$stepsRecordset->cycle_step_id] =
                            $GLOBALS['func']->object2array(
                                $docserverTypesRecordset
                            );
                    }
                    // no need for a purge
                    $query = "select * from " . _DOCSERVERS_TABLE_NAME 
                           . " where docserver_type_id = '" 
                           . $stepsRecordset->docserver_type_id 
                           . "' and coll_id = '" . $GLOBALS['collection'] 
                           . "' order by priority_number";
                    Bt_doQuery($GLOBALS['db2'], $query);
                    if ($GLOBALS['db2']->nb_result() == 0) {
                        Bt_exitBatch(13, 'Docserver not found');
                        break;
                    } else {
                        $docserversRecordset = $GLOBALS['db2']->fetch_object();
                        $GLOBALS['docservers'][$stepsRecordset->cycle_step_id]
                            ['docserver'] = $GLOBALS['func']->object2array(
                                $docserversRecordset
                            );
                    }
                }
            }
            break;
        /**********************************************************************/
        /*                          A_STEP                                    */
        /* Processes a step cycle                                             */
        /**********************************************************************/
        case "A_STEP" :
            $GLOBALS['state'] = "EMPTY_STACK";
            foreach ($GLOBALS['steps'] as $key => $value) {
                if ($GLOBALS['steps'][$key][0] == "KO") {
                    $GLOBALS['currentStep'] = $GLOBALS['steps'][$key]
                        ['cycle_step_id'];
                    $GLOBALS['logger']->write(
                        "current step:" . $GLOBALS['currentStep'], 'INFO'
                    );
                    $GLOBALS['logger']->write(
                        "current operation:" 
                        . $GLOBALS['steps'][$key]['step_operation'], 'INFO'
                    );
                    $cptRecordsInStep = 0;
                    $resInContainer = 0;
                    $totalSizeToAdd = 0;
                    $theLastRecordInStep = false;
                    $query = "select * from " . _LC_STACK_TABLE_NAME 
                           . " where policy_id = '" . $GLOBALS['policy'] 
                           . "' and cycle_id = '" . $GLOBALS['cycle'] 
                           . "' and cycle_step_id = '".$GLOBALS['currentStep'] 
                           . "' and status = 'I' and coll_id = '" 
                           . $GLOBALS['collection'] . "'";
                    Bt_doQuery($GLOBALS['db'], $query);
                    $cptRecordsTotalInStep = $GLOBALS['db']->nb_result();
                    $GLOBALS['logger']->write(
                        "total res in the step:" . $cptRecordsTotalInStep, 
                        'INFO'
                    );
                    // no need for a purge
                    if ($cptRecordsTotalInStep <> 0 
                    && $GLOBALS['steps'][$GLOBALS['currentStep']]
                        ['step_operation'] <> "PURGE"
                    ) {
                        // Check size of the docserver 
                        // (stop the program if the docserver will be 
                        // full at 95 percent)
                        $query = "select sum(filesize) as sumfilesize from " 
                               . $GLOBALS['table'] . " where res_id in (select "
                               . "res_id from " . _LC_STACK_TABLE_NAME 
                               . " where policy_id = '" . $GLOBALS['policy'] 
                               . "' and cycle_id = '" . $GLOBALS['cycle'] 
                               . "' and cycle_step_id = '" 
                               . $GLOBALS['currentStep'] 
                               . "' and status = 'I' and coll_id = '" 
                               . $GLOBALS['collection'] . "')";
                        Bt_doQuery($GLOBALS['db'], $query);
                        $resSum = $GLOBALS['db']->fetch_object();
                        $reasonableLimitSize =
                            $GLOBALS['docservers'][$GLOBALS['currentStep']]
                            ['docserver']['size_limit_number'] * 0.95;
                        $targetSize = $resSum->sumfilesize +
                            $GLOBALS['docservers'][$GLOBALS['currentStep']]
                            ['docserver']['actual_size_number'];
                        if ($targetSize > $reasonableLimitSize) {
                            Bt_exitBatch(
                                28, 'The docserver will be full at 95 percent:'
                                . $targetSize . " > " . $reasonableLimitSize
                            );
                        }
                        $resultPath = array();
                        $totalSizeToAdd =
                            $GLOBALS['docservers'][$GLOBALS['currentStep']]
                            ['docserver']['actual_size_number'];
                        $resultPath =
                            Ds_createPathOnDocServer(
                                $GLOBALS['docservers'][$GLOBALS['currentStep']]
                                ['docserver']['path_template']
                            );
                        if ($resultPath['error'] <> "") {
                            Bt_exitBatch(
                                18, $resultPath['error']
                            );
                        }
                        $pathOnDocserver = $resultPath['destinationDir'];
                        $GLOBALS['logger']->write(
                            "target path on docserver:" . $pathOnDocserver, 
                            'INFO'
                        );
                    } elseif ($GLOBALS['steps'][$GLOBALS['currentStep']]
                    ['step_operation'] == "PURGE"
                    ) {
                        $nbDocserver = 0;
                        $GLOBALS['docservers'][$GLOBALS['currentStep']]
                            ['docserver'] = array();
                        $query = "select * from " . _DOCSERVERS_TABLE_NAME 
                               . " where docserver_type_id = '" 
                               . $GLOBALS['steps'][$GLOBALS['currentStep']]
                               ['docserver_type_id'] . "' and coll_id = '" 
                               . $GLOBALS['collection'] . "'";
                        Bt_doQuery($GLOBALS['db2'], $query);
                        while ($docserversRecordset =
                            $GLOBALS['db2']->fetch_object()
                        ) {
                            $GLOBALS['docservers'][$GLOBALS['currentStep']]
                            ['docserver'][$nbDocserver] =
                            $GLOBALS['func']->object2array(
                                $docserversRecordset
                            );
                            $nbDocserver++;
                        }
                    }
                    $GLOBALS['state'] = "A_RECORD";
                    break;
                }
            }
            break;
        /**********************************************************************/
        /*                          A_RECORD                                  */
        /* Process a record of a step                                         */
        /**********************************************************************/
        case "A_RECORD" :
            $cptRecordsInStep++;
            $GLOBALS['totalProcessedResources']++;
            $query = "select * from " . _LC_STACK_TABLE_NAME 
                   . " where policy_id = '" . $GLOBALS['policy'] 
                   . "' and cycle_id = '" . $GLOBALS['cycle'] 
                   . "' and cycle_step_id = '" . $GLOBALS['currentStep'] 
                   . "' and status = 'I' and coll_id = '" 
                   . $GLOBALS['collection'] . "'";
            Bt_doQuery($GLOBALS['db'], $query);
            if ($GLOBALS['db']->nb_result() == 0) {
                foreach ($GLOBALS['steps'] as $key => $value) {
                    if ($key == $GLOBALS['currentStep']) {
                        $GLOBALS['steps'][$key][0] = "OK";
                        break;
                    }
                }
                $GLOBALS['state'] = "A_STEP";
                break;
            } else {
                if ($cptRecordsInStep == $cptRecordsTotalInStep) {
                    $GLOBALS['logger']->write(
                        "The last record of the step", 
                        'INFO'
                    );
                    $theLastRecordInStep = true;
                }
                $stackRecordset = $GLOBALS['db']->fetch_object();
                $currentRecordInStack = array();
                $currentRecordInStack = $GLOBALS['func']->object2array(
                    $stackRecordset
                );
                //if signature available, we control it
                if ($GLOBALS['enabledEsign']) {
                    esign($currentRecordInStack['res_id']);
                }
                // if NEW operation we have to add new states
                if ($GLOBALS['steps'][$GLOBALS['currentStep']]
                    ['step_operation'] == "COPY" 
                    || $GLOBALS['steps'][$GLOBALS['currentStep']]
                    ['step_operation'] == "MOVE"
                ) {
                    controlIntegrityOfSource($currentRecordInStack['res_id']);
                    $sourceFilePath = getSourceResourcePath(
                        $currentRecordInStack['res_id']
                    );
                    if (!file_exists($sourceFilePath)) {
                        Bt_exitBatch(
                            27, 'Resource not found:' . $sourceFilePath
                        );
                        $GLOBALS['state'] = "END";
                        break;
                    } else {
                        $currentRecordInStack['fingerprint'] =
                            Ds_doFingerprint(
                                $sourceFilePath, 
                                $GLOBALS['docservers'][$GLOBALS['currentStep']]
                                ['fingerprint_mode']
                            );
                        $GLOBALS['logger']->write(
                            "current record:" . $currentRecordInStack['res_id'],
                            'DEBUG'
                        );
                        $GLOBALS['state'] = "COPY_OR_MOVE";
                    }
                } elseif ($GLOBALS['steps'][$GLOBALS['currentStep']]
                    ['step_operation'] == "PURGE"
                ) {
                    $GLOBALS['state'] = "CONTROL_ADR_X";
                } elseif ($GLOBALS['steps'][$GLOBALS['currentStep']]
                    ['step_operation'] == "NONE"
                ) {
                    doMinimalUpdate($currentRecordInStack['res_id']);
                    $GLOBALS['state'] = "A_RECORD";
                } else {
                    $GLOBALS['state'] = "END";
                }
            }
            break;
        /**********************************************************************/
        /*                          CONTROL_ADR_X                             */
        /* Controls whether this is the last record of the container          */
        /**********************************************************************/
        case "CONTROL_ADR_X" :
            $query = "select res_id from " . $GLOBALS['adrTable'] 
                   . " where res_id = " . $currentRecordInStack['res_id'];
            Bt_doQuery($GLOBALS['db'], $query);
            if ($GLOBALS['db']->nb_result() <= 1) {
                $GLOBALS['logger']->write(
                    'No purge for the resource ' 
                    . $currentRecordInStack['res_id'] 
                    . ' because this is the last adr available', 'INFO'
                );
                updateOnNonePurge($currentRecordInStack['res_id']);
                $GLOBALS['state'] = "A_RECORD";
            } else {
                if ($GLOBALS['docservers'][$GLOBALS['currentStep']]
                ['is_container'] == "Y"
                ) {
                    $GLOBALS['state'] = "CONTROL_CONTAINER_EMPTY";
                } else {
                    $GLOBALS['state'] = "DO_PURGE_ON_DOCSERVER";
                }
            }
            break;
        /**********************************************************************/
        /*                          CONTROL_CONTAINER_EMPTY                   */
        /* Controls whether the container is empty                            */
        /**********************************************************************/
        case "CONTROL_CONTAINER_EMPTY" :
            $GLOBALS['state'] = "DELETE_RES_ON_ADR_X";
            $dsToUpdate = array();
            //print_r($GLOBALS['docservers'][$GLOBALS['currentStep']]);
            for (
                $cptDs = 0;
                $cptDs < count(
                    $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']
                );
                $cptDs++
            ) {
                $sourceFilePath = getSourceResourcePath(
                    $currentRecordInStack['res_id'], 
                    $GLOBALS['docservers'][$GLOBALS['currentStep']]
                    ['docserver'][$cptDs]['docserver_id'], true
                );
                if ($sourceFilePath[0]['docserverId'] <> "" 
                    && $sourceFilePath[0]['basePath'] <> ""
                ) {
                    //print_r($sourceFilePath);
                    $query = "select count(*) as cptadr from " 
                           . $GLOBALS['adrTable'] . " where docserver_id = '" 
                           . $sourceFilePath[0]['docserverId'] 
                           . "' and path = '" . $sourceFilePath[0]['basePath'] 
                           . "' and filename = '" 
                           . $sourceFilePath[0]['fileName'] 
                           . "' and offset_doc <> '" 
                           . $sourceFilePath[0]['offsetDoc'] . "'";
                    Bt_doQuery($GLOBALS['db'], $query);
                    $line = $GLOBALS['db']->fetch_object();
                    //if exists at least one doc on the container 
                    //we remove only the adr
                    if ($line->cptadr > 0) {
                        array_push(
                            $dsToUpdate, 
                            array(
                                "docserverId" => $GLOBALS['docservers']
                                [$GLOBALS['currentStep']]['docserver'][$cptDs]
                                ['docserver_id'],
                            )
                        );
                        $GLOBALS['state'] = "DELETE_RES_ON_ADR_X";
                    } else {
                        $GLOBALS['logger']->write(
                            'We can purge the resource ' 
                            . $currentRecordInStack['res_id'] . ' on ' 
                            . $GLOBALS['docservers'][$GLOBALS['currentStep']]
                            ['docserver'][$cptDs]['docserver_id'], 'DEBUG'
                        );
                        $GLOBALS['state'] = "DO_PURGE_ON_DOCSERVER";
                    }
                }
            }
            break;
        /**********************************************************************/
        /*                          DO_PURGE_ON_DOCSERVER                     */
        /* Purge the record or container on the document server               */
        /**********************************************************************/
        case "DO_PURGE_ON_DOCSERVER" :
            $GLOBALS['state'] = "DELETE_RES_ON_ADR_X";
            $dsToUpdate = array();
            for (
                $cptDs = 0;
                $cptDs < count(
                    $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']
                );
                $cptDs++
            ) {
                $sourceFilePath = getSourceResourcePath(
                    $currentRecordInStack['res_id'], 
                    $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']
                    [$cptDs]['docserver_id']
                );
                if ($GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']
                    [$cptDs]['docserver_id'] <> ""
                ) {
                    if (!file_exists($sourceFilePath) 
                        && $sourceFilePath <> ""
                    ) {
                        Bt_exitBatch(
                            27, 'Resource not found for purge:' 
                            . $sourceFilePath . ' res_id:' 
                            . $currentRecordInStack['res_id'] 
                            . ' docserver_id:' 
                            . $GLOBALS['docservers'][$GLOBALS['currentStep']]
                            ['docserver'][$cptDs]['docserver_id']
                        );
                        $GLOBALS['state'] = "END";
                        break;
                    } else {
                        if (str_replace(
                            $GLOBALS['docserverSourcePath'], "", 
                            $sourceFilePath
                        ) <> ""
                        ) {
                            // WARNING unlink file
                            array_push(
                                $dsToUpdate, 
                                array(
                                    "docserverId" => $GLOBALS['docservers']
                                    [$GLOBALS['currentStep']]['docserver']
                                    [$cptDs]['docserver_id'],
                                )
                            );
                            $currentFileSize = filesize($sourceFilePath);
                            if (!(unlink($sourceFilePath))) {
                                Bt_exitBatch(
                                    26, 'File deletion impossible:'
                                    . $sourceFilePath
                                );
                                $GLOBALS['state'] = "END";
                                break;
                            } else {
                                $GLOBALS['logger']->write(
                                    'Purge file:' . $sourceFilePath, 'DEBUG'
                                );
                                $query = "select actual_size_number from " 
                                       . _DOCSERVERS_TABLE_NAME 
                                       . " where docserver_id = '" 
                                       . $GLOBALS['docservers']
                                       [$GLOBALS['currentStep']]['docserver']
                                       [$cptDs]['docserver_id'] . "'";
                                Bt_doQuery($GLOBALS['db'], $query);
                                $docserverRec = $GLOBALS['db']->fetch_object();
                                setSize(
                                    $GLOBALS['docservers']
                                    [$GLOBALS['currentStep']]
                                    ['docserver'][$cptDs]['docserver_id'], 
                                    $docserverRec->actual_size_number -
                                    $currentFileSize
                                );
                            }
                        }
                    }
                }
            }
            break;
        /**********************************************************************/
        /*                          DELETE_RES_ON_ADR_X                       */
        /* Removes the address of the resource in the database                */
        /**********************************************************************/
        case "DELETE_RES_ON_ADR_X" :
            deleteAdrx($currentRecordInStack['res_id'], $dsToUpdate);
            $GLOBALS['state'] = "A_RECORD";
            break;
        /**********************************************************************/
        /*                          COPY_OR_MOVE                              */
        /* The action step is a copy or a move                                */
        /**********************************************************************/
        case "COPY_OR_MOVE" :
            if (
                $GLOBALS['docservers'][$GLOBALS['currentStep']]
                ['is_container'] == "Y"
            ) {
                $GLOBALS['state'] = "CONTAINER";
            } else {
                $GLOBALS['state'] = "DO_COPY_OR_MOVE";
            }
            break;
        /**********************************************************************/
        /*                          CONTAINER                                 */
        /* It is a new container, it opens                                    */
        /* This is not a new container, add a resource                        */
        /**********************************************************************/
        case "CONTAINER" :
            if (!$isAContainerOpened) {
                $GLOBALS['state'] = "OPEN_CONTAINER";
            } else {
                $GLOBALS['state'] = "ADD_RECORD";
            }
            break;
        /**********************************************************************/
        /*                          OPEN_CONTAINER                            */
        /* Declares that the container is opened                              */
        /**********************************************************************/
        case "OPEN_CONTAINER" :
            $isAContainerOpened = true;
            $cptResInContainer = 0;
            $resInContainer = array();
            $GLOBALS['state'] = "ADD_RECORD";
            break;
        /**********************************************************************/
        /*                          ADD_RECORD                                */
        /* Adds a resource in the container                                   */
        /**********************************************************************/
        case "ADD_RECORD" :
            $dontProcessTheCurrentRecord = false;
            //compute the break key if exists and if container max number > 1
            if (
                (isset($GLOBALS['breakKey']) && $GLOBALS['breakKey'] <> '') &&
                $GLOBALS['docservers']
                    [$GLOBALS['currentStep']]['container_max_number'] > 1
            ) {
                $breakKeyCompare = getBreakKeyValue(
                    $currentRecordInStack['res_id'], $GLOBALS['breakKey']
                );
                //echo "compare " . $breakKeyCompare 
                //. " value " . $GLOBALS['breakKeyValue'] . "\r\n";
                if ($GLOBALS['breakKeyValue'] == '') {
                    //echo "first break key\r\n";
                    $GLOBALS['breakKeyValue'] = $breakKeyCompare;
                } elseif ($breakKeyCompare <> $GLOBALS['breakKeyValue']) {
                    //close the container and don't process the current record
                    //the current record will be processed on next turn
                    //echo "new break key\r\n";
                    $GLOBALS['breakKeyValue'] = $breakKeyCompare;
                    $dontProcessTheCurrentRecord = true;
                    $currentRecordInStack = array();
                    $cptRecordsInStep--;
                    //echo "dont process the current record\r\n";
                }
            }
            if (!$dontProcessTheCurrentRecord) {
                $cptResInContainer++;
                array_push(
                    $resInContainer, 
                    array(
                        "res_id" => $currentRecordInStack['res_id'], 
                        "source_path" => $sourceFilePath, 
                        "fingerprint" => Ds_doFingerprint(
                            $sourceFilePath, 
                            $GLOBALS['docservers'][$GLOBALS['currentStep']]
                            ['fingerprint_mode']
                        ),
                    )
                );
                $offsetDoc = "";
                $query = "update " . _LC_STACK_TABLE_NAME 
                       . " set status = 'W' where policy_id = '" 
                       . $GLOBALS['policy'] . "' and cycle_id = '" 
                       . $GLOBALS['cycle'] . "' and cycle_step_id = '" 
                       . $GLOBALS['currentStep'] . "' and coll_id = '" 
                       . $GLOBALS['collection'] . "' and res_id = " 
                       . $currentRecordInStack['res_id'];
                Bt_doQuery($GLOBALS['db'], $query);
                if (
                    $cptResInContainer >= $GLOBALS['docservers']
                    [$GLOBALS['currentStep']]['container_max_number'] 
                    || $theLastRecordInStep
                ) {
                    $GLOBALS['state'] = "CLOSE_CONTAINER";
                } else {
                    $GLOBALS['state'] = "A_RECORD";
                }
            } else {
                $GLOBALS['state'] = "CLOSE_CONTAINER";
            }
            break;
        /**********************************************************************/
        /*                          CLOSE_CONTAINER                           */
        /* Close the container because it is full                             */
        /**********************************************************************/
        case "CLOSE_CONTAINER" :
            $resultAip = array();
            $resultAip = createAip($resInContainer);
            $sourceFilePath = $resultAip['newSourceFilePath'];
            $resInContainer = $resultAip['resInContainer'];
            $isAContainerOpened = false;
            $cptResInContainer = 0;
            $GLOBALS['state'] = "DO_COPY_OR_MOVE";
            break;
        /**********************************************************************/
        /*                          DO_COPY_OR_MOVE                           */
        /* Copy or move the resource on the target document server            */
        /**********************************************************************/
        case "DO_COPY_OR_MOVE" :
            $infoFileNameInTargetDocserver = array();
            $infoFileNameInTargetDocserver =
                $GLOBALS['docserverControler']->getNextFileNameInDocserver(
                    $pathOnDocserver
                );
            if ($infoFileNameInTargetDocserver['error'] <> "") {
                Bt_exitBatch(
                    21, $infoFileNameInTargetDocserver['error']
                );
            }
            $copyResultArray = array();
            $infoFileNameInTargetDocserver['fileDestinationName'] .= "." 
                . strtolower($GLOBALS['func']->extractFileExt($sourceFilePath));
            $copyResultArray = Ds_copyOnDocserver(
                $sourceFilePath, 
                $infoFileNameInTargetDocserver, 
                $GLOBALS['docserverSourceFingerprint']
            );
            if (isset($copyResultArray['error']) 
                && $copyResultArray['error'] <> ""
            ) {
                Bt_exitBatch(
                    15, 'error to copy file on docserver:' 
                    . $copyResultArray['error'] . " " . $sourceFilePath . " " 
                    . $infoFileNameInTargetDocserver['destinationDir'] 
                    . $infoFileNameInTargetDocserver['fileDestinationName']
                );
                $GLOBALS['state'] = "END";
                break;
            }
            $destinationDir = $copyResultArray['destinationDir'];
            $fileDestinationName = $copyResultArray['fileDestinationName'];
            $totalSizeToAdd = $totalSizeToAdd + $copyResultArray['fileSize'];
            setSize(
                $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']
                ['docserver_id'], $totalSizeToAdd
            );
            $GLOBALS['state'] = "UPDATE_DATABASE";
            break;
        /**********************************************************************/
        /*                          UPDATE_DATABASE                           */
        /* Updating the database                                              */
        /**********************************************************************/
        case "UPDATE_DATABASE" :
            controlIntegrityOfTransfer(
                $currentRecordInStack, 
                $resInContainer, $destinationDir, $fileDestinationName
            );
            updateDatabase(
                $currentRecordInStack, $resInContainer, 
                $destinationDir, $fileDestinationName
            );
            $GLOBALS['state'] = "A_RECORD";
            break;
        /**********************************************************************/
        /*                          EMPTY_STACK                               */
        /* Empty stack if all resources are processed                         */
        /**********************************************************************/
        case "EMPTY_STACK" :
            $query = "select * from " . _LC_STACK_TABLE_NAME 
                   . " where status <> 'P' and "
                   . " policy_id = '" . $GLOBALS['policy'] 
                   . "' and cycle_id = '" . $GLOBALS['cycle'] . "'";
            Bt_doQuery($GLOBALS['db'], $query);
            if ($GLOBALS['db']->nb_result() > 0) {
                Bt_exitBatch(108, 'There are still documents to be processed');
            }
            $query = "delete from " . _LC_STACK_TABLE_NAME 
                   . " where status = 'P' and "
                   . " policy_id = '" . $GLOBALS['policy'] 
                   . "' and cycle_id = '" . $GLOBALS['cycle'] . "'";
            Bt_doQuery($GLOBALS['db'], $query);
            $GLOBALS['state'] = "END";
            break;
    }
}
$GLOBALS['logger']->write('End of process', 'INFO');
Bt_logInDataBase(
    $GLOBALS['totalProcessedResources'], 0, 'process without error'
);
$GLOBALS['db']->disconnect();
$GLOBALS['db2']->disconnect();
$GLOBALS['db3']->disconnect();
$GLOBALS['dbLog']->disconnect();
Ds_washTmp($GLOBALS['tmpDirectory']);
unlink($GLOBALS['lckFile']);
exit($GLOBALS['exitCode']);
