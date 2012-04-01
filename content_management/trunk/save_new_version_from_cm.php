<?php

//TODO: REPLACE UPFILE VAR !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

if ($_SESSION['cm']['resMaster'] <> '') {
    $resMaster = $_SESSION['cm']['resMaster'];
} else {
    $resMaster = $objectId;
}

$_SESSION['cm']['resMaster'] = '';

$security = new security();
$versionTable = $security->retrieve_version_table_from_coll_id(
    $_SESSION['cm']['collId']
);

$docserverControler = new docservers_controler();
$docserver = $docserverControler->getDocserverToInsert(
    $_SESSION['cm']['collId']
);
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
            $_SESSION['cm']['collId'], $fileInfos
        );
        if (isset($storeResult['error']) && $storeResult['error'] <> '') {
            $_SESSION['error'] = $storeResult['error'];
        } else {
            $resVersion = new resource();
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
                    'value' => 'new version of original resource',
                    'type' => 'string',
                )
            );
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'coll_id',
                    'value' => $_SESSION['cm']['collId'],
                    'type' => 'string',
                )
            );
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'res_id_master',
                    'value' => $resMaster,
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
            $id = $resVersion->load_into_db(
                $versionTable,
                $storeResult['destination_dir'],
                $storeResult['file_destination_name'] ,
                $storeResult['path_template'],
                $storeResult['docserver_id'], 
                $_SESSION['data'],
                $_SESSION['config']['databasetype']
            );
            if ($id == false) {
                $_SESSION['error'] = $resVersion->get_error();
                //echo $resource->get_error();
                //$resource->show();
                //exit();
            } else {
                //if ($_SESSION['history']['cmadd'] == "true") {
                    $hist = new history();
                    $sec = new security();
                    $view = $sec->retrieve_view_from_coll_id(
                        $_SESSION['cm']['collId']
                    );
                    $hist->add(
                        $view, $resMaster, 'ADD', 'cmadd',
                        ucfirst(_DOC_NUM) . $id . ' '
                        . _NEW_VERSION_ADDED . ' ' . _TO_MASTER_DOCUMENT
                        . $resMaster,
                        $_SESSION['config']['databasetype'],
                        'apps'
                    );
                    $_SESSION['error'] = _NEW_VERSION;
                    $hist->add(
                        $versionTable, $id, 'ADD','cmadd',
                        $_SESSION['error'] 
                        . " (new version of original resource) ",
                        $_SESSION['config']['databasetype'],
                        'content_management'
                    );
                //}
            }
        }
    }
}
