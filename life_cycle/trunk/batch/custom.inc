<?php

function createPDIHistory($resInContainer) {
	$tmpXML = $GLOBALS['MaarchDirectory'] . "modules" . DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "pdi_history.xml";
	$docXML = new DomDocument("1.0", "utf-8");
	$docXML->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	//root
	$root = $docXML->createElement("ROOT");
	$docXML->appendChild($root);
	$CommentString = _HISTORY_COMMENT_ROOT;
	$CommentNodeRoot = $docXML->createComment($CommentString);
	$root->appendChild($CommentNodeRoot);
	for ($cptRes=0;$cptRes<count($resInContainer);$cptRes++) {
		//a record
		$pdiHistory = $docXML->createElement("PDI_HISTORY");
		$pdiHistory->setAttributeNode(new DOMAttr('AIU', $resInContainer[$cptRes]['offset_doc']));
		$pdiHistory->setAttributeNode(new DOMAttr('RES_ID', $resInContainer[$cptRes]['res_id']));
		$root->appendChild($pdiHistory);
		$query = "select * from history where record_id = '".$resInContainer[$cptRes]['res_id']."' and (table_name = '".$GLOBALS['table']."' or table_name = '".$GLOBALS['adrTable']."' or table_name = '".$GLOBALS['view']."') order by event_date";
		do_query($GLOBALS['db3'], $query);
		while ($historyRecordset = $GLOBALS['db3']->fetch_object()) {
			//an event
			$event = $docXML->createElement("EVENT");
			$pdiHistory->appendChild($event);
			$eventType = $docXML->createElement("EVENT_TYPE", $historyRecordset->event_type);
			$event->appendChild($eventType);
			$eventType->setAttributeNode(new DOMAttr('SOURCE', 'HIST'));
			$userId = $docXML->createElement("USER_ID", $historyRecordset->user_id);
			$event->appendChild($userId);
			$userId->setAttributeNode(new DOMAttr('SOURCE', 'HIST'));
			$eventDate = $docXML->createElement("EVENT_DATE", $historyRecordset->event_date);
			$event->appendChild($eventDate);
			$eventDate->setAttributeNode(new DOMAttr('SOURCE', 'HIST'));
			$idModule = $docXML->createElement("ID_MODULE", $historyRecordset->id_module);
			$event->appendChild($idModule);
			$idModule->setAttributeNode(new DOMAttr('SOURCE', 'HIST'));
			$info = $docXML->createElement("INFO", $GLOBALS['func']->wash_html($historyRecordset->info, "NO_ACCENT"));
			$event->appendChild($info);
			$info->setAttributeNode(new DOMAttr('SOURCE', 'HIST'));
		}
	}
	//save the xml
	$docXML->save($tmpXML);
}

function createPackagingInformation($PIArray) {
	$tmpXML = $GLOBALS['MaarchDirectory'] . "modules" . DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "packaging_information.xml";
	$docXML = new DomDocument("1.0", "utf-8");
	$docXML->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	//root
	$root = $docXML->createElement("ROOT");
	$docXML->appendChild($root);
	$CommentString = _PI_COMMENT_ROOT;
	$CommentNodeRoot = $docXML->createComment($CommentString);
	$root->appendChild($CommentNodeRoot);
	//general
	$general = $docXML->createElement("GENERAL");
	$root->appendChild($general);
	$fingerprint = $docXML->createElement("FINGERPRINT", $PIArray['CIFingerprint']);
	$general->appendChild($fingerprint);
	$CommentString = _PI_COMMENT_FINGERPRINT;
	$CommentNodeFingerprint = $docXML->createComment($CommentString);
	$fingerprint->appendChild($CommentNodeFingerprint);
	$fingerprintMode = $docXML->createElement("FINGERPRINT_MODE", $PIArray['fingerprintMode']);
	$general->appendChild($fingerprintMode);
	$aiuCount = $docXML->createElement("AIU_COUNT", $PIArray['aiuCount']);
	$general->appendChild($aiuCount);
	$CommentString = _PI_COMMENT_AIU;
	$CommentNodeAiuCount = $docXML->createComment($CommentString);
	$aiuCount->appendChild($CommentNodeAiuCount);
	//content
	$content = $docXML->createElement("CONTENT");
	$root->appendChild($content);
	$CommentString = _PI_COMMENT_CONTENT;
	$CommentNodeContent = $docXML->createComment($CommentString);
	$content->appendChild($CommentNodeContent);
	$contentFile = $docXML->createElement("CONTENT_FILE", $PIArray['ciName']);
	$content->appendChild($contentFile);
	$compressionMode = $docXML->createElement("COMPRESSION_MODE", $PIArray['compressionModeCI']);
	$content->appendChild($compressionMode);
	//pdi
	$pdi = $docXML->createElement("PDI");
	$root->appendChild($pdi);
	$CommentString = _PI_COMMENT_PDI;
	$CommentNodePdi = $docXML->createComment($CommentString);
	$pdi->appendChild($CommentNodePdi);
	$pdiFile = $docXML->createElement("PDI_FILE", $PIArray['pdiName']);
	$pdi->appendChild($pdiFile);
	$historyFile = $docXML->createElement("HISTORY_FILE", $PIArray['pdiHistoryName']);
	$pdi->appendChild($historyFile);
	$compressionModeHistory = $docXML->createElement("COMPRESSION_MODE", $PIArray['compressionModeHistory']);
	$pdi->appendChild($compressionModeHistory);
	//save the xml
	$docXML->save($tmpXML);
}

