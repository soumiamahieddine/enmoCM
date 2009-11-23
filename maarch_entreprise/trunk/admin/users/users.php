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
* @brief   Users list
*
*
* @file
* @author  Claire Figueras  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$core_tools2 = new core_tools();
$core_tools2->test_admin('admin_users', 'apps');
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=users&admin=users';
$page_label = _USERS_LIST;
$page_id = "list_users";
$core_tools2->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['businessapps'][0]['appid'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
$func = new functions();
$_SESSION['m_admin'] = array();
$select[$_SESSION['tablename']['users']] = array();
array_push($select[$_SESSION['tablename']['users']],"user_id","lastname","firstname","enabled",'status',"mail" );
$what = "";
$where = " status <> 'DEL'";
if(isset($_REQUEST['what']) && !empty($_REQUEST['what']))
{
	$what = $func->protect_string_db($_REQUEST['what']);
	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where .= "and( lastname ilike '".strtolower($what)."%' or lastname ilike '".strtoupper($what)."%' )";
	}
	else
	{
		$where .= "and( lastname like '".strtolower($what)."%' or lastname like '".strtoupper($what)."%' )";
	}
}
$list = new list_show();
$order = 'asc';
if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
{
	$order = trim($_REQUEST['order']);
}
$field = 'lastname';
if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field']))
{
	$field = trim($_REQUEST['order_field']);
}

$orderstr = $list->define_order($order, $field);

$request= new request;
$tab=$request->select($select,$where,$orderstr,$_SESSION['config']['databasetype']);
for ($i=0;$i<count($tab);$i++)
{
	for ($j=0;$j<count($tab[$i]);$j++)
	{
		foreach(array_keys($tab[$i][$j]) as $value)
		{
			if($tab[$i][$j][$value]=="user_id")
			{
				$tab[$i][$j]["user_id"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]= _ID;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='user_id';
			}
			if($tab[$i][$j][$value]=="lastname")
			{
				$tab[$i][$j]['value']= $request->show_string($tab[$i][$j]['value']);
				$tab[$i][$j]["lastname"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_LASTNAME;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='lastname';
			}
			if($tab[$i][$j][$value]=="firstname")
			{
				$tab[$i][$j]['value']= $request->show_string($tab[$i][$j]['value']);
				$tab[$i][$j]["firstname"]= $tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_FIRSTNAME;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='firstname';
			}
			if($tab[$i][$j][$value]=="enabled")
			{
				$tab[$i][$j]["enabled"]= $tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_STATUS;
				$tab[$i][$j]["size"]="3";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="center";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='enabled';
			}
			if($tab[$i][$j][$value]=="mail")
			{
				$tab[$i][$j]["mail"] = $tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_MAIL;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='mail';
			}
			if($tab[$i][$j][$value]=="status")
			{
				if($tab[$i][$j]['value'] == "ABS")
				{
					$tab[$i][$j]['value'] = "<em>("._MISSING.")</em>";
				}
				else
				{
					$tab[$i][$j]['value'] = '';
				}
				$tab[$i][$j]["status"] = $tab[$i][$j]['value'];
				$tab[$i][$j]["label"]='';
				$tab[$i][$j]["size"]="5";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='';
			}
		}
	}
}

$_SESSION['m_admin']['load_group']  = true;
$_SESSION['m_admin']['init'] = true;
$_SESSION['service_tag'] = 'users_list_init';
echo $core_tools2->execute_modules_services($_SESSION['modules_services'], 'users_list_init', "include");
$page_name = "users";
$page_name_up = "users_up";
$page_name_ban = "users_ban";
$page_name_del = "users_del";
$page_name_val = "users_allow";
$page_name_add = "users_add";
$label_add = _ADD_USER;
$title = _USERS_LIST." : ".$i." "._USERS;

$autoCompletionArray = array();
$autoCompletionArray["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&admin=users&page=users_list_by_name";
$autoCompletionArray["number_to_begin"] = 1;
$list->admin_list($tab, $i, $title,'user_id','users', 'users','user_id', true, $page_name_up, $page_name_val, $page_name_ban, $page_name_del, $page_name_add, $label_add, false, false, _ALL_USERS, _USER, $_SESSION['config']['img'].'/manage_users_b.gif', false, true, false, true, "", true, $autoCompletionArray);

?>
