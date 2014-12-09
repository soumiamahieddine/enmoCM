<?php

/*
 *   Copyright 2008-2015 Maarch
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

/**
 * @brief API to manage batchs
 *
 * @file
 * @author Laurent Giovannoni
 * @date $date$
 * @version $Revision$
 * @ingroup core
 */

/**
 * Execute a sql query
 *
 * @param object $dbConn connection object to the database
 * @param string $queryTxt path of the file to include
 * @param boolean $transaction for rollback if error
 * @return true if ok, exit if ko and rollback if necessary
 */
function Bt_doQuery($dbConn, $queryTxt, $transaction=false)
{
    $res = $dbConn->query($queryTxt, true);
    if (!$res) {
        if ($transaction) {
            $GLOBALS['logger']->write('ROLLBACK', 'INFO');
            $dbConn->query('ROLLBACK', true);
        }
        Bt_exitBatch(
            104, 'SQL Query error:' . $queryTxt
        );
    }
    $GLOBALS['logger']->write('SQL query:' . $queryTxt, 'DEBUG');
    return true;
}

/**
 * Exit the batch with a return code, message in the log and
 * in the database if necessary
 *
 * @param int $returnCode code to exit (if > O error)
 * @param string $message message to the log and the DB
 * @return nothing, exit the program
 */
function Bt_exitBatch($returnCode, $message='')
{
    if (file_exists($GLOBALS['lckFile'])) {
        unlink($GLOBALS['lckFile']);
    }
    if ($returnCode > 0) {
        $GLOBALS['totalProcessedResources']--;
        if ($GLOBALS['totalProcessedResources'] == -1) {
            $GLOBALS['totalProcessedResources'] = 0;
        }
        if($returnCode < 100) {
            if (file_exists($GLOBALS['errorLckFile'])) {
                unlink($GLOBALS['errorLckFile']);
            }
            $semaphore = fopen($GLOBALS['errorLckFile'], "a");
            fwrite($semaphore, '1');
            fclose($semaphore);
        }
        $GLOBALS['logger']->write($message, 'ERROR', $returnCode);
        Bt_logInDataBase($GLOBALS['totalProcessedResources'], 1, 'return code:'
                         . $returnCode . ', ' . $message);
    } elseif ($message <> '') {
        $GLOBALS['logger']->write($message, 'INFO', $returnCode);
        Bt_logInDataBase($GLOBALS['totalProcessedResources'], 0, 'return code:'
                         . $returnCode . ', ' . $message);
    }
    exit($returnCode);
}

/**
* Insert in the database the report of the batch
* @param long $totalProcessed total of resources processed in the batch
* @param long $totalErrors total of errors in the batch
* @param string $info message in db
*/
function Bt_logInDataBase($totalProcessed=0, $totalErrors=0, $info='')
{

    $query = "insert into history_batch(module_name, batch_id, event_date, "
           . "total_processed, total_errors, info) values('"
           . $GLOBALS['batchName'] . "', " . $GLOBALS['wb'] . ", "
           . $GLOBALS['db']->current_datetime() . ", " . $totalProcessed . ", " . $totalErrors . ", '"
           . $GLOBALS['func']->protect_string_db(substr(str_replace('\\', '\\\\', str_replace("'", "`", $info)), 0, 999)) . "')";
           //. $GLOBALS['func']->protect_string_db(substr($info, 0, 999)) . "')";
    $GLOBALS['dbLog']->query($query);
    //Bt_doQuery($GLOBALS['db'], $query);
}

/**
 * Get the batch if of the batch
 *
 * @return nothing
 */
function Bt_getWorkBatch()
{
    $req = "select param_value_int from parameters where id = "
         . "'". $GLOBALS['batchName'] . "_id'";
    $GLOBALS['db']->query($req);
    while ($reqResult = $GLOBALS['db']->fetch_array()) {
        $GLOBALS['wbCompute'] = $reqResult[0] + 1;
    }
    if ($GLOBALS['wbCompute'] == '') {
        $req = "insert into parameters(id, param_value_int) values "
             . "('" . $GLOBALS['batchName'] . "_id', 1)";
        $GLOBALS['db']->query($req);
        $GLOBALS['wbCompute'] = 1;
    }
}

/**
 * Update the database with the new batch id of the batch
 *
 * @return nothing
 */
function Bt_updateWorkBatch()
{
    $req = "update parameters set param_value_int  = " . $GLOBALS['wbCompute'] . " "
         . "where id = '" . $GLOBALS['batchName'] . "_id'";
    $GLOBALS['db']->query($req);
}

