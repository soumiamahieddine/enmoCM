<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Resource Controller
* @author dev@maarch.org
*/

namespace Resource\controllers;

use Attachment\models\AttachmentModel;
use Basket\models\BasketModel;
use Basket\models\GroupBasketModel;
use Convert\controllers\ConvertThumbnailController;
use Convert\models\AdrModel;
use Docserver\controllers\DocserverController;
use Docserver\models\DocserverModel;
use Entity\models\ListInstanceModel;
use Group\controllers\GroupController;
use Group\models\GroupModel;
use Note\models\NoteModel;
use Group\models\ServiceModel;
use setasign\Fpdi\TcpdfFpdi;
use SrcCore\models\CoreConfigModel;
use Status\models\StatusModel;
use SrcCore\models\ValidatorModel;
use History\controllers\HistoryController;
use Resource\models\ResModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use User\models\UserModel;
use Docserver\models\ResDocserverModel;
use Resource\models\ChronoModel;
use Convert\controllers\ConvertPdfController;

class ResController
{
    //*****************************************************************************************
    //LOG ONLY LOG FOR DEBUG
    // $file = fopen('storeResourceLogs.log', 'a');
    // fwrite($file, '[' . date('Y-m-d H:i:s') . '] new request' . PHP_EOL);
    // foreach ($data as $key => $value) {
    //     if ($key <> 'encodedFile') {
    //         fwrite($file, '[' . date('Y-m-d H:i:s') . '] ' . $key . ' : ' . $value . PHP_EOL);
    //     }
    // }
    // fclose($file);
    // ob_flush();
    // ob_start();
    // print_r($data);
    // file_put_contents("storeResourceLogs.log", ob_get_flush());
    //END LOG FOR DEBUG ONLY
    //*****************************************************************************************
    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'index_mlb', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'menu'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        $check = Validator::notEmpty()->validate($data['encodedFile']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['fileFormat']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['status']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['collId']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['table']);
        $check = $check && Validator::arrayType()->notEmpty()->validate($data['data']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $mandatoryColumns = ['type_id'];
        foreach ($data['data'] as $value) {
            foreach ($mandatoryColumns as $columnKey => $column) {
                if ($column == $value['column'] && !empty($value['value'])) {
                    unset($mandatoryColumns[$columnKey]);
                }
            }
        }
        if (!empty($mandatoryColumns)) {
            return $response->withStatus(400)->withJson(['errors' => 'Data array needs column(s) [' . implode(', ', $mandatoryColumns) . ']']);
        }

        $resId = StoreController::storeResource($data);

        if (empty($resId) || !empty($resId['errors'])) {
            return $response->withStatus(500)->withJson(['errors' => '[ResController create] ' . $resId['errors']]);
        }

        HistoryController::add([
            'tableName' => 'res_letterbox',
            'recordId'  => $resId,
            'eventType' => 'ADD',
            'info'      => _DOC_ADDED,
            'moduleId'  => 'res',
            'eventId'   => 'resadd',
        ]);

        return $response->withJson(['resId' => $resId]);
    }

