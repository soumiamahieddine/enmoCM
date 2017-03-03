<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Docserver Controller
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\DocserverModel;

class DocserverController
{

    public function getList(RequestInterface $request, ResponseInterface $response)
    {
        $obj = DocserverModel::getList();
        
        $datas = [
            $obj,
        ];
        
        return $response->withJson($datas);
    }

    public function getById(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (isset($aArgs['id'])) {
            $id = $aArgs['id'];
            $obj = DocserverModel::getById([
                'id' => $id
            ]);
        } else {

            return $response
                ->withStatus(500)
                ->withJson(['errors' => _ID . ' ' . _IS_EMPTY]);
        }
        
        $datas = [
            $obj,
        ];

        return $response->withJson($datas);
    }

    public function create(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $errors = [];

        $errors = $this->control($request, 'create');

        if (!empty($errors)) {

            return $response
                ->withStatus(500)
                ->withJson(['errors' => $errors]);
        }

        $aArgs = $request->getQueryParams();

        $return = DocserverModel::create($aArgs);

        if ($return) {
            $id = $aArgs['id'];
            $obj = DocserverModel::getById([
                'id' => $id
            ]);
        } else {

            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_CREATE]);
        }

        $datas = [
            $obj,
        ];

        return $response->withJson($datas);
    }

    public function update(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $errors = [];

        $errors = $this->control($request, 'update');

        if (!empty($errors)) {

            return $response
                ->withStatus(500)
                ->withJson(['errors' => $errors]);
        }

        $aArgs = $request->getQueryParams();

        $return = DocserverModel::update($aArgs);

        if ($return) {
            $id = $aArgs['id'];
            $obj = DocserverModel::getById([
                'id' => $id
            ]);
        } else {

            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_UPDATE]);
        }

        $datas = [
            $obj,
        ];

        return $response->withJson($datas);
    }

    public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (isset($aArgs['id'])) {
            $id = $aArgs['id'];
            $obj = DocserverModel::delete([
                'id' => $id
            ]);
        } else {
            
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_DELETE]);
        }
        
        $datas = [
            $obj,
        ];

        return $response->withJson($datas);
    }

    protected function control($request, $mode)
    {
        $errors = [];

        if($mode == 'update') {
            $obj = DocserverModel::getById([
                'id' => $request->getParam('id')
            ]);
            if (empty($obj)) {
                array_push(
                    $errors, 
                    _ID . ' ' . $request->getParam('id') . ' ' . _NOT_EXISTS
                );
            }
        }

        if (!Validator::notEmpty()->validate($request->getParam('id'))) {
            array_push($errors, _ID . ' ' . _IS_EMPTY);
        } elseif($mode == 'create') {
            $obj = DocserverModel::getById([
                'id' => $request->getParam('id')
            ]);
            if (!empty($obj)) {
                array_push(
                    $errors, 
                    _ID . ' ' . $obj[0]['id'] . ' ' . _ALREADY_EXISTS
                );
            }
        }

        if (!Validator::regex('/^[\w.-]*$/')->validate($request->getParam('id'))) {
            array_push($errors, _ID . ' ' . _NOT . ' ' . _VALID);
        }

        if (!Validator::notEmpty()->validate($request->getParam('label_status'))) {
            array_push($errors, _LABEL_STATUS . ' ' . _IS_EMPTY);
        }

        if (
            Validator::notEmpty()
                ->validate($request->getParam('is_system')) &&
            !Validator::contains('Y')
                ->validate($request->getParam('is_system')) &&
            !Validator::contains('N')
                ->validate($request->getParam('is_system'))
        ) {
            array_push($errors, _IS_SYSTEM . ' ' . _NOT . ' ' . _VALID);
        }

        if (
            Validator::notEmpty()
                ->validate($request->getParam('is_folder_status')) &&
            !Validator::contains('Y')
                ->validate($request->getParam('is_folder_status')) &&
            !Validator::contains('N')
                ->validate($request->getParam('is_folder_status'))
        ) {
            array_push($errors, _IS_FOLDER_STATUS . ' ' . _NOT . ' ' . _VALID);
        }

        if (
            Validator::notEmpty()
                ->validate($request->getParam('img_filename')) &&
            (!Validator::regex('/^[\w-.]+$/')
                ->validate($request->getParam('img_filename')) ||
            !Validator::length(null, 255)
                ->validate($request->getParam('img_filename')))
        ) {
            array_push($errors, _IMG_FILENAME . ' ' . _NOT . ' ' . _VALID);
        }

        if (
            Validator::notEmpty()
                ->validate($request->getParam('maarch_module')) &&
            !Validator::length(null, 255)
                ->validate($request->getParam('maarch_module'))
        ) {
            array_push($errors, _MAARCH_MODULE . ' ' . _NOT . ' ' . _VALID);
        }

        if (
            Validator::notEmpty()
                ->validate($request->getParam('can_be_searched')) &&
            !Validator::contains('Y')
                ->validate($request->getParam('can_be_searched')) &&
            !Validator::contains('N')
                ->validate($request->getParam('can_be_searched'))
        ) {
            array_push($errors, _CAN_BE_SEARCHED . ' ' . _NOT . ' ' . _VALID);
        }

        if (
            Validator::notEmpty()
                ->validate($request->getParam('can_be_modified')) &&
            !Validator::contains('Y')
                ->validate($request->getParam('can_be_modified')) &&
            !Validator::contains('N')
                ->validate($request->getParam('can_be_modified'))
        ) {
            array_push($errors, _CAN_BE_MODIFIED . ' ' . _NOT . ' ' . _VALID);
        }

        return $errors;
    }

    /**
     * Get docservers to insert a new doc.
     * Can return null if no corresponding object.
     * @param  $coll_id  string Collection identifier
     * @return docservers
     */
    public function getDocserverToInsert($aArgs)
    {
        if (isset($aArgs['collId'])) {
            $collId = $aArgs['collId'];
            $obj = DocserverModel::getDocserverToInsert([
                'collId' => $collId
            ]);
        } else {

            return ['errors' => 'collId ' . _EMPTY];
        }

        $datas = $obj;

        return $datas;
    }

    /**
    * Checks the size of the docserver plus a new file to see
    * if there is enough disk space
    *
    * @param  $docserver docservers object
    * @param  $filesize integer File size
    * @return integer New docserver size or 0 if not enough disk space available
    */
    public function checkSize($aArgs)
    {

        $newDsSize = $aArgs['docserver']['actual_size_number'] + $aArgs['filesize'];

        if (empty($aArgs['docserver']['actual_size_number'])) {
            $datas = [
                    'errors' => 'actual_size_number' . _EMPTY,
            ];

            return $datas;
        }

        if (empty($aArgs['filesize'])) {
            $datas = [
                    'errors' => 'filesize' . _EMPTY,
            ];

            return $datas;
        }
        
        if (
            $aArgs['docserver']['size_limit_number'] > 0 &&
            $newDsSize >= $aArgs['docserver']['size_limit_number']
        ) {
            $datas = [
                'newDsSize' => 0,
            ];
        } else {
            $datas = [
                'newDsSize' => $newDsSize,
            ];
        }

        return $datas;
    }

    /**
    * Sets the size of the docserver
    * @param $docserver docservers object
    * @param $newSize integer New size of the docserver
    */
    public function setSize($aArgs)
    {
        if (empty($aArgs['docserver_id'])) {

            return ['errors' => 'docserver_id ' . _EMPTY];
        }

        if (empty($aArgs['actual_size_number'])) {

            return ['errors' => 'actual_size_number ' . _EMPTY];
        }

        //$obj = DocserverModel::setSize($aArgs);
        $return = DocserverModel::update($aArgs);
        
        $datas = [
            'setSize' => true,
        ];

        return $datas;
    }


    /**
    * Calculates the next file name in the docserver
    * @param $pathOnDocserver docservers path
    * @return array Contains 3 items :
    * subdirectory path and new filename and error
    */
    public function getNextFileNameInDocserver($aArgs)
    {
        if (empty($aArgs['pathOnDocserver'])) {
            $datas = [
                    'errors' => 'pathOnDocserver ' . _EMPTY,
            ];

            return $datas;
        }

        if (!is_dir($aArgs['pathOnDocserver'])) {
            $datas = [
                    'errors' => 'pathOnDocserver ' . _NOT_EXISTS,
            ];

            return $datas;
        }

        $pathOnDocserver = $aArgs['pathOnDocserver'];

        $dsTools = new \Core\Controllers\DocserverToolsController();

        umask(0022);
        //Scans the docserver path
        $fileTab = scandir($pathOnDocserver);
        //Removes . and .. lines
        array_shift($fileTab);
        array_shift($fileTab);

        if (file_exists($pathOnDocserver . DIRECTORY_SEPARATOR . 'package_information')) {
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
                $datas = [
                    'errors' => 'Pb to create directory on the docserver:'
                        . $pathOnDocserver,
                ];

                return $datas;
            } else {
                $dsTools->setRights(
                    ['path' => $pathOnDocserver . '0001' . DIRECTORY_SEPARATOR]
                );
                $destinationDir = $pathOnDocserver . '0001'
                                . DIRECTORY_SEPARATOR;
                $fileDestinationName = '0001';
                $fileDestinationName = $fileDestinationName . '_' . mt_rand();
                $datas = [
                    'destinationDir' => $destinationDir,
                    'fileDestinationName' => $fileDestinationName,
                ];

                return $datas;
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
                    $datas = [
                        'errors' => 'Pb to create directory on the docserver:'
                        . $pathOnDocserver
                        . str_pad($newDir, 4, '0', STR_PAD_LEFT),
                    ];

                    return $datas;
                } else {
                    $dsTools->setRights(
                        [
                            'path' => $pathOnDocserver
                                . str_pad($newDir, 4, '0', STR_PAD_LEFT)
                                . DIRECTORY_SEPARATOR
                        ]
                    );
                    $destinationDir = $pathOnDocserver
                        . str_pad($newDir, 4, '0', STR_PAD_LEFT)
                        . DIRECTORY_SEPARATOR;
                    $fileDestinationName = '0001';
                    $fileDestinationName = $fileDestinationName . '_' . mt_rand();
                    $datas = [
                        'destinationDir' => $destinationDir,
                        'fileDestinationName' => $fileDestinationName,
                    ];

                    return $datas;
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
                $datas = [
                    'destinationDir' => $destinationDir,
                    'fileDestinationName' => $fileDestinationName,
                ];

                return $datas;
            }
        }
    }

    /**
     * Store a new doc in a docserver.
     * @param   $collId collection resource
     * @param   $fileInfos infos of the doc to store, contains :
     *          tmpDir : path to tmp directory
     *          size : size of the doc
     *          format : format of the doc
     *          tmpFileName : file name of the doc in Maarch tmp directory
     * @return  array of docserver data for res_x else return error
     */
    public function storeResourceOnDocserver($aArgs)
    {
        if (empty($aArgs['collId'])) {
            
            return ['errors' => 'collId ' . _EMPTY];
        }

        if (empty($aArgs['fileInfos'])) {
            
            return ['errors' => 'fileInfos ' . _EMPTY];
        }

        if (empty($aArgs['fileInfos']['tmpDir'])) {
            
            return ['errors' => 'fileInfos.tmpDir ' . _EMPTY];
        }

        if (empty($aArgs['fileInfos']['size'])) {
            
            return ['errors' => 'fileInfos.size ' . _EMPTY];
        }

        if (empty($aArgs['fileInfos']['format'])) {
            
            return ['errors' => 'fileInfos.format ' . _EMPTY];
        }

        if (empty($aArgs['fileInfos']['tmpFileName'])) {
            
            return ['errors' => 'fileInfos.tmpFileName ' . _EMPTY];
        }

        if (!is_dir($aArgs['fileInfos']['tmpDir'])) {
            
            return ['errors' => 'fileInfos.tmpDir ' . _NOT_EXISTS];
        }

        if (!file_exists($aArgs['fileInfos']['tmpDir'] . $aArgs['fileInfos']['tmpFileName'])) {
            
            return ['errors' => 'fileInfos.tmpDir fileInfos.tmpFileName' . _NOT_EXISTS];
        }

        $collId = $aArgs['collId'];
        $fileInfos = $aArgs['fileInfos'];
        $size = $aArgs['fileInfos']['size'];
        $tmpDir = $aArgs['fileInfos']['tmpDir'];

        $dsTools = new \Core\Controllers\DocserverToolsController();

        $docserver = $this->getDocserverToInsert(['collId' => $collId]);
        $docserver = $docserver[0];

        $tmpSourceCopy = '';
        
        if (empty($docserver)) {
            
            return [
                'errors' => _DOCSERVER_ERROR . ' : '
                    . _NO_AVAILABLE_DOCSERVER . ' .  ' . _MORE_INFOS 
            ];
        }

        $newSize = $this->checkSize(
            [
                'docserver' => $docserver, 
                'filesize' => $size,
            ]
        );

        if ($newSize['newDsSize'] == 0) {

            return [
                'errors' => _DOCSERVER_ERROR . ' : '
                . _NOT_ENOUGH_DISK_SPACE . ' .  ' . _MORE_INFOS 
            ];
        }

        if ($tmpDir == '') {
            $tmp = $_SESSION['config']['tmppath'];
        } else {
            $tmp = $tmpDir;
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
        $pathOnDocserver = $dsTools->createPathOnDocServer(
            ['path' => $docserver['path_template']]
        );

        $docinfo = $this->getNextFileNameInDocserver(
            ['pathOnDocserver' => $pathOnDocserver['createPathOnDocServer']['destinationDir']]
        );

        if ($docinfo['errors'] <> '') {

            return ['errors' => _FILE_SEND_ERROR];
        }

        $docserverTypeControler = new \Core\Models\DocserverTypeModel();
        $docserverTypeObject = $docserverTypeControler->getById(
            ['id' => $docserver['docserver_type_id']]
        );

        $docserverTypeObject = $docserverTypeObject[0];

        $pathInfoTmpSrc = pathinfo($tmpSourceCopy);

        $docinfo['fileDestinationName'] .= '.'
            . strtolower($pathInfoTmpSrc['extension']);

        $copyResult = $dsTools->copyOnDocserver(
            [
                'sourceFilePath'             => $tmpSourceCopy,
                'destinationDir'             => $docinfo['destinationDir'],
                'fileDestinationName'        => $docinfo['fileDestinationName'],
                'docserverSourceFingerprint' => $docserverTypeObject['fingerprint_mode'],
            ]
        );

        if (isset($copyResult['errors']) && $copyResult['errors'] <> '') {

            return ['errors' => $copyResult['errors']];
        }

        $destinationDir = $copyResult['copyOnDocserver']['destinationDir'];
        $fileDestinationName = $copyResult['copyOnDocserver']['fileDestinationName'];
        
        $destinationDir = substr(
            $destinationDir,
            strlen($docserver['path_template'])
        ) . DIRECTORY_SEPARATOR;
        
        $destinationDir = str_replace(
            DIRECTORY_SEPARATOR,
            '#',
            $destinationDir
        );

        $this->setSize(
            [
                'docserver_id' => $docserver['docserver_id'], 
                'actual_size_number' => $newSize['newDsSize']
            ]
        );

        $datas = [
            'path_template' => $docserver['path_template'],
            'destination_dir' => $destinationDir,
            'docserver_id' => $docserver['docserver_id'],
            'file_destination_name' => $fileDestinationName,
        ];

        return $datas;
    }

}