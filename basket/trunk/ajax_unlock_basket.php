<?php
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");
require_once($_SESSION['pathtomodules']."basket".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
$security = new security();
$core_tools = new core_tools();
$core_tools->load_lang();

if(empty($_REQUEST['coll_id']) || !isset($_REQUEST['coll_id']) || empty($_REQUEST['res_id']) || !isset($_REQUEST['res_id']))
{
	echo "{status:1, error_txt : '"._ERROR_COLL_ID."'}";
	exit();
}

$db = new dbquery();
$db->connect();
$table = $security->retrieve_table_from_coll($_REQUEST['coll_id']);
if(empty($table))
{
	echo "{status:1, error_txt : '"._ERROR_COLL_ID."'}";
	exit();
}
$db->query("update ".$table. " set video_user = '', video_time = 0 where res_id = ".$_REQUEST['res_id']);
echo "{status:0}";
exit();
?>