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
* @brief  View a document
*
* @file view.php
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/


/**
* $etapes  array Contains 2 etaps : form and status (order matters)
*/
$etapes = array('form', 'status');
/**
* $frm_width  Width of the modal (empty)
*/
$frm_width='';
/**
* $frm_height  Height of the modal (empty)
*/
$frm_height = '';
/**
* $mode_form  Mode of the modal : fullscreen
*/
$mode_form = 'fullscreen';

/**
 * Returns the indexing form text
 *
 * @param $values Array Contains the res_id of the document to process
 * @param $path_manage_action String Path to the PHP file called in Ajax
 * @param $id_action String Action identifier
 * @param $table String Table
 * @param $module String Origin of the action
 * @param $coll_id String Collection identifier
 * @param $mode String Action mode 'mass' or 'page'
 * @return String The form content text
 **/
function get_form_txt($values, $path_manage_action,  $id_action, $table, $module, $coll_id, $mode )
{
	$res_id = $values[0];
	$frm_str = '';
	$_SESSION['doc_id'] = $res_id;
	$frm_str .= '<div>';
	$frm_str .= '	<center><input name="close" id="close" type="button" value="'._CLOSE.'" class="button" onClick="javascript:$(\'baskets\').style.visibility=\'visible\';destroyModal(\'modal_'.$id_action.'\');reinit();"/></center>';
	$frm_str .= '	<iframe src="'.$_SESSION['config']['businesappurl'].'index.php?display=true&dir=indexing_searching&page=view&id='.$res_id.'" name="viewframe" id="viewframe"  scrolling="auto" frameborder="0" ></iframe>';
	$frm_str .= '</div>';
	$frm_str .= '<script type="text/javascript">resize_frame_view("modal_'.$id_action.'", "viewframe", true, true);window.scrollTo(0,0);</script>';
	return addslashes($frm_str);
}

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
 }
?>
