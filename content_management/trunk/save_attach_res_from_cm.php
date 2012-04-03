<?php
require_once 'modules/attachments/attachments_tables.php';

//new attachment from a template
if (isset($_SESSION['cm']['resMaster']) && $_SESSION['cm']['resMaster'] <> '') {
   $objectId = $_SESSION['cm']['resMaster'];
}

$_SESSION['cm']['resMaster'] = '';

$docserverControler = new docservers_controler();
$docserver = $docserverControler->getDocserverToInsert(
    $_SESSION['cm']['collId']
);

$collId = $_SESSION['cm']['collId'];
$_SESSION['cm']['collId'] = '';

if (empty($docserver)) {
    $_SESSION['error'] = _DOCSERVER_ERROR . ' : '
        . _NO_AVAILABLE_DOCSERVER . '. ' . _MORE_INFOS . '.';
    $location = '';
} else {
    // some checking on docserver size limit
    $newSize = $docserverControler->checkSize(
        $docserver, $_SESSION['upfile']['size']
    );
    if ($newSize == 0) {
        $_SESSION['error'] = _DOCSERVER_ERROR . ' : '
            . _NOT_ENOUGH_DISK_SPACE . '. ' . _MORE_INFOS . '.';
    } else {
        $fileInfos = array(
            'tmpDir'      => $_SESSION['config']['tmppath'],
            'size'        => $_SESSION['upfile']['size'],
            'format'      => $_SESSION['upfile']['format'],
            'tmpFileName' => $tmpFileName,
        );

        $storeResult = array();
        $storeResult = $docserverControler->storeResourceOnDocserver(
            $collId, $fileInfos
        );
        if (isset($storeResult['error']) && $storeResult['error'] <> '') {
            $_SESSION['error'] = $storeResult['error'];
        } else {
            $resAttach = new resource();
            $_SESSION['data'] = array();
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'typist',
                    'value' => $_SESSION['user']['UserId'],
                    'type' => 'string',
                )
            );
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'format',
                    'value' => $fileExtension,
                    'type' => 'string',
                )
            );
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'docserver_id',
                    'value' => $storeResult['docserver_id'],
                    'type' => 'string',
                )
            );
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'status',
                    'value' => 'NEW',
                    'type' => 'string',
                )
            );
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'offset_doc',
                    'value' => ' ',
                    'type' => 'string',
                )
            );
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'logical_adr',
                    'value' => ' ',
                    'type' => 'string',
                )
            );
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'title',
                    'value' => _NEW_ATTACHMENT_VERSION,
                    'type' => 'string',
                )
            );
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'coll_id',
                    'value' => $collId,
                    'type' => 'string',
                )
            );
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'res_id_master',
                    'value' => $objectId,
                    'type' => 'integer',
                )
            );
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'type_id',
                    'value' => 0,
                    'type' => 'int',
                )
            );
            //$_SESSION['error'] = 'test';
            $id = $resAttach->load_into_db(
                RES_ATTACHMENTS_TABLE,
                $storeResult['destination_dir'],
                $storeResult['file_destination_name'] ,
                $storeResult['path_template'],
                $storeResult['docserver_id'], 
                $_SESSION['data'],
                $_SESSION['config']['databasetype']
            );
            if ($id == false) {
                $_SESSION['error'] = $resAttach->get_error();
                //echo $resource->get_error();
                //$resource->show();
                //exit();
            } else {
                if ($_SESSION['history']['attachadd'] == "true") {
                    $hist = new history();
                    $sec = new security();
                    $view = $sec->retrieve_view_from_coll_id(
                        $collId
                    );
                    $hist->add(
                        $view, $objectId, 'ADD', 'attachadd',
                        ucfirst(_DOC_NUM) . $id . ' '
                        . _NEW_ATTACH_ADDED . ' ' . _TO_MASTER_DOCUMENT
                        . $objectId,
                        $_SESSION['config']['databasetype'],
                        'apps'
                    );
                    $_SESSION['error'] = _NEW_VERSION;
                    $hist->add(
                        RES_ATTACHMENTS_TABLE, $id, 'ADD','attachadd',
                        $_SESSION['error'] 
                        . _NEW_ATTACHMENT_VERSION,
                        $_SESSION['config']['databasetype'],
                        'attachments'
                    );
                }
            }
        }
    }
}
