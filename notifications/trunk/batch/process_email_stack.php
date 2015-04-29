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
    /*                          LOAD_NOTIFS                               */
    /* List the stack to proceed                                          */
    /**********************************************************************/
    case 'LOAD_EMAILS' :
        $query = "SELECT * FROM " . _NOTIF_EMAIL_STACK_TABLE_NAME
            . " WHERE exec_date is NULL";
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
    /*                          SEND_AN_EMAIL                                 */
    /* Load parameters and send an e-mail                                    */
    /**********************************************************************/
    case 'SEND_AN_EMAIL' :
        if($currentEmail < $totalEmailsToProcess) {
            $email = $GLOBALS['emails'][$currentEmail];
            $GLOBALS['mailer'] = new htmlMimeMail();
            $GLOBALS['mailer']->setSMTPParams(
                $host = (string)$mailerParams->smtp_host, 
                $port = (string)$mailerParams->smtp_port,
                $helo = (string)$mailerParams->domains,
                $auth = filter_var($mailerParams->smtp_auth, FILTER_VALIDATE_BOOLEAN),
                $user = (string)$mailerParams->smtp_user,
                $pass = (string)$mailerParams->smtp_password
                );
            $GLOBALS['logger']->write("Sending e-mail to : " . $email->recipient, 'INFO');
            $GLOBALS['mailer']->setFrom($email->sender);
            $GLOBALS['logger']->write("Subject : " . $email->subject, 'INFO');
            $GLOBALS['mailer']->setSubject($email->subject);
            $GLOBALS['mailer']->setHtml($email->html_body);
            $GLOBALS['mailer']->setHtml(str_replace('#and#', '&', $email->html_body));
            $GLOBALS['mailer']->setTextCharset((string)$email->charset);
            $GLOBALS['mailer']->setHtmlCharset((string)$email->charset);
            $GLOBALS['mailer']->setHeadCharset((string)$email->charset);
            
            if($email->attachments != '') {
                $attachments = explode(',', $email->attachments);
                foreach($attachments as $num => $attachment) {
                    if(is_file($attachment)) {
                    $ext = strrchr($attachment, '.');
                    $name = str_pad(($num + 1), 4, '0', STR_PAD_LEFT) . $ext;
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
            $return = $GLOBALS['mailer']->send(array($email->recipient), (string)$mailerParams->type);
            // if($return == 1) {
            if( ($return == 1 && ((string)$mailerParams->type == "smtp" || (string)$mailerParams->type == "mail" )) || ($return == 0 && (string)$mailerParams->type == "sendmail")) {
                $exec_result = 'SENT';
                
            } else {
                //$GLOBALS['logger']->write("Errors when sending message through SMTP :" . implode(', ', $GLOBALS['mailer']->errors), 'ERROR');
                $GLOBALS['logger']->write("Errors when sending message through SMTP :" . $GLOBALS['mailer']->errors[0], 'ERROR', 108);
                $exec_result = 'FAILED';
                $GLOBALS['exitCode'] = 108;
            }   
            $query = "UPDATE " . _NOTIF_EMAIL_STACK_TABLE_NAME 
                . " SET exec_date = " . $GLOBALS['db']->current_datetime()
                . ", exec_result = '".$exec_result."' "
                . " WHERE email_stack_sid = ".$email->email_stack_sid;
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
//unlink($GLOBALS['lckFile']);
exit($GLOBALS['exitCode']);
?>
