<?php
/*
*
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
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_docserver.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();
$db = new dbquery();
$db ->connect();

if($_SESSION['masterdoctype_res_id'] == "")
{
	$id = $_SESSION['res_id_to_qualify'];
	if($id == "")
	{
		$id = $_SESSION['new_id'];
	}
	if($_SESSION['collection_id_choice'] == "")
	{
		$_SESSION['collection_id_choice'] = $_SESSION['indexing2']['ind_coll'];
	}
}
else
{
	$id = $_SESSION['masterdoctype_res_id'];
}

if($_SESSION['new_id'] <> "")
{
	$db->query("insert into ".$_SESSION['tablename']['bask_listinstance']."  values ( '".$_SESSION['collection_id_choice']."', ".$id.", 0, '".$_SESSION['user']['UserId']."', 'DOC')");
}
?>
