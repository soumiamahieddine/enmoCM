<?php

use Convert\controllers\ConvertPdfController;
use Convert\controllers\ConvertThumbnailController;
use Convert\models\AdrModel;
use Docserver\controllers\DocserverController;
use Resource\models\ResModel;
use SrcCore\models\CoreConfigModel;

require '../../vendor/autoload.php';

chdir('../..');

$customs =  scandir('custom');

foreach ($customs as $custom) {
    if ($custom == 'custom.xml' || $custom == '.' || $custom == '..') {
        continue;
    }

    \SrcCore\models\DatabasePDO::reset();
    new \SrcCore\models\DatabasePDO(['customId' => $custom]);

    $migrated = 0;
    $attachmentsInfo = \SrcCore\models\DatabaseModel::select([
        'select' => ['l.category_id', 'a.res_id', 'a.relation', 'a.docserver_id', 'a.path', 'a.filename', 'a.format', 'a.res_id_master', 'a.in_signature_book',
                        'a.in_send_attach', 'a.external_id', 'a.attachment_type', 'a.origin_id', 'a.external_id', 'l.external_id as letterbox_external_id'],
        'table'  => ['res_attachments a, res_letterbox l'],
        'where'  => ['attachment_type = ?', 'a.status not in (?)', 'a.res_id_master = l.res_id', 'category_id = ?'],
        'data'   => ['outgoing_mail', ['DEL', 'TMP', 'OBS'], 'outgoing'],
        'orderBy' => ['a.res_id desc']
    ]);

    $superadmin = \User\models\UserModel::getByLogin(['select' => ['id'], 'login' => 'superadmin']);
    if (empty($superadmin)) {
        $firstMan = \User\models\UserModel::get(['select' => ['id'], 'orderBy' => ['id'], 'limit' => 1]);
        $masterOwnerId = $firstMan[0]['id'];
    } else {
        $masterOwnerId = $superadmin['id'];
    }

    $tmpPath = CoreConfigModel::getTmpPath();

    $previousResId = 0;
    foreach ($attachmentsInfo as $attachmentInfo) {
        if ($previousResId == $attachmentInfo['res_id_master']) {
            continue;
        }
        $previousResId = $attachmentInfo['res_id_master'];

        $inSignatureBook = empty($attachmentInfo['in_signature_book']) ?  'false' : 'true';
        $inSendAttach    = empty($attachmentInfo['in_send_attach']) ?  'false' : 'true';
        $format          = empty($attachmentInfo['format']) ?  pathinfo($attachmentInfo['filename'], PATHINFO_EXTENSION) : $attachmentInfo['format'];
        
        $docserver = \SrcCore\models\DatabaseModel::select([
            'select' => ['path_template', 'docserver_type_id'],
            'table'  => ['docservers'],
            'where'  => ['docserver_id = ?'],
            'data'   => [$attachmentInfo['docserver_id']]
        ]);

        $pathToDocument = $docserver[0]['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $attachmentInfo['path']) . $attachmentInfo['filename'];

        if (!file_exists($pathToDocument)) {
            echo "Le document avec res_attachments.res_id = " . $attachmentInfo['res_id']
                . " n'a pas été trouvé sur le docserver (path = '" . $pathToDocument . "')"
                . ", il n'est donc pas migré\n";
            continue;
        } else {
            $resource = file_get_contents($pathToDocument);
            $storeResult = DocserverController::storeResourceOnDocServer([
                'collId'            => 'letterbox_coll',
                'docserverTypeId'   => 'DOC',
                'encodedResource'   => base64_encode($resource),
                'format'            => $format
            ]);
            if (!empty($storeResult['errors'])) {
                echo "[ConvertPdf] {$storeResult['errors']}";
            }

            if (!empty($resource)) {
                \SrcCore\models\DatabaseModel::delete([
                    'table' => 'adr_letterbox',
                    'where' => ['res_id = ?'],
                    'data'  => [$attachmentInfo['res_id_master']]
                ]);

                $integration = [];
                $integration['in_signature_book'] = empty($attachmentInfo['in_signature_book']) ?  'false' : 'true';
                $integration['in_send_attach']    = empty($attachmentInfo['in_send_attach']) ?  'false' : 'true';
                $attachmentExternalId = json_decode($attachmentInfo['external_id'], true);
                $externalId           = json_decode($attachmentInfo['letterbox_external_id'], true);
                $externalId           = array_merge($externalId, $attachmentExternalId);
                ResModel::update([
                    'set' => [
                        'docserver_id' => $storeResult['docserver_id'],
                        'path'         => $storeResult['destination_dir'],
                        'filename'     => $storeResult['file_destination_name'],
                        'fingerprint'  => $storeResult['fingerPrint'],
                        'filesize'     => $storeResult['fileSize'],
                        'version'      => $attachmentInfo['relation'],
                        'integrations' => json_encode($integration),
                        'external_id'  => json_encode($externalId)
                    ],
                    'where' => ['res_id = ?'],
                    'data'  => [$attachmentInfo['res_id_master']]
                ]);

                ConvertPdfController::convert([
                    'resId'   => $attachmentInfo['res_id_master'],
                    'collId'  => 'letterbox_coll',
                    'version' => $attachmentInfo['relation']
                ]);
                ConvertThumbnailController::convert(['type' => 'resource', 'resId' => $attachmentInfo['res_id_master']]);
    
                $customId = empty($custom) ? 'null' : $custom;
                exec("php src/app/convert/scripts/FullTextScript.php --customId {$customId} --resId {$attachmentInfo['res_id_master']} --collId letterbox_coll --userId {$masterOwnerId} > /dev/null &");
    
                if ($attachmentInfo['relation'] > 1) {
                    $attachmentsVersion = \SrcCore\models\DatabaseModel::select([
                        'select'  => ['res_id', 'relation', 'docserver_id', 'path', 'filename', 'format', 'res_id_master', 'attachment_type'],
                        'table'   => ['res_attachments'],
                        'where'   => ['(origin_id = ? or res_id = ?)', 'relation < ?'],
                        'data'    => [$attachmentInfo['origin_id'], $attachmentInfo['origin_id'], $attachmentInfo['relation']],
                        'orderBy' => ['relation asc']
                    ]);

                    $attachmentToDelete = [];
                    foreach ($attachmentsVersion as $attachmentVersion) {
                        $attachmentVersion[0]['adrType'] = 'PDF';
                        $attachmentVersion[0]['relation'] = $attachmentVersion['relation'];
                        addOutgoingMailSignedInAdr($attachmentVersion[0]);
                    
                        $docserver = \SrcCore\models\DatabaseModel::select([
                            'select' => ['path_template', 'docserver_type_id'],
                            'table'  => ['docservers'],
                            'where'  => ['docserver_id = ?'],
                            'data'   => [$attachmentVersion['docserver_id']]
                        ]);
                    
                        $pathToDocument = $docserver[0]['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $attachmentVersion['path']) . $attachmentVersion['filename'];
                        $resource = file_get_contents($pathToDocument);
                        $storeResult = DocserverController::storeResourceOnDocServer([
                            'collId'            => 'letterbox_coll',
                            'docserverTypeId'   => 'TNL',
                            'encodedResource'   => base64_encode($resource),
                            'format'            => $attachmentVersion['format']
                        ]);
    
                        AdrModel::createDocumentAdr([
                            'resId'         => $attachmentInfo['res_id_master'],
                            'type'          => 'TNL',
                            'docserverId'   => $storeResult['docserver_id'],
                            'path'          => $storeResult['destination_dir'],
                            'filename'      => $storeResult['file_destination_name'],
                            'version'       => $attachmentVersion['relation'],
                            'fingerprint'   => $storeResult['fingerPrint']
                        ]);

                        unlink($pathToDocument);
                        unlink($tmpPath.$fileNameOnTmp.'.png');
                        $attachmentToDelete[] = $attachmentVersion['res_id'];
                    }
                }
                $outgoingMailSigned = \SrcCore\models\DatabaseModel::select([
                    'select'  => ['res_id', 'relation', 'docserver_id', 'path', 'filename', 'format', 'res_id_master', 'attachment_type'],
                    'table'   => ['res_attachments'],
                    'where'   => ['attachment_type = ?', 'res_id_master = ?', 'status not in (?)'],
                    'data'    => ['outgoing_mail_signed', $attachmentInfo['res_id_master'], ['DEL']],
                    'orderBy' => ['res_id desc'],
                    'limit'   => 1
                ]);
                if (!empty($outgoingMailSigned)) {
                    // Version signée outgoing_mail_signed
                    $outgoingMailSigned[0]['adrType'] = 'SIGN';
                    $outgoingMailSigned[0]['relation'] = $attachmentInfo['relation'];
                    addOutgoingMailSignedInAdr($outgoingMailSigned[0]);
                    $attachmentToDelete[] = $outgoingMailSigned[0]['res_id'];
                } else {
                    $signedResponse = \SrcCore\models\DatabaseModel::select([
                        'select'  => ['res_id', 'relation', 'docserver_id', 'path', 'filename', 'format', 'res_id_master', 'attachment_type'],
                        'table'   => ['res_attachments'],
                        'where'   => ['attachment_type = ?', 'origin = ?', 'status not in (?)'],
                        'data'    => ['signed_response', $attachmentInfo['res_id_master'].',res_attachments', ['DEL']],
                        'orderBy' => ['res_id desc'],
                        'limit'   => 1
                    ]);
                    if (!empty($signedResponse)) {
                        // Réponse signée signed_response
                        $signedResponse[0]['adrType'] = 'SIGN';
                        $signedResponse[0]['relation'] = $attachmentInfo['relation'];
                        addOutgoingMailSignedInAdr($signedResponse[0]);
                        $attachmentToDelete[] = $signedResponse[0]['res_id'];
                    }
                }
                $attachmentToDelete[] = $attachmentInfo['res_id'];
            }

            $outgoigAnnotated = \SrcCore\models\DatabaseModel::select([
                'select'  => ['res_id', 'relation', 'docserver_id', 'path', 'filename', 'format', 'res_id_master', 'attachment_type'],
                'table'   => ['res_attachments'],
                'where'   => ['attachment_type = ?', 'res_id_master = ?', 'status not in (?)'],
                'data'    => ['document_with_notes', $attachmentInfo['res_id_master'], ['DEL']],
                'orderBy' => ['res_id desc'],
                'limit'   => 1
            ]);
            if (!empty($outgoigAnnotated)) {
                // Version annotée
                $outgoigAnnotated[0]['adrType'] = 'NOTE';
                $outgoigAnnotated[0]['relation'] = empty($resource) ? 1 : $attachmentInfo['relation'];
                addOutgoingMailSignedInAdr($outgoigAnnotated[0]);
                $attachmentToDelete[] = $outgoigAnnotated[0]['res_id'];
            }
            
            // migrateHistoryVersion(['oldResId' => $attachmentInfo['res_id'], 'newResId' => $attachmentInfo['res_id_master']]);
            // migrateEmailsVersion(['oldResId' => $attachmentInfo['res_id'], 'newResId' => $attachmentInfo['res_id_master']]);
            // migrateMessageExchangeVersion(['oldResId' => $attachmentInfo['res_id'], 'newResId' => $attachmentInfo['res_id_master']]);
            // migrateShippingVersion(['oldResId' => $attachmentInfo['res_id'], 'newResId' => $attachmentInfo['res_id_master']]);
            $migrated++;
        }
    }

    // \SrcCore\models\DatabaseModel::delete([
    //     'table' => 'res_attachments',
    //     'where' => ['res_id in (?)'],
    //     'data'  => [$attachmentToDelete]
    // ]);

    // \SrcCore\models\DatabaseModel::delete([
    //     'table' => 'adr_attachments',
    //     'where' => ['res_id in (?)'],
    //     'data'  => [$attachmentToDelete]
    // ]);

    printf("Migration outgoing_mail, outgoing_mail_signed (CUSTOM {$custom}) : " . $migrated . " courier(s) départ(s) trouvé(s) et migré(s).\n");
}

function addOutgoingMailSignedInAdr($args = [])
{
    $tmpPath = CoreConfigModel::getTmpPath();

    $formatVersion = empty($args['format']) ?  pathinfo($args['filename'], PATHINFO_EXTENSION) : $args['format'];
    $fileNameOnTmp = 'migrationOutgoingAttachmentVersion_' . rand() . '_' . rand();

    $docserver = \SrcCore\models\DatabaseModel::select([
        'select' => ['path_template', 'docserver_type_id'],
        'table'  => ['docservers'],
        'where'  => ['docserver_id = ?'],
        'data'   => [$args['docserver_id']]
    ]);

    $pathToDocument = $docserver[0]['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $args['path']) . $args['filename'];
    if (file_exists($pathToDocument)) {
        copy($pathToDocument, $tmpPath.$fileNameOnTmp.'.'.$formatVersion);
    
        if (strtolower($formatVersion) != 'pdf') {
            $command = "timeout 30 unoconv -f pdf " . escapeshellarg($tmpPath.$fileNameOnTmp.'.'.$formatVersion);
            exec('export HOME=' . $tmpPath . ' && '.$command, $output, $return);
    
            if (!file_exists($tmpPath.$fileNameOnTmp.'.pdf')) {
                echo '[ConvertPdf]  Conversion failed ! '. implode(" ", $output);
                return '';
            }
        }
    
        $resource = file_get_contents("{$tmpPath}{$fileNameOnTmp}.pdf");
        $storeResult = DocserverController::storeResourceOnDocServer([
            'collId'            => 'letterbox_coll',
            'docserverTypeId'   => 'CONVERT',
            'encodedResource'   => base64_encode($resource),
            'format'            => 'pdf'
        ]);
    
        AdrModel::createDocumentAdr([
            'resId'         => $args['res_id_master'],
            'type'          => $args['adrType'],
            'docserverId'   => $storeResult['docserver_id'],
            'path'          => $storeResult['destination_dir'],
            'filename'      => $storeResult['file_destination_name'],
            'version'       => $args['relation'],
            'fingerprint'   => $storeResult['fingerPrint']
        ]);
    
        if (in_array($args['adrType'], ['SIGN', 'NOTE'])) {
            \SrcCore\models\DatabaseModel::delete([
                'table' => 'adr_letterbox',
                'where' => ['res_id = ?', 'type = ?', 'version = ?'],
                'data'  => [$args['res_id_master'], 'TNL', $args['relation']]
            ]);
        }
    
        unlink($tmpPath.$fileNameOnTmp.'.'.$formatVersion);
        unlink($tmpPath.$fileNameOnTmp.'.pdf');
    }
}

function migrateHistoryVersion($args = [])
{
    \SrcCore\models\DatabaseModel::update([
        'postSet' => ['info' => "REPLACE(info, '{$args['oldResId']} (res_attachments)', '{$args['newResId']} (res_letterbox)')"],
        'table'   => 'history',
        'where'   => ['table_name = ?', 'record_id = ?'],
        'data'    => ['res_attachments', $args['oldResId']]
    ]);
}

function migrateEmailsVersion($args = [])
{
    $emails = \SrcCore\models\DatabaseModel::select([
        'select' => ['id', 'document'],
        'table' => ['emails'],
        'where' => ['document->\'attachments\' @> ?'],
        'data' => ['[{"id":'.$args['oldResId'].'}]']
    ]);

    foreach ($emails as $email) {
        $document = json_decode($email['document'], true);
        foreach ($document['attachments'] as $key => $attachment) {
            if ($attachment['id'] == $args['oldResId']) {
                $document['isLinked'] = true;
                $document['original'] = $attachment['original'];
                unset($document['attachments'][$key]);
                break;
            }
        }
        \SrcCore\models\DatabaseModel::update([
            'set'   => ['document' => json_encode($document)],
            'table' => 'emails',
            'where' => ['id = ?'],
            'data'  => [$email['id']]
        ]);
    }
}

function migrateMessageExchangeVersion($args = [])
{
    \SrcCore\models\DatabaseModel::update([
        'set'   => ['res_id' => $args['newResId'], 'tablename' => 'res_letterbox'],
        'table' => 'unit_identifier',
        'where' => ['res_id = ?', 'tablename = ?'],
        'data'  => [$args['oldResId'], 'res_attachments']
    ]);
}

function migrateShippingVersion($args = [])
{
    \SrcCore\models\DatabaseModel::update([
        'set'   => ['resource_id' => $args['newResId'], 'resource_type' => 'res_letterbox'],
        'table' => 'shippings',
        'where' => ['resource_id = ?', 'resource_type = ?'],
        'data'  => [$args['oldResId'], 'res_attachment']
    ]);
}
