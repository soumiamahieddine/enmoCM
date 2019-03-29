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

use SrcCore\models\ValidatorModel;
use Attachment\models\AttachmentModel;
use ExternalSignatoryBook\controllers\XParaphController;
use Resource\models\ResModel;
use SrcCore\models\CoreConfigModel;

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
                // TODO
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
                    'set' => ['external_signatory_book_id' => $attachmentToFreeze['letterbox_coll'][$res_id]],
                    'where' => ['res_id = ?'],
                    'data' => [$res_id]
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

            // $stmt     = $db->query('SELECT status FROM res_letterbox WHERE res_id = ?', array($res_id));
            // $resource = $stmt->fetchObject();
            
            // if ($resource->status == 'EVIS' || $resource->status == 'ESIG') {
            //     $sequence    = $circuit_visa->getCurrentStep($res_id, $coll_id, 'VISA_CIRCUIT');
            //     $stepDetails = array();
            //     $stepDetails = $circuit_visa->getStepDetails($res_id, $coll_id, 'VISA_CIRCUIT', $sequence);

            //     $message = $circuit_visa->processVisaWorkflow(['stepDetails' => $stepDetails, 'res_id' => $res_id]);
            // }
        }

        return true;
    }
}
