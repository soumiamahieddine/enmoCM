<?php

 function setConfigSendmail_batch_config_Xml($from,$to,$host,$user,$pass,$type,$port,$auth,$charset,$smtpSecure)
    {

        $xmlconfig = simplexml_load_file(realpath('.').'/custom/cs_'.$_SESSION['config']['databasename'].'/modules/sendmail/batch/config/config.xml');

        $CONFIG = $xmlconfig->CONFIG;

        $CONFIG->MaarchDirectory = realpath('.')."/";
        $chemin = $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']);
        $maarchUrl = rtrim($chemin, "install");
        $CONFIG->MaarchUrl = $maarchUrl;
        $CONFIG->MaarchApps = 'maarch_entreprise';
        $CONFIG->TmpDirectory = realpath('.').'/modules/sendmail/batch/tmp/';

        $MAILER = $xmlconfig->MAILER;
        $MAILER->type = $type;
        $MAILER->smtp_port = $port;
        $MAILER->smtp_host = $host;
        $MAILER->smtp_user = $user;
        $MAILER->smtp_password = $pass;
        if($auth == 1){
        $MAILER->smtp_auth = "true";
        }else{
        $MAILER->smtp_auth = "false";   
        }
        


        $LOG4PHP = $xmlconfig->LOG4PHP;
        $LOG4PHP->Log4PhpConfigPath = realpath('.').'/apps/maarch_entreprise/xml/log4php.xml';


        $res = $xmlconfig->asXML();
        $fp = @fopen(realpath('.')."/custom/cs_".$_SESSION['config']['databasename']."/modules/sendmail/batch/config/config.xml", "w+");
        if (!$fp) {
            return false;
            exit;
        }
        $write = fwrite($fp,$res);
        if (!$write) {
            return false;
            exit;
        }

    }


 function setConfigNotification_batch_config_Xml($from,$to,$host,$user,$pass,$type,$port,$auth,$charset,$smtpSecure)
    {

        $xmlconfig = simplexml_load_file(realpath('.').'/custom/cs_'.$_SESSION['config']['databasename'].'/modules/notifications/batch/config/config.xml');
        $CONFIG = $xmlconfig->CONFIG;
        $CONFIG->MaarchDirectory = realpath('.')."/";
        $chemin = $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']);
        $maarchUrl = rtrim($chemin, "install");
        $CONFIG->MaarchUrl = $maarchUrl;
        $CONFIG->MaarchApps = 'maarch_entreprise';
        $CONFIG->TmpDirectory = realpath('.').'/modules/notifications/batch/tmp/';

        $MAILER = $xmlconfig->MAILER;
        $MAILER->type = $type;
        $MAILER->smtp_port = $port;
        $MAILER->smtp_host = $host;
        $MAILER->smtp_user = $user;
        $MAILER->smtp_password = $pass;
        if($auth == 1){
        $MAILER->smtp_auth = "true";
        }else{
        $MAILER->smtp_auth = "false";   
        }

        $LOG4PHP = $xmlconfig->LOG4PHP;
        $LOG4PHP->Log4PhpConfigPath = realpath('.').'/apps/maarch_entreprise/xml/log4php.xml';


        $res = $xmlconfig->asXML();
        $fp = @fopen(realpath('.')."/custom/cs_".$_SESSION['config']['databasename']."/modules/notifications/batch/config/config.xml", "w+");
        if (!$fp) {
            return false;
            exit;
        }
        $write = fwrite($fp,$res);
        if (!$write) {
            return false;
            exit;
        }

    }

include($_SESSION['config']['corepath'] 
    . '/apps/maarch_entreprise/tools/mails/htmlMimeMail.php');


$email = $GLOBALS['emails'][$currentEmail];
            $GLOBALS['mailer'] = new htmlMimeMail();
            $GLOBALS['mailer']->setSMTPParams(
                $host = $_REQUEST['smtpHost'], 
                $port = $_REQUEST['smtpPort'],
                $helo = (string)$mailerParams->domains,
                $auth = filter_var($_REQUEST['smtpAuth'], FILTER_VALIDATE_BOOLEAN),
                $user = $_REQUEST['smtpUser'],
                $pass = $_REQUEST['smtpPassword']
                );

            $GLOBALS['mailer']->setReturnPath($email->sender);
            $GLOBALS['mailer']->setFrom("notifications@maarch.fr");
            $GLOBALS['mailer']->setSubject("Test smtp Maarch");
            $GLOBALS['mailer']->setHtml($email->html_body);
            $GLOBALS['mailer']->setHtml(str_replace('#and#', '&', $email->html_body));
            $GLOBALS['mailer']->setTextCharset("ceci est un mail de test");
            $GLOBALS['mailer']->setHtmlCharset((string)$email->charset);
            $GLOBALS['mailer']->setHeadCharset((string)$email->charset);
            
            $return = $GLOBALS['mailer']->send(array($_REQUEST['smtpMailTo']), $_REQUEST['smtpType']);

if ($return == false) {

		$return2['status'] = 2;
        $return2['text'] = _SMTP_ERROR;

		$jsonReturn = json_encode($return2);

		echo $jsonReturn;
		exit;

  //echo("<p>" . $mail->getMessage() . "</p>");
} else {

	require_once 'install/class/Class_Install.php';
    
setConfigSendmail_batch_config_Xml($from,$to,$host,$user,$pass,$_REQUEST['smtpType'],$port,$auth,$charset,$smtpSecure);

setConfigNotification_batch_config_Xml($from,$to,$host,$user,$pass,$_REQUEST['smtpType'],$port,$auth,$charset,$smtpSecure);
        $return2['status'] = 2;
        $return2['text'] = _SMTP_OK;

        $jsonReturn = json_encode($return2);

        echo $jsonReturn;
        exit;

}
?>
