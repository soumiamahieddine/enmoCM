<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Docserver tools Controller
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\DocserverModel;

class DocserverToolsController
{

    /**
     * Compute the path in the docserver for a batch
     * @param $docServer docservers path
     * @return  array Contains 2 items : subdirectory path and error
     */
    public function createPathOnDocServer($aArgs)
    {
        if (empty($aArgs['path'])) {
            $datas = [
                    'errors' => 'path ' . _EMPTY,
            ];

            return $datas;
        }

        if (!is_dir($aArgs['path'])) {
            $datas = [
                    'errors' => 'path ' . _NOT_EXISTS,
            ];

            return $datas;
        }

        $pathOnDocserver = $aArgs['path'];

        error_reporting(0);

        umask(0022);

        if (!is_dir($pathOnDocserver . date('Y') . DIRECTORY_SEPARATOR)) {
            mkdir($pathOnDocserver . date('Y') . DIRECTORY_SEPARATOR, 0770);
            $this->setRights(['path' => $pathOnDocserver . date('Y') . DIRECTORY_SEPARATOR]);
        }
        if (!is_dir(
            $pathOnDocserver . date('Y') . DIRECTORY_SEPARATOR.date('m')
            . DIRECTORY_SEPARATOR
        )
        ) {
            mkdir(
                $pathOnDocserver . date('Y') . DIRECTORY_SEPARATOR.date('m')
                . DIRECTORY_SEPARATOR,
                0770
            );
            $this->setRights(
                ['path' => $pathOnDocserver . date('Y') . DIRECTORY_SEPARATOR.date('m') . DIRECTORY_SEPARATOR]
            );
        }
        if (isset($GLOBALS['wb']) && $GLOBALS['wb'] <> '') {
            $path = $pathOnDocserver . date('Y') . DIRECTORY_SEPARATOR.date('m')
                  . DIRECTORY_SEPARATOR . 'BATCH' . DIRECTORY_SEPARATOR 
                  . $GLOBALS['wb'] . DIRECTORY_SEPARATOR;
            if (!is_dir($path)) {
                mkdir($path, 0770, true);
                $this->setRights(['path' => $path]);
            } else {
                $datas = [
                    'errors' => 'Folder alreay exists, workbatch already exist:' . $path,
                ];

                return $datas;
            }
        } else {
            $path = $pathOnDocserver . date('Y') . DIRECTORY_SEPARATOR.date('m')
                  . DIRECTORY_SEPARATOR;
        }

        $datas = 
            [
                'createPathOnDocServer' => 
                    [
                        'destinationDir' => $path
                    ]
            ];
        
        return $datas;
    }

     /**
     * Set Rights on resources
     * @param   string $dest path of the resource
     * @return  nothing
     */
    public function setRights($aArgs)
    {
        if (empty($aArgs['path'])) {
            $datas = [
                    'errors' => 'path ' . _EMPTY,
            ];

            return $datas;
        }

        if (!is_dir($aArgs['path'])) {
            $datas = [
                    'errors' => 'path ' . _NOT_EXISTS,
            ];

            return $datas;
        }

        if (
            DIRECTORY_SEPARATOR == '/'
            && (isset($GLOBALS['apacheUserAndGroup'])
            && $GLOBALS['apacheUserAndGroup'] <> '')
        ) {
            exec('chown ' 
                . escapeshellarg($GLOBALS['apacheUserAndGroup']) . ' ' 
                . escapeshellarg($aArgs['path'])
            );
        }

        umask(0022);
        chmod($aArgs['path'], 0770);

        $datas = [
            'setRights' => true,
        ];

        return $datas;
    }

    /**
     * copy doc in a docserver.
     * @param   string $sourceFilePath collection resource
     * @param   array $infoFileNameInTargetDocserver infos of the doc to store,
     *          contains : subdirectory path and new filename
     * @param   string $docserverSourceFingerprint
     * @return  array of docserver data for res_x else return error
     */
    public function copyOnDocserver($aArgs)
    {
        if (empty($aArgs['destinationDir'])) {
            $datas = [
                'errors' => 'destinationDir ' . _EMPTY,
            ];

            return $datas;
        }

        if (empty($aArgs['fileDestinationName'])) {
            $datas = [
                'errors' => 'fileDestinationName ' . _EMPTY,
            ];

            return $datas;
        }

        if (file_exists(($aArgs['destinationDir'] . $aArgs['fileDestinationName']))) {
            $datas = [
                'errors' => '' . $aArgs['destinationDir'] 
                    . $aArgs['fileDestinationName'] . ' ' . _FILE_ALREADY_EXISTS,
            ];

            return $datas;
        }

        if (empty($aArgs['sourceFilePath'])) {
            $datas = [
                'errors' => 'sourceFilePath ' . _EMPTY,
            ];

            return $datas;
        }

        if (!file_exists($aArgs['sourceFilePath'])) {
            $datas = [
                'errors' => 'sourceFilePath '  . _NOT_EXISTS,
            ];

            return $datas;
        }

        $destinationDir = $aArgs['destinationDir'];
        $fileDestinationName = $aArgs['fileDestinationName'];
        $sourceFilePath = str_replace('\\\\', '\\', $aArgs['sourceFilePath']);
        $docserverSourceFingerprint = $aArgs['docserverSourceFingerprint'];

        error_reporting(0);

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0770, true);
            $aArgs = [
                'path'=> $destinationDir
            ];

