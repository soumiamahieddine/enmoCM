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

 if(file_exists($_SESSION['config']['lang'].'.php'))
{
	include($_SESSION['config']['lang'].'.php');
}
else
{
	$_SESSION['error'] = "Language file missing...<br/>";
}
  require_once("core/class/class_functions.php");
 require_once("core/class/class_db.php");
 require_once("core/class/class_request.php");
 require_once("class/class_folder.php");


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
