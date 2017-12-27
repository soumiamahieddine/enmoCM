<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
* @brief process fulltext class
*
* <ul>
* <li>Services to process the fulltext of resources</li>
* </ul>
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup convert
*/

namespace Convert\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Convert\Models\ProcessFulltextModel;
use Core\Models\ResDocserverModel;
use Core\Controllers\LogsController;

require_once 'core/class/class_functions.php';
require_once 'core/class/class_db_pdo.php';
// require_once 'core/class/class_db.php';
// require_once 'core/docservers_tools.php';
// require_once 'core/class/docservers_controler.php';
// require_once 'core/services/ManageDocservers.php';

//include_once('html2text/html2text.php');

class ProcessFulltextController
{
    protected $pdftotext;

    public function __construct($pdftotext = 'pdftotext')
    {
        // Storing text in lucene index
        set_include_path('apps/maarch_entreprise/tools/' 
            . PATH_SEPARATOR . get_include_path()
        );

        if(!@include('Zend/Search/Lucene.php')) {
            set_include_path($GLOBALS['MaarchDirectory'].'apps/maarch_entreprise/tools/' 
                . PATH_SEPARATOR . get_include_path()
            );

            require_once("Zend/Search/Lucene.php");
        }

        $this->pdftotext = $pdftotext;
    }

