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
* @brief Contains the docservers_controler Object (herits of the BaseObject class)
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
    require_once ("core/class/class_resource.php");
    require_once ("core/class/class_history.php");
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
    public function save($docserver, $mode = "") {
        //var_dump($docserver);
        $control = array();
        if(!isset($docserver) || empty($docserver)) {
            $control = array("status" => "ko", "value" => "", "error" => _DOCSERVER_EMPTY);
            return $control;
        }
        $docserver = self::isADocserver($docserver);
        self::set_foolish_ids(array('docserver_id', 'docserver_type_id', 'coll_id', 'docserver_location_id'));
        self::set_specific_id('docserver_id');
        if($mode == "up") {
            $control = self::control($docserver, "up");
            if($control['status'] == "ok") {
                //Update existing docserver
                if(self::update($docserver)) {
                	self::createPackageInformation($docserver);
                    $control = array("status" => "ok", "value" => $docserver->docserver_id);
                    //history
                    if($_SESSION['history']['docserversadd'] == "true") {

                        $history = new history();
                        $history->add(_DOCSERVERS_TABLE_NAME, $docserver->docserver_id, "UP", _DOCSERVER_UPDATED." : ".$docserver->docserver_id, $_SESSION['config']['databasetype']);
                    }
                } else {
                    $control = array("status" => "ko", "value" => "", "error" => _PB_WITH_DOCSERVER);
                }
                return $control;
            }
        } else {
            $control = self::control($docserver, "add");
            if($control['status'] == "ok") {
                //Insert new docserver
                if(self::insert($docserver)) {
                	self::createPackageInformation($docserver);
                    $control = array("status" => "ok", "value" => $docserver->docserver_id);
                    //history
                    if($_SESSION['history']['docserversadd'] == "true") {
                        $history = new history();
                        $history->add(_DOCSERVERS_TABLE_NAME, $docserver->docserver_id, "ADD", _DOCSERVER_ADDED." : ".$docserver->docserver_id, $_SESSION['config']['databasetype']);
                    }
                } else {
                    $control = array("status" => "ko", "value" => "", "error" => _PB_WITH_DOCSERVER);
                }
            }
        }
        return $control;
    }

    /**
    * control the docserver object before action
    *
    * @param  $docserver docserver object
    * @return array ok if the object is well formated, ko otherwise
    */
    private function control($docserver, $mode) {
        $f = new functions();
        $error = "";
        if($mode == "add") {
            // Update, so values exist
            $docserver->docserver_id = $f->protect_string_db($f->wash($docserver->docserver_id, "nick", _THE_DOCSERVER_ID." ", "yes", 0, 32));
        }
        $docserver->docserver_type_id = $f->protect_string_db($f->wash($docserver->docserver_type_id, "no", _DOCSERVER_TYPES." ", 'yes', 0, 32));
        $docserver->device_label = $f->protect_string_db($f->wash($docserver->device_label, "no", _DEVICE_LABEL." ", 'yes', 0, 255));
        if($docserver->is_readonly == "") {
            $docserver->is_readonly = "false";
        }
        $docserver->is_readonly = $f->protect_string_db($f->wash($docserver->is_readonly, "no", _IS_READONLY." ", 'yes', 0, 5));
        if($docserver->is_readonly == "false") {
            $docserver->is_readonly = false;
        } else {
            $docserver->is_readonly = true;
        }
        if(isset($docserver->size_limit_number) && !empty($docserver->size_limit_number)) {
            $docserver->size_limit_number = $f->protect_string_db($f->wash($docserver->size_limit_number, "no", _SIZE_LIMIT." ", 'yes', 0, 255));
            if(docservers_controler::sizeLimitControl($docserver)) {
                $error .= _SIZE_LIMIT_UNAPPROACHABLE."#";
            }
            if(docservers_controler::actualSizeNumberControl($docserver)) {
                $error .= _SIZE_LIMIT_LESS_THAN_ACTUAL_SIZE."#";
            }
        }
		$docserver->path_template = $f->protect_string_db($f->wash($docserver->path_template, "no", _PATH_TEMPLATE." ", 'yes', 0, 255));
		if(!is_dir($docserver->path_template)) {
			$error .= _PATH_OF_DOCSERVER_UNAPPROACHABLE."#";
        } else {
			// $Fnm = $docserver->path_template."test_docserver.txt";
			if(!is_writable($docserver->path_template) || !is_readable($docserver->path_template)) {
				$error .= _THE_DOCSERVER_DOES_NOT_HAVE_THE_ADEQUATE_RIGHTS;
			}
		}
        $docserver->coll_id = $f->protect_string_db($f->wash($docserver->coll_id, "no", _COLLECTION." ", 'yes', 0, 32));
        $docserver->priority_number = $f->protect_string_db($f->wash($docserver->priority_number, "num", _PRIORITY." ", 'yes', 0, 6));
        $docserver->docserver_location_id = $f->protect_string_db($f->wash($docserver->docserver_location_id, "no", _DOCSERVER_LOCATIONS." ", 'yes', 0, 32));
        $docserver->adr_priority_number = $f->protect_string_db($f->wash($docserver->adr_priority_number, "num", _ADR_PRIORITY." ", 'yes', 0, 6));
        if($mode == "add" && docservers_controler::docserversExists($docserver->docserver_id)) {
            $error .= $docserver->docserver_id." "._ALREADY_EXISTS."#";
        }
        if(!docservers_controler::adrPriorityNumberControl($docserver)) {
            $error .= _PRIORITY." ".$docserver->adr_priority_number." "._ALREADY_EXISTS_FOR_THIS_TYPE_OF_DOCSERVER."#";
        }
        if(!docservers_controler::priorityNumberControl($docserver)) {
            $error .= _ADR_PRIORITY.$docserver->priority_number."  "._ALREADY_EXISTS_FOR_THIS_TYPE_OF_DOCSERVER."#";
        }
        $error .= $_SESSION['error'];
        //TODO:rewrite wash to return errors without html
        $error = str_replace("<br />", "#", $error);
        $return = array();
        if(!empty($error)) {
                $return = array("status" => "ko", "value" => $docserver->docserver_id, "error" => $error);
        } else {
            $return = array("status" => "ok", "value" => $docserver->docserver_id);
        }
        return $return;
    }

	/**
    * method to create package information file on the root of the docserver
    * 
    * @param  $docserver docserver object
    */
	private function createPackageInformation($docserver) {
		if(is_writable($docserver->path_template) && is_readable($docserver->path_template)) {
			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."docserver_types_controler.php");
			$docserverTypeControler = new docserver_types_controler();
			$docserverTypeObject = $docserverTypeControler->get($docserver->docserver_type_id);
			$Fnm = $docserver->path_template.DIRECTORY_SEPARATOR."package_information";
			if(file_exists($Fnm)) {
				unlink($Fnm);
			}
			$inF = fopen($Fnm, "a");
			fwrite($inF, _DOCSERVER_TYPE_ID." : ".$docserverTypeObject->docserver_type_id."\r\n");
			fwrite($inF, _DOCSERVER_TYPE_LABEL." : ".$docserverTypeObject->docserver_type_label."\r\n");
			fwrite($inF, _IS_CONTAINER." : ".$docserverTypeObject->is_container."\r\n");
			fwrite($inF, _CONTAINER_MAX_NUMBER." : ".$docserverTypeObject->container_max_number."\r\n");
			fwrite($inF, _IS_COMPRESSED." : ".$docserverTypeObject->is_compressed."\r\n");
			fwrite($inF, _COMPRESS_MODE." : ".$docserverTypeObject->compression_mode."\r\n");
			fwrite($inF, _IS_META." : ".$docserverTypeObject->is_meta."\r\n");
			fwrite($inF, _META_TEMPLATE." : ".$docserverTypeObject->meta_template."\r\n");
			fwrite($inF, _IS_LOGGED." : ".$docserverTypeObject->is_logged."\r\n");
			fwrite($inF, _LOG_TEMPLATE." : ".$docserverTypeObject->log_template."\r\n");
			fwrite($inF, _IS_SIGNED." : ".$docserverTypeObject->is_signed."\r\n");
			fwrite($inF, _FINGERPRINT_MODE." : ".$docserverTypeObject->fingerprint_mode."\r\n");
			fclose($inF); 
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
        //var_dump($docserver_id);
        self::set_foolish_ids(array('docserver_id'));
        self::set_specific_id('docserver_id');
        $docserver = self::advanced_get($docserver_id, _DOCSERVERS_TABLE_NAME);
        //var_dump($docserver);
        if(get_class($docserver) <> "docservers") {
            return null;
        } else {
            //var_dump($docserver);
            return $docserver;
        }
    }

    /**
     * get docservers with given id for a ws.
     * Can return null if no corresponding object.
     * @param $docserver_id of docservers to send
     * @return docservers
     */
    public function getWs($docserver_id) {
        self::set_foolish_ids(array('docserver_id'));
        self::set_specific_id('docserver_id');
        $docserver = self::advanced_get($docserver_id, _DOCSERVERS_TABLE_NAME);
        if(get_class($docserver) <> "docservers") {
            return null;
        } else {
            $docserver = $docserver->getArray();
            return $docserver;
        }
    }

    /**
     * Delete given docserver from database.
     * @param docservers $docservers
     */
    public function delete($docserver) {
        $control = array();
        if(!isset($docserver) || empty($docserver)) {
            $control = array("status" => "ko", "value" => "", "error" => _DOCSERVER_EMPTY);
            return $control;
        }
        $docserver = self::isADocserver($docserver);
        if(!self::docserversExists($docserver->docserver_id)) {
            $control = array("status" => "ko", "value" => "", "error" => _DOCSERVER_NOT_EXISTS);
            return $control;
        }
        /*if(self::adrxLinkExists($docserver->docserver_id)) {
            $control = array("status" => "ko", "value" => "", "error" => _DOCSERVER_ATTACHED_TO_ADR_X);
            return $control;
        }*/
        if(self::resxLinkExists($docserver->docserver_id, $docserver->coll_id)) {
            $control = array("status" => "ko", "value" => "", "error" => _DOCSERVER_ATTACHED_TO_RES_X);
            return $control;
        }
        self::$db=new dbquery();
        self::$db->connect();
        $query="delete from "._DOCSERVERS_TABLE_NAME." where docserver_id ='".functions::protect_string_db($docserver->docserver_id)."'";
        try {
            if($_ENV['DEBUG']) {echo $query.' // ';}
            self::$db->query($query);
        } catch (Exception $e) {
            $control = array("status" => "ko", "value" => "", "error" => _CANNOT_DELETE_DOCSERVER_ID." ".$docserver->docserver_id);
        }
        self::$db->disconnect();
        $control = array("status" => "ok", "value" => $docserver->docserver_id);
        if($_SESSION['history']['docserversdel'] == "true") {
            $history = new history();
            $history->add(_DOCSERVERS_TABLE_NAME, $docserver->docserver_id, "DEL", _DOCSERVER_DELETED." : ".$docserver->docserver_id, $_SESSION['config']['databasetype']);
        }
        return $control;
    }

    /**
    * Disables a given docservers
    *
    * @param  $docserver docservers object
    * @return bool true if the disabling is complete, false otherwise
    */
    public function disable($docserver) {
        $control = array();
        if(!isset($docserver) || empty($docserver)) {
            $control = array("status" => "ko", "value" => "", "error" => _DOCSERVER_EMPTY);
            return $control;
        }
        $docserver = self::isADocserver($docserver);
        self::set_foolish_ids(array('docserver_id'));
        self::set_specific_id('docserver_id');
        if(self::advanced_disable($docserver)) {
            $control = array("status" => "ok", "value" => $docserver->docserver_id);
            if($_SESSION['history']['docserversban'] == "true") {
                $history = new history();
                $history->add(_DOCSERVERS_TABLE_NAME, $docserver->docserver_id, "BAN", _DOCSERVER_DISABLED." : ".$docserver->docserver_id, $_SESSION['config']['databasetype']);
            }
        } else {
            $control = array("status" => "ko", "value" => "", "error" => _PB_WITH_DOCSERVER);
        }
        return $control;
    }

    /**
    * Enables a given docserver
    *
    * @param  $docserver docservers object
    * @return bool true if the enabling is complete, false otherwise
    */
    public function enable($docserver) {
        $control = array();
        if(!isset($docserver) || empty($docserver)) {
            $control = array("status" => "ko", "value" => "", "error" => _DOCSERVER_EMPTY);
            return $control;
        }
        $docserver = self::isADocserver($docserver);
        self::set_foolish_ids(array('docserver_id'));
        self::set_specific_id('docserver_id');
        if(self::advanced_enable($docserver)) {
            $control = array("status" => "ok", "value" => $docserver->docserver_id);
            if($_SESSION['history']['docserversallow'] == "true") {
                $history = new history();
                $history->add(_DOCSERVERS_TABLE_NAME, $docserver->docserver_id, "VAL",_DOCSERVER_ENABLED." : ".$docserver->docserver_id, $_SESSION['config']['databasetype']);
            }
        } else {
            $control = array("status" => "ko", "value" => "", "error" => _PB_WITH_DOCSERVER);
        }
        return $control;
    }

    /**
    * Fill a docserver object with an object if it's not a docserver
    *
    * @param  $object ws docserver object
    * @return object docservers
    */
    private function isADocserver($object) {
        if(get_class($object) <> "docservers") {
            $func = new functions();
            $docserverObject = new docservers();
            $array = array();
            $array = $func->object2array($object);
            foreach(array_keys($array) as $key) {
                $docserverObject->$key = $array[$key];
            }
            return $docserverObject;
        } else {
            return $object;
        }
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
    private function resxLinkExists($docserver_id, $coll_id) {
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
    private function adrxLinkExists($docserver_id) {
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
    private function adrPriorityNumberControl($docserver) {
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
    private function priorityNumberControl($docserver) {
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
    private function sizeLimitControl($docserver) {
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
     * @param   $collId collection resource
     * @param   $fileInfos infos of the doc to store, contains :
     *          tmpDir : path to tmp directory
     *          size : size of the doc
     *          md5 : fingerprint of the doc
     *          format : format of the doc
     *          tmpFileName : file name of the doc in Maarch tmp directory
     * @return  array of docserver data for res_x else return error
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
        $pathOnDocserver = array();
		$pathOnDocserver = self::createPathOnDocServer($docserver->path_template);
        $docinfo = self::getNextFileNameInDocserver($pathOnDocserver['destinationDir']);
        if($docinfo['error'] <> "") {
			 $_SESSION['error'] = _FILE_SEND_ERROR.". "._TRY_AGAIN.". "._MORE_INFOS." : <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>";
		}
		$copyResultArray = copyOnDocserver($sourceFilePath, $docinfo);
		if($copyResultArray['error'] <> "") {
			$storeInfos = array('error'=>$copyResultArray['error']);
            return $storeInfos;
		}
		$destinationDir = $copyResultArray['destinationDir'];
		$fileDestinationName = $copyResultArray['fileDestinationName'];
        $destinationDir = substr($destinationDir, strlen($docserver->path_template)) . DIRECTORY_SEPARATOR;
        $destinationDir = str_replace(DIRECTORY_SEPARATOR, '#', $destinationDir);
        self::setSize($docserver, $newSize);
        $storeInfos = array("path_template"=>$docserver->path_template, "destination_dir"=>$destinationDir, "docserver_id"=>$docserver->docserver_id, "file_destination_name"=>$fileDestinationName);
        return $storeInfos;
    }

	public function copyOnDocserver($sourceFilePath, $infoFileNameInTargetDocserver) {
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
		if($GLOBALS['TmpDirectory'] <> "") {
			self::washTmp($GLOBALS['TmpDirectory'], true);
		}
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
    * Compute the path in the docserver for a batch
    * @param $docServer docservers path
    * @return @return array Contains 2 items : subdirectory path and error
    */
    public function createPathOnDocServer($docServer) {
		if (!is_dir($docServer . date("Y") . DIRECTORY_SEPARATOR)) {
			mkdir($docServer . date("Y") . DIRECTORY_SEPARATOR, 0777);
		}
		if (!is_dir($docServer . date("Y") . DIRECTORY_SEPARATOR.date("m") . DIRECTORY_SEPARATOR)) {
			mkdir($docServer . date("Y") . DIRECTORY_SEPARATOR.date("m") . DIRECTORY_SEPARATOR, 0777);
		}
		if ($GLOBALS['wb'] <> "") {
			$path = $docServer . date("Y") . DIRECTORY_SEPARATOR.date("m") . DIRECTORY_SEPARATOR . $GLOBALS['wb'] . DIRECTORY_SEPARATOR;
			if (!is_dir($path)) {
				mkdir($path, 0777);
			} else {
				return array("destinationDir" => "", "error" => "Folder alreay exists, workbatch already exist:" . $path);
			}
		} else {
			$path = $docServer . date("Y") . DIRECTORY_SEPARATOR.date("m") . DIRECTORY_SEPARATOR;
		}
		return array("destinationDir" => $path, "error" => "");
	}
    
    /**
    * Calculates the next file name in the docserver
    * @param $pathOnDocserver docservers path
    * @return array Contains 3 items : subdirectory path and new filename and error
    */
    public function getNextFileNameInDocserver($pathOnDocserver) {
        //Scans the docserver path
        $fileTab = scandir($pathOnDocserver);
        //Removes . and .. lines
        array_shift($fileTab);
        array_shift($fileTab);
        if(file_exists($pathOnDocserver . DIRECTORY_SEPARATOR . "package_information")) {
			unset($fileTab[array_search("package_information", $fileTab)]);
		}
        $nbFiles = count($fileTab);
        //Docserver is empty
        if ($nbFiles == 0 ) {
            //Creates the directory
            if (!mkdir($pathOnDocserver."0001",0000700)) {
				return array("destinationDir" => "", "fileDestinationName" => "", "error" => "Pb to create directory on the docserver:" . $pathOnDocserver);
            } else {
                $destinationDir = $pathOnDocserver . "0001" . DIRECTORY_SEPARATOR;
                $fileDestinationName = "0001";
                return array("destinationDir" => $destinationDir, "fileDestinationName" => $fileDestinationName, "error" => "");
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
                    return array("destinationDir" => "", "fileDestinationName" => "", "error" => "Pb to create directory on the docserver:" . $pathOnDocserver.str_pad($newDir, 4, "0", STR_PAD_LEFT));
                } else {
                    $destinationDir = $pathOnDocserver.str_pad($newDir, 4, "0", STR_PAD_LEFT) . DIRECTORY_SEPARATOR;
                    $fileDestinationName = "0001";
                    return array("destinationDir" => $destinationDir, "fileDestinationName" => $fileDestinationName, "error" => "");
                }
            } else {
                //Docserver contains less than 1000 files
                $newFileName = $nbFiles2 + 1;
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
                $fileDestinationName = str_pad($greater, 4, "0", STR_PAD_LEFT);
                return array("destinationDir" => $destinationDir, "fileDestinationName" => $fileDestinationName, "error" => "");
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
     * @param   $dir dir to wash
     * @param   $contentOnly boolean true if only the content
     * @return  boolean
     */
    function washTmp($dir, $contentOnly = false) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir.DIRECTORY_SEPARATOR.$object) == "dir") self::washTmp($dir.DIRECTORY_SEPARATOR.$object); else unlink($dir.DIRECTORY_SEPARATOR.$object);
                }
            }
			reset($objects);
			if(!$contentOnly) {
				rmdir($dir);
			}
        }
    }

    /**
     * Extract a file from an archive
     * @param   $fileInfos infos of the doc to store, contains :
     *          tmpDir : path to tmp directory
     *          path_to_file : path to the file in the docserver
     *          filename : name of the file
     *          offset_doc : offset of the doc in the container
     * @return  array with path of the extracted doc
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
        if($cp == false) {
            $result = array("status" => "ko", "path" => "", "mime_type" => "", "format" => "", "tmpArchive" => "", "fingerprint" => "", "error" => _TMP_COPY_ERROR);
            return $result;
        } else {
            $_exec_error = "";
            $tmpArchive = uniqid(rand());
            if(mkdir($tmp.$tmpArchive)) {
                if(DIRECTORY_SEPARATOR == "/") {
                    $command = "7z e -y -o".escapeshellarg($tmp.$tmpArchive)." ".escapeshellarg($fileNameOnTmp);
                } else {
                    $command = "\"".str_replace("\\", "\\\\", $_SESSION['docserversFeatures']['DOCSERVERS']['PATHTOCOMPRESSTOOL'])."\" e -y -o".escapeshellarg($tmp.$tmpArchive)." ".escapeshellarg($fileNameOnTmp);
                }
                $tmpCmd = "";
                exec($command, $tmpCmd, $_exec_error);
                if($_exec_error > 0) {
                    $result = array("status" => "ko", "path" => "", "mime_type" => "", "format" => "", "tmpArchive" => "", "fingerprint" => "", "error"=>_PB_WITH_EXTRACTION_OF_CONTAINER."#".$_exec_error);
                    return $result;
                }
            } else {
                $result = array("status" => "ko", "path" => "", "mime_type" => "", "format" => "", "tmpArchive" => "", "fingerprint" => "", "error"=>_PB_WITH_EXTRACTION_OF_CONTAINER."#".$tmp.$tmpArchive);
                return $result;
            }
            $format = substr($fileInfos['offset_doc'], strrpos($fileInfos['offset_doc'], '.') + 1);
            if(!file_exists($tmp.$tmpArchive.DIRECTORY_SEPARATOR.$fileInfos['offset_doc'])) {
                $classScan = dir($tmp.$tmpArchive);
                while(($fileScan=$classScan->read()) != false) {
                    if($fileScan=='.' || $fileScan=='..') {
                        continue;
                    } else {
                        $_exec_error_bis = "";
                        $tmpArchiveBis = uniqid(rand());
                        if(mkdir($tmp.$tmpArchive.DIRECTORY_SEPARATOR.$tmpArchiveBis)) {
                            if(DIRECTORY_SEPARATOR == "/") {
                                $commandBis = "7z e -y -o".escapeshellarg($tmp.$tmpArchive.DIRECTORY_SEPARATOR.$tmpArchiveBis)." ".escapeshellarg($tmp.$tmpArchive.DIRECTORY_SEPARATOR.$fileScan);
                            } else {
                                $commandBis = "\"".str_replace("\\", "\\\\", $_SESSION['docserversFeatures']['DOCSERVERS']['PATHTOCOMPRESSTOOL'])."\" e -y -o".escapeshellarg($tmp.$tmpArchive.DIRECTORY_SEPARATOR.$tmpArchiveBis)." ".escapeshellarg($tmp.$tmpArchive.DIRECTORY_SEPARATOR.$fileScan);
                            }
                            $tmpCmd = "";
                            exec($commandBis, $tmpCmd, $_exec_error_bis);
                            if($_exec_error_bis > 0) {
                                $result = array("status" => "ko", "path" => "", "mime_type" => "", "format" => "", "tmpArchive" => "", "fingerprint" => "", "error"=>_PB_WITH_EXTRACTION_OF_CONTAINER."#".$_exec_error_bis);
                                return $result;
                            }
                        } else {
                            $result = array("status" => "ko", "path" => "", "mime_type" => "", "format" => "", "tmpArchive" => "", "fingerprint" => "", "error"=>_PB_WITH_EXTRACTION_OF_CONTAINER."#".$tmp.$tmpArchive.DIRECTORY_SEPARATOR.$tmpArchiveBis);
                            return $result;
                        }
                        $result = array("status" => "ok", "path"=>$tmp.$tmpArchive.DIRECTORY_SEPARATOR.$tmpArchiveBis.DIRECTORY_SEPARATOR.$fileInfos['offset_doc'], "mime_type"=>self::getMimeType($tmp.$tmpArchive.DIRECTORY_SEPARATOR.$tmpArchiveBis.DIRECTORY_SEPARATOR.$fileInfos['offset_doc']), "format"=>$format, "fingerprint" => md5_file($tmp.$tmpArchive.DIRECTORY_SEPARATOR.$tmpArchiveBis.DIRECTORY_SEPARATOR.$fileInfos['offset_doc']), "tmpArchive"=>$tmp.$tmpArchive, "error"=> "");
                        unlink($tmp.$tmpArchive.DIRECTORY_SEPARATOR.$fileScan);
                        break;
                    }
                }
            } else {
                $result = array("status" => "ok", "path"=>$tmp.$tmpArchive.DIRECTORY_SEPARATOR.$fileInfos['offset_doc'], "mime_type"=>self::getMimeType($tmp.$tmpArchive.DIRECTORY_SEPARATOR.$fileInfos['offset_doc']), "format"=>$format, "tmpArchive"=>$tmp.$tmpArchive, "fingerprint" => md5_file($tmp.$tmpArchive.DIRECTORY_SEPARATOR.$fileInfos['offset_doc']), "error"=> "");
            }
            unlink($fileNameOnTmp);
            return $result;
        }
    }

    public function retrieveDocserverNetLinkOfResource($gedId, $tableName) {
        $adr = array();
        $resource = new resource();
        $whereClause = " and 1=1";
        $adr = $resource->getResourceAdr($tableName, $gedId, $whereClause);
        if($adr['status'] == "ko") {
            $result = array("status" => "ko", "value" => "", "error" => _RESOURCE_NOT_EXISTS);
        } else {
            $docserver = $adr['docserver_id'];
            //retrieve infos of the docserver
            $docserverObject = self::get($docserver);
            //retrieve infos of the docserver type
            require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."docserver_locations_controler.php");
            $docserverLocationControler = new docserver_locations_controler();
            $docserverLocationObject = $docserverLocationControler->get($docserverObject->docserver_location_id);
            $result = array("status" => "ok", "value" => $docserverLocationObject->net_link, "error" => "");
        }
        return $result;
    }

    public function viewResource($gedId, $tableName) {
        $coreTools = new core_tools();
        $whereClause = "";
        if($_SESSION['origin'] <> "basket" && $_SESSION['origin'] <> "workflow") {
            if(isset($_SESSION['user']['security'][$_SESSION['collection_id_choice']])) {
                $whereClause = " and( ".$_SESSION['user']['security'][$_SESSION['collection_id_choice']]['DOC']['where']." ) ";
            } else {
                $whereClause = " and 1=1";
            }
        } else {
            $whereClause = " and 1=1";
        }
        $adr = array();
        $resource = new resource();
        $adr = $resource->getResourceAdr($tableName, $gedId, $whereClause);
        //return $adr;exit;
        if($adr['status'] == "ko") {
            $result = array("status" => "ko", "mime_type" => "", "ext" => "", "file_content" => "", "tmp_path" => "", "error" => _NO_RIGHT_ON_RESOURCE_OR_RESOURCE_NOT_EXISTS);
        } else {
            $docserver = $adr['docserver_id'];
            $path = $adr['path'];
            $filename = $adr['filename'];
            $format = $adr['format'];
            $md5 = $adr['fingerprint'];
            $fingerprint_from_db = $adr['fingerprint'];
            $offset_doc = $adr['offset_doc'];
            //retrieve infos of the docserver
            $docserverObject = self::get($docserver);
            $docserver = $docserverObject->path_template;
            $file = $docserver.$path.$filename;
            $file = str_replace("#", DIRECTORY_SEPARATOR, $file);
            if(!file_exists($file)) {
                $result = array("status" => "ko", "mime_type" => "", "ext" => "", "file_content" => "", "tmp_path" => "", "error" => _FILE_NOT_EXISTS_ON_THE_SERVER." : ".$file);
            } else {
                $fingerprint_from_docserver = @md5_file($file);
                //echo md5_file($file)."<br>";
                //echo filesize($file)."<br>";
                $adr['path_to_file'] = $file;
                //retrieve infos of the docserver type
                require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."docserver_types_controler.php");
                $docserverTypeControler = new docserver_types_controler();
                $docserverTypeObject = $docserverTypeControler->get($docserverObject->docserver_type_id);
                if($docserverTypeObject->is_container && $offset_doc == "") {
                    $result = array("status" => "ko", "mime_type" => "", "ext" => "", "file_content" => "", "tmp_path" => "", "error" => _PB_WITH_OFFSET_OF_THE_DOC_IN_THE_CONTAINER);
                }
                //manage compressed resource
                if($docserverTypeObject->is_compressed) {
                    $extract = array();
                    $extract = self::extractArchive($adr);
                    if($extract['status'] == "ko") {
                        $result = array("status" => "ko", "mime_type" => "", "ext" => "", "file_content" => "", "tmp_path" => "", "error" => $extract['error']);
                    } else {
                        $file = $extract['path'];
                        $mimeType = $extract['mime_type'];
                        $format = $extract['format'];
                        //to control fingerprint of the offset 
                        $fingerprint_from_docserver = $extract['fingerprint'];
                    }
                }
                //var_dump($extract);exit;
                //manage view of the file
                $use_tiny_mce = false;
                if(strtolower($format) == 'maarch' && $coreTools->is_module_loaded('templates')) {
                    $mode = "content";
                    $type_state = true;
                    $use_tiny_mce = true;
                    $mimeType = "application/maarch";
                } else {
                    require_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR."class_indexing_searching_app.php");
                    $is = new indexing_searching_app();
                    $type_state = $is->is_filetype_allowed($format);
                }
                //if fingerprint from db = 0 we do not control fingerprint
                if($fingerprint_from_db == 0 || ($fingerprint_from_db == $fingerprint_from_docserver)) {
                    if($type_state <> false) {
                        if($_SESSION['history']['resview'] == "true") {
                            require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
                            $users = new history();
                            $users->add($tableName, $gedId, "VIEW", _VIEW_DOC_NUM."".$gedId, $_SESSION['config']['databasetype'], 'indexing_searching');
                        }
                        //count number of viewed in listinstance for the user
                        if($coreTools->is_module_loaded('entities')) {
                            require_once("modules".DIRECTORY_SEPARATOR."entities".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_manage_entities.php");
                            $ent = new entity();
                            $ent->increaseListinstanceViewed($gedId);
                        }
                        if(file_exists($file)) {
                            $content = file_get_contents($file, FILE_BINARY);
                            $encodedContent = base64_encode($content);
                            $result = array("status" => "ok", "mime_type" => $mimeType, "ext" => $format, "file_content" => $encodedContent, "tmp_path" => $_SESSION['config']['tmppath'], "error" => "");
                        } else {
                            $result = array("status" => "ko", "mime_type" => "", "ext" => "", "file_content" => "", "tmp_path" => "", "error" => "file not exists");
                        }
                    } else {
                        $result = array("status" => "ko", "mime_type" => "", "ext" => "", "file_content" => "", "tmp_path" => "", "error" => _FILE_TYPE.' '._UNKNOWN);
                    }
                } else {
                    $result = array("status" => "ko", "mime_type" => "", "ext" => "", "file_content" => "", "tmp_path" => "", "error" => _PB_WITH_FINGERPRINT_OF_DOCUMENT);
                }
                if(file_exists($extract['tmpArchive'])) {
                    self::washTmp($extract['tmpArchive']);
                }
            }

        }
        return $result;
    }
}

?>