/**
 * Include the file requested if exists
 *
 * @param string $file path of the file to include
 * @return nothing
 */
function Bt_myInclude($file)
{
    if (file_exists($file)) {
        include_once ($file);
    } else {
        throw new IncludeFileError($file);
    }
}

/**
 * Get the current date to process
 *
 * @return nothing
 */
function Bt_getCurrentDateToProcess()
{
    $req = "select param_value_date from parameters where id = "
         . "'". $GLOBALS['batchName'] . "_" . $GLOBALS['policy'] 
         . "_" . $GLOBALS['cycle'] . "_current_date'";
    $GLOBALS['db']->query($req);
    $reqResult = $GLOBALS['db']->fetch_object();
    if ($reqResult->param_value_date == '') {
        $req = "insert into parameters(id, param_value_date) values "
             . "('" . $GLOBALS['batchName'] . "_" . $GLOBALS['policy'] 
             . "_" . $GLOBALS['cycle'] . "_current_date', '" 
             . $GLOBALS['startDateRecovery'] . "')";
        $GLOBALS['db']->query($req);
        $GLOBALS['currentDate'] = $GLOBALS['startDateRecovery'];
    } else {
        $resultDate = formatDateFromDb($reqResult->param_value_date);
        if (
            $GLOBALS['func']->compare_date(
                $GLOBALS['startDateRecovery'], 
                $resultDate
            ) == 'date1'
        ) {
            $GLOBALS['currentDate'] = $GLOBALS['startDateRecovery'];
        } else {
            $GLOBALS['currentDate'] = $resultDate;
        }
    }
}

/**
 * Update the database with the current date to process
 *
 * @return nothing
 */
function Bt_updateCurrentDateToProcess()
{
    $req = "update parameters set param_value_date  = '" . $GLOBALS['currentDate'] . "' "
         . "where id = '" . $GLOBALS['batchName'] . "_" . $GLOBALS['policy'] 
         . "_" . $GLOBALS['cycle'] . "_current_date'";
    $GLOBALS['db']->query($req);
}

/**
 * Compute the end current date to process
 *
 * @return nothing
 */
function Bt_getEndCurrentDateToProcess()
{
    $dateArray = array();
    $tabDate = explode('/' , $GLOBALS['currentDate']);
    $theDate  = $tabDate[2] . '-' . $tabDate[1] . '-' . $tabDate[0];
    $dateArray = date_parse($theDate);
    $GLOBALS['endCurrentDate'] = strftime("%d/%m/%Y", mktime(0, 0, 0, $dateArray['month'] +1 , 0, $dateArray['year']));
}

/**
 * Compute the next month currentDate
 *
 * @return nothing
 */
function Bt_computeNextMonthCurrentDate()
{
    $tabDate = array();
    $tabDate = explode('/' , $GLOBALS['currentDate']);
    $theDate = $tabDate[2] . '-' . $tabDate[1] . '-' . $tabDate[0];
    $GLOBALS['currentDate'] = date("d/m/Y", strtotime('+1 month', strtotime($theDate)));
    Bt_getEndCurrentDateToProcess();
}

/**
 * Compute the creation date clause
 *
 * @return nothing
 */
function Bt_computeCreationDateClause()
{
    $GLOBALS['creationDateClause'] = '';
    if ($GLOBALS['currentDate'] <> '') {
        $GLOBALS['creationDateClause'] = " and (creation_date >= '" . $GLOBALS['currentDate'] . "'";
        if ($GLOBALS['endCurrentDate'] <> '') {
            $GLOBALS['creationDateClause'] .= " and creation_date <= '" . $GLOBALS['endCurrentDate'] . "'";
        }
        $GLOBALS['creationDateClause'] .= ")";
    }
}

/**
* Formats a datetime to a dd/mm/yyyy format (date)
*
* @param    $date datetime The date to format
* @return   datetime The formated date
*/
function formatDateFromDb($date)
{
    $lastDate = '';
    if ($date <> "") {
        if (strpos($date," ")) {
            $date_ex = explode(" ",$date);
            $theDate = explode("-",$date_ex[0]);
            $lastDate = $theDate[0] . "/" . $theDate[1] . "/" . $theDate[2];
        } else {
            $theDate = explode("-",$date);
            $lastDate = $theDate[0] . "/" . $theDate[1] . "/" . $theDate[2];
        }
    }
    return $lastDate;
}
