<?php
/**
* File : sous_dossier_del.php
*
* Delete a subfolder
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 06/2006x
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
//include('core/init.php');

//require_once("core/class/class_functions.php");
//require("core/class/class_core_tools.php");

$core_tools = new core_tools();
$core_tools->test_admin('admin_architecture', 'apps');
//here we loading the lang vars
$core_tools->load_lang();

//require_once("core/class/class_db.php");

$db = new dbquery();

if(isset($_GET['id']))
{
	$id = addslashes($db->wash($_GET['id'], "no", _THE_SUBFOLDER));
}
else
{
	$id = "";
}

$db->connect();

$db->query("select doctypes_second_level_label from ".$_SESSION['tablename']['doctypes_second_level']." where doctypes_second_level_id = ".$id."");

if($db->nb_result() == 0)
{
	$_SESSION['error'] = _SUBFOLDER.' '._UNKNOWN.".";
	header("location: ".$_SESSION['config']['businessappurl']."index.php?page=subfolders&order=".$_REQUEST['order']."&order_field=".$_REQUEST['order_field']."&start=".$_REQUEST['start']."&what=".$_REQUEST['what']);
	exit();
}
else
{
	$info = $db->fetch_object();

	$db->query("update ".$_SESSION['tablename']['doctypes_second_level']." set enabled = 'N' where doctypes_second_level_id = ".$id);

	$db->query("select type_id from ".$_SESSION['tablename']['doctypes']." where doctypes_second_level_id = ".$id);
	$db2 = new dbquery();
	$db2->connect();
	while($res = $db->fetch_object())
	{
		//delete the doctypes from the foldertypes_doctypes table
		$db2->query("delete from  ".$_SESSION['tablename']['fold_foldertypes_doctypes']."  where doctype_id = ".$res->type_id);
	}
	// delete the doctypes
	$db2->query("update ".$_SESSION['tablename']['doctypes']." set enabled = 'N' where doctypes_second_level_id = ".$id);

	if($_SESSION['history']['subfolderdel'] == "true")
	{
		require("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
		$users = new history();
		$users->add($_SESSION['tablename']['doctypes_second_level'], $id,"DEL",_DEL_SUBFOLDER." ".strtolower(_NUM).$id."", $_SESSION['config']['databasetype']);
	}
	$_SESSION['error'] = _SUBFOLDER_DELETED.".";
		unset($_SESSION['m_admin']);
	header("location: ".$_SESSION['config']['businessappurl']."index.php?page=subfolders&order=".$_REQUEST['order']."&order_field=".$_REQUEST['order_field']."&start=".$_REQUEST['start']."&what=".$_REQUEST['what']);
	exit();
}
?>
