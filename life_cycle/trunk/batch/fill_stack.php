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
 *  12 : Problem with the php include path
 *  13 : Collection unknow
 */

// load the config and prepare to process
include('load_fill_stack.inc');

/******************************************************************************/
/* beginning */
$state = 'CONTROL_STACK';
while ($state <> 'END') {
    if (isset($GLOBALS['logger'])) {
        $GLOBALS['logger']->write('STATE:' . $state, 'INFO');
    }
    switch ($state) {
        /**********************************************************************/
        /*                          CONTROL_STACK                             */
        /* Checking if the stack is full                                      */
        /**********************************************************************/
        case 'CONTROL_STACK' :
            $db->connect();
            $query = "select * from " . _LC_STACK_TABLE_NAME;
            do_query($db, $query);
            if ($db->nb_result() > 0) {
                $GLOBALS['logger']->write('WARNING stack is full', 'ERROR', 7);
                $db->disconnect();
                $GLOBALS['exitCode'] = 7;
                $state = 'END';
                break;
            }
            $db->disconnect();
            $state = 'GET_STEPS';
            break;
        /**********************************************************************/
        /*                          GET_STEPS                                 */
        /* Get the list of cycle steps                                        */
        /**********************************************************************/
        case 'GET_STEPS' :
            $db->connect();
            $query = "select * from " . _LC_CYCLE_STEPS_TABLE_NAME 
                   . " where policy_id = '" . $GLOBALS['policy'] 
                   . "' and cycle_id = '" . $GLOBALS['cycle'] . "'";
            do_query($db, $query);
            if ($db->nb_result() == 0) {
                $GLOBALS['logger']->write('Cycle Steps not found', 'ERROR', 11);
                $db->disconnect();
                $GLOBALS['exitCode'] = 11;
                $state = 'END';
                break;
            } else {
                while ($stepsRecordset = $db->fetch_object()) {
                    array_push(
                        $GLOBALS['steps'], 
                        array('cycle_step_id' => $stepsRecordset->cycle_step_id)
                    );
                }
            }
            $db->disconnect();
            $state = 'SELECT_RES';
            break;
        /**********************************************************************/
        /*                          SELECT_RES                                */
        /* Selects candidates from each step of the cycle                     */
        /**********************************************************************/
        case 'SELECT_RES' :
            $orderBy = '';
            $db->connect();
            // get the where clause of the cycle
            $query = "select * from " . _LC_CYCLES_TABLE_NAME 
                   . " where policy_id = '" . $GLOBALS['policy'] 
                   . "' and cycle_id = '" . $GLOBALS['cycle'] . "'";
            do_query($db, $query);
            if ($db->nb_result() > 0) {
                $cycleRecordset = $db->fetch_object();
            } else {
                $GLOBALS['logger']->write(
                    'cycle not found for policy:' 
                    . $GLOBALS['policy'] . ', cycle:' 
                    . $GLOBALS['cycle'], 'ERROR', 8
                );
                $db->disconnect();
                $GLOBALS['exitCode'] = 8;
                $state = 'END';
                break;
            }
            // compute the previous step
            $query = "select * from " . _LC_CYCLES_TABLE_NAME 
                   . " where policy_id = '" . $GLOBALS['policy'] 
                   . "' and sequence_number = " 
                   . ($cycleRecordset->sequence_number - 1
             );
            do_query($db, $query);
            if ($db->nb_result() > 0) {
                $cyclePreviousRecordset = $db->fetch_object();
            } else {
                $GLOBALS['logger']->write(
                    'previous cycle not found for policy:' 
                    . $GLOBALS['policy'] . ', cycle:' 
                    . $GLOBALS['cycle'], 'ERROR', 9
                );
                $db->disconnect();
                $GLOBALS['exitCode'] = 9;
                $state = 'END';
                break;
            }
            // select resources
            if ($cycleRecordset->break_key <> '') {
                $orderBy = ' order by ' . $cycleRecordset->break_key;
            }
            if ($GLOBALS['stackSizeLimit'] <> '') {
                $limit = ' LIMIT ' . $GLOBALS['stackSizeLimit'];
            }
            $query = "select res_id from " . $GLOBALS['table'] 
                   . " where policy_id = '" . $GLOBALS['policy'] 
                   . "' and cycle_id = '" . $cyclePreviousRecordset->cycle_id 
                   . "' and " . $cycleRecordset->where_clause 
                   . $orderBy . $limit;
            do_query($db, $query);
            $resourcesArray = array();
            if ($db->nb_result() > 0) {
                while ($resoucesRecordset = $db->fetch_object()) {
                    array_push(
                        $resourcesArray, 
                        array('res_id' => $resoucesRecordset->res_id)
                    );
                }
            } else {
                $GLOBALS['logger']->write(
                    'no resource found for policy:' 
                    . $GLOBALS['policy'] . ', cycle:' 
                    . $GLOBALS['cycle'], 'ERROR', 10
                );
                $db->disconnect();
                $GLOBALS['exitCode'] = 10;
                $state = 'END';
                break;
            }
            $db->disconnect();
            updateWorkBatch($db);
            $GLOBALS['logger']->write('Batch number:' . $GLOBALS['wb'], 'INFO');
            $state = 'FILL_STACK';
            break;
        /**********************************************************************/
        /*                          FILL_STACK                                */
        /* Fill the stack of candidates from each step of the cycle           */
        /**********************************************************************/
        case 'FILL_STACK' :
            $db->connect();
            for (
                $cptSteps = 0;
                $cptSteps < count($GLOBALS['steps']);
                $cptSteps++
            ) {
                for ($cptRes = 0;$cptRes < count($resourcesArray);$cptRes++) {
                    $query = "insert into " . _LC_STACK_TABLE_NAME 
                           . " (policy_id, cycle_id, cycle_step_id, coll_id, " 
                           . "res_id, status) values ('" . $GLOBALS['policy'] 
                           . "', '" . $GLOBALS['cycle'] . "', '" 
                           . $GLOBALS['steps'][$cptSteps]['cycle_step_id'] 
                           . "', '" . $GLOBALS['collection'] . "', " 
                           . $resourcesArray[$cptRes]["res_id"] . ", 'I')";
                    do_query($db, $query);
                    //history
                    $query = "insert into " . HISTORY_TABLE 
                           . " (table_name, record_id, event_type, user_id, "
                           . "event_date, info, id_module) values ('" 
                           . $GLOBALS['table'] . "', '" 
                           . $resourcesArray[$cptRes]["res_id"] 
                           . "', 'ADD', 'LC_BOT', '" . date("d") . "/" 
                           . date("m") . "/" . date("Y") . " " . date("H") 
                           . ":" . date("i") . ":" . date("s") 
                           . "', 'fill stack, policy:" . $GLOBALS['policy'] 
                           . ", cycle:" . $GLOBALS['cycle'] . ", cycle step:" 
                           . $GLOBALS['steps'][$cptSteps]['cycle_step_id'] 
                           . ", collection:" . $GLOBALS['collection'] 
                           . "', 'life_cycle')";
                    do_query($db, $query);
                }
            }
            $db->disconnect();
            $state = 'END';
            break;
    }
}
$GLOBALS['logger']->write('End of process', 'INFO');
exit($GLOBALS['exitCode']);