    public function createExt(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'index_mlb', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'menu'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        $check = Validator::intVal()->notEmpty()->validate($data['resId']);
        $check = $check && Validator::arrayType()->notEmpty()->validate($data['data']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $document = ResModel::getById(['resId' => $data['resId'], 'select' => ['1']]);
        if (empty($document)) {
            return $response->withStatus(404)->withJson(['errors' => 'Document does not exist']);
        }
        $documentExt = ResModel::getExtById(['resId' => $data['resId'], 'select' => ['1']]);
        if (!empty($documentExt)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document already exists in mlb_coll_ext']);
        }

        $formatedData = StoreController::prepareExtStorage(['resId' => $data['resId'], 'data' => $data['data']]);

        $check = Validator::stringType()->notEmpty()->validate($formatedData['category_id']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        ResModel::createExt($formatedData);

        return $response->withJson(['status' => true]);
    }

    public function updateStatus(Request $request, Response $response)
    {
        $data = $request->getParams();

        if (empty($data['status'])) {
            $data['status'] = 'COU';
        }
        if (empty(StatusModel::getById(['id' => $data['status']]))) {
            return $response->withStatus(400)->withJson(['errors' => _STATUS_NOT_FOUND]);
        }
        if (empty($data['historyMessage'])) {
            $data['historyMessage'] = _UPDATE_STATUS;
        }

        $check = Validator::arrayType()->notEmpty()->validate($data['chrono']) || Validator::arrayType()->notEmpty()->validate($data['resId']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['status']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['historyMessage']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $identifiers = !empty($data['chrono']) ? $data['chrono'] : $data['resId'];
        foreach ($identifiers as $id) {
            if (!empty($data['chrono'])) {
                $document = ResModel::getResIdByAltIdentifier(['altIdentifier' => $id]);
            } else {
                $document = ResModel::getById(['resId' => $id, 'select' => ['res_id']]);
            }
            if (empty($document)) {
                return $response->withStatus(400)->withJson(['errors' => _DOCUMENT_NOT_FOUND]);
            }
            if (!ResController::hasRightByResId(['resId' => $document['res_id'], 'userId' => $GLOBALS['userId']])) {
                return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
            }
    
            ResModel::update(['set' => ['status' => $data['status']], 'where' => ['res_id = ?'], 'data' => [$document['res_id']]]);
    
            HistoryController::add([
                'tableName' => 'res_letterbox',
                'recordId'  => $document['res_id'],
                'eventType' => 'UP',
                'info'      => $data['historyMessage'],
                'moduleId'  => 'apps',
                'eventId'   => 'resup',
            ]);
        }

        return $response->withJson(['success' => 'success']);
    }

    public static function duplicateForMailing(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'userId', 'contactId', 'addressId', 'altIdentifier']);
        ValidatorModel::intVal($aArgs, ['resId', 'contactId', 'addressId']);
        ValidatorModel::stringType($aArgs, ['userId', 'altIdentifier']);

        if (!ResController::hasRightByResId(['resId' => $aArgs['resId'], 'userId' => $aArgs['userId']])) {
            return ['errors' => 'Document out of perimeter'];
        }

        $resource = ResModel::getById(['resId' => $aArgs['resId']]);
        $resourceExt = ResModel::getExtById(['resId' => $aArgs['resId']]);
        if (empty($resource) || empty($resourceExt)) {
            return ['errors' => 'Resource not found'];
        }

        $usedDocserver = DocserverModel::getByDocserverId(['docserverId' => $resource['docserver_id'], 'select' => ['path_template']]);
        $pathToDocumentToCopy = $usedDocserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $resource['path']) . $resource['filename'];

        $docserver = DocserverModel::getCurrentDocserver(['typeId' => 'DOC', 'collId' => 'letterbox_coll', 'select' => ['path_template', 'docserver_id']]);
        $pathOnDocserver = DocserverController::createPathOnDocServer(['path' => $docserver['path_template']]);
        $docinfo = DocserverController::getNextFileNameInDocServer(['pathOnDocserver' => $pathOnDocserver['pathToDocServer']]);
        $docinfo['fileDestinationName'] .=  '.' . explode('.', $resource['filename'])[1];

        $copyResult = DocserverController::copyOnDocServer([
            'sourceFilePath'             => $pathToDocumentToCopy,
            'destinationDir'             => $docinfo['destinationDir'],
            'fileDestinationName'        => $docinfo['fileDestinationName']
        ]);
        if (!empty($copyResult['errors'])) {
            return ['errors' => 'Resource duplication failed : ' . $copyResult['errors']];
        }

        $resource['path'] = str_replace(str_replace(DIRECTORY_SEPARATOR, '#', $docserver['path_template']), '', $copyResult['copyOnDocserver']['destinationDir']);
        $resource['filename'] = $copyResult['copyOnDocserver']['fileDestinationName'];
        $resource['docserver_id'] = $docserver['docserver_id'];

        $resId = ResModel::create($resource);
        $resourceExt['res_id'] = $resId;
        $resourceExt['is_multicontacts'] = 'N';
        $resourceExt['address_id'] = $aArgs['addressId'];
        if ($resourceExt['category_id'] == 'outgoing') {
            $resourceExt['dest_contact_id'] = $aArgs['contactId'];
            $resourceExt['exp_contact_id'] = null;
        } else {
            $resourceExt['exp_contact_id'] = $aArgs['contactId'];
            $resourceExt['dest_contact_id'] = null;
        }
        $resourceExt['alt_identifier'] = $aArgs['altIdentifier'];
        ResModel::createExt($resourceExt);

        $listInstances = ListInstanceModel::get(['select' => ['*'], 'where' => ['res_id = ?'], 'data' => [$aArgs['resId']]]);
        foreach ($listInstances as $listInstance) {
            unset($listInstance['listinstance_id']);
            $listInstance['res_id'] = $resId;
            $listInstance['signatory'] = empty($listInstance['signatory']) ? 'false' : 'true';
            $listInstance['requested_signature'] = empty($listInstance['requested_signature']) ? 'false' : 'true';
            ListInstanceModel::create($listInstance);
        }

        return $resId;
    }

