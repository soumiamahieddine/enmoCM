<?php

 function setConfigSendmail_batch_config_Xml($from,$to,$host,$username,$password,$type,$port,$auth,$charset,$smtpSecure)
    {
       // var_dump('setConfigSendmail_batch_config_Xml OK');
        $xmlconfig = simplexml_load_file(realpath('.').'/modules/sendmail/batch/config/config.xml.default');
        //$xmlconfig = 'apps/maarch_entreprise/xml/config.xml.default';
        $CONFIG = $xmlconfig->CONFIG;

        //$chemin_core = realpath('.').'/core/';
        //$CONFIG->CORE_PATH = $chemin_core;
        $CONFIG->MaarchDirectory = realpath('.');
        $chemin = $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']);
        $maarchUrl = rtrim($chemin, "install");
        $CONFIG->MaarchUrl = $maarchUrl;
        $CONFIG->MaarchApps = 'maarch_entreprise';
        $CONFIG->TmpDirectory = realpath('.').'/modules/sendmail/batch/tmp/';
        
        $CONFIG_BASE = $xmlconfig->CONFIG_BASE;
        $CONFIG_BASE->databaseserver = $_SESSION['config']['databaseserver'];
        $CONFIG_BASE->databaseserverport = $_SESSION['config']['databaseserverport'];
        $CONFIG_BASE->databasetype = 'POSTGRESQL';
        $CONFIG_BASE->databasename = $_SESSION['config']['databasename'];
        $CONFIG_BASE->databaseuser = $_SESSION['config']['databaseuser'];
        $CONFIG_BASE->databasepassword = $_SESSION['config']['databasepassword'];

        $MAILER = $xmlconfig->MAILER;
        $MAILER->type = $type;
        $MAILER->port = $port;
        $MAILER->smtp_host = $host;
        $MAILER->smtp_user = $username;
        $MAILER->smtp_password = $password;
        $MAILER->smtp_auth = $auth;
        $MAILER->smtp_secure = $smtpSecure;
        $MAILER->charset = $charset;
        $MAILER->mailfrom = $from;

        $LOG4PHP = $xmlconfig->LOG4PHP;
        $LOG4PHP->Log4PhpConfigPath = realpath('.').'/apps/maarch_entreprise/xml/log4php.xml';


        $res = $xmlconfig->asXML();
        $fp = @fopen(realpath('.')."/modules/sendmail/batch/config/config.xml", "w+");
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


 function setConfigNotification_batch_config_Xml($from,$to,$host,$username,$password,$type,$port,$auth,$charset,$smtpSecure)
    {
        //var_dump('setConfigNotification_batch_config_Xml OK');
        $xmlconfig = simplexml_load_file(realpath('.').'/modules/notifications/batch/config/config.xml.default');
        //$xmlconfig = 'apps/maarch_entreprise/xml/config.xml.default';
        $CONFIG = $xmlconfig->CONFIG;

        //$chemin_core = realpath('.').'/core/';
        //$CONFIG->CORE_PATH = $chemin_core;
        $CONFIG->MaarchDirectory = realpath('.');
        $chemin = $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']);
        $maarchUrl = rtrim($chemin, "install");
        $CONFIG->MaarchUrl = $maarchUrl;
        $CONFIG->MaarchApps = 'maarch_entreprise';
        $CONFIG->TmpDirectory = realpath('.').'/modules/notifications/batch/tmp/';
        
        $CONFIG_BASE = $xmlconfig->CONFIG_BASE;
        $CONFIG_BASE->databaseserver = $_SESSION['config']['databaseserver'];
        $CONFIG_BASE->databaseserverport = $_SESSION['config']['databaseserverport'];
        $CONFIG_BASE->databasetype = 'POSTGRESQL';
        $CONFIG_BASE->databasename = $_SESSION['config']['databasename'];
        $CONFIG_BASE->databaseuser = $_SESSION['config']['databaseuser'];
        $CONFIG_BASE->databasepassword = $_SESSION['config']['databasepassword'];

        $MAILER = $xmlconfig->MAILER;
        $MAILER->type = $type;
        $MAILER->smtp_port = $port;
        $MAILER->smtp_host = $host;
        $MAILER->smtp_user = $username;
        $MAILER->smtp_password = $password;
        $MAILER->smtp_auth = $auth;
        //$MAILER->smtp_secure = $smtpSecure;
        //$MAILER->charset = $charset;
        //$MAILER->mailfrom = $from;

        $LOG4PHP = $xmlconfig->LOG4PHP;
        $LOG4PHP->Log4PhpConfigPath = realpath('.').'/apps/maarch_entreprise/xml/log4php.xml';


        $res = $xmlconfig->asXML();
        $fp = @fopen(realpath('.')."/modules/notifications/batch/config/config.xml", "w+");
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

include_once "Mail.php";

$from = $_REQUEST['smtpMailFrom'];
$to = $_REQUEST['smtpMailTo'];
$type = $_REQUEST['smtpType'];
$port = $_REQUEST['smtpPort'];
$auth = $_REQUEST['smtpAuth'];
$charset = $_REQUEST['smtpCharset'];
$smtpSecure = $_REQUEST['smtpSecure'];

$subject = 'Test email using PHP SMTP';
$body = 'This is a test email message';
 
$host = $_REQUEST['smtpHost'];
$username = $_REQUEST['smtpUser'];
$password = $_REQUEST['smtpPassword'];
 
    if($auth == 'true'){
        $auth = true;
    }elseif($auth == 'false'){
        $auth = false;
    }else{
        $return['status'] = 2;
        $return['text'] = 'Authentication SMTP incorrect';

        $jsonReturn = json_encode($return);

        echo $jsonReturn;
        exit;
    }


$headers = array ('From' => $to,
  'To' => $to,
  'Subject' => $subject);
$smtp = Mail::factory($type,
  array ('host' => $host,
    'port' => $port,
    'auth' => $auth,
    'username' => $username,
    'password' => $password));
 //var_dump($smtp);
$mail = $smtp->send($to, $headers, $body);
 
if (PEAR::isError($mail)) {

		$return['status'] = 2;
		//$return['text'] = 'Error données';
        $return['text'] = $mail->getMessage();

		$jsonReturn = json_encode($return);

		echo $jsonReturn;
		exit;

  //echo("<p>" . $mail->getMessage() . "</p>");
} else {
	require_once 'install/class/Class_Install.php';
    
setConfigSendmail_batch_config_Xml($from,$to,$host,$username,$password,$type,$port,$auth,$charset,$smtpSecure);

setConfigNotification_batch_config_Xml($from,$to,$host,$username,$password,$type,$port,$auth,$charset,$smtpSecure);
        $return['status'] = 2;
        $return['text'] = 'Informations ok';

        $jsonReturn = json_encode($return);

        echo $jsonReturn;
        exit;

 // echo("<p>Message successfully sent!</p>");
}
?>
