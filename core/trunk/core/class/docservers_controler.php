<?php

/*
*    Copyright 2008,2009,2010 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  Contains the docservers_controler Object (herits of the BaseObject class)
* 
* 
* @file
* @author Luc KEULEYAN - BULL
* @author Laurent Giovannoni
* @date $date$
* @version $Revision$
* @ingroup core
*/

//To activate de debug mode of the class
$_ENV['DEBUG'] = false;

//Loads the required class
try {
	require_once ("core/class/docservers.php");
	require_once ("core/core_tables.php");
	require_once ("core/class/ObjectControlerAbstract.php");
	require_once ("core/class/ObjectControlerIF.php");
	require_once ("core/class/class_security.php");
} catch (Exception $e) {
	echo $e->getMessage().' // ';
}

define ("_DEBUG", false);
define ("_ADVANCED_DEBUG",false);

/**
 * Class for controling docservers objects from database
 */
class docservers_controler extends ObjectControler implements ObjectControlerIF {
	
	/**
	 * Save given object in database: 
	 * - make an update if object already exists,
	 * - make an insert if new object.
	 * Return updated object.
	 * @param docservers $docservers
	 * @return boolean
	 */
	public function save($docserver) {
		if (!isset ($docserver))
			return false;

		self::set_foolish_ids(array('docserver_id', 'docserver_type_id', 'coll_id', 'docserver_location_id'));
		self::set_specific_id('docserver_id');
		
		if(self::docserversExists($docserver->docserver_id)){
				//Update existing docservers
				return self::update($docserver);
		} else {
			//Insert new docservers
			return self::insert($docserver);
		}
	}

	/**
	* Inserts in the database (docservers table) a docserver object
	*
	* @param  $docserver docserver object
	* @return bool true if the insertion is complete, false otherwise
	*/
	private function insert($docserver) {
		//Giving automatised values
		$docserver->enabled="Y";
		$docserver->creation_date=request::current_datetime();
		//Inserting object
		$result = self::advanced_insert($docserver);
		return $result;
	}
	
	/**
	* Updates in the database (docserver table) a docservers object
	*
	* @param  $docserver docserver object
	* @return bool true if the update is complete, false otherwise
	*/
	private function update($docserver) {
		return self::advanced_update($docserver);
	}
	
	/**
	 * Get docservers with given id.
	 * Can return null if no corresponding object.
	 * @param $id Id of docservers to get
	 * @return docservers 
	 */
	public function get($docserver_id) {
		self::set_foolish_ids(array('docserver_id'));
		self::set_specific_id('docserver_id');
		$docserver = self::advanced_get($docserver_id, _DOCSERVERS_TABLE_NAME);
		if (isset ($docserver_id))
			return $docserver;
		else
			return null;
	}

	/**
	 * Delete given docserver from database.
	 * @param docservers $docservers
	 */
	public function delete($docserver) {
		//Deletion of given docservers
		if(!isset($docserver) || empty($docserver) )
			return false;
		
		if(!self::docserversExists($docserver->docserver_id))
				return false;
			
		if(self::adrxLinkExists($docserver->docserver_id))
			return false;
		
		if(self::resxLinkExists($docserver->docserver_id, $docserver->coll_id))
			return false;
					
		self::$db=new dbquery();
		self::$db->connect();
		$query="delete from "._DOCSERVERS_TABLE_NAME." where docserver_id ='".functions::protect_string_db($docserver->docserver_id)."'";
		try {
			if($_ENV['DEBUG']) {echo $query.' // ';}
			self::$db->query($query);
			$ok = true;
		} catch (Exception $e) {
			echo _CANNOT_DELETE_DOCSERVER_ID." ".$docserver->docserver_id.' // ';
			$ok = false;
		}
		self::$db->disconnect();
		return $ok;
	}

	/**
	* Disables a given docservers
	* 
	* @param  $docserver docservers object 
	* @return bool true if the disabling is complete, false otherwise 
	*/
	public function disable($docserver) {
		self::set_foolish_ids(array('docserver_id'));
		self::set_specific_id('docserver_id');
		return self::advanced_disable($docserver);
	}

	/**
	* Enables a given docserver
	* 
	* @param  $docserver docservers object  
	* @return bool true if the enabling is complete, false otherwise 
	*/
	public function enable($docserver) {
		self::set_foolish_ids(array('docserver_id'));
		self::set_specific_id('docserver_id');
		return self::advanced_enable($docserver);
	}

