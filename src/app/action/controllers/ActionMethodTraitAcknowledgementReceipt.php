<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ActionController
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Action\controllers;

use AcknowledgementReceipt\models\AcknowledgementReceiptModel;
use Contact\models\ContactModel;
use ContentManagement\controllers\MergeController;
use Convert\controllers\ConvertPdfController;
use Docserver\controllers\DocserverController;
use Docserver\models\DocserverModel;
use Doctype\models\DoctypeExtModel;
use Resource\models\ResModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use Template\models\TemplateModel;
use User\models\UserModel;


trait ActionMethodTraitAcknowledgementReceipt
{
    public static function createAcknowledgementReceipts(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        $ext = ResModel::getExtById(['select' => ['category_id', 'address_id', 'is_multicontacts'], 'resId' => $aArgs['resId']]);
        if (empty($ext) || $ext['category_id'] != 'incoming') {
            return [];
        }

        $contactsToProcess = [];
        if ($ext['is_multicontacts'] == 'Y') {
            $multiContacts = DatabaseModel::select([
                'select'    => ['address_id'],
                'table'     => ['contacts_res'],
                'where'     => ['res_id = ?', 'mode = ?', 'address_id != ?'],
                'data'      => [$aArgs['resId'], 'multi', 0]
            ]);
            foreach ($multiContacts as $multiContact) {
                $contactsToProcess[] = $multiContact['address_id'];
            }
        } else {
            $contactsToProcess[] = $ext['address_id'];
        }

        if (empty($contactsToProcess)) {
            return [];
        }

        $resource = ResModel::getById(['select' => ['type_id', 'destination'], 'resId' => $aArgs['resId']]);
        $doctype = DoctypeExtModel::getById(['id' => $resource['type_id'], 'select' => ['process_mode']]);

        if ($doctype['type_id'] == 'SVA') {
            $templateAttachmentType = 'sva';
        } elseif ($doctype['type_id'] == 'SVR') {
            $templateAttachmentType = 'svr';
        } else {
            $templateAttachmentType = 'simple';
        }
        $template = TemplateModel::getWithAssociation([
            'select'    => ['template_content', 'template_path', 'template_file_name'],
            'where'     => ['templates.template_id = templates_association.template_id', 'template_target = ?', 'template_attachment_type = ?', 'value_field = ?'],
            'data'      => ['acknowledgementReceipt', $templateAttachmentType, $resource['destination']]
        ]);
        if (empty($template[0])) {
            return [];
        }

        $docserver = DocserverModel::getByDocserverId(['docserverId' => 'TEMPLATES', 'select' => ['path_template']]);
        $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $template[0]['template_path']) . $template[0]['template_file_name'];

        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $ids = [];
        foreach ($contactsToProcess as $contactToProcess) {
            $contact = ContactModel::getByAddressId(['addressId' => $contactToProcess, 'select' => ['email', 'address_street', 'address_town', 'address_postal_code']]);

            if (empty($contact['address_street']) || empty($contact['address_town']) || empty($contact['address_postal_code'])) {
                //TODO rollback
                return [];
            }

            if (!empty($contact['email'])) {
                if (empty($template[0]['template_content'])) {
                    //TODO rollback
                    return [];
                }
                $mergedDocument = MergeController::mergeDocument(['content' => $template[0]['template_content']]);
                $format = 'html';
            } else {
                if (!file_exists($pathToDocument)) {
                    //TODO rollback
                    return [];
                }
                $mergedDocument = MergeController::mergeDocument(['path' => $pathToDocument]);
                $mergedDocument['encodedDocument'] = ConvertPdfController::convertFromEncodedResource(['encodedResource' => $mergedDocument['encodedDocument']]);
                $format = 'pdf';

                if (!empty($mergedDocument['encodedDocument']['errors'])) {
                    //TODO rollback ??
                    return [];
                }
            }

            $storeResult = DocserverController::storeResourceOnDocServer([
                'collId'            => 'letterbox_coll',
                'docserverTypeId'   => 'ACKNOWLEDGEMENT_RECEIPTS',
                'encodedResource'   => $mergedDocument['encodedDocument'],
                'format'            => $format
            ]);
            if (!empty($storeResult['errors'])) {
                //TODO rollback
                return ['errors' => '[storeResource] ' . $storeResult['errors']];
            }

            $ids[] = AcknowledgementReceiptModel::create([
                'resId'             => $aArgs['resId'],
                'type'              => $templateAttachmentType,
                'format'            => $format,
                'userId'            => $currentUser['id'],
                'contactAddressId'  => $contactToProcess,
                'docserverId'       => 'ACKNOWLEDGEMENT_RECEIPTS',
                'path'              => $storeResult['directory'],
                'filename'          => $storeResult['file_destination_name'],
                'fingerprint'       => $storeResult['fingerPrint']
            ]);
        }

        return $ids;
    }
}
