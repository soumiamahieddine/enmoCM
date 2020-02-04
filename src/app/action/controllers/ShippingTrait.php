<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   AcknowledgementReceiptTrait
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Action\controllers;

use Attachment\models\AttachmentModel;
use Contact\controllers\ContactController;
use Contact\models\ContactModel;
use Convert\controllers\ConvertPdfController;
use Convert\models\AdrModel;
use Docserver\models\DocserverModel;
use Entity\models\EntityModel;
use Resource\models\ResModel;
use Resource\models\ResourceContactModel;
use Shipping\controllers\ShippingTemplateController;
use Shipping\models\ShippingModel;
use Shipping\models\ShippingTemplateModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\CurlModel;
use SrcCore\models\PasswordModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;


trait ShippingTrait
{
    public static function createMailevaShippings(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);
        ValidatorModel::arrayType($args, ['data']);

        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $resource = ResModel::getById(['select' => ['destination', 'integrations', 'subject as title', 'external_id', 'res_id'], 'resId' => $args['resId']]);
        $integrations = json_decode($resource['integrations'], true);

        $recipientEntity = EntityModel::getByEntityId(['select' => ['id'], 'entityId' => $resource['destination']]);

        $mailevaConfig = CoreConfigModel::getMailevaConfiguration();
        if (empty($mailevaConfig)) {
            return ['errors' => ['Maileva configuration does not exist']];
        }
        $shippingTemplate = ShippingTemplateModel::getById(['id' => $args['data']['shippingTemplateId']]);
        if (empty($shippingTemplate)) {
            return ['errors' => ['Shipping template does not exist']];
        }
        $shippingTemplate['options'] = json_decode($shippingTemplate['options'], true);
        $shippingTemplate['account'] = json_decode($shippingTemplate['account'], true);
        $shippingTemplate['fee'] = json_decode($shippingTemplate['fee'], true);

        $attachments = AttachmentModel::get([
            'select'    => ['res_id', 'title', 'recipient_id', 'recipient_type', 'external_id'],
            'where'     => ['res_id_master = ?', 'in_send_attach = ?', 'status not in (?)', 'attachment_type not in (?)'],
            'data'      => [$args['resId'], true, ['OBS', 'DEL', 'TMP', 'FRZ'], ['print_folder']]
        ]);

        if (empty($attachments) && empty($integrations['inShipping'])) {
            return true;
        }

        $resourcesList = [];

        $contacts = [];
        foreach ($attachments as $attachment) {
            $attachmentId = $attachment['res_id'];

            $convertedDocument = AdrModel::getConvertedDocumentById([
                'select'    => ['docserver_id','path', 'filename', 'fingerprint'],
                'resId'     => $attachmentId,
                'collId'    => 'attachments_coll',
                'type'      => 'PDF'
            ]);
            if (empty($convertedDocument)) {
                return ['errors' => ['No conversion for attachment']];
            }
            if (empty($attachment['recipient_id']) || $attachment['recipient_type'] != 'contact') {
                return ['errors' => ['Contact is empty for attachment']];
            }
            $contact = ContactModel::getById(['select' => ['*'], 'id' => $attachment['recipient_id']]);
            if (empty($contact)) {
                return ['errors' => ['Contact does not exist for attachment']];
            }
            if (!empty($contact['address_country']) && strtoupper(trim($contact['address_country'])) != 'FRANCE') {
                return ['errors' => ['Contact country is not France']];
            }
            $afnorAddress = ContactController::getContactAfnor($contact);
            if ((empty($afnorAddress[1]) && empty($afnorAddress[2])) || empty($afnorAddress[6]) || !preg_match("/^\d{5}\s/", $afnorAddress[6])) {
                return ['errors' => ['Contact is not fill enough for attachment']];
            }
            $contacts[] = $afnorAddress;

            $attachment['type'] = 'attachments_coll';
            $resourcesList[] = $attachment;
        }

