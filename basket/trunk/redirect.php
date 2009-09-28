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
* @brief   Action : redirect a document
*
* Opens a modal box, displays the form to redirect, checks the form and manages it.
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/

/**
* $confirm  bool false
*/
$confirm = false;

/**
* $etapes  array Contains only one etap, the form display
*/
$etapes = array('form');

/**
* $frm_width   string Form width (must be ended by px)
*/
$frm_width='360px';

/**
* $frm_width   string Form height (must be ended by px)
*/
$frm_height = '500px';

/**
* Returns the content of the form (All params must be declared, even if not used, to corresponds to the action management of the core)
*
* @param  $values  array Contains documents identifier (or others identifier)
* @param  $path_manage_action  string Path to the manage_action.php script
* @param  $id_action string Action identifier
* @param  $table string Database resource table
* @param  $module string Module identifier
* @param  $coll_id string Collection identifier
* @param  $mode string Action mode
* @return string Form content
*/
function get_form_txt($values, $path_manage_action,  $id_action, $table, $module, $coll_id, $mode )
{
	$services = array();
	$db = new dbquery();
	$db->connect();

	// Gets the allowed entities for the redirection in the current basket
	if(!empty($_SESSION['current_basket']['redirect_services']))
	{
		$db->query("select * from ".$_SESSION['tablename']['bask_entity']." where entity_id in (".$_SESSION['current_basket']['redirect_services'].") and enabled= 'Y' order by entity_label");
		while($res = $db->fetch_object())
		{
			array_push($services, array( 'ID' => $res->entity_id, 'LABEL' => $db->show_string($res->entity_label)));
		}
	}
	// Gets the allowed users for the redirection in the current basket
	$users = array();
	if(!empty($_SESSION['current_basket']['redirect_users']) )
	{
		$db2 = new dbquery();
		$db2->connect();
		$db->query("select distinct uc.user_id, u.lastname, u.department from ".$_SESSION['tablename']['usergroup_content']." uc, ".$_SESSION['tablename']['users']." u where group_id in (".$_SESSION['current_basket']['redirect_users'].") and u.user_id = uc.user_id order by u.lastname asc");
		while($res = $db->fetch_object())
		{
			$db2->query("select lastname, firstname from ".$_SESSION['tablename']['users']." where user_id = '".$res->user_id."' and user_id <> ''");
			$res2 = $db2->fetch_object();
			array_push($users, array( 'ID' => $res->user_id, 'NOM' => $db->show_string($res2->lastname), "PRENOM" => $db->show_string($res2->firstname), "SERVICE" => $db->show_string($res->department)));
		}
	}
	// The form content is stored int the $frm_str var
	$frm_str = '<div id="frm_error_'.$id_action.'" class="error"></div>';
	$frm_str .= '<h2 class="title">'._REDIRECT_MAIL.' ';
	$values_str = '';
	for($i=0; $i < count($values);$i++)
	{
		$values_str .= $values[$i].', ';
	}
	$values_str = preg_replace('/, $/', '', $values_str);
	$frm_str .= $values_str;
	$frm_str .= '</h2><br/><br/>';
	if(!empty($_SESSION['current_basket']['redirect_services']))
	{
		$frm_str .= '<hr />';
		$frm_str .='<div id="form2">';
		$frm_str .= '<form name="frm_redirect_dep" id="frm_redirect_dep" method="post" class="forms" action="#">';
		$frm_str .= '<input type="hidden" name="chosen_action" id="chosen_action" value="end_action" />';
				$frm_str .='<p>';
					$frm_str .= '<label><b>'._REDIRECT_TO_OTHER_DEP.' :</b></label>';
					$frm_str .= '<select name="department" id="department" onchange="document.getElementById('."'list_diff_view').src='".$_SESSION['config']['businessappurl']."diff_list_view.php?serv_id='+this.options[this.selectedIndex].value;".'">';
						$frm_str .='<option value="">'._CHOOSE_DEPARTMENT.'</option>';
					   for($i=0; $i < count($services); $i++)
					   {
							$frm_str .='<option value="'.$services[$i]['ID'].'" >'.$db->show_string($services[$i]['LABEL']).'</option>';
					   }
					$frm_str .='</select>';
					$frm_str .=' <input type="button" name="redirect_dep" value="'._REDIRECT.'" id="redirect_dep" class="button" onclick="valid_action_form( \'frm_redirect_dep\', \''.$path_manage_action.'\', \''. $id_action.'\', \''.$values_str.'\', \''.$table.'\', \''.$module.'\', \''.$coll_id.'\', \''.$mode.'\');" />';
					$frm_str .='<div align="center">';
						$frm_str .='<iframe name="list_diff_view" id="list_diff_view" src="'.$_SESSION['config']['businessappurl'].'diff_list_view.php"  height="180" width="370" scrolling="auto"  frameborder="1"  ></iframe>';
				    $frm_str .='</div>';
				$frm_str .='</p>';
			$frm_str .='</form>';
		$frm_str .='</div>';
	}
	if(!empty($_SESSION['current_basket']['redirect_users']))
	{
		$frm_str .='<hr />';
			$frm_str .='<div id="form3">';
				$frm_str .= '<form name="frm_redirect_user" id="frm_redirect_user" method="post" class="forms" action="#">';
				$frm_str .= '<input type="hidden" name="chosen_action" id="chosen_action" value="end_action" />';
				$frm_str .='<p>';
					$frm_str .='<label><b>'._REDIRECT_TO_USER.' :</b></label>';
				    $frm_str .='<select name="user" id="user">';
						$frm_str .='<option value="">'._CHOOSE_USER2.'</option>';
					    for($i=0; $i < count($users); $i++)
					   {
						$frm_str .='<option value="'.$users[$i]['ID'].'">'.$users[$i]['NOM'].' '.$users[$i]['PRENOM'].'</option>';
					   }
					$frm_str .='</select>';
					$frm_str .=' <input type="button" name="redirect_user" id="redirect_user" value="'._REDIRECT.'" class="button" onclick="valid_action_form( \'frm_redirect_user\', \''.$path_manage_action.'\', \''. $id_action.'\', \''.$values_str.'\', \''.$table.'\', \''.$module.'\', \''.$coll_id.'\', \''.$mode.'\');"  />';
				$frm_str .='</p>';
			$frm_str .='</form>';
		$frm_str .='</div>';
	}
	$frm_str .='<hr />';
	$frm_str .='<div>';
			$frm_str .='<input type="button" name="cancel" id="cancel" class="button"  value="'._CANCEL.'" onclick="destroyModal(\'modal_'.$id_action.'\');"/>';
	$frm_str .='</div>';
	return addslashes($frm_str);
 }

