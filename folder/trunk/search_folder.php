<?php 
include('core/init.php'); 

require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require_once("core/class/class_request.php");
require_once("core/class/class_core_tools.php"); 
$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->load_html();
//here we building the header
$core_tools->load_header();
if(isset($_SESSION['current_foldertype']) && !empty($_SESSION['current_foldertype']))
{
	$label = '';
	for($i=0; $i<count($_SESSION['folder_index']);$i++)
	{
		if($_SESSION['folder_index'][$i]['COLUMN'] == "custom_t1")
		{
			$label = $_SESSION['folder_index'][$i]['LABEL']; 
		}
	}
	$_SESSION['current_foldertype_to_search'] = $_SESSION['current_foldertype'];
}
if(isset($_REQUEST['search']))
{
	if(isset($_REQUEST['folder_id']) && !empty($_REQUEST['folder_id']))
	{
		$_SESSION['FOLDER']['SEARCH']['FOLDER_NUM'] = $_REQUEST['folder_id'];
		
	}
	if(isset($_REQUEST['custom_t1']) && !empty($_REQUEST['custom_t1']))
	{
		$_SESSION['FOLDER']['SEARCH']['CUSTOM_T1'] = $_REQUEST['custom_t1'];
	}
	?>
	<script language="javascript" type="text/javascript">
		window.top.location.href='<?php  echo $_SESSION['config']['businessappurl']."index.php?module=folder&page=".$_SESSION['origin'];?>';
	</script>
    <?php 
	$_SESSION['current_foldertype'] = '';
	//exit();
}
?>
<body>
<form name="search_folder"  method="post" <?php  if($_SESSION['origin'] == 'view_folder'){?>class="forms fold_addforms"<?php  } else{?>class="forms addforms"<?php  } ?> action="<?php  echo $_SESSION['urltomodules'].'folder/search_folder.php';?>">
	<p>
   		<label><?php  echo _MATRICULE;?> : </label>
        <input type="text" name="folder_id"  id="folder_id" />
    </p>
    <?php  
	if(isset($_SESSION['current_foldertype']) && !empty($_SESSION['current_foldertype']))
	{
	?>
    <p>&nbsp;</p>
	<p>
   		<label><?php  echo $label;?> : </label>
        <input type="text" name="custom_t1"  id="custom_t1" />
    </p>
    <?php 
	}
	?>
    <p class="buttons">
    	<input class="button" name="search" id="search" type="submit"  value="<?php  echo _SEARCH;?>"/>
    </p>
</form>
</body>
</html>
