<?php


/**
 * $confirm  bool true
 */
$confirm = true;

$etapes = ['empty_error'];

require_once('modules/visa/class/class_modules_tools.php');


function manage_empty_error($arr_id, $history, $id_action, $label_action, $status)
{
    $db = new Database();
    $_SESSION['action_error'] = '';
    $res_id = $arr_id[0];

    $db->query('UPDATE listinstance SET process_date = NULL WHERE res_id = ? AND difflist_type = ?', [$res_id, 'VISA_CIRCUIT']);

    return array('result' => $res_id.'#', 'history_msg' => $label_action);
}

