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
//require_once($_SESSION['pathtocoreclass']."class_request.php");

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
/*
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
*/

$core_tools->load_html();

$db = new dbquery();
$db->connect();
$db->query("select foldertype_id, foldertype_label from ".$_SESSION['tablename']['fold_foldertypes']." order by foldertype_label");

$foldertypes = array();
while($res = $db->fetch_object())
{
	array_push($foldertypes, array('id' => $res->foldertype_id, 'label' => $db->show_string($res->foldertype_label)));
}
?>

<h1><img src="<?php  echo $_SESSION['urltomodules']."folder/img/s_sheet_b.gif";?>" alt="" /> <?php  echo _CREATE_FOLDER;?></h1>
<div id="inner_content">
	<form name="create_folder" id="create_folder" action="<?php echo $_SESSION['urltomodules'];?>folder/manage_create_folder.php" method="post" class="forms">
		<p>
			<label for="foldertype"><?php echo _FOLDERTYPE;?> :</label>
			<select name="foldertype" id="foldertype" onchange="get_folder_index('<?php echo $_SESSION['urltomodules'];?>folder/create_folder_get_folder_index.php', this.options[this.options.selectedIndex].value);">
				<option value=""><?php  echo _CHOOSE_FOLDERTYPE;?></option>
				<?php  for($i=0; $i< count($foldertypes);$i++)
				{
				?><option value="<?php  echo $foldertypes[$i]['id'];?>" <?php  if($_SESSION['foldertype'] == $foldertypes[$i]['id']){ echo 'selected="selected"'; }?>><?php  echo $foldertypes[$i]['label'];?></option>
				<?php
				}?>
			</select>
		</p>
	</form>
  <!-- 	<div align="center">
    	<iframe name="choose_foldertype" id="choose_foldertype" src="<?php  echo $_SESSION['urltomodules']."folder/";?>choose_foldertype.php" frameborder="0" width="340" height="45"></iframe>
    </div>
    <div align="center">
    	<iframe name="frm_create_folder" id="frm_create_folder" src="<?php  echo $_SESSION['urltomodules']."folder/";  ?>frm_create_folder.php" frameborder="0" width="500" height="600"></iframe>
    </div>-->
</div>
