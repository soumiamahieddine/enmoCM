<?php 

function createAip($resInContainer) 
{
    if ($GLOBALS['func']->isDirNotEmpty($GLOBALS['TmpDirectory'])) {
        $GLOBALS['logger']->write(
            'tmp dir not empty:' . $GLOBALS['TmpDirectory'], 'ERROR', 19
        );
        exit(19);
    }
    $arrayOfFileToCompress = array();
    $tmpDir = $GLOBALS['TmpDirectory'] . DIRECTORY_SEPARATOR . mt_rand();
    mkdir($tmpDir);
    $newSourceFilePath = $GLOBALS['TmpDirectory'] . DIRECTORY_SEPARATOR 
                       . mt_rand();
    for ($cptRes=0;$cptRes<count($resInContainer);$cptRes++) {
        $resInContainer[$cptRes]['offset_doc'] = 
            str_pad($cptRes, 4, '0', STR_PAD_LEFT) . '.' 
            . strtolower(
                $GLOBALS['func']->extractFileExt(
                    $resInContainer[$cptRes]['source_path']
                )
            );
        $cp = copy(
            $resInContainer[$cptRes]['source_path'], 
            $tmpDir . DIRECTORY_SEPARATOR 
            . $resInContainer[$cptRes]['offset_doc']
        );
        $GLOBALS['docserverControler']->setRights(
            $tmpDir . DIRECTORY_SEPARATOR 
            . $resInContainer[$cptRes]['offset_doc'], 
            $GLOBALS['docservers'][$GLOBALS['currentStep']]
            ['docserver']['path_template']
        );
        if ($cp == false) {
            $storeInfos = array('error'=>_DOCSERVER_COPY_ERROR);
            return $storeInfos;
        }
        array_push(
            $arrayOfFileToCompress, 
            $tmpDir . DIRECTORY_SEPARATOR 
            . $resInContainer[$cptRes]['offset_doc']
        );
        $resInContainer[$cptRes]['offset_doc'] = 'CI.' 
            . strtolower(
                $GLOBALS['docservers'][$GLOBALS['currentStep']]
                ['compression_mode']
            ) 
            . '#' . $resInContainer[$cptRes]['offset_doc'];
    }
    //CI compression
    $newSourceFilePath = doCompression('CI', $arrayOfFileToCompress, $tmpDir);
    $result['resInContainer'] = $resInContainer;
    $result['newSourceFilePath'] = $newSourceFilePath;
    createPDIHistory($resInContainer);
    createPDI($resInContainer);
    $piArray = array();
    $piArray['CIFingerprint'] = 
        $GLOBALS['docserverControler']->doFingerprint(
            $result['newSourceFilePath'], 
            $GLOBALS['docservers'][$GLOBALS['currentStep']]['fingerprint_mode']
        );
    $piArray['fingerprintMode'] = 
        $GLOBALS['docservers'][$GLOBALS['currentStep']]['fingerprint_mode'];
    $piArray['aiuCount'] = count($resInContainer);
    $piArray['ciName'] = 'CI.' 
        . strtolower(
            $GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode']
        );
    $piArray['compressionModeCI'] = 
        $GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode'];
    $piArray['pdiName'] = 'PDI.' 
        . strtolower(
            $GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode']
        );
    $piArray['pdiHistoryName'] = 'PDI_HISTORY.' 
        . strtolower(
            $GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode']
        );
    $piArray['compressionModeHistory'] = 
        $GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode'];
    //PDI compression
    $pdiName = $GLOBALS['TmpDirectory'] . DIRECTORY_SEPARATOR . 'pdi.xml';
    $pdiName = doCompression('PDI', $pdiName);
    $piArray['PDIFingerprint'] = $GLOBALS['docserverControler']->doFingerprint(
        $pdiName, 
        $GLOBALS['docservers'][$GLOBALS['currentStep']]['fingerprint_mode']
    );
    //PDI_HISTORY compression
    $pdiHistoryName = $GLOBALS['TmpDirectory'] . DIRECTORY_SEPARATOR .
         'pdi_history.xml';
    $pdiHistoryName = doCompression('PDI_HISTORY', $pdiHistoryName);
    $piArray['PDIHISTORYFingerprint'] = 
        $GLOBALS['docserverControler']->doFingerprint(
            $pdiHistoryName, 
            $GLOBALS['docservers'][$GLOBALS['currentStep']]['fingerprint_mode']
        );
    createPackagingInformation($piArray);
    //AIP compression
    $aipName = $GLOBALS['TmpDirectory'] . DIRECTORY_SEPARATOR . mt_rand();
    $piName = $GLOBALS['TmpDirectory'] . DIRECTORY_SEPARATOR 
        . 'packaging_information.xml';
    $arrayOfFileToCompress = array();
    array_push($arrayOfFileToCompress, $newSourceFilePath);
    array_push($arrayOfFileToCompress, $pdiHistoryName);
    array_push($arrayOfFileToCompress, $pdiName);
    array_push($arrayOfFileToCompress, $piName);
    $newSourceFilePath = doCompression('AIP', $arrayOfFileToCompress);
    rename(
        $newSourceFilePath, 
        str_replace(
            strtolower(
                $GLOBALS['docservers'][$GLOBALS['currentStep']]
                ['compression_mode']
            ), 'aip', $newSourceFilePath
        )
    );
    $GLOBALS['docserverControler']->setRights(
        str_replace(
            strtolower(
                $GLOBALS['docservers'][$GLOBALS['currentStep']]
                ['compression_mode']
            ), 'aip', $newSourceFilePath
        ), 
        $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']
        ['path_template']
    );
    $result = array();
    $result['resInContainer'] = $resInContainer;
    $result['newSourceFilePath'] = str_replace(
        strtolower(
            $GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode']
        ), 'aip', $newSourceFilePath
    );
    return $result;
}

