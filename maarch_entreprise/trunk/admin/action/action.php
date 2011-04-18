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
* @brief  Displays actions in a list
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$admin = new core_tools();
$admin->test_admin('admin_actions', 'apps');
$func = new functions();
/****************Management of the location bar  ************/
$init = false;
if(isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == 'true'){
    $init = true;
}
$level = '';
if(isset($_REQUEST['level']) 
    && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 
        || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)){
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl']
           . 'index.php?page=action&admin=action';
$page_label = _ACTION_LIST;
$page_id = 'action';
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_request.php');
require_once('apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_list_show.php');
$_SESSION['m_admin'] = array();
$select[$_SESSION['tablename']['actions']] = array();
array_push($select[$_SESSION['tablename']['actions']], 'id', 'label_action', 
    'is_system');
$what = '';
$where = " enabled = 'Y' ";
if(isset($_REQUEST['what']) && !empty($_REQUEST['what'])){
    $what = $func->protect_string_db($_REQUEST['what']);
    if($_SESSION['config']['databasetype'] == 'POSTGRESQL'){
        $where .= " and (label_action ilike '"
               . $func->protect_string_db($what,
                    $_SESSION['config']['databasetype'])."%'  ) ";
    }
    else{
        $where .= " and (label_action like '" 
               . $func->protect_string_db($what,
                    $_SESSION['config']['databasetype'])."%'   ) ";
    }
}

$list = new list_show();
$order = 'asc';
if(isset($_REQUEST['order']) && !empty($_REQUEST['order'])){
    $order = trim($_REQUEST['order']);
}
$field = 'label_action';
if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field'])){
    $field = trim($_REQUEST['order_field']);
}

$orderstr = $list->define_order($order, $field);
$request = new request();
$tab = $request->select($select, $where, $orderstr,
    $_SESSION['config']['databasetype']);
//$request->show();
//$del = array();
for($i = 0;$i < count($tab); $i++){
    for ($j = 0;$j < count($tab[$i]); $j++){
        foreach(array_keys($tab[$i][$j]) as $value){
            if($tab[$i][$j][$value] == 'id'){
                $load = $admin->is_action_defined($tab[$i][$j]['value']);
                $tab[$i][$j]['id'] = $tab[$i][$j]['value'];
                $tab[$i][$j]['label'] = _ID;
                $tab[$i][$j]['size'] = '18';
                $tab[$i][$j]['label_align'] = 'left';
                $tab[$i][$j]['align'] = 'left';
                $tab[$i][$j]['valign'] = 'bottom';
                $tab[$i][$j]['show'] = true;
                $tab[$i][$j]['order'] = 'id';
            }
            if($tab[$i][$j][$value] == 'label_action'){
                $tab[$i][$j]['value'] = $request->show_string(
                                            $tab[$i][$j]['value']);
                $tab[$i][$j]['label_action'] = $tab[$i][$j]['value'];
                $tab[$i][$j]['label'] = _DESC;
                $tab[$i][$j]['size'] = '55';
                $tab[$i][$j]['label_align'] = 'left';
                $tab[$i][$j]['align'] = 'left';
                $tab[$i][$j]['valign'] = 'bottom';
                $tab[$i][$j]['show'] = true;
                $tab[$i][$j]['order'] = 'label_action';
            }
            if($tab[$i][$j][$value] == 'is_system'){
                if($tab[$i][$j]['value'] == 'Y'){
                    $tab[$i][$j]['value'] = _YES;
                    array_push($tab[$i], array(
                                                'column' => 'can_delete',
                                                'value' => 'false', 
                                                'can_delete' => 'false',
                                                'label' => _DESC,
                                                'show' => false
                                                )
                    );
                }
                else{
                    $tab[$i][$j]['value'] = _NO;
                    array_push($tab[$i], array(
                                                'column' => 'can_delete', 
                                                'value' => 'true', 
                                                'can_delete' => 'true',
                                                'label' => _DESC,
                                                'show' => false
                                                )
                    );
                }
                $tab[$i][$j]['is_system'] = $tab[$i][$j]['value'];
                $tab[$i][$j]['label'] = _IS_SYSTEM;
                $tab[$i][$j]['size'] = '5';
                $tab[$i][$j]['label_align'] = 'left';
                $tab[$i][$j]['align'] = 'left';
                $tab[$i][$j]['valign'] = 'bottom';
                $tab[$i][$j]['show'] = true;
                $tab[$i][$j]['order'] = 'is_system';
            }
        }
    }
}

//$request->show_array($tab);
$page_name = 'action';
$page_name_up = 'action_up';
$page_name_del = 'action_del';
$page_name_val= '';
$page_name_ban = '';
$page_name_add = 'action_add';
$label_add = _ADD_ACTION;
$_SESSION['m_admin']['init'] = true;
$title = _ACTION_LIST . ' : ' . count($tab) . ' ' . _ACTIONS;

$autoCompletionArray = array();
$autoCompletionArray['list_script_url'] = 
    $_SESSION['config']['businessappurl'] 
    . 'index.php?display=true&admin=action&page=action_list_by_name';
$autoCompletionArray['number_to_begin'] = 1;

$list->admin_list($tab, count($tab), $title, 'id','action','action','id', true, 
                  $page_name_up, $page_name_val, $page_name_ban, $page_name_del, 
                  $page_name_add, $label_add, FALSE, FALSE, _ALL_ACTIONS, 
                  _ACTION, $_SESSION['config']['businessappurl'] 
                  . 'static.php?filename=manage_actions_b.gif', false, true, 
                  false, true, $what, true, $autoCompletionArray
                 );
$_SESSION['m_admin']['action'] = array();
