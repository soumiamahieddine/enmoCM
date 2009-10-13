<?php
/**
* File : res_folders_list.php
*
* Open details of a folder
*
* @package  Maarch PeopleBox 1.0
* @version 2.0
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");
$core_tools = new core_tools();
if(!$core_tools->is_module_loaded("folder"))
{
	echo "Folder module missing !<br/>Please install this module.";
	exit();
}
//here we loading the lang vars
$core_tools->load_lang();
if(!isset($_REQUEST['field']) || empty($_REQUEST['field']))
{
	$page = "folders_list.php";
	header("location: ".$page);
	exit();
}
else
{
	$folder = new folder();
	$folder->load_folder($_REQUEST['field'], $_SESSION['tablename']['fold_folders']);
	$_SESSION['current_folder_id'] = $folder->get_field('folders_system_id');
	$_SESSION['current_foldertype_coll_id'] = $folder->get_field('foldertype_coll_id');
	?>
	<script language="JavaScript" type="text/javascript" >
	window.top.location ='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=salary_sheet&module=folder&id=<?php  echo $_SESSION['current_folder_id'];?>';
	</script>
	<?php
}
?>