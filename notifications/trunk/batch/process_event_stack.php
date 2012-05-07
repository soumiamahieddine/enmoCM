<?php
/******************************************************************************
 BATCH PROCESS EVENT STACK

 Processes events from table event_stack
 
 1 - Groups events by 
	* Recipient
	* Event

 2 - Merge template for each recipient / event
 
 3 - Prepare e-mail and add to e-mail stack
 
******************************************************************************/

/* begin */
// load the config and prepare to process
include('load_process_event_stack.php');

$state = 'LOAD_TEMPLATES_ASSOC';
while ($state <> 'END') {
    if (isset($logger)) {
        $logger->write('STATE:' . $state, 'INFO');
    }
    switch ($state) {
 
	/**********************************************************************/
    /*                          LOAD_TEMPLATES_ASSOC           	          */
    /* Load template association identified with notification id          */
    /**********************************************************************/
    case 'LOAD_TEMPLATES_ASSOC' :
		$logger->write("Loading configuration for notification id " . $notificationId, 'INFO');
		$templateAssociation = $templates_association_controler->getByNotificationId($notificationId);
		if ($templateAssociation === FALSE) {
			Bt_exitBatch(1, "Template association for notification '".$notificationId."' not found");
        }
		$state = 'LOAD_EVENTS';
        break;
		
	/**********************************************************************/
    /*                          LOAD_EVENTS                               */
    /* Checking if the stack has notifications to proceed                 */
    /**********************************************************************/
    case 'LOAD_EVENTS' :
		$logger->write("Loading events for template association id " . $templateAssociation->system_id, 'INFO');
		$events = $event_controler->getEventsByTemplateAssociationId($templateAssociation->system_id);
		$totalEventsToProcess = count($events);
		$currentEvent = 0;
		if ($totalEventsToProcess === 0) {
			Bt_exitBatch(0, 'No event to process');
        }
		$logger->write($totalEventsToProcess . ' events to process', 'INFO');
		$notifications = array();
		$state = 'MERGE_EVENT';
        break;
		
	/**********************************************************************/
    /*                  MERGE_EVENT	    	                              */
    /* Process event stack to get recipients 				              */
    /**********************************************************************/
	case 'MERGE_EVENT' :
		foreach($events as $event) {
			$logger->write("Getting recipients using diffusion type '" .$templateAssociation->diffusion_type . "'", 'INFO');
			// Diffusion type specific recipients
			$recipients = $diffusion_type_controler->getRecipients($templateAssociation, $event);
			$nbRecipients = count($recipients);
			if ($nbRecipients === 0) {
				$logger->write('No recipient found' , 'WARNING');
				$exec_result = "FAILED: no recipient found";
			} else {
				$exec_result  = 'SUCCESS';
				$logger->write($nbRecipients .' recipients found', 'INFO');
				foreach ($recipients as $recipient) {
					$user_id = $recipient->user_id;
					if(!isset($notifications[$user_id])) {
						$notifications[$user_id]['recipient'] = $recipient;
					}
					$notifications[$user_id]['events'][] = $event;
				}
			}
			$event_controler->commitEvent($event->system_id, $exec_result);
			
		} 
		$totalNotificationsToProcess = count($notifications);
		$logger->write($totalNotificationsToProcess .' notifications to process', 'INFO');
		$state = 'MERGE_TEMPLATE';
		break;
		
	/**********************************************************************/
    /*                    	MERGE_TEMPLATE			           	          */
    /* Load parameters				                                      */
    /**********************************************************************/
    case 'MERGE_TEMPLATE' :
		foreach($notifications as $user_id => $notification) {
			// Merge template with data and style
			$logger->write('Merging template #' . $templateAssociation->template_id 
				. ' ('.count($notification['events']).' events) for user ' . $user_id, 'INFO');
			
			$params = array(
				'recipient' => $notification['recipient'],
				'events' => $notification['events'],
				'maarchUrl' => $maarchUrl,
				'maarchApps' => $maarchApps,
				'coll_id' => $coll_id,
                'res_table' => $coll_table,
                'res_view' => $coll_view
			);
			
			$html = $templates_controler->merge($templateAssociation->template_id, $params, 'content');
			if(strlen($html) === 0) {
				Bt_exitBatch(8, "Could not merge template with the data");
			}
			
			// Prepare e-mail for stack
			$sender = $func->protect_string_db((string)$mailerParams->mailfrom);
			$recipient_mail = $notification['recipient']->mail;
			$subject = $func->protect_string_db($templateAssociation->description);
			$html = $func->protect_string_db($html);
			$html = str_replace('&amp;', '&', $html);
			
			// Attachments
			$logger->write('Checking if attachment required for ' . $user_id, 'INFO');
			$attachments = array();
			$indAttach = $diffusion_type_controler->getAttachFor($templateAssociation, $user_id);
			if($indAttach) {			
				foreach($notification['events'] as $event) {
					$query = "SELECT "
						. "ds.path_template ,"
						. "mlb.path, "
						. "mlb.filename " 
						. "FROM ".$coll_view." mlb LEFT JOIN docservers ds ON mlb.docserver_id = ds.docserver_id "
						. "WHERE mlb.res_id = " . $event->record_id;
					Bt_doQuery($db, $query);
					$path_parts = $db->fetch_object();
					$path = $path_parts->path_template . str_replace('#', '/', $path_parts->path) . $path_parts->filename;
					$path = str_replace('//', '/', $path);
					$path = str_replace('\\', '/', $path);
					$attachments[] = $path;
				}
				$logger->write(count($attachments) . ' attachment(s) added', 'INFO');
			}
			
			$logger->write('Adding e-mail to email stack', 'INFO');
			$query = "INSERT INTO " . _NOTIF_EMAIL_STACK_TABLE_NAME 
				. " (sender, recipient, subject, html_body, charset, attachments, module) "
				. "VALUES ('".$sender."', "
				. "'".$recipient_mail."', "
				. "'".$subject."', "
				. "'".$html."', " 
				. "'".(string)$mailerParams->charset."', "
				. "'".implode(',', $attachments)."', "
				. "'notifications')";
			$logger->write('SQL query:' . $query, 'DEBUG');
			$db2 = new dbquery();
			$db2->connect();
			$db2->query($query, false, true);
			$currentNotification++;
		} 
		$state = 'END';
		break;
	}

}

$logger->write('End of process', 'INFO');
Bt_logInDataBase(
    $totalEventsToProcess, 0, 'process without error'
);	
$db->disconnect();
unlink($GLOBALS['lckFile']);
exit($GLOBALS['exitCode']);
?>