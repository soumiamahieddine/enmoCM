<?php 

function createAip($resInContainer) {
	$fileList = "";
	$tmpDir = $GLOBALS['MaarchDirectory'] . "modules" . DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . mt_rand();
	mkdir($tmpDir);
	$newSourceFilePath = $GLOBALS['MaarchDirectory'] . "modules" .DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . mt_rand();
	for ($cptRes=0;$cptRes<count($resInContainer);$cptRes++) {
		$cp = copy($resInContainer[$cptRes]['source_path'], $tmpDir . DIRECTORY_SEPARATOR . $cptRes);
		if($cp == false) {
			$storeInfos = array('error'=>_DOCSERVER_COPY_ERROR);
			return $storeInfos;
		}
		$resInContainer[$cptRes]['offset_doc'] = $cptRes;
		$fileList .= escapeshellarg($tmpDir . DIRECTORY_SEPARATOR . $cptRes) . " ";
	}
	if(DIRECTORY_SEPARATOR == "/") {
		$command = "7z a -y " . escapeshellarg($newSourceFilePath) . " " . $fileList;
	} else {
		$command = "\"".str_replace("\\", "\\\\", $_SESSION['docserversFeatures']['DOCSERVERS']['PATHTOCOMPRESSTOOL']) . "\" a -y " . escapeshellarg($newSourceFilePath) . " " . $fileList;
	}
	$newSourceFilePath .= ".7z";
	$tmpCmd = "";
	exec($command, $tmpCmd, $ExecError);
	if($ExecError > 0) {
		print_r($ExecError);
	}
	$result['resInContainer'] = $resInContainer;
	$result['newSourceFilePath'] = $newSourceFilePath;
	createPDIHistory($resInContainer);
	createPDI($resInContainer);
	$PIArray = array();
	$PIArray['CIFingerprint'] = md5_file($result['newSourceFilePath']);
	$PIArray['fingerprintMode'] = "md5";
	$PIArray['aiuCount'] = count($resInContainer);
	$PIArray['ciName'] = "CI.xz";
	$PIArray['compressionModeCI'] = "LZMA2";
	$PIArray['pdiName'] = "PDI.xz";
	$PIArray['pdiHistoryName'] = "PDI_HISTORY.xz";
	$PIArray['compressionModeHistory'] = "LZMA2";
	createPackagingInformation($PIArray);
	$aipName = $GLOBALS['MaarchDirectory'] . "modules" .DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . mt_rand();
	$pdiHistoryName = $GLOBALS['MaarchDirectory'] . "modules" .DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "pdi_history.xml";
	$pdiName = $GLOBALS['MaarchDirectory'] . "modules" .DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "pdi.xml";
	$piName = $GLOBALS['MaarchDirectory'] . "modules" .DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "packaging_information.xml";
	if(DIRECTORY_SEPARATOR == "/") {
		$command = "7z a -y " . escapeshellarg($aipName) . " " . escapeshellarg($newSourceFilePath) . " " . escapeshellarg($pdiHistoryName) . " " . escapeshellarg($pdiName) . " " . escapeshellarg($piName);
	} else {
		$command = "\"".str_replace("\\", "\\\\", $_SESSION['docserversFeatures']['DOCSERVERS']['PATHTOCOMPRESSTOOL']) . "\" a -y " . escapeshellarg($aipName) . " " . escapeshellarg($newSourceFilePath) . " " . escapeshellarg($pdiHistoryName) . " " . escapeshellarg($pdiName) . " " . escapeshellarg($piName);
	}
	$newSourceFilePath = $aipName . ".7z";
	$tmpCmd = "";
	exec($command, $tmpCmd, $ExecError);
	if($ExecError > 0) {
		print_r($ExecError);
	}
	$result = array();
	$result['resInContainer'] = $resInContainer;
	$result['newSourceFilePath'] = $newSourceFilePath;
	return $result;
}

?>
