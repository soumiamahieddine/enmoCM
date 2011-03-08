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

/**
* @brief   Basket administration : list of existing baskets
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/

$_SESSION['m_admin'] = array();
$admin = new core_tools();
$admin->test_admin('admin_baskets', 'basket');

 /****************Management of the location bar  ************/
$init = false;
if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == 'true') {
    $init = true;
}
$level = '';
if (isset($_REQUEST['level']) && ($_REQUEST['level'] == 2
    || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4
    || $_REQUEST['level'] == 1)) {
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=basket&module=basket';
$page_label = _BASKETS_LIST;
$page_id = "basket";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
 require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
 require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");

 $func = new functions();

$select[$_SESSION['tablename']['bask_baskets']] = array();
array_push($select[$_SESSION['tablename']['bask_baskets']],"basket_id","basket_name" ,"basket_desc","is_generic", "enabled");

$what = "";
$where ="";
if(isset($_REQUEST['what']) && !empty($_REQUEST['what']))
{
    $what = addslashes($func->wash($_REQUEST['what'], "nick", "", "no"));
    if($_SESSION['config']['databasetype'] == "POSTGRESQL")
    {
        $where .= "  ( basket_id ilike '".$func->protect_string_db($what,$_SESSION['config']['databasetype'])."%') ";
    }
    else
    {
        $where .= " (basket_id like '".$func->protect_string_db($what,$_SESSION['config']['databasetype'])."%') ";
    }
}
$list = new list_show();
$order = 'asc';
if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
{
    $order = trim($_REQUEST['order']);
}
$field = 'basket_name';
if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field']))
{
    $field = trim($_REQUEST['order_field']);
}

$orderstr = $list->define_order($order, $field);

$request= new request;
$tab=$request->select($select,$where,$orderstr ,$_SESSION['config']['databasetype']);

for ($i=0;$i<count($tab);$i++)
{
    for ($j=0;$j<count($tab[$i]);$j++)
    {
        foreach(array_keys($tab[$i][$j]) as $value)
        {
            if($tab[$i][$j][$value]=="basket_id")
            {
                $tab[$i][$j]["basket_id"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]= _ID;
                $tab[$i][$j]["size"]="15";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='basket_id';
            }
            if($tab[$i][$j][$value]=="basket_name")
            {
                $tab[$i][$j]["value"]=$request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["basket_name"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]=_BASKET;
                $tab[$i][$j]["size"]="15";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='basket_name';
            }
            if($tab[$i][$j][$value]=="basket_desc")
            {
                $tab[$i][$j]["value"]=$request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["basket_desc"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]=_DESC;
                $tab[$i][$j]["size"]="15";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='basket_desc';
            }
            if($tab[$i][$j][$value]=="is_generic")
            {
                $tab[$i][$j]["is_generic"]= $tab[$i][$j]['value'];
                $tab[$i][$j]["show"]=false;
                $tab[$i][$j]["order"]='is_generic';
            }
            if($tab[$i][$j][$value]=="enabled")
            {
                $tab[$i][$j]["enabled"]= $tab[$i][$j]['value'];
                $tab[$i][$j]["label"]=_STATUS;
                $tab[$i][$j]["size"]="6";
                $tab[$i][$j]["label_align"]="center";
                $tab[$i][$j]["align"]="center";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=false;
                $tab[$i][$j]["order"]='enabled';
            }
        }
    }
}

$page_name = "basket";
$page_name_up = "basket_up";
$page_name_del = "basket_del";
$page_name_val= "";
$page_name_ban = "";
$page_name_add = "basket_add";
$label_add = _BASKET_ADDITION;

$_SESSION['m_admin']['load_security']  = true;
$_SESSION['m_admin']['init'] = true;

$_SESSION['m_admin']['basket']['basketId'] = "";
$_SESSION['m_admin']['basket']['desc'] = "";
$_SESSION['m_admin']['basket']['name'] = "";
$_SESSION['m_admin']['basket']['clause'] = "";
$_SESSION['m_admin']['basket']['table'] ="";
$_SESSION['m_admin']['basket']['is_generic'] ="";
$_SESSION['m_admin']['basket']['nbdays'] ="";
$_SESSION['m_admin']['basket']['groups'] = array();
$_SESSION['m_admin']['non_generic_basket'] = array();

$request->query("select basket_id, basket_name from ".$_SESSION['tablename']['bask_baskets']." where is_generic = 'N' ");
while($line = $request->fetch_object())
{
    array_push($_SESSION['m_admin']['non_generic_basket'], array("BASKET_ID" => $line->basket_id, "BASKET_NAME" => $request->show_string($line->basket_name)));
}
$request->query("select group_id from ".$_SESSION['tablename']['usergroups']." where enabled = 'Y' order by group_desc");

$_SESSION['groups'] = array();
$line = "";

while($line = $request->fetch_object())
{
    array_push($_SESSION['groups'],  $line->group_id);
}
$_SESSION['m_admin']['load_groupbasket'] = true;

$_SESSION['m_admin']['basket']['all_actions'] = array();
$request->query("select id, label_action, keyword, create_id from ".$_SESSION['tablename']['actions']." where enabled = 'Y' order by label_action");

while($line = $request->fetch_object())
{
    if($admin->is_action_defined($line->id))
    {
        array_push($_SESSION['m_admin']['basket']['all_actions'] ,array('ID' => $line->id, 'LABEL' => $line->label_action, 'KEYWORD' => $line->keyword, 'CREATE_ID' => $line->create_id));
    }
}
$title = _BASKET_LIST." : ".$i." "._BASKETS;


$list->admin_list($tab, $i, $title, 'basket_id','basket','basket','basket_id', true, $page_name_up, $page_name_val, $page_name_ban, $page_name_del, $page_name_add, $label_add, FALSE, FALSE, _ALL_BASKETS, _BASKET,$_SESSION['config']['businessappurl'].'static.php?module=basket&filename=picto_basket_b.gif', TRUE ,true, false, true, $what);

?>
