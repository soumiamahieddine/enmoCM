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
* @brief Group list
*
*
* @file
* @author  Claire Figueras  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$core_tools2 = new core_tools();
$core_tools2->test_admin('admin_groups', 'apps');
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=groups&admin=groups';
$page_label = _GROUPS_LIST;
$page_id = "list_groups";
$core_tools2->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
require_once("core/class/class_request.php");
require_once("apps/".$_SESSION['businessapps'][0]['appid']."/class".DIRECTORY_SEPARATOR."class_list_show.php");
$func = new functions();
$_SESSION['m_admin'] = array();
$select[$_SESSION['tablename']['usergroups']] = array();
array_push($select[$_SESSION['tablename']['usergroups']],"group_id","group_desc","enabled");
$what = "";
$where ="";
if(isset($_REQUEST['what']) && !empty($_REQUEST['what']))
{
	$what = $func->protect_string_db($_REQUEST['what']);
	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where = "group_desc ilike '".strtolower($what)."%' or group_id ilike '".strtoupper($what)."%' ";
	}
	else
	{
		$where = "group_desc like '".strtolower($what)."%' or group_id like '".strtoupper($what)."%' ";
	}
}
$list = new list_show();
$order = 'asc';
if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
{
	$order = trim($_REQUEST['order']);
}
$field = 'group_id';
if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field']))
{
	$field = trim($_REQUEST['order_field']);
}

$orderstr = $list->define_order($order, $field);

$request = new request;
$tab=$request->select($select,$where,$orderstr,$_SESSION['config']['databasetype']);
for ($i=0;$i<count($tab);$i++)
{
	for ($j=0;$j<count($tab[$i]);$j++)
	{
		foreach(array_keys($tab[$i][$j]) as $value)
		{
			if($tab[$i][$j][$value]=="group_id")
			{
				$tab[$i][$j]["group_id"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]= _ID;
				$tab[$i][$j]["size"]="18";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='group_id';
			}
			if($tab[$i][$j][$value]=="group_desc")
			{
				$tab[$i][$j]['value']=$request->show_string($tab[$i][$j]['value']);
				$tab[$i][$j]["group_desc"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_DESC;
				$tab[$i][$j]["size"]="30";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='group_desc';
			}
			if($tab[$i][$j][$value]=="enabled")
			{
				$tab[$i][$j]["enabled"]= $tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_STATUS;
				$tab[$i][$j]["size"]="6";
				$tab[$i][$j]["label_align"]="center";
				$tab[$i][$j]["align"]="center";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='enabled';
			}
		}
	}
}
$page_name = "groups";
$page_name_up = "group_up";
$page_name_del = "group_del";
$page_name_val= "group_allow";
$page_name_ban = "group_ban";
$page_name_add = "group_add";
$label_add = _GROUP_ADDITION;
$_SESSION['m_admin']['load_security']  = true;
$_SESSION['m_admin']['load_services'] = true;
$_SESSION['m_admin']['init'] = true;
$title = _GROUPS_LIST." : ".$i." "._GROUPS;
$autoCompletionArray = array();
$autoCompletionArray["list_script_url"] = $_SESSION['config']['businessappurl']."admin/groups/groups_list_by_name.php";
$autoCompletionArray["number_to_begin"] = 1;
$list->admin_list($tab, $i, $title, 'group_id','groups','groups', 'group_id', true, $page_name_up, $page_name_val, $page_name_ban, $page_name_del, $page_name_add, $label_add, false, false, _ALL_GROUPS, _GROUP, $_SESSION['config']['img'].'/manage_groupe_b.gif', false, true, false, true, "", true, $autoCompletionArray);
$_SESSION['m_admin']['groups'] = array();
$_SESSION['m_admin']['groups']['GroupId'] = "";
$_SESSION['m_admin']['groups']['desc'] = "";
$_SESSION['m_admin']['groups']['admin'] = "";
?>
