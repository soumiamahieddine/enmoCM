<?php
/*
 * Created on 30 mars 07
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
  include('core/init.php');


  require_once("core/class/class_functions.php");
 require_once("core/class/class_db.php");
 require_once("core/class/class_request.php");
require_once("modules/folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");

 require_once("core/class/class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();
 if(!isset($_REQUEST['field']) || empty($_REQUEST['field']))
 {
 	header("location: ".$_SESSION['urltomodules']."folder/result_new_folder.php");
 	exit;
 }
 else
 {
 	$folder = new folder();
 	$folder->load_folder(trim($_REQUEST['field']), $_SESSION['tablename']['folders']);
 	$_SESSION['current_folder'] = $folder;
 	?>
 	<script language="JavaScript" type="text/javascript" >
 	window.top.opener.location.reload();window.top.close();
 	</script>
 	<?php
 }
?>
