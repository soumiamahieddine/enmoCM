<?php

function getSourceResourcePath(
    $resId, 
    $docserverToPurge = '', 
    $returnArray = false
) 
{
    $pathArray = array();
    if ($docserverToPurge <> '') {
        $query = "select res_id, docserver_id, path, filename, offset_doc "
               . "from " . $GLOBALS['adrTable'] . " where res_id = " . $resId 
               . " and docserver_id = '" . $docserverToPurge . "'";
    } else {
        $query = "select res_id, docserver_id, path, filename, offset_doc "
               . "from " . $GLOBALS['adrTable'] . " where res_id = " 
               . $resId . " order by adr_priority";
    }
    do_query($GLOBALS['db'], $query);
    if ($GLOBALS['db']->nb_result() == 0) {
        if ($docserverToPurge <> '') {
            $query = "select res_id, docserver_id, path, filename, offset_doc "
                   . "from " . $GLOBALS['table'] . " where res_id = " 
                   . $resId . " and docserver_id = '" . $docserverToPurge 
                   . "'";
        } else {
            $query = "select res_id, docserver_id, path, filename, offset_doc"
                   . " from " . $GLOBALS['table'] . " where res_id = " . $resId;
        }
        do_query($GLOBALS['db'], $query);
    }
    $resRecordset = $GLOBALS['db']->fetch_object();
    $resPath = '';
    $resFilename = '';
    $resDocserverId = '';
    $resDocserverTypeId = '';
    $resFingerprintMode = '';
    if (isset($resRecordset->path)) {
        $resPath = $resRecordset->path;
    }
    if (isset($resRecordset->filename)) {
        $resFilename = $resRecordset->filename;
    }
    if (isset($resRecordset->docserver_id)) {
        $resDocserverId = $resRecordset->docserver_id;
    }
    if (isset($resRecordset->offset_doc) && $resRecordset->offset_doc <> '') {
        //purge a container
        if (
            $docserverToPurge <> '' 
            && $GLOBALS['docservers'][$GLOBALS['currentStep']]['is_container'] 
            == 't'
        ) {
            $sourceFilePath = $resRecordset->path . $resRecordset->filename;
        } else {
            $sourceFilePath = $resRecordset->path . $resRecordset->filename 
                            . DIRECTORY_SEPARATOR . $resRecordset->offset_doc;
        }
        array_push(
            $pathArray, 
            array(
                'docserverId' => $docserverToPurge, 
                'basePath' => $resRecordset->path, 
                'fileName' => $resRecordset->filename, 
                'offsetDoc' => $resRecordset->offset_doc
            )
        );
    } else {
        $sourceFilePath = $resPath . $resFilename;
        array_push(
            $pathArray, 
            array(
                'docserverId' => $docserverToPurge, 
                'basePath' => $resPath, 
                'fileName' => $resFilename
            )
        );
    }
    if ($GLOBALS['docserverSourcePath'] == '') {
        $query = "select path_template, docserver_type_id from " 
               . _DOCSERVERS_TABLE_NAME . " where docserver_id = '" 
               . $resDocserverId . "'";
        do_query($GLOBALS['db'], $query);
        $docserverRecordset = $GLOBALS['db']->fetch_object();
        if (isset($docserverRecordset->docserver_type_id)) {
            $resDocserverTypeId = $docserverRecordset->docserver_type_id;
        }
        $resPathTemplate = '';
        if (isset($docserverRecordset->path_template)) {
            $resPathTemplate = $docserverRecordset->path_template;
        }
        $GLOBALS['docserverSourcePath'] = $resPathTemplate;
        $GLOBALS['logger']->write(
            'Docserver source path:' . $GLOBALS['docserverSourcePath'], 'DEBUG'
        );
        $query = "select fingerprint_mode from " 
               . _DOCSERVER_TYPES_TABLE_NAME . " where docserver_type_id = '" 
               . $resDocserverTypeId . "'";
        do_query($GLOBALS['db'], $query);
        $docserverTypeRecordset = $GLOBALS['db']->fetch_object();
        if (isset($docserverTypeRecordset->fingerprint_mode)) {
            $resFingerprintMode = $docserverTypeRecordset->fingerprint_mode;
        }
        $GLOBALS['docserverSourceFingerprint'] = $resFingerprintMode;
        $GLOBALS['logger']->write(
            'Docserver source fingerprint:' 
            . $GLOBALS['docserverSourceFingerprint'], 'DEBUG'
        );
    }
    $sourceFilePath = $GLOBALS['docserverSourcePath'] . $sourceFilePath;
    $sourceFilePath = str_replace('#', DIRECTORY_SEPARATOR, $sourceFilePath);
    if ($returnArray) {
        return $pathArray;
    } else {
        return $sourceFilePath;
    }
}

