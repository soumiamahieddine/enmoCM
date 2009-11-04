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
* @brief   Manage core actions
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/
session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");

$db = new dbquery();
$core = new core_tools();
$core->load_lang();
$res_action = array();

/**
* Puts the values that are in a string into an array.
* $$ field separator, # field_name / value separator
*
* @param $val string Values to split
* @return array Values in array
**/
function get_values_in_array($val)
{
	$tab = explode('$$',$val);
	$values = array();
	for($i=0; $i<count($tab);$i++)
	{
		$tmp = explode('#', $tab[$i]);
		array_push($values, array('ID' => $tmp[0], 'VALUE' => trim($tmp[1])));
	}
	return $values;
}

// Form validation
if($_POST['req'] == 'valid_form' && !empty($_POST['action_id']) && isset($_POST['action_id']) && !empty($_POST['form_to_check'])&& isset($_POST['form_to_check']))
{
	$id_action = $_POST['action_id'];
	$db->connect();
	// Gets the action informations from the database
	$db->query("select * from ".$_SESSION['tablename']['actions']." where id = ".$id_action);

	if($db->nb_result() < 1)
	{
		$_SESSION['action_error'] = _ACTION_NOT_IN_DB;
		echo "{status : 5, error_txt : '".addslashes($_SESSION['action_error'])."'}";
		exit();
	}

	$res = $db->fetch_object();
	$label_action = $res->label_action;
	$status = $res->id_status;
	$action_page = $res->action_page;
	$bool_history = $res->history;
	$create_id = $res->create_id;

		//No script defined for this action
	if($action_page == '')
	{
		$_SESSION['action_error'] = _ACTION_NOT_IN_DB;
		echo "{status : 5, error_txt : '".addslashes($_SESSION['action_error'])."'}";
		exit();
	}

	$path_action_page = $core->get_path_action_page($action_page);
	// Invalid path to script
	if(!file_exists($path_action_page))
	{
		$_SESSION['action_error'] = $label_action.' '._ACTION_PAGE_MISSING;
		echo "{status : 8, error_txt : '".addslashes($_SESSION['action_error'])."'}";
		exit();
	}

	// If no error, includes script file and checks the form
	include($path_action_page);
	$frm_error = check_form(trim($_POST['form_to_check']),get_values_in_array($_POST['form_values']));
	if($frm_error == false)
	{
		echo "{status : 1, error_txt : '".addslashes($_SESSION['action_error'])."'}";
		exit();
	}
	else
	{
		if($create_id == 'N')
		{
			echo "{status : 0, error_txt : '".addslashes($_SESSION['action_error'])."', page_result : '', manage_form_now : false}";
		}
		else
		{
			echo "{status : 0, error_txt : '".addslashes($_SESSION['action_error'])."', page_result : '', manage_form_now : true}";
		}
		exit();
	}
}
// Post variables error
else if(empty($_POST['values']) || !isset($_POST['action_id']) || empty($_POST['action_id']) ||
($_POST['mode'] <> 'mass' && $_POST['mode'] <> 'page')  || empty($_POST['table'])
|| empty($_POST['coll_id']) || empty($_POST['module']) || ($_POST['req'] <> 'first_request' && $_POST['req'] <> 'second_request'))
{
	$tmp = 'values : '.$_POST['values'].', action_id : '.$_POST['action_id'].', mode : '. $_POST['mode'].', table : '.$_POST['table'].', coll_id : '.$_POST['coll_id'].', module : '.$_POST['module'].', req : '.$_POST['req'];
	$_SESSION['action_error'] = $tmp._AJAX_PARAM_ERROR;
	echo "{status : 1, error_txt : '".$id_action.addslashes($_SESSION['action_error'])."'}";
	exit();
}
else
{
	// Puts the res_id into an array
	$arr_id = explode(',', $_POST['values']);
	$id_action = $_POST['action_id'];
	$db->connect();
	// Gets the action informations from the database
	$db->query("select * from ".$_SESSION['tablename']['actions']." where id = ".$id_action);

	if($db->nb_result() < 1)
	{
		$_SESSION['action_error'] = _ACTION_NOT_IN_DB;
		echo "{status : 5, error_txt : '".addslashes($_SESSION['action_error'])."'}";
		exit();
	}

	$res = $db->fetch_object();
	$label_action = $res->label_action;
	$status = $res->id_status;
	$action_page = $res->action_page;
	$bool_history = $res->history;

	//No script defined for this action
	if($action_page == '')
	{
		//If second request : Error
		if($_POST['req'] == 'second_request')
		{
			$_SESSION['action_error'] = _ACTION_NOT_IN_DB;
			echo "{status : 5, error_txt : '".addslashes($_SESSION['action_error'])."'}";
			exit();
		}

		//If no status defined in the action file , error
		if($status == '' || $status == 'NONE')
		{
			$_SESSION['action_error'] = $label_action.' : '._ERROR_PARAM_ACTION;
			echo "{status : 6, error_txt : '".addslashes($_SESSION['action_error'])."'}";
			exit();
		}

		// Update the status
		$result = '';
		for($i=0; $i<count($arr_id );$i++)
		{
			$arr_id[$i] = str_replace('#', '', $arr_id[$i]);
			$result .= $arr_id[$i].'#';
			$query_str = "update ".$_POST['table']. " set status = '".$status."' where res_id = ".$arr_id[$i];
			$req = $db->query($query_str, true);
			if(!$req)
			{
				$_SESSION['action_error'] = _SQL_ERROR.' : '.$query_str;
				echo "{status : 7, error_txt : '".addslashes($label_action.' : '.$_SESSION['action_error'])."'}";
				exit();
			}
		}
		$res_action = array('result' => $result, 'history_msg' => '');
		$_SESSION['action_error'] = _ACTION_DONE.' : '.$label_action;
		echo "{status : 0, error_txt : '".addslashes($_SESSION['action_error']).", status : ".$status.", ".$_POST['values']."', page_result	: ''}";

	}
	// There is a script for the action
	else
	{
		$path_action_page = $core->get_path_action_page($action_page);

		// Invalid path to the action script
		if(!file_exists($path_action_page))
		{
			$_SESSION['action_error'] = $label_action.' '._ACTION_PAGE_MISSING.$path_action_page;
			echo "{status : 8, error_txt : '".addslashes($_SESSION['action_error'])."'}";
			exit();
		}

		// Include the action file
		include($path_action_page);
		if($_POST['req'] == 'first_request' && in_array('form', $etapes))
		{
			$frm_test = get_form_txt($arr_id, $_SESSION['config']['coreurl'].'core/manage_action.php', $id_action, $_POST['table'],$_POST['module'], $_POST['coll_id'],  $_POST['mode'] );
			echo "{status : 3, form_content : '".$frm_test."', height : '".$frm_height."', width : '".$frm_width."', 'mode_frm' : '".$mode_form."'}";
			exit();
		}
		elseif( $_POST['req'] == 'first_request' && $confirm == true)
		{
			echo "{status : 2, confirm_content : '".addslashes(_ACTION_CONFIRM." ".$label_action)."', validate : '"._VALIDATE."', cancel : '"._CANCEL."', label_action : '".addslashes($label_action)."'}";
			exit();
		}
		else
		{
			$_SESSION['action_error'] = $label_action.' : '._ERROR_SCRIPT;
			for($i=0; $i<count($etapes);$i++)
			{
				if( function_exists('manage_'.$etapes[$i]))
				{
					try
					{
						if($_POST['req'] == 'second_request')
						{
							$res_action = call_user_func('manage_'.$etapes[$i],$arr_id, $bool_history, $id_action, $label_action, $status, $_POST['coll_id'], $_POST['table'], get_values_in_array($_POST['form_values'])  );
						}
						else
						{
							$res_action = call_user_func('manage_'.$etapes[$i],$arr_id, $bool_history, $id_action, $label_action, $status, $_POST['coll_id'], $_POST['table']);
						}
					}
					catch(Exception $e)
					{
						echo "{status : 9, error_txt : '".addslashes($_SESSION['action_error'])."'}";
						exit();
					}
				}
				else
				{
					echo "{status : 9, error_txt : '".addslashes($_SESSION['action_error'])."'}";
					exit();
				}
			}
			if($res_action == false)
			{
				echo "{status : 9, error_txt : '".addslashes($_SESSION['action_error'])."'}";
				exit();
			}
			$comp = ", page_result	: ''";
			if(isset($res_action['page_result']) && !empty($res_action['page_result']))
			{
				$comp = ", page_result	: '".$res_action['page_result']."'";
			}
			$_SESSION['action_error'] = _ACTION_DONE.' : '.$label_action;
			echo "{status : 0, error_txt : '".addslashes($_SESSION['action_error'])."'".$comp.", result_id : '".$res_action['result']."'}";
		}
	}
	// Save action in history if needed
	if($bool_history=='Y')
	{
		require_once($_SESSION['pathtocoreclass']."class_history.php");
		$hist = new history();
		$arr_res = explode('#', $res_action['result']);
		for($i=0; $i<count($arr_res );$i++)
		{
			if(!empty($arr_res[$i]))
			{
				$what = $label_action.'('._NUM.$arr_res[$i].') ';
				if(isset($res_action['history_msg']) && !empty($res_action['history_msg']))
				{
					$what .= $res_action['history_msg'];
				}
				$hist->add($_POST['table'],$arr_res[$i],'ACTION#'.$id_action,$what, $_SESSION['config']['databasetype'], $_POST['module']);
			}
		}
	}
	exit();
}
?>
