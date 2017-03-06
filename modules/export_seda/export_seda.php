<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Export seda Action
* @author dev@maarch.org
* @ingroup export_seda
*/


/**
* $confirm  bool true
*/
$confirm = true;
$frm_width='100%';
$frm_height = 'auto';
/**
* $etapes  array Contains only one etap, the status modification
*/
 
$etapes = array('form');
require_once __DIR__.'/ArchiveTransfer.php';
//require_once __DIR__.'/StreamClient.php';

function get_form_txt($values, $path_manage_action,  $id_action, $table, $module, $coll_id, $mode) {
    $archiveTransfer = new ArchiveTransfer();

    $result = $archiveTransfer->receive($values);
    
    $db = new Database();
    $stmt = $db->query("select message_id from unit_identifier where res_id = ?",array($values[0]));
    $unitIdentifier = $stmt->fetchObject();
    $stmt = $db->query("select data from seda where message_id = ?",array($unitIdentifier->message_id));

    $messageData = $stmt->fetchObject();

    $messageObject = json_decode($messageData->data);

    /*$test = new StreamClient();
    var_dump($test->send($messageObject->messageIdentifier->value));
    exit();*/
    $frm_str = '<div id="frm_error_'.$id_action.'" class="error"></div>';
    $frm_str .= '<h2 class="title">'._MESSAGE.' '. $messageObject->messageIdentifier->value;
    $frm_str .= '</h2><br/>';
    $frm_str .= '<div class="block forms details" >';

    // Information Message
    $frm_str .= '<h3 class="title">'._INFORMATION_MESSAGE.'</h3>';
    $frm_str .= '<table width="100%" cellspacing="2" cellpading="2" border="0"><tbody><tr class="col"><br/>';
    $frm_str .='<td><b>'._MESSAGE_IDENTIFIER.':</b></td>';
    $frm_str .= '<td><input type="text" id="messageIdentifier" name="messageIdentifier" value="'.$messageObject->messageIdentifier->value. '" disabled></td>';
    $frm_str .='<td><b>'._DATE.':</b></td>';
    $frm_str .= '<td><input type="text" id="date" name="date" value="'.$messageObject->date. '" disabled></td></tr><tr class="col">';
    $frm_str .='<td><b>'._ARCHIVAL_AGREEMENT.':</b></td>';
    $frm_str .= '<td><input type="text" id="archivalAgreement" name="archivalAgreement" value="'.$messageObject->archivalAgreement->value. '" disabled></td>';
    $frm_str .='<td><b>'._ARCHIVAL_AGENCY_SIREN.':</b></td>';
    $frm_str .= '<td><input type="text" id="archivalAgency" name="archivalAgency" value="'.$messageObject->archivalAgency->identifier->value. '" disabled></td></tr><tr class="col">';
    $frm_str .='<td><b>'.TRANSFERRING_AGENCY_SIREN.':</b></td>';
    $frm_str .= '<td><input type="text" id="transferringAgency" name="transferringAgency" value="'.$messageObject->transferringAgency->identifier->value. '" disabled></td>';
    $frm_str .= '</tr></tbody></table><hr />';

    //Information n Archive
    
    foreach ($messageObject->dataObjectPackage->descriptiveMetadata->archiveUnit as $archiveUnit) {
        $frm_str .= viewArchiveUnit($archiveUnit);
    }


    /*
*/

    $path_to_script = $_SESSION['config']['businessappurl']."index.php?display=true&module=export_seda";

    $frm_str .= '</div>';
    $frm_str .='<div align="center">';
    $frm_str .='<input type="button" name="zip" id="zip" class="button"  value="'._ZIP.'" onclick="actionSeda(\''.$path_to_script.'&page=Ajax_seda_zip&reference='.$messageObject->messageIdentifier->value.'\');"/>&nbsp&nbsp&nbsp';
    $frm_str .='<input type="button" name="sendMessage" id="sendMessage" class="button"  value="'._SEND_MESSAGE.'" onclick="actionSeda(\''.$path_to_script.'&page=Ajax_seda_send&reference='.$messageObject->messageIdentifier->value.'\');"/>';
    $frm_str .='</div>';
    $frm_str .='<div align="center" id="validSeda"></div>';
    $frm_str .='<hr />';

    $frm_str .='<div align="center">';
            $frm_str .='<input type="button" name="cancel" id="cancel" class="button"  value="'._CANCEL.'" onclick="pile_actions.action_pop();destroyModal(\'modal_'.$id_action.'\');"/>';
    $frm_str .='</div>';
    //$frm_str .='<script type="text/javascript">'. require_once __DIR__.'/js/function.js'.'</script>';

    /*$extract = new Extract();
    $extract->exportZip($messageObject->messageIdentifier->value));*/

    return addslashes($frm_str);
}

