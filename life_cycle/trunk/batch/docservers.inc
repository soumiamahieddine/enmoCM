<?php 

function doCompression($targeFile, $arrayOfFileToCompress, $dirToCompress = "") {
	if ($dirToCompress <> "") {
		$listOfFileToCompress = " -r " . $dirToCompress;
	} else {
		if (!is_array($arrayOfFileToCompress)) {
			$arr = array();
			$arr[0] = $arrayOfFileToCompress;
			$arrayOfFileToCompress = $arr;
		}
		$tmpCmd = "";
		for ($cpt=0;$cpt<count($arrayOfFileToCompress);$cpt++) {
			$listOfFileToCompress .= " " . escapeshellarg($arrayOfFileToCompress[$cpt]);
		}
	}
	if (DIRECTORY_SEPARATOR == "/") {
		$command = "7z a -y -t" . strtolower($GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode']) . " " . escapeshellarg($GLOBALS['TmpDirectory'] . $targeFile) . " " . $listOfFileToCompress;
	} else {
		$command = "\"" . str_replace("\\", "\\\\", $GLOBALS['docserversFeatures']['DOCSERVERS']['PATHTOCOMPRESSTOOL']) . "\" a -y -t" . strtolower($GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode']) . " " . escapeshellarg($GLOBALS['TmpDirectory'] . $targeFile) . " " . $listOfFileToCompress;
	}
	//echo $command."\r\n";exit;
	exec($command, $tmpCmd, $execError);
	if($execError > 0) {
		$GLOBALS['logger']->write('Pb with compression:' .$command . ' ' . print_r($tmpCmd), 'ERROR', 23);
		exit(23);
	}
	return $GLOBALS['TmpDirectory'] . $targeFile . "." . strtolower($GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode']);
}

function extractAip($path) {
	$path = str_replace('#', DIRECTORY_SEPARATOR, $path);
	$fileNameOnTmp = $GLOBALS['TmpDirectory'] . rand();
	$cp = copy($path, $fileNameOnTmp);
	$GLOBALS['docserverControler']->setRights($fileNameOnTmp, $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template']);
	$control = array();
	$control = $GLOBALS['docserverControler']->controlFingerprint($path, $fileNameOnTmp, $GLOBALS['docserverSourceFingerprint']);
	if($control['error'] <> "") {
		$GLOBALS['logger']->write($control['error'], 'ERROR', 22);
		exit(22);
	}
	if(DIRECTORY_SEPARATOR == "/") {
		$command = "7z e -y -o".escapeshellarg($GLOBALS['TmpDirectory'])." ".escapeshellarg($fileNameOnTmp);
	} else {
		$command = "\"" . str_replace("\\", "\\\\", $GLOBALS['docserversFeatures']['DOCSERVERS']['PATHTOCOMPRESSTOOL']) . "\" e -y -o" . escapeshellarg($GLOBALS['TmpDirectory'])." ".escapeshellarg($fileNameOnTmp);
	}
	$tmpCmd = "";
	exec($command, $tmpCmd, $execError);
	if($execError > 0) {
		$GLOBALS['logger']->write('Pb with extract:' . $command . ' ' . print_r($tmpCmd), 'ERROR', 24);
		exit(24);
	}
	unlink($fileNameOnTmp);
	mkdir($GLOBALS['TmpDirectory'] . "CI", 0777);
	$fileNameOnTmp = $GLOBALS['TmpDirectory'] . "CI." . strtolower($GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode']);
	if(DIRECTORY_SEPARATOR == "/") {
		$command = "7z e -y -o" . escapeshellarg($GLOBALS['TmpDirectory'] . "CI") . " " . escapeshellarg($fileNameOnTmp);
	} else {
		$command = "\"" . str_replace("\\", "\\\\", $GLOBALS['docserversFeatures']['DOCSERVERS']['PATHTOCOMPRESSTOOL']) . "\" e -y -o" . escapeshellarg($GLOBALS['TmpDirectory'] . "CI") . " " . escapeshellarg($fileNameOnTmp);
	}
	$tmpCmd = "";
	exec($command, $tmpCmd, $execError);
	if($execError > 0) {
		$GLOBALS['logger']->write('Pb with extract:' . $command . ' ' . print_r($tmpCmd), 'ERROR', 24);
		exit(24);
	}
}

function controlIntegrityOfTransfert($currentRecordInStack, $resInContainer, $destinationDir, $fileDestinationName, $fileOffsetDoc) {
	if (is_array($resInContainer) && count($resInContainer) > 0) {
		extractAip($GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template'] . $destinationDir . $fileDestinationName);
		for ($cptRes=0;$cptRes<count($resInContainer);$cptRes++) {
			$control = array();
			$control = $GLOBALS['docserverControler']->controlFingerprint($resInContainer[$cptRes]['source_path'], $GLOBALS['TmpDirectory'] . "CI" . DIRECTORY_SEPARATOR . str_replace("CI." . strtolower($GLOBALS['docservers'][$GLOBALS['currentStep']]['compression_mode']) . "#", "", $resInContainer[$cptRes]['offset_doc']), $GLOBALS['docserverSourceFingerprint']);
			if($control['error'] <> "") {
				$GLOBALS['logger']->write($control['error'], 'ERROR', 22);
				exit(22);
			}
		}
	} else {
		$sourceFilePath = getSourceResourcePath($currentRecordInStack['res_id']);
		$control = array();
		$control = $GLOBALS['docserverControler']->controlFingerprint($sourceFilePath, $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template'] . str_replace("#", DIRECTORY_SEPARATOR, $destinationDir . $fileDestinationName), $GLOBALS['docserverSourceFingerprint']);
		if($control['error'] <> "") {
			$GLOBALS['logger']->write($control['error'], 'ERROR', 22);
			exit(22);
		}
	}
	//$GLOBALS['docserverControler']->washTmp($GLOBALS['TmpDirectory'], true);
}

function controlIntegrityOfSource($currentRecordInStack) {
	$sourceFilePath = getSourceResourcePath($currentRecordInStack);
	$query = "select fingerprint from " . $GLOBALS['table'] . " where res_id = " . $currentRecordInStack;
	do_query($GLOBALS['db'], $query);
	$resRecordset = $GLOBALS['db']->fetch_object();
	if ($GLOBALS['docserverControler']->doFingerprint($sourceFilePath, $GLOBALS['docserverSourceFingerprint']) <> $resRecordset->fingerprint) {
		$GLOBALS['logger']->write('Pb with fingerprint of the source:' . $currentRecordInStack . ' ' . $sourceFilePath, 'ERROR', 25);
		exit(25);
	}
	$GLOBALS['docserverControler']->washTmp($GLOBALS['TmpDirectory'], true);
}

function setSize($docserverId, $newSize) {
	$query = "update "._DOCSERVERS_TABLE_NAME." set actual_size_number=" . $newSize . " where docserver_id='" . $docserverId . "'";
	do_query($GLOBALS['db'], $query);
}

?>
