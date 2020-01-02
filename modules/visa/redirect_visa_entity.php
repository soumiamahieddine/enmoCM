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
    $resListModel = [];

    $stmt = $db->query("SELECT entity_label FROM entities WHERE entity_id = ?", [$resInitiator['initiator']]);
    $resEntity = $stmt->fetch();
    $stmt = $db->query("SELECT lastname, firstname FROM users WHERE user_id = ?", [$resListModel['item_id']]);
    $resUsers = $stmt->fetch();
    $_SESSION['process']['diff_list']['dest']['users'][0]['user_id'] = $resListModel['item_id'];
    $_SESSION['process']['diff_list']['dest']['users'][0]['lastname'] = $resUsers['lastname'];
    $_SESSION['process']['diff_list']['dest']['users'][0]['firstname'] = $resUsers['firstname'];
    $_SESSION['process']['diff_list']['dest']['users'][0]['entity_id'] = $resInitiator['initiator'];
    $_SESSION['process']['diff_list']['dest']['users'][0]['entity_label'] = $resEntity['entity_label'];

    return array('result' => $res_id . '#', 'history_msg' => '');
}
