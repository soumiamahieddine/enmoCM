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

use Attachment\models\AttachmentModel;
use Docserver\controllers\DocserverController;
use IndexingModel\models\IndexingModelModel;
use Resource\models\ChronoModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use Resource\models\ResModel;
use SrcCore\models\CoreConfigModel;

class StoreController
{
    public static function storeResource(array $args)
    {
        ValidatorModel::notEmpty($args, ['encodedFile', 'format', 'doctype', 'modelId']);
        ValidatorModel::stringType($args, ['format']);
        ValidatorModel::intVal($args, ['doctype', 'modelId']);

        try {
            $fileContent = base64_decode(str_replace(['-', '_'], ['+', '/'], $args['encodedFile']));

            $storeResult = DocserverController::storeResourceOnDocServer([
                'collId'            => 'letterbox_coll',
                'docserverTypeId'   => 'DOC',
                'encodedResource'   => base64_encode($fileContent),
                'format'            => $args['format']
            ]);
            if (!empty($storeResult['errors'])) {
                return ['errors' => '[storeResource] ' . $storeResult['errors']];
            }

            $resId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'res_id_mlb_seq']);

            $data = [
                'resId'         => $resId,
                'docserver_id'  => $storeResult['docserver_id'],
                'filename'      => $storeResult['file_destination_name'],
                'filesize'      => $storeResult['fileSize'],
                'path'          => $storeResult['directory'],
                'fingerprint'   => $storeResult['fingerPrint']
            ];
            $data = array_merge($args, $data);
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

    public static function prepareStorage(array $args)
    {
        ValidatorModel::notEmpty($args, ['docserver_id', 'filename', 'format', 'filesize', 'path', 'fingerprint', 'resId', 'modelId']);
        ValidatorModel::stringType($args, ['docserver_id', 'filename', 'format', 'path', 'fingerprint']);
        ValidatorModel::intVal($args, ['filesize', 'resId', 'modelId']);

        $indexingModel = IndexingModelModel::getById(['id' => $args['modelId'], 'select' => ['category']]);

        if (empty($args['typist'])) {
            $args['typist'] = $GLOBALS['id'];
        }

        $chrono = null;
        if (!empty($args['chrono'])) {
            $chrono = ChronoModel::getChrono(['id' => $args['category_id'], 'entityId' => $args['destination'], 'typeId' => $args['doctype'], 'resId' => $args['resId']]);
        }

        if (!empty($args['processLimitDate']) && !empty($args['priority'])) {
            $args['priority'] = IndexingController::calculatePriorityWithProcessLimitDate(['processLimitDate' => $args['processLimitDate']]);
        }

        $externalId = '{}';
        if (!empty($args['externalId']) && is_array($args['externalId'])) {
            $externalId = json_encode($args['externalId']);
        }

        $preparedData = [
            'res_id'                => $args['resId'],
            'model_id'              => $args['modelId'],
            'category_id'           => $indexingModel['category'],
            'type_id'               => $args['doctype'],
            'subject'               => $args['subject'] ?? null,
            'alt_identifier'        => $chrono,
            'format'                => $args['format'],
            'typist'                => $args['typist'],
            'status'                => $args['status'] ?? null,
            'destination'           => $args['destination'] ?? null,
            'initiator'             => $args['initiator'] ?? null,
            'confidentiality'       => empty('confidentiality') ? 'N' : 'Y',
            'doc_date'              => $args['documentDate'] ?? null,
            'admission_date'        => $args['arrivalDate'] ?? null,
            'departure_date'        => $args['departureDate'] ?? null,
            'process_limit_date'    => $args['processLimitDate'] ?? null,
            'priority'              => $args['priority'] ?? null,
            'barcode'               => $args['barcode'] ?? null,
            'origin'                => $args['origin'] ?? null,
            'external_id'           => $externalId,
            'docserver_id'          => $args['docserver_id'],
            'filename'              => $args['filename'],
            'filesize'              => $args['filesize'],
            'path'                  => $args['path'],
            'fingerprint'           => $args['fingerprint'],
            'creation_date'         => 'CURRENT_TIMESTAMP'
        ];

        return $preparedData;
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

    public static function getFingerPrint(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['filePath']);
        ValidatorModel::stringType($aArgs, ['filePath', 'mode']);

        if (empty($aArgs['mode']) || $aArgs['mode'] == 'NONE') {
            $aArgs['mode'] = 'sha512';
        }

        return hash_file(strtolower($aArgs['mode']), $aArgs['filePath']);
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

    public static function getBytesSizeFromPhpIni(array $args)
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
