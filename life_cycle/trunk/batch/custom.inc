<?php

function createPDI($resInContainer) {
	$sourceTemplatePI = $GLOBALS['MaarchDirectory'] . "modules" .DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "xml_templates" . DIRECTORY_SEPARATOR . "pdi.xml";
	if (file_exists($sourceTemplatePI)) {
		$xml = simplexml_load_file($sourceTemplatePI);
		if ($xml == false) {
			$returnInfos = array('error'=>'Error on loading xml file:' . $templatePI);
			return $returnInfos;
		}
		$tmpXML = $GLOBALS['TmpDirectory'] . DIRECTORY_SEPARATOR . "pdi.xml";
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
				$title = $docXML->createElement("TITLE", htmlentities($resRecordset->title));
				$title->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$title->setAttributeNode(new DOMAttr('LABEL', _LIBTRT));
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
				$custom_t2 = $docXML->createElement("CUSTOM_T2", $resRecordset->doc_custom_t2);
				$custom_t2->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t2->setAttributeNode(new DOMAttr('LABEL', _LIBMDT));
				$reference->appendChild($custom_t2);
				$custom_t3 = $docXML->createElement("CUSTOM_T3", $resRecordset->doc_custom_t3);
				$custom_t3->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t3->setAttributeNode(new DOMAttr('LABEL', _TYPTRAIT));
				$reference->appendChild($custom_t3);
				$custom_t4 = $docXML->createElement("CUSTOM_T4", $resRecordset->doc_custom_t4);
				$custom_t4->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t4->setAttributeNode(new DOMAttr('LABEL', _LIBIMM));
				$reference->appendChild($custom_t4);
				$custom_t5 = $docXML->createElement("CUSTOM_T5", $resRecordset->custom_t5);
				$custom_t5->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t5->setAttributeNode(new DOMAttr('LABEL', _NUMDOSTRV));
				$reference->appendChild($custom_t5);
				$custom_t6 = $docXML->createElement("CUSTOM_T6", $resRecordset->doc_custom_t6);
				$custom_t6->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t6->setAttributeNode(new DOMAttr('LABEL', _MODEREGLT));
				$reference->appendChild($custom_t6);
				$custom_t7 = $docXML->createElement("CUSTOM_T7", $resRecordset->doc_custom_t7);
				$custom_t7->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t7->setAttributeNode(new DOMAttr('LABEL', _DESTINATAIRE));
				$reference->appendChild($custom_t7);
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
				$custom_t11 = $docXML->createElement("CUSTOM_T11", $resRecordset->doc_custom_t11);
				$custom_t11->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t11->setAttributeNode(new DOMAttr('LABEL', _REFCLI));
				$reference->appendChild($custom_t11);
				$custom_t12 = $docXML->createElement("CUSTOM_T12", $resRecordset->doc_custom_t12);
				$custom_t12->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t12->setAttributeNode(new DOMAttr('LABEL', _NUMSOC));
				$reference->appendChild($custom_t12);
				$custom_t13 = $docXML->createElement("CUSTOM_T13", $resRecordset->doc_custom_t13);
				$custom_t13->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t13->setAttributeNode(new DOMAttr('LABEL', _NUMMDT));
				$reference->appendChild($custom_t13);
				$custom_t14 = $docXML->createElement("CUSTOM_T14", $resRecordset->doc_custom_t14);
				$custom_t14->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t14->setAttributeNode(new DOMAttr('LABEL', _NUMIMM));
				$reference->appendChild($custom_t14);
				$custom_t15 = $docXML->createElement("CUSTOM_T15", $resRecordset->doc_custom_t15);
				$custom_t15->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t15->setAttributeNode(new DOMAttr('LABEL', _NUMLOT));
				$reference->appendChild($custom_t15);
				$custom_t16 = $docXML->createElement("CUSTOM_T16", $resRecordset->doc_custom_t16);
				$custom_t16->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t16->setAttributeNode(new DOMAttr('LABEL', _ANNEEMOIS));
				$reference->appendChild($custom_t16);
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
