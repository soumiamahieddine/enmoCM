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

 require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
 require_once("modules".DIRECTORY_SEPARATOR."folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_folder.php");

 if(!isset($_REQUEST['field']) || empty($_REQUEST['field']))
 {
 
 	$page = $_SESSION['config']['businessappurl']."index.php?display=true&module=folder&page=folders_out_list";
 	
	header("location: ".$page);
 	exit;
 }
 else
 {
 	$_SESSION['folder_out_id'] = $_REQUEST['field'];
 	?>
	 	<script  type="text/javascript" >
	 	window.top.location = '<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=details_folder_out&origin=welcome&module=folder';
	 	</script>
	 	<?php 
 	
 }
?>
