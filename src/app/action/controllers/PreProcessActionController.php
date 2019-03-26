<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   Pre Process Action Controller
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Action\controllers;

use AcknowledgementReceipt\models\AcknowledgementReceiptModel;
use Attachment\models\AttachmentModel;
use Basket\models\BasketModel;
use Basket\models\GroupBasketRedirectModel;
use Contact\controllers\ContactController;
use Contact\models\ContactModel;
use Convert\controllers\ConvertPdfController;
use Docserver\models\DocserverModel;
use Doctype\models\DoctypeExtModel;
use Entity\models\EntityModel;
use Group\models\GroupModel;
use Parameter\models\ParameterModel;
use Resource\controllers\ResController;
use Resource\controllers\ResourceListController;
use Resource\models\ResModel;
use Respect\Validation\Validator;
use setasign\Fpdi\Tcpdf\Fpdi;
use Shipping\models\ShippingTemplateModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;
use Template\models\TemplateModel;
use User\models\UserEntityModel;
use User\models\UserModel;

class PreProcessActionController
{
    public static function getRedirectInformations(Request $request, Response $response, array $args)
    {
        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $errors = ResourceListController::listControl(['groupId' => $args['groupId'], 'userId' => $args['userId'], 'basketId' => $args['basketId'], 'currentUserId' => $currentUser['id']]);
        if (!empty($errors['errors'])) {
            return $response->withStatus($errors['code'])->withJson(['errors' => $errors['errors']]);
        }

        $basket = BasketModel::getById(['id' => $args['basketId'], 'select' => ['basket_id']]);
        $group = GroupModel::getById(['id' => $args['groupId'], 'select' => ['group_id']]);
        $user = UserModel::getById(['id' => $args['userId'], 'select' => ['user_id']]);

        $keywords = [
            'ALL_ENTITIES'          => '@all_entities',
            'ENTITIES_JUST_BELOW'   => '@immediate_children[@my_primary_entity]',
            'ENTITIES_BELOW'        => '@subentities[@my_entities]',
            'ALL_ENTITIES_BELOW'    => '@subentities[@my_primary_entity]',
            'ENTITIES_JUST_UP'      => '@parent_entity[@my_primary_entity]',
            'MY_ENTITIES'           => '@my_entities',
            'MY_PRIMARY_ENTITY'     => '@my_primary_entity',
            'SAME_LEVEL_ENTITIES'   => '@sisters_entities[@my_primary_entity]'
        ];

        $users = [];
        $allEntities = [];

        foreach (['ENTITY', 'USERS'] as $mode) {
            $entityRedirects = GroupBasketRedirectModel::get([
                'select'    => ['entity_id', 'keyword'],
                'where'     => ['basket_id = ?', 'group_id = ?', 'action_id = ?', 'redirect_mode = ?'],
                'data'      => [$basket['basket_id'], $group['group_id'], $args['actionId'], $mode]
            ]);

            $allowedEntities = [];
            $clauseToProcess = '';
            foreach ($entityRedirects as $entityRedirect) {
                if (!empty($entityRedirect['entity_id'])) {
                    $allowedEntities[] = $entityRedirect['entity_id'];
                } elseif (!empty($entityRedirect['keyword'])) {
                    if (!empty($keywords[$entityRedirect['keyword']])) {
                        if (!empty($clauseToProcess)) {
                            $clauseToProcess .= ', ';
                        }
                        $clauseToProcess .= $keywords[$entityRedirect['keyword']];
                    }
                }
            }

            if (!empty($clauseToProcess)) {
                $preparedClause = PreparedClauseController::getPreparedClause(['clause' => $clauseToProcess, 'login' => $user['user_id']]);
                $preparedEntities = EntityModel::get(['select' => ['entity_id'], 'where' => ['enabled = ?', "entity_id in {$preparedClause}"], 'data' => ['Y']]);
                foreach ($preparedEntities as $preparedEntity) {
                    $allowedEntities[] = $preparedEntity['entity_id'];
                }
            }

            $allowedEntities = array_unique($allowedEntities);

            if ($mode == 'USERS') {
                if (!empty($allowedEntities)) {
                    $users = UserEntityModel::getWithUsers([
                        'select'    => ['DISTINCT users.id', 'users.user_id', 'firstname', 'lastname'],
                        'where'     => ['users_entities.entity_id in (?)', 'status not in (?)'],
                        'data'      => [$allowedEntities, ['DEL', 'ABS']],
                        'orderBy'   => ['lastname', 'firstname']
                    ]);

                    foreach ($users as $key => $user) {
                        $users[$key]['labelToDisplay'] = "{$user['firstname']} {$user['lastname']}";
                        $users[$key]['descriptionToDisplay'] = UserModel::getPrimaryEntityByUserId(['userId' => $user['user_id']])['entity_label'];
                    }
                }
            } elseif ($mode == 'ENTITY') {
                $primaryEntity = UserModel::getPrimaryEntityByUserId(['userId' => $GLOBALS['userId']]);

                $allEntities = EntityModel::get(['select' => ['id', 'entity_id', 'entity_label', 'parent_entity_id'], 'where' => ['enabled = ?'], 'data' => ['Y'], 'orderBy' => ['parent_entity_id']]);
                foreach ($allEntities as $key => $value) {
                    $allEntities[$key]['id'] = $value['entity_id'];
                    $allEntities[$key]['serialId'] = $value['id'];
                    if (empty($value['parent_entity_id'])) {
                        $allEntities[$key]['parent'] = '#';
                        $allEntities[$key]['icon'] = "fa fa-building";
                    } else {
                        $allEntities[$key]['parent'] = $value['parent_entity_id'];
                        $allEntities[$key]['icon'] = "fa fa-sitemap";
                    }
                    $allEntities[$key]['state']['opened'] = false;
                    if (in_array($value['entity_id'], $allowedEntities)) {
                        $allEntities[$key]['allowed'] = true;
                        if ($primaryEntity['entity_id'] == $value['entity_id']) {
                            $allEntities[$key]['state']['opened'] = true;
                            $allEntities[$key]['state']['selected'] = true;
                        }
                    } else {
                        $allEntities[$key]['allowed'] = false;
                        $allEntities[$key]['state']['disabled'] = true;
                    }
                    $allEntities[$key]['text'] = $value['entity_label'];
                }
            }
        }

        $parameter = ParameterModel::getById(['select' => ['param_value_int'], 'id' => 'keepDestForRedirection']);

        return $response->withJson(['entities' => $allEntities, 'users' => $users, 'keepDestForRedirection' => !empty($parameter['param_value_int'])]);
    }