function createPDI($resInContainer) {
	$tmpDir = $GLOBALS['MaarchDirectory'] . "modules" . DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp";
	$sourceTemplatePI = $GLOBALS['MaarchDirectory'] . "modules" .DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "xml_templates" . DIRECTORY_SEPARATOR . "pdi.xml";
	if (file_exists($sourceTemplatePI)) {
		$xml = simplexml_load_file($sourceTemplatePI);
		if ($xml == false) {
			$returnInfos = array('error'=>'Error on loading xml file:' . $templatePI);
			return $returnInfos;
		}
		$tmpXML = $GLOBALS['MaarchDirectory'] . "modules" . DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "pdi.xml";
		$docXML = new DomDocument("1.0", "utf-8");
		$docXML->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		//root
		$root = $docXML->createElement("ROOT");
		$docXML->appendChild($root);
		$CommentString = _PDI_COMMENT_ROOT;
		$CommentNodeRoot = $docXML->createComment($CommentString);
		$root->appendChild($CommentNodeRoot);
		//access rights
		$accessRights = $docXML->createElement("ACCESS_RIGHTS");
		$root->appendChild($accessRights);
		$query = "select * from security where coll_id = '".$GLOBALS['collection']."'";
		do_query($GLOBALS['db3'], $query);
		while ($securityRecordset = $GLOBALS['db3']->fetch_object()) {
			//an access right
			$AccessRight = $docXML->createElement("ACCESS_RIGHT");
			$accessRights->appendChild($AccessRight);
			$group = $docXML->createElement("GROUP", $securityRecordset->group_id);
			$AccessRight->appendChild($group);
			$collection = $docXML->createElement("COLLECTION", $securityRecordset->coll_id);
			$AccessRight->appendChild($collection);
			$whereClause = $docXML->createElement("WHERE_CLAUSE", $securityRecordset->where_clause);
			$AccessRight->appendChild($whereClause);
			$comment = $docXML->createElement("COMMENT", $securityRecordset->comment);
			$AccessRight->appendChild($comment);
			$canInsert = $docXML->createElement("CAN_INSERT", $securityRecordset->can_insert);
			$AccessRight->appendChild($canInsert);
			$canUpdate = $docXML->createElement("CAN_UPDATE", $securityRecordset->can_update);
			$AccessRight->appendChild($canUpdate);
			$canDelete = $docXML->createElement("CAN_DELETE", $securityRecordset->can_delete);
			$AccessRight->appendChild($canDelete);
		}
		for ($cptRes=0;$cptRes<count($resInContainer);$cptRes++) {
			//a record
			$pdi = $docXML->createElement("PDI");
			$root->appendChild($pdi);
			$pdi->setAttributeNode(new DOMAttr('AIU', $resInContainer[$cptRes]['offset_doc']));
			$pdi->setAttributeNode(new DOMAttr('RES_ID', $resInContainer[$cptRes]['res_id']));
			$query = "select * from " . $GLOBALS['view'] . " where res_id = ".$resInContainer[$cptRes]['res_id'];
			do_query($GLOBALS['db3'], $query);
			while ($resRecordset = $GLOBALS['db3']->fetch_object()) {
				//a record
				//a provenance
				$provenance = $docXML->createElement("PROVENANCE");
				$pdi->appendChild($provenance);
				$publisher = $docXML->createElement("PUBLISHER", $resRecordset->publisher);
				$publisher->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($publisher);
				$contributor = $docXML->createElement("CONTRIBUTOR", $resRecordset->contributor);
				$contributor->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($contributor);
				$contributor = $docXML->createElement("CONTRIBUTOR", $resRecordset->contributor);
				$contributor->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($contributor);
				$typist = $docXML->createElement("TYPIST", $resRecordset->typist);
				$typist->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($typist);
				$publisher = $docXML->createElement("PUBLISHER", $resRecordset->publisher);
				$publisher->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($publisher);
				$author = $docXML->createElement("AUTHOR", $resRecordset->author);
				$author->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($author);
				$source = $docXML->createElement("SOURCE", $resRecordset->source);
				$source->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($source);
				$scan_date = $docXML->createElement("SCAN_DATE", $resRecordset->scan_date);
				$scan_date->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($scan_date);
				$scan_user = $docXML->createElement("SCAN_USER", $resRecordset->scan_user);
				$scan_user->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($scan_user);
				$scan_location = $docXML->createElement("SCAN_LOCATION", $resRecordset->scan_location);
				$scan_location->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($scan_location);
				$scan_wkstation = $docXML->createElement("SCAN_WKSTATION", $resRecordset->scan_wkstation);
				$scan_wkstation->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($scan_wkstation);
				$scan_batch = $docXML->createElement("SCAN_BATCH", $resRecordset->scan_batch);
				$scan_batch->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($scan_batch);
				$envelop_id = $docXML->createElement("ENVELOP_ID", $resRecordset->envelop_id);
				$envelop_id->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($envelop_id);
				$origin = $docXML->createElement("ORIGIN", $resRecordset->origin);
				$origin->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($origin);
				$is_ingoing = $docXML->createElement("IS_INGOING", $resRecordset->is_ingoing);
				$is_ingoing->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($is_ingoing);
				$history = $docXML->createElement("HISTORY", $resRecordset->history);
				$history->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($history);
				$CommentString = _PDI_COMMENT_HISTORY;
				$CommentNodeHistory = $docXML->createComment($CommentString);
				$history->appendChild($CommentNodeHistory);
				//a reference
				$reference = $docXML->createElement("REFERENCE");
				$pdi->appendChild($reference);
				$title = $docXML->createElement("TITLE", $resRecordset->title);
				$title->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$title->setAttributeNode(new DOMAttr('LABEL', _LIBTRAIT));
				$reference->appendChild($title);
				$subject = $docXML->createElement("SUBJECT", $resRecordset->subject);
				$subject->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$subject->setAttributeNode(new DOMAttr('LABEL', _LIBCPT));
				$reference->appendChild($subject);
				$description = $docXML->createElement("DESCRIPTION", $resRecordset->description);
				$description->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$reference->appendChild($description);
				$identifier = $docXML->createElement("IDENTIFIER", $resRecordset->identifier);
				$identifier->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$reference->appendChild($identifier);
				$coverage = $docXML->createElement("COVERAGE", $resRecordset->coverage);
				$coverage->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$reference->appendChild($coverage);
				$doc_date = $docXML->createElement("DOC_DATE", $resRecordset->doc_date);
				$doc_date->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$reference->appendChild($doc_date);
				$type_id = $docXML->createElement("TYPE_ID", $resRecordset->type_id);
				$type_id->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$reference->appendChild($type_id);
				$type_label = $docXML->createElement("TYPE_LABEL", $resRecordset->type_label);
				$type_label->setAttributeNode(new DOMAttr('SOURCE', 'VIEW'));
				$reference->appendChild($type_label);
				//customs
				$custom_t1 = $docXML->createElement("CUSTOM_T1", $resRecordset->doc_custom_t1);
				$custom_t1->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t1->setAttributeNode(new DOMAttr('LABEL', _NUMCPT));
				$reference->appendChild($custom_t1);
				$custom_n1 = $docXML->createElement("CUSTOM_N1", $resRecordset->doc_custom_n1);
				$custom_n1->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_n1->setAttributeNode(new DOMAttr('LABEL', _REFCLI));
				$reference->appendChild($custom_n1);
				$custom_t2 = $docXML->createElement("CUSTOM_T2", $resRecordset->doc_custom_t2);
				$custom_t2->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t2->setAttributeNode(new DOMAttr('LABEL', _LIBMDT));
				$reference->appendChild($custom_t2);
				$custom_n2 = $docXML->createElement("CUSTOM_N2", $resRecordset->doc_custom_n2);
				$custom_n2->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_n2->setAttributeNode(new DOMAttr('LABEL', _NUMSOC));
				$reference->appendChild($custom_n2);
				$custom_t3 = $docXML->createElement("CUSTOM_T3", $resRecordset->doc_custom_t3);
				$custom_t3->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t3->setAttributeNode(new DOMAttr('LABEL', _LIBCPT));
				$reference->appendChild($custom_t3);
				$custom_t4 = $docXML->createElement("CUSTOM_T4", $resRecordset->doc_custom_t4);
				$custom_t4->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t4->setAttributeNode(new DOMAttr('LABEL', _LIBIMM));
				$reference->appendChild($custom_t4);
				$custom_n4 = $docXML->createElement("CUSTOM_N4", $resRecordset->doc_custom_n4);
				$custom_n4->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_n4->setAttributeNode(new DOMAttr('LABEL', _NUMIMM));
				$reference->appendChild($custom_n4);
				$custom_n5 = $docXML->createElement("CUSTOM_N5", $resRecordset->doc_custom_n5);
				$custom_n5->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_n5->setAttributeNode(new DOMAttr('LABEL', _NUMLOT));
				$reference->appendChild($custom_n5);
				$custom_t6 = $docXML->createElement("CUSTOM_T6", $resRecordset->doc_custom_t6);
				$custom_t6->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t6->setAttributeNode(new DOMAttr('LABEL', _MODEREGLT));
				$reference->appendChild($custom_t6);
				$custom_n6 = $docXML->createElement("CUSTOM_N6", $resRecordset->doc_custom_n6);
				$custom_n6->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_n6->setAttributeNode(new DOMAttr('LABEL', _ANNEEMOIS));
				$reference->appendChild($custom_n6);
				$custom_t7 = $docXML->createElement("CUSTOM_T7", $resRecordset->doc_custom_t7);
				$custom_t7->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t7->setAttributeNode(new DOMAttr('LABEL', _DESTINATAIRE));
				$reference->appendChild($custom_t7);
				$custom_n7 = $docXML->createElement("CUSTOM_N7", $resRecordset->doc_custom_n7);
				$custom_n7->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_n7->setAttributeNode(new DOMAttr('LABEL', _NUMDOSTRV));
				$reference->appendChild($custom_n7);
				$custom_t8 = $docXML->createElement("CUSTOM_T8", $resRecordset->doc_custom_t8);
				$custom_t8->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t8->setAttributeNode(new DOMAttr('LABEL', _COURRIEL));
				$reference->appendChild($custom_t8);
				$custom_t9 = $docXML->createElement("CUSTOM_T9", $resRecordset->doc_custom_t9);
				$custom_t9->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t9->setAttributeNode(new DOMAttr('LABEL', _MANDATAIRE));
				$reference->appendChild($custom_t9);
				$custom_t10 = $docXML->createElement("CUSTOM_T10", $resRecordset->doc_custom_t10);
				$custom_t10->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t10->setAttributeNode(new DOMAttr('LABEL', _TYPDOS));
				$reference->appendChild($custom_t10);
				//a fixity
				$fixity = $docXML->createElement("FIXITY");
				$pdi->appendChild($fixity);
				$fingerprint = $docXML->createElement("FINGERPRINT", $resRecordset->fingerprint);
				$fingerprint->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$fixity->appendChild($fingerprint);
				$scan_postmark = $docXML->createElement("SCAN_POSTMARK", $resRecordset->scan_postmark);
				$scan_postmark->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$fixity->appendChild($scan_postmark);
				$filesize = $docXML->createElement("FILESIZE", $resRecordset->filesize);
				$filesize->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$fixity->appendChild($filesize);
				$page_count = $docXML->createElement("PAGE_COUNT", $resRecordset->page_count);
				$page_count->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$fixity->appendChild($page_count);
				//a context
				$context = $docXML->createElement("CONTEXT");
				$pdi->appendChild($context);
				$res_id = $docXML->createElement("RES_ID", $resRecordset->res_id);
				$res_id->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$context->appendChild($res_id);
				$collection = $docXML->createElement("COLLECTION", $GLOBALS['collection']);
				$collection->setAttributeNode(new DOMAttr('SOURCE', 'LIFECYCLE'));
				$context->appendChild($collection);
				$fingerprint_mode = $docXML->createElement("FINGERPRINT_MODE", "md5");
				$fingerprint_mode->setAttributeNode(new DOMAttr('SOURCE', 'LIFECYCLE'));
				$context->appendChild($fingerprint_mode);
				$format = $docXML->createElement("FORMAT", $resRecordset->format);
				$format->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$context->appendChild($format);
				$creation_date = $docXML->createElement("CREATION_DATE", $resRecordset->creation_date);
				$creation_date->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$context->appendChild($creation_date);
				$doc_language = $docXML->createElement("DOC_LANGUAGE", $resRecordset->doc_language);
				$doc_language->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$context->appendChild($doc_language);
				$relation = $docXML->createElement("RELATION", $resRecordset->relation);
				$relation->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$context->appendChild($relation);
				$coverage = $docXML->createElement("COVERAGE", $resRecordset->coverage);
				$coverage->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$context->appendChild($coverage);
				$rights = $docXML->createElement("RIGHTS", $resRecordset->rights);
				$rights->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$context->appendChild($rights);
			}
		}
		//save the xml
		$docXML->save($tmpXML);
	}
}

?>
