<?php
/*
*
*    Copyright 2008,2012 Maarch
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
*
*   @author  Cyril Vazquez <dev@maarch.org>
*/

$admin = new core_tools();
$admin->test_admin('admin_listmodel_types', 'entities');
$_SESSION['m_admin']= array();
/****************Management of the location bar  ************/
$init = false;
if(isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true")
{
    $init = true;
}
$level = "";
if(isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1))
{
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=admin_listmodel_types&module=entities';
$page_label = _LISTMODEL_TYPES;
$page_id = "admin_listmodel_types";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/

require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("modules/entities/entities_tables.php");

$func = new functions();
$request = new request;

$what = '';
$where = '';

$list = new list_show();

if(isset($_REQUEST['what']) && !empty($_REQUEST['what']))
{
    $what = $func->protect_string_db($_REQUEST['what']);
	$where = " lower(listmodel_type_label) like lower('".$what."%')";
}

$order = 'asc';
if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
{
    $order = trim($_REQUEST['order']);
}
$field = 'listmodel_type_id';
if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field']))
{
    $field = trim($_REQUEST['order_field']);
}

$orderstr = $list->define_order($order, $field);

$select[ENT_LISTMODEL_TYPES] = array();
array_push($select[ENT_LISTMODEL_TYPES], "listmodel_type_id", "listmodel_type_label");

$tab = $request->select($select, $where, $orderstr, $_SESSION['config']['databasetype']);
//$request->show();

for ($i=0;$i<count($tab);$i++)
{
    for ($j=0;$j<count($tab[$i]);$j++)
    {
        foreach(array_keys($tab[$i][$j]) as $value)
        {
            if($tab[$i][$j][$value]=="listmodel_type_id")
            {
                $tab[$i][$j]["listmodel_type_id"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]= _ID;
                $tab[$i][$j]["size"]="18";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["order"]=$tab[$i][$j][$value];
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
            }

            if($tab[$i][$j][$value]=="listmodel_type_label")
            {
                $tab[$i][$j]['value']=$request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["listmodel_type_label"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]=_LISTMODEL_TYPE_LABEL;
                $tab[$i][$j]["size"]="25";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["order"]=$tab[$i][$j][$value];
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
            }
        }
    }
}

$page_name = "admin_listmodel_types";
$page_name_up = "admin_listmodel_type&mode=up";
$page_name_add = "admin_listmodel_type&mode=add";
$page_name_del = "admin_listmodel_type&mode=del";
$label_add = _ADD_LISTMODEL_TYPE;
$_SESSION['m_admin']['init'] = true;

$title = _LISTMODEL_TYPES." : ".$i." "._LISTMODEL_TYPE;
$autoCompletionArray = false;//array();

$list->admin_list($tab, $i, $title, 'listmodel_type_id','admin_listmodel_types','entities', 'listmodel_type_id', true, $page_name_up, $page_name_val, $page_name_ban, $page_name_del, $page_name_add, $label_add, false, false, _ALL_LISTMODEL_TYPES, _LISTMODEL_TYPE, $_SESSION['config']['businessappurl'].'static.php?module=entities&filename=manage_entities_b.gif', true, true, false, true, "", true, $autoCompletionArray, false, true);
?>
