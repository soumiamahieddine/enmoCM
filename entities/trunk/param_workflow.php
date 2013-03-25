<?php

// Group - Basket Form : actions params
require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'apps_tables.php';
    
if ($_SESSION['service_tag'] == 'group_basket') {
    $current_groupbasket = $_SESSION['m_admin']['basket']['groups'][$_SESSION['m_admin']['basket']['ind_group']];
    $current_compteur = $_SESSION['m_admin']['compteur'];
    // This param is only for the actions with the keyword : workflow
    if (trim($_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['KEYWORD']) == 'workflow') {
        $_SESSION['m_admin']['show_where_clause'] = false;
        $is_default_action = false;
        // Is the action the default action for the group on this basket ?
        if (
            isset($current_groupbasket['DEFAULT_ACTION']) 
            && $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'] == $current_groupbasket['DEFAULT_ACTION']
        ) {
            $is_default_action = true;
        }
        // workflow statuses list
        ?>
        <br />
        <table>
            <tr>
                <td>
                    <b><?php echo _TARGET_STATUS;?> :</b>
                </td>
                <td>
                    <select name ="<?php 
                        echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];
                        ?>_statuses_chosen[]" id ="<?php 
                        echo $_SESSION['m_admin']['basket']['all_actions'][$current_compteur]['ID'];
                        ?>_statuses_chosen[]">
                        <?php
                        // Browse all the statuses
                        for ($cpt=0;$cpt<count($_SESSION['m_admin']['statuses']);$cpt++) {
                            $selected= '';
                            if (isset($current_groupbasket['ACTIONS'])) {
                                for ($j = 0; $j<count($current_groupbasket['ACTIONS']);$j ++) {
                                    for ($k=0;$k<count($current_groupbasket['ACTIONS'][$j]['STATUSES_LIST']);$k ++) {
                                        if ($_SESSION['m_admin']['statuses'][$cpt]['id'] == $current_groupbasket['ACTIONS'][$j]['STATUSES_LIST'][$k]['ID']) {
                                            $state_status = true;
                                            $selected = ' selected="selected" ';
                                        }
                                    }
                                }
                            }
                            ?>
                            <option value=<?php 
                                echo $_SESSION['m_admin']['statuses'][$cpt]['id'] . ' ' . $selected;?> ><?php 
                                echo $_SESSION['m_admin']['statuses'][$cpt]['label'];?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }
} elseif ($_SESSION['service_tag'] == 'manage_groupbasket') {
    $db = new dbquery();
    $db->connect();
    
/*
    echo 'before<br>';
    echo 'param status';
    $db->show_array($_SESSION['m_admin']['basket']['groups']);
    exit;
*/
    $groupe = $_REQUEST['group'];
    if (isset($_REQUEST['old_group']) && !empty($_REQUEST['old_group'])) {
        $old_group = $_REQUEST['old_group'];
    }
    $ind = -1;
    $find = false;
    for ($cpt=0;$cpt<count($_SESSION['m_admin']['basket']['groups']);$cpt++) {
        if (
            $_SESSION['m_admin']['basket']['groups'][$cpt]['GROUP_ID'] == $groupe 
            || $old_group == $_SESSION['m_admin']['basket']['groups'][$cpt]['GROUP_ID']) {
            for ($j=0;$j<count($_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS']);$j++) {
                $chosen_statuses = array();
                if (
                    isset($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['ID_ACTION'].'_statuses_chosen']) 
                    && count($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['ID_ACTION'].'_statuses_chosen']) > 0
                ) {
                    for ($k=0; $k < count($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['ID_ACTION'].'_statuses_chosen']); $k++) {
                        $statusId = $_REQUEST[$_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['ID_ACTION'].'_statuses_chosen'][$k];
                        $db->query("SELECT label_status FROM " .$_SESSION['tablename']['status']. " WHERE id = '" . $statusId . "'");
                        $res = $db->fetch_object();
                        $label = $res->label_status;
                        array_push($chosen_statuses , array('ID' => $statusId, 'LABEL' => $label));
                    }
                }
                $_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['STATUSES_LIST'] = $chosen_statuses ;
            }
            if ($_SESSION['m_admin']['basket']['groups'][$cpt]['GROUP_ID'] == $groupe) {
                $ind = $cpt;
                $find = true;
                break;
            }
            if ($old_group == $_SESSION['m_admin']['basket']['groups'][$cpt]['GROUP_ID']) {
                $ind = $cpt;
                $find = true;
                break;
            }
        }
    }

    if ($find && $ind >= 0) {
        //$_SESSION['m_admin']['basket']['groups'][$ind]['PARAM_DEFAULT_ACTION'] = array();
        $_SESSION['m_admin']['basket']['groups'][$ind]['PARAM_DEFAULT_ACTION']['STATUSES_LIST'] = array();

        if (
            isset($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$ind]['DEFAULT_ACTION'].'_statuses_chosen']) 
            && count($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$ind]['DEFAULT_ACTION'].'_statuses_chosen']) > 0
        ) {
            for ($l=0; $l < count($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$ind]['DEFAULT_ACTION'].'_statuses_chosen']); $l++) {
                $statusId = $_REQUEST[$_SESSION['m_admin']['basket']['groups'][$ind]['DEFAULT_ACTION'].'_statuses_chosen'][$l];
                $db->query("SELECT label_status FROM " .$_SESSION['tablename']['status']. " WHERE id = '" . $statusId . "'");
                $res = $db->fetch_object();
                $label = $res->label_status;
                array_push($_SESSION['m_admin']['basket']['groups'][$ind]['PARAM_DEFAULT_ACTION']['STATUSES_LIST'] , array('ID' =>$statusId, 'LABEL' => $label));
            }
        }
    }
    $_SESSION['m_admin']['load_groupbasket'] = false;
    
