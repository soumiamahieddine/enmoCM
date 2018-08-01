<?php

$confirm = true;

$etapes = ['form'];

function get_form_txt($values, $path_manage_action, $id_action, $table, $module, $coll_id, $mode)
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

    $html = '';
    if (!empty($config)) {
        if ($config['id'] == 'ixbus') {
            include_once 'modules/visa/class/IxbusController.php';

            $html = IxbusController::getModal($config);
        } elseif ($config['id'] == 'iParapheur') {
            include_once 'modules/visa/class/iParapheurController.php';

            $html = iParapheurController::getModal($config);
        } elseif ($config['id'] == 'fastParapheur') {
            include_once 'modules/visa/class/fastParapheurController.php';

            $html = fastParapheurController::getModal($config);
        }
    }

    return addslashes($html);
}

function manage_form($aId)
{
    $result = '';

    // TODO SEND DATA


    return ['result' => $result, 'history_msg' => ''];
}
