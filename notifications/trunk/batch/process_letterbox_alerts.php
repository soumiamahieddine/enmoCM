<?php

/******************************************************************************/
/* begin */
// load the config and prepare to process
include('load_process_letterbox_alerts.php');

$state = 'LOAD_ALERTS_NOTIFS';
while ($state <> 'END') {
    if (isset($GLOBALS['logger'])) {
        $GLOBALS['logger']->write('STATE:' . $state, 'INFO');
    }
    switch ($state) {
	/**********************************************************************/
    /*                          LOAD_ALERTS_NOTIFS           	         		 */
    /* Load parameters				                                      */
    /**********************************************************************/
    case 'LOAD_ALERTS_NOTIFS' :
		$query = "SELECT system_id, value_field FROM " 
        . _TEMPLATES_ASSOCIATION_TABLE_NAME 
        . " WHERE UPPER(what) = 'EVENT' "
        . " AND value_field IN ('alert1', 'alert2') "
        . " AND maarch_module = 'notifications'";
		Bt_doQuery($db, $query);
		$totalAlertsToProcess = $GLOBALS['db']->nb_result();
		if ($totalAlertsToProcess === 0) {
			Bt_exitBatch(0, 'No alert parametered');
        }
		$logger->write($totalAlertsToProcess . " notifications parametered for mail alerts", 'INFO');
		$GLOBALS['alert_notifs'] = array();
		while ($alertRecordset = $GLOBALS['db']->fetch_object()) {
			$GLOBALS['alert_notifs'][$alertRecordset->value_field][] = $alertRecordset->system_id;
		}
	
		$state = 'LOAD_DOCTYPES';
        break;
	
	/**********************************************************************/
    /*                          LOAD_DOCTYPES							 */
    /* Load parameters				                                     */
    /**********************************************************************/
    case 'LOAD_DOCTYPES' :
		$query = "SELECT * FROM " . $collDoctypeExt;
		Bt_doQuery($db, $query);
		$totalDocTypes = $GLOBALS['db']->nb_result();
		$GLOBALS['doctypes'] = array();
		while ($doctypeRecordset = $GLOBALS['db']->fetch_object()) {
			$GLOBALS['doctypes'][$doctypeRecordset->type_id] = $doctypeRecordset;
		}
		$logger->write($totalDocTypes . " document types parametered", 'INFO');
		$state = 'LIST_DOCS';
        break;
	/**********************************************************************/
    /*                          LIST_DOCS 								  */
    /* List the resources to proccess for alarms						  */
    /**********************************************************************/
    case 'LIST_DOCS' :
		$query = "SELECT res_id, type_id, process_limit_date" 
			. " FROM " . $collView
			. " WHERE closing_date IS null"
			. " AND status NOT IN ('CLO', 'DEL')"
			. " AND (flag_alarm1 = 'N' OR flag_alarm2 = 'N')";
		Bt_doQuery($GLOBALS['db'], $query);
		$totalDocsToProcess = $GLOBALS['db']->nb_result();
		$currentDoc = 0;
		if ($totalDocsToProcess === 0) {
			Bt_exitBatch(0, 'No document to process');
        }
		$logger->write($totalDocsToProcess . " documents to process (i.e. not closed, at least one alert to send)", 'INFO');
		$GLOBALS['docs'] = array();
		while ($DocRecordset = $GLOBALS['db']->fetch_object()) {
			$GLOBALS['docs'][] = $DocRecordset;
		}
		$state = 'A_DOC';
		break;
		
	/**********************************************************************/
    /*                          A_DOC	 		          	          	  */
    /* Add notification to event_stack for each notif to be sent	  	  */
    /**********************************************************************/
    case 'A_DOC' :
		if($currentDoc < $totalDocsToProcess) {
			$myDoc = $GLOBALS['docs'][$currentDoc];
			$logger->write("Processing document #" . $myDoc->res_id, 'INFO');
				
			$myDoctype = $GLOBALS['doctypes'][$myDoc->type_id];
			if(!$myDoctype) {
				Bt_exitBatch(1, 'Unknown document type ' . $myDoc->type_id);
			}
			$logger->write("Document type id is #" . $myDoc->type_id, 'INFO');
			
			// Alert 1 = limit - n days
			$query = "SELECT 'true' as alarm1 FROM parameters "
				. " WHERE " . $db->get_date_diff($db->current_datetime(), "'".$myDoc->process_limit_date."'") 
				. " <= " . $myDoctype->delay1;
			Bt_doQuery($db, $query);	
			$result = $db->fetch_object();
			if($result->alarm1 === 'true') {
				$logger->write("Alarm 1 will be sent", 'INFO');
				$info = 'Relance 1 pour traitement du document No' . $myDoc->res_id . ' avant date limite.';  
				if(count($GLOBALS['alert_notifs']['alert1']) > 0) {
					foreach($GLOBALS['alert_notifs']['alert1'] as $ta_sid) {
						$query = "INSERT INTO " . _NOTIF_EVENT_STACK_TABLE_NAME
							. " (ta_sid, table_name, record_id, user_id, event_info"
							. ", event_date)" 
							. " VALUES(" . $ta_sid . ", '" 
							. $collView . "', '" . $myDoc->res_id . "', 'superadmin', '" . $info . "', " 
							. $db->current_datetime() . ")";
						Bt_doQuery($db, $query);
					}
				}
				
				$query = "UPDATE " . $collExt
					. " SET flag_alarm1 = 'Y', alarm1_date = " . $db->current_datetime()
					. " WHERE res_id = " . $myDoc->res_id;
				Bt_doQuery($db, $query);
			}
			
			// Alert 2 = limit + n days
			$query = "SELECT 'true' as alarm2 FROM parameters "
				. " WHERE " . $db->get_date_diff("'".$myDoc->process_limit_date."'", $db->current_datetime()) . " >= " . $myDoctype->delay2;
			Bt_doQuery($db, $query);	
			$result = $db->fetch_object();
			if($result->alarm2 === 'true') {
				$logger->write("Alarm 2 will be sent", 'INFO');
				$info = 'Relance 2 pour traitement du document No' . $myDoc->res_id . ' apres date limite.';  
				if(count($GLOBALS['alert_notifs']['alert2']) > 0) {
					foreach($GLOBALS['alert_notifs']['alert2'] as $ta_sid) {
						$query = "INSERT INTO " . _NOTIF_EVENT_STACK_TABLE_NAME
							. " (ta_sid, table_name, record_id, user_id, event_info"
							. ", event_date)" 
							. " VALUES(" . $ta_sid . ", '" 
							. $collView . "', '" . $myDoc->res_id . "', 'superadmin', '" . $info . "', " 
							. $db->current_datetime() . ")";
						$this->query($query, false, true);
					}
				}
				$query = "UPDATE " . $collExt
					. " SET flag_alarm2 = 'Y', alarm2_date = " . $db->current_datetime()
					. " WHERE res_id = " . $myDoc->res_id;
				Bt_doQuery($db, $query);
		
			}
			$currentDoc++;
			$state = 'A_DOC';
		} else {
			$state = 'END';
		}
		
        break;
	}
}

$GLOBALS['logger']->write('End of process', 'INFO');
Bt_logInDataBase(
    $totalDocsToProcess, 0, 'process without error'
);
$GLOBALS['db']->disconnect();
unlink($GLOBALS['lckFile']);
exit($GLOBALS['exitCode']);
?>