        $contactsResource = [];
        if (!empty($integrations['inShipping'])) {
            $convertedDocument = AdrModel::getConvertedDocumentById([
                'select'    => ['docserver_id','path', 'filename', 'fingerprint'],
                'resId'     => $args['resId'],
                'collId'    => 'letterbox_coll',
                'type'      => 'PDF'
            ]);
            if (empty($convertedDocument)) {
                return ['errors' => ['No conversion for resource']];
            }
            $resourceContacts = ResourceContactModel::get([
                'where' => ['res_id = ?', 'mode = ?', 'type = ?'],
                'data'  => [$args['resId'], 'recipient', 'contact']
            ]);
            if (empty($resourceContacts)) {
                return ['errors' => ['No contact found for resource']];
            }

            foreach ($resourceContacts as $resourceContact) {
                $contact = ContactModel::getById(['select' => ['*'], 'id' => $resourceContact['item_id']]);
                if (empty($contact)) {
                    return ['errors' => ['Contact does not exist for resource']];
                }
                if (!empty($contact['address_country']) && strtoupper(trim($contact['address_country'])) != 'FRANCE') {
                    return ['errors' => ['Contact country is not France']];
                }
                $afnorAddress = ContactController::getContactAfnor($contact);
                if ((empty($afnorAddress[1]) && empty($afnorAddress[2])) || empty($afnorAddress[6]) || !preg_match("/^\d{5}\s/", $afnorAddress[6])) {
                    return ['errors' => ['Contact is not filled enough for resource']];
                }
                $contactsResource[] = $afnorAddress;
            }
            $resource['type'] = 'letterbox_coll';
            $resourcesList[] = $resource;
        }

        $curlAuth = CurlModel::execSimple([
            'url'           => $mailevaConfig['connectionUri'] . '/authentication/oauth2/token',
            'basicAuth'     => ['user' => $mailevaConfig['clientId'], 'password' => $mailevaConfig['clientSecret']],
            'headers'       => ['Content-Type: application/x-www-form-urlencoded'],
            'method'        => 'POST',
            'queryParams'   => [
                'grant_type'    => 'password',
                'username'      => $shippingTemplate['account']['id'],
                'password'      => PasswordModel::decrypt(['cryptedPassword' => $shippingTemplate['account']['password']])
            ]
        ]);
        if ($curlAuth['code'] != 200) {
            return ['errors' => ['Maileva authentication failed']];
        }
        $token = $curlAuth['response']['access_token'];

