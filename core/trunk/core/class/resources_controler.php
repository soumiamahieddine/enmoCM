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
        $func = new functions();
        $data = $func->object2array($data);
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
        return $resId;
    }

    private function prepareStorage($data, $docserverId, $status, $fileFormat)
    {
        $statusFound = false;
        $typistFound = false;
        $typeIdFound = false;
        $toAddressFound = false;
        for ($i=0;$i<count($data);$i++) {
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
    ## Web Service de versement de données issue du gros scanner
    #####################################
    public function storeAttachmentResource($resId, $collId, $encodedContent, $fileFormat, $fileName)
    {
        require_once 'core/class/class_request.php';
        require_once 'core/class/class_resource.php';
        require_once 'core/class/docservers_controler.php';
        require_once 'core/class/class_security.php';
        $sec = new security();
        $table = $sec->retrieve_table_from_coll($collId);
        $db = new request();
        $db->connect();
        $query = 'select res_id from ' . $table . ' where res_id = '
               . $resId;
        $db->query($query);
        if ($db->nb_result() == 0) {
            $status = 'ko';
            $error .= 'res_id inexistant';
        } else {
            $fileContent = base64_decode($encodedContent);
            $tmpFileName = 'tmp_file_ws_'
                 . rand() . "_" . md5($fileContent) 
                 . "." . strtolower($fileFormat);
            $Fnm = $_SESSION['config']['tmppath'] . $tmpFileName; 
            $inF = fopen($Fnm, "w");
            fwrite($inF, $fileContent);
            fclose($inF);
            $docserverControler = new docservers_controler();
            $docserver = $docserverControler->getDocserverToInsert(
               $collId
            );
            if (empty($docserver)) {
                $status = 'ko';
                $error = _DOCSERVER_ERROR . ' : '
                    . _NO_AVAILABLE_DOCSERVER . ". " . _MORE_INFOS . ".";
            } else {
                $newSize = $docserverControler->checkSize(
                    $docserver, $_SESSION['upfile']['size']
                );
                if ($newSize == 0) {
                    $status = 'ko';
                    $error = _DOCSERVER_ERROR . ' : '
                        . _NOT_ENOUGH_DISK_SPACE . ". " . _MORE_INFOS . ".";
                } else {
                    $fileInfos = array(
                        "tmpDir"      => $_SESSION['config']['tmppath'],
                        "size"        => filesize($Fnm),
                        "format"      => strtolower($fileFormat),
                        "tmpFileName" => $tmpFileName,
                    );
                    $storeResult = array();
                    $storeResult = $docserverControler->storeResourceOnDocserver(
                        $collId, $fileInfos
                    );
                    if (isset($storeResult['error']) && $storeResult['error'] <> '') {
                        $status = 'ko';
                        $error = $storeResult['error'];
                    } else {
                        unlink($Fnm);
                        $resAttach = new resource();
                        $_SESSION['data'] = array();
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "typist",
                                'value' => $_SESSION['user']['UserId'],
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "format",
                                'value' => strtolower($fileFormat),
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "docserver_id",
                                'value' => $storeResult['docserver_id'],
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "status",
                                'value' => 'NEW',
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "offset_doc",
                                'value' => ' ',
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "logical_adr",
                                'value' => ' ',
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "title",
                                'value' => strtolower($fileName),
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "coll_id",
                                'value' => $collId,
                                'type' => "string",
                            )
                        );
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "res_id_master",
                                'value' => $resId,
                                'type' => "integer",
                            )
                        );
                        if ($_SESSION['origin'] == "scan") {
                            array_push(
                                $_SESSION['data'],
                                array(
                                    'column' => "scan_user",
                                    'value' => $_SESSION['user']['UserId'],
                                    'type' => "string",
                                )
                            );
                            array_push(
                                $_SESSION['data'],
                                array(
                                    'column' => "scan_date",
                                    'value' => $req->current_datetime(),
                                    'type' => "function",
                                )
                            );
                        }
                        array_push(
                            $_SESSION['data'],
                            array(
                                'column' => "type_id",
                                'value' => 0,
                                'type' => "int",
                            )
                        );
                        $id = $resAttach->load_into_db(
                            'res_attachments',
                            $storeResult['destination_dir'],
                            $storeResult['file_destination_name'] ,
                            $storeResult['path_template'],
                            $storeResult['docserver_id'], $_SESSION['data'],
                            $_SESSION['config']['databasetype']
                        );
                        if ($id == false) {
                            $status = 'ko';
                            $error = $resAttach->get_error();
                        } else {
                            $status = 'ok';
                            if ($_SESSION['history']['attachadd'] == "true") {
                                $users = new history();
                                $view = $sec->retrieve_view_from_coll_id(
                                    $collId
                                );
                                $users->add(
                                    $view, $resId, "ADD", 'attachadd',
                                    ucfirst(_DOC_NUM) . $id . ' '
                                    . _NEW_ATTACH_ADDED . ' ' . _TO_MASTER_DOCUMENT
                                    . $resId,
                                    $_SESSION['config']['databasetype'],
                                    'apps'
                                );
                                $users->add(
                                    RES_ATTACHMENTS_TABLE, $id, "ADD",'attachadd',
                                    _NEW_ATTACH_ADDED . " (" . $fileName
                                    . ") ",
                                    $_SESSION['config']['databasetype'],
                                    'attachments'
                                );
                            }
                        }
                    }
                }
            }
        }
        $returnArray = array(
            'status' => $status,
            'value' => $id,
            'error' => $error,
        );
        return $returnArray;
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
