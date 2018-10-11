<?php

require_once 'modules/visa/class/class_modules_tools.php';
$visa = new visa();

if ($visa->currentUserSignRequired($_SESSION['doc_id']) == 'true') {
    $label_action .=" ("._NO_USER_SIGNED_DOC.")";
}

$confirm = true;
$etapes = ['empty_error'];

function manage_empty_error($arr_id, $history, $id_action, $label_action, $status)
{
    $db = new Database();
    $_SESSION['action_error'] = '';
    $coll_id = $_SESSION['current_basket']['coll_id'];
    $res_id = $arr_id[0];
    $circuit_visa = new visa();
    $sequence = $circuit_visa->getCurrentStep($res_id, $coll_id, 'VISA_CIRCUIT');
    $stepDetails = $circuit_visa->getStepDetails($res_id, $coll_id, 'VISA_CIRCUIT', $sequence);

    // Person who ends the workflow
    if ($stepDetails['listinstance_id']) {
        $db->query(
            'UPDATE listinstance SET process_date = CURRENT_TIMESTAMP, process_comment = ? WHERE listinstance_id = ? AND item_id = ? AND res_id = ? AND difflist_type = ?',
            ["A terminé le circuit avec l'action {$label_action}", $stepDetails['listinstance_id'], $stepDetails['item_id'], $res_id, 'VISA_CIRCUIT']
        );
    } else {
        $db->query(
            'UPDATE listinstance SET process_date = CURRENT_TIMESTAMP, process_comment = ? WHERE res_id = ? AND difflist_type = ? AND item_mode = ?',
            ["A terminé le circuit avec l'action {$label_action}", $res_id, 'VISA_CIRCUIT', 'sign']
        );
    }

    // People remaining in the workflow
    $db->query(
        'UPDATE listinstance SET process_date = CURRENT_TIMESTAMP, process_comment = ? WHERE res_id = ? AND difflist_type = ? AND process_date IS NULL',
        ['Circuit Interrompu', $res_id, 'VISA_CIRCUIT']
    );

    $stmt = $db->query('SELECT initiator FROM res_letterbox WHERE res_id = ?', [$res_id]);
    $resInitiator = $stmt->fetch();
    $stmt = $db->query("SELECT item_id, item_type FROM listmodels WHERE object_id = ? and item_mode = 'dest'", [$resInitiator['initiator']]);
    $resListModel = $stmt->fetch();
    if (!empty($resListModel)) {
        $db->query("UPDATE listinstance SET item_id = ?, item_type = ? WHERE res_id = ? AND item_mode = 'dest'", [$resListModel['item_id'], $resListModel['item_type'], $res_id]);
        $db->query("UPDATE res_letterbox SET dest_user = ?, destination = ? WHERE res_id = ?", [$resListModel['item_id'], $resInitiator['initiator'], $res_id]);
    }

    return array('result' => $res_id . '#', 'history_msg' => '');
}
