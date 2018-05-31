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
*/

namespace Docserver\controllers;

use Docserver\models\DocserverTypeModel;
use Group\models\ServiceModel;
use History\controllers\HistoryController;
use Resource\controllers\StoreController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\ValidatorModel;
use Docserver\models\DocserverModel;

class DocserverController
{
    public function get(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_docservers', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson(['docservers' => DocserverModel::get()]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_docservers', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $docserver = DocserverModel::getById(['id' => $aArgs['id']]);

        if(empty($docserver)){
            return $response->withStatus(400)->withJson(['errors' => 'Docserver not found']);
        }

        return $response->withJson($docserver);
    }

    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_docservers', 'userId' => $GLOBALS['userId'], 'location' => 'basket', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['id']) && preg_match("/^[\w-]*$/", $data['id']) && (strlen($data['id']) < 32);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['docserver_type_id']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['device_label']);
        $check = $check && Validator::intVal()->notEmpty()->validate($data['size_limit_number']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['path_template']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['coll_id']);
        $check = $check && Validator::intVal()->notEmpty()->validate($data['priority_number']);
        $check = $check && Validator::intVal()->notEmpty()->validate($data['adr_priority_number']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $existingDocserver = DocserverModel::getById(['id' => $data['id'], 'select' => ['1']]);
        if (!empty($existingDocserver)) {
            return $response->withStatus(400)->withJson(['errors' => _ID. ' ' . _ALREADY_EXISTS]);
        }

        $data['is_readonly'] = empty($data['is_readonly']) ? 'N' : 'Y';

        DocserverModel::create($data);
        HistoryController::add([
            'tableName' => 'docservers',
            'recordId'  => $data['id'],
            'eventType' => 'ADD',
            'info'      => _BASKET_CREATION . " : {$data['id']}",
            'moduleId'  => 'docserver',
            'eventId'   => 'docserverCreation',
        ]);

        return $response->withJson(['basket' => $data['id']]);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_docservers', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $docserver = DocserverModel::getById(['id' => $aArgs['id']]);

        if(empty($docserver)){
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        DocserverModel::delete(['id' => $aArgs['id']]);

        return $response->withJson(['docservers' => DocserverModel::get()]);
    }

    public static function storeResourceOnDocServer(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['collId', 'fileInfos']);
        ValidatorModel::arrayType($aArgs, ['fileInfos']);
        ValidatorModel::stringType($aArgs, ['collId', 'docserverTypeId']);
        ValidatorModel::notEmpty($aArgs['fileInfos'], ['tmpDir', 'size', 'format', 'tmpFileName']);
        ValidatorModel::stringType($aArgs['fileInfos'], ['tmpDir', 'format', 'tmpFileName']);
        ValidatorModel::intVal($aArgs['fileInfos'], ['size']);

        if (!is_dir($aArgs['fileInfos']['tmpDir'])) {
            return ['errors' => '[storeRessourceOnDocserver] FileInfos.tmpDir does not exist'];
        }
        if (!file_exists($aArgs['fileInfos']['tmpDir'] . $aArgs['fileInfos']['tmpFileName'])) {
            return ['errors' => '[storeRessourceOnDocserver] FileInfos.tmpFileName does not exist '
                . $aArgs['fileInfos']['tmpDir'] . $aArgs['fileInfos']['tmpFileName']];
        }

        $aArgs['docserverTypeId'] = empty($aArgs['docserverTypeId']) ? 'DOC' : $aArgs['docserverTypeId'];
        $docserver = DocserverModel::getDocserverToInsert(['collId' => $aArgs['collId'], 'typeId' => $aArgs['docserverTypeId']]);
        if (empty($docserver)) {
            return ['errors' => '[storeRessourceOnDocserver] No available Docserver'];
        }

        $pathOnDocserver = DocserverController::createPathOnDocServer(['path' => $docserver['path_template']]);
        if (!empty($pathOnDocserver['errors'])) {
            return ['errors' => '[storeRessourceOnDocserver] ' . $pathOnDocserver['errors']];
        }

        $docinfo = DocserverController::getNextFileNameInDocServer(['pathOnDocserver' => $pathOnDocserver['pathToDocServer']]);
        if (!empty($docinfo['errors'])) {
            return ['errors' => '[storeRessourceOnDocserver] ' . $docinfo['errors']];
        }
        $pathInfoOnTmp = pathinfo($aArgs['fileInfos']['tmpDir'] . $aArgs['fileInfos']['tmpFileName']);
        $docinfo['fileDestinationName'] .= '.' . strtolower($pathInfoOnTmp['extension']);

        $docserverTypeObject = DocserverTypeModel::getById(['id' => $docserver['docserver_type_id']]);
        $copyResult = DocserverController::copyOnDocServer([
            'sourceFilePath'             => $aArgs['fileInfos']['tmpDir'] . $aArgs['fileInfos']['tmpFileName'],
            'destinationDir'             => $docinfo['destinationDir'],
            'fileDestinationName'        => $docinfo['fileDestinationName'],
            'docserverSourceFingerprint' => $docserverTypeObject['fingerprint_mode'],
        ]);
        if (!empty($copyResult['errors'])) {
            return ['errors' => '[storeRessourceOnDocserver] ' . $copyResult['errors']];
        }

        $destinationDir = substr($copyResult['copyOnDocserver']['destinationDir'], strlen($docserver['path_template'])) . '/';
        $destinationDir = str_replace(DIRECTORY_SEPARATOR, '#', $destinationDir);

        DocserverModel::update([
            'docserver_id'          => $docserver['docserver_id'],
            'actual_size_number'    => $docserver['actual_size_number'] + $aArgs['fileInfos']['size']
        ]);

        return [
            'path_template'         => $docserver['path_template'],
            'destination_dir'       => $destinationDir,
            'docserver_id'          => $docserver['docserver_id'],
            'file_destination_name' => $copyResult['copyOnDocserver']['fileDestinationName'],
            'fileSize'              => $copyResult['copyOnDocserver']['fileSize'],
            'fingerPrint'           => StoreController::getFingerPrint([
                'filePath'  => $docinfo['destinationDir'] . $docinfo['fileDestinationName'],
                'mode'      => $docserverTypeObject['fingerprint_mode']
            ])
        ];
    }

