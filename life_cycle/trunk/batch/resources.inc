<?php

function getSourceResourcePath($resId) {
	$query = "select res_id, docserver_id, path, filename from " . $GLOBALS['adrTable'] . " where res_id = " . $resId . " order by adr_priority";
	do_query($GLOBALS['db'], $query);
	if ($GLOBALS['db']->nb_result() == 0) {
		$query = "select res_id, docserver_id, path, filename from " . $GLOBALS['table'] . " where res_id = " . $resId;
		do_query($GLOBALS['db'], $query);
	}
	$resRecordset = $GLOBALS['db']->fetch_object();
	$sourceFilePath = $resRecordset->path . $resRecordset->filename;
	if ($GLOBALS['docserverSourcePath'] == "") {
		$query = "select path_template from " . _DOCSERVERS_TABLE_NAME . " where docserver_id = '" . $resRecordset->docserver_id . "'";
		do_query($GLOBALS['db'], $query);
		$docserverRecordset = $GLOBALS['db']->fetch_object();
		$GLOBALS['docserverSourcePath'] = $docserverRecordset->path_template;
		$GLOBALS['logger']->write("Docserver source path:" . $GLOBALS['docserverSourcePath'], 'INFO');
	}
	$sourceFilePath = $GLOBALS['docserverSourcePath'] . $sourceFilePath;
	$sourceFilePath = str_replace("#", DIRECTORY_SEPARATOR, $sourceFilePath);
	return $sourceFilePath;
}

function updateDatabase($currentRecordInStack, $resInContainer, $path, $fileName, $offsetDoc) {
	if (is_array($resInContainer) && count($resInContainer) > 0) {
		for ($cptRes=0;$cptRes<count($resInContainer);$cptRes++) {
			do_update_db($resInContainer[$cptRes]['res_id'], $path, $fileName, $resInContainer[$cptRes]['offset_doc']);
		}
	} else {
		do_update_db($currentRecordInStack['res_id'], $path, $fileName, $offsetDoc);
	}
}

function do_update_db($resId, $path, $fileName, $offsetDoc) {
	$query = "update " . _LC_STACK_TABLE_NAME . " set status = 'P' where policy_id = '" . $GLOBALS['policy'] . "' and cycle_id = '" . $GLOBALS['cycle'] . "' and cycle_step_id = '" . $GLOBALS['currentStep'] . "' and coll_id = '" . $GLOBALS['collection'] . "' and res_id = " . $resId;
	do_query($GLOBALS['db'], $query);
	$query = "update " . $GLOBALS['table'] . " set cycle_id = '" . $GLOBALS['cycle'] . "', is_multi_docservers = 'Y' where res_id = " . $resId;
	do_query($GLOBALS['db'], $query);
	$query = "select * from " . $GLOBALS['adrTable'] . " where res_id = " . $resId . " order by adr_priority";
	do_query($GLOBALS['db'], $query);
	if ($GLOBALS['db']->nb_result() == 0) {
		$query = "select docserver_id, path, filename, offset_doc from " . $GLOBALS['table'] . " where res_id = " . $resId;
		do_query($GLOBALS['db'], $query);
		$recordset = $GLOBALS['db']->fetch_object();
		$resDocserverId = $recordset->docserver_id;
		$resPath = $recordset->path;
		$resFilename = $recordset->filename;
		$resOffsetDoc = $recordset->offset_doc;
		$query = "select adr_priority_number from " . _DOCSERVERS_TABLE_NAME . " where docserver_id = '" . $resDocserverId . "'";
		do_query($GLOBALS['db'], $query);
		$recordset = $GLOBALS['db']->fetch_object();
		$query = "insert into " . $GLOBALS['adrTable'] . " (res_id, docserver_id, path, filename, offset_doc, adr_priority) values (" . $resId . ", '" . $resDocserverId . "', '" . $resPath . "', '" . $resFilename . "', '" .  $resOffsetDoc . "', " . $recordset->adr_priority_number . ")";
		do_query($GLOBALS['db'], $query);
	}
	$query = "insert into " . $GLOBALS['adrTable'] . " (res_id, docserver_id, path, filename, offset_doc, adr_priority) values (" . $resId . ", '" . $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['docserver_id'] . "', '" . $path . "', '" . $fileName . "', '" .  $offsetDoc . "', " . $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['adr_priority_number'] . ")";
	do_query($GLOBALS['db'], $query);
	//history...
}

?>
