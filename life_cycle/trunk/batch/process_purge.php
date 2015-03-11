<?php

/*
 *  Copyright 2008-2015 Maarch
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

/*****************************************************************************
WARNING : THIS BATCH ERASE RESOURCES IN DATABASE AND IN DOCSERVERS 
Please note this batch deletes resources in the database 
and storage spaces (docservers). 
You need to run only if it is set -> Make especially careful to 
define the where clause.
FOR THE CASE OF AIP : to be used only if the AIP are single resources.
*****************************************************************************/

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
 *  113 : Security problem with where clause
 * ****   HEAVY PROBLEMS with an error semaphore
 *  13  : Docserver not found
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
            $where_clause = $GLOBALS['whereClause'];
            $query = "select res_id, docserver_id, path, filename, fingerprint from " . $GLOBALS['table'] 
                . " where " . $where_clause . " " 
                . $limit . " " . $orderBy;
            Bt_doQuery($GLOBALS['db'], $query);
            $GLOBALS['logger']->write('select res query:' . $query, 'INFO');
            $resourcesArray = array();
            if ($GLOBALS['db']->nb_result() > 0) {
                while ($resoucesRecordset = $GLOBALS['db']->fetch_object()) {
                    $queryDs = "select path_template from docservers " 
                       . " where docserver_id = '" . $resoucesRecordset->docserver_id . "' ";
                    Bt_doQuery($GLOBALS['db2'], $queryDs);
                    if ($GLOBALS['db2']->nb_result() == 0) {
                        Bt_exitBatch(13, 'Docserver:' . $resoucesRecordset->docserver_id . ' not found');
                        break;
                    } else {
                        $dsRecordset = $GLOBALS['db2']->fetch_object();
                        $dsPath = $dsRecordset->path_template;
                    }
                    array_push(
                        $resourcesArray,
                        array(
                            'res_id' => $resoucesRecordset->res_id,
                            'docserver_id' => $resoucesRecordset->docserver_id,
                            'path_template' => $dsPath,
                            'path' =>  str_replace('#', DIRECTORY_SEPARATOR, $resoucesRecordset->path),
                            'filename' => $resoucesRecordset->filename,
                            'fingerprint' => $resoucesRecordset->fingerprint,
                            'adr' => array(),
                        )
                    );
                }
            } else {
                Bt_exitBatch(111, 'no resource found for collection:'
                    . $GLOBALS['collection'] . ', where clause:' 
                    . str_replace("'", "''", $GLOBALS['whereClause']));
                break;
            }
            //var_dump($resourcesArray);
            Bt_updateWorkBatch();
            $GLOBALS['logger']->write('Batch number:' . $GLOBALS['wb'], 'INFO');
            $countRA = count($resourcesArray);
            for ($cptRes = 0;$cptRes < $countRA;$cptRes++) {
                $queryAip = "select res_id, docserver_id, path, filename, fingerprint from " 
                    . $GLOBALS['adrTable']
                    . " where res_id = " . $resourcesArray[$cptRes]["res_id"];
                Bt_doQuery($GLOBALS['db'], $queryAip);
                $aipArray = array();
                if ($GLOBALS['db']->nb_result() > 0) {
                    while ($resoucesRecordsetAdr = $GLOBALS['db']->fetch_object()) {
                        $queryDs = "select path_template from docservers " 
                           . " where docserver_id = '" . $resoucesRecordsetAdr->docserver_id . "' ";
                        Bt_doQuery($GLOBALS['db2'], $queryDs);
                        if ($GLOBALS['db2']->nb_result() == 0) {
                            Bt_exitBatch(13, 'Docserver:' . $resoucesRecordsetAdr->docserver_id . ' not found');
                            break;
                        } else {
                            $dsRecordset = $GLOBALS['db2']->fetch_object();
                            $dsPath = $dsRecordset->path_template;
                        }
                        array_push($resourcesArray[$cptRes]['adr'], array(
                                'res_id' => $resoucesRecordsetAdr->res_id,
                                'docserver_id' => $resoucesRecordsetAdr->docserver_id,
                                'path_template' => $dsPath,
                                'path' => str_replace('#', DIRECTORY_SEPARATOR, $resoucesRecordsetAdr->path),
                                'filename' => $resoucesRecordsetAdr->filename,
                                'fingerprint' => $resoucesRecordsetAdr->fingerprint,
                            )
                        );
                    }
                }
                //history
                $query = "insert into " . HISTORY_TABLE
                       . " (table_name, record_id, event_type, user_id, "
                       . "event_date, info, id_module) values ('"
                       . $GLOBALS['table'] . "', '"
                       . $resourcesArray[$cptRes]["res_id"]
                       . "', 'ADD', 'PURGE_BOT', '"
                       . date("d") . "/" . date("m") . "/" . date("Y") . " " . date("H") 
                       . ":" . date("i") . ":" . date("s")
                       . "', 'purge, where clause:" 
                       . str_replace("'", "''", $GLOBALS['whereClause'])
                       . ", collection:" . $GLOBALS['collection']
                       . "', 'life_cyle')";
                Bt_doQuery($GLOBALS['db'], $query);
                $GLOBALS['totalProcessedResources']++;
            }
            //print_r($resourcesArray);
            $state = 'DELETE_RES_ON_FS';
            //$state = 'END';
            break;
        /**********************************************************************/
        /*                          DELETE_RES_ON_FS                          */
        /*                                                                    */
        /**********************************************************************/
        case "DELETE_RES_ON_FS" :
            $cptRes = 0;
            $countRA = count($resourcesArray);
            for ($cptRes = 0;$cptRes < $countRA;$cptRes++) {
                $GLOBALS['logger']->write('Prepare file deletion for res_id:' 
                    . $resourcesArray[$cptRes]["res_id"], 'INFO');
                $countAdr = count($resourcesArray[$cptRes]['adr']);
                if ($countAdr > 0) {
                    $cptAdr = 0;
                    for ($cptAdr = 0;$cptAdr < $countAdr;$cptAdr++) {
                        $path = $resourcesArray[$cptRes]['adr'][$cptAdr]['path_template'] 
                            . $resourcesArray[$cptRes]['adr'][$cptAdr]['path']
                            . $resourcesArray[$cptRes]['adr'][$cptAdr]['filename'];
                        //echo $path . PHP_EOL;
                        if (file_exists($path)) {
                            unlink($path);
                        } else {
                            $GLOBALS['logger']->write('File for the collection ' 
                                . $GLOBALS['collection'] . ' and res_id ' 
                                . $resourcesArray[$cptRes]['res_id'] . ' not exits : '
                                . $path, 'WARNING');
                        }
                    }
                } else {
                    $path = $resourcesArray[$cptRes]['path_template'] 
                          . $resourcesArray[$cptRes]['path']
                          . $resourcesArray[$cptRes]['filename'];
                    //echo $path . PHP_EOL;
                    if (file_exists($path)) {
                        unlink($path);
                    } else {
                        $GLOBALS['logger']->write('File for the collection ' 
                            . $GLOBALS['collection'] . ' and res_id ' 
                            . $resourcesArray[$cptRes]['res_id'] . ' not exits : '
                            . $path, 'WARNING');
                    }
                }
            }
            $state = 'DELETE_RES_ON_DB';
            break;
        /**********************************************************************/
        /*                          DELETE_RES_ON_DB                          */
        /*                                                                    */
        /**********************************************************************/
        case "DELETE_RES_ON_DB" :

            $arrayEntityId = array();
            $arrayEntityNbDocs = array();
            $arraySubEntitiesNbDocs = array();

            array_push($arrayEntityId, 'Nom de l\'entité');
            array_push($arrayEntityNbDocs, 'Nombre de document dans l\'entité');
            array_push($arraySubEntitiesNbDocs, 'Nombre de document dans l\'entité et sous entités');

            for ($cptRes = 0;$cptRes < $countRA;$cptRes++) {

                $queryDestination = "SELECT destination FROM " . $GLOBALS['table'] 
                   . " WHERE res_id = " . $resourcesArray[$cptRes]["res_id"];
                Bt_doQuery($GLOBALS['db2'], $queryDestination);

                if ($GLOBALS['db2']->nb_result() > 0) {

                    $destinationRes = $GLOBALS['db2']->fetch_object();

                    if (!in_array($destinationRes->destination, $arrayEntityId)) {
                        array_push($arrayEntityId, $destinationRes->destination);
                        array_push($arrayEntityNbDocs, 1);
                    } else {
                        $keyEntity = array_search($destinationRes->destination, $arrayEntityId);
                        $arrayEntityNbDocs[$keyEntity]++;
                    }
                }

                $deleteResQuery = '';
                $deleteAdrQuery = '';
                $deleteNotesQuery = '';
                $GLOBALS['logger']->write('Prepare sql deletion for res_id:' 
                    . $resourcesArray[$cptRes]["res_id"], 'INFO');

                $deleteResQuery = "DELETE FROM " . $GLOBALS['table']
                   . " WHERE res_id = " . $resourcesArray[$cptRes]["res_id"];
                //echo $deleteResQuery . PHP_EOL;
                 Bt_doQuery($GLOBALS['db'], $deleteResQuery);

                if ($GLOBALS['extensionTable'] <> "") {
                    $deleteExtQuery = "DELETE FROM " . $GLOBALS['extensionTable']
                       . " WHERE res_id = " . $resourcesArray[$cptRes]["res_id"];
                    //echo $deleteExtQuery . PHP_EOL;
                    Bt_doQuery($GLOBALS['db'], $deleteExtQuery);
                }

                if ($GLOBALS['versionTable'] <> "") {
                    $deleteVersionQuery = "DELETE FROM " . $GLOBALS['versionTable']
                       . " WHERE res_id_master = " . $resourcesArray[$cptRes]["res_id"];
                    //echo $deleteVersionQuery . PHP_EOL;
                    Bt_doQuery($GLOBALS['db'], $deleteVersionQuery);
                }

                if ($GLOBALS['adrTable'] <> "") {
                    $deleteAdrQuery = "DELETE FROM " . $GLOBALS['adrTable']
                       . " WHERE res_id = " . $resourcesArray[$cptRes]["res_id"];
                    //echo $deleteAdrQuery . PHP_EOL;
                    Bt_doQuery($GLOBALS['db'], $deleteAdrQuery);
                }

                $deleteNotesQuery = "DELETE FROM notes "
                   . " WHERE coll_id = '" . $GLOBALS['collection'] . "' "
                   . " and identifier = '" . $resourcesArray[$cptRes]["res_id"] . "'";
                //echo $deleteNotesQuery . PHP_EOL;
                Bt_doQuery($GLOBALS['db'], $deleteNotesQuery);

                $deleteLinkedQuery = "DELETE FROM res_linked "
                   . " WHERE coll_id = '" . $GLOBALS['collection'] . "' "
                   . " and (res_child = '" . $resourcesArray[$cptRes]["res_id"] . "' or res_parent = '" . $resourcesArray[$cptRes]["res_id"] . "')";
                //echo $deleteLinkedQuery . PHP_EOL;
                Bt_doQuery($GLOBALS['db'], $deleteLinkedQuery);

                $deleteTagsQuery = "DELETE FROM tags "
                   . " WHERE coll_id = '" . $GLOBALS['collection'] . "' "
                   . " and res_id = '" . $resourcesArray[$cptRes]["res_id"] . "'";
                //echo $deleteTagsQuery . PHP_EOL;
                Bt_doQuery($GLOBALS['db'], $deleteTagsQuery);

                $deleteAttachmentsQuery = "DELETE FROM res_attachments "
                   . " WHERE coll_id = '" . $GLOBALS['collection'] . "' "
                   . " and res_id_master = '" . $resourcesArray[$cptRes]["res_id"] . "'";
                //echo $deleteAttachmentsQuery . PHP_EOL;
                Bt_doQuery($GLOBALS['db'], $deleteAttachmentsQuery);

                $deleteCasesQuery = "DELETE FROM cases_res "
                   . " WHERE res_id = '" . $resourcesArray[$cptRes]["res_id"] . "' ";
                //echo $deleteCasesQuery . PHP_EOL;
                Bt_doQuery($GLOBALS['db'], $deleteCasesQuery);

            }

            $repertoiredujour = date('Y-m-d-Hi');
            $chemin = $GLOBALS['exportFolder'].'DocsSupprimesEntites-'.$repertoiredujour.'.csv';
            $delimiteur = ";";

            $fichier_csv = fopen($chemin, 'w+');

            fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));

            foreach($arrayEntityId as $key => $value){
                if ($key > 0) {
                    $subEntities_tmp = array();
                    $subEntities = array();
                    $subEntities_tmp = getEntityChildrenTree($subEntities_tmp, $value);

                    for($iSubEntities=0;$iSubEntities<count($subEntities_tmp);$iSubEntities++){
                        if (in_array($subEntities_tmp[$iSubEntities]['ID'], $arrayEntityId)) {
                            array_push($subEntities, $subEntities_tmp[$iSubEntities]['ID']);
                        }
                    }
                    array_push($subEntities, $value);

                    $nbDocsSubEntities = 0;

                    foreach ($subEntities as $value2) {
                        $SubEntitiesKeys = array_search($value2, $arrayEntityId);
                        $nbDocsSubEntities = $nbDocsSubEntities + $arrayEntityNbDocs[$SubEntitiesKeys];
                    }

                    $queryEntityLabel = "SELECT entity_label FROM entities WHERE entity_id = '" . $value."'";
                    Bt_doQuery($GLOBALS['db2'], $queryEntityLabel);
                    $EntityDB = $GLOBALS['db2']->fetch_object();

                    fputcsv($fichier_csv, array($EntityDB->entity_label, $arrayEntityNbDocs[$key], $nbDocsSubEntities), $delimiteur);
                } else {
                    fputcsv($fichier_csv, array($value, $arrayEntityNbDocs[$key], $arraySubEntitiesNbDocs[$key]), $delimiteur);
                }
                
            }

            fclose($fichier_csv);

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
