<?php
/*
*    Copyright 2008,2009 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Delete a structure
*
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

session_name('PeopleBox');
session_start();
 require_once($_SESSION['pathtocoreclass']."class_functions.php");

require_once($_SESSION['pathtocoreclass']."class_core_tools.php");

$core_tools = new core_tools('admin_architecture');
$core_tools->test_admin('admin_architecture', 'apps');
//here we loading the lang vars
$core_tools->load_lang();
require_once($_SESSION['pathtocoreclass']."class_db.php");


$db = new dbquery();

if(isset($_GET['id']))
{
	$id = addslashes($db->wash($_GET['id'], "no", _THE_STRUCTURE));
}
else
{
	$id = "";
}

$db->connect();

$db->query("select doctypes_first_level_label from ".$_SESSION['tablename']['doctypes_first_level']." where doctypes_first_level_id = ".$id."");

if($db->nb_result() == 0)
{
	$_SESSION['error'] = _STRUCTURE.' '._UNKNOWN.".";
	header("location: ".$_SESSION['config']['businessappurl']."index.php?page=structures&order=".$_REQUEST['order']."&order_field=".$_REQUEST['order_field']."&start=".$_REQUEST['start']."&what=".$_REQUEST['what']);
	exit();
}
else
{
	$info = $db->fetch_object();

	// delete structure
	$db->query("update ".$_SESSION['tablename']['doctypes_first_level']." set enabled = 'N' where doctypes_first_level_id = ".$id);

	//delete subfolders depending on that structure
	$db->query("update ".$_SESSION['tablename']['doctypes_second_level']." set enabled = 'N' where doctypes_first_level_id = ".$id);

	$db->query("delete from ".$_SESSION['tablename']['fold_foldertypes_doctypes_level1']." where doctypes_first_level_id = ".$id);

	$db->query("select type_id from ".$_SESSION['tablename']['doctypes']." where doctypes_first_level_id = ".$id);

	$db2 = new dbquery();
	$db2->connect();
	while($res = $db->fetch_object())
	{
		//delete the doctypes from the foldertypes_doctypes table
		$db2->query("delete from  ".$_SESSION['tablename']['fold_foldertypes_doctypes']."  where doctype_id = ".$res->type_id);
	}
	// delete the doctypes
	$db2->query("update ".$_SESSION['tablename']['doctypes']." set enabled = 'N' where doctypes_first_level_id = ".$id);

	if($_SESSION['history']['structuredel'] == "true")
	{
		require_once($_SESSION['pathtocoreclass']."class_history.php");
		$users = new history();
		$users->add($_SESSION['tablename']['doctypes_first_level'], $id,"DEL",_STRUCTURE_DEL." ".strtolower(_NUM).$id."", $_SESSION['config']['databasetype']);
	}
	$_SESSION['error'] = _DELETED_STRUCTURE.".";
	unset($_SESSION['m_admin']);
	header("location: ".$_SESSION['config']['businessappurl']."index.php?page=structures&order=".$_REQUEST['order']."&order_field=".$_REQUEST['order_field']."&start=".$_REQUEST['start']."&what=".$_REQUEST['what']);
	exit();
}
?>