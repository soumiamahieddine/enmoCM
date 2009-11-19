<?php

include('core/init.php');
 
//require_once("core/class/class_functions.php");
$admin = new core_tools();
$admin->test_admin('admin_foldertypes', 'folder');
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=foldertype_add&module=folder';
$page_label = _ADDITION;
$page_id = "foldertype_add";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
//require_once("core/class/class_db.php");
require_once("modules/folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_admin_foldertypes.php");
$func = new functions();

$ft = new foldertype();

$ft->formfoldertype("add");
?>
