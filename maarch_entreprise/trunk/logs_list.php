<?php
/*
*
*    Copyright 2008-2013 Maarch
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
* @brief   Displays the logs list in the following baskets 
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
require_once('core/class/class_request.php');
require_once('core/class/class_security.php');
require_once('core/class/class_manage_status.php');
require_once('apps/maarch_entreprise/class/class_list_show.php');
require_once('modules/basket/class/class_modules_tools.php');

$security = new security();
$core_tools = new core_tools();
$request = new request();
$template_to_use = '';

$bask = new basket();
if (!empty($_REQUEST['id'])) {
    $bask->load_current_basket(trim($_REQUEST['id']), 'frame');
}
if (!empty($_SESSION['current_basket']['view'])) {
    $table = $_SESSION['current_basket']['view'];
} else {
    $table = $_SESSION['current_basket']['table'];
}
$_SESSION['collection_id_choice'] = $_SESSION['current_basket']['coll_id'];
$select[$table]= array();
$where = $_SESSION['current_basket']['clause'];
array_push($select[$table], 'res_id',  'creation_date');
$order = '';
if (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) {
    $order = trim($_REQUEST['order']);
} else {
    $order = 'asc';
}
$order_field = '';
if (isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field'])) {
    $order_field = trim($_REQUEST['order_field']);
} else {
    $order_field = 'creation_date';
}
$list=new list_show();
$orderstr = $list->define_order($order, $order_field);
$bask->connect();
$do_actions_arr = array();

if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == 'searching') {
    $where = $_SESSION['searching']['where_request'] . ' '. $where;
}

$tab = $request->select(
    $select,
    $where,
    $orderstr,
    $_SESSION['config']['databasetype'], 
    '1000', 
    false, 
    '', 
    '', 
    '', 
    false
);
//$request->show();
//###################
for ($i=0;$i<count($tab);$i++) {
    for ($j=0;$j<count($tab[$i]);$j++) {
        foreach (array_keys($tab[$i][$j]) as $value) {
            if ($tab[$i][$j][$value]=='res_id') {
                $tab[$i][$j]['res_id']=$tab[$i][$j]['value'];
                $tab[$i][$j]['label']=_GED_NUM;
                $tab[$i][$j]['size']='4';
                $tab[$i][$j]['label_align']='left';
                $tab[$i][$j]['align']='left';
                $tab[$i][$j]['valign']='bottom';
                $tab[$i][$j]['show']=true;
                $tab[$i][$j]['order']='res_id';
            }
            if ($tab[$i][$j][$value]=='creation_date') {
                $tab[$i][$j]['value']=$core_tools->format_date_db(
                    $tab[$i][$j]['value'], false
                );
                $tab[$i][$j]['label']=_CREATION_DATE;
                $tab[$i][$j]['size']='10';
                $tab[$i][$j]['label_align']='left';
                $tab[$i][$j]['align']='left';
                $tab[$i][$j]['valign']='bottom';
                $tab[$i][$j]['show']=true;
                $tab[$i][$j]['order']='creation_date';
            }
        }
    }
}

$i = count($tab);
$title = _RESULTS.' : '.$i.' '._FOUND_LOGS;
//$request->show_array($tab);
$_SESSION['origin'] = 'basket';
$_SESSION['collection_id_choice'] = $_SESSION['current_basket']['coll_id'];

$details = 'details&dir=indexing_searching';

$param_list = array(
    'values' => $tab,
    'title' => $title,
    'key' => 'res_id',
    'page_name' => 'view_baskets&module=basket&baskets=' 
        . $_SESSION['current_basket']['id'].'&origin='.$_REQUEST['origin'],
    'what' => 'res_id',
    'detail_destination' => $details,
    'details_page' => '',
    'view_doc' => true,
    'bool_details' => false,
    'bool_order' => true,
    'bool_frame' => false,
    'module' => '',
    'css' => 'listing spec',
    'hidden_fields' => "<input type='hidden' name='module' id='module' value='basket' />"
        . "<input type='hidden' name='table' id='table' value="
        . $_SESSION['current_basket']['table'] . "/><input type='hidden' name='coll_id' id='coll_id' value="
        . $_SESSION['current_basket']['coll_id'] . "/>",
    'open_details_popup' => false,
    'do_actions_arr' => $do_actions_arr,
    'template' => true,
    'template_list' => $template_list,
    'actual_template' => $template_to_use,
    'bool_export' => true
);
$bask->basket_list_doc(
    $param_list, 
    $_SESSION['current_basket']['actions'],
    '', 
    true, 
    $template_list, 
    $template_to_use
);

