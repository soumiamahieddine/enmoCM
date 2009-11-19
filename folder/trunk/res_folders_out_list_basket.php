<?php
/**
* File : res_folders_out_list.php
*
* Open the folder out page
*
* @package  Maarch PeopleBox 1.0
* @version 2.0
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
  include('core/init.php');


 require_once("core/class/class_functions.php");
 require_once("core/class/class_db.php");
 require_once("core/class/class_request.php");
 require_once("core/class/class_core_tools.php");
 $core_tools = new core_tools();
 if(!$core_tools->is_module_loaded("folder"))
 {
 	echo "Folder module missing !<br/>Please install this module.";
	exit();
 }
 require_once("modules/folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");

//here we loading the lang vars
$core_tools->load_lang();


 if(!isset($_REQUEST['field']) || empty($_REQUEST['field']))
 {

 	$page = "folders_out_list.php";

 	header("location: ".$page);
 	exit;
 }
 else
 {
 	$_SESSION['folder_out_id'] = $_REQUEST['field'];
 	?>
	 	<script language="JavaScript" type="text/javascript" >
	 	window.top.location = 'index.php?page=details_folder_out&origin=welcome&module=folder';
	 	</script>
	 	<?php

 }
?>
