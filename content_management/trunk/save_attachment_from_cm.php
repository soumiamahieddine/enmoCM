<?php

// FOR ADD, UP TEMPLATES

/*$_SESSION['m_admin']['templates']['current_style'] 
        = $_SESSION['config']['tmppath'] . $tmpFileName;
$_SESSION['m_admin']['templates']['applet'] = true;*/

$_SESSION['upfile']['tmp_name'] = $_SESSION['config']['tmppath'] . $tmpFileName;

$_SESSION['upfile']['size'] = filesize($_SESSION['config']['tmppath'] . $tmpFileName);

$_SESSION['upfile']['error'] = "";

$_SESSION['upfile']['fileNameOnTmp'] = $tmpFileName;

$_SESSION['upfile']['format'] = $fileExtension;

$_SESSION['m_admin']['templates']['applet'] = true;

$_SESSION['upfile']['upAttachment'] = true;

if ($_SESSION['modules_loaded']['attachments']['convertPdf'] == true){
	$_SESSION['upfile']['fileNamePdfOnTmp'] = $tmpFilePdfName;
}