	/**
	* Test if a given docserver exists
	* 
	* @param  $docserver docservers object  
	* @return bool true if exists, false otherwise 
	*/
	public function docserversExists($docserver_id) {
		if(!isset($docserver_id) || empty($docserver_id))
			return false;
		self::$db=new dbquery();
		self::$db->connect();
		$query = "select docserver_id from "._DOCSERVERS_TABLE_NAME." where docserver_id = '".$docserver_id."'";			
		try{
			if($_ENV['DEBUG']){echo $query.' // ';}
			self::$db->query($query);
		} catch (Exception $e){
			echo _UNKNOWN._DOCSERVER." ".$docserver_id.' // ';
		}
		if(self::$db->nb_result() > 0){
			self::$db->disconnect();
			return true;
		}
		self::$db->disconnect();
		return false;
	}
	
	/**
	*Check if the docserver is linked to a ressource
	*@param docserver_id docservers 
	*@return bool true if it's linked  
	*/
	public function resxLinkExists($docserver_id, $coll_id) {	
		self::$db=new dbquery();
		self::$db->connect();
		$tableName = security::retrieve_table_from_coll($coll_id);
		$query = "select docserver_id from ".$tableName." where docserver_id = '".$docserver_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}
		self::$db->disconnect();
	}

	/**
	*Check if the docserver is linked to a ressource address
	*@param docserver_id docservers 
	*@return bool true if it's linked  
	*/		
	public function adrxLinkExists($docserver_id) {
		self::$db=new dbquery();
		self::$db->connect();
		$query = "select docserver_id from "._ADR_X_TABLE_NAME." where docserver_id = '".$docserver_id."'";
		self::$db->query($query);
		if (self::$db->nb_result()>0) {
			self::$db->disconnect();
			return true;
		}
		self::$db->disconnect();
	}
	
	/**
	* Check if two docservers have the same priorities
	* 
	* @param $docserver docservers object
	* @return bool true if the control is ok
	*/
	public function adrPriorityNumberControl($docserver) {
		if(!isset($docserver) || empty($docserver) || empty($docserver->adr_priority_number))
		return false;
		self::$db=new dbquery();
		self::$db->connect();
		$query = "select adr_priority_number from "._DOCSERVERS_TABLE_NAME." where adr_priority_number = ".$docserver->adr_priority_number.
																			" AND docserver_type_id = '".functions::protect_string_db($docserver->docserver_type_id)."'".
																			" AND docserver_id <> '".functions::protect_string_db($docserver->docserver_id)."'";
		self::$db->query($query);
		if (self::$db->nb_result() > 0) {
			self::$db->disconnect();
			return false;
		}
		self::$db->disconnect();
		return true;
	}
	
	/**
	* Check if two docservers have the same priorities
	* 
	* @param $docserver docservers object
	* @return bool true if the control is ok
	*/
	public function priorityNumberControl($docserver) {
		if(!isset($docserver) || empty($docserver) || empty($docserver->priority_number))
		return false;
		self::$db=new dbquery();
		self::$db->connect();
		$query = "select priority_number from "._DOCSERVERS_TABLE_NAME." where priority_number = ".$docserver->priority_number.
																		" AND docserver_type_id = '".functions::protect_string_db($docserver->docserver_type_id)."'".
																		" AND docserver_id <> '".functions::protect_string_db($docserver->docserver_id)."'";
		self::$db->query($query);
		if (self::$db->nb_result() > 0) {
			self::$db->disconnect();
			return false;
		}
		self::$db->disconnect();
		return true;
	}
	
	/**
	* Check if the docserver actual size is less than the size limit
	* 
	* @param $docserver docservers object
	* @return bool true if the control is ok
	*/
	public function actualSizeNumberControl($docserver) {
		if(!isset($docserver) || empty($docserver))
		return false;
		
		$size_limit_number = floatval($docserver->size_limit_number);
		$size_limit_number = $size_limit_number*1000*1000*1000;
		self::$db=new dbquery();
		self::$db->connect();
		$query = "select actual_size_number from " ._DOCSERVERS_TABLE_NAME." where docserver_id = '".$docserver->docserver_id."'";
		self::$db->query($query);
		$queryResult = self::$db->fetch_object();
		$actual_size_number = floatval($queryResult->actual_size_number);
		self::$db->disconnect();
		if($size_limit_number < $actual_size_number) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Check if the docserver size has not reached the limit
	* 
	* @param $docserver docservers object
	* @return bool true if the control is ok
	*/
	public function sizeLimitControl($docserver) {
		$docserver->size_limit_number = floatval($docserver->size_limit_number);
		$maxsizelimit = floatval($_SESSION['docserversFeatures']['DOCSERVERS']['MAX_SIZE_LIMIT'])*1000*1000*1000;
		if(!isset($docserver) || empty($docserver))
			return false;
		
		if($docserver->size_limit_number < $maxsizelimit) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Get docservers to insert a new doc.
	 * Can return null if no corresponding object.
	 * @param  $coll_id  string Collection identifier
	 * @return docservers 
	 */
	public function getDocserverToInsert($collId) {
		self::$db=new dbquery();
		self::$db->connect();
		$query = "select priority_number, docserver_id from "._DOCSERVERS_TABLE_NAME." where is_readonly = 'N' and enabled = 'Y' and coll_id = '".$collId."' order by priority_number";
		self::$db->query($query);
		$queryResult = self::$db->fetch_object();
		self::$db->disconnect();
		if($queryResult->docserver_id <> "") {
			$docserver = self::get($queryResult->docserver_id);
			if(isset($docserver->docserver_id))	
				return $docserver;
			else
				return null;
		} else {
			return null;
		}
	}
	
	/**
	 * Store a new doc in a docserver.
	 * @param  	$collId collection resource
	 * @param  	$fileInfos infos of the doc to store, contains :
	 * 			tmpDir : path to tmp directory
	 * 			size : size of the doc
	 * 			md5 : fingerprint of the doc
	 * 			format : format of the doc
	 * 			tmpFileName : file name of the doc in Maarch tmp directory
	 * @return 	array of docserver data for res_x else return error
	 */
	public function storeResourceOnDocserver($collId, $fileInfos) {
		$docserver = self::getDocserverToInsert($collId);
		if(empty($docserver)) {
			$storeInfos = array('error'=>_DOCSERVER_ERROR.' : '._NO_AVAILABLE_DOCSERVER.". "._MORE_INFOS.".");
			return $storeInfos;
		}
		$newSize = self::checkSize($docserver, $fileInfos['size']);
		if($newSize == 0) {
			$storeInfos = array('error'=>_DOCSERVER_ERROR.' : '._NOT_ENOUGH_DISK_SPACE.". "._MORE_INFOS.".");
			return $storeInfos;
		}
		if($fileInfos['tmpDir'] == "") {
			$tmp = $_SESSION['config']['tmppath'];
		} else {
			$tmp = $fileInfos['tmpDir'];
		}
		
		$d = dir($tmp);
		$pathTmp = $d->path;
		while($entry = $d->read()) {
			if($entry == $fileInfos['tmpFileName']) {
				$tmpSourceCopy = $pathTmp.$entry;
				$theFile = $entry;
				break;
			}
		}
		$d->close();
		$docinfo = self::filename($docserver);
		$destinationDir = $docinfo['destination_rept'];
		$fileDestinationName = $docinfo['file_destination_name'];
		$file_path = $destinationDir.$fileDestinationName.".".$fileInfos['format'];
		$tmpSourceCopy = str_replace("\\\\","\\",$tmpSourceCopy);
		if(file_exists($destinationDir.$fileDestinationName.".".$fileInfos['format'])) {
			$storeInfos = array('error'=>_FILE_ALREADY_EXISTS.". "._MORE_INFOS." : <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>.");
			return $storeInfos;
		}
		$cp = copy($tmpSourceCopy, $destinationDir.$fileDestinationName.".".$fileInfos['format']);
		$file_name = $entry;
		if($cp == false) {
			$storeInfos = array('error'=>_DOCSERVER_COPY_ERROR);
			return $storeInfos;
		} else {
			$delete = unlink($tmpSourceCopy);
			if($delete == false) {
				$storeInfos = array('error'=>_TMP_FILE_DEL_ERROR);
				return $storeInfos;
			}
		}
		$destinationDir = substr($destinationDir, strlen($docserver->path_template),4);
		$destinationDir = str_replace(DIRECTORY_SEPARATOR,'#',$destinationDir);
		self::setSize($docserver, $newSize);
		$storeInfos = array("path_template"=>$docserver->path_template, "destination_dir"=>$destinationDir, "docserver_id"=>$docserver->docserver_id, "file_destination_name"=>$fileDestinationName);
		return $storeInfos;
	}
	
	/**
	* Checks the size of the docserver plus a new file to see if there is enough disk space
	*
	* @param  $docserver docservers object
	* @param  $filesize integer File size
	* @return integer New docserver size or 0 if not enough disk space available
	*/
 	public function checkSize($docserver, $filesize) {
 		$new_docserver_size = $docserver->actual_size + $filesize;
 		if($docserver->size_limit > 0 && $new_docserver_size >= $docserver->size_limit) {
			return 0;
		} else {
			return $new_docserver_size;
		}
 	}
 	
 	/**
	* Calculates the next file name in the docserver
	* @param $docserver docservers object
	* @return array Contains 2 items : subdirectory path and new filename
	*/
	public function filename($docserver) {
		$path_template = $docserver->path_template;
		//Scans the docserver path
		$file_tab = scandir($path_template);
		//Removes . and .. lines
		array_shift($file_tab);
		array_shift($file_tab);
		$nb_files = count($file_tab);
		//Docserver is empty
		if ($nb_files == 0 ) {
			//Creates the directory
			if (!mkdir($path_template."1",0000700)) {
				//management of errors in the view controler
				//$this->error = _FILE_SEND_ERROR;
				$_SESSION['error'] = _FILE_SEND_ERROR.". "._TRY_AGAIN.". "._MORE_INFOS." : <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>";
			} else {
				$destination_rept = $path_template."1".DIRECTORY_SEPARATOR;
				$file_destination_name = "1";
				return array("destination_rept" => $destination_rept, "file_destination_name" => $file_destination_name);
			}
		} else {
			//Gets next usable subdirectory in the docserver
			$destination_rept = $path_template.count($file_tab).DIRECTORY_SEPARATOR;
			$file_tab2 = scandir($path_template.strval(count($file_tab)));
			//Removes . and .. lines
			array_shift($file_tab2);
			array_shift($file_tab2);
			$nb_files2 = count($file_tab2);
			//If number of files => 2000 then creates a new subdirectory
			if($nb_files2 >= 2000 ) {
				$new_rept = ($nb_files) + 1;
				if (!mkdir($path_template.$new_rept,0000700)) {
					//management of errors in the view controler
					//$docserver->error = _FILE_SEND_ERROR;
					$_SESSION['error'] = _FILE_SEND_ERROR.". "._TRY_AGAIN.". "._MORE_INFOS." : <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>";
				} else {
					$destination_rept = $path_template.$new_rept.DIRECTORY_SEPARATOR;
					$file_destination_name = "1";
					return array("destination_rept" => $destination_rept, "file_destination_name" => $file_destination_name);
				}
			} else {
				//Docserver contains less than 2000 files
				$new_file_name = ($nb_files2) + 1;
				$greater = $new_file_name;
				for($n=0;$n<count($file_tab2);$n++) {
					$current_file_name = array();
					$current_file_name = explode(".",$file_tab2[$n]);
					if((int)$greater  <= (int)$current_file_name[0]) {
						if((int)$greater  == (int)$current_file_name[0]) {
							$greater ++;
						} else {
							//$greater < current
							$greater = (int)$current_file_name[0] +1;
						}
					}
				}
				$file_destination_name = $greater ;
				return array("destination_rept" => $destination_rept, "file_destination_name" => $file_destination_name);
			}
		}
	}
	
	/**
	* Sets the size of the docserver
	* @param $docserver docservers object
	* @param $newSize integer New size of the docserver
	*/
	public function setSize($docserver, $newSize) {
		self::$db=new dbquery();
		self::$db->connect();
		self::$db->query("update "._DOCSERVERS_TABLE_NAME." set actual_size_number=".$newSize." where docserver_id='".$docserver->docserver_id."'");
		self::$db->disconnect();
		return $newSize;
	}
	
	/**
	* get the mime type of a doc
	* @param $filePath path of the file
	* @return string of the mime type
	*/
	public function getMimeType($filePath) {
		require_once 'MIME/Type.php';
		return MIME_Type::autoDetect($filePath);
	}
	
	/**
	 * del tmp files
	 * @param  	$tmpPath infos of the doc to store, contains :
	 * @return 	boolean
	 */
	public function washTmp($tmpPath) {
		$classScan= dir($tmpPath);
		while(($fileScan=$classScan->read())!=false) {
			if($fileScan=='.'||$fileScan=='..') {
		 		continue;
			} elseif(is_dir($tmpPath.DIRECTORY_SEPARATOR.$fileScan)) {
				self::washTmp($tmpPath.DIRECTORY_SEPARATOR.$fileScan);
			} else {
				unlink($tmpPath.DIRECTORY_SEPARATOR.$fileScan);
			}
		}
		rmdir($tmpPath);
	}
	
	/**
	 * Extract a file from an archive
	 * @param  	$fileInfos infos of the doc to store, contains :
	 * 			tmpDir : path to tmp directory
	 * 			path_to_file : path to the file in the docserver
	 * 			filename : name of the file
	 * 			offset_doc : offset of the doc in the container
	 * @return 	array with path of the extracted doc
	 */
	public function extractArchive($fileInfos) {
		//var_dump($fileInfos);
		if($fileInfos['tmpDir'] == "") {
			$tmp = $_SESSION['config']['tmppath'];
		} else {
			$tmp = $fileInfos['tmpDir'];
		}
		//TODO:extract on the maarch tmp dir on server or on the fly in the docserver dir ?
		$fileNameOnTmp = $tmp.rand()."_".md5_file($fileInfos['path_to_file'])."_".$fileInfos['filename'];
		$cp = copy($fileInfos['path_to_file'], $fileNameOnTmp);
		//echo self::getMimeType($fileNameOnTmp);
		if($cp == false) {
			$result = array('error'=>_TMP_COPY_ERROR);
			return $result;
		} else {
			$_exec_error = '';
			$tmpArchive = uniqid(rand());
			if(mkdir($tmp.$tmpArchive)) {
				$command = '7z e -y -o'.escapeshellarg($tmp.$tmpArchive).' '.escapeshellarg($fileNameOnTmp);
				//echo $command."<br>";
				$tmpCmd = "";
				exec($command, $tmpCmd, $_exec_error);
				if($_exec_error > 0) {
					$result = array('error'=>_PB_WITH_EXTRACTION_OF_CONTAINER." : <br>".$_exec_error);
					return $result;
				}
			} else {
				$result = array('error'=>_PB_WITH_EXTRACTION_OF_CONTAINER." : <br>".$tmp.$tmpArchive);
				return $result;
			}
			$format = substr($fileInfos['offset_doc'], strrpos($fileInfos['offset_doc'], '.') + 1);
			$result = array('path'=>$tmp.$tmpArchive.DIRECTORY_SEPARATOR.$fileInfos['offset_doc'], 'mime_type'=>self::getMimeType($tmp.$tmpArchive.DIRECTORY_SEPARATOR.$fileInfos['offset_doc']), 'format'=>$format, 'tmpArchive'=>$tmp.$tmpArchive);
			$classScan = dir($tmp.$tmpArchive);
			/*while(($fileScan=$classScan->read())!=false) {
				if($fileScan=='.'||$fileScan=='..') {
			 		continue;
				}
				unlink($tmp.$tmpArchive.DIRECTORY_SEPARATOR.$fileScan);
			}*/
			unlink($fileNameOnTmp);
			return $result;
			/*require_once "File/Archive.php";
			$toExtract = $fileNameOnTmp.DIRECTORY_SEPARATOR.$fileInfos['offset_doc'];
			$extract = File_Archive::extract(
			    $toExtract,
				$tmp
				//File_Archive::toOutput()
			);
			//var_dump($extract);
			$delete = unlink($fileNameOnTmp);
			if($delete == false) {
				$result = array('error'=>_TMP_FILE_DEL_ERROR);
				return $result;
			}
			if($extract <> null) {
				$result = array('error'=>_PB_WITH_EXTRACTION_OF_CONTAINER." : <br>".$extract->message);
				return $result;
			} else {
				$format = substr($fileInfos['offset_doc'], strrpos($fileInfos['offset_doc'], '.') + 1);
				$result = array('path'=>$tmp.$fileInfos['offset_doc'], 'mime_type'=>self::getMimeType($tmp.$fileInfos['offset_doc']), 'format'=>$format);
				return $result;
			}*/
		}
	}
	
	public function docserverWs($theArg) {
		return $theArg;
	}
}

?>
