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
* @brief process convert class
*
* <ul>
* <li>Services to process the convertion of resources</li>
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

class Convert_ProcessConvertAbstract_Service extends Core_Abstract_Service {

    protected $libreOfficeExecutable;

    //public function __construct($libreOfficeExecutable = 'cloudooo')
    public function __construct($libreOfficeExecutable = 'soffice')
    //public function __construct($libreOfficeExecutable = 'unoconv')
    {
        $this->libreOfficeExecutable = $libreOfficeExecutable;
    }

    /**
     * Ask for conversion
     *
     * @param string $collId collection
     * @param string $resTable resource table
     * @param string $adrTable adr table
     * @param long $resId res_id
     * @param string $tmpDir path to tmp
     * @param array $tgtfmt array of target format
     * @return array $returnArray the result
     */
    public function convert(array $args=[])
    {
        $timestart = microtime(true);
        // Core_Logs_Service::info(['message'=>'debut convert', 'code'=>111, ]);
        $returnArray = array();
        if (empty($args['collId'])) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'collId empty',
            );
            return $returnArray;
        } else {
            $collId = $args['collId'];
        }
        if (empty($args['resTable'])) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'resTable empty',
            );
            return $returnArray;
        } else {
            $resTable = $args['resTable'];
        }
        if (empty($args['adrTable'])) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'adrTable empty',
            );
            return $returnArray;
        } else {
            $adrTable = $args['adrTable'];
        }
        if (empty($args['resId'])) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'resId empty',
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
        if ($line->res_id <> '') {
            $resourcePath = $ManageDocservers->getSourceResourcePath(
                $resTable, 
                $adrTable, 
                $line->res_id,
                'DOC'
            );
        }
        if (!file_exists($resourcePath)) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'file not exists : ' . $resourcePath,
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
                'error' => 'copy on tmp failed',
            );
            $this->manageErrorOnDb($resTable, $resId, '-1');
            return $returnArray;
        }
        //now do the conversion !
        if (strtoupper($line->format) <> 'PDF') {
            $resultOfConversion = $this->launchConvert(
                $fileNameOnTmp, 
                'pdf', 
                $tmpDir,
                pathinfo($resourcePath, PATHINFO_EXTENSION)
            );
        } else {
            //echo $fileNameOnTmp;
            copy($fileNameOnTmp, $fileNameOnTmp . '.pdf');
            $resultOfConversion = array(
                'status' => '0',
                'value' => '',
                'error' => '',
            );
        }
        
        if ($resultOfConversion['status'] <> '0') {
            $this->manageErrorOnDb($resTable, $resId, '-1');
            return $resultOfConversion;
        }
        //find the target docserver
        $targetDs = $ManageDocservers->findTargetDs($collId);
        if (empty($targetDs)) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'Ds of collection not found:' . $collId,
            );
            $this->manageErrorOnDb($resTable, $resId, '-1');
            return $returnArray;
        }
        //copy the result on docserver
        // Core_Logs_Service::info(['message'=>'avant cp ds', 'code'=>1112, ]);
        $resultCopyDs = $ManageDocservers->copyResOnDS($fileNameOnTmp . '.pdf', $targetDs);
        if ($resultCopyDs['status'] <> '0') {
            $this->manageErrorOnDb($resTable, $resId, '-1');
            return $resultCopyDs;
        }
        // Core_Logs_Service::info(['message'=>'avant update', 'code'=>19, ]);
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
        // Core_Logs_Service::info(['message'=>var_export($resultOfUpDb, true), 'code'=>111111, ]);
        // Core_Logs_Service::info(['message'=>$collId, 'code'=>2, ]);
        // Core_Logs_Service::info(['message'=>$resTable, 'code'=>3, ]);
        // Core_Logs_Service::info(['message'=>$adrTable, 'code'=>4, ]);
        // Core_Logs_Service::info(['message'=>$resId, 'code'=>5, ]);
        // Core_Logs_Service::info(['message'=>'apres res_id', 'code'=>6, ]);
        // Core_Logs_Service::info(['message'=>$targetDs, 'code'=>6, ]);
        // Core_Logs_Service::info(['message'=>var_export($resultCopyDs, true), 'code'=>7, ]);

        if ($resultOfUpDb['status'] <> '0') {
            $this->manageErrorOnDb($resTable, $resId, '-1');
            return $resultOfUpDb;
        }

        unlink($fileNameOnTmp);
        unlink($fileNameOnTmp . '.pdf');

        $returnArray = array(
            'status' => '0',
            'value' => '',
            'error' => '',
        );
        Core_Logs_Service::executionTimeLog($timestart, '', 'debug', '[TIMER] Convert_ProcessConvertAbstract_Service::convert');
        return $returnArray;
    }

    /**
     * Launch the conversion
     *
     * @param string $srcfile source file
     * @param string $tgtfmt target format
     * @param string $tgtdir target dir
     * @param string $srcfmt source format
     * @return array $returnArray the result
     */
    public function launchConvert(
        $srcfile, 
        $tgtfmt, 
        $tgtdir=false, 
        $srcfmt=null
    ) {
		$timestart=microtime(true);

        $processHtml = false;
		$executable='';
		
        Core_Logs_Service::info(['message'=>'[TIMER] Debut Convert_ProcessConvertAbstract_Service::launchConvert']);
        if (strtoupper($srcfmt) == 'MAARCH' || strtoupper($srcfmt) == 'HTML') {
            $processHtml = true;
            Core_Logs_Service::info(['message'=>'[TIMER] srcfmt ' . $srcfmt]);
            copy($srcfile, str_ireplace('.maarch', '.', $srcfile) . '.html');
            if (file_exists('/usr/bin/mywkhtmltopdf')) {
                $command = "mywkhtmltopdf " 
                    . escapeshellarg(str_ireplace('.maarch', '.', $srcfile) . '.html') . " " 
                    . escapeshellarg($tgtdir . basename(str_ireplace('.maarch', '.', $srcfile)) . '.pdf');
            } else {
                $envVar = "export DISPLAY=FRPAROEMINT:0.0 ; ";
                $command = $envVar . "wkhtmltopdf " 
                    . escapeshellarg(str_ireplace('.maarch', '.', $srcfile) . '.html') . " " 
                    . escapeshellarg($tgtdir . basename(str_ireplace('.maarch', '.', $srcfile)) . '.pdf');
            }
			$executable='wkhtmltopdf';
        } else {
			$executable='soffice';
            Core_Logs_Service::info(['message'=>'[TIMER] let LO do it ' . $this->libreOfficeExecutable]);
            if ($this->libreOfficeExecutable == "cloudooo") {
                $serverAddress = "http://192.168.21.40:8011";
                $tokens = array();
                require_once 'apps/maarch_entreprise/tools/phpxmlrpc/lib/xmlrpc.inc';
                require_once 'apps/maarch_entreprise/tools/phpxmlrpc/lib/xmlrpcs.inc';
                require_once 'apps/maarch_entreprise/tools/phpxmlrpc/lib/xmlrpc_wrappers.inc';
                $fileContent = file_get_contents($srcfile, FILE_BINARY);
                $encodedContent = base64_encode($fileContent);
                $params = array();
                array_push($params, new PhpXmlRpc\Value($encodedContent));
                array_push($params, new PhpXmlRpc\Value($srcfmt));
                array_push($params, new PhpXmlRpc\Value($tgtfmt));
                array_push($params, new PhpXmlRpc\Value(false));
                $v = new PhpXmlRpc\Value($params, "array");
            } elseif ($this->libreOfficeExecutable == "unoconv") {
                $tokens = array('"' . $this->libreOfficeExecutable . '"');
                $tokens[] = "-f";
                $tokens[] = $tgtfmt;
                $tokens[] = '-o "' . $srcfile . '.' . $tgtfmt . '"';
                $tokens[] = '"' . $srcfile . '"';
            } else {
                $tokens = array('"' . $this->libreOfficeExecutable . '"');
                $tokens[] = "--headless";
                $tokens[] = "--convert-to";
                $tokens[] = $tgtfmt;
                $tokens[] = '"' . $srcfile . '"';
                if (!$tgtdir) {
                    $tgtdir = dirname($srcfile);
                }
                $tokens[] = '--outdir "' . $tgtdir . '"';
            }
            
            if (!$srcfmt) {
                $tokens[] = $srcfmt;
            }

            $command = implode(' ', $tokens);

            $output = array();
            $return = null;
            $this->errors = array();
        }
        //echo $command . '<br />';exit;
        if ($this->libreOfficeExecutable == "cloudooo" && !$processHtml) {
            Core_Logs_Service::info(['message'=>'[TIMER] commande : cloudooo url ' . $serverAddress]);
            Core_Logs_Service::info(['message'=>'[TIMER] Debut Convert_ProcessConvertAbstract_Service::launchConvert__exec']);
            $req = new PhpXmlRpc\Request('convertFile', $v);
            //Core_Logs_Service::info(['message'=>'[TIMER] commande : cloudooo url ' . $serverAddress]);
            Core_Logs_Service::info(['message'=>'[TIMER] Fin Convert_ProcessConvertAbstract_Service::launchConvert__exec']);
            $client = new PhpXmlRpc\Client($serverAddress);
            $resp = $client->send($req);
            if (!$resp->faultCode()) {
                $encoder = new PhpXmlRpc\Encoder();
                $value = $encoder->decode($resp->value());
                $theFile = fopen($srcfile . '.' . $tgtfmt, 'w+');
                fwrite($theFile, base64_decode($value));
                fclose($theFile);
                $returnArray = array(
                    'status' => '0',
                    'value' => '',
                    'error' => '',
                );
            } else {
                //print "An error occurred: ";
                //print "Code: " . htmlspecialchars($resp->faultCode()) 
                //    . " Reason: '" . htmlspecialchars($resp->faultString()) . "'\n";
                $returnArray = array(
                    'status' => '1',
                    'value' => '',
                    'error' => "Code: " . htmlspecialchars($resp->faultCode()) 
                        . " Reason: '" . htmlspecialchars($resp->faultString()),
                );
            }
        } else {
		    $timestart_command = microtime(true);
		    exec("timeout -k 5m 3m " . $command, $output, $return);
		    Core_Logs_Service::debug(['message'=>'[TIMER] commande : ' . $command]);
		    Core_Logs_Service::executionTimeLog($timestart_command, '', 'info', '[TIMER] ' . $executable . ' - Convert_ProcessConvertAbstract_Service::launchConvert__exec');
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
        Core_Logs_Service::executionTimeLog($timestart, '', 'info', '[TIMER] Fin Convert_ProcessConvertAbstract_Service::launchConvert');
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
                // Core_Logs_Service::info(['message'=>$recordset, 'code'=>8, ]);
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
                . " where res_id = ? and adr_type = 'CONV'";
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
                        'CONV'
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
                        'CONV'
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
                    "process convert done"
                )
            );

            $queryCpt = "select coalesce(custom_t9, '0') as custom_t9 from " . $resTable 
                . " where res_id = ?";
            $stmtCpt = $dbConv->query($queryCpt, array($resId));
            $rsCpt = $stmtCpt->fetchObject();
            $cptConvert = $rsCpt->custom_t9 + 1;

            $query = "update " . $resTable 
                . " set convert_result = '1', is_multi_docservers = 'Y', custom_t9 = '" . $cptConvert . "' where "
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
            . " set convert_result = ? where "
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
    public function isAlreadyProcessedByConvert(
        $resTable, 
        $resId
    ) {
        $dbConv = new Database($GLOBALS['configFile']);
        $query = "select convert_result from " . $resTable 
            . "  where res_id = ?";
        $stmt = $dbConv->query(
            $query,
            array(
                $resId
            )
        );
        $rs = $stmt->fetchObject();
        if (
            empty($rs->convert_result) || 
            $rs->convert_result == '0'
        ) {
            return false;
        } else {
            return true;
        }
    }
}
