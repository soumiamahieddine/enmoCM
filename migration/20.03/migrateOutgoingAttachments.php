<?php

use Convert\models\AdrModel;
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
        'select' => ['l.category_id', 'a.res_id', 'a.relation', 'a.docserver_id', 'a.path', 'a.filename', 'a.filesize', 'a.format', 'a.res_id_master', 'a.in_signature_book',
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
    $attachmentToDelete = [];
    foreach ($attachmentsInfo as $attachmentInfo) {
        if ($previousResId == $attachmentInfo['res_id_master']) {
            continue;
        }
        $previousResId = $attachmentInfo['res_id_master'];

        $convertedDocument = \SrcCore\models\DatabaseModel::select([
            'select'    => ['docserver_id','path', 'filename', 'fingerprint'],
            'table'     => ['adr_attachments'],
            'where'     => ['res_id = ?', 'type = ?'],
            'data'      => [$attachmentInfo['res_id'], 'PDF'],
        ]);

        if (!empty($convertedDocument)) {
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

            $attachmentExternalId = empty($attachmentExternalId) ? [] : $attachmentExternalId;
            $externalId           = empty($externalId) ? [] : $externalId;
            $externalId           = array_merge($externalId, $attachmentExternalId);
            ResModel::update([
                'set' => [
                    'docserver_id' => $attachmentInfo['docserver_id'],
                    'path'         => $attachmentInfo['path'],
                    'filename'     => $attachmentInfo['filename'],
                    'fingerprint'  => $attachmentInfo['fingerprint'],
                    'filesize'     => $attachmentInfo['filesize'],
                    'version'      => $attachmentInfo['relation'],
                    'integrations' => json_encode($integration),
                    'external_id'  => json_encode($externalId)
                ],
                'where' => ['res_id = ?'],
                'data'  => [$attachmentInfo['res_id_master']]
            ]);

            AdrModel::createDocumentAdr([
                'resId'         => $attachmentInfo['res_id_master'],
                'type'          => 'PDF',
                'docserverId'   => $convertedDocument[0]['docserver_id'],
                'path'          => $convertedDocument[0]['path'],
                'filename'      => $convertedDocument[0]['filename'],
                'version'       => $attachmentInfo['relation'],
                'fingerprint'   => $convertedDocument[0]['fingerprint']
            ]);

            $thumbnailDocument = \SrcCore\models\DatabaseModel::select([
                'select'    => ['docserver_id','path', 'filename', 'fingerprint'],
                'table'     => ['adr_attachments'],
                'where'     => ['res_id = ?', 'type = ?'],
                'data'      => [$attachmentInfo['res_id'], 'TNL'],
            ]);
            if (!empty($thumbnailDocument)) {
                AdrModel::createDocumentAdr([
                    'resId'         => $attachmentInfo['res_id_master'],
                    'type'          => 'TNL',
                    'docserverId'   => $thumbnailDocument[0]['docserver_id'],
                    'path'          => $thumbnailDocument[0]['path'],
                    'filename'      => $thumbnailDocument[0]['filename'],
                    'version'       => $attachmentInfo['relation'],
                    'fingerprint'   => $thumbnailDocument[0]['fingerprint']
                ]);
            }
    
            $attachmentToDelete[] = $attachmentInfo['res_id'];
            $customId = empty($custom) ? 'null' : $custom;
            exec("php src/app/convert/scripts/FullTextScript.php --customId {$customId} --resId {$attachmentInfo['res_id_master']} --collId letterbox_coll --userId {$masterOwnerId} > /dev/null &");
    
            if ($attachmentInfo['relation'] > 1) {
                $attachmentsVersion = \SrcCore\models\DatabaseModel::select([
                        'select'  => ['res_id', 'relation', 'docserver_id', 'path', 'filename', 'fingerprint', 'format', 'res_id_master', 'attachment_type'],
                        'table'   => ['res_attachments'],
                        'where'   => ['(origin_id = ? or res_id = ?)', 'relation < ?'],
                        'data'    => [$attachmentInfo['origin_id'], $attachmentInfo['origin_id'], $attachmentInfo['relation']],
                        'orderBy' => ['relation asc']
                    ]);

                foreach ($attachmentsVersion as $attachmentVersion) {
                    $attachmentVersion[0]['adrType'] = 'PDF';
                    $attachmentVersion[0]['relation'] = $attachmentVersion['relation'];
                    addOutgoingMailSignedInAdr($attachmentVersion[0]);
                    $thumbnailDocument = \SrcCore\models\DatabaseModel::select([
                        'select'    => ['docserver_id','path', 'filename', 'fingerprint'],
                        'table'     => ['adr_attachments'],
                        'where'     => ['res_id = ?', 'type = ?'],
                        'data'      => [$attachmentVersion['res_id'], 'TNL'],
                    ]);
                    if (!empty($thumbnailDocument)) {
                        AdrModel::createDocumentAdr([
                            'resId'         => $attachmentInfo['res_id_master'],
                            'type'          => 'TNL',
                            'docserverId'   => $thumbnailDocument[0]['docserver_id'],
                            'path'          => $thumbnailDocument[0]['path'],
                            'filename'      => $thumbnailDocument[0]['filename'],
                            'version'       => $attachmentVersion['relation'],
                            'fingerprint'   => $thumbnailDocument[0]['fingerprint']
                        ]);
                    }
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
            
            // migrateHistoryVersion(['oldResId' => $attachmentInfo['res_id'], 'newResId' => $attachmentInfo['res_id_master']]);
            // migrateEmailsVersion(['oldResId' => $attachmentInfo['res_id'], 'newResId' => $attachmentInfo['res_id_master']]);
            // migrateMessageExchangeVersion(['oldResId' => $attachmentInfo['res_id'], 'newResId' => $attachmentInfo['res_id_master']]);
            // migrateShippingVersion(['oldResId' => $attachmentInfo['res_id'], 'newResId' => $attachmentInfo['res_id_master']]);
        }
        $migrated++;
    }

    // Version annotée
    $outgoigAnnotated = \SrcCore\models\DatabaseModel::select([
        'select'  => ['l.version', 'a.res_id', 'a.docserver_id', 'a.path', 'a.filename', 'a.format', 'a.res_id_master', 'a.attachment_type'],
        'table'   => ['res_attachments a, res_letterbox l'],
        'where'   => ['a.attachment_type = ?', 'a.status in (?)', 'category_id = ?', 'a.res_id_master = l.res_id'],
        'data'    => ['document_with_notes', ['A_TRA', 'TRA'], 'incoming'],
        'orderBy' => ['res_id desc']
    ]);

    $documentWithNote = 0;
    $previousResId    = 0;
    foreach ($outgoigAnnotated as $document) {
        if ($previousResId == $document['res_id_master']) {
            continue;
        }
        $previousResId = $document['res_id_master'];
        $document['adrType']  = 'NOTE';
        $document['relation'] = $document['version'];
        addOutgoingMailSignedInAdr($document);
        $attachmentToDelete[] = $document['res_id'];
        // migrateHistoryVersion(['oldResId' => $document['res_id'], 'newResId' => $document['res_id_master']]);
        // migrateEmailsVersion(['oldResId' => $document['res_id'], 'newResId' => $document['res_id_master']]);
        // migrateMessageExchangeVersion(['oldResId' => $document['res_id'], 'newResId' => $document['res_id_master']]);
        // migrateShippingVersion(['oldResId' => $document['res_id'], 'newResId' => $document['res_id_master']]);
        $documentWithNote++;
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

    printf("Migration outgoing_mail, outgoing_mail_signed (CUSTOM {$custom}) : " . $migrated . " courier(s) départ(s) trouvé(s) et migré(s). ".$documentWithNote." courrier(s) annoté(s)\n");
}

function addOutgoingMailSignedInAdr($args = [])
{
    $convertedDocument = \SrcCore\models\DatabaseModel::select([
        'select'    => ['docserver_id','path', 'filename', 'fingerprint'],
        'table'     => ['adr_attachments'],
        'where'     => ['res_id = ?', 'type = ?'],
        'data'      => [$args['res_id'], 'PDF'],
    ]);

    if (!empty($convertedDocument)) {
        \SrcCore\models\DatabaseModel::delete([
            'table' => 'adr_letterbox',
            'where' => ['res_id = ?', 'type = ?', 'version = ?'],
            'data'  => [$args['res_id_master'], $args['adrType'], $args['relation']]
        ]);
        AdrModel::createDocumentAdr([
            'resId'       => $args['res_id_master'],
            'type'        => $args['adrType'],
            'docserverId' => $convertedDocument[0]['docserver_id'],
            'path'        => $convertedDocument[0]['path'],
            'filename'    => $convertedDocument[0]['filename'],
            'version'     => $args['relation'],
            'fingerprint' => $convertedDocument[0]['fingerprint']
        ]);
    
        if ($args['adrType'] == 'SIGN') {
            \SrcCore\models\DatabaseModel::delete([
                'table' => 'adr_letterbox',
                'where' => ['res_id = ?', 'type = ?', 'version = ?'],
                'data'  => [$args['res_id_master'], 'TNL', $args['relation']]
            ]);
        }
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