/**
* Checks the form results
*
* @param  $form_id  string Identifier to form to check
* @param  $values  array Form values (User input)
* @return bool True if the values are correct, False otherwise
*/
 function check_form($form_id,$values)
 {
	if($form_id == 'frm_redirect_dep')
	{
		if(count($values) < 1)
		{
			$_SESSION['error'] = _MUST_CHOOSE_DEP;
			return false;
		}
		else
		{
			return true;
		}
	}
	else if($form_id == 'frm_redirect_user')
	{
		if(count($values) < 1)
		{
			$_SESSION['error'] = _MUST_CHOOSE_USER;
			return false;
		}
		else
		{
			return true;
		}
	}
	else
	{
		$_SESSION['error'] = _FORM_ERROR;
		return false;
	}
 }

/**
* Process the action form(All params must be declared, even if not used, to corresponds to the action management of the core)
*
* @param  $arr_id  array Contains the res_id to be modified
* @param  $history  string Not Used here
* @param  $id_action string Action identifier
* @param  $label_action string Action label
* @param  $status string New status
* @param  $coll_id string Collection identifier
* @param  $table string Database resource table
* @param  $values_form array Values of the form
* @return string Form content
*/
 function manage_form($arr_id, $history, $id_action, $label_action, $status,  $coll_id, $table, $values_form )
 {
	if(empty($values_form) || count($arr_id) < 1)
	{
		return false;
	}
	$db = new dbquery();
	$db->connect();

	for($j=0; $j<count($values_form); $j++)
	{
		if($values_form[$j]['ID'] == "department")
		{
			for($i=0; $i < count($arr_id); $i++)
			{
				$db2 = new dbquery();
				$db2->connect();
				$db->query("update ".$table." set destination = '".$db->protect_string_db($values_form[$j]['VALUE'])."', status = 'COU' where res_id = ".$arr_id[$i]);
				$db->query("delete from ".$_SESSION['tablename']['bask_listinstance']." where coll_id = '".$coll_id."' and res_id = ".$arr_id[$i]."");
				$db->query("select sequence, user_id from ".$_SESSION['tablename']['bask_listmodels']." where id = '".$db->protect_string_db($values_form[$j]['VALUE'])."' order by sequence asc ");
				while($line = $db->fetch_object())
				{
					$db2->query("insert into ".$_SESSION['tablename']['bask_listinstance']." values('".$coll_id."','".$arr_id[$i]."',".$line->sequence.",'".$line->user_id."','DOC')");
				}
			}
			$_SESSION['error'] = _REDIRECT_TO_DEP_OK;
			return true;
		}
		elseif($values_form[$j]['ID'] == "user")
		{
			for($i=0; $i < count($arr_id); $i++)
			{
				$db->query("update ".$table." set dest_user = '".$db->protect_string_db($values_form[$j]['VALUE'])."' where res_id = ".$arr_id[$i]);
				$db->query("delete from ".$_SESSION['tablename']['bask_listinstance']." where coll_id = '".$coll_id."' and res_id = ".$arr_id[$i]."");
				$db->query("insert into ".$_SESSION['tablename']['bask_listinstance']." values('".$coll_id."','".$arr_id[$i]."',0,'".$db->protect_string_db($values_form[$j]['VALUE'])."','DOC')");
			}
			$_SESSION['error'] = _REDIRECT_TO_USER_OK;
			return true;
		}
	}
	return false;
 }
?>