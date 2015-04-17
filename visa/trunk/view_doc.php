<?php
	require_once('apps' . DIRECTORY_SEPARATOR 
                . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR 
                . 'class' . DIRECTORY_SEPARATOR 
                . "class_indexing_searching_app.php");
	$is = new indexing_searching_app();
	
	
	$filePathOnTmp = $_REQUEST['path'];
	if ($filePathOnTmp == "last") {
		$path_tmp = $_SESSION['generated_file'];
		$filename_pdf = str_replace(pathinfo($path_tmp, PATHINFO_EXTENSION), "pdf",$path_tmp);
		$filePathOnTmp = $filename_pdf;
	}
	$viewResourceArr['ext'] = pathinfo($filePathOnTmp, PATHINFO_EXTENSION);
	$mimeType = $is->get_mime_type($viewResourceArr['ext']);
	$viewResourceArr['mime_type'] = $mimeType;
	
	$viewResourceArr['status'] = "ok";
	
	//echo $filePathOnTmp;
	include('apps' . DIRECTORY_SEPARATOR 
                . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR 
                . 'indexing_searching' . DIRECTORY_SEPARATOR 
                . 'view_resource.php');
?>