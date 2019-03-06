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

use setasign\Fpdi\Tcpdf\Fpdi;
use AcknowledgementReceipt\models\AcknowledgementReceiptModel;
use SrcCore\controllers\PreparedClauseController;
use User\models\UserModel;
use Basket\models\BasketModel;
use Resource\models\ResModel;
use Resource\controllers\ResController;
use Docserver\models\DocserverModel;
use Docserver\models\DocserverTypeModel;
use Resource\controllers\StoreController;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator;
use Resource\controllers\ResourceListController;
use Contact\models\ContactModel;
use SrcCore\models\DatabaseModel;
use Doctype\models\DoctypeExtModel;
use Template\models\TemplateModel;
use Entity\models\EntityModel;

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
            'select'  => ['res_id', 'docserver_id', 'path', 'filename', 'fingerprint', 'send_date'],
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
            if (empty($value['send_date'])) {
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
        //$data = $request->getParams();

        if (!Validator::arrayType()->notEmpty()->validate($data['resources'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Data resources is empty or not an array']);
        }
        
        $sendEmail = 0;
        $sendPaper = 0;
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
            $canSendEmail = true;
            $canSendPaper = true;
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
                $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _NO_TEMPLATE . '\'' . $templateAttachmentType . '\' ' . _FOR_ENTITY . $entity['entity_label'] ];
                continue;
            }

            $docserver = DocserverModel::getByDocserverId(['docserverId' => 'TEMPLATES', 'select' => ['path_template']]);
            $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $template[0]['template_path']) . $template[0]['template_file_name'];

            //Verify sending
            $acknowledgements = AcknowledgementReceiptModel::get([
                'select'    => ['res_id', 'type', 'format', 'creation_date', 'send_date'],
                'where'     => ['res_id = (?)', 'type = (?)'],
                'data'      => [$resId, $templateAttachmentType],
            ]);

            if(!empty($acknowledgements)){
                $sendedEmail = 0;
                $sendedPaper = 0;
                $generatedPaper = 0;
                $generatedEmail = 0;
                $sendError = 0;
                $canSendEmail = false;
                $canSendPaper = false;

                foreach ($acknowledgements as $acknowledgement) {

                    if ($acknowledgement['format'] == 'html') {
                        if (!empty($acknowledgement['creation_date']) && !empty($acknowledgement['send_date'])) {
                            $sendedEmail += 1;
                        } else if (!empty($acknowledgement['creation_date']) && empty($acknowledgement['send_date'])) {
                            $generatedEmail += 1;
                        } else {
                            $sendedError +=1;
                        }
                    } else if($acknowledgement['format'] == 'pdf') {
                        if (!empty($acknowledgement['creation_date']) && !empty($acknowledgement['send_date'])) {
                            $sendedPaper += 1;
                        } else if (!empty($acknowledgement['creation_date']) && empty($acknowledgement['send_date'])) {
                            $generatedPaper += 1;
                        } else {
                            $sendedError +=1;
                        }
                    }
                }
                
                if($sendedError > 0) {
                    $noSendAR['number'] += 1;
                    $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _AR_SEND_ERROR ];
                    continue;
                }

                if($sendedEmail + $sendedPaper == sizeof($acknowledgements)){
                    $alreadySend['number'] += 1;
                    $alreadySend['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _AR_ALREADY_SEND ];
                    continue;
                }

                if($generatedEmail + $generatedPaper > 0 ){
                    $alreadyGenerated['number'] += 1;
                    $alreadyGenerated['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _AR_ALREADY_GENERATED ];

                    if ($generatedEmail > 0) {
                        $canSendEmail = true;
                    }
                    if($generatedPaper > 0) {
                        $canSendPaper = true;
                    }
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
                    $email = 0;
                    $paper = 0;
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
                        $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _NO_EMAIL_TEMPLATE . '\'' . $templateAttachmentType . '\' ' . _FOR_ENTITY . $entity['entity_label'] ];
                        continue 2;
                    } else {
                        $email += 1;
                    }
                } elseif (!empty($contact['address_street']) && !empty($contact['address_town']) && !empty($contact['address_postal_code'])) {
                    if (!file_exists($pathToDocument) || !is_file($pathToDocument)) {
                        $noSendAR['number'] += 1;
                        $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _NO_PAPER_TEMPLATE . '\'' . $templateAttachmentType . '\' ' . _FOR_ENTITY . $entity['entity_label'] ];
                        continue 2;
                    } else {
                        $paper += 1;
                    }
                }
            }
            
            if($email > 0 && $canSendEmail){
                $sendEmail += $email;
            }

            if($paper > 0 && $canSendPaper){
                $sendPaper += $paper;
            }
        }

        return $response->withJson(['sendEmail' => $sendEmail, 'sendPaper' => $sendPaper, 'noSendAR' => $noSendAR, 'alreadySend' => $alreadySend, 'alreadyGenerated' => $alreadyGenerated]);
    }
}
