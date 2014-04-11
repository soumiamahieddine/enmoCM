<?php

/*
 *  Copyright 2008-2014 Maarch
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
 * @brief Batch to process purge
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
 *  112 : AIP not able to be purged
 * ****   HEAVY PROBLEMS with an error semaphore
 *  13  : Docserver not found
 *  21  : Problem to create directory on output directory
 *  22  : Problem to copy resource on output directory
 */

date_default_timezone_set('Europe/Paris');
try {
    include('load_process_purge.php');
} catch (IncludeFileError $e) {
    echo "Maarch_CLITools required ! \n (pear.maarch.org)\n";
    exit(106);
}

/******************************************************************************/
/* beginning */
$GLOBALS['state'] = "SELECT_RES";
while ($GLOBALS['state'] <> "END") {
    if (isset($GLOBALS['logger'])) {
        $GLOBALS['logger']->write("STATE:" . $GLOBALS['state'], 'DEBUG');
    }
    switch($GLOBALS['state']) {
        /**********************************************************************/
        /*                          SELECT_RES                                */
        /*                                                                    */
        /**********************************************************************/
        case "SELECT_RES" :
            $orderBy = 'order by res_id';
            
            if ($GLOBALS['stackSizeLimit'] <> '') {
                $limit = ' LIMIT ' . $GLOBALS['stackSizeLimit'];
            }

            $where_clause = " policy_id = '" . $GLOBALS['policy']
                . "' and cycle_id = '" . $GLOBALS['cycle']
                . "' and " . $GLOBALS['whereClause'];
                   
            $reverse_where_clause = " policy_id = '" . $GLOBALS['policy']
                . "' and cycle_id = '" . $GLOBALS['cycle']
                . "' and " . $GLOBALS['reverseWhereClause'];

            $query = $GLOBALS['db']->limit_select(0, $GLOBALS['stackSizeLimit'], 'res_id', 
                $GLOBALS['table'], $where_clause, $orderBy);
            
            Bt_doQuery($GLOBALS['db'], $query);
            $GLOBALS['logger']->write('select res query:' . $query, 'INFO');

            $resourcesArray = array();
            if ($GLOBALS['db']->nb_result() > 0) {
                while ($resoucesRecordset = $GLOBALS['db']->fetch_object()) {
                    array_push(
                        $resourcesArray,
                        array('res_id' => $resoucesRecordset->res_id)
                    );
                }
            } else {
                Bt_exitBatch(111, 'no resource found for policy:'
                    . $GLOBALS['policy'] . ', cycle:'
                    . $GLOBALS['cycle'] . ', where clause:' . $GLOBALS['whereClause']);
                break;
            }
            //var_dump($resourcesArray);
            Bt_updateWorkBatch();
            $GLOBALS['logger']->write('Batch number:' . $GLOBALS['wb'], 'INFO');
            $countRA = count($resourcesArray);
            for ($cptRes = 0;$cptRes < $countRA;$cptRes++) {
                //history
                $query = "insert into " . HISTORY_TABLE
                       . " (table_name, record_id, event_type, user_id, "
                       . "event_date, info, id_module) values ('"
                       . $GLOBALS['table'] . "', '"
                       . $resourcesArray[$cptRes]["res_id"]
                       . "', 'ADD', 'PURGE_BOT', "
                       . $GLOBALS['db']->current_datetime()
                       . ", 'purge, policy:" . $GLOBALS['policy']
                       . ", cycle:" . $GLOBALS['cycle'] . ", where clause:"
                       . str_replace("'", "''", $GLOBALS['whereClause'])
                       . ", collection:" . $GLOBALS['collection']
                       . "', 'life_cyle')";
                Bt_doQuery($GLOBALS['db'], $query);
                $GLOBALS['totalProcessedResources']++;
            }
            $state = 'SELECT_AIP';
            break;
        /**********************************************************************/
        /*                          SELECT_AIP                                */
        /*                                                                    */
        /**********************************************************************/
        case "SELECT_AIP" :
            $queryAip = "select distinct (docserver_id, path, filename) from " 
                . $GLOBALS['adrTable']
                . " where res_id in (select res_id from " . $GLOBALS['table'] 
                . " where " . $where_clause . ")";
            
            Bt_doQuery($GLOBALS['db'], $queryAip);
            $GLOBALS['logger']->write('select AIP query:' . $queryAip, 'INFO');
            
            $aipArray = array();
            if ($GLOBALS['db']->nb_result() > 0) {
                while ($aipRecordset = $GLOBALS['db']->fetch_array()) {
                    $aipRecordset[0] = str_replace("(", "", $aipRecordset[0]);
                    $aipRecordset[0] = str_replace(")", "", $aipRecordset[0]);
                    $currentAip = array();
                    $currentAip = explode(",", $aipRecordset[0]);
                    $query = "select path_template from docservers " 
                       . " where docserver_id = '" . $currentAip[0] . "' ";
                    Bt_doQuery($GLOBALS['db2'], $query);
                    if ($GLOBALS['db2']->nb_result() == 0) {
                        Bt_exitBatch(13, 'Docserver:' . $currentAip[0] . ' not found');
                        break;
                    } else {
                        $dsRecordset = $GLOBALS['db2']->fetch_object();
                        $dsPath = $dsRecordset->path_template;
                    }
                    array_push(
                        $aipArray, 
                        array(
                            'docserver_id' => $currentAip[0],
                            'path' => $currentAip[1],
                            'filename' => $currentAip[2],
                            'path_template' => $dsPath
                        )
                    );
                }
            } else {
                Bt_exitBatch(111, 'no aip found for policy:'
                    . $GLOBALS['policy'] . ', cycle:'
                    . $GLOBALS['cycle'] . ', where clause:' . $GLOBALS['whereClause']);
                break;
            }
            //var_dump($aipArray);
            $state = 'CONTROL_AIP';
            break;
        /**********************************************************************/
        /*                          CONTROL_AIP                               */
        /*                                                                    */
        /**********************************************************************/
        case "CONTROL_AIP" :
            $countAIP = count($aipArray);
            for ($cptAIPCTRL = 0;$cptAIPCTRL < $countAIP;$cptAIPCTRL++) {
                $controlAip = "select res_id from " . $GLOBALS['table'] 
                    . " where res_id in (select res_id from " . $GLOBALS['adrTable']
                    . " where docserver_id = '" . $aipArray[$cptAIPCTRL]['docserver_id'] ."'"  
                    . " and path = '" . $aipArray[$cptAIPCTRL]['path'] ."'"  
                    . " and filename = '" . $aipArray[$cptAIPCTRL]['filename'] ."')"  
                    . " and " . $reverse_where_clause;
                Bt_doQuery($GLOBALS['db'], $controlAip);
                $GLOBALS['logger']->write('control AIP query:' . $controlAip, 'INFO');
                if ($GLOBALS['db']->nb_result() > 0) {
                    $GLOBALS['logger']->write('AIP:' . $aipArray[$cptAIPCTRL]['docserver_id'] 
                        . ',' . $aipArray[$cptAIPCTRL]['path'] 
                        . ',' . $aipArray[$cptAIPCTRL]['filename'] 
                        . ' not able to be purged', 'ERROR', 112);
                    unset($aipArray[$cptAIPCTRL]);
                }
            }
            array_values($aipArray);
            $state = 'MOVE_AIP';
            break;
        /**********************************************************************/
        /*                          MOVE_AIP                                  */
        /*                                                                    */
        /**********************************************************************/
        case "MOVE_AIP" :
            umask(0022);
            $countAIP = count($aipArray);
            for ($cptAIPMV = 0;$cptAIPMV < $countAIP;$cptAIPMV++) {
                $origin = $aipArray[$cptAIPMV]['path_template'] 
                    . DIRECTORY_SEPARATOR 
                    . str_replace(
                        '#', 
                        DIRECTORY_SEPARATOR, 
                        $aipArray[$cptAIPMV]['path']
                    ) 
                    . $aipArray[$cptAIPMV]['filename'];
                $rootPath = $GLOBALS['outputDirectory'] 
                    . DIRECTORY_SEPARATOR . $aipArray[$cptAIPMV]['docserver_id'] . DIRECTORY_SEPARATOR
                    . str_replace('#', DIRECTORY_SEPARATOR, $aipArray[$cptAIPMV]['path']);
                //create output dir
                if (!is_dir($rootPath)) {
                    if(!mkdir($rootPath, 0777, true)) {
                        Bt_exitBatch(
                            21, 'Problem to create directory on output directory:' . $rootPath
                        );
                    }
                }
                $destination = $rootPath . $aipArray[$cptAIPMV]['filename'];
                //TODO:replace copy by move
                if (!rename($origin, $destination)) {
                //if (!copy($origin, $destination)) {
                    Bt_exitBatch(
                        22, 'Problem to copy resource on output directory:' 
                        . $origin . ' ' . $destination
                    );
                }
                $GLOBALS['logger']->write('move AIP :' . $origin . ' to:' . $destination, 'INFO');
            }
            $state = 'UPDATE_RES';
            break;
        /**********************************************************************/
        /*                          UPDATE_RES                                */
        /*                                                                    */
        /**********************************************************************/
        case "UPDATE_RES" :
            $deleteQuery = '';
            for ($cptRes = 0;$cptRes < $countRA;$cptRes++) {
                $GLOBALS['logger']->write('UPDATE RES and prepare sql deletion:' 
                    . $resourcesArray[$cptRes]["res_id"], 'INFO');
                $query = "update " . $GLOBALS['table']
                   . " set cycle_id = '" . $GLOBALS['endCycleId'] . "' "
                   . " where res_id = " . $resourcesArray[$cptRes]["res_id"];
                //TODO:execute query
                Bt_doQuery($GLOBALS['db'], $query);
                $deleteQuery .= "delete from " . $GLOBALS['table']
                   . " where res_id = " . $resourcesArray[$cptRes]["res_id"] . ';' . PHP_EOL;
            }
            $fileDeleteQuery = $GLOBALS['outputDirectory'] . DIRECTORY_SEPARATOR 
                . $GLOBALS['wb'] . '_delete_query.sql';
            $fp = fopen($fileDeleteQuery, 'w');
            fwrite($fp, $deleteQuery);
            fclose($fp);
            $state = 'END';
            break;
    }
}
$GLOBALS['logger']->write('End of process', 'INFO');
Bt_logInDataBase(
    $GLOBALS['totalProcessedResources'], 0, 'process without error'
);
$GLOBALS['db']->disconnect();
$GLOBALS['db2']->disconnect();
$GLOBALS['dbLog']->disconnect();
unlink($GLOBALS['lckFile']);
exit($GLOBALS['exitCode']);