    public function getFileContent(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $document = ResModel::getById(['select' => ['docserver_id', 'path', 'filename'], 'resId' => $aArgs['resId']]);
        $extDocument = ResModel::getExtById(['select' => ['category_id', 'alt_identifier'], 'resId' => $aArgs['resId']]);
        if (empty($document) || empty($extDocument)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
        }

        if ($extDocument['category_id'] == 'outgoing') {
            $attachment = AttachmentModel::getOnView([
                'select'    => ['res_id', 'res_id_version', 'docserver_id', 'path', 'filename'],
                'where'     => ['res_id_master = ?', 'attachment_type = ?', 'status not in (?)'],
                'data'      => [$aArgs['resId'], 'outgoing_mail', ['DEL', 'OBS']],
                'limit'     => 1
            ]);
            if (!empty($attachment[0])) {
                $attachmentTodisplay = $attachment[0];
                $id = (empty($attachmentTodisplay['res_id']) ? $attachmentTodisplay['res_id_version'] : $attachmentTodisplay['res_id']);
                $isVersion = empty($attachmentTodisplay['res_id']);
                if ($isVersion) {
                    $collId = "attachments_version_coll";
                } else {
                    $collId = "attachments_coll";
                }
                $convertedAttachment = AttachmentModel::getConvertedPdfById(['select' => ['docserver_id', 'path', 'filename'], 'resId' => $id, 'isVersion' => $isVersion]);
                if (empty($convertedAttachment)) {
                    ConvertPdfController::convert([
                        'resId'     => $id,
                        'collId'    => $collId,
                        'isVersion' => $isVersion,
                    ]);
        
                    $convertedAttachment = AttachmentModel::getConvertedPdfById(['select' => ['docserver_id', 'path', 'filename'], 'resId' => $id, 'isVersion' => $isVersion]);
                    
                    if (!empty($convertedAttachment)) {
                        $attachmentTodisplay = $convertedAttachment;
                    }
                } else {
                    $attachmentTodisplay = $convertedAttachment;
                }
                $document['docserver_id'] = $attachmentTodisplay['docserver_id'];
                $document['path'] = $attachmentTodisplay['path'];
                $document['filename'] = $attachmentTodisplay['filename'];
            }
        } else {
            $convertedDocument = ResModel::getConvertedPdfById(['select' => ['docserver_id', 'path', 'filename'], 'resId' => $aArgs['resId']]);

            if (empty($convertedDocument)) {
                ConvertPdfController::convert([
                    'resId'     => $aArgs['resId'],
                    'collId'    => 'letterbox_coll',
                ]);
    
                $convertedDocument = ResModel::getConvertedPdfById(['select' => ['docserver_id', 'path', 'filename'], 'resId' => $aArgs['resId']]);
                
                if (!empty($convertedDocument)) {
                    $documentTodisplay = $convertedDocument;
                }
            } else {
                $documentTodisplay = $convertedDocument;
            }
            $document['docserver_id'] = $documentTodisplay['docserver_id'];
            $document['path'] = $documentTodisplay['path'];
            $document['filename'] = $documentTodisplay['filename'];
        }

        $docserver = DocserverModel::getByDocserverId(['docserverId' => $document['docserver_id'], 'select' => ['path_template']]);
        if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $document['path']) . $document['filename'];

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/features.xml']);
        if ($loadedXml) {
            $watermark = (array)$loadedXml->FEATURES->watermark;
            if ($watermark['enabled'] == 'true') {

                $text = "watermark by {$GLOBALS['userId']}";
                if (!empty($watermark['text'])) {
                    $text = $watermark['text'];
                    preg_match_all('/\[(.*?)\]/i', $watermark['text'], $matches);

                    foreach ($matches[1] as $value) {
                        $tmp = '';
                        if ($value == 'date_now') {
                            $tmp = date('d-m-Y');
                        } elseif ($value == 'hour_now') {
                            $tmp = date('H:i');
                        } elseif($value == 'alt_identifier'){
                            $tmp = $extDocument['alt_identifier'];
                        } else {
                            $backFromView = ResModel::getOnView(['select' => $value, 'where' => ['res_id = ?'], 'data' => [$aArgs['resId']]]);
                            if (!empty($backFromView[0][$value])) {
                                $tmp = $backFromView[0][$value];
                            }
                        }
                        $text = str_replace("[{$value}]", $tmp, $text);
                    }
                }

                $color = ['192', '192', '192']; //RGB
                if (!empty($watermark['text_color'])) {
                    $rawColor = explode(',', $watermark['text_color']);
                    $color = count($rawColor) == 3 ? $rawColor : $color;
                }

                $font = ['helvetica', '10']; //Familly Size
                if (!empty($watermark['font'])) {
                    $rawFont = explode(',', $watermark['font']);
                    $font = count($rawFont) == 2 ? $rawFont : $font;
                }

                $position = [30, 35, 0, 0.5]; //X Y Angle Opacity
                if (!empty($watermark['position'])) {
                    $rawPosition = explode(',', $watermark['position']);
                    $position = count($rawPosition) == 4 ? $rawPosition : $position;
                }

                try {
                    $pdf = new TcpdfFpdi('P', 'pt');
                    $nbPages = $pdf->setSourceFile($pathToDocument);
                    $pdf->setPrintHeader(false);
                    for ($i = 1; $i <= $nbPages; $i++) {
                        $page = $pdf->importPage($i);
                        $size = $pdf->getTemplateSize($page);
                        $pdf->AddPage($size['orientation'], $size);
                        $pdf->useImportedPage($page);
                        $pdf->SetFont($font[0], '', $font[1]);
                        $pdf->SetTextColor($color[0], $color[1], $color[2]);
                        $pdf->SetAlpha($position[3]);
                        $pdf->Rotate($position[2]);
                        $pdf->Text($position[0], $position[1], $text);
                    }
                    $fileContent = $pdf->Output('', 'S');
                } catch (\Exception $e) {
                    $fileContent = null;
                }
            }
        }

