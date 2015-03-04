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
* @brief List the documents types
*
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$admin = new core_tools();
$admin->test_admin('admin_architecture', 'apps');
/****************Management of the location bar  ************/
$init = false;
if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true") {
    $init = true;
}
$level = "";
if (isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 
	|| $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 
	|| $_REQUEST['level'] == 1)
) {
    $level = $_REQUEST['level'];
}
$pagePath = $_SESSION['config']['businessappurl'] . 'index.php?page=types';
$pageLabel = _DOCTYPES_LIST2;
$pageId = "types";
$admin->manage_location_bar($pagePath, $pageLabel, $pageId, $init, $level);
/***********************************************************/
require_once "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
	."class_request.php";
require_once "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
	. DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
	. "class_list_show.php";
$func = new functions();
$_SESSION['m_admin'] = array();
$select[$_SESSION['tablename']['doctypes']] = array();
array_push(
	$select[$_SESSION['tablename']['doctypes']],
	"type_id",
	"description"
);
$what = "";
$where = " enabled = 'Y' ";
if (isset($_REQUEST['what']) && ! empty($_REQUEST['what'])) {
    $what = $func->protect_string_db($_REQUEST['what']);
    $where .= " and lower(description) like lower('" .$what. "%') ";
}
$list = new list_show();
$order = 'asc';
if (isset($_REQUEST['order']) && ! empty($_REQUEST['order'])) {
    $order = trim($_REQUEST['order']);
}
$field = 'description';
if (isset($_REQUEST['order_field']) && ! empty($_REQUEST['order_field'])) {
    $field = trim($_REQUEST['order_field']);
}

$orderstr = $list->define_order($order, $field);
$request = new request;
$tab = $request->select(
	$select, $where, $orderstr, $_SESSION['config']['databasetype']
);
for ($i = 0; $i < count($tab); $i ++) {
    for ($j = 0; $j < count($tab[$i]); $j ++) {
        foreach (array_keys($tab[$i][$j]) as $value) {
            if ($tab[$i][$j][$value] == "type_id") {
                $tab[$i][$j]["type_id"] = $tab[$i][$j]['value'];
                $tab[$i][$j]["label"] = _ID;
                $tab[$i][$j]["size"] = "20";
                $tab[$i][$j]["label_align"] = "left";
                $tab[$i][$j]["align"] = "left";
                $tab[$i][$j]["valign"] = "bottom";
                $tab[$i][$j]["show"] = true;
                $tab[$i][$j]["order"] = 'type_id';
            }
            if ($tab[$i][$j][$value] == "description") {
                $tab[$i][$j]['value'] = $func->show_string(
                	$tab[$i][$j]['value']
                );
                $tab[$i][$j]["description"] = $tab[$i][$j]['value'];
                $tab[$i][$j]["label"] = _DESC;
                $tab[$i][$j]["size"] = "70";
                $tab[$i][$j]["label_align"] = "left";
                $tab[$i][$j]["align"] = "left";
                $tab[$i][$j]["valign"] = "bottom";
                $tab[$i][$j]["show"] = true;
                $tab[$i][$j]["order"] = 'description';
            }
        }
    }
}
$pageName = "types";
$pageNameUp = "types_up";
$pageNameAdd = "types_add";
$pageNameDel = "types_del";
$pageNameVal = "";
$tableName = $_SESSION['tablename']['doctypes'];
$pageNameBan = "";
$addLabel = _ADD_DOCTYPE;
$_SESSION['m_admin']['load_security']  = true;
$_SESSION['m_admin']['init'] = true;
$_SESSION['m_admin']['doctypes'] = array();
$_SESSION['sous_dossiers'] = array();
$request->query(
	"select * from " . $_SESSION['tablename']['doctypes_second_level']
	. " where enabled = 'Y'"
);
while ($res = $request->fetch_object()) {
    array_push(
    	$_SESSION['sous_dossiers'], 
    	array(
    		'ID' => $res->doctypes_second_level_id, 
    		'LABEL' => $res->doctypes_second_level_label,
    		'STYLE' => $res->css_style,
    	)
    );
}
function cmp($a, $b)
{
    return strcmp($a["LABEL"], $b["LABEL"]);
}
usort($_SESSION['sous_dossiers'], "cmp");

$title = _DOCTYPES_LIST . " : " . $i . " " . _TYPES;
$autoCompletionArray = array();
$autoCompletionArray["list_script_url"] = $_SESSION['config']['businessappurl']
	. "index.php?display=true&page=types_list_by_name";
$autoCompletionArray["number_to_begin"] = 1;

$list->admin_list(
	$tab, $i, $title, 'type_id', 'types', 'architecture/types', 'type_id', true, 
	$pageNameUp, $pageNameVal, $pageNameBan, $pageNameDel, $pageNameAdd, 
	$addLabel, false, false, _ALL_DOCTYPES, _TYPE, 
	'files-o', false, true, true, true, "", 
	true, $autoCompletionArray
);