function updateDatabase(
    $currentRecordInStack, 
    $resInContainer, 
    $path, 
    $fileName, 
    $offsetDoc = ''
)
{
    do_query($GLOBALS['db'], 'START TRANSACTION');
    if (is_array($resInContainer) && count($resInContainer) > 0) {
        for ($cptRes=0;$cptRes<count($resInContainer);$cptRes++) {
            do_update_db(
                $resInContainer[$cptRes]['res_id'], 
                $path, $fileName, $resInContainer[$cptRes]['offset_doc'], 
                $resInContainer[$cptRes]['fingerprint']
            );
        }
    } else {
        do_update_db(
            $currentRecordInStack['res_id'], $path, $fileName, $offsetDoc, 
            $currentRecordInStack['fingerprint']
        );
    }
    do_query($GLOBALS['db'], 'COMMIT');
}

function do_update_db($resId, $path, $fileName, $offsetDoc, $fingerprint) 
{
    $query = "update " . _LC_STACK_TABLE_NAME . " set status = 'P' where"
           . " policy_id = '" . $GLOBALS['policy'] . "' and cycle_id = '" 
           . $GLOBALS['cycle'] . "' and cycle_step_id = '" 
           . $GLOBALS['currentStep'] . "' and coll_id = '" 
           . $GLOBALS['collection'] . "' and res_id = " . $resId;
    do_query($GLOBALS['db'], $query, true);
    $query = "update " . $GLOBALS['table'] . " set cycle_id = '" 
           . $GLOBALS['cycle'] . "', is_multi_docservers = 'Y' where"
           . " res_id = " . $resId;
    do_query($GLOBALS['db'], $query, true);
    $query = "select * from " . $GLOBALS['adrTable'] . " where res_id = " 
           . $resId . " order by adr_priority";
    do_query($GLOBALS['db'], $query);
    if ($GLOBALS['db']->nb_result() == 0) {
        $query = "select docserver_id, path, filename, offset_doc, fingerprint"
               . " from " . $GLOBALS['table'] . " where res_id = " . $resId;
        do_query($GLOBALS['db'], $query);
        $recordset = $GLOBALS['db']->fetch_object();
        $resDocserverId = $recordset->docserver_id;
        $resPath = $recordset->path;
        $resFilename = $recordset->filename;
        $resOffsetDoc = $recordset->offset_doc;
        $fingerprintInit = $recordset->fingerprint;
        $query = "select adr_priority_number from " . _DOCSERVERS_TABLE_NAME 
               . " where docserver_id = '" . $resDocserverId . "'";
        do_query($GLOBALS['db'], $query);
        $recordset = $GLOBALS['db']->fetch_object();
        $query = "insert into " . $GLOBALS['adrTable'] . " (res_id, "
               . "docserver_id, path, filename, offset_doc, fingerprint, "
               . "adr_priority) values (" . $resId . ", '" . $resDocserverId 
               . "', '" . $resPath . "', '" . $resFilename . "', '" 
               .  $resOffsetDoc . "', '" .  $fingerprintInit . "', " 
               . $recordset->adr_priority_number . ")";
        do_query($GLOBALS['db'], $query, true);
    }
    $query = "insert into " . $GLOBALS['adrTable'] . " (res_id, docserver_id, "
           . "path, filename, offset_doc, fingerprint, adr_priority) values (" 
           . $resId . ", '" . $GLOBALS['docservers'][$GLOBALS['currentStep']]
           ['docserver']['docserver_id'] . "', '" . $path . "', '" . $fileName 
           . "', '" .  $offsetDoc . "', '" .  $fingerprint . "', " 
           . $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']
           ['adr_priority_number'] . ")";
    do_query($GLOBALS['db'], $query, true);
    $query = "insert into " . HISTORY_TABLE . " (table_name, record_id, "
           . "event_type, user_id, event_date, info, id_module) values ('" 
           . $GLOBALS['table'] . "', '" . $resId . "', 'ADD', 'LC_BOT', '" 
           . date("d") . "/" . date("m") . "/" . date("Y") . " " . date("H") 
           . ":" . date("i") . ":" . date("s") . "', 'process stack, policy:" 
           . $GLOBALS['policy'] . ", cycle:" . $GLOBALS['cycle'] 
           . ", cycle step:" . $GLOBALS['currentStep'] . ", collection:" 
           . $GLOBALS['collection'] . "', 'life_cycle')";
    do_query($GLOBALS['db'], $query, true);
}

