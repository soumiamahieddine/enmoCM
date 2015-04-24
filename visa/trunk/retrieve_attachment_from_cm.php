<?php
//case of res -> update attachment
require_once 'modules/attachments/attachments_tables.php';
$dbAttachment = new dbquery();
$dbAttachment->connect();
$dbAttachment->query("SELECT relation, docserver_id, path, filename, format 
                        FROM res_view_attachments 
                        WHERE (res_id = " . $objectId . " OR res_id_version = ".$objectId.") AND res_id_master = ".$_SESSION['doc_id']
                         ." ORDER BY relation desc"
);

if ($dbAttachment->nb_result() == 0) {
    $result = array('ERROR' => _THE_DOC . ' ' . _EXISTS_OR_RIGHT);
    createXML('ERROR', $result);
} else {
    $line = $dbAttachment->fetch_object();
    $docserver = $line->docserver_id;
    $path = $line->path;
    $filename = $line->filename;
    $format = $line->format;
    $dbAttachment->query(
        "select path_template from " . _DOCSERVERS_TABLE_NAME
        . " where docserver_id = '" . $docserver . "'"
    );
    $func = new functions();
    $lineDoc = $dbAttachment->fetch_object();
	
	$_SESSION['visa']['repSignRel'] = $lineDoc->relation;
	$_SESSION['visa']['repSignId'] = $objectId;
	
    $docserver = $lineDoc->path_template;
    $fileOnDs = $docserver . $path . str_replace(pathinfo($filename, PATHINFO_EXTENSION), "pdf",$filename);;
    $fileOnDs = str_replace('#', DIRECTORY_SEPARATOR, $fileOnDs);
    $fileExtension = $func->extractFileExt($fileOnDs);
    $fileNameOnTmp = 'tmp_file_' . $_SESSION['user']['UserId']
        . '_' . rand() . '.' . $fileExtension;
    $filePathOnTmp = $_SESSION['config']['tmppath'] . $fileNameOnTmp;
    if (!copy($fileOnDs, $filePathOnTmp)) {
        $result = array('ERROR' => _FAILED_TO_COPY_ON_TMP 
            . ':' . $fileOnDs . ' ' . $filePathOnTmp
        );
        createXML('ERROR', $result);
    }
}
