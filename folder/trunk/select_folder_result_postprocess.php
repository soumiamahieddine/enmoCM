<?php
/**
* File : select_folder_result_postprocess.php
*
* service of updating the folder after indexing (postprocess)
*
* @package  Maarch Framework 3.0
* @version 3.0
* @since 10/2005
* @license GPL
* @author  Laurent Giovannoni <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$func = new functions();
$core_tools = new core_tools();
$core_tools->load_lang();
require_once($_SESSION['pathtomodules']."folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
require_once($_SESSION['pathtocoreclass']."class_history.php");
//*************** HISTORY ***************//
$users = new history();
if($_SESSION['origin'] <> 'store_file' && !$_SESSION['is_store'])
{
	$users->add($_SESSION['tablename']['fold_folders'], $_SESSION['current_folder_id'], "ADD", $_SESSION['error']." ("._TYPE." : ".$func->show_string($_SESSION['type_description']).", ".strtolower(_NUM).$_SESSION['new_id'].") ", $_SESSION['config']['databasetype'],'indexing_searching');
}
else
{
	$users->add($_SESSION['tablename']['fold_folders'], $_SESSION['current_folder_id'], "ADD", $_SESSION['error']." "._BY_INTERN." ", $_SESSION['config']['databasetype'],'indexing_searching');
}
//*************** UPDATING THE FOLDER ***************//
$folder = new folder();
//echo "folder ".$_SESSION['current_folder_id'];
$folder->load_folder($_SESSION['current_folder_id'], $_SESSION['tablename']['fold_folders']);
$bool_complete = $folder->is_complete($_SESSION['collections'][$_SESSION['indexing2']['ind_coll']]['table'], $_SESSION['tablename']['fold_foldertypes_doctypes'],$_SESSION['tablename']['doctypes'],$_SESSION['current_folder_id']);
if($bool_complete)
{
	$folder->query("update ".$_SESSION['tablename']['fold_folders']." set is_complete = 'Y' where folders_system_id = ".$_SESSION['current_folder_id']);
}
else
{
	if($folder->get_field('status') == "IMP")
	{
		$folder->query("update ".$_SESSION['tablename']['fold_folders']." set status = 'NEW' where folders_system_id = ".$_SESSION['current_folder_id']);
		$users->add($_SESSION['tablename']['fold_folders'], $_SESSION['current_folder_id'], "ADD", _CREATE_FOLDER, $_SESSION['config']['databasetype'],'indexing_searching');
	}
}

?>