    public static function createPathOnDocServer(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['path']);
        ValidatorModel::stringType($aArgs, ['path']);

        if (!is_dir($aArgs['path'])) {
            return ['errors' => '[createPathOnDocServer] Path does not exist ' . $aArgs['path']];
        }

        error_reporting(0);
        umask(0022);

        $yearPath = $aArgs['path'] . date('Y') . '/';
        if (!is_dir($yearPath)) {
            mkdir($yearPath, 0770);
            if (DIRECTORY_SEPARATOR == '/' && !empty($GLOBALS['apacheUserAndGroup'])) {
                exec('chown ' . escapeshellarg($GLOBALS['apacheUserAndGroup']) . ' ' . escapeshellarg($yearPath));
            }
            umask(0022);
            chmod($yearPath, 0770);
        }

        $monthPath = $yearPath . date('m') . '/';
        if (!is_dir($monthPath)) {
            mkdir($monthPath, 0770);
            if (DIRECTORY_SEPARATOR == '/' && !empty($GLOBALS['apacheUserAndGroup'])) {
                exec('chown ' . escapeshellarg($GLOBALS['apacheUserAndGroup']) . ' ' . escapeshellarg($monthPath));
            }
            umask(0022);
            chmod($monthPath, 0770);
        }

        $pathToDS = $monthPath;
        if (!empty($GLOBALS['wb'])) {
            $pathToDS = "{$monthPath}BATCH/{$GLOBALS['wb']}/";
            if (!is_dir($pathToDS)) {
                mkdir($pathToDS, 0770, true);
                if (DIRECTORY_SEPARATOR == '/' && !empty($GLOBALS['apacheUserAndGroup'])) {
                    exec('chown ' . escapeshellarg($GLOBALS['apacheUserAndGroup']) . ' ' . escapeshellarg($monthPath));
                }
                umask(0022);
                chmod($monthPath, 0770);
            }
        }

