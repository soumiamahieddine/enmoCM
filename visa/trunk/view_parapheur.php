<?php

	
	$filePathOnTmp = $_REQUEST['path']. DIRECTORY_SEPARATOR . $_REQUEST['filename'];
	
	$viewResourceArr['mime_type'] = "application/pdf";
	$viewResourceArr['ext'] = "pdf";
	$viewResourceArr['status'] = "ok";
	include('apps' . DIRECTORY_SEPARATOR 
                . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR 
                . 'indexing_searching' . DIRECTORY_SEPARATOR 
                . 'view_resource.php');
	
?>