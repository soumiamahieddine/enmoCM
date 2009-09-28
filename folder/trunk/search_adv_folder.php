<?php 
/**
* File : search_adv.php
*
* Advanced search form
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
session_name('PeopleBox'); 
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();
$core_tools->test_user();
//$core_tools->load_lang();
$core_tools->test_service('folder_search', 'folder');
//lgi
//$_SESSION['current_foldertype_coll_id'] = "coll_1";
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=search_adv_folder&module=folder';
$page_label = _SEARCH_ADV_FOLDER;
$page_id = "is_search_folder_adv";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
require_once($_SESSION['pathtomodules'].'folder'.$_SESSION['slash_env'].'class'.$_SESSION['slash_env']."class_modules_tools.php");
$fold = new folder();
require_once($_SESSION['pathtomodules'].'indexing_searching'.$_SESSION['slash_env'].'class'.$_SESSION['slash_env']."class_modules_tools.php");
$is = new indexing_searching();
$request = new request();
if ($_GET['erase'] == 'true')
{
	unset($_SESSION['folder_search']);
}
$db = new dbquery;
$db->connect();
$db->query("select doctypes_first_level_id, doctypes_first_level_label from ".$_SESSION['tablename']['doctypes_first_level']." where enabled= 'Y' order by doctypes_first_level_label");
$structures = array();
while($res = $db->fetch_object())
{
	array_push($structures , array('id' => $res->doctypes_first_level_id, 'label' => $res->doctypes_first_level_label));
}
$subfolders = array();
$db->query("select doctypes_second_level_id, doctypes_second_level_label from ".$_SESSION['tablename']['doctypes_second_level']." where enabled= 'Y' order by doctypes_second_level_label");
while($res = $db->fetch_object())
{
	array_push($subfolders , array('id' => $res->doctypes_second_level_id, 'label' => $res->doctypes_second_level_label));
}
$doctypes = array();
$db->query("select type_id, description from ".$_SESSION['tablename']['doctypes']." where enabled= 'Y' order by description");
while($res = $db->fetch_object())
{
	array_push($doctypes , array('id' => $res->type_id, 'label' => $res->description));
}
$foldertypes = array();
$db->query("select foldertype_id, foldertype_label from ".$_SESSION['tablename']['fold_foldertypes']." order by foldertype_label");
while($res = $db->fetch_object())
{
	array_push($foldertypes , array('id' => $res->foldertype_id, 'label' => $res->foldertype_label));
}
?>
<h1><img src="<?php  echo $_SESSION['urltomodules']."indexing_searching/img/picto_search_b.gif";?>" alt="" /> <?php  echo _ADV_SEARCH_FOLDER_TITLE; ?></h1>
<br>
<div class="clearsearch">
	<a href="index.php?page=search_adv_folder&module=folder&reinit=true&erase=true"><img src="<?php  echo $_SESSION['urltomodules']."indexing_searching/img/reset.gif";?>" alt="" /> <?php  echo _NEW_SEARCH; ?></a>
</div>
<br>
<br>
<br>
<!--<div class="newTipbox">
	<div class="newTipContentbox">-->
	<div class="block">
		<br>
		<h2><?php  echo _INFOS_FOLDERS;?></h2>
		<form name="frmsearch2" method="get" action="<?php  echo $_SESSION['config']['businessappurl'];?>index.php" id="frmsearch2" class="forms2">
			<input type="hidden" name="page" value="search_adv_folder_result" />
			<input type="hidden" name="module" value="folder" />
			<input type="hidden" name="foldertype_id" id="foldertype_id" value="1" />
			<table width="90%" border="0">
				<tr>
					<td width="25%" align="right"><label for="folder_id"><?php  echo _FOLDERID;?> :</label></td>
					<td width="24%">
						<input type="text" name="folder_id" id="folder_id" value="<?php  echo $_SESSION['folder_search']['folder_id'] ;?>" />
						<div id="foldersListById" class="autocomplete"></div>
						<script type="text/javascript">
							initList('folder_id', 'foldersListById', '<?php  echo $_SESSION['urltomodules'];?>folder/folders_list_by_id.php', 'folder', '3');
						</script>
					</td>
					<td width="2%">&nbsp;</td>
					<td width="25%" align="right"><label for="folder_name"><?php  echo _FOLDERNAME;?> :</label></td>
					<td width="24%">
						<input type="text" name="folder_name" id="folder_name" value="<?php  echo $_SESSION['folder_search']['folder_name'] ;?>" />
						<div id="foldersListByName" class="autocomplete"></div>
						<script type="text/javascript">
							initList('folder_name', 'foldersListByName', '<?php  echo $_SESSION['urltomodules'];?>folder/folders_list_by_name.php', 'folder', '3');
						</script>
					</td>
					<?php
					$_SESSION['folder_search']['foldertype_id'] = 1;
					?>
				</tr>
				<tr>
					<td width="25%" align="right"><label for="start_archive_date"><?php  echo _FOLDERDATE_START;?> :<label></td>
					<td width="24%">
						<input name="start_archive_date" type="text" id="start_archive_date" value="<?php  echo $_SESSION['folder_search']['start_archive_date'] ;?>" onclick='showCalender(this)'/>
					</td>
					<td width="2%">&nbsp;</td>
					<td width="25%" align="right"><label for="end_archive_date"><?php  echo _FOLDERDATE_END;?>:<label></td>
					<td width="24%">
						<input name="end_archive_date" type="text" id="end_archive_date" value="<?php  echo $_SESSION['folder_search']['end_archive_date'] ;?>" onclick='showCalender(this)'/>
					</td>
				</tr>
			</table>
			<br/>
			<p class="buttons">
				<input class="button" name="imageField" type="submit" value="<?php  echo _SEARCH; ?>" onclick="javascript:return(verif_search(this.form));"  />
			</p>
		</form>
	</div>
	<div class="block_end"></div>
	<!--</div>
</div>-->
