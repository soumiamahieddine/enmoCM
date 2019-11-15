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
use ContentManagement\controllers\MergeController;
use Docserver\controllers\DocserverController;
use Entity\models\EntityModel;
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
        ValidatorModel::notEmpty($args, ['doctype', 'modelId']);
        ValidatorModel::intVal($args, ['doctype', 'modelId']);

        try {
            $resId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'res_id_mlb_seq']);

            $data = ['resId' => $resId];
            $data = array_merge($args, $data);
            $data = StoreController::prepareStorage($data);

            if (!empty($args['encodedFile'])) {
                $fileContent = base64_decode(str_replace(['-', '_'], ['+', '/'], $args['encodedFile']));

                if (in_array($args['format'], MergeController::OFFICE_EXTENSIONS)) {
                    $tmpPath = CoreConfigModel::getTmpPath();
                    $uniqueId = CoreConfigModel::uniqueId();
                    $tmpFilename = "storeTmp_{$GLOBALS['id']}_{$uniqueId}.{$args['format']}";
                    file_put_contents($tmpPath . $tmpFilename, $fileContent);
                    $fileContent = MergeController::mergeChronoDocument(['chrono' => $data['alt_identifier'], 'path' => $tmpPath . $tmpFilename]);
                    $fileContent = base64_decode($fileContent['encodedDocument']);
                    unlink($tmpPath . $tmpFilename);
                }

                $storeResult = DocserverController::storeResourceOnDocServer([
                    'collId'            => 'letterbox_coll',
                    'docserverTypeId'   => 'DOC',
                    'encodedResource'   => base64_encode($fileContent),
                    'format'            => $args['format']
                ]);
                if (!empty($storeResult['errors'])) {
                    return ['errors' => '[storeResource] ' . $storeResult['errors']];
                }

                $data['docserver_id'] = $storeResult['docserver_id'];
                $data['filename'] = $storeResult['file_destination_name'];
                $data['filesize'] = $storeResult['fileSize'];
                $data['path'] = $storeResult['directory'];
                $data['fingerprint'] = $storeResult['fingerPrint'];
            }

            ResModel::create($data);

            return $resId;
        } catch (\Exception $e) {
            return ['errors' => '[storeResource] ' . $e->getMessage()];
        }
    }

    public static function storeAttachment(array $args)
    {
        try {
            $data = [];
            if (!empty($args['encodedFile'])) {
                $fileContent    = base64_decode(str_replace(['-', '_'], ['+', '/'], $args['encodedFile']));

                $storeResult = DocserverController::storeResourceOnDocServer([
                    'collId'            => 'attachments_coll',
                    'docserverTypeId'   => 'DOC',
                    'encodedResource'   => base64_encode($fileContent),
                    'format'            => $args['format']
                ]);
                if (!empty($storeResult['errors'])) {
                    return ['errors' => '[storeAttachment] ' . $storeResult['errors']];
                }

                $data = [
                    'docserver_id'  => $storeResult['docserver_id'],
                    'filename'      => $storeResult['file_destination_name'],
                    'filesize'      => $storeResult['fileSize'],
                    'path'          => $storeResult['directory'],
                    'fingerprint'   => $storeResult['fingerPrint']
                ];
            }

            $data = array_merge($args, $data);
            if (empty($args['id'])) {
                $data = StoreController::prepareAttachmentStorage($data);
                $id = AttachmentModel::create($data);

            } else {
                $data = StoreController::prepareUpdateAttachmentStorage($data, $args['id']);
                $id = AttachmentModel::update(['set' => $data, 'where' => ['res_id = ?'], 'data' => [$args['id']]]);
            }

            return $id;
        } catch (\Exception $e) {
            return ['errors' => '[storeAttachment] ' . $e->getMessage()];
        }
    }

    public static function prepareStorage(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId', 'modelId']);
        ValidatorModel::intVal($args, ['resId', 'modelId']);

        $indexingModel = IndexingModelModel::getById(['id' => $args['modelId'], 'select' => ['category']]);

        if (empty($args['typist'])) {
            $args['typist'] = $GLOBALS['id'];
        }

        if (!empty($args['initiator'])) {
            $entity = EntityModel::getById(['id' => $args['initiator'], 'select' => ['entity_id']]);
            $args['initiator'] = $entity['entity_id'];
        }
        if (!empty($args['destination'])) {
            $entity = EntityModel::getById(['id' => $args['destination'], 'select' => ['entity_id']]);
            $args['destination'] = $entity['entity_id'];
        }
        $chrono = null;
        if (!empty($args['chrono'])) {
            $chrono = ChronoModel::getChrono(['id' => $indexingModel['category'], 'entityId' => $args['destination'], 'typeId' => $args['doctype'], 'resId' => $args['resId']]);
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
            'typist'                => $args['typist'],
            'status'                => $args['status'] ?? null,
            'destination'           => $args['destination'] ?? null,
            'initiator'             => $args['initiator'] ?? null,
            'confidentiality'       => empty($args['confidentiality']) ? 'N' : 'Y',
            'doc_date'              => $args['documentDate'] ?? null,
            'admission_date'        => $args['arrivalDate'] ?? null,
            'departure_date'        => $args['departureDate'] ?? null,
            'process_limit_date'    => $args['processLimitDate'] ?? null,
            'priority'              => $args['priority'] ?? null,
            'barcode'               => $args['barcode'] ?? null,
            'origin'                => $args['origin'] ?? null,
            'external_id'           => $externalId,
            'creation_date'         => 'CURRENT_TIMESTAMP'
        ];

        return $preparedData;
    }

    public static function prepareAttachmentStorage(array $args)
    {
        ValidatorModel::notEmpty($args, ['docserver_id', 'filename', 'format', 'path', 'fingerprint']);
        ValidatorModel::stringType($args, ['docserver_id', 'filename', 'format', 'path', 'fingerprint']);
        ValidatorModel::intVal($args, ['filesize']);

        $attachmentsTypes = AttachmentModel::getAttachmentsTypesByXML();
        if ($attachmentsTypes[$args['type']]['chrono'] && empty($args['chrono'])) {
            $resource = ResModel::getById(['select' => ['destination', 'type_id'], 'resId' => $args['resIdMaster']]);
            $args['chrono'] = ChronoModel::getChrono(['id' => 'outgoing', 'entityId' => $resource['destination'], 'typeId' => $resource['type_id'], 'resId' => $args['resIdMaster']]);
        }

        $relation = 1;
        if (!empty($args['originId'])) {
            $relations = AttachmentModel::get(['select' => ['relation'], 'where' => ['origin_id = ?'], 'data' => [$args['originId']], 'orderBy' => ['relation DESC'], 'limit' => 1]);
            $relation = $relations[0]['relation'] + 1 ?? 2;
            AttachmentModel::update(['set' => ['status' => 'OBS'], 'where' => ['(origin_id = ? OR res_id = ?)'], 'data' => [$args['originId'], $args['originId']]]);
        }

        $externalId = '{}';
        if (!empty($args['externalId']) && is_array($args['externalId'])) {
            $externalId = json_encode($args['externalId']);
        }

        $preparedData = [
            'title'                 => $args['title'] ?? null,
            'identifier'            => $args['chrono'] ?? null,
            'typist'                => $GLOBALS['userId'],
            'status'                => 'A_TRA',
            'relation'              => $relation,
            'origin_id'             => $args['originId'] ?? null,
            'res_id_master'         => $args['resIdMaster'],
            'attachment_type'       => $args['type'],
            'validation_date'       => $args['validationDate'] ?? null,
            'effective_date'        => $args['effectiveDate'] ?? null,
            'in_signature_book'     => empty($args['inSignatureBook']) ? 'false' : 'true',
            'external_id'           => $externalId,
            'format'                => $args['format'],
            'docserver_id'          => $args['docserver_id'],
            'filename'              => $args['filename'],
            'filesize'              => $args['filesize'],
            'path'                  => $args['path'],
            'fingerprint'           => $args['fingerprint'],
            'creation_date'         => 'CURRENT_TIMESTAMP'
        ];

        return $preparedData;
    }

    public static function prepareUpdateAttachmentStorage(array $args, int $id)
    {
        $attachment = AttachmentModel::getById(['id' => $id, 'select' => ['identifier', 'res_id_master']]);
        $attachmentsTypes = AttachmentModel::getAttachmentsTypesByXML();
        if ($attachmentsTypes[$args['type']]['chrono'] && empty($attachment['identifier'])) {
            $resource = ResModel::getById(['select' => ['destination', 'type_id'], 'resId' => $attachment['res_id_master']]);
            $chrono = ChronoModel::getChrono(['id' => 'outgoing', 'entityId' => $resource['destination'], 'typeId' => $resource['type_id'], 'resId' => $attachment['res_id_master']]);
        }

        $preparedData = [
            'title'                 => $args['title'] ?? null,
            'attachment_type'       => $args['type'],
            'validation_date'       => $args['validationDate'] ?? null,
            'modification_date'     => 'CURRENT_TIMESTAMP'
        ];

        if (!empty($chrono)) {
            $preparedData['identifier'] = $chrono;
        }
        if (!empty($args['docserver_id'])) {
            $preparedData = array_merge($preparedData, [
                'format'                => $args['format'],
                'docserver_id'          => $args['docserver_id'],
                'filename'              => $args['filename'],
                'filesize'              => $args['filesize'],
                'path'                  => $args['path'],
                'fingerprint'           => $args['fingerprint'],
            ]);
        }

        return $preparedData;
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
