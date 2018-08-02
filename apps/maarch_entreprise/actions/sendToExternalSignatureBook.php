<?php

$confirm = true;

$etapes = ['form'];

function get_form_txt($values, $path_manage_action, $id_action, $table, $module, $coll_id, $mode)
{
    $config = getXml();

    $html = '<form name="sendToExternalSB" id="sendToExternalSB" method="post" class="forms" action="#">';
    $html .= '<input type="hidden" name="chosen_action" id="chosen_action" value="end_action" />';
    if (!empty($config)) {
        if ($config['id'] == 'ixbus') {
            include_once 'modules/visa/class/IxbusController.php';

            $html .= IxbusController::getModal($config);
        } elseif ($config['id'] == 'iParapheur') {
            include_once 'modules/visa/class/IParapheurController.php';

            $html .= IParapheurController::getModal($config);
        } elseif ($config['id'] == 'fastParapheur') {
            include_once 'modules/visa/class/FastParapheurController.php';

            $html .= FastParapheurController::getModal($config);
        }
    }

    $html .='<div align="center">';
    $html .=' <input type="button" name="validate" id="validate" value="'._VALIDATE.'" class="button" ' .
            'onclick="valid_action_form(\'sendToExternalSB\', \'' . $path_manage_action .
            '\', \'' . $id_action . '\', \'value\', \'res_letterbox\', \'null\', \'letterbox_coll\', \'' .
            $mode . '\');" />';
    $html .='<input type="button" name="cancel" id="cancel" class="button" value="'._CANCEL.'" onclick="pile_actions.action_pop();destroyModal(\'modal_'.$id_action.'\');"/>';

    $html .='</div>';
    $html .='</form>';

    return addslashes($html);
}

function check_form($form_id, $values)
{
    return true;
}

function manage_form($arr_id, $history, $id_action, $label_action, $status, $coll_id, $table, $values_form)
{
    $result = '';
    $config = getXml();

    foreach ($arr_id as $value) {
        if (!empty($config)) {
            if ($config['id'] == 'ixbus') {
                include_once 'modules/visa/class/IxbusController.php';
                $attachmentToFreeze = IxbusController::sendDatas(['config' => $config, 'resIdMaster' => $value]);
            } elseif ($config['id'] == 'iParapheur') {
                include_once 'modules/visa/class/IParapheurController.php';
                $attachmentToFreeze = IParapheurController::sendDatas(['config' => $config, 'resIdMaster' => $value]);
            } elseif ($config['id'] == 'fastParapheur') {
                include_once 'modules/visa/class/FastParapheurController.php';
                $attachmentToFreeze = FastParapheurController::sendDatas(['config' => $config, 'resIdMaster' => $value]);
            }
        }

        foreach ($attachmentToFreeze as $resId => $externalId) {
            \Attachment\modelsx\AttachmentModel::freezeAttachment(['resId' => $resId, 'table' => 'res_attachments', 'externalId' => $externalId]);
        }
    }

    return ['result' => $result, 'history_msg' => ''];
}

function getXml()
{
    if (file_exists("custom/{$_SESSION['custom_override_id']}/modules/visa/xml/remoteSignatoryBooks.xml")) {
        $path = "custom/{$_SESSION['custom_override_id']}/modules/visa/xml/remoteSignatoryBooks.xml";
    } else {
        $path = 'modules/visa/xml/remoteSignatoryBooks.xml';
    }

    $config = [];
    if (file_exists($path)) {
        $loadedXml = simplexml_load_file($path);
        if ($loadedXml) {
            $config['id']               = (string)$loadedXml->signatoryBookEnabled;
            $config['validatedStatus']  = (string)$loadedXml->validatedStatus;
            $config['refusedStatus']    = (string)$loadedXml->refusedStatus;
            foreach ($loadedXml->signatoryBook as $value) {
                if ($value->id == $config['id']) {
                    $config['data'] = (array)$value;
                }
            }
        }
    }

    return $config;
}
