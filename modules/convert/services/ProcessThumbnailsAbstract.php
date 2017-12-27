<?php

/*
*   Copyright 2008-2016 Maarch
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
* @brief process thumbnails class
*
* <ul>
* <li>Services to process the thumbnails of resources</li>
* </ul>
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup convert
*/


require_once 'core/services/Abstract.php';
require_once 'core/class/class_functions.php';
require_once 'core/class/class_db_pdo.php';
require_once 'core/class/class_db.php';
require_once 'core/docservers_tools.php';
require_once 'core/class/docservers_controler.php';
require_once 'core/services/ManageDocservers.php';

class Convert_ProcessThumbnailsAbstract_Service extends Core_Abstract_Service {

    protected $tnlExecutable;

    public function __construct($tnlExecutable = 'convert')
    {
        $this->tnlExecutable = $tnlExecutable;
    }

    /**
     * Ask for thumbnails
     *
     * @param string $collId collection
     * @param string $resTable resource table
     * @param string $adrTable adr table
     * @param long $resId res_id
     * @param string $tmpDir path to tmp
     * @param array $tgtfmt array of target format
     * @return array $returnArray the result
     */
    public function thumbnails(array $args=[])
    {
        $timestart = microtime(true);
        $returnArray = array();
        if (empty($args['collId'])) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'collId empty for thumbnails',
            );
            return $returnArray;
        } else {
            $collId = $args['collId'];
        }
        if (empty($args['resTable'])) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'resTable empty for thumbnails',
            );
            return $returnArray;
        } else {
            $resTable = $args['resTable'];
        }
        if (empty($args['adrTable'])) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'adrTable empty for thumbnails',
            );
            return $returnArray;
        } else {
            $adrTable = $args['adrTable'];
        }
        if (empty($args['resId'])) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'resId empty for thumbnails',
            );
            return $returnArray;
        } else {
            $resId = $args['resId'];
        }

        if (!isset($args['tmpDir']) || $args['tmpDir'] == '') {
            $tmpDir = $_SESSION['config']['tmppath'];
        } else {
            $tmpDir = $args['tmpDir'];
        }

        $dbConv = new Database($GLOBALS['configFile']);
        
        //retrieve path of the resource
        $stmtConv = $dbConv->query("select * from " . $resTable 
            . " where res_id = ?", array($resId)
        );
        $line = $stmtConv->fetchObject();
        $ManageDocservers = new Core_ManageDocservers_Service();
        if ($args['fileSource'] <> '' && file_exists($args['fileSource'])) {
            $resourcePath = $args['fileSource'];
        } else {
            if ($line->res_id <> '')  {
                $adrType = 'CONV';
                if (
                    strtoupper($line->format) == 'HTML' ||
                    strtoupper($line->format) == 'MAARCH'
                ) {
                    $adrType = 'DOC';
                }
                $resourcePath = $ManageDocservers->getSourceResourcePath(
                    $resTable, 
                    $adrTable, 
                    $line->res_id, 
                    $adrType
                );
            }
        }
        
        if (!file_exists($resourcePath)) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'file not already converted in pdf for thumbnails. Path : ' 
                    . $resourcePath . ", adrType : " . $adrType,
            );
            $this->manageErrorOnDb($resTable, $resId, '-1');
            return $returnArray;
        }
        //copy the resource on tmp directory
        $fileNameOnTmp = $tmpDir . rand() . rand();
        if (!copy($resourcePath, $fileNameOnTmp)) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'copy on tmp failed for thumbnails. Copy ' . $resourcePath . ' to ' . $fileNameOnTmp,
            );
            $this->manageErrorOnDb($resTable, $resId, '-1');
            return $returnArray;
        }
        //now do the thumbnails !
        $resultOfConversion = $this->launchThumbnails(
            $fileNameOnTmp, 
            $tmpDir,
            pathinfo($resourcePath, PATHINFO_EXTENSION)
        );
        if ($resultOfConversion['status'] <> '0') {
            $this->manageErrorOnDb($resTable, $resId, '-1');
            return $resultOfConversion;
        }
        //find the target docserver
        $targetDs = $ManageDocservers->findTargetDs($collId, 'TNL');
        if (empty($targetDs)) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'Ds of collection and ds type not found for thumbnails:' 
                    . $collId . ' TNL',
            );
            $this->manageErrorOnDb($resTable, $resId, '-1');
            return $returnArray;
        }
        //copy the result on docserver
        $resultCopyDs = $ManageDocservers->copyResOnDS($fileNameOnTmp . '.png', $targetDs);
        if ($resultCopyDs['status'] <> '0') {
            $this->manageErrorOnDb($resTable, $resId, '-1');
            return $resultCopyDs;
        }
        //update the database
        $resultOfUpDb = $this->updateDatabase(
            $collId,
            $resTable, 
            $adrTable, 
            $resId,
            $targetDs,
            $resultCopyDs['value']['destinationDir'],
            $resultCopyDs['value']['fileDestinationName']
        );
        if ($resultOfUpDb['status'] <> '0') {
            $this->manageErrorOnDb($resTable, $resId, '-1');
            return $resultOfUpDb;
        }

        unlink($fileNameOnTmp);
        unlink($fileNameOnTmp . '.png');

        $returnArray = array(
            'status' => '0',
            'value' => '',
            'error' => '',
        );
        Core_Logs_Service::executionTimeLog($timestart, '', 'info', '[TIMER] Convert_ProcessThumbnailsAbstract_Service::thumbnails');
        return $returnArray;
    }

    /**
     * Launch the thumbnails process
     *
     * @param string $srcfile source file
     * @param string $tgtdir target dir
     * @param string $srcfmt source format
     * @return array $returnArray the result
     */
    private function launchThumbnails(
        $srcfile, 
        $tgtdir=false, 
        $srcfmt
    ) {
        $timestart = microtime(true);
        if (!$tgtdir) {
            $tgtdir = dirname($srcfile);
        }

        $output = array();
        $return = null;
        $this->errors = array();

        //wkhtmltoimage must be installed with compiled sources
        if (strtoupper($srcfmt) == 'MAARCH' || strtoupper($srcfmt) == 'HTML') {
            copy($srcfile, str_ireplace('.maarch', '.', $srcfile) . '.html');
            if (file_exists('/usr/bin/mywkhtmltoimage')) {
                $command = "mywkhtmltoimage  --width 164 --height 105 --quality 100 --zoom 0.2 " 
                    . escapeshellarg(str_ireplace('.maarch', '.', $srcfile) . '.html') . " " 
                    . escapeshellarg($tgtdir . basename(str_ireplace('.maarch', '.', $srcfile)) . '.png');
            } else {
                $envVar = "export DISPLAY=FRPAROEMINT:0.0 ; ";
                $command = $envVar . "wkhtmltoimage --width 164 --height 105 --quality 100 --zoom 0.2 " 
                    . escapeshellarg(str_ireplace('.maarch', '.', $srcfile) . '.html') . " " 
                    . escapeshellarg($tgtdir . basename(str_ireplace('.maarch', '.', $srcfile)) . '.png');
            }
        } else {
            $command = "convert -thumbnail 200x300 -background white -alpha remove " 
                . escapeshellarg($srcfile) . "[0] "
                . escapeshellarg($tgtdir . basename($srcfile) . '.png');
        }
        //echo $command . PHP_EOL;exit;
        $timestart_command = microtime(true);
        exec($command, $output, $return);
        Core_Logs_Service::debug(['message'=>'[TIMER] Commande : ' . $command]);
        Core_Logs_Service::executionTimeLog($timestart_command, '', 'debug', '[TIMER] Convert_ProcessThumbnailsAbstract_Service::launchThumbnails__exec');

        if ($return === 0) {
            $returnArray = array(
                'status' => '0',
                'value' => '',
                'error' => '',
            );
        } else {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => $return . $output,
            );
        }
        if (strtoupper($srcfmt) == 'MAARCH' || strtoupper($srcfmt) == 'HTML') {
            $returnArray = array();
            unlink(str_ireplace('.maarch', '.', $srcfile) . '.html');
            $returnArray = array(
                'status' => '0',
                'value' => '',
                'error' => '',
            );
        }
        Core_Logs_Service::executionTimeLog($timestart, '', 'debug', '[TIMER] Convert_ProcessThumbnailsAbstract_Service::launchThumbnails');
        return $returnArray;
    }

    /**
     * Updating the database with the location information of the document on the
     * new docserver
     * @param string $collId collection
     * @param string $resTable res table
     * @param string $adrTable adr table
     * @param bigint $resId Id of the resource to process
     * @param docserver $docserver docserver object
     * @param string $path location of the resource on the docserver
     * @param string $fileName file name of the resource on the docserver
     * @return array $returnArray the result
     */
    private function updateDatabase(
        $collId, 
        $resTable, 
        $adrTable, 
        $resId,
        $docserver,
        $path, 
        $fileName
    ) {
        try {
            $docserver->path_template = str_replace(
                DIRECTORY_SEPARATOR, 
                '#', 
                $docserver->path_template
            );
            $path = str_replace($docserver->path_template, '', $path);
            $dbConv = new Database($GLOBALS['configFile']);
            $query = "update convert_stack set status = 'P' where "
               . " coll_id = ? and res_id = ?";
            $stmt = $dbConv->query(
                $query,
                array(
                    $collId,
                    $resId
                )
            );

            $query = "select * from " . $adrTable 
                . " where res_id = ? order by adr_priority";
            $stmt = $dbConv->query($query, array($resId));
            if ($stmt->rowCount() == 0) {
                $query = "select docserver_id, path, filename, offset_doc, fingerprint"
                       . " from " . $resTable . " where res_id = ?";
                $stmt = $dbConv->query($query, array($resId));
                $recordset = $stmt->fetchObject();
                $resDocserverId = $recordset->docserver_id;
                $resPath = $recordset->path;
                $resFilename = $recordset->filename;
                $resOffsetDoc = $recordset->offset_doc;
                $fingerprintInit = $recordset->fingerprint;
                $query = "select adr_priority_number from docservers "
                       . " where docserver_id = ?";
                $stmt = $dbConv->query($query, array($resDocserverId));
                $recordset = $stmt->fetchObject();
                $query = "insert into " . $adrTable . " (res_id, "
                       . "docserver_id, path, filename, offset_doc, fingerprint, "
                       . "adr_priority) values (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $dbConv->query(
                    $query, 
                    array(
                        $resId,
                        $resDocserverId,
                        $resPath,
                        $resFilename,
                        $resOffsetDoc,
                        $fingerprintInit,
                        $recordset->adr_priority_number
                    )
                );
            }

            $query = "select * from " . $adrTable 
                . " where res_id = ? and adr_type = 'TNL'";
            $stmt = $dbConv->query($query, array($resId));
            if ($stmt->rowCount() == 0) {
                $query = "insert into " . $adrTable . " (res_id, docserver_id, "
                   . "path, filename, offset_doc, fingerprint, adr_priority, adr_type) values (" 
                   . "?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $dbConv->query(
                    $query, 
                    array(
                        $resId,
                        $docserver->docserver_id,
                        $path,
                        $fileName,
                        $offsetDoc,
                        $fingerprint,
                        $docserver->adr_priority_number,
                        'TNL'
                    )
                );
            } else {
                $query = "update " . $adrTable . " set docserver_id = ?, "
                   . " path = ?, filename = ?, offset_doc = ?, fingerprint = ?, adr_priority = ?"
                   . " where res_id = ? and adr_type = ? ";
                $stmt = $dbConv->query(
                    $query, 
                    array(
                        $docserver->docserver_id,
                        $path,
                        $fileName,
                        $offsetDoc,
                        $fingerprint,
                        $docserver->adr_priority_number,
                        $resId,
                        'TNL'
                    )
                );
            }
            if ($_SESSION['user']['UserId'] <> '') {
                $user = $_SESSION['user']['UserId'];
            } else {
                $user = 'CONVERT_BOT';
            }
            $query = "insert into history (table_name, record_id, "
                   . "event_type, user_id, event_date, info, id_module) values (" 
                   . "?, ?, 'ADD', '" . $user . "', " 
                   . $dbConv->current_datetime() 
                   . ", ?, 'convert')";
            $stmt = $dbConv->query( 
                $query, 
                array(
                    $resTable,
                    $resId,
                    "process thumbnails done"
                )
            );

            $query = "update " . $resTable 
                . " set tnl_result = '1', is_multi_docservers = 'Y' where "
                . " res_id = ?";
            $stmt = $dbConv->query(
                $query,
                array(
                    $resId
                )
            );
            $returnArray = array(
                'status' => '0',
                'value' => '',
                'error' => '',
            );
            return $returnArray;
        } catch (Exception $e) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => $e->getMessage(),
            );
            return $returnArray;
        }
    }

    /**
     * Updating the database with the error code
     * @param string $resTable res table
     * @param bigint $resId Id of the resource to process
     * @param string $result error code
     * @return nothing
     */
    private function manageErrorOnDb(
        $resTable, 
        $resId,
        $result
    ) {
        $dbConv = new Database($GLOBALS['configFile']);
        $query = "update " . $resTable 
            . " set tnl_result = ? where "
            . " res_id = ?";
        $stmt = $dbConv->query(
            $query,
            array(
                $result,
                $resId
            )
        );
    }

    /**
     * Test if the record is already processed by convert module
     * @param string $resTable res table
     * @param bigint $resId Id of the resource to process
     * @return boolean
     */
    public function isAlreadyProcessedByhumbnails(
        $resTable, 
        $resId
    ) {
        $dbConv = new Database($GLOBALS['configFile']);
        $query = "select tnl_result from " . $resTable 
            . "  where res_id = ?";
        $stmt = $dbConv->query(
            $query,
            array(
                $resId
            )
        );
        $rs = $stmt->fetchObject();
        if (
            empty($rs->tnl_result) || 
            $rs->tnl_result == '0'
        ) {
            return false;
        } else {
            return true;
        }
    }
}
