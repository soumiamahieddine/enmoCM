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

$state = 'LOAD_NOTIFICATIONS';
while ($state <> 'END') {
    if (isset($logger)) {
        $logger->write('STATE:' . $state, 'INFO');
    }
    switch ($state) {
 
	/**********************************************************************/
    /*                          LOAD_NOTIFICATIONS           	          */
    /* Load notification defsidentified with notification id              */
    /**********************************************************************/
    case 'LOAD_NOTIFICATIONS' :
		$logger->write("Loading configuration for notification id " . $notificationId, 'INFO');
		$notification = $notifications_controler->getByNotificationId($notificationId);
		if ($notification === FALSE) {
			Bt_exitBatch(1, "Template association for notification '".$notificationId."' not found");
        }
		$state = 'LOAD_EVENTS';
        break;
		
	/**********************************************************************/
    /*                          LOAD_EVENTS                               */
    /* Checking if the stack has notifications to proceed                 */
    /**********************************************************************/
    case 'LOAD_EVENTS' :
		$logger->write("Loading events for notification sid " . $notification->notification_sid, 'INFO');
		$events = $events_controler->getEventsByNotificationSid($notification->notification_sid);
		$totalEventsToProcess = count($events);
		$currentEvent = 0;
		if ($totalEventsToProcess === 0) {
			Bt_exitBatch(0, 'No event to process');
        }
		$logger->write($totalEventsToProcess . ' events to process', 'INFO');
		$tmpNotifs = array();
		$state = 'MERGE_EVENT';
        break;
		
	/**********************************************************************/
    /*                  MERGE_EVENT	    	                              */
    /* Process event stack to get recipients 				              */
    /**********************************************************************/
	case 'MERGE_EVENT' :
		foreach($events as $event) {
			$logger->write("Getting recipients using diffusion type '" .$notification->diffusion_type . "'", 'INFO');
			// Diffusion type specific recipients
			$recipients = $diffusion_type_controler->getRecipients($notification, $event);
			$nbRecipients = count($recipients);
			if ($nbRecipients === 0) {
				$logger->write('No recipient found' , 'WARNING');
				$events_controler->commitEvent($event->event_stack_sid, "FAILED: no recipient found");
			} else {
				$logger->write($nbRecipients .' recipients found', 'INFO');
				foreach ($recipients as $recipient) {
					$user_id = $recipient->user_id;
					if(!isset($tmpNotifs[$user_id])) {
						$tmpNotifs[$user_id]['recipient'] = $recipient;
						$logger->write('Checking if attachment required for ' . $user_id, 'INFO');
						$tmpNotifs[$user_id]['attach'] = $diffusion_type_controler->getAttachFor($notification, $user_id);
					}
					$tmpNotifs[$user_id]['events'][] = $event;
				}
			}		
		} 
		$totalNotificationsToProcess = count($tmpNotifs);
		$logger->write($totalNotificationsToProcess .' notifications to process', 'INFO');
		$state = 'MERGE_TEMPLATE';
		break;
		
	/**********************************************************************/
    /*                    	MERGE_TEMPLATE			           	          */
    /* Load parameters				                                      */
    /**********************************************************************/
    case 'MERGE_TEMPLATE' :
		foreach($tmpNotifs as $user_id => $tmpNotif) {
			// Merge template with data and style
			$logger->write('Merging template #' . $notification->template_id 
				. ' ('.count($tmpNotif['events']).' events) for user ' . $user_id, 'INFO');
			
			$params = array(
				'recipient' => $tmpNotif['recipient'],
				'events' => $tmpNotif['events'],
				'maarchUrl' => $maarchUrl,
				'maarchApps' => $maarchApps,
				'coll_id' => $coll_id,
                'res_table' => $coll_table,
                'res_view' => $coll_view
			);
			$html = $templates_controler->merge($notification->template_id, $params, 'content');
			if(strlen($html) === 0) {
				foreach($tmpNotif['events'] as $event) {
					$events_controler->commitEvent($event->event_stack_sid, "FAILED: Error when merging template");
				}
				Bt_exitBatch(8, "Could not merge template with the data");
			}
			
			// Prepare e-mail for stack
			$sender = $func->protect_string_db((string)$mailerParams->mailfrom);
			$recipient_mail = $tmpNotif['recipient']->mail;
			$subject = $func->protect_string_db($notification->description);
			$html = $func->protect_string_db($html);
			$html = str_replace('&amp;', '&', $html);
			
			// Attachments
			$attachments = array();
			if($tmpNotif['attach']) {	
				$logger->write('Adding attachments', 'INFO');
				foreach($tmpNotif['events'] as $event) {
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
			
			foreach($tmpNotif['events'] as $event) {
				$events_controler->commitEvent($event->event_stack_sid, "SUCCESS");
			}
			
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