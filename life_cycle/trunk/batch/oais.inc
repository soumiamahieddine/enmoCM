<?php 

function createAip($resInContainer) {
	if(isDirNotEmpty($GLOBALS['MaarchDirectory'] . "modules" . DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR)) {
		echo "WARNING\r\n";
		$GLOBALS['logger']->write("WARNING!!!", 'INFO');
	}
	$arrayOfFileToCompress = array();
	$tmpDir = $GLOBALS['MaarchDirectory'] . "modules" . DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . mt_rand();
	mkdir($tmpDir);
	$newSourceFilePath = $GLOBALS['MaarchDirectory'] . "modules" .DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . mt_rand();
	for ($cptRes=0;$cptRes<count($resInContainer);$cptRes++) {
		$resInContainer[$cptRes]['offset_doc'] = str_pad($cptRes, 4, "0", STR_PAD_LEFT) . "." . strtolower(extractFileExt($resInContainer[$cptRes]['source_path']));
		$cp = copy($resInContainer[$cptRes]['source_path'], $tmpDir . DIRECTORY_SEPARATOR . $resInContainer[$cptRes]['offset_doc']);
		if($cp == false) {
			$storeInfos = array('error'=>_DOCSERVER_COPY_ERROR);
			return $storeInfos;
		}
		array_push($arrayOfFileToCompress, $tmpDir . DIRECTORY_SEPARATOR . $resInContainer[$cptRes]['offset_doc']);
		$resInContainer[$cptRes]['offset_doc'] = "CI.7z#" . $resInContainer[$cptRes]['offset_doc'];
	}
	//CI compression
	$newSourceFilePath = doCompression("CI", $arrayOfFileToCompress);
	$result['resInContainer'] = $resInContainer;
	$result['newSourceFilePath'] = $newSourceFilePath;
	createPDIHistory($resInContainer);
	createPDI($resInContainer);
	$PIArray = array();
	$PIArray['CIFingerprint'] = md5_file($result['newSourceFilePath']);
	$PIArray['fingerprintMode'] = "md5";
	$PIArray['aiuCount'] = count($resInContainer);
	$PIArray['ciName'] = "CI.7z";
	$PIArray['compressionModeCI'] = "LZMA2";
	$PIArray['pdiName'] = "PDI.7z";
	$PIArray['pdiHistoryName'] = "PDI_HISTORY.7z";
	$PIArray['compressionModeHistory'] = "LZMA2";
	createPackagingInformation($PIArray);
	//PDI compression
	$pdiName = $GLOBALS['MaarchDirectory'] . "modules" . DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "pdi.xml";
	$pdiName = doCompression("PDI", $pdiName);
	//PDI_HISTORY compression
	$pdiHistoryName = $GLOBALS['MaarchDirectory'] . "modules" . DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "pdi_history.xml";
	$pdiHistoryName = doCompression("PDI_HISTORY", $pdiHistoryName);
	//AIP compression
	$aipName = $GLOBALS['MaarchDirectory'] . "modules" . DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . mt_rand();
	$piName = $GLOBALS['MaarchDirectory'] . "modules" . DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "packaging_information.xml";
	$arrayOfFileToCompress = array();
	array_push($arrayOfFileToCompress, $newSourceFilePath);
	array_push($arrayOfFileToCompress, $pdiHistoryName);
	array_push($arrayOfFileToCompress, $pdiName);
	array_push($arrayOfFileToCompress, $piName);
	$newSourceFilePath = doCompression("AIP", $arrayOfFileToCompress);
	rename($newSourceFilePath, str_replace("7z", "aip", $newSourceFilePath));
	$result = array();
	$result['resInContainer'] = $resInContainer;
	$result['newSourceFilePath'] = str_replace("7z", "aip", $newSourceFilePath);
	return $result;
}

function doCompression($targeFile, $arrayOfFileToCompress) {
	if(!is_array($arrayOfFileToCompress)) {
		$arr = array();
		$arr[0] = $arrayOfFileToCompress;
		$arrayOfFileToCompress = $arr;
	}
	$tmpRoot = $GLOBALS['MaarchDirectory'] . "modules" . DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR;
	$tmpCmd = "";
	for($cpt=0;$cpt<count($arrayOfFileToCompress);$cpt++) {
		$listOfFileToCompress .= " " . escapeshellarg($arrayOfFileToCompress[$cpt]);
	}
	if (DIRECTORY_SEPARATOR == "/") {
		$command = "7z a -y " . escapeshellarg($tmpRoot . $targeFile) . " " . $listOfFileToCompress;
	} else {
		$command = "\"".str_replace("\\", "\\\\", $_SESSION['docserversFeatures']['DOCSERVERS']['PATHTOCOMPRESSTOOL']) . "\" a -y " . escapeshellarg($tmpRoot . $targeFile) . " " . $listOfFileToCompress;
	}
	exec($command, $tmpCmd, $ExecError);
	if($ExecError > 0) {
		//MANAGEMENT OF ERRORS
		print_r($ExecError);
	}
	return $tmpRoot . $targeFile . ".7z";
}

?>
