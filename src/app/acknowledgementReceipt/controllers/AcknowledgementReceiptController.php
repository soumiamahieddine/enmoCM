<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Acknowledgement Receipt Controller
* @author dev@maarch.org
*/

namespace AcknowledgementReceipt\controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use User\models\UserModel;
use Resource\models\ResModel;
use setasign\Fpdi\Tcpdf\Fpdi;
use Basket\models\BasketModel;
use Entity\models\EntityModel;
use Contact\models\ContactModel;
use Respect\Validation\Validator;
use SrcCore\models\DatabaseModel;
use Template\models\TemplateModel;
use Doctype\models\DoctypeExtModel;
use Docserver\models\DocserverModel;
use Resource\controllers\ResController;
use Resource\controllers\StoreController;
use History\controllers\HistoryController;
use Resource\controllers\ResourceListController;
use SrcCore\controllers\PreparedClauseController;
use AcknowledgementReceipt\models\AcknowledgementReceiptModel;

class AcknowledgementReceiptController
{
    public function createPaperAcknowledgement(Request $request, Response $response, array $aArgs)
    {
        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $errors = ResourceListController::listControl(['groupId' => $aArgs['groupId'], 'userId' => $aArgs['userId'], 'basketId' => $aArgs['basketId'], 'currentUserId' => $currentUser['id']]);
        if (!empty($errors['errors'])) {
            return $response->withStatus($errors['code'])->withJson(['errors' => $errors['errors']]);
        }

        $bodyData = $request->getParsedBody();

        if (!Validator::arrayType()->notEmpty()->validate($bodyData['resources'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Resources is not set or empty']);
        }

        $bodyData['resources'] = array_slice($bodyData['resources'], 0, 500);
        $basket = BasketModel::getById(['id' => $aArgs['basketId'], 'select' => ['basket_clause', 'basket_res_order', 'basket_name']]);
        $user   = UserModel::getById(['id' => $aArgs['userId'], 'select' => ['user_id']]);

        $acknowledgements = AcknowledgementReceiptModel::getByIds([
            'select'  => ['res_id', 'docserver_id', 'path', 'filename', 'fingerprint', 'send_date', 'format'],
            'ids'     => $bodyData['resources'],
            'orderBy' => ['res_id']
        ]);

        $resourcesInBasket = [];
        foreach ($acknowledgements as $acknowledgement) {
            $resourcesInBasket[$acknowledgement['res_id']] = $acknowledgement['res_id'];
        }

        $whereClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'login' => $user['user_id']]);
        $rawResourcesInBasket = ResModel::getOnView([
            'select'    => ['res_id'],
            'where'     => [$whereClause, 'res_view_letterbox.res_id in (?)'],
            'data'      => [$resourcesInBasket]
        ]);

        $allResourcesInBasket = [];
        foreach ($rawResourcesInBasket as $rawResourceInBasket) {
            $allResourcesInBasket[$rawResourceInBasket['res_id']] = $rawResourceInBasket['res_id'];
        }

        $aDiff = array_diff($resourcesInBasket, $allResourcesInBasket);
        if (!empty($aDiff)) {
            return $response->withStatus(403)->withJson(['errors' => 'Documents out of perimeter']);
        }

        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);

        foreach ($acknowledgements as $value) {
            if (empty($value['send_date']) && $value['format'] == 'pdf') {
                $docserver = DocserverModel::getByDocserverId(['docserverId' => $value['docserver_id'], 'select' => ['path_template', 'docserver_type_id']]);
                if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
                    return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
                }
                $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $value['path']) . $value['filename'];
                if (!file_exists($pathToDocument)) {
                    return $response->withStatus(404)->withJson(['errors' => 'Document not found on docserver']);
                }

                $fingerprint = StoreController::getFingerPrint(['filePath' => $pathToDocument]);
                if (!empty($value['fingerprint']) && $value['fingerprint'] != $fingerprint) {
                    return $response->withStatus(400)->withJson(['errors' => 'Fingerprints do not match']);
                }

