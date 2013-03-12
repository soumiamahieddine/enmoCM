<?php
/*
*   Copyright 2011 Maarch
*
*   This file is part of Maarch Framework.
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  Contains the controler of the Resource Object
*
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

// To activate de debug mode of the class
$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

// Loads the required class
try {
    require_once 'core/class/resources.php';
    require_once 'core/core_tables.php';
    require_once 'core/class/class_functions.php';
    require_once 'core/class/docservers_controler.php';
    require_once 'core/class/class_resource.php';
} catch (Exception $e) {
    echo $e->getMessage().' // ';
}

/**
* @brief  Controler of the Resource Object
*
* @ingroup core
*/
class resources_controler
{
    #####################################
    ## Web Service de versement de données issue du gros scanner
    #####################################
    public function storeResource($encodedFile, $data, $collId, $table, $fileFormat, $status)
    {
        try {
            $func = new functions();
            $data = $func->object2array($data);
            for ($i=0; $i < count($data);$i++) {
                $data[$i]['column'] = strtolower($data[$i]['column']);
            }
            $returnCode = 0;
            $db = new dbquery();
            $db->connect();
            //copy sended file on tmp 
            $fileContent = base64_decode($encodedFile);
            $random = rand();
            $fileName = 'tmp_file_' . $random . '.' . $fileFormat;
            $Fnm = $_SESSION['config']['tmppath'] . $fileName;
            $inF = fopen($Fnm,"w");
            fwrite($inF, $fileContent);
            fclose($inF);
            //store resource on docserver
            $docserverControler = new docservers_controler();
            $fileInfos = array(
                'tmpDir'      => $_SESSION['config']['tmppath'],
                'size'        => filesize($Fnm),
                'format'      => $fileFormat,
                'tmpFileName' => $fileName,
            );
            //print_r($fileInfos);
            $storeResult = array();
            $storeResult = $docserverControler->storeResourceOnDocserver(
                $collId, $fileInfos
            );
            if (!empty($storeResult['error'])) {
                $returnResArray = array(
                    'returnCode' => (int) -3,
                    'resId' => '',
                    'error' => $storeResult['error'],
                );
                return $returnResArray;
            }
            //print_r($storeResult);exit;
            //store resource metadata in database
            $resource = new resource();
            
            $data = $this->prepareStorage(
                $data, 
                $storeResult['docserver_id'],
                $status,
                $fileFormat
            );
            unlink($Fnm);
            //var_dump($data);exit;
            $resId = $resource->load_into_db(
                $table, 
                $storeResult['destination_dir'],
                $storeResult['file_destination_name'],
                $storeResult['path_template'],
                $storeResult['docserver_id'], 
                $data,
                $_SESSION['config']['databasetype']
            );
            if (!is_numeric($resId)) {
                $returnResArray = array(
                    'returnCode' => (int) -2,
                    'resId' => '',
                    'error' => 'Pb with SQL insertion : ' .$resId ,
                );
                return $returnResArray;
            }
            if ($resId == 0) {
                $resId = '';
            }
            $returnResArray = array(
                'returnCode' => (int) 0,
                'resId' => $resId,
                'error' => '',
            );
            return $returnResArray;
        } catch (Exception $e) {
            $returnResArray = array(
                'returnCode' => (int) -1,
                'resId' => '',
                'error' => 'unknown error' . $e->getMessage(),
            );
            return $returnResArray;
        }
    }

