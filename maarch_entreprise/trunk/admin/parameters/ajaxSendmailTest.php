<?php

/*
*
*    Copyright 2008,2015 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

include($_SESSION['config']['corepath'] 
    . '/apps/maarch_entreprise/tools/mails/htmlMimeMail.php');
//var_dump($_REQUEST);

$mailer = new htmlMimeMail();

$mailer->setSMTPParams(
    $host = (string)$_REQUEST['sendmailHost'], 
    $port = (string)$_REQUEST['sendmailPort'],
    $helo = (string)$_REQUEST['sendmailDomains'],
    $auth = filter_var($_REQUEST['sendmailAuth'], FILTER_VALIDATE_BOOLEAN),
    $user = (string)$_REQUEST['sendmailUser'],
    $pass = (string)$_REQUEST['sendmailPassword']
);

$mailer->setFrom(' <testSendmail@maarch.org> ');
$mailer->setSubject('test ' . $_REQUEST['sendmailApp']);
$mailer->setText('test ' . $_REQUEST['sendmailApp']);

$mailer->setTextCharset((string)$_REQUEST['sendmailCharset']);
$mailer->setHtmlCharset((string)$_REQUEST['sendmailCharset']);
$mailer->setHeadCharset((string)$_REQUEST['sendmailCharset']);
$recipients = array();
array_push($recipients, $_REQUEST['sendmailTo']);

try {
    $sendmail = $mailer->send($recipients, (string)$_REQUEST['sendmailType']);
} catch (Exception $e) {
    echo 'Exception : ',  $e->getMessage(), "";
}

$errorDetails = '';
$status = 'ok';
if( 
    ($sendmail == 1 && ((string)$_REQUEST['sendmailType'] == "smtp" || (string)$_REQUEST['sendmailType'] == "mail" )) 
    || ($sendmail == 0 && (string)$_REQUEST['sendmailType'] == "sendmail")
) {
    $status = 'ok';
} else {
    $status = 'ko';
    $errorDetails = 'sendmail code : ' . $sendmail . ' Errors when sending message through SMTP :' 
        . $mailer->errors[0] . ' ' . $mailer->errors[1];
}

echo "{status : '" . $status . "', errorDetails : '"
    . json_encode($errorDetails) . "'}";
exit;