function createPDIHistory($resInContainer) 
{
    $tmpXML = $GLOBALS['TmpDirectory'] . DIRECTORY_SEPARATOR 
        . 'pdi_history.xml';
    $docXML = new DomDocument('1.0', 'utf-8');
    $docXML->preserveWhiteSpace = true;
    $docXML->formatOutput = true;
    //root
    $root = $docXML->createElement('ROOT');
    $docXML->appendChild($root);
    $commentString = _HISTORY_COMMENT_ROOT;
    $commentNodeRoot = $docXML->createComment($commentString);
    $root->appendChild($commentNodeRoot);
    for ($cptRes=0;$cptRes<count($resInContainer);$cptRes++) {
        //a record
        $pdiHistory = $docXML->createElement('PDI_HISTORY');
        $pdiHistory->setAttributeNode(
            new DOMAttr('AIU', $resInContainer[$cptRes]['offset_doc'])
        );
        $pdiHistory->setAttributeNode(
            new DOMAttr('RES_ID', $resInContainer[$cptRes]['res_id'])
        );
        $root->appendChild($pdiHistory);
        $query = "select * from history where record_id = '" 
               . $resInContainer[$cptRes]['res_id'] . "' and (table_name = '" 
               . $GLOBALS['table'] . "' or table_name = '" 
               . $GLOBALS['adrTable'] . "' or table_name = '" 
               . $GLOBALS['view'] . "') order by event_date";
        do_query($GLOBALS['db3'], $query);
        while ($historyRecordset = $GLOBALS['db3']->fetch_object()) {
            //an event
            $event = $docXML->createElement('EVENT');
            $pdiHistory->appendChild($event);
            $eventType = $docXML->createElement(
                'EVENT_TYPE', $historyRecordset->event_type
            );
            $event->appendChild($eventType);
            $eventType->setAttributeNode(new DOMAttr('SOURCE', 'HIST'));
            $userId = $docXML->createElement(
                'USER_ID', $historyRecordset->user_id
            );
            $event->appendChild($userId);
            $userId->setAttributeNode(new DOMAttr('SOURCE', 'HIST'));
            $eventDate = $docXML->createElement(
                'EVENT_DATE', $historyRecordset->event_date
            );
            $event->appendChild($eventDate);
            $eventDate->setAttributeNode(new DOMAttr('SOURCE', 'HIST'));
            $idModule = $docXML->createElement(
                'ID_MODULE', $historyRecordset->id_module
            );
            $event->appendChild($idModule);
            $idModule->setAttributeNode(new DOMAttr('SOURCE', 'HIST'));
            $info = $docXML->createElement(
                'INFO', $GLOBALS['func']->wash_html(
                    $historyRecordset->info, 'NO_ACCENT'
                )
            );
            $event->appendChild($info);
            $info->setAttributeNode(new DOMAttr('SOURCE', 'HIST'));
        }
    }
    //save the xml
    $docXML->save($tmpXML);
}