        if (empty($fileContent)) {
            $fileContent = file_get_contents($pathToDocument);
        }
        if ($fileContent === false) {
            return $response->withStatus(404)->withJson(['errors' => 'Document not found on docserver']);
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($fileContent);
        $pathInfo = pathinfo($pathToDocument);

        $response->write($fileContent);
        $response = $response->withAddedHeader('Content-Disposition', "inline; filename=maarch.{$pathInfo['extension']}");

        HistoryController::add([
            'tableName' => 'res_letterbox',
            'recordId'  => $aArgs['resId'],
            'eventType' => 'VIEW',
            'info'      => _DOC_DISPLAYING . " : {$aArgs['resId']}",
            'moduleId'  => 'res',
            'eventId'   => 'resview',
        ]);

        return $response->withHeader('Content-Type', $mimeType);
    }

    public function getThumbnailContent(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $pathToThumbnail = 'apps/maarch_entreprise/img/noThumbnail.png';
        $tnlAdr = AdrModel::getTypedDocumentAdrByResId([
            'select'    => ['docserver_id', 'path', 'filename'],
            'resId'     => $aArgs['resId'],
            'type'      => 'TNL'
        ]);
        if (empty($tnlAdr)) {
            $extDocument = ResModel::getExtById(['select' => ['category_id'], 'resId' => $aArgs['resId']]);
            if ($extDocument['category_id'] == 'outgoing') {
                $attachment = AttachmentModel::getOnView([
                    'select'    => ['res_id', 'res_id_version'],
                    'where'     => ['res_id_master = ?', 'attachment_type = ?', 'status not in (?)'],
                    'data'      => [$aArgs['resId'], 'outgoing_mail', ['DEL', 'OBS']],
                    'limit'     => 1
                ]);
                if (!empty($attachment[0])) {
                    ConvertThumbnailController::convert([
                        'collId'            => 'letterbox_coll',
                        'resId'             => $aArgs['resId'],
                        'outgoingId'        => empty($attachment[0]['res_id']) ? $attachment[0]['res_id_version'] : $attachment[0]['res_id'],
                        'isOutgoingVersion' => empty($attachment[0]['res_id'])
                    ]);
                }
            } else {
                ConvertThumbnailController::convert(['collId' => 'letterbox_coll', 'resId' => $aArgs['resId']]);
            }
            $tnlAdr = AdrModel::getTypedDocumentAdrByResId([
                'select'    => ['docserver_id', 'path', 'filename'],
                'resId'     => $aArgs['resId'],
                'type'      => 'TNL'
            ]);
        }

        if (!empty($tnlAdr)) {
            $docserver = DocserverModel::getByDocserverId(['docserverId' => $tnlAdr['docserver_id'], 'select' => ['path_template']]);
            if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
            }

            $pathToThumbnail = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $tnlAdr['path']) . $tnlAdr['filename'];
        }

        $fileContent = file_get_contents($pathToThumbnail);
        if ($fileContent === false) {
            return $response->withStatus(404)->withJson(['errors' => 'Thumbnail not found on docserver']);
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($fileContent);
        $pathInfo = pathinfo($pathToThumbnail);

        $response->write($fileContent);
        $response = $response->withAddedHeader('Content-Disposition', "inline; filename=maarch.{$pathInfo['extension']}");

        return $response->withHeader('Content-Type', $mimeType);
    }

    public function getResourcesByBasket(Request $request, Response $response, array $aArgs)
    {
        $data = $request->getQueryParams();

        if (empty($data['offset']) || !is_numeric($data['offset'])) {
            $data['offset'] = 0;
        }
        if (empty($data['limit']) || !is_numeric($data['limit'])) {
            $data['limit'] = 0;
        }

        $group = GroupModel::getById(['id' => $aArgs['groupSerialId'], 'select' => ['group_id']]);
        $basket = BasketModel::getById(['id' => $aArgs['basketId'], 'select' => ['basket_clause', 'basket_res_order']]);
        if (empty($group) || empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group or basket does not exist']);
        }

        $groups = UserModel::getGroupsByUserId(['userId' => $GLOBALS['userId']]);
        $groupFound = false;
        foreach ($groups as $value) {
            if ($value['id'] == $aArgs['groupSerialId']) {
                $groupFound = true;
            }
        }
        if (!$groupFound) {
            return $response->withStatus(400)->withJson(['errors' => 'Group is not linked to this user']);
        }

        $isBasketLinked = GroupBasketModel::get(['select' => [1], 'where' => ['basket_id = ?', 'group_id = ?'], 'data' => [$aArgs['basketId'], $group['group_id']]]);
        if (empty($isBasketLinked)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group is not linked to this basket']);
        }

        $whereClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'userId' => $GLOBALS['userId']]);
        $resources = ResModel::getForList([
            'clause'    => $whereClause,
            'orderBy'   => ["{$basket['basket_res_order']} DESC"],
            'offset'    => (int)$data['offset'],
            'limit'     => (int)$data['limit'],
        ]);
        $allResources = ResModel::getOnView([
            'select'    => [1],
            'where'     => [$whereClause],
        ]);

        return $response->withJson(['resources' => $resources, 'number' => count($allResources)]);
    }

    public function updateExternalInfos(Request $request, Response $response)
    {
        $data = $request->getParams();

        if (empty($data['externalInfos'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }
        if (empty($data['status'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        foreach ($data['externalInfos'] as $mail) {
            if(!Validator::intType()->validate($mail['res_id'])){
                return $response->withStatus(400)->withJson(['errors' => 'Bad Request: invalid res_id']);
            }
            if(!Validator::StringType()->notEmpty()->validate($mail['external_id'])){
                return $response->withStatus(400)->withJson(['errors' => 'Bad Request: invalid external_id for element : '.$mail['res_id']]);
            }
            if(!Validator::StringType()->notEmpty()->validate($mail['external_link'])){
                return $response->withStatus(400)->withJson(['errors' => 'Bad Request:  invalid external_link for element'.$mail['res_id']]);
            }          
        }

        foreach ($data['externalInfos'] as $mail) {
            $document = ResModel::getById(['resId' => $mail['res_id'], 'select' => ['res_id']]);
            if (empty($document)) {
                return $response->withStatus(400)->withJson(['errors' => _DOCUMENT_NOT_FOUND]);
            }
            if (!ResController::hasRightByResId(['resId' => $document['res_id'], 'userId' => $GLOBALS['userId']])) {
                return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
            }
            ResModel::update(['set' => ['external_id' => $mail['external_id'] , 'external_link' => $mail['external_link'], 'status' => $data['status']], 'where' => ['res_id = ?'], 'data' => [$document['res_id']]]);
        }        

        return $response->withJson(['success' => 'success']);
    }

    public function isLock(Request $request, Response $response, array $aArgs)
    {
        return $response->withJson(ResModel::isLock(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']]));
    }

    public function getNotesCountForCurrentUserById(Request $request, Response $response, array $aArgs)
    {
        return $response->withJson(NoteModel::countByResId(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']]));
    }

    public static function hasRightByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'userId']);
        ValidatorModel::stringType($aArgs, ['userId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        if ($aArgs['userId'] == 'superadmin') {
            return true;
        }
        $groups = UserModel::getGroupsByUserId(['userId' => $aArgs['userId']]);
        $groupsClause = '';
        foreach ($groups as $key => $group) {
            if (!empty($group['where_clause'])) {
                $groupClause = PreparedClauseController::getPreparedClause(['clause' => $group['where_clause'], 'userId' => $aArgs['userId']]);
                if ($key > 0) {
                    $groupsClause .= ' or ';
                }
                $groupsClause .= "({$groupClause})";
            }
        }

        if (!empty($groupsClause)) {
            $res = ResModel::getOnView(['select' => [1], 'where' => ['res_id = ?', "({$groupsClause})"], 'data' => [$aArgs['resId']]]);
            if (!empty($res)) {
                return true;
            }
        }

        $baskets = BasketModel::getBasketsByUserId(['userId' => $aArgs['userId'], 'unneededBasketId' => ['IndexingBasket']]);
        $basketsClause = '';
        foreach ($baskets as $key => $basket) {
            if (!empty($basket['basket_clause'])) {
                $basketClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'userId' => $aArgs['userId']]);
                if ($key > 0) {
                    $basketsClause .= ' or ';
                }
                $basketsClause .= "({$basketClause})";
            }
        }

        if (!empty($basketsClause)) {
            try {
                $res = ResModel::getOnView(['select' => [1], 'where' => ['res_id = ?', "({$basketsClause})"], 'data' => [$aArgs['resId']]]);
                if (!empty($res)) {
                    return true;
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    public function getList(Request $request, Response $response)
    {
        $data = $request->getParams();

        if (!Validator::stringType()->notEmpty()->validate($data['select'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request: select is not valid']);
        }
        if (!Validator::stringType()->notEmpty()->validate($data['clause'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request: clause is not valid']);
        }
        if (!empty($data['withFile'])) {
            if(!Validator::boolType()->validate($data['withFile'])){
                return $response->withStatus(400)->withJson(['errors' => 'Bad Request: withFile parameter is not a boolean']);
            }            
        }

        if (!empty($data['orderBy'])) {
            if (!Validator::arrayType()->notEmpty()->validate($data['orderBy'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Bad Request: orderBy parameter not valid']);
            }            
        }

        if (!empty($data['limit'])) {
            if (!Validator::intType()->validate($data['limit'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Bad Request: limit parameter not valid']);
            }
        }
        $select = explode(',', $data['select']);
        
        if (!PreparedClauseController::isRequestValid(['select' => $select, 'clause' => $data['clause'], 'orderBy' => $data['orderBy'], 'limit' => $data['limit'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(400)->withJson(['errors' => _INVALID_REQUEST]);
        }

        $where = [$data['clause']];
        if ($GLOBALS['userId'] != 'superadmin') {
            $groupsClause = GroupController::getGroupsClause(['userId' => $GLOBALS['userId']]);
            if (empty($groupsClause)) {
                return $response->withStatus(400)->withJson(['errors' => 'User has no groups']);
            }
            $where[] = "({$groupsClause})";
        }

        if ($data['withFile'] === true) {
            $select[] = 'res_id';            
        }

        $resources = ResModel::getOnView(['select' => $select, 'where' => $where, 'orderBy' => $data['orderBy'], 'limit' => $data['limit']]);
        if ($data['withFile'] === true) {
            foreach ($resources as $key => $res) {
                $path = ResDocserverModel::getSourceResourcePath(['resId' => $res['res_id'], 'resTable' => 'res_letterbox', 'adrTable' => 'null']);
                $file = file_get_contents($path);
                $base64Content = base64_encode($file);
                $resources[$key]['fileBase64Content'] = $base64Content;
            }
        }

        return $response->withJson(['resources' => $resources, 'count' => count($resources)]);
    }

    public function getCategories(Request $request, Response $response)
    {
        return $response->withJson(['categories' => ResModel::getCategories()]);
    }

    public function getNatures(Request $request, Response $response)
    {
        return $response->withJson(['natures' => ResModel::getNatures()]);
    }
}
