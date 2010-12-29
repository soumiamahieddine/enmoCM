<?php 

function createPathOnDocServer($docServer) {
	if (!is_dir($docServer . date("Y") . DIRECTORY_SEPARATOR)) {
		mkdir($docServer . date("Y") . DIRECTORY_SEPARATOR, 0777);
	}
	if (!is_dir($docServer . date("Y") . DIRECTORY_SEPARATOR.date("m") . DIRECTORY_SEPARATOR)) {
		mkdir($docServer . date("Y") . DIRECTORY_SEPARATOR.date("m") . DIRECTORY_SEPARATOR, 0777);
	}
	if (!is_dir($docServer . date("Y") . DIRECTORY_SEPARATOR.date("m") . DIRECTORY_SEPARATOR . $GLOBALS['wb'] . DIRECTORY_SEPARATOR)) {
		mkdir($docServer . date("Y") . DIRECTORY_SEPARATOR.date("m") . DIRECTORY_SEPARATOR . $GLOBALS['wb'] . DIRECTORY_SEPARATOR, 0777);
	} else {
		$GLOBALS['logger']->write('Folder alreay exists, workbatch already exist:' . $docServer . date("Y") . DIRECTORY_SEPARATOR.date("m") . DIRECTORY_SEPARATOR . $GLOBALS['wb'] . DIRECTORY_SEPARATOR, 'ERROR', 18);
		exit(18);
	}
	return $docServer . date("Y") . DIRECTORY_SEPARATOR . date("m") . DIRECTORY_SEPARATOR . $GLOBALS['wb'] . DIRECTORY_SEPARATOR;
}

/**
* Calculates the next file name in the docserver
* @return array Contains 2 items : subdirectory path and new filename
*/
function getNextFileNameInDocserver($pathOnDocserver) {
	//Scans the docserver path
	$fileTab = scandir($pathOnDocserver);
	//Removes . and .. lines
	array_shift($fileTab);
	array_shift($fileTab);
	$nbFiles = count($fileTab);
	//Docserver is empty
	if ($nbFiles == 0 ) {
		//Creates the directory
		if (!mkdir($pathOnDocserver."0001", 0000700)) {
			$GLOBALS['logger']->write('Pb to create directory on the docserver:' . $pathOnDocserver, 'ERROR', 21);
			exit(21);
		} else {
			$destinationDir = $pathOnDocserver . "0001" . DIRECTORY_SEPARATOR;
			$fileDestinationName = "0001";
			return array("destinationDir" => $destinationDir, "fileDestinationName" => $fileDestinationName);
		}
	} else {
		//Gets next usable subdirectory in the docserver
		$destinationDir = $pathOnDocserver . str_pad(count($fileTab), 4, "0", STR_PAD_LEFT) . DIRECTORY_SEPARATOR;
		$fileTab2 = scandir($pathOnDocserver . strval(str_pad(count($fileTab), 4, "0", STR_PAD_LEFT)));
		//Removes . and .. lines
		array_shift($fileTab2);
		array_shift($fileTab2);
		$nbFiles2 = count($fileTab2);
		//If number of files => 1000 then creates a new subdirectory
		if($nbFiles2 >= 1000 ) {
			$newDir = ($nbFiles) + 1;
			if (!mkdir($pathOnDocserver.str_pad($newDir, 4, "0", STR_PAD_LEFT), 0000700)) {
				$GLOBALS['logger']->write('Pb to create directory on the docserver:' . $pathOnDocserver.str_pad($newDir, 4, "0", STR_PAD_LEFT), 'ERROR', 21);
				exit(21);
			} else {
				$destinationDir = $pathOnDocserver.str_pad($newDir, 4, "0", STR_PAD_LEFT) . DIRECTORY_SEPARATOR;
				$fileDestinationName = "0001";
				return array("destinationDir" => $destinationDir, "fileDestinationName" => $fileDestinationName);
			}
		} else {
			//Docserver contains less than 1000 files
			$newFileName = $nbFiles2 + 1;
			$greater = $newFileName;
			for($n=0;$n<count($fileTab2);$n++) {
				$currentFileName = array();
				$currentFileName = explode(".", $fileTab2[$n]);
				if((int)$greater <= (int)$currentFileName[0]) {
					if((int)$greater == (int)$currentFileName[0]) {
						$greater ++;
					} else {
						//$greater < current
						$greater = (int)$currentFileName[0] + 1;
					}
				}
			}
			$fileDestinationName = str_pad($greater, 4, "0", STR_PAD_LEFT);
			return array("destinationDir" => $destinationDir, "fileDestinationName" => $fileDestinationName);
		}
	}
}

