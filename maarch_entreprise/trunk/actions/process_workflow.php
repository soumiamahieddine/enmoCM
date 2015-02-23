<?php
$confirm = false;
$etapes = array('form');
$frm_width='355px';
$frm_height = '500px';
require("modules/entities/entities_tables.php");

function get_form_txt($values, $path_manage_action,  $id_action, $table, $module, $coll_id, $mode )
{
    // Select roles from GROUPBASKET_DIFFLIST_ROLES
    $statuses = array();
    $db = new dbquery();
    $db->connect();
    $query = "SELECT difflist_role_id FROM " . ENT_GROUPBASKET_DIFFLIST_ROLES
        . " where basket_id= '" . $_SESSION['current_basket']['id']
        . "' and group_id = '"  . $_SESSION['current_basket']['group_id']
        . "' and action_id = "  . $id_action;
    $db->query($query);

    if($db->nb_result() > 0) {
        $rolesLine = $db->fetch_object();
        $roleFromParam = $rolesLine->difflist_role_id;
    }
    
    $res_id = $values[0];
    //WF COMPUTING
    require_once('modules/basket/class/class_modules_tools.php');
    $myBasket = new basket();

    $myTurnInTheWF = false;
    //is it my turn in the WF ?
    $myTurnInTheWF = $myBasket->isItMyTurnInTheWF(
        $_SESSION['user']['UserId'],
        $res_id,
        $coll_id
    );
    
     $frm_str .= '<h2 class="title">' . _PROCESS_STEP 
        . ', ' . _DOCUMENT . ' ' . _NUM . ' ' . $res_id . '</h2>';
     
    if ($myTurnInTheWF) {
        $rolesArr = array();
        //get the roles in the wf of the user
        $rolesArr = $myBasket->whatAreMyRoleInTheWF(
            $_SESSION['user']['UserId'],
            $res_id,
            $coll_id
        );
        //print_r($rolesArr);
        $foundARoleForMe = false;
        if (!empty($rolesArr)) {
            $rolesInTheWF = array();
            for ($cptRoles=0;$cptRoles<count($rolesArr);$cptRoles++) {
                if ($rolesArr[$cptRoles] == $roleFromParam) {
                    $foundARoleForMe = true;
                    $sequence = $myBasket->whatIsMySequenceForMyRole(
                        $_SESSION['user']['UserId'],
                        $res_id,
                        $coll_id,
                        $rolesArr[$cptRoles]
                    );
                    array_push(
                        $rolesInTheWF,
                        array(
                            'role' => $rolesArr[$cptRoles],
                            'sequence' => $sequence,
                            'isThereSomeoneAfterMeInTheWF' =>$myBasket->isThereSomeoneAfterMeInTheWF(
                                $res_id,
                                $coll_id,
                                $rolesArr[$cptRoles],
                                $sequence
                            ),
                            'theNextInTheWF' =>$myBasket->whoseTheNextInTheWF(
                                $res_id,
                                $coll_id,
                                $rolesArr[$cptRoles],
                                $sequence
                            ),
                            'isThereSomeoneBeforeMeInTheWF' =>$myBasket->isThereSomeoneBeforeMeInTheWF(
                                $res_id,
                                $coll_id,
                                $rolesArr[$cptRoles],
                                $sequence
                            ),
                            'thePreviousInTheWF' =>$myBasket->whoseThePreviousInTheWF(
                                $res_id,
                                $coll_id,
                                $rolesArr[$cptRoles],
                                $sequence
                            ),
                        )
                    );
                }
            }
        }
        //print_r($rolesInTheWF);
    }
    
    //WF general view for the agent
    if ($myTurnInTheWF) {
        $frm_str .= '<form name="process_wf" id="process_wf" method="post" class="forms" action="#">';
        $frm_str .= '<input type="hidden" name="chosen_action" id="chosen_action" value="end_action" />';
        $frm_str .= '<input type="hidden" name="coll_id" id="coll_id" value="' . $coll_id . '" />';
        $frm_str .= '<input type="hidden" name="res_id" id="res_id" value="' . $res_id . '" />';
        $frm_str .= '<input type="hidden" name="way" id="way" value="forward" />';
        $frm_str .= '<input type="hidden" name="role" id="role" />';
        $countRoles = count($rolesInTheWF);
        for ($cptR=0;$cptR<$countRoles;$cptR++) {
             $frm_str .= '<h3 onclick="new Effect.toggle(\'wf_div' . $cptR . '\', \'blind\', {delay:0.2});'
                . 'whatIsTheDivStatus(\'wf_div' . $cptR . '\', \'divStatus_wf_div' . $cptR . '\');return false;" '
                . 'onmouseover="this.style.cursor=\'pointer\';" class="categorie" style="width:90%;">';
            $frm_str .= ' <span id="divStatus_wf_div' . $cptR . '" style="color:#1C99C5;">>></span>&nbsp;<b>'
                . _WF  . '</b> : <small><small>' . _ROLE . ' ' . $rolesInTheWF[$cptR]['role'] . '</small></small>';
            $frm_str .= '<span class="lb1-details">&nbsp;</span>';
            $frm_str .= '</h3>';
            $frm_str .= '<div id="wf_div' . $cptR . '">';
            $frm_str .= '<table width="98%" align="center" border="0" cellspacing="5" cellpadding="5">';
            if ($rolesInTheWF[$cptR]['isThereSomeoneAfterMeInTheWF']) {
                $textButton = '>> ' . _ADVANCE_TO . ' ' . $rolesInTheWF[$cptR]['theNextInTheWF'] . ' >>';
            } else {
                $textButton = _VALID_STEP . ' ' . $rolesInTheWF[$cptR]['role'];
            }
            $frm_str .= '<tr>';
            $frm_str .= '<td class="tdButtonGreen" onmouseover="this.style.cursor=\'pointer\';" '
                . 'onclick="$(\'role\').value=\'' . $rolesInTheWF[$cptR]['role'] 
                . '\';$(\'way\').value=\'forward\';valid_action_form(\'process_wf\', \''
                        . $path_manage_action . '\', \'' . $id_action . '\', \'' 
                        . $res_id . '\', \'' . $table . '\', \'' 
                        . $module . '\', \'' . $coll_id . '\', \'' . $mode . '\');">';
                $frm_str .= $textButton;
            $frm_str .= '</td>';
            $frm_str .= '</tr>';
            if ($rolesInTheWF[$cptR]['isThereSomeoneBeforeMeInTheWF']) {
                $frm_str .= '<tr>';
                $frm_str .= '<td class="tdButtonRed" onmouseover="this.style.cursor=\'pointer\';" '
                     . 'onclick="$(\'role\').value=\'' . $rolesInTheWF[$cptR]['role'] 
                     . '\';$(\'way\').value=\'back\';valid_action_form(\'process_wf\', \''
                        . $path_manage_action . '\', \'' . $id_action . '\', \'' 
                        . $res_id . '\', \'' . $table . '\', \'' 
                        . $module . '\', \'' . $coll_id . '\', \'' . $mode . '\');">';
                    $frm_str .= '<< ' . _BACK_TO . ' ' . $rolesInTheWF[$cptR]['thePreviousInTheWF'] . ' <<';
                $frm_str .= '</td>';
                $frm_str .= '</tr>';
            }
            $frm_str .= '</table>';
            $frm_str .= '</div>';
        }
        $frm_str .= '</form>';
    } else {
         $frm_str .= _ITS_NOT_MY_TURN_IN_THE_WF . '<br />';
    }
    if (!$foundARoleForMe) {
         $frm_str .= _NO_AVAILABLE_ROLE_FOR_ME_IN_THE_WF . '<br />';
    }
    $frm_str .='<hr />';
    $frm_str .='<div align="center">';
    $frm_str .='<input type="button" name="cancel" id="cancel" class="button"  value="' 
        . _CANCEL.'" onclick="destroyModal(\'modal_' . $id_action . '\');"/>';
    $frm_str .='</div>';
    return addslashes($frm_str);
}

