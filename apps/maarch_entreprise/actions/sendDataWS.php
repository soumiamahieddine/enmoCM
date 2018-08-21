<?php

$confirm = true;

$etapes = ['send'];

function manage_send($aId)
{
    $result = '';

    foreach ($aId as $resId) {
        $config = \SrcCore\models\CurlModel::getConfigByCallId(['curlCallId' => 'sendData']);
        $select = [];
        foreach ($config['rawData'] as $value) {
            $select[] = $value;
        }
        $document = \Resource\models\ResModel::getOnView(['select' => $select, 'where' => ['res_id = ?'], 'data' => [$aId[0]]]);

        \SrcCore\models\CurlModel::exec(['curlCallId' => 'sendData', 'bodyData' => $document[0]]);

        $result .= $resId . '#';
    }

    return ['result' => $result, 'history_msg' => ''];
}
