<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

*
* @brief   visa_workflow
*
* @author  dev <dev@maarch.org>
* @ingroup visa
*/
require_once 'modules/visa/class/class_modules_tools.php';
$visa = new visa();
$confirm = true;

$error_visa_workflow_signature_book = false;
if ($visa->isAllAttachementSigned($_SESSION['doc_id']) == 'noAttachment') {
    $error_visa_workflow_signature_book = true;
} elseif ($visa->currentUserSignRequired($_SESSION['doc_id']) == 'true') {
    $label_action .= ' ('._NO_USER_SIGNED_DOC.')';
}

$etapes = ['empty_error'];

function manage_empty_error($arr_id, $history, $id_action, $label_action, $status)
{
    $db = new Database();
    $result = '';

    if (!empty($_SESSION['stockCheckbox'])) {
        $arr_id = $_SESSION['stockCheckbox'];
    }

    for ($i = 0; $i < count($arr_id); ++$i) {
        $_SESSION['action_error'] = '';
        $coll_id = $_SESSION['current_basket']['coll_id'];
        $res_id = $arr_id[$i];
        include_once 'core/class/class_security.php';
        $sec = new security();
        include_once 'core/class/class_history.php';
        $history = new history();
        $table = $sec->retrieve_table_from_coll($coll_id);
        $circuit_visa = new visa();
        $sequence = $circuit_visa->getCurrentStep($res_id, $coll_id, 'VISA_CIRCUIT');
        $stepDetails = array();
        $stepDetails = $circuit_visa->getStepDetails($res_id, $coll_id, 'VISA_CIRCUIT', $sequence);

        $message = $circuit_visa->processVisaWorkflow(['stepDetails' => $stepDetails, 'res_id' => $res_id]);

        $stmt = $db->query('SELECT status FROM res_letterbox WHERE res_id = ?', array($res_id));
        $resource = $stmt->fetchObject();
        if ($resource->status == 'EVIS' || $resource->status == 'ESIG') {
            $circuit_visa->setStatusVisa($res_id, 'letterbox_coll');
        }

        //USEFULL FOR SPM PARAM (can set SIGN WITH OTHER STATUS)
        $stmt = $db->query(
            'select count(1) as total FROM listinstance '
            .' WHERE item_mode = ? AND res_id = ? AND difflist_type = ? AND requested_signature = ?',
            array('sign', $res_id, 'VISA_CIRCUIT', true)
        );
        $res = $stmt->fetchObject();
        if ($res->total > 0 && $circuit_visa->getCurrentStep($res_id, $coll_id, 'VISA_CIRCUIT') == $circuit_visa->nbVisa($res_id, $coll_id)) {
            $mailStatus = 'ESIG';
            $db->query('UPDATE res_letterbox SET status = ? WHERE res_id = ? ', [$mailStatus, $res_id]);
        }

        $result .= $arr_id[$i].'#';
    }

    return array('result' => $result, 'history_msg' => $message);
}