        $errors = [];
        foreach ($resourcesList as $key => $resource) {
            $sendingName = CoreConfigModel::uniqueId();
            $resId = $resource['res_id'];

            $createSending = CurlModel::execSimple([
                'url'           => $mailevaConfig['uri'] . '/mail/v1/sendings',
                'bearerAuth'    => ['token' => $token],
                'headers'       => ['Content-Type: application/json'],
                'method'        => 'POST',
                'body'          => json_encode(['name' => $sendingName])
            ]);
            if ($createSending['code'] != 201) {
                $errors[] = "Maileva sending creation failed for attachment {$attachmentId}";
                continue;
            }
            foreach ($createSending['headers'] as $header) {
                if (strpos($header, 'Location:') !== false) {
                    $sendingId = strrchr($header, '/');
                    $sendingId = substr($sendingId, 1);
                    break;
                }
            }
            if (empty($sendingId)) {
                $errors[] = "Maileva sending id not found for attachment {$attachmentId}";
                continue;
            }

            $convertedDocument = ConvertPdfController::getConvertedPdfById(['resId' => $resId, 'collId' => $resource['type']]);
            $docserver = DocserverModel::getByDocserverId(['docserverId' => $convertedDocument['docserver_id'], 'select' => ['path_template']]);
            if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
                $errors[] = "Docserver does not exist for resource {$resId}";
                continue;
            }
            $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $convertedDocument['path']) . $convertedDocument['filename'];
            if (!file_exists($pathToDocument) || !is_file($pathToDocument)) {
                $errors[] = "Document not found on docserver for resource {$resId}";
                continue;
            }

            $createDocument = CurlModel::execSimple([
                'url'           => $mailevaConfig['uri'] . "/mail/v1/sendings/{$sendingId}/documents",
                'bearerAuth'    => ['token' => $token],
                'method'        => 'POST',
                'multipartBody' => ['document' => file_get_contents($pathToDocument), 'metadata' => json_encode(['priority' => 0, 'name' => $resource['title']])]
            ]);
            if ($createDocument['code'] != 201) {
                $errors[] = "Maileva document creation failed for resource {$resId}";
                continue;
            }

            if ($resource['type'] == 'attachments_coll') {
                $createRecipient = CurlModel::execSimple([
                    'url'           => $mailevaConfig['uri'] . "/mail/v1/sendings/{$sendingId}/recipients",
                    'bearerAuth'    => ['token' => $token],
                    'headers'       => ['Content-Type: application/json'],
                    'method'        => 'POST',
                    'body'          => json_encode([
                        "address_line_1"    => $contacts[$key][1],
                        "address_line_2"    => $contacts[$key][2],
                        "address_line_3"    => $contacts[$key][3],
                        "address_line_4"    => $contacts[$key][4],
                        "address_line_5"    => $contacts[$key][5],
                        "address_line_6"    => $contacts[$key][6],
                        "country_code"      => 'FR'
                    ]),
                ]);
                if ($createRecipient['code'] != 201) {
                    $errors[] = "Maileva recipient creation failed for resource {$resId}";
                    continue;
                }
            } else {
                foreach ($contactsResource as $contact) {
                    $createRecipient = CurlModel::execSimple([
                        'url'           => $mailevaConfig['uri'] . "/mail/v1/sendings/{$sendingId}/recipients",
                        'bearerAuth'    => ['token' => $token],
                        'headers'       => ['Content-Type: application/json'],
                        'method'        => 'POST',
                        'body'          => json_encode([
                            "address_line_1"    => $contact[1],
                            "address_line_2"    => $contact[2],
                            "address_line_3"    => $contact[3],
                            "address_line_4"    => $contact[4],
                            "address_line_5"    => $contact[5],
                            "address_line_6"    => $contact[6],
                            "country_code"      => 'FR'
                        ]),
                    ]);
                    if ($createRecipient['code'] != 201) {
                        $errors[] = "Maileva recipient creation failed for resource {$resId}";
                        continue 2;
                    }
                }
            }

            $setOptions = CurlModel::execSimple([
                'url'           => $mailevaConfig['uri'] . "/mail/v1/sendings/{$sendingId}/options",
                'bearerAuth'    => ['token' => $token],
                'headers'       => ['Content-Type: application/json'],
                'method'        => 'PATCH',
                'body'          => json_encode([
                    'postage_type'              => strtoupper($shippingTemplate['options']['sendMode']),
                    'color_printing'            => in_array('color', $shippingTemplate['options']['shapingOptions']),
                    'duplex_printing'           => in_array('duplexPrinting', $shippingTemplate['options']['shapingOptions']),
                    'optional_address_sheet'    => in_array('addressPage', $shippingTemplate['options']['shapingOptions'])
                ]),
            ]);
            if ($setOptions['code'] != 200) {
                $errors[] = "Maileva options modification failed for attachment {$attachmentId}";
                continue;
            }

            $submit = CurlModel::execSimple([
                'url'           => $mailevaConfig['uri'] . "/mail/v1/sendings/{$sendingId}/submit",
                'bearerAuth'    => ['token' => $token],
                'headers'       => ['Content-Type: application/json'],
                'method'        => 'POST'
            ]);
            if ($submit['code'] != 200) {
                $errors[] = "Maileva submit failed for attachment {$attachmentId}";
                continue;
            }

            $externalId = json_decode($resource['external_id'], true);
            $externalId['mailevaSendingId'] = $sendingId;
            AttachmentModel::update(['set' => ['external_id' => json_encode($externalId)], 'where' => ['res_id = ?'], 'data' => [$resId]]);

            $fee = ShippingTemplateController::calculShippingFee([
                'fee'       => $shippingTemplate['fee'],
                'resources' => [$resource]
            ]);

            $documentType = $resource['type'] == 'attachments_coll' ? 'attachment' : 'resource';
            ShippingModel::create([
                'userId'            => $currentUser['id'],
                'documentId'        => $resId,
                'documentType'      => $documentType,
                'options'           => json_encode($shippingTemplate['options']),
                'fee'               => $fee,
                'recipientEntityId' => $recipientEntity['id'],
                'accountId'         => $shippingTemplate['account']['id']
            ]);
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return true;
    }
}
