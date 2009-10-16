<?php
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");


require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtomodules']."folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_admin_foldertypes.php");

require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();

$core_tools->load_lang();
$core_tools->test_admin('admin_foldertypes', 'folder');
$ft = new foldertype();

$ft->addupfoldertype("add");
?>
