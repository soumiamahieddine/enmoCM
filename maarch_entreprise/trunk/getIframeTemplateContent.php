<?php

$_SESSION['template_content'] = $_REQUEST['template_content'];

$_SESSION['template_content'] = str_replace('[dates]', date('d-m-Y'), $_SESSION['template_content']);
$_SESSION['template_content'] = str_replace('[time]', date('G:i:s'), $_SESSION['template_content']);

echo "{status : 'OK " . addslashes($_REQUEST['template_content']) . "'}";
exit;