                $nbPages = $pdf->setSourceFile($pathToDocument);
                for ($i = 1; $i <= $nbPages; $i++) {
                    $page = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($page);
                    $pdf->AddPage($size['orientation'], $size);
                    $pdf->useImportedPage($page);
                }
            }
        }

        $fileContent = $pdf->Output('', 'S');
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($fileContent);

        $response->write($fileContent);
        $response = $response->withAddedHeader('Content-Disposition', "inline; filename=maarch.pdf");

        return $response->withHeader('Content-Type', $mimeType);
    }
    
    public function checkAcknowledgementReceipt(Request $request, Response $response, array $aArgs)
    {
        //check service
        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $errors = ResourceListController::listControl(['groupId' => $aArgs['groupId'], 'userId' => $aArgs['userId'], 'basketId' => $aArgs['basketId'], 'currentUserId' => $currentUser['id']]);
        if (!empty($errors['errors'])) {
            return $response->withStatus($errors['code'])->withJson(['errors' => $errors['errors']]);
        }

        $data = $request->getParsedBody();

        if (!Validator::arrayType()->notEmpty()->validate($data['resources'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Data resources is empty or not an array']);
        }
        
        $sendEmail = [
            'number'    => 0,
            'list'      => [],
        ];
        $sendPaper = [
            'number'    => 0,
            'list'      => [],
        ];
        $noSendAR = [
            'number'    => 0,
            'list'      => [],
        ];
        $alreadyGenerated = [
            'number'    => 0,
            'list'      => [],
        ];
        $alreadySend = [
            'number'    => 0,
            'list'      => [],
        ];

        $data['resources'] = array_slice($data['resources'], 0, 500);
        foreach ($data['resources'] as $resId) {
            $ext = ResModel::getExtById(['select' => ['res_id', 'category_id', 'address_id', 'is_multicontacts', 'alt_identifier'], 'resId' => $resId]);

            //Check
            if (empty($ext)) {
                $noSendAR['number'] += 1;
                $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _DOCUMENT_NOT_FOUND ];
                continue;
            }
        
            if (!ResController::hasRightByResId(['resId' => $resId, 'userId' => $GLOBALS['userId']])) {
                $noSendAR['number'] += 1;
                $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _DOCUMENT_OUT_PERIMETER ];
                continue;
            }

            //Verify resource category
            if ($ext['category_id'] != 'incoming') {
                $noSendAR['number'] += 1;
                $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _NOT_INCOMING_CATEGORY ];
                continue;
            }

            //Verify template
            $resource = ResModel::getById(['select' => ['type_id', 'destination'], 'resId' => $resId]);
            $doctype = DoctypeExtModel::getById(['id' => $resource['type_id'], 'select' => ['process_mode']]);

            if (empty($resource['destination'])) {
                $noSendAR['number'] += 1;
                $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _NO_ENTITY];
                continue;
            }

            $entity = EntityModel::getByEntityId(['select' => ['entity_label'], 'entityId' => $resource['destination']]);

            if ($doctype['process_mode'] == 'SVA') {
                $templateAttachmentType = 'sva';
            } elseif ($doctype['process_mode'] == 'SVR') {
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
                $noSendAR['number'] += 1;
                $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _NO_TEMPLATE . ' \'' . $templateAttachmentType . '\' ' . _FOR_ENTITY . ' ' .$entity['entity_label'] ];
                continue;
            }

            $docserver = DocserverModel::getByDocserverId(['docserverId' => 'TEMPLATES', 'select' => ['path_template']]);
            $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $template[0]['template_path']) . $template[0]['template_file_name'];

            //Verify sending
            $acknowledgements = AcknowledgementReceiptModel::get([
                'select'    => ['res_id', 'type', 'format', 'creation_date', 'send_date'],
                'where'     => ['res_id = ?', 'type = ?'],
                'data'      => [$resId, $templateAttachmentType],
            ]);

            if (!empty($acknowledgements)) {
                $sendedEmail = 0;
                $sendedPaper = 0;
                $generatedPaper = 0;
                $generatedEmail = 0;

                foreach ($acknowledgements as $acknowledgement) {
                    if ($acknowledgement['format'] == 'html') {
                        if (!empty($acknowledgement['creation_date']) && !empty($acknowledgement['send_date'])) {
                            $sendedEmail += 1;
                        } elseif (!empty($acknowledgement['creation_date']) && empty($acknowledgement['send_date'])) {
                            $generatedEmail += 1;
                        }
                    } elseif ($acknowledgement['format'] == 'pdf') {
                        if (!empty($acknowledgement['creation_date']) && !empty($acknowledgement['send_date'])) {
                            $sendedPaper += 1;
                        } elseif (!empty($acknowledgement['creation_date']) && empty($acknowledgement['send_date'])) {
                            $generatedPaper += 1;
                        }
                    }
                }
                
                if ($sendedEmail + $sendedPaper == sizeof($acknowledgements)) {
                    $alreadySend['number'] += 1;
                    $alreadySend['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier']];
                }

                if ($generatedEmail + $generatedPaper > 0) {
                    $alreadyGenerated['number'] += 1;
                    $alreadyGenerated['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier']];
                }
            }

            //Verify associated contact
            $contactsToProcess = [];
            if ($ext['is_multicontacts'] == 'Y') {
                $multiContacts = DatabaseModel::select([
                    'select'    => ['address_id'],
                    'table'     => ['contacts_res'],
                    'where'     => ['res_id = ?', 'mode = ?', 'address_id != ?'],
                    'data'      => [$resId, 'multi', 0]
                ]);
                foreach ($multiContacts as $multiContact) {
                    $contactsToProcess[] = $multiContact['address_id'];
                }
            } else {
                $contactsToProcess[] = $ext['address_id'];
            }

            //Verify contact informations
            $email = 0;
            $paper = 0;
            foreach ($contactsToProcess as $contactToProcess) {
                if (empty($contactToProcess)) {
                    $noSendAR['number'] += 1;
                    $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _NO_CONTACT ];
                    continue 2;
                }

                $contact = ContactModel::getByAddressId(['addressId' => $contactToProcess, 'select' => ['email', 'address_street', 'address_town', 'address_postal_code']]);
    
                if (empty($contact['email']) && (empty($contact['address_street']) || empty($contact['address_town']) || empty($contact['address_postal_code']))) {
                    $noSendAR['number'] += 1;
                    $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _USER_MISSING_INFORMATIONS ];
                    continue 2;
                }
                
                if (!empty($contact['email'])) {
                    if (empty($template[0]['template_content'])) {
                        $noSendAR['number'] += 1;
                        $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _NO_EMAIL_TEMPLATE . ' \'' . $templateAttachmentType . '\' ' . _FOR_ENTITY . ' ' . $entity['entity_label'] ];
                        continue 2;
                    } else {
                        $email += 1;
                    }
                } elseif (!empty($contact['address_street']) && !empty($contact['address_town']) && !empty($contact['address_postal_code'])) {
                    if (!file_exists($pathToDocument) || !is_file($pathToDocument)) {
                        $noSendAR['number'] += 1;
                        $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _NO_PAPER_TEMPLATE . ' \'' . $templateAttachmentType . '\' ' . _FOR_ENTITY . ' ' . $entity['entity_label'] ];
                        continue 2;
                    } else {
                        $paper += 1;
                    }
                }
            }

            if ($email > 0) {
                $sendEmail['number'] += $email;
                $sendEmail['list'][] = $resId;
            }
            if ($paper > 0) {
                $sendPaper['number'] += $paper;
                $sendPaper['list'][] = $resId;
            }
        }

        return $response->withJson(['sendEmail' => $sendEmail, 'sendPaper' => $sendPaper, 'noSendAR' => $noSendAR, 'alreadySend' => $alreadySend, 'alreadyGenerated' => $alreadyGenerated]);
    }

    public function getAcknowledgementReceipt(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $mainDocument = ResModel::getById(['select' => ['docserver_id', 'path', 'filename', 'fingerprint'], 'resId' => $aArgs['resId']]);
        $extDocument = ResModel::getExtById(['select' => ['category_id', 'alt_identifier'], 'resId' => $aArgs['resId']]);
        if (empty($mainDocument) || empty($extDocument)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
        }

        $document = AcknowledgementReceiptModel::getByIds([
            'select'  => ['docserver_id', 'path', 'filename', 'fingerprint'],
            'ids'      => [$aArgs['id']]
        ]);

        $docserver = DocserverModel::getByDocserverId(['docserverId' => $document[0]['docserver_id'], 'select' => ['path_template', 'docserver_type_id']]);
        if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $document[0]['path']) . $document[0]['filename'];

        if (!file_exists($pathToDocument)) {
            return $response->withStatus(404)->withJson(['errors' => 'Document not found on docserver']);
        }

        $fingerprint = StoreController::getFingerPrint(['filePath' => $pathToDocument]);
        if (!empty($document[0]['fingerprint']) && $document[0]['fingerprint'] != $fingerprint) {
            return $response->withStatus(400)->withJson(['errors' => 'Fingerprints do not match']);
        }

        $fileContent = file_get_contents($pathToDocument);

        if ($fileContent === false) {
            return $response->withStatus(404)->withJson(['errors' => 'Document not found on docserver']);
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($fileContent);
        $pathInfo = pathinfo($pathToDocument);

        $response->write($fileContent);
        $response = $response->withAddedHeader('Content-Disposition', "inline; filename=maarch.{$pathInfo['extension']}");

        HistoryController::add([
            'tableName' => 'acknowledgement_receipt',
            'recordId'  => $aArgs['id'],
            'eventType' => 'VIEW',
            'info'      => _ACKNOWLEDGEMENT_RECEIPT_DISPLAYING . " : {$aArgs['id']}",
            'moduleId'  => 'res',
            'eventId'   => 'acknowledgementreceiptview',
        ]);

        if ($mimeType == 'text/plain') {
            $mimeType = 'text/html';
        }

        return $response->withHeader('Content-Type', $mimeType);
    }
}
