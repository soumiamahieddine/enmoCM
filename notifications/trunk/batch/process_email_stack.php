<?php

/******************************************************************************/
/* begin */
// load the config and prepare to process
include('load_process_email_stack.php');

$state = 'LOAD_EMAILS';
while ($state <> 'END') {
    if (isset($GLOBALS['logger'])) {
        $GLOBALS['logger']->write('STATE:' . $state, 'INFO');
    }
    switch ($state) {
		
	/**********************************************************************/
    /*                          LOAD_NOTIFS 							  */
    /* List the stack to proceed       									  */
    /**********************************************************************/
    case 'LOAD_EMAILS' :
		$query = "SELECT * FROM email_stack WHERE exec_date is NULL";
		Bt_doQuery($GLOBALS['db'], $query);
		$totalEmailsToProcess = $GLOBALS['db']->nb_result();
		$currentEmail = 0;
		if ($totalEmailsToProcess === 0) {
			Bt_exitBatch(0, 'No e-mail to process');
        }
		
		$GLOBALS['logger']->write($totalEmailsToProcess . ' e-mails to proceed.', 'INFO');
		
		$GLOBALS['emails'] = array();
		while ($emailRecordset = $GLOBALS['db']->fetch_object()) {
			$GLOBALS['emails'][] = $emailRecordset;
		}
		$state = 'SEND_AN_EMAIL';
        break;
		
	/**********************************************************************/
    /*                          SEND_AN_EMAIL	 		          	          */
    /* Load parameters and send an e-mail                                    */
    /**********************************************************************/
    case 'SEND_AN_EMAIL' :
		if($currentEmail < $totalEmailsToProcess) {
			$email = $GLOBALS['emails'][$currentEmail];
			$GLOBALS['mailer'] = new htmlMimeMail();
			$GLOBALS['mailer']->setSMTPParams(
				(string)$mailerParams->smtp_host, 
				(string)$mailerParams->smtp_port,
				(string)$mailerParams->smtp_host . ":" . (string)$mailerParams->smtp_port,
				true,
				(string)$mailerParams->smtp_user,
				(string)$mailerParams->smtp_password
				);
			$GLOBALS['logger']->write("Sending e-mail to : " . $email->recipient, 'INFO');
			$GLOBALS['mailer']->setFrom($email->sender);
			$GLOBALS['logger']->write("Subject : " . $email->subject, 'INFO');
			$GLOBALS['mailer']->setSubject($email->subject);
			$GLOBALS['mailer']->setHtml($email->html_body);
			$GLOBALS['mailer']->setTextCharset((string)$email->charset);
			$GLOBALS['mailer']->setHtmlCharset((string)$email->charset);
			$GLOBALS['mailer']->setHeadCharset((string)$email->charset);
			
			if($email->attachments != '') {
				$attachments = explode(',', $email->attachments);
				foreach($attachments as $attachment) {
					if(is_file($attachment)) {
					$name = basename($attachment);
					$ext = strrchr($attachment, '.');
					$ctype = '';
					switch($ext) {
						case '.pdf':
							$ctype = 'application/pdf';
							break;
					}
					
						$file_content = $GLOBALS['mailer']->getFile($attachment);
						$GLOBALS['mailer']->addAttachment($file_content, $name, $ctype); 
					}
				}
			}
			
			$return = $GLOBALS['mailer']->send(array($email->recipient), 'smtp');
			if($return) {
				$exec_result = 'SENT';
			} else {
				$exec_result = 'FAILED';
			}	
			$query = "UPDATE email_stack SET exec_date = " . $GLOBALS['db']->current_datetime()
				. ", exec_result = '".$exec_result."' WHERE system_id = ".$email->system_id;
			Bt_doQuery($GLOBALS['db'], $query);
			$currentEmail++;
			$state = 'SEND_AN_EMAIL';
		} else {
			$state = 'END';
		}
        break;
	}
}

$GLOBALS['logger']->write('End of process', 'INFO');
Bt_logInDataBase(
    $totalEmailsToProcess, 0, 'process without error'
);
$GLOBALS['db']->disconnect();
unlink($GLOBALS['lckFile']);
exit($GLOBALS['exitCode']);
?>