            $this->setRights($aArgs);
        }

        if(!copy($sourceFilePath, $destinationDir . $fileDestinationName)) {
            $datas = [
                'errors' => _DOCSERVER_COPY_ERROR . ' source : ' . $sourceFilePath
                    . ' dest : ' . $destinationDir . $fileDestinationName
            ];

            return $datas;
        }
        
        $aArgs = [
            'path'=> $destinationDir . $fileDestinationName
        ];

        $this->setRights($aArgs);

        $fingerprintControl = array();

        $aArgs = [
            'pathInit'          => $sourceFilePath,
            'pathTarget'        => $destinationDir . $fileDestinationName,
            'fingerprintMode'   => $docserverSourceFingerprint,
        ];

        $fingerprintControl = $this->controlFingerprint($aArgs);

        if (!empty($fingerprintControl['errors'])) {
            $datas = [
                'errors' => $fingerprintControl['errors'],
            ];

            return $datas;
        }

        //for batch like life cycle
        if (isset($GLOBALS['currentStep'])) {
            $destinationDir = str_replace(
                $GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']
                ['path_template'],
                '',
                $destinationDir
            );
        }

        $destinationDir = str_replace(
            DIRECTORY_SEPARATOR,
            '#',
            $destinationDir
        );

        $datas = [
            'copyOnDocserver' =>  
                [
                    'destinationDir'        => $destinationDir,
                    'fileDestinationName'   => $fileDestinationName,
                    'fileSize'              => filesize($sourceFilePath),
                ]
        ];

        if (isset($GLOBALS['TmpDirectory']) && $GLOBALS['TmpDirectory'] <> '') {
            $aArgs = [
                'path'        => $GLOBALS['TmpDirectory'],
                'contentOnly' => true,
            ];
            $this->washTmp($aArgs);
        }

        return $datas;
    }

    /**
     * Compute the fingerprint of a resource
     * @param   string $path path of the resource
     * @param   string $fingerprintMode (md5, sha512, ...)
     * @return  string the fingerprint
     */
    public function doFingerprint($aArgs)
    {
        if (empty($aArgs['path'])) {
            $datas = [
                    'errors' => 'path ' . _EMPTY,
            ];

            return $datas;
        }

        if (!file_exists($aArgs['path'])) {
            $datas = [
                    'errors' => 'path ' . _NOT_EXISTS,
            ];

            return $datas;
        }

        if (
            $aArgs['fingerprintMode'] == 'NONE' || 
            $aArgs['fingerprintMode'] == ''
        ) {
            $datas = [
                'fingerprint' => '0',
            ];

            return $datas;
        } else {
            $fingerprint = hash_file(
                strtolower($aArgs['fingerprintMode']), 
                $aArgs['path']
            );

            $datas = [
                'fingerprint' => $fingerprint,
            ];
            
            return $datas;
        }
    }

    /**
     * Control fingerprint between two resources
     * @param   string $pathInit path of the resource 1
     * @param   string $pathTarget path of the resource 2
     * @param   string $fingerprintMode (md5, sha512, ...)
     * @return  array ok or ko with error
     */
    public function controlFingerprint($aArgs)
    {
        if (empty($aArgs['pathInit'])) {
            $datas = [
                    'errors' => 'pathInit ' . _EMPTY,
            ];

            return $datas;
        }

        if (!file_exists($aArgs['pathInit'])) {
            $datas = [
                    'errors' => 'pathInit ' . _NOT_EXISTS,
            ];

            return $datas;
        }

        if (empty($aArgs['pathTarget'])) {
            $datas = [
                    'errors' => 'pathTarget ' . _EMPTY,
            ];

            return $datas;
        }

        if (!file_exists($aArgs['pathTarget'])) {
            $datas = [
                    'errors' => 'pathTarget ' . _NOT_EXISTS,
            ];

            return $datas;
        }

        $aArgsSrc = [
            'path'            => $aArgs['pathInit'],
            'fingerprintMode' => $aArgs['fingerprintMode'],
        ];

        $aArgsTarget = [
            'path'            => $aArgs['pathTarget'],
            'fingerprintMode' => $aArgs['fingerprintMode'],
        ];

        if ($this->doFingerprint($aArgsSrc) <> $this->doFingerprint($aArgsTarget)) {
            $datas = [
                    'errors' => PB_WITH_FINGERPRINT_OF_DOCUMENT . ' ' . $aArgs['pathInit']
                        . ' '. _AND . ' ' . $aArgs['pathTarget'],
            ];
        } else {
            $datas = [
                'controlFingerprint' => true,
            ];
        }

        return $datas;
    }

    /**
     * del tmp files
     * @param   $path dir to wash
     * @param   $contentOnly boolean true if only the content
     * @return  boolean
     */
    public function washTmp($aArgs)
    {
        if (empty($aArgs['path'])) {
            $datas = [
                    'errors' => 'path ' . _EMPTY,
            ];

            return $datas;
        }

        if (!is_dir($aArgs['path'])) {
            $datas = [
                    'errors' => 'path ' . _NOT_EXISTS,
            ];

            return $datas;
        }

        if (!is_bool($aArgs['contentOnly'])) {
            $datas = [
                    'errors' => 'contentOnly ' . _NOT . ' ' . _VALID,
            ];

            return $datas;
        }

        $objects = scandir($aArgs['path']);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (filetype($aArgs['path'] . DIRECTORY_SEPARATOR . $object) == 'dir') {
                    $this->washTmp(
                        ['path' => $aArgs['path'] . DIRECTORY_SEPARATOR . $object]
                    );
                } else {
                    unlink($aArgs['path'] . DIRECTORY_SEPARATOR . $object);
                }
            }
        }

        reset($objects);
        
        if (!$aArgs['contentOnly']) {
            rmdir($aArgs['path']);
        }

        $datas = [
            'washTmp' => true,
        ];

        return $datas;
    }

}