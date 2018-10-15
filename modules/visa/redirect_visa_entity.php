<?php

$confirm = true;
$etapes = ['empty_error'];

function manage_empty_error($arr_id, $history, $id_action, $label_action, $status)
{
    $db = new Database();
    $_SESSION['action_error'] = '';
    $res_id = $arr_id[0];

    $stmt = $db->query('SELECT initiator FROM res_letterbox WHERE res_id = ?', [$res_id]);
    $resInitiator = $stmt->fetch();
    $stmt = $db->query("SELECT item_id, item_type FROM listmodels WHERE object_id = ? and item_mode = 'dest'", [$resInitiator['initiator']]);
    $resListModel = $stmt->fetch();
    if (!empty($resListModel)) {
        $db->query("UPDATE listinstance SET item_id = ?, item_type = ? WHERE res_id = ? AND item_mode = 'dest'", [$resListModel['item_id'], $resListModel['item_type'], $res_id]);
        $db->query("UPDATE res_letterbox SET dest_user = ?, destination = ?, status = ? WHERE res_id = ?", [$resListModel['item_id'], $resInitiator['initiator'], $status, $res_id]);
    }

    return array('result' => $res_id . '#', 'history_msg' => '');
}
