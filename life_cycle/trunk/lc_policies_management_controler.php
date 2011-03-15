<?php

/*
*    Copyright 2008-2011 Maarch
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
* @brief  Contains the life_cycle Object (herits of the BaseObject class)
* 
* 
* @file
* @author Luc KEULEYAN - BULL
* @author Laurent Giovannoni
* @date $date$
* @version $Revision$
* @ingroup life_cycle
*/

$sessionName = "lc_policies";
$pageName = "lc_policies_management_controler";
$tableName = "lc_policies";
$idName = "policy_id";

$mode = 'add';

$core = new core_tools();
$core->load_lang();

if (isset($_REQUEST['mode']) && !empty($_REQUEST['mode'])) {
    $mode = $_REQUEST['mode'];
} else {
    $mode = 'list'; 
}

try{
    require_once("modules/life_cycle/class/lc_policies_controler.php");
    require_once("core/class/class_request.php");
    if ($mode == 'list') {
        require_once("modules/life_cycle/lang/fr.php");
        require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

if (isset($_REQUEST['submit'])) {
    // Action to do with db
    validate_cs_submit($mode);
} else {
    // Display to do
    if (isset($_REQUEST['id']) && !empty($_REQUEST['id']))
        $policy_id = $_REQUEST['id'];
    $state = true;
    switch ($mode) {
        case "up" :
            $state=display_up($policy_id); 
            location_bar_management($mode);
            break;
        case "add" :
            display_add(); 
            location_bar_management($mode);
            break;
        case "del" :
            display_del($policy_id); 
            break;
        case "list" :
            $lc_policies_list=display_list(); 
            location_bar_management($mode);
            break;
        case "allow" :
            display_enable($policy_id); 
            location_bar_management($mode);
        case "ban" :
            display_disable($policy_id); 
            location_bar_management($mode);
    }
    include('lc_policies_management.php');
}

/**
 * Initialize session variables
 */
function init_session() {
    $sessionName = "lc_policies";
    $_SESSION['m_admin'][$sessionName] = array();
}

/**
 * Management of the location bar  
 */
function location_bar_management($mode) {
    $sessionName = "lc_policies";
    $pageName = "lc_policies_management_controler";
    $tableName = "lc_policies";
    $idName = "policy_id";
    
    $page_labels = array('add' => _ADDITION, 'up' => _MODIFICATION, 'list' => _LC_POLICIES_LIST);
    $page_ids = array('add' => 'docserver_add', 'up' => 'docserver_up', 'list' => 'lc_policies_list');

    $init = false;
    if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true") 
        $init = true;

    $level = "";
    if (isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1))
        $level = $_REQUEST['level'];
    
    $page_path = $_SESSION['config']['businessappurl'].'index.php?page='.$pageName.'&module=life_cycle&mode='.$mode;
    $page_label = $page_labels[$mode];
    $page_id = $page_ids[$mode];
    $ct=new core_tools();
    $ct->manage_location_bar($page_path, $page_label, $page_id, $init, $level);

}

/**
 * Validate a submit (add or up),
 * up to saving object
 */
function validate_cs_submit($mode) {
    $sessionName = "lc_policies";
    $pageName = "lc_policies_management_controler";
    $tableName = "lc_policies";
    $idName = "policy_id";
    $f=new functions();
    $lcPoliciesControler = new lc_policies_controler();
    $status= array();
    $status['order']=$_REQUEST['order'];
    $status['order_field']=$_REQUEST['order_field'];
    $status['what']=$_REQUEST['what'];
    $status['start']=$_REQUEST['start'];
    $lc_policies = new lc_policies();
    if (isset($_REQUEST['id'])) $lc_policies->policy_id = $_REQUEST['id'];
    if (isset($_REQUEST['policy_name'])) $lc_policies->policy_name = $_REQUEST['policy_name'];
    if (isset($_REQUEST['policy_desc'])) $lc_policies->policy_desc = $_REQUEST['policy_desc'];
    $control = array();
    $control = $lcPoliciesControler->save($lc_policies, $mode);
    if (!empty($control['error']) && $control['error'] <> 1) {
        // Error management depending of mode
        $_SESSION['error'] = str_replace("#", "<br />", $control['error']);
        put_in_session("status", $status);
        put_in_session("lc_policies", $lc_policies->getArray());
        switch ($mode) {
            case "up":
                if (!empty($_REQUEST['id'])) {
                    header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=up&id=".$_REQUEST['id']."&module=life_cycle");
                } else {
                    header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$status['order']."&order_field=".$status['order_field']."&start=".$status['start']."&what=".$status['what']);
                }
                exit;
            case "add":
                header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=add&module=life_cycle");
                exit;
        }
    } else {
        if ($mode == "add")
            $_SESSION['error'] = _LC_POLICY_ADDED;
         else
            $_SESSION['error'] = _LC_POLICY_UPDATED;
        unset($_SESSION['m_admin']);
        header("location: ".$_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle&order=".$status['order']."&order_field=".$status['order_field']."&start=".$status['start']."&what=".$status['what']);
    }
}

/**
 * Initialize session parameters for update display
 * @param Long $policy_id
 */
function display_up($policy_id) {
    $state=true;
    $lcPoliciesControler = new lc_policies_controler();
    $lc_policies = $lcPoliciesControler->get($policy_id);
    if (empty($lc_policies))
        $state = false; 
    else
        put_in_session("lc_policies", $lc_policies->getArray()); 
    
    return $state;
}

/**
 * Initialize session parameters for add display with given docserver
 */
function display_add() {
    $sessionName = "lc_policies";
    if (!isset($_SESSION['m_admin'][$sessionName]))
        init_session();
}

/**
 * Initialize session parameters for list display
 */
function display_list() {
    $sessionName = "lc_policies";
    $pageName = "lc_policies_management_controler";
    $tableName = "lc_policies";
    $idName = "policy_id";
    
    $_SESSION['m_admin'] = array();
    
    init_session();
    
    $select[_LC_POLICIES_TABLE_NAME] = array();
    array_push($select[_LC_POLICIES_TABLE_NAME], $idName, "policy_id", "policy_name", "policy_desc");
    $what = "";
    $where ="";
    if (isset($_REQUEST['what']) && !empty($_REQUEST['what'])) {
        $func = new functions();
        $what = $func->protect_string_db($_REQUEST['what']);
        if ($_SESSION['config']['databasetype'] == "POSTGRESQL") {
            $where = $idName." ilike '".strtoupper($what)."%' ";
        } else {
            $where = $idName." like '".strtoupper($what)."%' ";
        }
    }

    // Checking order and order_field values
    $order = 'asc';
    if (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) {
        $order = trim($_REQUEST['order']);
    }
    $field = $idName;
    if (isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field'])) {
        $field = trim($_REQUEST['order_field']);
    }
    $listShow = new list_show();
    $orderstr = $listShow->define_order($order, $field);
    $request = new request();
    $tab=$request->select($select,$where,$orderstr,$_SESSION['config']['databasetype']);
    for ($i=0;$i<count($tab);$i++) {
        foreach($tab[$i] as &$item) {
            switch ($item['column']) {
                case $idName:
                    format_item($item,_ID,"20","left","left","bottom",true); break;
                case "policy_name":
                    format_item($item,_POLICY_NAME,"20","left","left","bottom",true); break;
                case "policy_desc":
                    format_item($item,_POLICY_DESC,"40","left","left","bottom",true); break;
            }
        }    
    }
    
    /**
     * - La plus propre : créer un objet "PageList"
     * - La plus locale : si cela ne sert que pour admin_list dans docserver_management.php,
     *                    il est possible d'en construire directement la string et de la récupérer en return.
     */  
    $result = array();
    $result['tab']=$tab;
    $result['what']=$what;
    $result['page_name'] = $pageName."&mode=list";
    $result['page_name_up'] = $pageName."&mode=up";
    $result['page_name_del'] = $pageName."&mode=del";
    //$result['page_name_val']= $pageName."&mode=allow";
    //$result['page_name_ban'] = $pageName."&mode=ban";
    $result['page_name_add'] = $pageName."&mode=add";
    $result['label_add'] = _LC_POLICY_ADDITION;
    $_SESSION['m_admin']['init'] = true;
    $result['title'] = _LC_POLICIES_LIST." : ".count($tab)." "._LC_POLICIES;
    $result['autoCompletionArray'] = array();
    $result['autoCompletionArray']["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&module=life_cycle&page=lc_policies_list_by_id";
    $result['autoCompletionArray']["number_to_begin"] = 1;
    return $result;
}

/**
 * Delete given docserver if exists and initialize session parameters
 * @param string $policy_id
 */
function display_del($policy_id) {
    $lcPoliciesControler = new lc_policies_controler();
    $lc_policies = $lcPoliciesControler->get($policy_id);
    if (isset($lc_policies)) {
        // Deletion
        $control = array();
        $control = $lcPoliciesControler->delete($lc_policies);
        if (!empty($control['error']) && $control['error'] <> 1) {
            $_SESSION['error'] = str_replace("#", "<br />", $control['error']);
        } else {
            $_SESSION['error'] = _LC_POLICY_DELETED." ".$policy_id;
        }
        $pageName = "lc_policies_management_controler";
        ?>
        <script type="text/javascript">window.top.location='<?php echo $_SESSION['config']['businessappurl']."index.php?page=".$pageName."&mode=list&module=life_cycle";?>';</script>
        <?php
        exit;
    } else {
        // Error management
        $_SESSION['error'] = _LC_POLICY.' '._UNKNOWN;
    }
}

/**
 * Format given item with given values, according with HTML formating.
 * NOTE: given item needs to be an array with at least 2 keys: 
 * 'column' and 'value'.
 * NOTE: given item is modified consequently.  
 * @param $item
 * @param $label
 * @param $size
 * @param $label_align
 * @param $align
 * @param $valign
 * @param $show
 */
function format_item(&$item,$label,$size,$label_align,$align,$valign,$show) {
    $func = new functions();
    $item['value'] = $func->show_string($item['value']);    
    $item[$item['column']]=$item['value'];
    $item["label"]=$label;
    $item["size"]=$size;
    $item["label_align"]=$label_align;
    $item["align"]=$align;
    $item["valign"]=$valign;
    $item["show"]=$show;
    $item["order"]=$item['column'];    
}

/**
 * Put given object in session, according with given type
 * NOTE: given object needs to be at least hashable
 * @param string $type
 * @param hashable $hashable
 */
function put_in_session($type,$hashable) {
    $func = new functions();
    foreach($hashable as $key=>$value) {
        // echo "Key: $key Value: $value f:".$func->show_string($value)." // ";
        $_SESSION['m_admin'][$type][$key]=$func->show_string($value);
    }
}