    private function prepareStorage($data, $docserverId, $status, $fileFormat)
    {
        $statusFound = false;
        $typistFound = false;
        $typeIdFound = false;
        $toAddressFound = false;
        for ($i=0;$i<count($data);$i++) {
            if (strtoupper($data[$i]['type']) == 'INTEGER' || strtoupper($data[$i]['type']) == 'FLOAT') {
                if ($data[$i]['value'] == '') {
                    $data[$i]['value'] = '0';
                }
            }
            if (strtoupper($data[$i]['column']) == strtoupper('status')) {
                $statusFound = true;
            }
            if (strtoupper($data[$i]['column']) == strtoupper('typist')) {
                $typistFound = true;
            }
            if (strtoupper($data[$i]['column']) == strtoupper('type_id')) {
                $typeIdFound = true;
            }
            if (strtoupper($data[$i]['column']) == strtoupper('custom_t10')) {
                require_once 'core/class/class_db.php';
                $dbQuery = new dbquery();
                $dbQuery->connect();
                $mail = array();
                $theString = str_replace(">", "", $data[$i]['value']);
                $mail = explode("<", $theString);
                $queryUser = "select user_id from users where mail = "
                    . "'" . $dbQuery->protect_string_db($mail[count($mail) -1]) . "'";
                $dbQuery->query($queryUser);
                $userIdFound = $dbQuery->fetch_object();
                if (!empty($userIdFound->user_id)) {
                    $toAddressFound = true;
                    $destUser = $userIdFound->user_id;
                }
            }
        }
        if (!$typistFound) {
            array_push(
                $data,
                array(
                    'column' => 'typist',
                    'value' => 'auto',
                    'type' => 'string',
                )
            );
        }
        if (!$typeIdFound) {
            array_push(
                $data,
                array(
                    'column' => 'type_id',
                    'value' => '10',
                    'type' => 'string',
                )
            );
        }
        if (!$statusFound) {
            array_push(
                $data,
                array(
                    'column' => 'status',
                    'value' => $status,
                    'type' => 'string',
                )
            );
        }
        if ($toAddressFound) {
            array_push(
                $data,
                array(
                    'column' => 'dest_user',
                    'value' => $destUser,
                    'type' => 'string',
                )
            );
        }
        array_push(
            $data,
            array(
                'column' => 'format',
                'value' => $fileFormat,
                'type' => 'string',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'offset_doc',
                'value' => '',
                'type' => 'string',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'logical_adr',
                'value' => '',
                'type' => 'string',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'docserver_id',
                'value' => $docserverId,
                'type' => 'string',
            )
        );
        return $data;
    }
    
    #####################################
    ## Store datas of the resource in extension table 
    #####################################
    public function storeExtResource($resId, $data, $table)
    {
        try {
            $func = new functions();
            $data = $func->object2array($data);
            $queryExtFields = '(';
            $queryExtValues = '(';
            for ($i=0;$i<count($data);$i++) {
                if (strtoupper($data[$i]['type']) == 'INTEGER' || strtoupper($data[$i]['type']) == 'FLOAT') {
                    if ($data[$i]['value'] == '') {
                        $data[$i]['value'] = '0';
                    }
                }
                //COLUMN
                $data[$i]['column'] = strtolower($data[$i]['column']);
                $queryExtFields .= $data[$i]['column'] . ',';
                //VALUE
                if ($data[$i]['type'] == 'string' || $data[$i]['type'] == 'date') {
                    $queryExtValues .= "'" . $data[$i]['value'] . "',";
                } else {
                    $queryExtValues .= $data[$i]['value'] . ",";
                }
            }
            $queryExtFields = preg_replace('/,$/', ',res_id)', $queryExtFields);
            $queryExtValues = preg_replace(
                '/,$/', ',' . $resId . ')', $queryExtValues
            );
            $queryExt = " insert into " . $table . " " . $queryExtFields
                   . ' values ' . $queryExtValues ;
            $returnCode = 0;
            $db = new dbquery();
            $db->connect();
            if ($db->query($queryExt)) {
                $returnResArray = array(
                    'returnCode' => (int) 0,
                    'resId' => $resId,
                    'error' => '',
                );
            } else {
                $returnResArray = array(
                    'returnCode' => (int) -2,
                    'resId' => '',
                    'error' => 'Pb with SQL insertion',
                );
            }
            return $returnResArray;
        } catch (Exception $e) {
            $returnResArray = array(
                'returnCode' => (int) -1,
                'resId' => '',
                'error' => 'unknown error' . $e->getMessage(),
            );
            return $returnResArray;
        }
    }
    
    function Demo_searchResources($searchParams)
    {
        $whereClause = '';
        if ($searchParams->countryForm <> '') {
            $whereClause .= " custom_t3 = '" . $searchParams->countryForm . "' and ";
        }
        if ($searchParams->docDateForm <> '') {
            $whereClause .= " doc_date >= '" . $searchParams->docDateForm . "'";
        }
        $listResult = array();
        try {
            $db = new dbquery();
            $db->connect();
            $cpt = 0;
            $db->query("select * from res_x where " . $whereClause . " ORDER BY res_id ASC");
            if ($db->nb_result() > 0) {
                while ($line = $db->fetch_object()) {
                    $listResult[$cpt]['resid'] = $line->res_id;
                    $listResult[$cpt]['subject'] = $line->subject;
                    $listResult[$cpt]['docdate'] = $line->doc_date;
                    $cpt++;
                }
            } else {
                $error = _NO_DOC_OR_NO_RIGHTS;
            }
        } catch (Exception $e) {
            $fault = new SOAP_Fault($e->getMessage(), '1');
            return $fault->message();
        }
        $return = array(
            'status' => 'ok',
            'value' => $listResult,
            'error' => $error,
        );
        return $return;
    }
}