    /**
     * Ask for fulltext
     *
     * @param string $collId collection
     * @param string $resTable resource table
     * @param string $adrTable adr table
     * @param long $resId res_id
     * @param string $tmpDir path to tmp
     * @param array $tgtfmt array of target format
     * @return array $returnArray the result
     */
    public function fulltext(array $args=[])
    {
        $timestart = microtime(true);
        $returnArray = array();
        if (empty($args['collId'])) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'collId empty for fulltext',
            );
            return $returnArray;
        } else {
            $collId = $args['collId'];
        }
        if (empty($args['resTable'])) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'resTable empty for fulltext',
            );
            return $returnArray;
        } else {
            $resTable = $args['resTable'];
        }
        if (empty($args['adrTable'])) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'adrTable empty for fulltext',
            );
            return $returnArray;
        } else {
            $adrTable = $args['adrTable'];
        }
        if (empty($args['resId'])) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'resId empty for fulltext',
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

        if(isset($args['path_to_lucene']) && !empty($args['path_to_lucene'])){
            $indexFileDirectory = $args['path_to_lucene'];
        } else {
            $countColl = count($_SESSION['collections']);
            for ($i=0;$i<$countColl;$i++) {
                if ($_SESSION['collections'][$i]['id'] == $collId) {
                    $indexFileDirectory 
                        = $_SESSION['collections'][$i]['path_to_lucene_index'];
                }
            }
        }

        $dbConv = new \Database($GLOBALS['configFile']);
        
        //retrieve path of the resource
        $stmtConv = $dbConv->query("select * from " . $resTable 
            . " where res_id = ?", array($resId)
        );
        $line = $stmtConv->fetchObject();
        
        if ($line->res_id <> '')  {
            $resourcePath = ResDocserverModel::getSourceResourcePath(
                [
                    'resTable' => $resTable, 
                    'adrTable' => $adrTable, 
                    'resId' => $line->res_id, 
                    'adrType' => 'CONV'
                ]
            );
        }
        if (!file_exists($resourcePath)) {
            $returnArray = array(
                'status' => '2',
                'value' => '',
                'error' => 'file not already converted in pdf for fulltext. path :' 
                    . $resourcePath . ", adrType : CONV, adr_table : " . $adrTable,
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
                'error' => 'copy on tmp failed for fulltext. Copy ' . $resourcePath . ' to ' . $fileNameOnTmp,
            );
            $this->manageErrorOnDb($resTable, $resId, '-1');
            return $returnArray;
        }
        //now do the fulltext !
        if (!empty($args['zendIndex'])) {
            $resultOfConversion = $this->launchFulltext(
                $fileNameOnTmp, 
                $resId, 
                $indexFileDirectory, 
                $tmpDir,
                $args['zendIndex']
            );
        } else {
            $resultOfConversion = $this->launchFulltext(
                $fileNameOnTmp, 
                $resId, 
                $indexFileDirectory, 
                $tmpDir
            );
        }
        
        if ($resultOfConversion['status'] <> '0') {
            $this->manageErrorOnDb($resTable, $resId, '-1');
            LogsController::executionTimeLog(
                $timestart, 
                '', 
                'debug', 
                '[TIMER] Convert_ProcessFulltextAbstract_Service::fulltext aucunContenuAIndexer'
            );
            return $resultOfConversion;
        }
        //find the target docserver
        $targetDs = $ManageDocservers->findTargetDs($collId, 'FULLTEXT');
        if (empty($targetDs)) {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => 'Ds of collection and ds type not found for fulltext:' 
                    . $collId . ' FULLTEXT',
            );
            $this->manageErrorOnDb($resTable, $resId, '-1');
            return $returnArray;
        }
        //copy the result on docserver
        $resultCopyDs = $ManageDocservers->copyResOnDS($fileNameOnTmp . '.txt', $targetDs);
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
            $resultCopyDs['value']['fileDestinationName'],
            $args['zendIndex']
        );
        if ($resultOfUpDb['status'] <> '0') {
            $this->manageErrorOnDb($resTable, $resId, '-1');
            return $resultOfUpDb;
        }

        unlink($fileNameOnTmp);
        unlink($fileNameOnTmp . '.txt');

        $returnArray = array(
            'status' => '0',
            'value' => '',
            'error' => '',
        );
        LogsController::executionTimeLog(
            $timestart, 
            '', 
            'debug', 
            '[TIMER] Convert_ProcessFulltextAbstract_Service::fulltext'
        );
        return $returnArray;
    }

    /**
     * Launch the fulltext process
     *
     * @param string $srcfile source file
     * @param string $tgtdir target dir
     * @param string $srcfmt source format
     * @return array $returnArray the result
     */
    private function launchFulltext(
        $srcfile, 
        $resId,
        $indexFileDirectory, 
        $tgtdir=false,
        $zendIndex=''
    ) {
        if (!empty($zendIndex)) {
            $return = $this->prepareIndexFullTextPdf(
                $srcfile, 
                $tgtdir, 
                $indexFileDirectory,
                $resId,
                $zendIndex
            );
        } else {
            $return = $this->prepareIndexFullTextPdf(
                $srcfile, 
                $tgtdir, 
                $indexFileDirectory,
                $resId
            );
        }
        
        if ($return === 0) {
            $returnArray = array(
                'status' => '0',
                'value' => '',
                'error' => '',
            );
            return $returnArray;
        } else {
            $returnArray = array(
                'status' => '1',
                'value' => '',
                'error' => $return . $output,
            );
            return $returnArray;
        }
    }

    /**
    * Read a txt file
    * @param  $file string path of the file to read
    * @return string contents of the file
    */
    private function readFileF($file)
    {
        $result = "";
        if (is_file($file)) {
            $fp = fopen($file, "r");
            $result = fread($fp, filesize($file));
            fclose($fp);
        }
        return $result;
    }

    private function prepareIndexFullTextPdf($pathToFile, $tmpDir, $indexFileDirectory, $resId, $zendIndex)
    {
        $timestart = microtime(true);
        if (is_file($pathToFile)) {
            $tmpFile = $tmpDir . basename($pathToFile) . ".txt";
            $timestart_fulltext = microtime(true);
            $resultExtraction = exec("pdftotext " . escapeshellarg($pathToFile)
                    . " " . escapeshellarg($tmpFile) 
                );
            LogsController::executionTimeLog($timestart_fulltext, '', 'debug', '[TIMER] Convert_ProcessFulltextAbstract_Service::prepareIndexFullTextPdf__exec');
            
            $fileContent = trim($this->readFileF($tmpFile));
            
            if (!empty($zendIndex)) {
                $result = $this->launchIndexFullTextWithZendIndex(
                        $fileContent, 
                        $indexFileDirectory, 
                        $resId, 
                        $zendIndex
                );
            } else {
                // TODO : will be done only by the batch convert in OnlyIndexes mode
                //$result = $this->launchIndexFullText($fileContent, $indexFileDirectory, $resId);
                $result = 0;
            }
            
        } else {
            $result = 'file not found ' . $pathToFile;
        }
        LogsController::executionTimeLog(
            $timestart, 
            '', 
            'debug', 
            '[TIMER] Convert_ProcessFulltextAbstract_Service::prepareIndexFullTextPdf'
        );
        return $result;
    }

    /**
    * Return zend index object for batch mode
    * @param  $indexFileDirectory string directory of the lucene index
    * @return zend index object
    */
    public function createZendIndexObject($tempIndexFileDirectory, $numberOfIndexes = 1000) 
    {
        //echo 'createZendIndexObject : ' . $numberOfIndexes . PHP_EOL;
        $func = new functions();
        $indexFileDirectory = (string) $tempIndexFileDirectory; 
        // with version 1.12, we need a string, not an XML element
        
        if (!is_dir($indexFileDirectory)) {
            $index = Zend_Search_Lucene::create($indexFileDirectory);
        } else {
            if ($func->isDirEmpty($indexFileDirectory)) {
                $index = Zend_Search_Lucene::create($indexFileDirectory);
            } else {
                $index = Zend_Search_Lucene::open($indexFileDirectory);
            }
        }
        $index->setFormatVersion(Zend_Search_Lucene::FORMAT_2_3); 
        // we set the lucene format to 2.3
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive()
        );

        //$index->MaxBufferedDocs();
        $index->setMaxBufferedDocs($numberOfIndexes);

        return $index;
    }

    /**
    * Commit the zend index at the end of the batch
    * @return nothing
    */
    public function commitZendIndex($index) 
    {
        //echo 'the commit' . PHP_EOL;
        $index->commit();
    }

    /**
    * Retrieve the text of a pdftext and launch the lucene engine
    * @param  $pathToFile string path of the file to index
    * @param  $indexFileDirectory string directory of the lucene index
    * @param  $id integer id of the document to index
    * @return integer user exit code is stored in fulltext_result column of the
    * document in "res_x"
    */
    private function launchIndexFullText($fileContent, $tempIndexFileDirectory, $Id) 
    {
        // $IndexFileDirectory is replace by tempIndexFileDirectory
        $func = new functions();
        $fileContent = $func->normalize($fileContent);
        $fileContent = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $fileContent);
        $indexFileDirectory = (string) $tempIndexFileDirectory; 
        // with version 1.12, we need a string, not an XML element
        $result = -1;
        if (strlen($fileContent) > 2) {
            if (!is_dir($indexFileDirectory)) {
                //$_ENV['logger']->write($indexFileDirectory . " not exists !", "ERROR", 2);
                $index = Zend_Search_Lucene::create($indexFileDirectory);
            } else {
                if ($func->isDirEmpty($indexFileDirectory)) {
                    //$_ENV['logger']->write($indexFileDirectory . " empty !");
                    $index = Zend_Search_Lucene::create($indexFileDirectory);
                } else {
                    $index = Zend_Search_Lucene::open($indexFileDirectory);
                }
            }
            $index->setFormatVersion(Zend_Search_Lucene::FORMAT_2_3); 
            // we set the lucene format to 2.3
            Zend_Search_Lucene_Analysis_Analyzer::setDefault(
                new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive()
            );
            // we need utf8 for accents
            $term = new Zend_Search_Lucene_Index_Term($Id, 'Id');
            foreach ($index->termDocs($term) as $id) {
                $index->delete($id);
            }
            //echo $fileContent;
            $doc = new Zend_Search_Lucene_Document();
            $doc->addField(Zend_Search_Lucene_Field::UnIndexed('Id', (integer) $Id));
            $doc->addField(Zend_Search_Lucene_Field::UnStored(
                'contents', $fileContent)
            );
            //$func->show_array($doc);
            $index->addDocument($doc);
            $index->commit();
            //$func->show_array($index);
            //$index->optimize();
            $result = 0;
        } else {
            $result = 1;
        }
        return $result;
    }

    /**
    * Retrieve the text of a pdftext and launch the lucene engine
    * @param  $pathToFile string path of the file to index
    * @param  $indexFileDirectory string directory of the lucene index
    * @param  $id integer id of the document to index
    * @return integer user exit code is stored in fulltext_result column of the
    * document in "res_x"
    */
    private function launchIndexFullTextWithZendIndex($fileContent, $tempIndexFileDirectory, $Id, $index) 
    {
        //echo 'launchIndexFullTextWithZendIndex' . PHP_EOL;
        // $IndexFileDirectory is replace by tempIndexFileDirectory
        $func = new functions();
        $fileContent = $func->normalize($fileContent);
        $fileContent = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $fileContent);

        // with version 1.12, we need a string, not an XML element
        $result = -1;
        if (strlen($fileContent) > 2) {
            try {
                // we need utf8 for accents
                $term = new Zend_Search_Lucene_Index_Term($Id, 'Id');
                foreach ($index->termDocs($term) as $id) {
                    $index->delete($id);
                }
                //echo $fileContent;
                $doc = new Zend_Search_Lucene_Document();
                $doc->addField(Zend_Search_Lucene_Field::UnIndexed('Id', (integer) $Id));
                $doc->addField(Zend_Search_Lucene_Field::UnStored(
                    'contents', $fileContent)
                );
                //$func->show_array($doc);
                $index->addDocument($doc);
                //$index->commit();
                //$func->show_array($index);
                //$index->optimize();
                $result = 0;
            } catch (Exception $e) {
                $result = $e->getMessage();
            }
            
        } else if (strlen($fileContent) >= 0){
            $result = 0;
        }
        return $result;
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
        $fileName,
        $zendIndex = ''
    ) {
        try {
            $docserver->path_template = str_replace(
                DIRECTORY_SEPARATOR, 
                '#', 
                $docserver->path_template
            );
            $path = str_replace($docserver->path_template, '', $path);
            $dbConv = new \Database($GLOBALS['configFile']);
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
                . " where res_id = ? and adr_type = 'TXT'";
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
                        'TXT'
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
                        'TXT'
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
                    "process fulltext done"
                )
            );

            $queryCpt = "select coalesce(custom_t15, '0') as custom_t15 from " . $resTable 
                . " where res_id = ?";
            $stmtCpt = $dbConv->query($queryCpt, array($resId));
            $rsCpt = $stmtCpt->fetchObject();
            $cptFullText = $rsCpt->custom_t15 + 1;

            if (!empty($zendIndex)) {
                $query = "update " . $resTable 
                    . " set fulltext_result = '1', is_multi_docservers = 'Y', custom_t15 = '" 
                    . $cptFullText . "' where "
                    . " res_id = ?";
            } else {
                $query = "update " . $resTable 
                    . " set fulltext_result = '0', is_multi_docservers = 'Y', custom_t15 = '" 
                    . $cptFullText . "' where "
                    . " res_id = ?";
            }
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
        $dbConv = new \Database($GLOBALS['configFile']);

        $queryCpt = "select coalesce(custom_t15, '0') as custom_t15 from " . $resTable 
                . " where res_id = ?";
        $stmtCpt = $dbConv->query($queryCpt, array($resId));
        $rsCpt = $stmtCpt->fetchObject();
        $cptFullText = $rsCpt->custom_t15 + 1;
        
        $query = "update " . $resTable 
            . " set fulltext_result = ?, custom_t15 = '" . $cptFullText . "' where "
            . " res_id = ?";
        $stmt = $dbConv->query(
            $query,
            array(
                $result,
                $resId
            )
        );
    }

    public static function optimizeLuceneIndex(array $args=[]){
        $timestart = microtime(true);
        // Prés-requis :
        self::checkRequired($args, ['collId']);
        self::checkString($args, ['collId']); 
        
        $collId = $args['collId'];

        $countColl = count($_SESSION['collections']);
        for ($i=0;$i<$countColl;$i++) {
            if ($_SESSION['collections'][$i]['id'] == $collId) {
                $path_to_lucene = $_SESSION['collections'][$i]['path_to_lucene_index'];
            }
        }

        if(!empty($path_to_lucene)){
            exec(
                'php '.$_SESSION['config']['corepath'] . 
                'modules/convert/optimizeLuceneIndex.php ' . 
                $path_to_lucene . ' ' . 
                $_SESSION['config']['corepath'] . ' > /dev/null 2>&1 &'
            );
        }
        LogsController::executionTimeLog(
            $timestart, 
            '', 
            'debug', 
            '[TIMER] Convert_ProcessFulltextAbstract_Service::optimizeLuceneIndex'
        );

        return true;
    }

}