function check_form($form_id, $values)
{
    $res_id = get_value_fields($values, 'res_id');
    $coll_id = get_value_fields($values, 'coll_id');
    $role = get_value_fields($values, 'role');
    $way = get_value_fields($values, 'way');
    if (empty($res_id) || empty($coll_id) || empty($role) || empty($way)) {
        $_SESSION['action_error'] = _MISSING_FIELD;
        return false;
    } else {
        return true;
    }
}

function manage_form($arr_id, $history, $id_action, $label_action, $status, $coll_id, $table, $values_form)
{
    if (empty($values_form) || count($arr_id) < 1) {
        return false;
    }
    
    $res_id = get_value_fields($values_form, 'res_id');
    $coll_id = get_value_fields($values_form, 'coll_id');
    $role = get_value_fields($values_form, 'role');
    $way = get_value_fields($values_form, 'way');
    
    $core_tools = new core_tools();
    $dbActions= new dbquery();
    $dbActions->connect();
    require_once('modules/basket/class/class_modules_tools.php');
    $b = new basket();

    //WF COMPUTING
     $myTurnInTheWF = false;
    //is it my turn in the WF ?
    $myTurnInTheWF = $b->isItMyTurnInTheWF(
        $_SESSION['user']['UserId'],
        $res_id,
        $coll_id,
        $role
    );

    if ($myTurnInTheWF) {
        $someoneAfterMe =true;
        $sequence = $b->whatIsMySequenceForMyRole(
            $_SESSION['user']['UserId'],
            $res_id,
            $coll_id,
            $role
        );
        $someoneAfterMe = $b->isThereSomeoneAfterMeInTheWF(
            $res_id,
            $coll_id,
            $role,
            $sequence
        );
        $updateStatus = true;
        $theNewStatus = '';
        // Select statuses from groupbasket
        $db = new dbquery();
        $db->connect();
        $query = "SELECT status_id, label_status FROM " 
            . GROUPBASKET_STATUS . " left join " . $_SESSION['tablename']['status']
            . " on status_id = id "
            . " where basket_id= '" . $_SESSION['current_basket']['id']
            . "' and group_id = '"  . $_SESSION['current_basket']['group_id']
            . "' and action_id = "  . $id_action;
        $db->query($query);
        if($db->nb_result() > 0) {
            $status = $db->fetch_object();
            $theNewStatus = $status->status_id;
        } else {
            $updateStatus = false;
        }
        //UPDATE STATUS IF NOBODY AFTER ME
        if (
            !$someoneAfterMe 
            && ($updateStatus && $theNewStatus <> '')
            && $way == 'forward'
        ) {
            $queryUpdateStatus = "update " 
                . $table . " set status = '" 
                . $theNewStatus 
                . "' where res_id = " . $res_id;
            $db->query($queryUpdateStatus);
        }
        $b->moveInTheWF(
            $way,
            $coll_id,
            $res_id,
            $role,
            $_SESSION['user']['UserId']
        );
        require_once('modules/entities/class/class_manage_listdiff.php');
        $listdiff = new diffusion_list();
        $_SESSION['process']['diff_list'] = $listdiff->get_listinstance(
            $res_id, 
            false, 
            $coll_id
        );
        if ($way == 'forward') {
            $histText = _FORWARD_IN_THE_WF;
        } else {
            $histText = _BACK_IN_THE_WF;
        }
        require_once('core/class/class_security.php');
        $sec = new security();
        $view = $sec->retrieve_view_from_coll_id($coll_id);
        require_once('core/class/class_history.php');
        $hist = new history();
        $hist->add(
            $view, 
            $res_id, 
            'UP', 
            'stepWF' . $way,
            _DOC_NUM . $res_id . ' ' . $histText . ' ' . $role, 
            $_SESSION['config']['databasetype'], 
            'apps'
        );
        if (!$someoneAfterMe) {
            $hist->add(
                $view, 
                $res_id, 
                'UP', 
                'stepWFEND',
                _DOC_NUM . $res_id . ' ' . _END_OF_THE_WF . ' ' . $role, 
                $_SESSION['config']['databasetype'], 
                'apps'
            );
        }
        return array('result' => $arr_id[0].'#', 'history_msg' => '');
    } else  {
        $_SESSION['error'] = _ITS_NOT_MY_TURN_IN_THE_WF;
        return array(
            'result' => $arr_id[0] . '#', 
            'history_msg' => _ITS_NOT_MY_TURN_IN_THE_WF
        );
    }
    return false;
}

 /**
 * Get the value of a given field in the values returned by the form
 *
 * @param $values Array Values of the form to check
 * @param $field String the field
 * @return String the value, false if the field is not found
 **/
function get_value_fields($values, $field)
{
    for ($i=0; $i<count($values);$i++) {
        if ($values[$i]['ID'] == $field) {
            return  $values[$i]['VALUE'];
        }
    }
    return false;
}

