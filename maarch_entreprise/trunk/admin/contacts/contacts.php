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
* @brief  contacts list
*
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/
$admin = new core_tools();
$admin->test_admin('admin_contacts', 'apps');
$func = new functions();
$_SESSION['m_admin']['contact'] = array();
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=contacts&admin=contacts';
$page_label = _CONTACTS_LIST;
$page_id = "contacts";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_list_show.php");
$select[$_SESSION['tablename']['contacts']] = array();
array_push($select[$_SESSION['tablename']['contacts']],"contact_id", "society","lastname","firstname", 'user_id');
$what = "";
//$where =" (user_id is null or user_id = '') and enabled = 'Y' ";
$where ="  enabled = 'Y' ";
if(isset($_REQUEST['what']))
{
	$what = $func->protect_string_db($func->wash($_REQUEST['what'], "alphanum", "", "no"));
	if($_SESSION['config']['databasetype'] == "POSTGRESQL")
	{
		$where .= " and (lastname ilike '".$func->protect_string_db($what,$_SESSION['config']['databasetype'])."%'  or society ilike '".$func->protect_string_db($what,$_SESSION['config']['databasetype'])."%' ) ";
	}
	else
	{
		$where .= " and (lastname like '".$func->protect_string_db($what,$_SESSION['config']['databasetype'])."%'  or society like '".$func->protect_string_db($what,$_SESSION['config']['databasetype'])."%' ) ";
	}
}
$list = new list_show();
$order = 'asc';
if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
{
	$order = trim($_REQUEST['order']);
}
$field = 'lastname, society';
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
			if($tab[$i][$j][$value]=="contact_id")
			{
				$tab[$i][$j]["contact_id"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]= _ID;
				$tab[$i][$j]["size"]="18";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]= "contact_id";
			}
			if($tab[$i][$j][$value]=="society")
			{
				$tab[$i][$j]['value']=$request->show_string($tab[$i][$j]['value']);
				$tab[$i][$j]["society"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_SOCIETY;
				$tab[$i][$j]["size"]="15";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]= "society";
			}
			if($tab[$i][$j][$value]=="lastname")
			{
				$tab[$i][$j]['value']=$request->show_string($tab[$i][$j]['value']);
				$tab[$i][$j]["lastname"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_LASTNAME;
				$tab[$i][$j]["size"]="15";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]= "lastname";
			}
			if($tab[$i][$j][$value]=="firstname")
			{
				$tab[$i][$j]["firstname"]= $tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_FIRSTNAME;
				$tab[$i][$j]["size"]="15";
				$tab[$i][$j]["label_align"]="center";
				$tab[$i][$j]["align"]="center";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]= "firstname";
			}
			if($tab[$i][$j][$value]=="user_id")
			{
				$tab[$i][$j]["user_id"]= $tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_OWNER;
				$tab[$i][$j]["size"]="15";
				$tab[$i][$j]["label_align"]="center";
				$tab[$i][$j]["align"]="center";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]= "user_id";
			}

		}
	}
}
$page_name = "contacts";
$page_name_up = "contact_up";
$page_name_del = "contact_del";
$page_name_val= "";
$page_name_ban = "";
$page_name_add = "contact_add";
$label_add = _CONTACT_ADDITION;
$_SESSION['m_admin']['init'] = true;
$title = _CONTACTS_LIST." : ".$i." "._CONTACTS;
$autoCompletionArray = array();
$autoCompletionArray["list_script_url"] = $_SESSION['config']['businessappurl']."admin/contacts/contact_list_by_name.php";
$autoCompletionArray["number_to_begin"] = 1;

$list->admin_list($tab, $i, $title, 'contact_id','contacts','contacts','contact_id', true, $page_name_up, $page_name_val, $page_name_ban, $page_name_del, $page_name_add, $label_add, FALSE, FALSE, _ALL_CONTACTS, _CONTACT, $_SESSION['config']['img'].'/manage_contact_b.gif', false, true, false, true, $what, true, $autoCompletionArray);
$_SESSION['m_admin']['contacts'] = array();
$_SESSION['m_admin']['contacts']['id'] = "";
$_SESSION['m_admin']['contacts']['title'] = "";
$_SESSION['m_admin']['contacts']['lastname'] = "";
$_SESSION['m_admin']['contacts']['firtsname'] = "";
$_SESSION['m_admin']['contacts']['society'] = "";
$_SESSION['m_admin']['contacts']['function'] = "";
$_SESSION['m_admin']['contacts']['address_num'] = "";
$_SESSION['m_admin']['contacts']['address_street'] = "";
$_SESSION['m_admin']['contacts']['address_complement'] = "";
$_SESSION['m_admin']['contacts']['address_town'] = "";
$_SESSION['m_admin']['contacts']['address_postal_code'] = "";
$_SESSION['m_admin']['contacts']['address_country'] = "";
$_SESSION['m_admin']['contacts']['email'] = "";
$_SESSION['m_admin']['contacts']['phone'] = "";
$_SESSION['m_admin']['contacts']['other_data'] = "";
$_SESSION['m_admin']['contacts']['is_corporate_person'] = "";
?>
