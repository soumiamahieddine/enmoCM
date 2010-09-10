<?php
/*
 * Created on 30 mars 07
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

 require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("modules".DIRECTORY_SEPARATOR."folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");

$core_tools = new core_tools();
$core_tools->load_lang();
 if(!isset($_REQUEST['field']) || empty($_REQUEST['field']))
 {
 	header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&module=folder&page=result_new_folder");
 	exit;
 }
 else
 {
 	$folder = new folder();
 	$folder->load_folder(trim($_REQUEST['field']), $_SESSION['tablename']['folders']);
 	$_SESSION['current_folder'] = $folder;
 	?>
 	<script type="text/javascript" >
 	window.top.opener.location.reload();window.top.close();
 	</script>
 	<?php
 }
?>
