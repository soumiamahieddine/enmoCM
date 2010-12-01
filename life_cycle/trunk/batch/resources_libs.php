<?php

function getSourceResourcePath($resId) {
	$query = "select res_id, docserver_id, path, filename from " . $GLOBALS['table'] . " where res_id = " . $resId;
	do_query($GLOBALS['db'], $query);
	$resRecordset = $GLOBALS['db']->fetch_object();
	$sourceFilePath = $resRecordset->path.$resRecordset->filename;
	if ($GLOBALS['docserverSourcePath'] == "") {
		getDocserverSourcePath($resId);
		$GLOBALS['logger']->write("Docserver source path:".$GLOBALS['docserverSourcePath'], 'INFO');
	}
	$sourceFilePath = $GLOBALS['docserverSourcePath'] . $sourceFilePath;
	$sourceFilePath = str_replace("#", DIRECTORY_SEPARATOR, $sourceFilePath);
	return $sourceFilePath;
}

function getDocserverSourcePath($resId) {
	$query = "select docserver_id from " . $GLOBALS['adrTable'] . " where res_id = " .$resId . " order by adr_priority";
	do_query($GLOBALS['db'], $query);
	if ($GLOBALS['db']->nb_result() == 0) {
		$query = "select docserver_id from " . $GLOBALS['table'] . " where res_id = " . $resId;
		do_query($GLOBALS['db'], $query);
		$recordset = $GLOBALS['db']->fetch_object();
	} else {
		$recordset = $GLOBALS['db']->fetch_object();
	}
	$query = "select path_template from " . _DOCSERVERS_TABLE_NAME . " where docserver_id = '" . $recordset->docserver_id."'";
	do_query($GLOBALS['db'], $query);
	$docserverRecordset = $GLOBALS['db']->fetch_object();
	$GLOBALS['docserverSourcePath'] = $docserverRecordset->path_template;
}

function updateDatabase($resId, $currentRecordInStack, $resInContainer, $path, $fileName) {
	if (is_array($resInContainer) && count($resInContainer) > 0) {
		for ($cptRes = 0;$cptRes<count($resInContainer);$cptRes++) {
			$query = "update " . _LC_STACK_TABLE_NAME ." set status = 'P' where policy_id = '" . $GLOBALS['policy'] . "' and cycle_id = '" . $GLOBALS['cycle'] . "' and cycle_step_id = '" . $GLOBALS['currentStep'] . "' and coll_id = '" . $GLOBALS['collection'] . "' and res_id = " . $resInContainer[$cptRes];
			do_query($GLOBALS['db'], $query);
			$query = "update " . $GLOBALS['table'] . " set cycle_id = '" . $GLOBALS['cycle'] . "', is_multi_docservers = 'Y' where res_id = " . $resInContainer[$cptRes];
			//echo $query."\r\n";
			do_query($GLOBALS['db'], $query);
			$query = "select * from " . $GLOBALS['adrTable'] . " where res_id = " .$resInContainer[$cptRes] . " order by adr_priority";
			do_query($GLOBALS['db'], $query);
			if ($GLOBALS['db']->nb_result() == 0) {
				$query = "select docserver_id, path, filename, offset_doc from " . $GLOBALS['table'] . " where res_id = " . $resInContainer[$cptRes];
				do_query($GLOBALS['db'], $query);
				$recordset = $GLOBALS['db']->fetch_object();
				$resDocserverId = $recordset->docserver_id;
				$resPath = $recordset->path;
				$resFilename = $recordset->filename;
				$resOffsetDoc = $recordset->offset_doc;
				$query = "select adr_priority_number from " . _DOCSERVERS_TABLE_NAME . " where docserver_id = '" . $resDocserverId . "'";
				do_query($GLOBALS['db'], $query);
				$recordset = $GLOBALS['db']->fetch_object();
				$query = "insert into " . $GLOBALS['adrTable'] . " (res_id, docserver_id, path, filename, offset_doc, adr_priority) values (" . $resInContainer[$cptRes] . ", '" . $resDocserverId . "', '" . $resPath . "', '" . $resFilename . "', '" .  $resOffsetDoc . "', " . $recordset->adr_priority_number . ")";
				do_query($GLOBALS['db'], $query);
			}
			$query = "insert into " . $GLOBALS['adrTable'] . " (res_id, docserver_id, path, filename, offset_doc, adr_priority) values (" . $resInContainer[$cptRes] . ", '" . $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['docserver_id'] . "', '" . $path . "', '" . $fileName . "', '" .  $offsetDoc . "', " . $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['adr_priority_number'] . ")";
			//echo $query."\r\n";exit;
			do_query($GLOBALS['db'], $query);
		}
	} else {
		$query = "update "._LC_STACK_TABLE_NAME." set status = 'P' where policy_id = '".$GLOBALS['policy']."' and cycle_id = '".$GLOBALS['cycle']."' and cycle_step_id = '".$GLOBALS['currentStep']."' and coll_id = '".$GLOBALS['collection']."' and res_id = ".$currentRecordInStack['res_id'];
		do_query($GLOBALS['db'], $query);
		$query = "update " . $GLOBALS['table'] . " set cycle_id = '" . $GLOBALS['cycle'] . "', is_multi_docservers = 'Y' where res_id = " . $currentRecordInStack['res_id'];
		do_query($GLOBALS['db'], $query);
		$query = "select * from " . $GLOBALS['adrTable'] . " where res_id = " .$currentRecordInStack['res_id'] . " order by adr_priority";
		do_query($GLOBALS['db'], $query);
		if ($GLOBALS['db']->nb_result() == 0) {
			$query = "select docserver_id, path, filename, offset_doc from " . $GLOBALS['table'] . " where res_id = " . $currentRecordInStack['res_id'];
			do_query($GLOBALS['db'], $query);
			$recordset = $GLOBALS['db']->fetch_object();
			$resDocserverId = $recordset->docserver_id;
			$resPath = $recordset->path;
			$resFilename = $recordset->filename;
			$resOffsetDoc = $recordset->offset_doc;
			$query = "select adr_priority_number from " . _DOCSERVERS_TABLE_NAME . " where docserver_id = '" . $resDocserverId . "'";
			do_query($GLOBALS['db'], $query);
			$recordset = $GLOBALS['db']->fetch_object();
			$query = "insert into " . $GLOBALS['adrTable'] . " (res_id, docserver_id, path, filename, offset_doc, adr_priority) values (" . $currentRecordInStack['res_id'] . ", '" . $resDocserverId . "', '" . $resPath . "', '" . $resFilename . "', '" .  $resOffsetDoc . "', " . $recordset->adr_priority_number . ")";
			do_query($GLOBALS['db'], $query);
		}
		$query = "insert into " . $GLOBALS['adrTable'] . " (res_id, docserver_id, path, filename, offset_doc, adr_priority) values (" . $currentRecordInStack['res_id'] . ", '" . $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['docserver_id'] . "', '" . $path . "', '" . $fileName . "', '" .  $offsetDoc . "', " . $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['adr_priority_number'] . ")";
		do_query($GLOBALS['db'], $query);
	}
}

?>
