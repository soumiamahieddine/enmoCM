<?php 
session_name('PeopleBox');    
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php"); 


require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_admin_foldertypes.php"); 

$func = new functions();
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();

$core_tools->load_lang();
$core_tools->test_admin('admin_foldertypes', 'folder');
if(isset($_GET['id']))
{
	$s_id = addslashes($func->wash($_GET['id'], "alphanum", _THE_ID));
}
else
{
	$s_id = "";
}

$ft = new foldertype();
$ft->adminfoldertype($s_id,"del");
?>