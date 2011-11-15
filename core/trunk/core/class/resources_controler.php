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
}
