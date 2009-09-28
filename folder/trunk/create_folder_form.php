<?php 
/**
* File : create_folder_form.php
*
* Form to create a folder
*
* @package  Maarch PeopleBox 1.0
* @version 1.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
session_name('PeopleBox'); 
session_start();
/*require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php"); 
require_once($_SESSION['pathtocoreclass']."class_db.php");*/
require_once($_SESSION['pathtocoreclass']."class_request.php");

$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->test_service('create_folder', 'folder');
 
 /****************Management of the location bar  ************/
$init = false;
if($_REQUEST['reinit'] == "true")
{
	$init = true;
}
$level = "";
if($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
{
	$level = $_REQUEST['level'];
}  
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=create_folder_form&module=folder';
$page_label = _CREATE_FOLDER;
$page_id = "fold_create_folder_form";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
$_SESSION['res_folder'] = "";
$_SESSION['search_res_folder'] = "";

if(isset($_REQUEST['submit2']))
{
	if(isset($_REQUEST['matricule'])and !empty($_REQUEST['matricule']))
	{
		 $_SESSION['res_folder'] = "matricule";
		  $_SESSION['search_res_folder'] =$_REQUEST['matricule'];
	}
	elseif( isset($_REQUEST['nom']) and !empty($_REQUEST['nom']))
	{
		 $_SESSION['res_folder'] = "nom";
		  $_SESSION['search_res_folder'] = $_REQUEST['nom'];
	}
}
 
$core_tools->load_html();

?>

<h1><img src="<?php  echo $_SESSION['urltomodules']."folder/img/s_sheet_b.gif";?>" alt="" /> <?php  echo _CREATE_FOLDER;?></h1>
<div id="inner_content">
   	<div align="center">
    	<iframe name="choose_foldertype" id="choose_foldertype" src="<?php  echo $_SESSION['urltomodules']."folder/";?>choose_foldertype.php" frameborder="0" width="340" height="45"></iframe>
    </div>
    <div align="center">
    	<iframe name="frm_create_folder" id="frm_create_folder" src="<?php  echo $_SESSION['urltomodules']."folder/";?>frm_create_folder.php" frameborder="0" width="500" height="600"></iframe>
    </div>
 <!--   <hr/>
      	<div id="list_folder">
      	<iframe name="result_new_folder" src="<?php  echo $_SESSION['urltomodules']."folder/result_new_folder.php"; ?>" frameborder="0" width="900" height="400" scrolling="auto"></iframe>
      </div>-->
   </div>   
