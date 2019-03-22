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
use Convert\controllers\ConvertPdfController;
use Docserver\models\DocserverModel;
use Shipping\models\ShippingTemplateModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\CurlModel;
use SrcCore\models\PasswordModel;
use SrcCore\models\ValidatorModel;


trait ShippingTrait
{
    public static function createMailevaShippings(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);
        ValidatorModel::arrayType($args, ['data']);

        //TODO remove after test
        $args['data']['shippingTemplateId'] = 1;

        $mailevaConfig = CoreConfigModel::getMailevaConfiguration();
        if (empty($mailevaConfig)) {
            //TODO
            return ['errors' => 'Maileva configuration does not exist'];
        }
        $shippingTemplate = ShippingTemplateModel::getById(['id' => $args['data']['shippingTemplateId']]);
        if (empty($shippingTemplate)) {
            //TODO
            return ['errors' => 'Shipping template does not exist'];
        }
        $shippingTemplate['options'] = json_decode($shippingTemplate['options'], true);
        $shippingTemplate['account'] = json_decode($shippingTemplate['account'], true);

        //TODO recup les bon attachments
        $attachments = AttachmentModel::getOnView(['select' => ['res_id', 'res_id_version', 'title'], 'where' => ['res_id_master = ?'], 'data' => [$args['resId']]]);
        if (empty($attachments)) {
            return true;
        }
        $attachments = [$attachments[0]]; //TODO Remove for test

        $curlAuth = CurlModel::execSimple([
            'url'           => $mailevaConfig['uri'] . '/authentication/oauth2/token',
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
            //TODO
            return ['errors' => 'Maileva authentication failed'];
        }
        $token = $curlAuth['response']['access_token'];

        $errors = [];
        foreach ($attachments as $attachment) {
            $sendingName = CoreConfigModel::uniqueId();
            if (!empty($attachment['res_id'])) {
                $isVersion = false;
                $attachmentId = $attachment['res_id'];
            } else {
                $isVersion = true;
                $attachmentId = $attachment['res_id_version'];
            }

            $createSending = CurlModel::execSimple([
                'url'           => $mailevaConfig['uri'] . '/mail/v1/sendings',
                'bearerAuth'    => ['token' => $token],
                'headers'       => ['Content-Type: application/json'],
                'method'        => 'POST',
                'body'          => ['name' => $sendingName]
            ]);
            if ($createSending['code'] != 201) {
                //TODO AttachmentId ??
                $errors[] = 'Maileva sending creation failed';
                continue;
            }

            $sendings = CurlModel::execSimple([
                'url'           => $mailevaConfig['uri'] . '/mail/v1/sendings',
                'bearerAuth'    => ['token' => $token],
                'method'        => 'GET'
            ]);
            if ($sendings['code'] != 200) {
                //TODO AttachmentId ??
                $errors[] = 'Maileva get sendings failed';
                continue;
            }

            foreach ($sendings['response']['sendings'] as $sending) {
                if ($sending['name'] == $sendingName) {
                    $sendingId = $sending['id'];
                }
            }
            if (empty($sendingId)) {
                //TODO AttachmentId ??
                $errors[] = 'Maileva sending id not found';
                continue;
            }

            $convertedDocument = ConvertPdfController::getConvertedPdfById(['resId' => $attachmentId, 'collId' => 'attachment_coll', 'isVersion' => $isVersion]);
            $docserver = DocserverModel::getByDocserverId(['docserverId' => $convertedDocument['docserver_id'], 'select' => ['path_template']]);
            if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
                //TODO AttachmentId
                $errors[] = 'Docserver does not exist';
                continue;
            }
            $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $convertedDocument['path']) . $convertedDocument['filename'];
            if (!file_exists($pathToDocument) || !is_file($pathToDocument)) {
                //TODO AttachmentId
                $errors[] = 'Document not found on docserver';
                continue;
            }

            $createDocument = CurlModel::execSimple([
                'url'           => $mailevaConfig['uri'] . "/mail/v1/sendings/{$sendingId}/documents",
                'bearerAuth'    => ['token' => $token],
                'method'        => 'POST',
                'multipartBody' => ['document' => file_get_contents($pathToDocument), 'metadata' => json_encode(['priority' => 0, 'name' => $attachment['title']])]
            ]);
            if ($createDocument['code'] != 201) {
                //TODO AttachmentId ??
                $errors[] = 'Maileva document creation failed';
                continue;
            }

            //TODO remove after test
            $curl = CurlModel::execSimple([
                'url'           => "https://api.sandbox.aws.maileva.net/mail/v1/sendings/{$sendingId}/documents",
                'bearerAuth'    => ['token' => $token],
                'method'        => 'GET'
            ]);

            //TODO Aller chercher le contact de l'attachment
            $createRecipient = CurlModel::execSimple([
                'url'           => $mailevaConfig['uri'] . "/mail/v1/sendings/{$sendingId}/recipients",
                'bearerAuth'    => ['token' => $token],
                'headers'       => ['Content-Type: application/json'],
                'method'        => 'POST',
                'body'          => [
                    "address_line_1"    => "La Poste",
                    "address_line_2"    => "Me Eva DUPONT",
                    "address_line_3"    => "Résidence des Peupliers",
                    "address_line_4"    => "33 avenue de Paris",
                    "address_line_5"    => "BP 356",
                    "address_line_6"    => "75000 Paris",
                    "country_code"      => "FR"
                ],
            ]);
            if ($createRecipient['code'] != 201) {
                //TODO AttachmentId ??
                $errors[] = 'Maileva recipient creation failed';
                continue;
            }

            $setOptions = CurlModel::execSimple([
                'url'           => $mailevaConfig['uri'] . "/mail/v1/sendings/{$sendingId}/options",
                'bearerAuth'    => ['token' => $token],
                'headers'       => ['Content-Type: application/json'],
                'method'        => 'PATCH',
                'body'          => [
                    'postage_type'              => strtoupper($shippingTemplate['options']['sendMode']),
                    'color_printing'            => in_array('color', $shippingTemplate['options']['shapingOptions']),
                    'duplex_printing'           => in_array('duplexPrinting', $shippingTemplate['options']['shapingOptions']),
                    'optional_address_sheet'    => in_array('addressPage', $shippingTemplate['options']['shapingOptions'])
                ],
            ]);
            if ($setOptions['code'] != 200) {
                //TODO AttachmentId ??
                $errors[] = 'Maileva options modification failed';
                continue;
            }

            $submit = CurlModel::execSimple([
                'url'           => $mailevaConfig['uri'] . "/mail/v1/sendings/{$sendingId}/submit",
                'bearerAuth'    => ['token' => $token],
                'headers'       => ['Content-Type: application/json'],
                'method'        => 'POST'
            ]);
            if ($submit['code'] != 200) {
                //TODO AttachmentId ??
                $errors[] = 'Maileva submit failed';
                continue;
            }
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return true;
    }
}
