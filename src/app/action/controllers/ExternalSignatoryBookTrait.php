<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ExternalSignatoryBookTrait
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Action\controllers;

use Attachment\models\AttachmentModel;
use ExternalSignatoryBook\controllers\MaarchParapheurController;
use ExternalSignatoryBook\controllers\XParaphController;
use Resource\models\ResModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;

trait ExternalSignatoryBookTrait
{
    public static function sendExternalSignatoryBookAction(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'modules/visa/xml/remoteSignatoryBooks.xml']);
        $config = [];

        if (!empty($loadedXml)) {
            $config['id'] = (string)$loadedXml->signatoryBookEnabled;
            foreach ($loadedXml->signatoryBook as $value) {
                if ($value->id == $config['id']) {
                    $config['data'] = (array)$value;
                    break;
                }
            }

            if ($config['id'] == 'ixbus') {
                // TODO
            } elseif ($config['id'] == 'iParapheur') {
                // TODO
            } elseif ($config['id'] == 'fastParapheur') {
                // TODO
            } elseif ($config['id'] == 'maarchParapheur') {
                $attachments = AttachmentModel::getOnView([
                    'select'    => [
                        'count(1) as nb'
                    ],
                    'where'     => ["res_id_master = ?", "attachment_type not in (?)", "status not in ('DEL', 'OBS', 'FRZ', 'TMP')", "in_signature_book = 'true'"],
                    'data'      => [$args['resId'], ['converted_pdf', 'incoming_mail_attachment', 'print_folder', 'signed_response']]
                ]);
                if ($attachments[0]['nb'] == 0 && $args['data']['objectSent'] == 'attachment') {
                    $noAttachmentsResource = ResModel::getExtById(['resId' => $args['resId'], 'select' => ['alt_identifier']]);
                    return ['error' => ['No attachment for this mail : ' . $noAttachmentsResource['alt_identifier']]];
                }

                $attachmentToFreeze = MaarchParapheurController::sendDatas([
                    'config'             => $config,
                    'resIdMaster'        => $args['resId'],
                    'processingUser'     => $args['data']['processingUser'],
                    'objectSent'         => $args['data']['objectSent'],
                    'userId'             => $GLOBALS['userId']
                ]);

                $processingUserInfo = MaarchParapheurController::getUserById(['config' => $config, 'id' => $args['data']['processingUser']]);
                $historyInfo = ' (à ' . $processingUserInfo['firstname'] . ' ' . $processingUserInfo['lastname'] . ')';
            } elseif ($config['id'] == 'xParaph') {
                $attachmentToFreeze = XParaphController::sendDatas([
                    'config'      => $config,
                    'resIdMaster' => $args['resId']
                ]);
            }
        }

        if (!empty($attachmentToFreeze)) {
            if (!empty($attachmentToFreeze['letterbox_coll'])) {
                ResModel::update([
                    'set' => ['external_signatory_book_id' => $attachmentToFreeze['letterbox_coll'][$args['resId']]],
                    'where' => ['res_id = ?'],
                    'data' => [$args['resId']]
                ]);
            } else {
                if (!empty($attachmentToFreeze['attachments_coll'])) {
                    foreach ($attachmentToFreeze['attachments_coll'] as $resId => $externalId) {
                        AttachmentModel::freezeAttachment([
                            'resId' => $resId,
                            'table' => 'res_attachments',
                            'externalId' => $externalId
                        ]);
                    }
                }
                if (!empty($attachmentToFreeze['attachments_version_coll'])) {
                    foreach ($attachmentToFreeze['attachments_version_coll'] as $resId => $externalId) {
                        AttachmentModel::freezeAttachment([
                            'resId' => $resId,
                            'table' => 'res_version_attachments',
                            'externalId' => $externalId
                        ]);
                    }
                }
            }

            $document = ResModel::getById(['resId' => $args['resId'], 'select' => ['status']]);
            
            if ($document['status'] == 'EVIS' || $document['status'] == 'ESIG') {
            //     $sequence    = $circuit_visa->getCurrentStep($res_id, $coll_id, 'VISA_CIRCUIT');
                $stepDetails = array();
            //     $stepDetails = $circuit_visa->getStepDetails($res_id, $coll_id, 'VISA_CIRCUIT', $sequence);

            //     $message = $circuit_visa->processVisaWorkflow(['stepDetails' => $stepDetails, 'res_id' => $res_id]);
            }
        }

        return ['history' => $historyInfo];
    }
}
