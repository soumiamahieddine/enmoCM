<?php 

function doCompression($targeFile, $arrayOfFileToCompress) {
	if (!is_array($arrayOfFileToCompress)) {
		$arr = array();
		$arr[0] = $arrayOfFileToCompress;
		$arrayOfFileToCompress = $arr;
	}
	$tmpCmd = "";
	for ($cpt=0;$cpt<count($arrayOfFileToCompress);$cpt++) {
		$listOfFileToCompress .= " " . escapeshellarg($arrayOfFileToCompress[$cpt]);
	}
	if (DIRECTORY_SEPARATOR == "/") {
		$command = "7z a -y -t" . strtolower($GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode']) . " " . escapeshellarg($GLOBALS['TmpDirectory'] . $targeFile) . " " . $listOfFileToCompress;
	} else {
		$command = "\"" . str_replace("\\", "\\\\", $_SESSION['docserversFeatures']['DOCSERVERS']['PATHTOCOMPRESSTOOL']) . "\" a -y -t" . strtolower($GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode']) . " " . escapeshellarg($GLOBALS['TmpDirectory'] . $targeFile) . " " . $listOfFileToCompress;
	}
	//echo $command."\r\n";exit;
	exec($command, $tmpCmd, $ExecError);
	if($ExecError > 0) {
		$GLOBALS['logger']->write('Pb with compression:' .$command . ' ' . $tmpCmd, 'ERROR', 23);
		exit(23);
	}
	return $GLOBALS['TmpDirectory'] . $targeFile . "." . strtolower($GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode']);
}

function extractAip($path) {
	$path = str_replace('#', DIRECTORY_SEPARATOR, $path);
	$fileNameOnTmp = $GLOBALS['TmpDirectory'] . rand();
	$cp = copy($path, $fileNameOnTmp);
	controlFingerprint($path, $fileNameOnTmp);
	if(DIRECTORY_SEPARATOR == "/") {
		$command = "7z e -y -o".escapeshellarg($GLOBALS['TmpDirectory'])." ".escapeshellarg($fileNameOnTmp);
	} else {
		$command = "\"" . str_replace("\\", "\\\\", $_SESSION['docserversFeatures']['DOCSERVERS']['PATHTOCOMPRESSTOOL']) . "\" e -y -o" . escapeshellarg($GLOBALS['TmpDirectory'])." ".escapeshellarg($fileNameOnTmp);
	}
	$tmpCmd = "";
	exec($command, $tmpCmd, $_exec_error);
	if($ExecError > 0) {
		$GLOBALS['logger']->write('Pb with extract:' . $command . ' ' . $tmpCmd, 'ERROR', 24);
		exit(24);
	}
	unlink($fileNameOnTmp);
	mkdir($GLOBALS['TmpDirectory'] . "CI", 0777);
	$fileNameOnTmp = $GLOBALS['TmpDirectory'] . "CI." . strtolower($GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode']);
	if(DIRECTORY_SEPARATOR == "/") {
		$command = "7z e -y -o" . escapeshellarg($GLOBALS['TmpDirectory'] . "CI") . " " . escapeshellarg($fileNameOnTmp);
	} else {
		$command = "\"" . str_replace("\\", "\\\\", $_SESSION['docserversFeatures']['DOCSERVERS']['PATHTOCOMPRESSTOOL']) . "\" e -y -o" . escapeshellarg($GLOBALS['TmpDirectory'] . "CI") . " " . escapeshellarg($fileNameOnTmp);
	}
	$tmpCmd = "";
	exec($command, $tmpCmd, $_exec_error);
	if($ExecError > 0) {
		$GLOBALS['logger']->write('Pb with extract:' . $command . ' ' . $tmpCmd, 'ERROR', 24);
		exit(24);
	}
}

function doFingerprint($path) {
	if ($GLOBALS['docservers'][$GLOBALS['currentStep']]['fingerprint_mode'] == "NONE" || $GLOBALS['docservers'][$GLOBALS['currentStep']]['fingerprint_mode'] == "") {
		return "";
	} else {
		return hash_file(strtolower($GLOBALS['docservers'][$GLOBALS['currentStep']]['fingerprint_mode']), $path);
	}
}

function controlIntegrityOfTransfert($currentRecordInStack, $resInContainer, $destinationDir, $fileDestinationName, $fileOffsetDoc) {
	if (is_array($resInContainer) && count($resInContainer) > 0) {
		extractAip($GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template'] . $destinationDir . $fileDestinationName);
		for ($cptRes=0;$cptRes<count($resInContainer);$cptRes++) {
			controlFingerprint($resInContainer[$cptRes]['source_path'], $GLOBALS['TmpDirectory'] . "CI" . DIRECTORY_SEPARATOR . str_replace("CI." . strtolower($GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode']) . "#", "", $resInContainer[$cptRes]['offset_doc']));
		}
	} else {
		$sourceFilePath = getSourceResourcePath($currentRecordInStack['res_id']);
		controlFingerprint($sourceFilePath, $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template'] . str_replace("#", DIRECTORY_SEPARATOR, $destinationDir . $fileDestinationName));
	}
	//$GLOBALS['docserverControler']->washTmp($GLOBALS['TmpDirectory'], true);
}

function controlIntegrityOfSource($currentRecordInStack) {
	$sourceFilePath = getSourceResourcePath($currentRecordInStack);
	$query = "select fingerprint from " . $GLOBALS['table'] . " where res_id = " . $currentRecordInStack;
	do_query($GLOBALS['db'], $query);
	$resRecordset = $GLOBALS['db']->fetch_object();
	if (md5_file($sourceFilePath) <> $resRecordset->fingerprint) {
		$GLOBALS['logger']->write('Pb with fingerprint of the source:' . $currentRecordInStack . ' ' . $sourceFilePath, 'ERROR', 25);
		exit(25);
	}
	$GLOBALS['docserverControler']->washTmp($GLOBALS['TmpDirectory'], true);
}

function controlFingerprint($pathInit, $pathTarget) {
	if (md5_file($pathInit) <> md5_file($pathTarget)) {
		$GLOBALS['logger']->write('Pb during transfert of file (fingerprint control) between ' . $pathInit . ' and ' . $pathTarget, 'ERROR', 22);
		exit(22);
	}
}

?>