function manage_form($arr_id, $history, $id_action, $label_action, $status)
{
    

    

    
    // récupérer l'entité racine du courrier *
    // récupérer archival_agency et archival_agreement *

    // récupérer la retention_final_disposition et retention_rule du type de doc du courrier *

    // appel fonction de transfert et génération bdx *

    

    // historisation du transfert

    // modification statut -> fait automatiquement par mécanique bannette

    // ensuite il y a aura une suppression logique des documents et des contacts (si plus de courriers associés)

    return array('result' => $result, 'history_msg' => '');
}

function viewArchiveUnit($archiveUnit, $archiveUnitChildren = false) 
{
    if (!$archiveUnitChildren) {
        $frm_str .= '<h3 class="title">'._INFORMATION_ARCHIVE. ' "'. $archiveUnit->content->title[0].'"</h3>';
    } else {
        $frm_str .= '<h4 class="title">'._INFORMATION_ARCHIVE_CHILDREN. ' "'. $archiveUnit->content->title[0].'"</h4>';
    }
    
    $frm_str .= '<table width="100%" cellspacing="2" cellpading="2" border="0"><tbody><tr class="col"><br/>';
    $frm_str .='<td><b>'._ARCHIVE_IDENTIFIER.':</b></td>';
    $frm_str .= '<td><input type="text" id="archiveIdentifier" name="archiveIdentifier" value="'.$archiveUnit->id. '" disabled></td></tr>';

    if ($archiveUnit->management) {
        $frm_str .='<tr class="col"><td><b>'._RETENTION_RULE.':</b></td>';
        $frm_str .= '<td><input type="text" id="rule" name="rule" value="'.$archiveUnit->management->appraisalRule->rule->value. '" disabled></td>';
        $frm_str .='<td><b>'._RETENTION_FINAL_DISPOSITION.':</b></td>';
        $frm_str .= '<td><input type="text" id="finalAction" name="finalAction" value="'.$archiveUnit->management->appraisalRule->finalAction. '" disabled></td>';
        $frm_str .= '</tr>';
    }

    $frm_str .= '<tr class="col"><td><b>'._DESCRIPTION_LEVEL.':</b></td>';
    $frm_str .= '<td><input type="text" id="descriptionLevel" name="descriptionLevel" value="'.$archiveUnit->content->descriptionLevel. '" disabled></td>';
    $frm_str .= '<td><b>'._DOCUMENT_TYPE.':</b></td>';
    $frm_str .= '<td><input type="text" id="documentType" name="documentType" value="'.$archiveUnit->content->documentType. '" disabled></td></tr>';
    $frm_str .= '<tr class="col"><td><b>'._RECEIVED_DATE.':</b></td>';
    $frm_str .= '<td><input type="text" id="receivedDate" name="receivedDate" value="'.$archiveUnit->content->receivedDate. '" disabled></td>';
    $frm_str .= '<td><b>'._SENT_DATE.':</b></td>';
    $frm_str .= '<td><input type="text" id="sentDate" name="sentDate" value="'.$archiveUnit->content->sentDate. '" disabled></td></tr>';
    $frm_str .= '<tr class="col"><td><b>'._END_DATE.':</b></td>';
    $frm_str .= '<td><input type="text" id="endDate" name="endDate" value="'.$archiveUnit->content->endDate. '" disabled></td></tr>';
    $frm_str .= '</tr></tbody></table>';

    if ($archiveUnit->archiveUnit) {
        foreach ($archiveUnit->archiveUnit as $archiveUnitChildren) {
            $frm_str .= viewArchiveUnit($archiveUnitChildren,true);
        }
    }

    

    return $frm_str;
}