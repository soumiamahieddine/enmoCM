<?php

//FOR ADD NEW ATTACHMENTS



require_once 'modules/attachments/attachments_tables.php';

//new attachment from a template
if (isset($_SESSION['cm']['resMaster']) && $_SESSION['cm']['resMaster'] <> '') {
   $objectId = $_SESSION['cm']['resMaster'];
}

$_SESSION['cm']['resMaster'] = '';

$collId =  $_SESSION['current_basket']['coll_id'];

$docserverControler = new docservers_controler();
$docserver = $docserverControler->getDocserverToInsert(
   $collId
);




if (empty($docserver)) {
    $_SESSION['error'] = _DOCSERVER_ERROR . ' : '
        . _NO_AVAILABLE_DOCSERVER . '. ' . _MORE_INFOS;
} else {
    // some checking on docserver size limit
    $newSize = $docserverControler->checkSize(
        $docserver, filesize($_SESSION['config']['tmppath'] . $tmpFileName)
    );
    if ($newSize == 0) {
        $_SESSION['error'] = _DOCSERVER_ERROR . ' : '
            . _NOT_ENOUGH_DISK_SPACE . '. ' . _MORE_INFOS . '.';
    } else {
        $fileInfos = array(
            'tmpDir'      => $_SESSION['config']['tmppath'],
            'size'        => filesize($_SESSION['config']['tmppath'] . $tmpFileName),
            'format'      => strtoupper($fileExtension),
            'tmpFileName' => $tmpFileName,
        );

        $storeResult = array();
        $storeResult = $docserverControler->storeResourceOnDocserver(
            $collId, $fileInfos
        );
        if (isset($storeResult['error']) && $storeResult['error'] <> '') {
            $_SESSION['error'] = $storeResult['error'];
        } else {
			
			require_once "core/class/class_request.php";
			$req = new request();
			$req->connect();
			if ($_SESSION['visa']['repSignRel'] > 1) {
                $req->query("UPDATE res_version_attachments set status = 'OBS' WHERE res_id = ".$_SESSION['visa']['repSignId']);
            } else {
               $req->query("UPDATE res_attachments set status = 'OBS' WHERE res_id = ".$_SESSION['visa']['repSignId']);
            }
						
			unset($_SESSION['visa']['repSignRel']);
			unset($_SESSION['visa']['repSignId']);
			
			
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
                    'value' => 'SIGN',
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
                    'value' => $_SESSION['visa']['last_resId_signed']['title'],
                    'type' => 'string',
                )
            );
			array_push(
                $_SESSION['data'],
                array(
                    'column' => 'relation',
                    'value' => 1,
                    'type' => 'integer',
                )
            );
            $_SESSION['cm']['templateStyle'] = '';
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
                    'value' => $_SESSION['visa']['last_resId_signed']['res_id'],
                    'type' => 'integer',
                )
            );
			unset($_SESSION['visa']['last_resId_signed']);
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'type_id',
                    'value' => 0,
                    'type' => 'int',
                )
            );
            array_push(
                $_SESSION['data'],
                array(
                    'column' => 'identifier',
                    'value' => '1',
                    'type' => 'string',
                )
            );
			array_push(
                $_SESSION['data'],
                array(
                    'column' => 'attachment_type',
                    'value' => 'signed_response',
                    'type' => 'string',
                )
            );
			writeLogIndex("Insertion BDD");
			writeLogIndex("Paramètres load into DB");
			writeLogIndex("destination dir = ".$storeResult['destination_dir']);
			writeLogIndex("file_destination_name = ".$storeResult['file_destination_name']);
			writeLogIndex("path_template = ".$storeResult['path_template']);
			writeLogIndex("docserver_id = ".$storeResult['docserver_id']);
			writeLogIndex("data = ".print_r($_SESSION['data'],true));
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
			
			writeLogIndex("Fin insertion BDD");
			
			$_SESSION['visa']['last_ans_signed'] = $id;
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
                    $hist->add(
                        RES_ATTACHMENTS_TABLE, $id, 'ADD','attachadd',
                        $_SESSION['error'] 
                        . _NEW_ATTACHMENT,
                        $_SESSION['config']['databasetype'],
                        'attachments'
                    );
                }
            }
        }
    }
}
