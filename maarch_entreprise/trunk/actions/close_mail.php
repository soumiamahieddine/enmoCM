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
* @brief   Action : simple confirm
*
* Open a modal box to confirm a status modification. Used by the core (manage_action.php page).
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

/**
* $confirm  bool true
*/
 $confirm = true;

/**
* $etapes  array Contains only one etap, the status modification
*/
 $etapes = array('status','close');

require_once("core/class/class_history.php");

/**
* Manages a status (All params must be declared, even if not used, to corresponds to the action management of the core)
*
* @param  $arr_id  array Contains the res_id to be modified
* @param  $history  string Not Used here
* @param  $id_action string Action identifier
* @param  $label_action string Action label
* @param  $status string New status
* @return bool false in sql error case, true otherwise
*/
function manage_status($arr_id, $history, $id_action, $label_action, $status)
{
	$db = new dbquery();
	$db->connect();
	$result = '';
	for($i=0; $i<count($arr_id );$i++)
	{
		$result .= $arr_id[$i].'#';
		$req = $db->query("update ".$_POST['table']. " set status = '".$status."' where res_id = ".$arr_id[$i], true);
		if(!$req)
		{
			$_SESSION['action_error'] = _SQL_ERROR;
			return false;
		}
	}
	return array('result' => $result, 'history_msg' => '');
	//return true;
 }

function manage_close($arr_id, $history, $id_action, $label_action, $status)
{
	$db = new dbquery();
	$db->connect();
	$result = '';
	require_once($_SESSION['pathtocoreclass'].'class_security.php');
	require_once($_SESSION['pathtocoreclass'].'class_request.php');
	$sec = new security();
	$req = new request();
	$ind_coll = $sec->get_ind_collection($_POST['coll_id']);
	$ext_table = $_SESSION['collections'][$ind_coll]['extensions'][0];
	$current_date = $req->current_datetime();
	for($i=0; $i<count($arr_id );$i++)
	{
		$result .= $arr_id[$i].'#';
		$req = $db->query("update ".$ext_table. " set closing_date = ".$current_date." where res_id = ".$arr_id[$i], true);

		if(!$req)
		{
			$_SESSION['action_error'] = _SQL_ERROR;
			return false;
		}

	}
	return array('result' => $result, 'history_msg' => '');
 }
?>
