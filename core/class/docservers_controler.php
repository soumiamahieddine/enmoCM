<?php


/*
*   Copyright 2008-2011 Maarch
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
*   along with Maarch Framework. If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Contains the docservers_controler Object
* (herits of the BaseObject class)
*
* @file
* @author Luc KEULEYAN - BULL
* @author Laurent Giovannoni
* @date $date$
* @version $Revision$
* @ingroup core
*/

//Loads the required class
try {
    require_once 'core/class/class_request.php';
    require_once 'core/class/docservers.php';
    require_once 'core/docservers_tools.php';
    require_once 'core/core_tables.php';
    require_once 'core/class/ObjectControlerAbstract.php';
    require_once 'core/class/ObjectControlerIF.php';
    require_once 'core/class/class_security.php';
    require_once 'core/class/class_resource.php';
    require_once 'core/class/class_history.php';
} catch (Exception $e) {
    functions::xecho($e->getMessage()) . ' // ';
}

/**
 * Class for controling docservers objects from database
 */
class docservers_controler
    extends ObjectControler
    implements ObjectControlerIF
{

    /**
     * Save given object in database:
     * - make an update if object already exists,
     * - make an insert if new object.
     * Return updated object.
     * @param docservers $docservers
     * @return array
     */
    public function save($docserver, $mode='')
    {
    }


    /**
     * Get docservers with given id.
     * Can return null if no corresponding object.
     * @param $id Id of docservers to get
     * @return docservers
     */
    public function get($docserver_id)
    {
        //var_dump($docserver_id);
        $this->set_foolish_ids(array('docserver_id'));
        $this->set_specific_id('docserver_id');
        $docserver = $this->advanced_get($docserver_id, _DOCSERVERS_TABLE_NAME);
        //var_dump($docserver);
        if (get_class($docserver) <> 'docservers') {
            return null;
        } else {
            //var_dump($docserver);
            return $docserver;
        }
    }

    public function delete($args)
    {
    }


    /**
     * get docservers with given id for a ws.
     * Can return null if no corresponding object.
     * @param $docserver_id of docservers to send
     * @return docservers
     */
    public function getWs($docserver_id)
    {
        $this->set_foolish_ids(array('docserver_id'));
        $this->set_specific_id('docserver_id');
        $docserver = $this->advanced_get($docserver_id, _DOCSERVERS_TABLE_NAME);
        if (get_class($docserver) <> 'docservers') {
            return null;
        } else {
            $docserver = $docserver->getArray();
            return $docserver;
        }
    }

    /**
    * Disables a given docservers
    *
    * @param  $docserver docservers object
    * @return bool true if the disabling is complete, false otherwise
    */
    public function disable($docserver)
    {
        if ($docserver->docserver_id <> 'TEMPLATES') {
            $control = array();
            if (!isset($docserver) || empty($docserver)) {
                $control = array(
                    'status' => 'ko',
                    'value' => '',
                    'error' => _DOCSERVER_EMPTY,
                );
                return $control;
            }
            $docserver = $this->isADocserver($docserver);
            $this->set_foolish_ids(array('docserver_id'));
            $this->set_specific_id('docserver_id');
            if ($this->advanced_disable($docserver)) {
                $control = array(
                    'status' => 'ok',
                    'value' => $docserver->docserver_id,
                );
                if ($_SESSION['history']['docserversban'] == 'true') {
                    $history = new history();
                    $history->add(
                        _DOCSERVERS_TABLE_NAME,
                        $docserver->docserver_id,
                        'BAN','docserversban',
                        _DOCSERVER_DISABLED . ' : ' . $docserver->docserver_id,
                        $_SESSION['config']['databasetype']
                    );
                }
            } else {
                $control = array(
                    'status' => 'ko',
                    'value' => '',
                    'error' => _PB_WITH_DOCSERVER,
                );
            }
        } else {
            $control = array(
                'status' => 'ko',
                'value' => '',
                'error' => _CANNOT_SUSPEND_DOCSERVER . ' ' . $docserver->docserver_id,
            );
        }
        return $control;
    }

    /**
    * Enables a given docserver
    *
    * @param  $docserver docservers object
    * @return bool true if the enabling is complete, false otherwise
    */
    public function enable($docserver)
    {
    }

    /**
    * Fill a docserver object with an object if it's not a docserver
    *
    * @param  $object ws docserver object
    * @return object docservers
    */
    private function isADocserver($object)
    {
        if (get_class($object) <> 'docservers') {
            $func = new functions();
            $docserverObject = new docservers();
            $array = array();
            $array = $func->object2array($object);
            foreach (array_keys($array) as $key) {
                $docserverObject->{$key} = $array[$key];
            }
            return $docserverObject;
        } else {
            return $object;
        }
    }

    public function getDocserverToInsert($collId, $typeId = 'DOC')
    {
        if ($collId == 'templates') {
            $typeId = 'TEMPLATES';
        }

        $db = new Database();
        $query = "select docserver_id from docservers where is_readonly = 'N' and coll_id = ?  and docserver_type_id = ?";
        $stmt = $db->query($query, [$collId, $typeId]);
        $queryResult = $stmt->fetchObject();
        if ($queryResult->docserver_id <> '') {
            $docserver = $this->get($queryResult->docserver_id);
            if (isset($docserver->docserver_id)) {
                return $docserver;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Store a new doc in a docserver.
     * @param   $collId string collection resource
     * @param   $fileInfos array , contains :
     *          tmpDir : path to tmp directory
     *          size : size of the doc
     *          format : format of the doc
     *          tmpFileName : file name of the doc in Maarch tmp directory
     * @return  array of docserver data for res_x else return error
     */
    public function storeResourceOnDocserver($collId, $fileInfos)
    {
        $docserver = $this->getDocserverToInsert($collId);
        $tmpSourceCopy = '';
        $func = new functions();
        if (empty($docserver)) {
            $storeInfos = array(
                'error' => _DOCSERVER_ERROR . ' : '
                . _NO_AVAILABLE_DOCSERVER . ' .  ' . _MORE_INFOS . '.',
            );
            return $storeInfos;
        }
        $newSize = $this->checkSize($docserver, $fileInfos['size']);
        if ($newSize == 0) {
            $storeInfos = array(
                'error' => _DOCSERVER_ERROR . ' : '
                . _NOT_ENOUGH_DISK_SPACE . ' .  ' . _MORE_INFOS . '.',
            );
            return $storeInfos;
        }
        if ($fileInfos['tmpDir'] == '') {
            $tmp = $_SESSION['config']['tmppath'];
        } else {
            $tmp = $fileInfos['tmpDir'];
        }
        $d = dir($tmp);
        $pathTmp = $d->path;
        while ($entry = $d->read()) {
            if ($entry == $fileInfos['tmpFileName']) {
                $tmpSourceCopy = $pathTmp . $entry;
                $theFile = $entry;
                break;
            }
        }
        $d->close();
        $pathOnDocserver = array();
        $pathOnDocserver = Ds_createPathOnDocServer(
            $docserver->path_template
        );
        $docinfo = $this->getNextFileNameInDocserver(
            $pathOnDocserver['destinationDir']
        );
        if ($docinfo['error'] <> '') {
             $_SESSION['error'] = _FILE_SEND_ERROR . '. '._TRY_AGAIN . '. '
                                . _MORE_INFOS . ' : <a href=\'mailto:'
                                . $_SESSION['config']['adminmail'] . '\'>'
                                . $_SESSION['config']['adminname'] . '</a>';
        }
        require_once('core' . DIRECTORY_SEPARATOR . 'class'
            . DIRECTORY_SEPARATOR . 'docserver_types_controler.php');
        $docserverTypeControler = new docserver_types_controler();
        $docserverTypeObject = $docserverTypeControler->get(
            $docserver->docserver_type_id
        );
        $docinfo['fileDestinationName'] .= '.'
            . strtolower($func->extractFileExt($tmpSourceCopy));
        $copyResultArray = Ds_copyOnDocserver(
            $tmpSourceCopy,
            $docinfo,
            $docserverTypeObject->fingerprint_mode
        );

        if (isset($copyResultArray['error']) && $copyResultArray['error'] <> '') {
            //second chance
            $docinfo = array();
            $copyResultArray = array();
            $docinfo = $this->getNextFileNameInDocserver(
                $pathOnDocserver['destinationDir']
            );
            if ($docinfo['error'] <> '') {
                 $_SESSION['error'] = _FILE_SEND_ERROR . '. '._TRY_AGAIN . '. '
                                    . _MORE_INFOS . ' : <a href=\'mailto:'
                                    . $_SESSION['config']['adminmail'] . '\'>'
                                    . $_SESSION['config']['adminname'] . '</a>';
            }
            $docinfo['fileDestinationName'] .= '.'
                . strtolower($func->extractFileExt($tmpSourceCopy));
            $copyResultArray = Ds_copyOnDocserver(
                $tmpSourceCopy,
                $docinfo,
                $docserverTypeObject->fingerprint_mode
            );
            if (isset($copyResultArray['error']) && $copyResultArray['error'] <> '') {
                $storeInfos = array('error' => $copyResultArray['error']);
                return $storeInfos;
            }
        }
        $destinationDir = $copyResultArray['destinationDir'];
        $fileDestinationName = $copyResultArray['fileDestinationName'];
        $destinationDir = substr(
            $destinationDir,
            strlen($docserver->path_template)
        ) . DIRECTORY_SEPARATOR;
        $destinationDir = str_replace(
            DIRECTORY_SEPARATOR,
            '#',
            $destinationDir
        );
        $this->setSize($docserver, $newSize);
        $storeInfos = array(
            'path_template' => $docserver->path_template,
            'destination_dir' => $destinationDir,
            'docserver_id' => $docserver->docserver_id,
            'file_destination_name' => $fileDestinationName,
        );
        return $storeInfos;
    }

    /**
    * Checks the size of the docserver plus a new file to see
    * if there is enough disk space
    *
    * @param  $docserver docservers object
    * @param  $filesize integer File size
    * @return integer New docserver size or 0 if not enough disk space available
    */
    public function checkSize($docserver, $filesize)
    {
        $new_docserver_size = $docserver->actual_size_number + $filesize;
        if ($docserver->size_limit_number > 0
            && $new_docserver_size >= $docserver->size_limit_number
        ) {
            return 0;
        } else {
            return $new_docserver_size;
        }
    }

    /**
    * Calculates the next file name in the docserver
    * @param $pathOnDocserver docservers path
    * @return array Contains 3 items :
    * subdirectory path and new filename and error
    */
    public function getNextFileNameInDocserver($pathOnDocserver)
    {
        umask(0022);
        //Scans the docserver path
        $fileTab = scandir($pathOnDocserver);
        //Removes . and .. lines
        array_shift($fileTab);
        array_shift($fileTab);

        if (file_exists(
            $pathOnDocserver . DIRECTORY_SEPARATOR . 'package_information'
        )
        ) {
            unset($fileTab[array_search('package_information', $fileTab)]);
        }
        if (is_dir($pathOnDocserver . DIRECTORY_SEPARATOR . 'BATCH')) {
            unset($fileTab[array_search('BATCH', $fileTab)]);
        }
        $nbFiles = count($fileTab);
        //Docserver is empty
        if ($nbFiles == 0 ) {
            //Creates the directory
            if (!mkdir($pathOnDocserver . '0001', 0770)) {
                return array(
                    'destinationDir' => '',
                    'fileDestinationName' => '',
                    'error' => 'Pb to create directory on the docserver:'
                    . $pathOnDocserver,
                );
            } else {
                Ds_setRights($pathOnDocserver . '0001' . DIRECTORY_SEPARATOR);
                $destinationDir = $pathOnDocserver . '0001'
                                . DIRECTORY_SEPARATOR;
                $fileDestinationName = '0001';
                $fileDestinationName = $fileDestinationName . '_' . mt_rand();
                return array(
                    'destinationDir' => $destinationDir,
                    'fileDestinationName' => $fileDestinationName,
                    'error' => '',
                );
            }
        } else {
            //Gets next usable subdirectory in the docserver
            $destinationDir = $pathOnDocserver
                . str_pad(
                    count($fileTab),
                    4,
                    '0',
                    STR_PAD_LEFT
                )
                . DIRECTORY_SEPARATOR;
            $fileTabBis = scandir(
                $pathOnDocserver
                . strval(str_pad(count($fileTab), 4, '0', STR_PAD_LEFT))
            );
            //Removes . and .. lines
            array_shift($fileTabBis);
            array_shift($fileTabBis);
            $nbFilesBis = count($fileTabBis);
            //If number of files => 1000 then creates a new subdirectory
            if ($nbFilesBis >= 1000 ) {
                $newDir = ($nbFiles) + 1;
                if (!mkdir(
                    $pathOnDocserver
                    . str_pad($newDir, 4, '0', STR_PAD_LEFT), 0770
                )
                ) {
                    return array(
                        'destinationDir' => '',
                        'fileDestinationName' => '',
                        'error' => 'Pb to create directory on the docserver:'
                        . $pathOnDocserver
                        . str_pad($newDir, 4, '0', STR_PAD_LEFT),
                    );
                } else {
                    Ds_setRights(
                        $pathOnDocserver
                        . str_pad($newDir, 4, '0', STR_PAD_LEFT)
                        . DIRECTORY_SEPARATOR
                    );
                    $destinationDir = $pathOnDocserver
                        . str_pad($newDir, 4, '0', STR_PAD_LEFT)
                        . DIRECTORY_SEPARATOR;
                    $fileDestinationName = '0001';
                    $fileDestinationName = $fileDestinationName . '_' . mt_rand();
                    return array(
                        'destinationDir' => $destinationDir,
                        'fileDestinationName' => $fileDestinationName,
                        'error' => '',
                    );
                }
            } else {
                //Docserver contains less than 1000 files
                $newFileName = $nbFilesBis + 1;
                $greater = $newFileName;
                for ($n = 0;$n < count($fileTabBis);$n++) {
                    $currentFileName = array();
                    $currentFileName = explode('.', $fileTabBis[$n]);
                    if ((int) $greater <= (int) $currentFileName[0]) {
                        if ((int) $greater == (int) $currentFileName[0]) {
                            $greater ++;
                        } else {
                            //$greater < current
                            $greater = (int) $currentFileName[0] + 1;
                        }
                    }
                }
                $fileDestinationName = str_pad($greater, 4, '0', STR_PAD_LEFT);
                $fileDestinationName = $fileDestinationName . '_' . mt_rand();
                return array(
                    'destinationDir' => $destinationDir,
                    'fileDestinationName' => $fileDestinationName,
                    'error' => '',
                );
            }
        }
    }

    /**
    * Sets the size of the docserver
    * @param $docserver docservers object
    * @param $newSize integer New size of the docserver
    */
    public function setSize($docserver, $newSize)
    {
        $db = new Database();
        $stmt = $db->query(
            "update " . _DOCSERVERS_TABLE_NAME
            . " set actual_size_number = ? where docserver_id = ?",
            array(
                $newSize,
                $docserver->docserver_id
            )
        );
        
        return $newSize;
    }

    /**
     * View the resource, returns the content of the resource
     * @param   bigint $gedId id of th resource
     * @param   string $tableName name of the res table
     * @param   string $adrTable name of the res address table
     * @return  array of elements to view the resource :
     *          status, mime_type, extension,
     *          file_content, tmp_path, file_path, called_by_ws error
     */
    public function viewResource(
        $gedId,
        $tableName,
        $adrTable,
        $calledByWS=false
    ) {
        $history = new history();
        $coreTools = new core_tools();
        //$whereClause = '';
        //THE TEST HAVE TO BE DONE BEFORE !!!
        $whereClause = ' and 1=1';
/*
        if (isset($_SESSION['origin']) && ($_SESSION['origin'] <> 'basket'
            && $_SESSION['origin'] <> 'workflow')
        ) {
            if (isset(
                $_SESSION['user']['security']
                [$_SESSION['collection_id_choice']]
                )
            ) {
                $whereClause = ' and( '
                             . $_SESSION['user']['security']
                             [$_SESSION['collection_id_choice']]['DOC']['where']
                             . ' ) ';
            } else {
                $whereClause = ' and 1=1';
            }
        } else {
            $whereClause = ' and 1=1';
        }
*/
        $adr = array();
        $resource = new resource();
        $adr = $resource->getResourceAdr(
            $tableName,
            $gedId,
            $whereClause,
            $adrTable
        );
        //$coreTools->show_array($adr);
        if ($adr['status'] == 'ko') {
            $result = array(
                'status' => 'ko',
                'mime_type' => '',
                'ext' => '',
                'file_content' => '',
                'tmp_path' => '',
                'file_path' => '',
                'called_by_ws' => $calledByWS,
                'error' => _NO_RIGHT_ON_RESOURCE_OR_RESOURCE_NOT_EXISTS,
            );
            $history->add(
                $tableName,
                $gedId,
                'ERR','docserverserr',
                _NO_RIGHT_ON_RESOURCE_OR_RESOURCE_NOT_EXISTS,
                $_SESSION['config']['databasetype']
            );
        } else {
            require_once('core' . DIRECTORY_SEPARATOR . 'class'
                . DIRECTORY_SEPARATOR . 'docserver_types_controler.php');
            $docserverTypeControler = new docserver_types_controler();
            $concatError = '';
            //failover management
            for (
                $cptDocserver = 0;
                $cptDocserver < count($adr[0]);
                $cptDocserver++
            ) {
                $error = false;
                //retrieve infos of the docserver
                $fingerprintFromDb = $adr[0][$cptDocserver]['fingerprint'];
                $format = $adr[0][$cptDocserver]['format'];
                $docserverObject = $this->get(
                    $adr[0][$cptDocserver]['docserver_id']
                );
                $docserver = $docserverObject->path_template;
                $file = $docserver . $adr[0][$cptDocserver]['path']
                      . $adr[0][$cptDocserver]['filename'];
                $file = str_replace('#', DIRECTORY_SEPARATOR, $file);
                $docserverTypeObject = $docserverTypeControler->get(
                    $docserverObject->docserver_type_id
                );
                if (!file_exists($file) || empty($adr[0][$cptDocserver]['path']) || empty($adr[0][$cptDocserver]['filename'])) {
                    
                    $concatError .= _FILE_NOT_EXISTS_ON_THE_SERVER . ' : '
                                  . $file . '||';
                    $history->add(
                        $tableName, $gedId, 'ERR','docserverserr',
                        _FAILOVER . ' ' . _DOCSERVERS . ' '
                        . $adr[0][$cptDocserver]['docserver_id'] . ':'
                        . _FILE_NOT_EXISTS_ON_THE_SERVER . ' : '
                        . $file, $_SESSION['config']['databasetype']
                    );
                } else {
                    $fingerprintFromDocserver = Ds_doFingerprint(
                        $file, $docserverTypeObject->fingerprint_mode
                    );
                    $adrToExtract = array();
                    $adrToExtract = $adr[0][$cptDocserver];
                    $adrToExtract['path_to_file'] = $file;
                    //retrieve infos of the docserver type
                    require_once('core' . DIRECTORY_SEPARATOR . 'class'
                    . DIRECTORY_SEPARATOR . 'docserver_types_controler.php');
                    $docserverTypeControler = new docserver_types_controler();
                    $docserverTypeObject = $docserverTypeControler->get(
                        $docserverObject->docserver_type_id
                    );
                    //manage compressed resource
                    $mimeType = Ds_getMimeType(
                        $adrToExtract['path_to_file']
                    );
                    //manage view of the file
                    $use_tiny_mce = false;
                    if (strtolower($format) == 'maarch'
                        && $coreTools->is_module_loaded('templates')
                    ) {
                        $mode = 'content';
                        $type_state = true;
                        $use_tiny_mce = true;
                        $mimeType = 'application/maarch';
                    } else {
                        require_once 'core/docservers_tools.php';
                        $arrayIsAllowed = array();
                        $arrayIsAllowed = Ds_isFileTypeAllowed($file);
                        $type_state = $arrayIsAllowed['status'];
                    }
                    //if fingerprint from db = 0 we do not control fingerprint
                    if ($fingerprintFromDb == '0'
                        || ($fingerprintFromDb == $fingerprintFromDocserver)
                        || $docserverTypeObject->fingerprint_mode == 'NONE'
                    ) {
                        if ($type_state <> false) {
                            if ($_SESSION['history']['resview'] == 'true') {
                                require_once(
                                    'core' . DIRECTORY_SEPARATOR
                                    . 'class' . DIRECTORY_SEPARATOR
                                    . 'class_history.php'
                                );
                                $history->add(
                                    $tableName, $gedId, 'VIEW','resview',
                                    _VIEW_DOC_NUM . $gedId,
                                    $_SESSION['config']['databasetype'],
                                    'indexing_searching'
                                );
                            }
                            //count number of viewed in listinstance for
                            //the user
                            if ($coreTools->is_module_loaded('entities')
                                && $coreTools->is_module_loaded('basket')
                            ) {
                                require_once(
                                    'modules' . DIRECTORY_SEPARATOR
                                    . 'entities' . DIRECTORY_SEPARATOR
                                    . 'class' . DIRECTORY_SEPARATOR
                                    . 'class_manage_entities.php'
                                );
                                $ent = new entity();
                                $ent->increaseListinstanceViewed($gedId);
                            }
                            $encodedContent = '';
                            if (file_exists($file) && !$error) {
                                if ($calledByWS) {
                                    $content = '';
                                    /*$content = file_get_contents(
                                        $file, FILE_BINARY
                                    );*/
                                    $handle = fopen($file, 'r');
                                    if ($handle) {
                                        while (!feof($handle)) {
                                            $content .= fgets($handle, 4096);
                                        }
                                        fclose($handle);
                                    }
                                    $encodedContent = base64_encode($content);
                                } else {
                                    $fileNameOnTmp = 'tmp_file_' . rand()
                                        . '.' . strtolower($format);
                                    $filePathOnTmp = $_SESSION['config']
                                        ['tmppath'] . DIRECTORY_SEPARATOR
                                        . $fileNameOnTmp;
                                    copy($file, $filePathOnTmp);
                                }
                                $result = array(
                                    'status' => 'ok',
                                    'mime_type' => $mimeType,
                                    'ext' => $format,
                                    'file_content' => $encodedContent,
                                    'tmp_path' => $_SESSION['config']
                                    ['tmppath'],
                                    'file_path' => $filePathOnTmp,
                                    'called_by_ws' => $calledByWS,
                                    'error' => '',
                                );
                                if (isset($extract)
                                    && file_exists($extract['tmpArchive'])
                                ) {
                                    Ds_washTmp($extract['tmpArchive']);
                                }
                                return $result;
                            } else {
                                $concatError .= _FILE_NOT_EXISTS . '||';
                                $history->add(
                                    $tableName, $gedId, 'ERR','docserverserr',
                                    _FAILOVER . ' ' . _DOCSERVERS . ' '
                                    . $adr[0][$cptDocserver]['docserver_id']
                                    . ':' . _FILE_NOT_EXISTS,
                                    $_SESSION['config']['databasetype']
                                );
                            }
                        } else {
                            $concatError .= strtoupper(_WRONG_FILE_TYPE) . ' (extension => '.strtoupper($format).', mime_type => '.$mimeType.') ||';
                            $history->add(
                                $tableName, $gedId, 'ERR','docserverserr',
                                _FAILOVER . ' ' . _DOCSERVERS . ' '
                                . $adr[0][$cptDocserver]['docserver_id'] . ':'
                                . _WRONG_FILE_TYPE,
                                $_SESSION['config']['databasetype']
                            );
                        }
                    } else {
                        $concatError .= _PB_WITH_FINGERPRINT_OF_DOCUMENT . '||';
                        $history->add(
                            $tableName, $gedId, 'ERR','docserverserr',
                            _FAILOVER . ' ' . _DOCSERVERS . ' '
                            . $adr[0][$cptDocserver]['docserver_id'] . ':'
                            . _PB_WITH_FINGERPRINT_OF_DOCUMENT,
                            $_SESSION['config']['databasetype']
                        );
                    }
                    if (file_exists($extract['tmpArchive'])) {
                        Ds_washTmp($extract['tmpArchive']);
                    }
                }
            }
        }
        //if errors :
        $result = array(
            'status' => 'ko',
            'mime_type' => '',
            'ext' => '',
            'file_content' => '',
            'tmp_path' => '',
            'file_path' => '',
            'called_by_ws' => $calledByWS,
            'error' => $concatError,
        );
        return $result;
    }
}

