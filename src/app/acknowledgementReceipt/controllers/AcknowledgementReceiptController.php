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

        $whereClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'login' => $user['user_id']]);
        $rawResourcesInBasket = ResModel::getOnView([
            'select'    => ['res_id'],
            'where'     => [$whereClause, 'res_view_letterbox.res_id in (?)'],
            'data'      => [$bodyData['resources']]
        ]);

        $allResourcesInBasket = [];
        foreach ($rawResourcesInBasket as $resource) {
            $allResourcesInBasket[] = $resource['res_id'];
        }

        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);

        $acknowledgement = AcknowledgementReceiptModel::getByResIds([
            'select'  => ['res_id', 'docserver_id', 'path', 'filename', 'fingerprint', 'send_date'],
            'resIds'  => $allResourcesInBasket,
            'orderBy' => ['res_id']
        ]);

        foreach ($acknowledgement as $value) {
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

                $pdf->setSourceFile($pathToDocument);
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
        $data = $request->getParsedBody();
        //$data = $request->getParams();
        $sendEmail = 0;
        $sendPaper = 0;
        $noSendAR = [
            'number'    => 0,
            'list'      => [],
        ];
        $alreadySend = [
            'number'    => 0,
            'list'      => [],
        ];

        if (!Validator::arrayType()->notEmpty()->validate($data['resources'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Data resources is empty or not an array']);
        }
        $data['resources'] = array_slice($data['resources'], 0, 500);

        
        foreach($data['resources'] as $resId) {
            $ext = ResModel::getExtById(['select' => ['res_id', 'category_id', 'address_id', 'is_multicontacts', 'alt_identifier'], 'resId' => $resId]);
                        
            //Verify resource category
            if (empty($ext) || $ext['category_id'] != 'incoming') {
                $noSendAR['number'] += 1;
                array_push($noSendAR['list'], ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => 'Not incoming category' ]);
                continue;
            }

            //Verify associated contact
            if ($ext['address_id'] == '' && $ext['is_multicontacts'] == '') {
                $noSendAR['number'] += 1;
                array_push($noSendAR['list'], ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => 'No contact' ]);
                continue;
            }
            
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

            if (empty($contactsToProcess)) {
                $noSendAR['number'] += 1;
                array_push($noSendAR['list'], ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => 'No contact' ]);
                continue;
            }

            //Verify template
            $resource = ResModel::getById(['select' => ['type_id', 'destination'], 'resId' => $resId]);
            $doctype = DoctypeExtModel::getById(['id' => $resource['type_id'], 'select' => ['process_mode']]);

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
                array_push($noSendAR['list'], ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => 'No template']);
                continue;
            }

            $docserver = DocserverModel::getByDocserverId(['docserverId' => 'TEMPLATES', 'select' => ['path_template']]);
            $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $template[0]['template_path']) . $template[0]['template_file_name'];

            //Verify sending
            $acknowledgement = AcknowledgementReceiptModel::get([
                'select'    => ['res_id', 'type', 'format', 'send_date'],
                'where'     => ['res_id = (?)', 'type = (?)'],
                'data'      => [$resId, $templateAttachmentType],
            ]);

            if (!empty($acknowledgement)) {
                $alreadySend['number'] += 1;
                array_push($alreadySend['list'], ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => 'AR already send' ]);
                continue;
            }

            //Verify user informations
            $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

            foreach ($contactsToProcess as $contactToProcess) {
                $email = 0;
                $paper = 0;
                $contact = ContactModel::getByAddressId(['addressId' => $contactToProcess, 'select' => ['email', 'address_street', 'address_town', 'address_postal_code']]);
    
                if (empty($contact['address_street']) && empty($contact['address_town']) && empty($contact['address_postal_code'] && empty($contact['email']))) {
                    $noSendAR['number'] += 1;
                    array_push($noSendAR['list'], ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => 'No user informations' ]);
                    continue;
                }  
                
                if (!empty($contact['email'])) {
                    if (empty($template[0]['template_content'])) {
                        $noSendAR['number'] += 1;
                        array_push($noSendAR['list'], ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => 'No email template' ]);
                        continue;
                    } else {
                        $email += 1;
                    }
                    
                } else if (!empty($contact['address_street']) && !empty($contact['address_town']) && !empty($contact['address_postal_code'] )) {
                    if (!file_exists($pathToDocument)) {
                        $noSendAR['number'] += 1;
                        array_push($noSendAR['list'], ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => 'No paper template' ]);
                        continue;
                    } else {
                        $paper += 1;
                    }
                }
            }
            
            $sendEmail += $email;
            $sendPaper += $paper;            
        }

        return $response->withJson(['sendEmail' => $sendEmail, 'sendPaper' => $sendPaper, 'noSendAR' => $noSendAR, 'alreadySend' => $alreadySend]);
    }
}
