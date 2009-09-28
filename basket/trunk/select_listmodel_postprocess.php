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

/*
* @brief  service of storing the listmodel choice
*
* Used in Letterbox
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
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
$func = new functions();
$conn = new dbquery();
$conn->connect();
if($_SESSION['masterdoctype_res_id'] == "")
{
	$res_id = $_SESSION['res_id_to_qualify'];
}
else
{
	$res_id = $_SESSION['masterdoctype_res_id'];
}
if($_SESSION['m_admin']['entity'] == "")
{
	//$_SESSION['error'] .= _MUST_CHOOSE_AN_ENTITY."<br/>";
}
else
{
	$conn->query("delete from ".$_SESSION['tablename']['bask_listinstance']." where coll_id = '".$_SESSION['collection_id_choice']."' and res_id = ".$res_id."");
	for($i=0; $i<count($_SESSION['m_admin']['entity']['listmodel']); $i++)
	{
		$conn->query("insert into ".$_SESSION['tablename']['bask_listinstance']." (coll_id, res_id, sequence, user_id) values('".$_SESSION['collection_id_choice']."',".$res_id.",".$i." ,'".$_SESSION['m_admin']['entity']['listmodel'][$i]['USER_ID']."')");
	}
}
?>
