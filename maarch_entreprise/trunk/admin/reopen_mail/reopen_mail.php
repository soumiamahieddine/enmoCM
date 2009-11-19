<?php
include('core/init.php');


$core_tools2 = new core_tools();
//here we loading the lang vars
$core_tools2->load_lang();
$core_tools2->test_admin('reopen_mail', 'apps');
/****************Management of the location bar  ************/
$init = false;
if($_REQUEST['reinit'] == "true")
{
	$init = true;
}
$level = "";
if($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
{
	$level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=reopen_mail&admin=reopen_mail';
$page_label = _REOPEN_MAIL;
$page_id = "reopen_mail";
$core_tools2->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
require($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_reopen_mail.php");

$reopen = new ReopenMail();
$reopen->formreopenmail();
