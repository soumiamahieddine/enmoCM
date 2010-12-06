<?php 

// DON'T FORGET TO ORGANIZE DOCSERVER LIKE AUTOIMPORT

/**
* Calculates the next file name in the docserver
* @return array Contains 2 items : subdirectory path and new filename
*/
function getNextFileNameInDocserver() {
	//Scans the docserver path
	$fileTab = scandir($GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template']);
	//Removes . and .. lines
	array_shift($fileTab);
	array_shift($fileTab);
	unset($fileTab[array_search("package_information", $fileTab)]);
	$nbFiles = count($fileTab);
	//Docserver is empty
	if ($nbFiles == 0 ) {
		//Creates the directory
		if (!mkdir($GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template']."1",0000700)) {
			//$_SESSION['error'] = _FILE_SEND_ERROR.". "._TRY_AGAIN.". "._MORE_INFOS." : <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>";
		} else {
			$destinationDir = $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template']."1".DIRECTORY_SEPARATOR;
			$fileDestinationName = "1";
			return array("destinationDir" => $destinationDir, "fileDestinationName" => $fileDestinationName);
		}
	} else {
		//Gets next usable subdirectory in the docserver
		$destinationDir = $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template'].count($fileTab).DIRECTORY_SEPARATOR;
		$fileTab2 = scandir($GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template'].strval(count($fileTab)));
		//Removes . and .. lines
		array_shift($fileTab2);
		array_shift($fileTab2);
		$nbFiles2 = count($fileTab2);
		//If number of files => 2000 then creates a new subdirectory
		if($nbFiles2 >= 2000 ) {
			$newDir = ($nbFiles) + 1;
			if (!mkdir($GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template'].$newDir,0000700)) {
				//$_SESSION['error'] = _FILE_SEND_ERROR.". "._TRY_AGAIN.". "._MORE_INFOS." : <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>";
			} else {
				$destinationDir = $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template'].$newDir.DIRECTORY_SEPARATOR;
				$fileDestinationName = "1";
				return array("destinationDir" => $destinationDir, "fileDestinationName" => $fileDestinationName);
			}
		} else {
			//Docserver contains less than 2000 files
			$newFileName = ($nbFiles2) + 1;
			$greater = $newFileName;
			for($n=0;$n<count($fileTab2);$n++) {
				$currentFileName = array();
				$currentFileName = explode(".",$fileTab2[$n]);
				if((int)$greater  <= (int)$currentFileName[0]) {
					if((int)$greater  == (int)$currentFileName[0]) {
						$greater ++;
					} else {
						//$greater < current
						$greater = (int)$currentFileName[0] +1;
					}
				}
			}
			$fileDestinationName = $greater ;
			return array("destinationDir" => $destinationDir, "fileDestinationName" => $fileDestinationName);
		}
	}
}

function copyOnDocserver($sourceFilePath, $infoFileNameInTargetDocserver) {
	$destinationDir = $infoFileNameInTargetDocserver['destinationDir'];
	$fileDestinationName = $infoFileNameInTargetDocserver['fileDestinationName'];
	$sourceFilePath = str_replace("\\\\","\\",$sourceFilePath);
	if(file_exists($destinationDir.$fileDestinationName)) {
		$storeInfos = array('error'=>_FILE_ALREADY_EXISTS);
		return $storeInfos;
	}
	$cp = copy($sourceFilePath, $destinationDir.$fileDestinationName);
	if($cp == false) {
		$storeInfos = array('error'=>_DOCSERVER_COPY_ERROR);
		return $storeInfos;
	}
	/*$ofile = fopen($destinationDir.$fileDestinationName, "r");
	if (isCompleteFile($ofile)) {
		fclose($ofile);
	} else {
		$storeInfos = array('error'=>_COPY_OF_DOC_NOT_COMPLETE);
		return $storeInfos;
	}*/
	$destinationDir = substr($destinationDir, strlen($GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template']),4);
	$destinationDir = str_replace(DIRECTORY_SEPARATOR,'#',$destinationDir);
	$storeInfos = array("destinationDir" => $destinationDir, "fileDestinationName" => $fileDestinationName, "fileSize" => filesize($sourceFilePath));
	washTmp($GLOBALS['MaarchDirectory'] . "modules" .DIRECTORY_SEPARATOR . "life_cycle" . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . "tmp");
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
 * @param   $tmpPath infos of the doc to store, contains :
 * @return  boolean
 */
function washTmp($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir.DIRECTORY_SEPARATOR.$object) == "dir") washSubDir($dir.DIRECTORY_SEPARATOR.$object); else unlink($dir.DIRECTORY_SEPARATOR.$object);
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
				if (filetype($dir.DIRECTORY_SEPARATOR.$object) == "dir") washSubDir($dir.DIRECTORY_SEPARATOR.$object); else unlink($dir.DIRECTORY_SEPARATOR.$object);
			}
		}
	reset($objects);
	rmdir($dir);
	}
}

?>
