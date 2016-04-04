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

    $stmt = $db->query('SELECT listinstance_id FROM listinstance WHERE res_id = ? and difflist_type = ? AND process_date IS NOT NULL ORDER BY process_date DESC LIMIT 1',
        [$res_id, 'VISA_CIRCUIT']);

    if ($stmt->rowCount() < 1) {
        $newStatus = 'AREV';
    } else {
        $listInstance = $stmt->fetchObject();
        $db->query('UPDATE listinstance SET process_date = NULL WHERE res_id = ? AND difflist_type = ? AND listinstance_id = ?',
            [$res_id, 'VISA_CIRCUIT', $listInstance->listinstance_id]);
        $newStatus = 'AREVVI';
    }

    $db->query("UPDATE res_letterbox SET status = ? WHERE res_id = ? ", [$newStatus, $res_id]);
    return array('result' => $res_id.'#', 'history_msg' => $label_action);
}

