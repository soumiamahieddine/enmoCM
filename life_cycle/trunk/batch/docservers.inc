<?php 

function createPathOnDocServer($docServer) {
	if (!is_dir($docServer . date("Y") . DIRECTORY_SEPARATOR)) {
		mkdir($docServer . date("Y") . DIRECTORY_SEPARATOR, 0777);
	}
	if (!is_dir($docServer . date("Y") . DIRECTORY_SEPARATOR.date("m") . DIRECTORY_SEPARATOR)) {
		mkdir($docServer . date("Y") . DIRECTORY_SEPARATOR.date("m") . DIRECTORY_SEPARATOR, 0777);
	}
	if (!is_dir($docServer . date("Y") . DIRECTORY_SEPARATOR.date("m") . DIRECTORY_SEPARATOR . str_pad($GLOBALS['wb'], 4, "0", STR_PAD_LEFT) . DIRECTORY_SEPARATOR)) {
		mkdir($docServer . date("Y") . DIRECTORY_SEPARATOR.date("m") . DIRECTORY_SEPARATOR . str_pad($GLOBALS['wb'], 4, "0", STR_PAD_LEFT) . DIRECTORY_SEPARATOR, 0777);
	} else {
		//ERROR !!!
		//echo "\Folder Already exist... Software stopped!";
		//manageError("Workbatch already exist! Unable to send file in the Docserver. End of application.", 11);
	}
	return $docServer . date("Y") . DIRECTORY_SEPARATOR . date("m") . DIRECTORY_SEPARATOR . str_pad($GLOBALS['wb'], 4, "0", STR_PAD_LEFT) . DIRECTORY_SEPARATOR;
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
			//$_SESSION['error'] = _FILE_SEND_ERROR.". "._TRY_AGAIN.". "._MORE_INFOS." : <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>";
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
				//$_SESSION['error'] = _FILE_SEND_ERROR.". "._TRY_AGAIN.". "._MORE_INFOS." : <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>";
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

?>
