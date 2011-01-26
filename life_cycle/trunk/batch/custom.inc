<?php

function createPDI($resInContainer) {
	/*$sourceTemplatePI = $GLOBALS['MaarchDirectory'] . "modules" .DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "xml_templates" . DIRECTORY_SEPARATOR . "pdi.xml";
	if (file_exists($sourceTemplatePI)) {
		$xml = simplexml_load_file($sourceTemplatePI);
		if ($xml == false) {
			$returnInfos = array('error'=>'Error on loading xml file:' . $templatePI);
			return $returnInfos;
		}*/
		$tmpXML = $GLOBALS['TmpDirectory'] . DIRECTORY_SEPARATOR . "pdi.xml";
		$docXML = new DomDocument("1.0", "utf-8");
		$docXML->preserveWhiteSpace = true;
		$docXML->formatOutput = true;
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
				if(isset($resRecordset->publisher)) {
					$publisher = $docXML->createElement("PUBLISHER", $resRecordset->publisher);
				} else {
					$publisher = $docXML->createElement("PUBLISHER", "");
				}
				$publisher->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($publisher);
				if(isset($resRecordset->contributor)) {
					$contributor = $docXML->createElement("CONTRIBUTOR", $resRecordset->contributor);
				} else {
					$contributor = $docXML->createElement("CONTRIBUTOR", "");
				}
				$contributor->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($contributor);
				$typist = $docXML->createElement("TYPIST", $resRecordset->typist);
				$typist->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($typist);
				if(isset($resRecordset->author)) {
					$author = $docXML->createElement("AUTHOR", $resRecordset->author);
				} else {
					$author = $docXML->createElement("AUTHOR", "");
				}
				$author->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($author);
				if(isset($resRecordset->source)) {
					$source = $docXML->createElement("SOURCE", $resRecordset->source);
				} else {
					$source = $docXML->createElement("SOURCE", "");
				}
				$source->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($source);
				if(isset($resRecordset->scan_date)) {
					$scan_date = $docXML->createElement("SCAN_DATE", $resRecordset->scan_date);
				} else {
					$scan_date = $docXML->createElement("SCAN_DATE", "");
				}	
				$scan_date->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($scan_date);
				if(isset($resRecordset->scan_user)) {
					$scan_user = $docXML->createElement("SCAN_USER", $resRecordset->scan_user);
				} else {
					$scan_user = $docXML->createElement("SCAN_USER", "");
				}
				$scan_user->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($scan_user);
				if(isset($resRecordset->scan_location)) {
					$scan_location = $docXML->createElement("SCAN_LOCATION", $resRecordset->scan_location);
				} else {
					$scan_location = $docXML->createElement("SCAN_LOCATION", "");
				}
				$scan_location->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($scan_location);
				if(isset($resRecordset->scan_wkstation)) {
					$scan_wkstation = $docXML->createElement("SCAN_WKSTATION", $resRecordset->scan_wkstation);
				} else {
					$scan_wkstation = $docXML->createElement("SCAN_WKSTATION", "");
				}
				$scan_wkstation->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($scan_wkstation);
				if(isset($resRecordset->scan_batch)) {
					$scan_batch = $docXML->createElement("SCAN_BATCH", $resRecordset->scan_batch);
				} else {
					$scan_batch = $docXML->createElement("SCAN_BATCH", "");
				}
				$scan_batch->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($scan_batch);
				if(isset($resRecordset->envelop_id)) {
					$envelop_id = $docXML->createElement("ENVELOP_ID", $resRecordset->envelop_id);
				} else {
					$envelop_id = $docXML->createElement("ENVELOP_ID", "");
				}
				$envelop_id->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($envelop_id);
				if(isset($resRecordset->origin)) {
					$origin = $docXML->createElement("ORIGIN", $resRecordset->origin);
				} else {
					$origin = $docXML->createElement("ORIGIN", "");
				}
				$origin->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($origin);
				if(isset($resRecordset->is_ingoing)) {
					$is_ingoing = $docXML->createElement("IS_INGOING", $resRecordset->is_ingoing);
				} else {
					$is_ingoing = $docXML->createElement("IS_INGOING", "");
				}
				$is_ingoing->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($is_ingoing);
				if(isset($resRecordset->history)) {
					$history = $docXML->createElement("HISTORY", $resRecordset->history);
				} else {
					$history = $docXML->createElement("HISTORY", "");
				}
				$history->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$provenance->appendChild($history);
				$CommentString = _PDI_COMMENT_HISTORY;
				$CommentNodeHistory = $docXML->createComment($CommentString);
				$history->appendChild($CommentNodeHistory);
				//a reference
				$reference = $docXML->createElement("REFERENCE");
				$pdi->appendChild($reference);
				if(isset($resRecordset->title)) {
					$title = $docXML->createElement("TITLE", htmlentities($resRecordset->title));
				} else {
					$title = $docXML->createElement("TITLE", "");
				}
				$title->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$title->setAttributeNode(new DOMAttr('LABEL', _TITLE2));
				$reference->appendChild($title);
				if(isset($resRecordset->subject)) {
					$subject = $docXML->createElement("SUBJECT", $resRecordset->subject);
				} else {
					$subject = $docXML->createElement("SUBJECT", "");
				}
				$subject->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$subject->setAttributeNode(new DOMAttr('LABEL', _SUBJECT));
				$reference->appendChild($subject);
				if(isset($resRecordset->description)) {
					$description = $docXML->createElement("DESCRIPTION", $resRecordset->description);
				} else {
					$description = $docXML->createElement("DESCRIPTION", "");
				}
				$description->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$reference->appendChild($description);
				if(isset($resRecordset->identifier)) {
					$identifier = $docXML->createElement("IDENTIFIER", $resRecordset->identifier);
				} else {
					$identifier = $docXML->createElement("IDENTIFIER", "");
				}
				$identifier->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$reference->appendChild($identifier);
				if(isset($resRecordset->coverage)) {
					$coverage = $docXML->createElement("COVERAGE", $resRecordset->coverage);
				} else {
					$coverage = $docXML->createElement("COVERAGE", "");
				}
				$coverage->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$reference->appendChild($coverage);
				if(isset($resRecordset->doc_date)) {
					$doc_date = $docXML->createElement("DOC_DATE", $resRecordset->doc_date);
				} else {
					$doc_date = $docXML->createElement("DOC_DATE", "");
				}
				$doc_date->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$reference->appendChild($doc_date);
				if(isset($resRecordset->type_id)) {
					$type_id = $docXML->createElement("TYPE_ID", $resRecordset->type_id);
				} else {
					$type_id = $docXML->createElement("TYPE_ID", "");
				}
				$type_id->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$reference->appendChild($type_id);
				if(isset($resRecordset->type_label)) {
					$type_label = $docXML->createElement("TYPE_LABEL", $resRecordset->type_label);
				} else {
					$type_label = $docXML->createElement("TYPE_LABEL", "");
				}
				$type_label->setAttributeNode(new DOMAttr('SOURCE', 'VIEW'));
				$reference->appendChild($type_label);
				//customs
				if(isset($resRecordset->doc_custom_t1)) {
					$custom_t1 = $docXML->createElement("CUSTOM_T1", $resRecordset->doc_custom_t1);
				} else {
					$custom_t1 = $docXML->createElement("CUSTOM_T1", "");
				}
				$custom_t1->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t1->setAttributeNode(new DOMAttr('LABEL', _IDENTIFIER));
				$reference->appendChild($custom_t1);
				if(isset($resRecordset->doc_custom_n1)) {
					$custom_n1 = $docXML->createElement("CUSTOM_N1", $resRecordset->doc_custom_n1);
				} else {
					$custom_n1 = $docXML->createElement("CUSTOM_N1", "");
				}
				$custom_n1->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_n1->setAttributeNode(new DOMAttr('LABEL', _AMOUNT));
				$reference->appendChild($custom_n1);
				if(isset($resRecordset->doc_custom_d1)) {
					$custom_d1 = $docXML->createElement("CUSTOM_D1", $resRecordset->doc_custom_d1);
				} else {
					$custom_d1 = $docXML->createElement("CUSTOM_D1", "");
				}
				$custom_d1->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_d1->setAttributeNode(new DOMAttr('LABEL', _CUSTOM_D1));
				$reference->appendChild($custom_d1);
				if(isset($resRecordset->doc_custom_t2)) {
					$custom_t2 = $docXML->createElement("CUSTOM_T2", $resRecordset->doc_custom_t2);
				} else {
					$custom_t2 = $docXML->createElement("CUSTOM_T2", "");
				}
				$custom_t2->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t2->setAttributeNode(new DOMAttr('LABEL', _CONTACT_NAME));
				$reference->appendChild($custom_t2);
				if(isset($resRecordset->doc_custom_t3)) {
					$custom_t3 = $docXML->createElement("CUSTOM_T3", $resRecordset->doc_custom_t3);
				} else {
					$custom_t3 = $docXML->createElement("CUSTOM_T3", "");
				}
				$custom_t3->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t3->setAttributeNode(new DOMAttr('LABEL', _COUNTRY));
				$reference->appendChild($custom_t3);
				if(isset($resRecordset->doc_custom_t4)) {
					$custom_t4 = $docXML->createElement("CUSTOM_T4", $resRecordset->doc_custom_t4);
				} else {
					$custom_t4 = $docXML->createElement("CUSTOM_T4", "");
				}
				$custom_t4->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t4->setAttributeNode(new DOMAttr('LABEL', _CUSTOMER));
				$reference->appendChild($custom_t4);
				if(isset($resRecordset->doc_custom_t5)) {
					$custom_t5 = $docXML->createElement("CUSTOM_T5", $resRecordset->doc_custom_t5);
				} else {
					$custom_t5 = $docXML->createElement("CUSTOM_T5", "");
				}
				$custom_t5->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$custom_t5->setAttributeNode(new DOMAttr('LABEL', _PO_NUMBER));
				$reference->appendChild($custom_t5);
				//a fixity
				$fixity = $docXML->createElement("FIXITY");
				$pdi->appendChild($fixity);
				if(isset($resRecordset->fingerprint)) {
					$fingerprint = $docXML->createElement("FINGERPRINT", $resRecordset->fingerprint);
				} else {
					$fingerprint = $docXML->createElement("FINGERPRINT", "");
				}
				$fingerprint->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$fixity->appendChild($fingerprint);
				if(isset($resRecordset->scan_postmark)) {
					$scan_postmark = $docXML->createElement("SCAN_POSTMARK", $resRecordset->scan_postmark);
				} else {
					$scan_postmark = $docXML->createElement("SCAN_POSTMARK", "");
				}
				$scan_postmark->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$fixity->appendChild($scan_postmark);
				if(isset($resRecordset->filesize)) {
					$filesize = $docXML->createElement("FILESIZE", $resRecordset->filesize);
				} else {
					$filesize = $docXML->createElement("FILESIZE", "");
				}
				$filesize->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$fixity->appendChild($filesize);
				if(isset($resRecordset->page_count)) {
					$page_count = $docXML->createElement("PAGE_COUNT", $resRecordset->page_count);
				} else {
					$page_count = $docXML->createElement("PAGE_COUNT", "");
				}
				$page_count->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$fixity->appendChild($page_count);
				//a context
				$context = $docXML->createElement("CONTEXT");
				$pdi->appendChild($context);
				if(isset($resRecordset->res_id)) {
					$res_id = $docXML->createElement("RES_ID", $resRecordset->res_id);
				} else {
					$res_id = $docXML->createElement("RES_ID", "");
				}
				$res_id->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$context->appendChild($res_id);
				$collection = $docXML->createElement("COLLECTION", $GLOBALS['collection']);
				$collection->setAttributeNode(new DOMAttr('SOURCE', 'LIFECYCLE'));
				$context->appendChild($collection);
				$fingerprint_mode = $docXML->createElement("FINGERPRINT_MODE", $GLOBALS['docserverSourceFingerprint']);
				$fingerprint_mode->setAttributeNode(new DOMAttr('SOURCE', 'LIFECYCLE'));
				$context->appendChild($fingerprint_mode);
				$format = $docXML->createElement("FORMAT", $resRecordset->format);
				$format->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$context->appendChild($format);
				if(isset($resRecordset->creation_date)) {
					$creation_date = $docXML->createElement("CREATION_DATE", $resRecordset->creation_date);
				} else {
					$creation_date = $docXML->createElement("CREATION_DATE", "");
				}
				$creation_date->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$context->appendChild($creation_date);
				if(isset($resRecordset->doc_language)) {
					$doc_language = $docXML->createElement("DOC_LANGUAGE", $resRecordset->doc_language);
				} else {
					$doc_language = $docXML->createElement("DOC_LANGUAGE", "");
				}
				$doc_language->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$context->appendChild($doc_language);
				if(isset($resRecordset->relation)) {
					$relation = $docXML->createElement("RELATION", $resRecordset->relation);
				} else {
					$relation = $docXML->createElement("RELATION", "");
				}
				$relation->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$context->appendChild($relation);
				if(isset($resRecordset->coverage)) {
					$coverage = $docXML->createElement("COVERAGE", $resRecordset->coverage);
				} else {
					$coverage = $docXML->createElement("COVERAGE", "");
				}
				$coverage->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$context->appendChild($coverage);
				if(isset($resRecordset->rights)) {
					$rights = $docXML->createElement("RIGHTS", $resRecordset->rights);
				} else {
					$rights = $docXML->createElement("RIGHTS", "");
				}
				$rights->setAttributeNode(new DOMAttr('SOURCE', 'RES'));
				$context->appendChild($rights);
			}
		}
		//save the xml
		$docXML->save($tmpXML);
	//}
}

?>
