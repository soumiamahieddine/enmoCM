<?php
/*
*    Copyright 2008,2009 Maarch
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
* @brief   Contains all the function to manage the resources
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

/**
* @brief   Contains all the function to manage the resources
*
* <ul>
*<li>Standardized methods to insert, update and delete a resource</li>
*</ul>
* @ingroup core
*/
 class resource extends request
 {

    /**
    * Resource identifier
    * Integer
    */
    private $res_id;


    /**
    * Type identifier of the resource
    * String
    */
    private $type_id;

    /**
    * Person who inserts the resource in the application
    * String
    */
    private $typist;

    /**
    * File format of the resource
    * String
    */
    private $format;

    /**
    * Docserver identifier of the resource
    * String
    */
    private $docserver_id;

    /**
    * Path of the resource in the docserver
    * String
    */
    private $path;

    /**
    * Fingerprint of the resource
    * String
    */
    private $fingerprint;

    /**
    * File name of the resource
    * String
    */
    private $filename;

    /**
    * File Size of the resource
    * Integer
    */
    private $filesize;

    /**
    * Offset
    * Integer
    */
    private $offset;

    /**
    * Logical address
    * Integer
    */
    private $log_adr;

    /**
    * Status of the resource
    * String
    */
    private $status;

    /**
    * Error message
    * String
    */
    private $error;

    /**
    * Inserts the Resource Object data into the data base
    *
    * @param  $table_res string Resource table where to insert
    * @param  $path  string Resource path in the docserver
    * @param  $filename string Resource file name
    * @param  $docserver_path  string Docserver path
    * @param  $docserver_id  string Docserver identifier
    * @param  $data  array Data array
    * @param  $databasetype string Type of the db (MYSQL, SQLSERVER, ...)
    */
    public function load_into_db($table_res, $path, $filename, $docserver_path, $docserver_id, $data, $databasetype, $calledByWs=false)
    {
        $filetmp = $docserver_path;
        $tmp = $path;
        $tmp = str_replace('#', DIRECTORY_SEPARATOR, $tmp);
        $filetmp .= $tmp;
        $filetmp .= $filename;
        $db = new Database();
        require_once 'core/class/docservers_controler.php';
        require_once 'core/class/docserver_types_controler.php';
        require_once 'core/docservers_tools.php';
        $docserverControler = new docservers_controler();
        $docserverTypeControler = new docserver_types_controler();
        $docserver = $docserverControler->get($docserver_id);
        $docserverTypeObject = $docserverTypeControler->get($docserver->docserver_type_id);
        $fingerprint = Ds_doFingerprint($filetmp, $docserverTypeObject->fingerprint_mode);
        $filesize = filesize($filetmp);
        array_push($data, array('column' => "fingerprint", 'value' => $fingerprint, 'type' => "string"));
        array_push($data, array('column' => "filesize", 'value' => $filesize, 'type' => "int"));
        array_push($data, array('column' => "path", 'value' => $path, 'type' => "string"));
        array_push($data, array('column' => "filename", 'value' => $filename, 'type' => "string"));
        array_push($data, array('column' => 'creation_date', 'value' => $db->current_datetime(), 'type' => "function"));
        if (!$this->check_basic_fields($data)) {
            $_SESSION['error'] = $this->error;
            functions::xecho($this->error);
            return false;
        } else {
            if (!$this->insert($table_res, $data, $_SESSION['config']['databasetype'])) {
                if (!$calledByWs) {
                    $this->error = _INDEXING_INSERT_ERROR."<br/>".$this->show();
                }
                return false;
            } else {
                $db2 = new Database();
                $stmt = $db2->query(
                    "select res_id from " . $table_res
                        . " where docserver_id = ? and path = ? and filename= ?  order by res_id desc ",
                    array(
                        $docserver_id,
                        $path,
                        $filename
                    )
                );
                $res = $stmt->fetchObject();
                if ($table_res == 'res_letterbox') {
                    require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
                    $hist = new history();
                    $hist->add(
                        $table_res,
                        $res->res_id, 'ADD', 'resadd',
                        _DOC_CREATED, $_SESSION['config']['databasetype'], 'apps');
                }

                return $res->res_id;
            }
        }
    }


    /**
    * Gets the error message of the resource object
    *
    * @return string Error message of the resource object
    */
    public function get_error()
    {
        return $this->error;
    }

    /**
    * Checks the mininum fields required for an insert into the database
    *
    * @param  $data array Array of the fields to insert into the database
    * @return bool True if all the fields are ok, False otherwise
         */
    private function check_basic_fields($data)
    {
        $error = '';
        $db = new Database();
        $find_format = false;
        $find_typist = false;
        $find_creation_date = false;
        $find_docserver_id = false;
        $find_path = false;
        $find_filename = false;
        $find_fingerprint = false;
        $find_filesize = false;
        $find_status = false;

        foreach ($data as $value) {
            if ($value['column'] == 'format') {
                $find_format = true;
                // must be tested in the file_index.php file (module = indexing_searching)
            } elseif ($value['column'] == 'typist') {
                $find_typist = true;
            } elseif ($value['column'] == 'creation_date') {
                $find_creation_date = true;
                if ($value['value'] <> $db->current_datetime()) {
                    $error .= _CREATION_DATE_ERROR.'<br/>';
                }
            } elseif ($value['column'] == 'docserver_id') {
                $find_docserver_id =  true;
                $db = new Database();
                if (!$db->query("select docserver_id from ".$_SESSION['tablename']['docservers']." where docserver_id = ?", array($value['value']))) {
                    $error .= _DOCSERVER_ID_ERROR.'<br/>';
                }
            } elseif ($value['column'] == 'path') {
                $find_path = true;
                if (empty($value['value'])) {
                    $error .= _PATH_ERROR.'<br/>';
                }
            } elseif ($value['column'] == 'filename') {
                $find_filename = true;
                if (!preg_match("/^[\w\-.]+.([a-zA-Z-0-9][a-zA-Z-0-9][a-zA-Z-0-9][a-zA-Z-0-9]?|maarch)$/", $value['value'])) {
                    $error .= _FILENAME_ERROR . ' ' . $value['value'] . '<br/>';
                }
            } elseif ($value['column'] == 'fingerprint') {
                $find_fingerprint  = true;
                if (!preg_match("/^[0-9A-Fa-f]+$/", $value['value'])) {
                    $error .= _FINGERPRINT_ERROR.'<br/>';
                }
            } elseif ($value['column'] == 'filesize') {
                $find_filesize = true;
                if ($value['value'] <= 0) {
                    $error .= _FILESIZE_ERROR.'<br/>';
                }
            } elseif ($value['column'] == 'status') {
                $find_status = true;
            }
        }

        if ($find_format == false) {
            $error .= _MISSING_FORMAT.'<br/>';
        }
        if ($find_typist == false) {
            $error .= _MISSING_TYPIST.'<br/>';
        }
        if ($find_creation_date == false) {
            $error .= _MISSING_CREATION_DATE.'<br/>';
        }
        if ($find_docserver_id == false) {
            $error .= _MISSING_DOCSERVER_ID.'<br/>';
        }
        if ($find_path == false) {
            $error .= _MISSING_PATH.'<br/>';
        }
        if ($find_filename == false) {
            $error .= _MISSING_FILENAME.'<br/>';
        }
        if ($find_fingerprint == false) {
            $error .= _MISSING_FINGERPRINT.'<br/>';
        }
        if ($find_filesize == false) {
            $error .= _MISSING_FILESIZE.'<br/>';
        }
        if ($find_status == false) {
            $error .= _MISSING_STATUS.'<br/>';
        }

        $this->error = $error;
        if (!empty($error)) {
            return false;
        } else {
            return true;
        }
    }

    /**
    * get the adr of the document
    *
    * @param $view resource view
    * @param $resId resource ID
    * @param $whereClause security clause
    * @return array of adr fields if is ok
    */
    public function getResourceAdr($view, $resId, $whereClause, $adrTable)
    {
        $control = array();
        if (!isset($view) || empty($resId) || empty($whereClause)) {
            $control = array("status" => "ko", "error" => _PB_WITH_ARGUMENTS);
            return $control;
        }
        $docserverAdr = array();
        $db = new Database();
        $query = "select res_id, docserver_id, path, filename, format, fingerprint, is_multi_docservers from " . $view
            . " where res_id = ? ". $whereClause;
        $stmt = $db->query($query, array($resId));
        if ($stmt->rowCount() > 0) {
            $line = $stmt->fetchObject();
            $format = $line->format;
            if ($line->is_multi_docservers == "Y") {
                if (
                    $adrTable == 'adr_letterbox' ||
                    $adrTable == 'adr_attachments' ||
                    $adrTable == 'adr_attachments_version'

                ) {
                    if ($adrTable == 'adr_x') {
                        $adrTable = 'adr_letterbox';
                    }
                    $query = "select res_id, docserver_id, path, filename, fingerprint, adr_priority from "
                        . $adrTable . " where res_id = ? order by adr_priority";
                    $stmt = $db->query($query, array($resId));
                    if ($stmt->rowCount() > 0) {
                        while ($line = $stmt->fetchObject()) {
                            array_push($docserverAdr, array("docserver_id" => $line->docserver_id, "path" => $line->path, "filename" => $line->filename, "format" => $format, "fingerprint" => $line->fingerprint, "adr_priority" => $line->adr_priority));
                        }
                    } else {
                        $control = array("status" => "ko", "error" => _RESOURCE_NOT_FOUND);
                        return $control;
                    }
                } else {
                    $control = array("status" => "ko", "error" => _PB_WITH_ARGUMENTS . ' adrTable');
                    return $control;
                }
            } else {
                array_push($docserverAdr, array("docserver_id" => $line->docserver_id, "path" => $line->path, "filename" => $line->filename, "format" => $format, "fingerprint" => $line->fingerprint, "adr_priority" => ""));
            }
            $control = array("status" => "ok", $docserverAdr, "error" => "");
            return $control;
        } else {
            $control = array("status" => "ko", "error" => _RESOURCE_NOT_FOUND);
            return $control;
        }
    }
 }