    public function checkAcknowledgementReceipt(Request $request, Response $response, array $aArgs)
    {
        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $errors = ResourceListController::listControl(['groupId' => $aArgs['groupId'], 'userId' => $aArgs['userId'], 'basketId' => $aArgs['basketId'], 'currentUserId' => $currentUser['id']]);
        if (!empty($errors['errors'])) {
            return $response->withStatus($errors['code'])->withJson(['errors' => $errors['errors']]);
        }

        $data = $request->getParsedBody();

        if (!Validator::arrayType()->notEmpty()->validate($data['resources'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Data resources is empty or not an array']);
        }

        $sendList = [];
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
            $ext = ResModel::getExtById(['select' => ['res_id', 'category_id', 'address_id', 'is_multicontacts', 'alt_identifier'], 'resId' => $resId]);

            if (empty($ext)) {
                $noSendAR['number'] += 1;
                $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _DOCUMENT_NOT_FOUND ];
                continue;
            }

            if (!ResController::hasRightByResId(['resId' => [$resId], 'userId' => $GLOBALS['userId']])) {
                $noSendAR['number'] += 1;
                $noSendAR['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier'], 'info' => _DOCUMENT_OUT_PERIMETER ];
                continue;
            }

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
                $sended = 0;
                $generated = 0;

                foreach ($acknowledgements as $acknowledgement) {
                    if (!empty($acknowledgement['creation_date']) && !empty($acknowledgement['send_date'])) {
                        $sended += 1;
                    } elseif (!empty($acknowledgement['creation_date']) && empty($acknowledgement['send_date'])) {
                        $generated += 1;
                    }
                }

                if ($sended > 0) {
                    $alreadySend['number'] += $sended;
                    $alreadySend['list'][] = ['resId' => $resId, 'alt_identifier' => $ext['alt_identifier']];
                }

                if ($generated > 0) {
                    $alreadyGenerated['number'] += $generated;
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
                $sendEmail += $email;
            }
            if ($paper > 0) {
                $sendPaper += $paper;
            }
            if ($email > 0 || $paper > 0) {
                $sendList[] = $resId;
            }
        }

