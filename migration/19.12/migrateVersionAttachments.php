<?php

require '../../vendor/autoload.php';

chdir('../..');

$customs =  scandir('custom');

foreach ($customs as $custom) {
    if ($custom == 'custom.xml' || $custom == '.' || $custom == '..') {
        continue;
    }

    \SrcCore\models\DatabasePDO::reset();
    new \SrcCore\models\DatabasePDO(['customId' => $custom]);

    \SrcCore\models\DatabaseModel::delete([
        'table' => 'res_attachments',
        'where' => ['relation > 1']
    ]);

    $migrated = 0;
    $attachmentsInfo = \SrcCore\models\DatabaseModel::select([
        'select'   => ['res_id', 'title', 'subject', 'description', 'type_id', 'format', 'typist', 'creation_date', 'fulltext_result', 'author', 'identifier', 'source', 'relation', 'doc_date', 'docserver_id', 'path', 'filename', 'offset_doc', 'fingerprint', 'filesize', 'status', 'destination', 'validation_date', 'effective_date', 'origin', 'priority', 'initiator', 'dest_user', 'res_id_master', 'attachment_type', 'dest_contact_id', 'dest_address_id', 'updated_by', 'is_multicontacts', 'in_signature_book', 'signatory_user_serial_id', 'in_send_attach', 'external_id', 'attachment_id_master'],
        'table'    => ['res_version_attachments']
    ]);

    foreach ($attachmentsInfo as $attachmentInfo) {
        $oldResId = $attachmentInfo['res_id'];
        unset($attachmentInfo['res_id']);
        $attachmentInfo['origin_id'] = $attachmentInfo['attachment_id_master'];
        unset($attachmentInfo['attachment_id_master']);
        $attachmentInfo['in_signature_book'] = empty($attachmentInfo['in_signature_book']) ?  'false' : 'true';
        $attachmentInfo['in_send_attach']    = empty($attachmentInfo['in_send_attach']) ?  'false' : 'true';
        $attachmentInfo['format']            = empty($attachmentInfo['format']) ?  pathinfo($attachmentInfo['filename'], PATHINFO_EXTENSION) : $attachmentInfo['format'];
        if (empty($attachmentInfo['fingerprint'])) {
            $docserver = \SrcCore\models\DatabaseModel::select([
                'select' => ['path_template', 'docserver_type_id'],
                'table'  => ['docservers'],
                'where'  => ['docserver_id = ?'],
                'data'   => [$attachmentInfo['docserver_id']]
            ]);
            $docserverType = \SrcCore\models\DatabaseModel::select([
                'select' => ['fingerprint_mode'],
                'table'  => ['docserver_types'],
                'where'  => ['docserver_type_id = ?'],
                'data'   => [$docserver[0]['docserver_type_id']]
            ]);
            $pathToDocument = $docserver[0]['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $attachmentInfo['path']) . $attachmentInfo['filename'];
            if (file_exists($pathToDocument)) {
                $fingerprint = \Resource\controllers\StoreController::getFingerPrint(['filePath' => $pathToDocument, 'mode' => $docserverType[0]['fingerprint_mode']]);
                $attachmentInfo['fingerprint'] = $fingerprint;
            } else {
                $attachmentInfo['fingerprint'] = 1;
            }
        }

        $newResId = \Attachment\models\AttachmentModel::create($attachmentInfo);

        migrateOrigin(['oldResId' => $oldResId, 'newResId' => $newResId]);
        migrateAdrVersionAttachments(['oldResId' => $oldResId, 'newResId' => $newResId]);
        migrateHistoryVersion(['oldResId' => $oldResId, 'newResId' => $newResId]);
        migrateEmailsVersion(['oldResId' => $oldResId, 'newResId' => $newResId]);
        migrateMessageExchangeVersion(['oldResId' => $oldResId, 'newResId' => $newResId]);
        migrateShippingVersion(['oldResId' => $oldResId, 'newResId' => $newResId]);

        $migrated++;
    }

    printf("Migration version attachement (CUSTOM {$custom}) : " . $migrated . " Version(s) trouvée(s) et migrée(s).\n");
}

function migrateOrigin($args = [])
{
    \SrcCore\models\DatabaseModel::update([
        'set'   => ['origin' => $args['newResId'] . ',res_attachments'],
        'table' => 'res_attachments',
        'where' => ['origin = ?'],
        'data'  => [$args['oldResId'] . ',res_version_attachments']
    ]);
}

function migrateAdrVersionAttachments($args = [])
{
    $adrInfos = \SrcCore\models\DatabaseModel::select(['select' => ['*'], 'table' => ['adr_attachments_version'], 'where' => ['res_id = ?'], 'data' => [$args['oldResId']]]);
    foreach ($adrInfos as $value) {
        unset($value['id']);
        $value['res_id'] = $args['newResId'];
        \SrcCore\models\DatabaseModel::insert([
            'table'         => 'adr_attachments',
            'columnsValues' => $value
        ]);
    }
}

function migrateHistoryVersion($args = [])
{
    \SrcCore\models\DatabaseModel::update([
        'set'   => ['table_name' => 'res_attachments', 'record_id' => $args['newResId']],
        'postSet' => ['info' => "REPLACE(info, '{$args['oldResId']} (res_version_attachments)', '{$args['newResId']} (res_attachments)')"],
        'table' => 'history',
        'where' => ['table_name = ?', 'record_id = ?'],
        'data'  => ['res_version_attachments', $args['oldResId']]
    ]);
}

function migrateEmailsVersion($args = [])
{
    $emails = \SrcCore\models\DatabaseModel::select([
        'select' => ['id', 'document'],
        'table' => ['emails'],
        'where' => ['document->\'attachments\' @> ?'],
        'data' => ['[{"id":'.$args['oldResId'].', "isVersion":true}]']
    ]);

    foreach ($emails as $email) {
        $document = json_decode($email['document'], true);
        foreach ($document['attachments'] as $key => $attachment) {
            if ($attachment['id'] == $args['oldResId'] && $attachment['isVersion']) {
                $document['attachments'][$key]['id'] = $args['newResId'];
                unset($document['attachments'][$key]['isVersion']);
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
    \SrcCore\models\DatabaseModel::update([
        'set'   => ['document' => json_encode($document)],
        'table' => 'emails',
        'where' => ['id = ?'],
        'data'  => [$email['id']]
    ]);
}

function migrateMessageExchangeVersion($args = [])
{
    \SrcCore\models\DatabaseModel::update([
        'set'   => ['res_id' => $args['newResId'], 'tablename' => 'res_attachments'],
        'table' => 'unit_identifier',
        'where' => ['res_id = ?', 'tablename = ?'],
        'data'  => [$args['oldResId'], 'res_version_attachments']
    ]);
}

function migrateShippingVersion($args = [])
{
    \SrcCore\models\DatabaseModel::update([
        'set'   => ['attachment_id' => $args['newResId'], 'is_version' => 'false'],
        'table' => 'shippings',
        'where' => ['attachment_id = ?', 'is_version = ?'],
        'data'  => [$args['oldResId'], 'true']
    ]);
}