        return ['pathToDocServer' => $pathToDS];
    }

    public static function getNextFileNameInDocServer(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['pathOnDocserver']);
        ValidatorModel::stringType($aArgs, ['pathOnDocserver']);

        if (!is_dir($aArgs['pathOnDocserver'])) {
            return ['errors' => '[getNextFileNameInDocServer] PathOnDocserver does not exist'];
        }

        umask(0022);

        $aFiles = scandir($aArgs['pathOnDocserver']);
        array_shift($aFiles); // Remove . line
        array_shift($aFiles); // Remove .. line

        if (file_exists($aArgs['pathOnDocserver'] . '/package_information')) {
            unset($aFiles[array_search('package_information', $aFiles)]);
        }
        if (is_dir($aArgs['pathOnDocserver'] . '/BATCH')) {
            unset($aFiles[array_search('BATCH', $aFiles)]);
        }

        $filesNb = count($aFiles);
        if ($filesNb == 0) {
            $zeroOnePath = $aArgs['pathOnDocserver'] . '0001/';

            if (!mkdir($zeroOnePath, 0770)) {
                return ['errors' => '[getNextFileNameInDocServer] Directory creation failed: ' . $zeroOnePath];
            } else {
                if (DIRECTORY_SEPARATOR == '/' && !empty($GLOBALS['apacheUserAndGroup'])) {
                    exec('chown ' . escapeshellarg($GLOBALS['apacheUserAndGroup']) . ' ' . escapeshellarg($zeroOnePath));
                }
                umask(0022);
                chmod($zeroOnePath, 0770);

                return [
                    'destinationDir'        => $zeroOnePath,
                    'fileDestinationName'   => '0001_' . mt_rand(),
                ];
            }
        } else {
            $destinationDir = $aArgs['pathOnDocserver'] . str_pad(count($aFiles), 4, '0', STR_PAD_LEFT) . '/';
            $aFilesBis = scandir($aArgs['pathOnDocserver'] . strval(str_pad(count($aFiles), 4, '0', STR_PAD_LEFT)));
            array_shift($aFilesBis); // Remove . line
            array_shift($aFilesBis); // Remove .. line

            $filesNbBis = count($aFilesBis);
            if ($filesNbBis >= 1000) { //If number of files >= 1000 then creates a new subdirectory
                $zeroNumberPath = $aArgs['pathOnDocserver'] . str_pad($filesNb + 1, 4, '0', STR_PAD_LEFT) . '/';

                if (!mkdir($zeroNumberPath, 0770)) {
                    return ['errors' => '[getNextFileNameInDocServer] Directory creation failed: ' . $zeroNumberPath];
                } else {
                    if (DIRECTORY_SEPARATOR == '/' && !empty($GLOBALS['apacheUserAndGroup'])) {
                        exec('chown ' . escapeshellarg($GLOBALS['apacheUserAndGroup']) . ' ' . escapeshellarg($zeroNumberPath));
                    }
                    umask(0022);
                    chmod($zeroNumberPath, 0770);

                    return [
                        'destinationDir'        => $zeroNumberPath,
                        'fileDestinationName'   => '0001_' . mt_rand(),
                    ];
                }
            } else {
                $higher = $filesNbBis + 1;
                foreach ($aFilesBis as $value) {
                    $currentFileName = explode('.', $value);
                    if ($higher <= (int)$currentFileName[0]) {
                        $higher = (int)$currentFileName[0] + 1;
                    }
                }

                return [
                    'destinationDir'        => $destinationDir,
                    'fileDestinationName'   => str_pad($higher, 4, '0', STR_PAD_LEFT) . '_' . mt_rand(),
                ];
            }
        }
    }

    public static function copyOnDocServer(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['destinationDir', 'fileDestinationName', 'sourceFilePath']);
        ValidatorModel::stringType($aArgs, ['destinationDir', 'fileDestinationName', 'sourceFilePath']);

        if (file_exists($aArgs['destinationDir'] . $aArgs['fileDestinationName'])) {
            return ['errors' => '[copyOnDocserver] File already exists: ' . $aArgs['destinationDir'] . $aArgs['fileDestinationName']];
        }

        if (!file_exists($aArgs['sourceFilePath'])) {
            return ['errors' => '[copyOnDocserver] File does not exist'];
        }

        error_reporting(0);
        $aArgs['sourceFilePath'] = str_replace('\\\\', '\\', $aArgs['sourceFilePath']);

        if (!is_dir($aArgs['destinationDir'])) {
            mkdir($aArgs['destinationDir'], 0770, true);
            if (DIRECTORY_SEPARATOR == '/' && !empty($GLOBALS['apacheUserAndGroup'])) {
                exec('chown ' . escapeshellarg($GLOBALS['apacheUserAndGroup']) . ' ' . escapeshellarg($aArgs['destinationDir']));
            }
            umask(0022);
            chmod($aArgs['destinationDir'], 0770);
        }

        if (!copy($aArgs['sourceFilePath'], $aArgs['destinationDir'] . $aArgs['fileDestinationName'])) {
            return ['errors' => '[copyOnDocserver] Copy on the docserver failed'];
        }
        if (DIRECTORY_SEPARATOR == '/' && !empty($GLOBALS['apacheUserAndGroup'])) {
            exec('chown ' . escapeshellarg($GLOBALS['apacheUserAndGroup']) . ' ' . escapeshellarg($aArgs['destinationDir'] . $aArgs['fileDestinationName']));
        }
        umask(0022);
        chmod($aArgs['destinationDir'] . $aArgs['fileDestinationName'], 0770);

        $fingerprintControl = StoreController::controlFingerPrint([
            'pathInit'          => $aArgs['sourceFilePath'],
            'pathTarget'        => $aArgs['destinationDir'] . $aArgs['fileDestinationName'],
            'fingerprintMode'   => $aArgs['docserverSourceFingerprint'],
        ]);
        if (!empty($fingerprintControl['errors'])) {
            return ['errors' => '[copyOnDocserver] ' . $fingerprintControl['errors']];
        }

        if (!empty($GLOBALS['currentStep'])) { // For batch like life cycle
            $aArgs['destinationDir'] = str_replace($GLOBALS['docservers'][$GLOBALS['currentStep']]['docserver']['path_template'], '', $aArgs['destinationDir']);
        }
        $aArgs['destinationDir'] = str_replace(DIRECTORY_SEPARATOR, '#', $aArgs['destinationDir']);

        $dataToReturn = [
            'copyOnDocserver' =>
                [
                    'destinationDir'        => $aArgs['destinationDir'],
                    'fileDestinationName'   => $aArgs['fileDestinationName'],
                    'fileSize'              => filesize(str_replace('#', '/', $aArgs['destinationDir']) . $aArgs['fileDestinationName']),
                ]
        ];

        if (!empty($GLOBALS['TmpDirectory'])) {
            DocserverController::directoryWasher(['path' => $GLOBALS['TmpDirectory']]);
        }

        return $dataToReturn;
    }

    private static function directoryWasher(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['path']);
        ValidatorModel::stringType($aArgs, ['path']);

        if (!is_dir($aArgs['path'])) {
            return ['errors' => '[directoryWasher] Path does not exist'];
        }

        $aFiles = scandir($aArgs['path']);
        foreach ($aFiles as $file) {
            if ($file != '.' && $file != '..') {
                if (filetype($aArgs['path'] . '/' . $file) == 'dir') {
                    DocserverController::directoryWasher(['path' => $aArgs['path'] . '/' . $file]);
                } else {
                    unlink($aArgs['path'] . '/' . $file);
                }
            }
        }

        reset($aFiles);

        return true;
    }
}