function deleteAdrx($resId, $dsToUpdate) 
{
    do_query($GLOBALS['db'], 'START TRANSACTION');
    $query = "update " . _LC_STACK_TABLE_NAME . " set status = 'P' where "
           . "policy_id = '" . $GLOBALS['policy'] . "' and cycle_id = '" 
           . $GLOBALS['cycle'] . "' and cycle_step_id = '" 
           . $GLOBALS['currentStep'] . "' and coll_id = '" 
           . $GLOBALS['collection'] . "' and res_id = " . $resId;
    do_query($GLOBALS['db'], $query, true);
    $query = "update " . $GLOBALS['table'] . " set cycle_id = '" 
           . $GLOBALS['cycle'] . "' where res_id = " . $resId;
    do_query($GLOBALS['db'], $query, true);
    $docserverSizeToUpdate = 0;
    for ($cptDs=0;$cptDs<count($dsToUpdate);$cptDs++) {
        $query = "delete from " . $GLOBALS['adrTable'] . " where res_id = " 
               . $resId . " and docserver_id = '" 
               . $dsToUpdate[$cptDs]['docserverId'] . "'";
        do_query($GLOBALS['db'], $query, true);
    }
    $query = "insert into " . HISTORY_TABLE . " (table_name, record_id, "
           . "event_type, user_id, event_date, info, id_module) values ('" 
           . $GLOBALS['table'] . "', '" . $resId . "', 'ADD', 'LC_BOT', '" 
           . date("d") . "/" . date("m") . "/" . date("Y") . " " . date("H") 
           . ":" . date("i") . ":" . date("s") . "', 'process stack, policy:" 
           . $GLOBALS['policy'] . ", cycle:" . $GLOBALS['cycle'] 
           . ", cycle step:" . $GLOBALS['currentStep'] . ", collection:" 
           . $GLOBALS['collection'] . "', 'life_cycle')";
    do_query($GLOBALS['db'], $query, true);
    do_query($GLOBALS['db'], 'COMMIT');
}

function updateOnNonePurge($resId) 
{
    do_query($GLOBALS['db'], 'START TRANSACTION');
    $query = "update " . _LC_STACK_TABLE_NAME 
           . " set status = 'P' where policy_id = '" . $GLOBALS['policy'] 
           . "' and cycle_id = '" . $GLOBALS['cycle'] 
           . "' and cycle_step_id = '" . $GLOBALS['currentStep'] 
           . "' and coll_id = '" . $GLOBALS['collection'] . "' and res_id = " 
           . $resId;
    do_query($GLOBALS['db'], $query, true);
    $query = "update " . $GLOBALS['table'] . " set cycle_id = '" 
           . $GLOBALS['cycle'] . "' where res_id = " . $resId;
    do_query($GLOBALS['db'], $query, true);
    $query = "insert into " . HISTORY_TABLE . " (table_name, record_id, "
           . "event_type, user_id, event_date, info, id_module) values ('" 
           . $GLOBALS['table'] . "', '" . $resId . "', 'ADD', 'LC_BOT', '" 
           . date("d") . "/" . date("m") . "/" . date("Y") . " " . date("H") 
           . ":" . date("i") . ":" . date("s") . "', 'process stack, policy:" 
           . $GLOBALS['policy'] . ", cycle:" . $GLOBALS['cycle'] 
           . ", cycle step:" . $GLOBALS['currentStep'] . ", collection:" 
           . $GLOBALS['collection'] . ", No purge for the resource " . $resId 
           . " because this is the last adr available', 'life_cycle')";
    do_query($GLOBALS['db'], $query, true);
    do_query($GLOBALS['db'], 'COMMIT');
}
