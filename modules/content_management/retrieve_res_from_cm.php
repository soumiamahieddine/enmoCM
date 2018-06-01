<?php

//FOR ADD RES VERSIONS, ATTACHMENT
//case of res -> master or version
$sec = new security();
$resId = $_REQUEST['objectId'];
$collId = $sec->retrieve_coll_id_from_table($objectTable);
$_SESSION['cm']['collId'] = $collId;
for (
    $cptColl = 0;
    $cptColl < count($_SESSION['collections']);
    $cptColl++
) {
    if ($objectTable == $_SESSION['collections'][$cptColl]['table']
        || $objectTable == $_SESSION['collections'][$cptColl]['view']
    ) {
        $adrTable = $_SESSION['collections'][$cptColl]['adr'];
    } else {
        $adrTable = '';
    }
}
$docserverControler = new docservers_controler();

$viewResourceArr = $docserverControler->viewResource(
    $resId,
    $objectTable,
    $adrTable,
    false
);
if ($viewResourceArr['error'] <> '') {
    $result = array('ERROR' => $viewResourceArr['error']);
    createXML('ERROR', $result);
} else {
    $filePathOnTmp = $viewResourceArr['file_path'];
    $fileExtension = $viewResourceArr['ext'];
}
