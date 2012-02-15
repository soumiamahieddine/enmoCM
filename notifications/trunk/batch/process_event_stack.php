<?php
/******************************************************************************
 BATCH PROCESS EVENT STACK

 Processes events from table event_stack
 
 1 - Groups events by 
	* Type/template
	* Recipient
	* Event

 Result =
 $notifications
	[ta_sid]
		[mail]
			[es_sid]
			[]

 2 - Uses data to prepare data source for template merging :
 $templates_association
 $events
 $GLOBALS['xxxxxxx'] => all datasources that may be constructed by the specific script
 
 Result = 
 $notifications
	[ta_sid]
		[user_id]
			[notification] = current notification
			[recipient] = current recipient
			[events] = list of events
			[xxxxxxx] = custom 
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
    /* Load parameters				                                      */
    /**********************************************************************/
    case 'LOAD_TEMPLATES_ASSOC' :
		$query = "SELECT * FROM templates_association WHERE notification_id ='" . $ta_id . "'";
		Bt_doQuery($db, $query);
		if ($db->nb_result() === 0) {
			Bt_exitBatch(1, "Template association for notification '".$ta_id."' not found");
        }
		$ta = $db->fetch_object();
		
		$state = 'LOAD_TEMPLATE';
        break;
		
	/**********************************************************************/
    /*                          LOAD_TEMPLATES 		           	          */
    /* Load parameters				                                      */
    /**********************************************************************/
    case 'LOAD_TEMPLATE' :
		$logger->write('Loading template ' . $ta->template_id, 'INFO');
		$query = "SELECT * FROM templates WHERE id = " . $ta->template_id;
		Bt_doQuery($db, $query);
		if ($db->nb_result() === 0) {
			Bt_exitBatch(5, 'Could not load template '. $ta->template_id);
		}
		$template = $db->fetch_object();
		$state = 'LOAD_DIFFUSION_TYPE';
        break;
	/**********************************************************************/
    /*                      LOAD_DIFFUSION_TYPE           	          */
    /* Load parameters				                                      */
    /**********************************************************************/
	case 'LOAD_DIFFUSION_TYPE':
		$dt = @$diffusion_type_controler->getDiffusionType($ta->diffusion_type);
		if($dt == false) {
			Bt_exitBatch(4, 'Unknown diffusion type '. $ta->diffusion_type);
		}
		$dscript = $maarchDirectory . $dt->script;
		if(!is_file($dscript)) {
			Bt_exitBatch(5, 'Could not find the diffusion script '. $dscript);
		}
		require_once($dscript);
		$state = 'LOAD_CONTENT_TYPE';
		break;
	
	/**********************************************************************/
    /*                      LOAD_CONTENT_TYPE           	          */
    /* Load parameters				                                      */
    /**********************************************************************/
    case 'LOAD_CONTENT_TYPE' :
		$dc = @$diffusion_content_controler->getDiffusionContent($ta->diffusion_content);
		if($dc == false) {
			Bt_exitBatch(4, 'Unknown diffusion content '. $ta->diffusion_content);
		}
		$dcscript = $maarchDirectory . $dc->script;
		if(!is_file($dcscript)) {
			Bt_exitBatch(5, 'Could not find the diffusion content script '. $dcscript);
		}
		require_once($dcscript);
		$state = 'LOAD_EVENTS';
        break;
		
	/**********************************************************************/
    /*                          LOAD_EVENTS                               */
    /* Checking if the stack has notifications to proceed                 */
    /**********************************************************************/
    case 'LOAD_EVENTS' :
		$query = "SELECT * FROM event_stack WHERE exec_date is NULL "
			. "AND ta_sid = " . $ta->system_id ;
		Bt_doQuery($db, $query);
		$totalEventsToProcess = $db->nb_result();
		$currentEvent = 0;
		if ($totalEventsToProcess === 0) {
			Bt_exitBatch(0, 'No event to process');
        }
		$logger->write($totalEventsToProcess . ' events to process', 'INFO');
        $events = array();
		while ($eventRecordset = $db->fetch_object()) {
            $events[] = $eventRecordset;
		}
		$notifications = array();
		$state = 'MERGE_EVENT';
        break;
		
	/**********************************************************************/
    /*                  MERGE_EVENT	    	                              */
    /* Process event stack to get recipients 				              */
    /**********************************************************************/
	case 'MERGE_EVENT' :
		foreach($events as $event) {
			$logger->write("Getting recipients using diffusion type '" .$ta->diffusion_type . "'", 'INFO');
			$query = '';
			$query = getRecipients($ta, $event);
			if(!$query) {
				Bt_exitBatch(4, 'Could not retrieve the query to select recipients');
			}
			Bt_doQuery($db, $query);
			$nbRecipients = $db->nb_result();
			if ($nbRecipients === 0) {
				$logger->write('No recipient found' , 'WARNING');
				$exec_result = "FAILED: no recipient found";
			} else {
				$exec_result  = 'SUCCESS';
				$logger->write('Found ' . $nbRecipients .' recipients', 'INFO');
				while ($userRecordset = $db->fetch_object()) {
					$user_id = $userRecordset->user_id;
					if(!isset($notifications[$user_id])) {
						$notifications[$user_id]['maarchUrl'] = $maarchUrl;
						$notifications[$user_id]['maarchApps'] = $maarchApps;
						$notifications[$user_id]['template_association'] = $ta;
						$notifications[$user_id]['template'] = $template;
						$notifications[$user_id]['recipient'] = $userRecordset;
						$notifications[$user_id]['attachments'] = array();
					}
					$notifications[$user_id]['events'][] = $event;
				}
			}
			$query = "UPDATE event_stack" 
				. " SET exec_date = ".$db->current_datetime().", exec_result = '".$exec_result."'" 
				. " WHERE system_id = ".$event->system_id;
			//Bt_doQuery($db, $query);
		} 
		$totalNotificationsToProcess = count($notifications);
		$logger->write('There are ' . $totalNotificationsToProcess .' notifications to process', 'INFO');
		$state = 'MERGE_TEMPLATE';
		break;
		
	/**********************************************************************/
    /*                    	MERGE_TEMPLATE			           	          */
    /* Load parameters				                                      */
    /**********************************************************************/
    case 'MERGE_TEMPLATE' :
		foreach($notifications as $user_id => $notification) {
			// Get additional data from content script
			$notification = getAdditionalContent($notification);
			$logger->write('Data for notification is "' . implode(', ', array_keys($notification)) .'"', 'INFO');
			$logger->write('Data for notification is "' . print_r($notification,true) .'"', 'INFO');
			// Load language file
			require_once($maarchDirectory 
				. 'modules' 
				. DIRECTORY_SEPARATOR . 'notifications' 
				. DIRECTORY_SEPARATOR . 'lang' 
				. DIRECTORY_SEPARATOR . 'fr.php'
				);
				
			// Load CSS
			$style = file_get_contents($maarchDirectory 
				. 'modules' 
				. DIRECTORY_SEPARATOR . 'notifications' 
				. DIRECTORY_SEPARATOR . 'css' 
				. DIRECTORY_SEPARATOR . 'template.css'
				);
			
			// Merge template with data and style
			$logger->write('Merging template with data', 'INFO');
			$html = $template_merger->merge($template->content, $notification, $style);
			if(strlen($html) === 0) {
				Bt_exitBatch(8, "Could not merge template with the data");
			}
			// Prepare e-mail for stack
			$sender = $func->protect_string_db((string)$mailerParams->mailfrom, $databasetype);
			$recipient = $notification['recipient']->mail;
			$subject = $func->protect_string_db($ta->description, $databasetype);
			$html = $func->protect_string_db($html, $databasetype);
			
			if($ta->is_attached == 'Y') {
				$attachments = implode(',', $notification['attachments']);
			}
			
			$logger->write('Adding e-mail to email stack', 'INFO');
			$query = "INSERT INTO email_stack " 
				. "(sender, recipient, subject, html_body, charset, attachments, module) "
				. "VALUES ('".$sender."', "
				. "'".$recipient."', "
				. "'".$subject."', "
				. "'".$html."', " 
				. "'".(string)$mailerParams->charset."', "
				. "'".$attachments."', "
				. "'notifications')";
			$logger->write('SQL query:' . $query, 'DEBUG');
			$db->query($query, false, true);
			$currentNotification++;
		} 
		$state = 'END';
		break;
	}

}

$logger->write('End of process', 'INFO');
Bt_logInDataBase(
    $currentNotification, 0, 'process without error'
);
$db->disconnect();
unlink($GLOBALS['lckFile']);
exit($GLOBALS['exitCode']);
?>