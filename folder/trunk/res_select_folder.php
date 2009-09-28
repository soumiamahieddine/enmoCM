<?php 
/**
* File : res_select_folder.php
*
* Result of a form
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
$core_tools = new core_tools();
$core_tools->load_lang();
require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");

 if(!isset($_REQUEST['field']) || empty($_REQUEST['field']))
 {
 	header("location: ".$_SESSION['urltomodules']."folder/result_folder.php");
 	exit;
 }
 else
 {
	
 	$folder = new folder();
 	$folder->load_folder1(trim($_REQUEST['field']), $_SESSION['tablename']['fold_folders']);
 	$_SESSION['current_folder_id'] = $folder->get_field('folders_system_id');
 	$folder->modify_default_folder_in_db($_SESSION['current_folder_id'], $_SESSION['user']['UserId'], $_SESSION['tablename']['users']);

	 ?>
	
 	<script language="JavaScript" type="text/javascript">
		//window.alert(window.top.location);
		if(window.top.name == 'CreateFolder')
		{
			
			window.top.opener.top.opener.location.reload();window.top.opener.close();window.top.close();
		}
		else // opener = index_file
		{
				
			<?php 
			
			if($_SESSION['physical_archive_origin'] == 'true')
			{
				?>
			
				var eleframe1 = window.top.document.getElementById('myframe');
				eleframe1.src = '<?php  echo $_SESSION['urltomodules']?>physical_archive/select_types_for_pa.php';
				
				<?php 
			}
			elseif($_SESSION['origin'] <> 'store_file')
			{
			?>
				//var eleframe1 = window.top.document.getElementById('myframe');
				var eleframe1 = window.top.frames['index'].document.getElementById('myframe');
				eleframe1.src = '<?php  echo $_SESSION['urltomodules']?>physical_archive/select_type.php';
				//eleframe1.src = '<?php  echo $_SESSION['urltomodules']?>physical_archive/select_types_for_pa.php';
				//window.top.location.reload();//window.top.close();
			<?php 
			}
			else
			{
			?>
				window.top.location.reload();
			<?php 
			}
			?>
		}
 	</script>
 	<?php 
 }
?>