/*
    echo 'after<br>';
    echo 'param status';
    $ent->show_array($_SESSION['m_admin']['basket']['groups']);
    exit;
*/
    
} elseif ($_SESSION['service_tag'] == 'load_basket_session') {
    $db = new dbquery();
    $db->connect();
    
    for ($cpt=0; $cpt < count($_SESSION['m_admin']['basket']['groups'] ); $cpt++) {
        //$_SESSION['m_admin']['basket']['groups'][$cpt]['PARAM_DEFAULT_ACTION'] = array();
        $_SESSION['m_admin']['basket']['groups'][$cpt]['PARAM_DEFAULT_ACTION']['STATUSES_LIST'] = array();
        if (!empty($_SESSION['m_admin']['basket']['groups'][$cpt]['DEFAULT_ACTION'] ))
        {
            $query = "SELECT status_id, label_status FROM " . GROUPBASKET_STATUS . " left join " . $_SESSION['tablename']['status'] 
                . " on status_id = id "
                . " where basket_id= '" . $this->protect_string_db(trim($_SESSION['m_admin']['basket']['basketId']))
                . "' and group_id = '" . $this->protect_string_db(trim($_SESSION['m_admin']['basket']['groups'][$cpt]['GROUP_ID']))
                . "' and action_id = " . $_SESSION['m_admin']['basket']['groups'][$cpt]['DEFAULT_ACTION'];
            $db->query($query);
            $array = array();
            while ($status = $db->fetch_object()) {
                $array[] = array('ID' => $status->status_id, 'LABEL' => $status->label_status);
            }
            $_SESSION['m_admin']['basket']['groups'][$cpt]['PARAM_DEFAULT_ACTION']['STATUSES_LIST'] = $array;
        }
        for ($j=0;$j<count($_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS']);$j++) {
            
            $query = "SELECT status_id, label_status FROM " . GROUPBASKET_STATUS . " left join " . $_SESSION['tablename']['status'] 
                . " on status_id = id "
                . " where basket_id= '" . $this->protect_string_db(trim($_SESSION['m_admin']['basket']['basketId']))
                . "' and group_id = '" . $this->protect_string_db(trim($_SESSION['m_admin']['basket']['groups'][$cpt]['GROUP_ID']))
                . "' and action_id = " . $_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['ID_ACTION'];
            $db->query($query);
            $array = array();
            while ($status = $db->fetch_object()) {
                $array[] = array('ID' => $status->status_id, 'LABEL' => $status->label_status);
            }
            $_SESSION['m_admin']['basket']['groups'][$cpt]['ACTIONS'][$j]['STATUSES_LIST'] = $array;
        }
    }
} elseif ($_SESSION['service_tag'] == 'load_basket_db') {
    $db = new dbquery();
    $db->connect();
    $workflow_actions = array();
    for ($cpt=0; $cpt<count($_SESSION['m_admin']['basket']['all_actions']);$cpt++ ) {
        if ($_SESSION['m_admin']['basket']['all_actions'][$cpt]['KEYWORD'] == 'workflow') {
            array_push($workflow_actions,$_SESSION['m_admin']['basket']['all_actions'][$cpt]['ID']);
        }
    }
    for ($cpt=0; $cpt < count($_SESSION['m_admin']['basket']['groups'] ); $cpt++) {
        $GroupBasket = $_SESSION['m_admin']['basket']['groups'][$cpt];
        if (!empty($GroupBasket['DEFAULT_ACTION']) && in_array($GroupBasket['DEFAULT_ACTION'], $workflow_actions)) {   
            $db->query(
            "DELETE FROM " . GROUPBASKET_STATUS
            . " where basket_id= '" . $db->protect_string_db(trim($_SESSION['m_admin']['basket']['basketId']))
            . "' and group_id = '" . $db->protect_string_db(trim($GroupBasket['GROUP_ID']))
            . "' and action_id = " . $GroupBasket['DEFAULT_ACTION']);
            
            for ($k = 0; $k < count($GroupBasket['PARAM_DEFAULT_ACTION']['STATUSES_LIST']); $k++) {
                $Status = $GroupBasket['PARAM_DEFAULT_ACTION']['STATUSES_LIST'][$k];
                $db->query(
                    "INSERT INTO " . GROUPBASKET_STATUS
                    . " (group_id, basket_id, action_id, status_id) values ('" 
                    . $db->protect_string_db(trim($GroupBasket['GROUP_ID'])) . "', '" 
                    . $db->protect_string_db(trim($_SESSION['m_admin']['basket']['basketId'])) . "', "
                    . $GroupBasket['DEFAULT_ACTION'] . ", '" 
                    . $Status['ID']. "')"
                );
            }
        }
        for ($j=0;$j<count($GroupBasket['ACTIONS']);$j++) {
            $GroupBasketAction = $GroupBasket['ACTIONS'][$j];
            if (in_array($GroupBasketAction['ID_ACTION'], $workflow_actions)) {
                $db->query(
                "DELETE FROM " . GROUPBASKET_STATUS
                . " where basket_id= '" . $db->protect_string_db(trim($_SESSION['m_admin']['basket']['basketId']))
                . "' and group_id = '" . $db->protect_string_db(trim($GroupBasket['GROUP_ID']))
                . "' and action_id = " . $GroupBasketAction['ID_ACTION']);
                if (isset($GroupBasketAction['STATUSES_LIST'])) {
                    for ($k = 0; $k < count($GroupBasketAction['STATUSES_LIST']); $k++) {
                        $Status = $GroupBasketAction['STATUSES_LIST'][$k];
                        $db->query(
                            "INSERT INTO " . GROUPBASKET_STATUS
                            . " (group_id, basket_id, action_id, status_id) values ('" 
                            . $db->protect_string_db(trim($GroupBasket['GROUP_ID'])) . "', '" 
                            . $db->protect_string_db(trim($_SESSION['m_admin']['basket']['basketId'])) . "', "
                            . $GroupBasketAction['ID_ACTION'] . ", '" 
                            . $Status['ID']. "')"
                        );
                    }
                }
            }
        }
    }
} else if ($_SESSION['service_tag'] == 'del_basket' && !empty($_SESSION['temp_basket_id'])) {
    $db = new dbquery();
    $db->query("delete from ".GROUPBASKET_STATUS." where basket_id = '".$_SESSION['temp_basket_id']."'");
    unset($_SESSION['temp_basket_id']);
}
