<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Store Controller
 * @author dev@maarch.org
 * @ingroup core
 */

namespace Resource\controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Attachment\models\AttachmentModel;
use Contact\models\ContactModel;
use Docserver\controllers\DocserverController;
use Resource\models\ChronoModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use Respect\Validation\Validator;
use Resource\models\ResModel;
use SrcCore\models\CoreConfigModel;

class StoreController
{
    public static function storeResource(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['encodedFile', 'format', 'status', 'type_id', 'category_id']);
        ValidatorModel::stringType($aArgs, ['format', 'status']);

        try {
            foreach ($aArgs as $column => $value) {
                if (empty($value)) {
                    unset($aArgs[$column]);
                }
            }
            $fileContent = base64_decode(str_replace(['-', '_'], ['+', '/'], $aArgs['encodedFile']));

            $storeResult = DocserverController::storeResourceOnDocServer([
                'collId'            => 'letterbox_coll',
                'docserverTypeId'   => 'DOC',
                'encodedResource'   => base64_encode($fileContent),
                'format'            => $aArgs['format']
            ]);
            if (!empty($storeResult['errors'])) {
                return ['errors' => '[storeResource] ' . $storeResult['errors']];
            }
            unset($aArgs['encodedFile']);

            $resId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'res_id_mlb_seq']);

            $data = [
                'docserver_id'  => $storeResult['docserver_id'],
                'filename'      => $storeResult['file_destination_name'],
                'filesize'      => $storeResult['fileSize'],
                'path'          => $storeResult['destination_dir'],
                'fingerprint'   => $storeResult['fingerPrint'],
                'res_id'        => $resId
            ];
            $data = array_merge($aArgs, $data);
            $data = StoreController::prepareStorage($data);

            ResModel::create($data);

            return $resId;
        } catch (\Exception $e) {
            return ['errors' => '[storeResource] ' . $e->getMessage()];
        }
    }

    public static function storeAttachment(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['encodedFile', 'data', 'table', 'fileFormat', 'status']);
        ValidatorModel::stringType($aArgs, ['collId', 'table', 'fileFormat', 'status']);

        try {
            $fileContent    = base64_decode(str_replace(['-', '_'], ['+', '/'], $aArgs['encodedFile']));

            $storeResult = DocserverController::storeResourceOnDocServer([
                'collId'            => empty($aArgs['version']) ? 'attachments_coll' : 'attachments_version_coll',
                'docserverTypeId'   => 'DOC',
                'encodedResource'   => base64_encode($fileContent),
                'format'            => $aArgs['fileFormat']
            ]);
            if (!empty($storeResult['errors'])) {
                return ['errors' => '[storeResource] ' . $storeResult['errors']];
            }

            $data = StoreController::prepareAttachmentStorage([
                'data'          => $aArgs['data'],
                'docserverId'   => $storeResult['docserver_id'],
                'status'        => $aArgs['status'],
                'fileName'      => $storeResult['file_destination_name'],
                'fileFormat'    => $aArgs['fileFormat'],
                'fileSize'      => $storeResult['fileSize'],
                'path'          => $storeResult['destination_dir'],
                'fingerPrint'   => $storeResult['fingerPrint']
            ]);

            if (empty($aArgs['version'])) {
                $id = AttachmentModel::create($data);
            } else {
                $id = AttachmentModel::createVersion($data);
            }

            return $id;
        } catch (\Exception $e) {
            return ['errors' => '[storeResource] ' . $e->getMessage()];
        }
    }

    public static function controlFingerPrint(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['pathInit', 'pathTarget']);
        ValidatorModel::stringType($aArgs, ['pathInit', 'pathTarget', 'fingerprintMode']);

        if (!file_exists($aArgs['pathInit'])) {
            return ['errors' => '[controlFingerprint] PathInit does not exist'];
        }
        if (!file_exists($aArgs['pathTarget'])) {
            return ['errors' => '[controlFingerprint] PathTarget does not exist'];
        }

        $fingerprint1 = StoreController::getFingerPrint(['filePath' => $aArgs['pathInit'], 'mode' => $aArgs['fingerprintMode']]);
        $fingerprint2 = StoreController::getFingerPrint(['filePath' => $aArgs['pathTarget'], 'mode' => $aArgs['fingerprintMode']]);

        if ($fingerprint1 != $fingerprint2) {
            return ['errors' => '[controlFingerprint] Fingerprints do not match: ' . $aArgs['pathInit'] . ' and ' . $aArgs['pathTarget']];
        }

        return true;
    }

    public static function getFingerPrint(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['filePath']);
        ValidatorModel::stringType($aArgs, ['filePath', 'mode']);

        if (empty($aArgs['mode']) || $aArgs['mode'] == 'NONE') {
            $aArgs['mode'] = 'sha512';
        }

        return hash_file(strtolower($aArgs['mode']), $aArgs['filePath']);
    }

    public static function prepareStorage(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['docserver_id', 'filename', 'format', 'filesize', 'path', 'fingerprint', 'status', 'res_id']);
        ValidatorModel::stringType($aArgs, ['docserver_id', 'filename', 'format', 'path', 'fingerprint', 'status']);
        ValidatorModel::intVal($aArgs, ['filesize', 'res_id']);

        if (empty($aArgs['typist'])) {
            $aArgs['typist'] = 'auto';
        }

        unset($aArgs['alt_identifier']);
        if (!empty($aArgs['chrono'])) {
            $aArgs['alt_identifier'] = ChronoModel::getChrono(['id' => $aArgs['category_id'], 'entityId' => $aArgs['destination'], 'typeId' => $aArgs['type_id'], 'resId' => $aArgs['res_id']]);
        }
        unset($aArgs['chrono']);

        if (empty($aArgs['process_limit_date'])) {
            $processLimitDate = ResModel::getStoredProcessLimitDate(['typeId' => $aArgs['type_id'], 'admissionDate' => $aArgs['admission_date']]);
            $aArgs['process_limit_date'] = $processLimitDate;
        }

        if (!empty($aArgs['exp_contact_id']) && !is_numeric($aArgs['exp_contact_id'])) {
            $mail = explode('<', str_replace('>', '', $aArgs['exp_contact_id']));
            $contact = ContactModel::getByEmail(['email' => $mail[count($mail) - 1], 'select' => ['contacts_v2.contact_id']]);
            if (!empty($contact['contact_id'])) {
                $aArgs['exp_contact_id'] = $contact['contact_id'];
            } else {
                $aArgs['exp_contact_id'] = 0;
            }
        }

        if (!empty($aArgs['address_id']) && !is_numeric($aArgs['address_id'])) {
            $mail = explode('<', str_replace('>', '', $aArgs['address_id']));
            $contact = ContactModel::getByEmail(['email' => $mail[count($mail) - 1], 'select' => ['contact_addresses.id']]);
            if (!empty($contact['id'])) {
                $aArgs['address_id'] = $contact['id'];
            } else {
                $aArgs['address_id'] = 0;
            }
        }

        unset($aArgs['external_id']);
        if (!empty($aArgs['externalId'])) {
            if (is_array($aArgs['externalId'])) {
                $aArgs['external_id'] = json_encode($aArgs['externalId']);
            }
            unset($aArgs['externalId']);
        }

        $aArgs['creation_date'] = 'CURRENT_TIMESTAMP';

        return $aArgs;
    }

    public static function prepareAttachmentStorage(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['data', 'docserverId', 'fileName', 'fileFormat', 'fileSize', 'path', 'fingerPrint']);
        ValidatorModel::stringType($aArgs, ['docserverId', 'status', 'fileName', 'fileFormat', 'path', 'fingerPrint']);
        ValidatorModel::arrayType($aArgs, ['data']);
        ValidatorModel::intVal($aArgs, ['fileSize']);

        foreach ($aArgs['data'] as $key => $value) {
            $aArgs['data'][$key]['column'] = strtolower($value['column']);
        }

        $aArgs['data'][] = [
            'column'    => 'docserver_id',
            'value'     => $aArgs['docserverId'],
            'type'      => 'string'
        ];
        $aArgs['data'][] = [
            'column'    => 'creation_date',
            'value'     => 'CURRENT_TIMESTAMP',
            'type'      => 'function'
        ];
        $aArgs['data'][] = [
            'column'    => 'path',
            'value'     => $aArgs['path'],
            'type'      => 'string'
        ];
        $aArgs['data'][] = [
            'column'    => 'fingerprint',
            'value'     => $aArgs['fingerPrint'],
            'type'      => 'string'
        ];
        $aArgs['data'][] = [
            'column'    => 'filename',
            'value'     => $aArgs['fileName'],
            'type'      => 'string'
        ];
        $aArgs['data'][] = [
            'column'    => 'format',
            'value'     => $aArgs['fileFormat'],
            'type'      => 'string'
        ];
        $aArgs['data'][] = [
            'column'    => 'filesize',
            'value'     => $aArgs['fileSize'],
            'type'      => 'int'
        ];

        $formatedData = [];
        foreach ($aArgs['data'] as $value) {
            $formatedData[$value['column']] = $value['value'];
        }

        return $formatedData;
    }

    public function checkFileUpload(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        if (!Validator::notEmpty()->validate($body['size'])) {
            return $response->withStatus(400)->withJson(['errors' => 'filesize is empty']);
        } else if (!Validator::notEmpty()->validate($body['type'])) {
            return $response->withStatus(400)->withJson(['errors' => 'no mime type detected']);
        } else if (!Validator::notEmpty()->validate($body['extension'])) {
            return $response->withStatus(400)->withJson(['errors' => 'this filename has no extension']);
        }

        if (!StoreController::isFileAllowed($body)) {
            return $response->withStatus(400)->withJson(['errors' => _FILE_NOT_ALLOWED_INFO_1.' "'.$body['extension'].'" '._FILE_NOT_ALLOWED_INFO_2.' "'. $body['type']. '" '._FILE_NOT_ALLOWED_INFO_3]);
        }

        $maxFilesizeMo = ini_get('upload_max_filesize');
        $maxFilesizeKo = ini_get('upload_max_filesize')*1024;

        if ($body['size']/1024 > $maxFilesizeKo) {
            return $response->withStatus(400)->withJson(['errors' => _MAX_SIZE_UPLOAD_REACHED.' ('.round($maxFilesizeMo).'Mo Max.)']);
        }
        return $response->withJson(['success']);
    }

    public static function isFileAllowed(array $args)
    {
        ValidatorModel::notEmpty($args, ['extension', 'type']);
        ValidatorModel::stringType($args, ['extension', 'type']);

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/extensions.xml']);
        if ($loadedXml) {
            foreach ($loadedXml->FORMAT as $value) {
                if (strtolower((string)$value->name) == strtolower($args['extension']) && strtolower((string)$value->mime) == strtolower($args['type'])) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function getAllowedFiles()
    {
        $allowedFiles = [];

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/extensions.xml']);
        if ($loadedXml) {
            foreach ($loadedXml->FORMAT as $value) {
                $allowedFiles[] = [
                    'extension'     => (string)$value->name,
                    'mimeType'      => (string)$value->mime,
                    'canConvert'    => filter_var((string)$value->canConvert, FILTER_VALIDATE_BOOLEAN)
                ];
            }
        }

        return $allowedFiles;
    }

    public static function getOctetSizeFromPhpIni(array $args)
    {
        if (strpos($args['size'], 'K') !== false) {
            return (int)$args['size'] * 1024;
        } elseif (strpos($args['size'], 'M') !== false) {
            return (int)$args['size'] * 1048576;
        } elseif (strpos($args['size'], 'G') !== false) {
            return (int)$args['size'] * 1073741824;
        }

        return (int)$args['size'];
    }
}
