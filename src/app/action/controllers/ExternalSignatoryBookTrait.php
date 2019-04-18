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
use Entity\models\ListInstanceModel;
use ExternalSignatoryBook\controllers\MaarchParapheurController;
use ExternalSignatoryBook\controllers\XParaphController;
use Resource\models\ResModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

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
                    'where'     => ["res_id_master = ?", "attachment_type not in (?)", "status not in ('DEL', 'OBS', 'FRZ', 'TMP', 'SEND_MASS')", "in_signature_book = 'true'"],
                    'data'      => [$args['resId'], ['converted_pdf', 'print_folder', 'signed_response']]
                ]);
                if ($attachments[0]['nb'] == 0 && $args['data']['objectSent'] == 'attachment') {
                    $noAttachmentsResource = ResModel::getExtById(['resId' => $args['resId'], 'select' => ['alt_identifier']]);
                    return ['errors' => ['No attachment for this mail : ' . $noAttachmentsResource['alt_identifier']]];
                }

                $processingUserInfo = MaarchParapheurController::getUserById(['config' => $config, 'id' => $args['data']['processingUser']]);
                $sendedInfo = MaarchParapheurController::sendDatas([
                    'config'           => $config,
                    'resIdMaster'      => $args['resId'],
                    'processingUser'   => $processingUserInfo['login'],
                    'objectSent'       => $args['data']['objectSent'],
                    'userId'           => $GLOBALS['userId']
                ]);
                if (!empty($sendedInfo['error'])) {
                    return ['errors' => [$sendedInfo['error']]];
                } else {
                    $attachmentToFreeze = $sendedInfo['sended'];
                }

                $historyInfo = ' (à ' . $processingUserInfo['firstname'] . ' ' . $processingUserInfo['lastname'] . ')';
            } elseif ($config['id'] == 'xParaph') {
                $attachments = AttachmentModel::getOnView([
                    'select'    => [
                        'count(1) as nb'
                    ],
                    'where'     => ["res_id_master = ?", "attachment_type not in (?)", "status not in ('DEL', 'OBS', 'FRZ', 'TMP', 'SEND_MASS')", "in_signature_book = 'true'"],
                    'data'      => [$args['resId'], ['converted_pdf', 'print_folder', 'signed_response']]
                ]);
                if ($attachments[0]['nb'] == 0) {
                    $noAttachmentsResource = ResModel::getExtById(['resId' => $args['resId'], 'select' => ['alt_identifier']]);
                    return ['errors' => ['No attachment for this mail : ' . $noAttachmentsResource['alt_identifier']]];
                }

                $sendedInfo = XParaphController::sendDatas([
                    'config'      => $config,
                    'resIdMaster' => $args['resId'],
                    'info'        => $args['data']['info'],
                    'steps'       => $args['data']['steps'],
                ]);
                if (!empty($sendedInfo['error'])) {
                    return ['errors' => [$sendedInfo['error']]];
                } else {
                    $attachmentToFreeze = $sendedInfo['sended'];
                }
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
                $stepDetails = ListInstanceModel::getCurrentStepByResId(['resId' => $args['resId']]);

                if (!empty($stepDetails) && $stepDetails['item_id'] != $GLOBALS['userId']) {
                    ListInstanceModel::update([
                        'set'   => ['process_date' => 'CURRENT_TIMESTAMP'],
                        'where' => ['listinstance_id = ?', 'item_mode = ?', 'res_id = ?', 'item_id = ?', 'difflist_type = ?'],
                        'data'  => [$stepDetails['listinstance_id'], $stepDetails['item_mode'], $args['resId'], $stepDetails['item_id'], 'VISA_CIRCUIT']
                    ]);

                    $currentUserInfo = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['firstname', 'lastname']]);
                    $visaUserInfo = UserModel::getByLogin(['login' => $stepDetails['item_id'], 'select' => ['firstname', 'lastname']]);
        
                    $historyInfo = ' ' . _VISA_BY . ' ' . $currentUserInfo['firstname'] . ' ' . $currentUserInfo['lastname'] . ' ' . _INSTEAD_OF . ' ' . $visaUserInfo['firstname'] . ' ' . $visaUserInfo['lastname'];
                } elseif (!empty($stepDetails)) {
                    ListInstanceModel::update([
                        'set'   => ['process_date' => 'CURRENT_TIMESTAMP'],
                        'where' => ['listinstance_id = ?', 'item_mode = ?', 'res_id = ?', 'item_id = ?', 'difflist_type = ?'],
                        'data'  => [$stepDetails['listinstance_id'], $stepDetails['item_mode'], $args['resId'], $GLOBALS['userId'], 'VISA_CIRCUIT']
                    ]);

                    $historyInfo = '';
                }
            }
        }

        return ['history' => $historyInfo];
    }
}
