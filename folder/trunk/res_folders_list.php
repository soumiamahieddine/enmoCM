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

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("modules".DIRECTORY_SEPARATOR."folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
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
	$page = $_SESSION['config']['businessappurl']."index.php?display=true&module=folder&page=folders_list";
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
	<script type="text/javascript" >
	window.top.location ='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=salary_sheet&module=folder&id=<?php  echo $_SESSION['current_folder_id'];?>';
	</script>
	<?php
}
?>