        return $response->withJson(['sendEmail' => $sendEmail, 'sendPaper' => $sendPaper, 'sendList' => $sendList,  'noSendAR' => $noSendAR, 'alreadySend' => $alreadySend, 'alreadyGenerated' => $alreadyGenerated]);
    }

    public function checkShippings(Request $request, Response $response, array $aArgs)
    {
        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        // $errors = ResourceListController::listControl(['groupId' => $aArgs['groupId'], 'userId' => $aArgs['userId'], 'basketId' => $aArgs['basketId'], 'currentUserId' => $currentUser['id']]);
        // if (!empty($errors['errors'])) {
        //     return $response->withStatus($errors['code'])->withJson(['errors' => $errors['errors']]);
        // }

        $mailevaConfig = CoreConfigModel::getMailevaConfiguration();
        if (empty($mailevaConfig)) {
            return $response->withStatus($errors['code'])->withJson(['errors' => _MAILEVA_CONFIG_DOES_NOT_EXIST]);
        }

        $data = $request->getParsedBody();

        if (!Validator::arrayType()->notEmpty()->validate($data['resources'])) {
            return $response->withStatus(400)->withJson(['errors' => _DATA_RESOURCES_FORMAT]);
        }

        $data['resources'] = array_slice($data['resources'], 0, 500);
        if (!ResController::hasRightByResId(['resId' => $data['resources'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => _DOCUMENT_OUT_PERIMETER]);
        }

        $aDestination = ResModel::get([
            'select' => ['distinct(destination)'],
            'where'  => ['res_id in (?)'],
            'data'   => [$data['resources']]
        ]);

        $aTemplates = [];
        $entities   = [];
        $resources  = [];
        $canNotSend = [];

        foreach ($aDestination as $values) {
            if (!empty($values['destination'])) {
                $entities[] = $values['destination'];
            }
        }

        if (!empty($entities)) {
            $aEntities = EntityModel::get(['select' => ['id', 'entity_label'], 'where' => ['entity_id in (?)'], 'data' => $entities]);

            $entitiesId = [];
            $entitiesInfos = [];
            foreach ($aEntities as $value) {
                $entitiesId[] = (string)$value['id'];
                $entitiesInfos[] = $value['entity_label'];
            }

            $aTemplates = ShippingTemplateModel::getByEntities([
                'select'   => ['id', 'label', 'description', 'options', 'fee'],
                'entities' => $entitiesId
            ]);
    
            foreach ($aTemplates as $key => $value) {
                $aTemplates[$key]['options']  = json_decode($value['options'], true);
                $aTemplates[$key]['fee']      = json_decode($value['fee'], true);
            }
        }

        if (!empty($aTemplates)) {
            $aAttachments = AttachmentModel::getAttachmentToSend(['ids' => $data['resources']]);
            foreach ($data['resources'] as $valueResId) {
                $resIdFound = false;
                foreach ($aAttachments as $key => $attachment) {
                    if ($attachment['res_id_master'] == $valueResId) {
                        $resIdFound = true;
                        if (empty($attachment['dest_address_id'])) {
                            $resInfo = ResModel::getExtById(['select' => ['alt_identifier'], 'resId' => $valueResId]);
                            $canNotSend[] = ['resId' => $valueResId, 'chrono' => $resInfo['alt_identifier'], 'reason' => _NO_CONTACT_ATTACHED_FOR . ' : ' . $attachment['identifier']];
                            unset($aAttachments[$key]);
                            break;
                        }
                        $contact = ContactModel::getOnView(['select' => ['*'], 'where' => ['ca_id = ?'], 'data' => [$attachment['dest_address_id']]]);
                        if (empty($contact[0])) {
                            $resInfo = ResModel::getExtById(['select' => ['alt_identifier'], 'resId' => $valueResId]);
                            $canNotSend[] = ['resId' => $valueResId, 'chrono' => $resInfo['alt_identifier'], 'reason' => _NO_CONTACT_ATTACHED_FOR . ' : ' . $attachment['identifier']];
                            unset($aAttachments[$key]);
                            break;
                        }
                        if (!empty($contact['address_country']) && strtoupper(trim($contact['address_country'])) != 'FRANCE') {
                            $resInfo = ResModel::getExtById(['select' => ['alt_identifier'], 'resId' => $valueResId]);
                            $canNotSend[] = ['resId' => $valueResId, 'chrono' => $resInfo['alt_identifier'], 'reason' => _ONLY_FRANCE_AVAILABLE_FOR . ' : ' . $attachment['identifier']];
                            unset($aAttachments[$key]);
                            break;
                        }
                        $afnorAddress = ContactController::getContactAfnor($contact[0]);
                        if ((empty($afnorAddress[1]) && empty($afnorAddress[2])) || empty($afnorAddress[6])) {
                            $resInfo = ResModel::getExtById(['select' => ['alt_identifier'], 'resId' => $valueResId]);
                            $canNotSend[] = ['resId' => $valueResId, 'chrono' => $resInfo['alt_identifier'], 'reason' => _INCOMPETE_ADDRESS_FOR . ' : ' . $attachment['identifier']];
                            unset($aAttachments[$key]);
                            break;
                        }

                        $resources[] = $attachment;
                        unset($aAttachments[$key]);
                    }
                }

                if (!$resIdFound) {
                    $resInfo = ResModel::getExtById(['select' => ['alt_identifier'], 'resId' => $valueResId]);
                    $canNotSend[] = ['resId' => $valueResId, 'chrono' => $resInfo['alt_identifier'], 'reason' => _NO_ATTACHMENT_TO_SEND];
                }
            }
    
            foreach ($aTemplates as $key => $value) {
                if (!empty($resources)) {
                    $templateFee = PreProcessActionController::calculFee([
                        'fee'       => $value['fee'],
                        'resources' => $resources
                    ]);
                } else {
                    $templateFee = 0;
                }
                $aTemplates[$key]['fee'] = $templateFee;
            }
        }

        return $response->withJson([
            'shippingTemplates' => $aTemplates,
            'entities'          => $entitiesInfos,
            'resources'         => $resources,
            'canNotSend'        => $canNotSend
        ]);
    }

    public function isDestinationChanging(Request $request, Response $response, array $args)
    {
        if (!ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $user = UserModel::getById(['id' => $args['userId'], 'select' => ['user_id']]);
        if (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User does not exist']);
        }

        $changeDestination = true;
        $entities = UserEntityModel::get(['select' => ['entity_id'], 'where' => ['user_id = ?'], 'data' => [$user['user_id']]]);
        $resource = ResModel::getById(['select' => ['destination'], 'resId' => $args['resId']]);
        foreach ($entities as $entity) {
            if ($entity['entity_id'] == $resource['destination']) {
                $changeDestination = false;
            }
        }

        return $response->withJson(['isDestinationChanging' => $changeDestination]);
    }

    public static function calculFee(array $aArgs)
    {
        $fee = 0;
        foreach ($aArgs['resources'] as $value) {
            $realId = 0;
            if ($value['res_id'] == 0) {
                $realId = $value['res_id_version'];
                $isVersion = true;
            } elseif ($value['res_id_version'] == 0) {
                $realId = $value['res_id'];
                $isVersion = false;
            }
            $convertedAttachment = ConvertPdfController::getConvertedPdfById(['select' => ['docserver_id', 'path', 'filename'], 'resId' => $value['res_id'], 'collId' => 'attachments_coll', 'isVersion' => $isVersion]);
            $docserver           = DocserverModel::getByDocserverId(['docserverId' => $convertedAttachment['docserver_id'], 'select' => ['path_template']]);
            $pathToDocument      = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $convertedAttachment['path']) . $convertedAttachment['filename'];

            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($pathToDocument);

            $attachmentFee = ($pageCount > 1) ? ($pageCount - 1) * $aArgs['fee']['nextPagePrice'] : 0 ;
            $fee = $fee + $attachmentFee + $aArgs['fee']['firstPagePrice'] + $aArgs['fee']['postagePrice'];
        }

        return $fee;
    }
}