function createPackagingInformation($piArray) 
{
    $tmpXML = $GLOBALS['TmpDirectory'] . DIRECTORY_SEPARATOR 
            . 'packaging_information.xml';
    $docXML = new DomDocument('1.0', 'utf-8');
    $docXML->preserveWhiteSpace = true;
    $docXML->formatOutput = true;
    //root
    $root = $docXML->createElement('ROOT');
    $docXML->appendChild($root);
    $commentString = _PI_COMMENT_ROOT;
    $commentNodeRoot = $docXML->createComment($commentString);
    $root->appendChild($commentNodeRoot);
    //general
    $general = $docXML->createElement('GENERAL');
    $root->appendChild($general);
    $fingerprint = $docXML->createElement(
        'FINGERPRINT', $piArray['CIFingerprint']
    );
    $general->appendChild($fingerprint);
    $commentString = _PI_COMMENT_FINGERPRINT;
    $commentNodeFingerprint = $docXML->createComment($commentString);
    $fingerprint->appendChild($commentNodeFingerprint);
    $fingerprintMode = $docXML->createElement(
        'FINGERPRINT_MODE', $piArray['fingerprintMode']
    );
    $general->appendChild($fingerprintMode);
    $aiuCount = $docXML->createElement('AIU_COUNT', $piArray['aiuCount']);
    $general->appendChild($aiuCount);
    $commentString = _PI_COMMENT_AIU;
    $commentNodeAiuCount = $docXML->createComment($commentString);
    $aiuCount->appendChild($commentNodeAiuCount);
    //content
    $content = $docXML->createElement('CONTENT');
    $root->appendChild($content);
    $commentString = _PI_COMMENT_CONTENT;
    $commentNodeContent = $docXML->createComment($commentString);
    $content->appendChild($commentNodeContent);
    $contentFile = $docXML->createElement('CONTENT_FILE', $piArray['ciName']);
    $content->appendChild($contentFile);
    $compressionMode = $docXML->createElement(
        'COMPRESSION_MODE', $piArray['compressionModeCI']
    );
    $content->appendChild($compressionMode);
    //pdi
    $pdi = $docXML->createElement('PDI');
    $root->appendChild($pdi);
    $commentString = _PI_COMMENT_PDI;
    $commentNodePdi = $docXML->createComment($commentString);
    $pdi->appendChild($commentNodePdi);
    $pdiFile = $docXML->createElement('PDI_FILE', $piArray['pdiName']);
    $pdi->appendChild($pdiFile);
    $pdiFingerprint = $docXML->createElement(
        'PDI_FINGERPRINT', $piArray['PDIFingerprint']
    );
    $pdi->appendChild($pdiFingerprint);
    $historyFile = $docXML->createElement(
        'HISTORY_FILE', $piArray['pdiHistoryName']
    );
    $pdi->appendChild($historyFile);
    $pdiHistoryFingerprint = $docXML->createElement(
        'HISTORY_FINGERPRINT', $piArray['PDIHISTORYFingerprint']
    );
    $pdi->appendChild($pdiHistoryFingerprint);
    $compressionModeHistory = $docXML->createElement(
        'COMPRESSION_MODE', $piArray['compressionModeHistory']
    );
    $pdi->appendChild($compressionModeHistory);
    $fingerprintModePDI = $docXML->createElement(
        'FINGERPRINT_MODE', $piArray['fingerprintMode']
    );
    $pdi->appendChild($fingerprintModePDI);
    //save the xml
    $docXML->save($tmpXML);
}