function copyOnDocserver($sourceFilePath, $infoFileNameInTargetDocserver) {
	$destinationDir = $infoFileNameInTargetDocserver['destinationDir'];
	$fileDestinationName = $infoFileNameInTargetDocserver['fileDestinationName'];
	$sourceFilePath = str_replace("\\\\", "\\", $sourceFilePath);
	if(file_exists($destinationDir.$fileDestinationName)) {
		$storeInfos = array('error'=>_FILE_ALREADY_EXISTS);
		return $storeInfos;
	}
	$cp = copy($sourceFilePath, $destinationDir.$fileDestinationName);
	if($cp == false) {
		$storeInfos = array('error'=>_DOCSERVER_COPY_ERROR);
		return $storeInfos;
	}
	controlFingerprint($sourceFilePath, $destinationDir.$fileDestinationName);
	/*$ofile = fopen($destinationDir.$fileDestinationName, "r");
	if (isCompleteFile($ofile)) {
		fclose($ofile);
	} else {
		$storeInfos = array('error'=>_COPY_OF_DOC_NOT_COMPLETE);
		return $storeInfos;
	}*/
	$destinationDir = str_replace($GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template'], "", $destinationDir);
	$destinationDir = str_replace(DIRECTORY_SEPARATOR, '#', $destinationDir);
	$storeInfos = array("destinationDir" => $destinationDir, "fileDestinationName" => $fileDestinationName, "fileSize" => filesize($sourceFilePath));
	washTmp();
	return $storeInfos;
}

/**
* Return true when the file is completed
* @param  $file
* @param  $delay
* @param  $pointer position in the file
*/ 
function isCompleteFile($file, $delay=500, $pointer=0) {
	if ($file == null) {
		return false;
	}
	fseek($file, $pointer);
	$currentLine = fgets($file);
	while (!feof($file)) {
		$currentLine = fgets($file);
	}
	$currentPos = ftell($file);
	//Wait $delay ms
	usleep($delay * 1000);
	if ($currentPos == $pointer) {
		return true;
	} else {
		return isCompleteFile($file, $delay, $currentPos);
	}
}

/**
 * del tmp files
 * @return  boolean
 */
function washTmp() {
	if (is_dir($GLOBALS['TmpDirectory'])) {
		$objects = scandir($GLOBALS['TmpDirectory']);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($GLOBALS['TmpDirectory'] . DIRECTORY_SEPARATOR.$object) == "dir") washSubDir($GLOBALS['TmpDirectory'] . DIRECTORY_SEPARATOR.$object); else unlink($GLOBALS['TmpDirectory'] . DIRECTORY_SEPARATOR . $object);
			}
		}
		reset($objects);
	}
}

/**
 * del tmp files
 * @param   $tmpPath infos of the doc to store, contains :
 * @return  boolean
 */
function washSubDir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir . DIRECTORY_SEPARATOR . $object) == "dir") washSubDir($dir . DIRECTORY_SEPARATOR . $object); else unlink($dir . DIRECTORY_SEPARATOR . $object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

/**
* Return the file's extention of a file
* @param  $sFullPath string path of the file
*/
function extractFileExt($sFullPath) {
	$sName = $sFullPath;
	if(strpos($sName, ".")==0) {
		$extractFileExt = "";
	} else {
		$extractFileExt = explode(".", $sName);
	}
	return $extractFileExt[count($extractFileExt) - 1];
}

/**
* Browse each file and folder in the folder and return true if the folder is not empty
* @param  $folder path string of the folder
*/
function isDirNotEmpty($folder) {
	$foundDoc = false;
	$classScan = dir($folder);
	while (($fileScan = $classScan->read()) != false) {
		if($fileScan == '.' || $fileScan == '..' || $fileScan == '.svn') {
	 		continue;
		} else {
			$foundDoc = true;break;
		}
	}
	return $foundDoc;
}

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
	//washTmp();
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
	washTmp();
}

function controlFingerprint($pathInit, $pathTarget) {
	if (md5_file($pathInit) <> md5_file($pathTarget)) {
		$GLOBALS['logger']->write('Pb during transfert of file (fingerprint control) between ' . $pathInit . ' and ' . $pathTarget, 'ERROR', 22);
		exit(22);
	}
}

?>
