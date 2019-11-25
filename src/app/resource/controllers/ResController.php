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

use AcknowledgementReceipt\models\AcknowledgementReceiptModel;
use Basket\models\BasketModel;
use Basket\models\RedirectBasketModel;
use Convert\controllers\ConvertPdfController;
use Convert\controllers\ConvertThumbnailController;
use Convert\models\AdrModel;
use CustomField\models\CustomFieldModel;
use CustomField\models\ResourceCustomFieldModel;
use Docserver\models\DocserverModel;
use Docserver\models\DocserverTypeModel;
use Doctype\models\DoctypeModel;
use Entity\models\EntityModel;
use Entity\models\ListInstanceModel;
use Folder\controllers\FolderController;
use Folder\models\FolderModel;
use Folder\models\ResourceFolderModel;
use Group\controllers\GroupController;
use Group\controllers\PrivilegeController;
use Group\models\PrivilegeModel;
use History\controllers\HistoryController;
use IndexingModel\models\IndexingModelFieldModel;
use IndexingModel\models\IndexingModelModel;
use Note\models\NoteModel;
use Priority\models\PriorityModel;
use Resource\models\ResModel;
use Respect\Validation\Validator;
use setasign\Fpdi\Tcpdf\Fpdi;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;
use Status\models\StatusModel;
use Tag\models\TagModel;
use Tag\models\TagResModel;
use User\models\UserGroupModel;
use User\models\UserModel;

class ResController
{
    //*****************************************************************************************
    //LOG ONLY LOG FOR DEBUG
    // ob_flush();
    // ob_start();
    // print_r($data);
    // file_put_contents("storeResourceLogs.log", ob_get_flush());
    //END LOG FOR DEBUG ONLY
    //*****************************************************************************************

    public function create(Request $request, Response $response)
    {
        if (!PrivilegeModel::canIndex(['userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $body = $request->getParsedBody();

        $control = ResController::controlResource(['body' => $body]);
        if (!empty($control['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
        }

        $resId = StoreController::storeResource($body);
        if (empty($resId) || !empty($resId['errors'])) {
            return $response->withStatus(500)->withJson(['errors' => '[ResController create] ' . $resId['errors']]);
        }

        ResController::createAdjacentData(['body' => $body, 'resId' => $resId]);

        if (!empty($body['encodedFile'])) {
            ConvertPdfController::convert([
                'resId'     => $resId,
                'collId'    => 'letterbox_coll'
            ]);

            $customId = CoreConfigModel::getCustomId();
            $customId = empty($customId) ? 'null' : $customId;
            exec("php src/app/convert/scripts/FullTextScript.php --customId {$customId} --resId {$resId} --collId letterbox_coll --userId {$GLOBALS['id']} > /dev/null &");
        }

        HistoryController::add([
            'tableName' => 'res_letterbox',
            'recordId'  => $resId,
            'eventType' => 'ADD',
            'info'      => _DOC_ADDED,
            'moduleId'  => 'resource',
            'eventId'   => 'resadd',
        ]);

        return $response->withJson(['resId' => $resId]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['resId']) || !ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $queryParams = $request->getQueryParams();

        $select = ['model_id', 'category_id', 'priority', 'subject', 'alt_identifier', 'process_limit_date', 'closing_date', 'creation_date', 'modification_date'];
        if (empty($queryParams['light'])) {
            $select = array_merge($select, ['type_id', 'typist', 'status', 'destination', 'initiator', 'confidentiality', 'doc_date', 'admission_date', 'departure_date', 'barcode']);
        }

        $document = ResModel::getById([
            'select'    => $select,
            'resId'     => $args['resId']
        ]);
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
        }

        $unchangeableData = [
            'resId'                 => (int)$args['resId'],
            'modelId'               => $document['model_id'],
            'categoryId'            => $document['category_id'],
            'chrono'                => $document['alt_identifier'],
            'closingDate'           => $document['closing_date'],
            'creationDate'          => $document['creation_date'],
            'modificationDate'      => $document['modification_date']
        ];
        $formattedData = [
            'subject'               => $document['subject'],
            'processLimitDate'      => $document['process_limit_date'],
            'priority'              => $document['priority']
        ];
        if (empty($queryParams['light'])) {
            $formattedData = array_merge($formattedData, [
                'doctype'               => $document['type_id'],
                'typist'                => $document['typist'],
                'typistLabel'           => UserModel::getLabelledUserById(['id' => $document['typist']]),
                'status'                => $document['status'],
                'destination'           => $document['destination'],
                'initiator'             => $document['initiator'],
                'confidentiality'       => $document['confidentiality'] == 'Y',
                'documentDate'          => $document['doc_date'],
                'arrivalDate'           => $document['admission_date'],
                'departureDate'         => $document['departure_date'],
                'barcode'               => $document['barcode']
            ]);
        }
        
        $modelFields = IndexingModelFieldModel::get([
            'select'    => ['identifier'],
            'where'     => ['model_id = ?'],
            'data'      => [$document['model_id']]
        ]);
        $modelFields = array_column($modelFields, 'identifier');

        foreach ($formattedData as $key => $data) {
            if (!in_array($key, $modelFields)) {
                unset($formattedData[$key]);
            }
        }
        $formattedData = array_merge($unchangeableData, $formattedData);

        if (!empty($formattedData['destination'])) {
            $entity = EntityModel::getByEntityId(['entityId' => $formattedData['destination'], 'select' => ['entity_label']]);
            $formattedData['destinationLabel'] = $entity['entity_label'];
        }
        if (!empty($formattedData['initiator'])) {
            $entity = EntityModel::getByEntityId(['entityId' => $formattedData['initiator'], 'select' => ['entity_label']]);
            $formattedData['initiatorLabel'] = $entity['entity_label'];
        }
        if (!empty($formattedData['status'])) {
            $status = StatusModel::getById(['id' => $formattedData['status'], 'select' => ['label_status']]);
            $formattedData['statusLabel'] = $status['label_status'];
        }
        if (!empty($formattedData['priority'])) {
            $priority = PriorityModel::getById(['id' => $formattedData['priority'], 'select' => ['label', 'color']]);
            $formattedData['priorityLabel'] = $priority['label'];
            $formattedData['priorityColor'] = $priority['color'];
        }

        return $response->withJson($formattedData);
    }

    public function update(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['resId']) || !ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        } elseif (!PrivilegeController::hasPrivilege(['privilegeId' => 'edit_resource', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $body = $request->getParsedBody();

        $control = ResController::controlUpdateResource(['body' => $body, 'resId' => $args['resId']]);
        if (!empty($control['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
        }

        $body['resId'] = $args['resId'];
        $resId = StoreController::storeResource($body);
        if (empty($resId) || !empty($resId['errors'])) {
            return $response->withStatus(500)->withJson(['errors' => '[ResController update] ' . $resId['errors']]);
        }

        ResController::updateAdjacentData(['body' => $body, 'resId' => $args['resId']]);

        if (!empty($body['encodedFile'])) {
            AdrModel::deleteDocumentAdr(['where' => ['res_id = ?'], 'data' => [$args['resId']]]);
            ConvertPdfController::convert([
                'resId'     => $args['resId'],
                'collId'    => 'letterbox_coll'
            ]);

            $customId = CoreConfigModel::getCustomId();
            $customId = empty($customId) ? 'null' : $customId;
            exec("php src/app/convert/scripts/FullTextScript.php --customId {$customId} --resId {$args['resId']} --collId letterbox_coll --userId {$GLOBALS['id']} > /dev/null &");

            HistoryController::add([
                'tableName' => 'res_letterbox',
                'recordId'  => $args['resId'],
                'eventType' => 'UP',
                'info'      => _FILE_UPDATED,
                'moduleId'  => 'resource',
                'eventId'   => 'fileModification'
            ]);
        }

        HistoryController::add([
            'tableName' => 'res_letterbox',
            'recordId'  => $args['resId'],
            'eventType' => 'UP',
            'info'      => _DOC_UPDATED,
            'moduleId'  => 'resource',
            'eventId'   => 'resourceModification'
        ]);

        return $response->withStatus(204);
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
                $document = ResModel::getByAltIdentifier(['altIdentifier' => $id, 'select' => ['res_id']]);
            } else {
                $document = ResModel::getById(['resId' => $id, 'select' => ['res_id']]);
            }
            if (empty($document)) {
                return $response->withStatus(400)->withJson(['errors' => _DOCUMENT_NOT_FOUND]);
            }
            if (!ResController::hasRightByResId(['resId' => [$document['res_id']], 'userId' => $GLOBALS['id']])) {
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

    public function getFileContent(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => [$aArgs['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $document = ResModel::getById(['select' => ['docserver_id', 'path', 'filename', 'fingerprint', 'category_id', 'alt_identifier'], 'resId' => $aArgs['resId']]);
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
        }

        if (empty($document['filename'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Document has no file']);
        }

        $convertedDocument = ConvertPdfController::getConvertedPdfById(['resId' => $aArgs['resId'], 'collId' => 'letterbox_coll']);
        if (!empty($convertedDocument['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Conversion error : ' . $convertedDocument['errors']]);
        }

        if ($document['docserver_id'] == $convertedDocument['docserver_id']) {
            return $response->withStatus(400)->withJson(['errors' => 'Document can not be converted']);
        }

        $document = $convertedDocument;

        $docserver = DocserverModel::getByDocserverId(['docserverId' => $document['docserver_id'], 'select' => ['path_template', 'docserver_type_id']]);
        if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $document['path']) . $document['filename'];

        if (!file_exists($pathToDocument)) {
            return $response->withStatus(404)->withJson(['errors' => 'Document not found on docserver']);
        }

        $docserverType = DocserverTypeModel::getById(['id' => $docserver['docserver_type_id'], 'select' => ['fingerprint_mode']]);
        $fingerprint = StoreController::getFingerPrint(['filePath' => $pathToDocument, 'mode' => $docserverType['fingerprint_mode']]);
        if (!empty($document['fingerprint']) && $document['fingerprint'] != $fingerprint) {
            return $response->withStatus(400)->withJson(['errors' => 'Fingerprints do not match']);
        }

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
                        } elseif ($value == 'alt_identifier') {
                            $tmp = $document['alt_identifier'];
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
                    $pdf = new Fpdi('P', 'pt');
                    $nbPages = $pdf->setSourceFile($pathToDocument);
                    $pdf->setPrintHeader(false);
                    for ($i = 1; $i <= $nbPages; $i++) {
                        $page = $pdf->importPage($i, 'CropBox');
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

        ListInstanceModel::update([
            'postSet'   => ['viewed' => 'viewed + 1'],
            'where'     => ['item_id = ?', 'res_id = ?'],
            'data'      => [$GLOBALS['userId'], $aArgs['resId']]
        ]);
        HistoryController::add([
            'tableName' => 'res_letterbox',
            'recordId'  => $aArgs['resId'],
            'eventType' => 'VIEW',
            'info'      => _DOC_DISPLAYING . " : {$aArgs['resId']}",
            'moduleId'  => 'res',
            'eventId'   => 'resview',
        ]);

        $data = $request->getQueryParams();
        if ($data['mode'] == 'base64') {
            return $response->withJson(['encodedDocument' => base64_encode($fileContent)]);
        } else {
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($fileContent);
            $pathInfo = pathinfo($pathToDocument);

            $response->write($fileContent);
            $contentDisposition = $data['mode'] == 'view' ? 'inline' : 'attachment';
            $response = $response->withAddedHeader('Content-Disposition', "{$contentDisposition}; filename=maarch.{$pathInfo['extension']}");
            return $response->withHeader('Content-Type', $mimeType);
        }
    }

    public function getOriginalFileContent(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => [$aArgs['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $document = ResModel::getById(['select' => ['docserver_id', 'path', 'filename', 'category_id'], 'resId' => $aArgs['resId']]);
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
        }

        if (empty($document['filename'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Document has no file']);
        }

        $docserver = DocserverModel::getByDocserverId(['docserverId' => $document['docserver_id'], 'select' => ['path_template', 'docserver_type_id']]);
        if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $document['path']) . $document['filename'];
        if (!file_exists($pathToDocument)) {
            return $response->withStatus(404)->withJson(['errors' => 'Document not found on docserver']);
        }

        $docserverType = DocserverTypeModel::getById(['id' => $docserver['docserver_type_id'], 'select' => ['fingerprint_mode']]);
        $fingerprint = StoreController::getFingerPrint(['filePath' => $pathToDocument, 'mode' => $docserverType['fingerprint_mode']]);
        if (!empty($document['fingerprint']) && $document['fingerprint'] != $fingerprint) {
            return $response->withStatus(400)->withJson(['errors' => 'Fingerprints do not match']);
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
        $response = $response->withAddedHeader('Content-Disposition', "attachment; filename=maarch.{$pathInfo['extension']}");

        ListInstanceModel::update([
            'postSet'   => ['viewed' => 'viewed + 1'],
            'where'     => ['item_id = ?', 'res_id = ?'],
            'data'      => [$GLOBALS['userId'], $aArgs['resId']]
        ]);
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
        if (!Validator::intVal()->validate($aArgs['resId'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $pathToThumbnail = 'apps/maarch_entreprise/img/noThumbnail.png';

        $document = ResModel::getById(['select' => ['filename'], 'resId' => $aArgs['resId']]);
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
        }

        if (!empty($document['filename']) && ResController::hasRightByResId(['resId' => [$aArgs['resId']], 'userId' => $GLOBALS['id']])) {
            $tnlAdr = AdrModel::getTypedDocumentAdrByResId([
                'select'    => ['docserver_id', 'path', 'filename'],
                'resId'     => $aArgs['resId'],
                'type'      => 'TNL'
            ]);
            if (empty($tnlAdr)) {
                ConvertThumbnailController::convert(['collId' => 'letterbox_coll', 'resId' => $aArgs['resId']]);
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

    public function updateExternalInfos(Request $request, Response $response)
    {
        //TODO Revoir cette fonction
        $data = $request->getParams();

        if (empty($data['externalInfos'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request : externalInfos is empty']);
        }
        if (empty($data['status'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request : status is empty']);
        }

        foreach ($data['externalInfos'] as $mail) {
            if (!Validator::intType()->validate($mail['res_id'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Bad Request: invalid res_id']);
            }
            if (!Validator::StringType()->notEmpty()->validate($mail['external_id'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Bad Request: invalid external_id for element : '.$mail['res_id']]);
            }
            if (!Validator::StringType()->notEmpty()->validate($mail['external_link'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Bad Request:  invalid external_link for element'.$mail['res_id']]);
            }
        }

        foreach ($data['externalInfos'] as $mail) {
            $document = ResModel::getById(['resId' => $mail['res_id'], 'select' => ['res_id', 'external_id']]);
            if (empty($document)) {
                return $response->withStatus(400)->withJson(['errors' => _DOCUMENT_NOT_FOUND]);
            }
            if (!ResController::hasRightByResId(['resId' => [$document['res_id']], 'userId' => $GLOBALS['id']])) {
                return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
            }
            $externalId = json_decode($document['external_id'], true);
            $externalId['publikId'] = $mail['external_id'];
            ResModel::update(['set' => ['external_id' => json_encode($externalId), 'external_link' => $mail['external_link'], 'status' => $data['status']], 'where' => ['res_id = ?'], 'data' => [$document['res_id']]]);
        }

        return $response->withJson(['success' => 'success']);
    }

    public function getNotesCountForCurrentUserById(Request $request, Response $response, array $aArgs)
    {
        return $response->withJson(NoteModel::countByResId(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['id'], 'login' => $GLOBALS['userId']]));
    }

    public function getCategories(Request $request, Response $response)
    {
        return $response->withJson(['categories' => ResModel::getCategories()]);
    }

    public function isAllowedForCurrentUser(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => [$aArgs['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withJson(['isAllowed' => false]);
        }

        return $response->withJson(['isAllowed' => true]);
    }

    public static function getEncodedDocument(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);
        ValidatorModel::boolType($aArgs, ['original']);

        $document = ResModel::getById(['select' => ['docserver_id', 'path', 'filename', 'subject'], 'resId' => $aArgs['resId']]);

        if (empty($aArgs['original'])) {
            $convertedDocument = ConvertPdfController::getConvertedPdfById(['resId' => $aArgs['resId'], 'collId' => 'letterbox_coll']);

            if (empty($convertedDocument['errors'])) {
                $document['docserver_id'] = $convertedDocument['docserver_id'];
                $document['path'] = $convertedDocument['path'];
                $document['filename'] = $convertedDocument['filename'];
                $document['fingerprint'] = $convertedDocument['fingerprint'];
            }
        }

        $docserver = DocserverModel::getByDocserverId(['docserverId' => $document['docserver_id'], 'select' => ['path_template', 'docserver_type_id']]);
        if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
            return ['errors' => 'Docserver does not exist'];
        }

        $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $document['path']) . $document['filename'];
        if (!file_exists($pathToDocument)) {
            return ['errors' => 'Document not found on docserver'];
        }

        $docserverType = DocserverTypeModel::getById(['id' => $docserver['docserver_type_id'], 'select' => ['fingerprint_mode']]);
        $fingerprint = StoreController::getFingerPrint(['filePath' => $pathToDocument, 'mode' => $docserverType['fingerprint_mode']]);
        if (!empty($document['fingerprint']) && $document['fingerprint'] != $fingerprint) {
            ['errors' => 'Fingerprints do not match'];
        }

        $fileContent = file_get_contents($pathToDocument);
        if ($fileContent === false) {
            return ['errors' => 'Document not found on docserver'];
        }

        $encodedDocument = base64_encode($fileContent);

        if (!empty($document['subject'])) {
            $document['subject'] = preg_replace(utf8_decode('@[\\/:*?"<>|]@i'), '_', substr($document['subject'], 0, 30));
        }

        $pathInfo = pathinfo($pathToDocument);
        $fileName = (empty($document['subject']) ? 'document' : $document['subject']) . ".{$pathInfo['extension']}";

        return ['encodedDocument' => $encodedDocument, 'fileName' => $fileName];
    }

    public static function hasRightByResId(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId', 'userId']);
        ValidatorModel::intVal($args, ['userId']);
        ValidatorModel::arrayType($args, ['resId']);

        $resources = array_unique($args['resId']);
        $resourcesNumber = count($resources);

        $user = UserModel::getById(['id' => $args['userId'], 'select' => ['user_id']]);

        if ($user['user_id'] == 'superadmin') {
            return true;
        }
        $groups = UserModel::getGroupsByLogin(['login' => $user['user_id']]);
        $groupsClause = '';
        foreach ($groups as $key => $group) {
            if (!empty($group['where_clause'])) {
                $groupClause = PreparedClauseController::getPreparedClause(['clause' => $group['where_clause'], 'login' => $user['user_id']]);
                if ($key > 0) {
                    $groupsClause .= ' or ';
                }
                $groupsClause .= "({$groupClause})";
            }
        }

        if (!empty($groupsClause)) {
            $res = ResModel::getOnView(['select' => [1], 'where' => ['res_id in (?)', "({$groupsClause})"], 'data' => [$resources]]);
            if (!empty($res) && count($res) == $resourcesNumber) {
                return true;
            }
        }

        $baskets = BasketModel::getBasketsByLogin(['login' => $user['user_id']]);
        $basketsClause = '';
        foreach ($baskets as $basket) {
            if (!empty($basket['basket_clause'])) {
                $basketClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'login' => $user['user_id']]);
                if (!empty($basketsClause)) {
                    $basketsClause .= ' or ';
                }
                $basketsClause .= "({$basketClause})";
            }
        }
        $assignedBaskets = RedirectBasketModel::getAssignedBasketsByUserId(['userId' => $args['userId']]);
        foreach ($assignedBaskets as $basket) {
            if (!empty($basket['basket_clause'])) {
                $basketOwner = UserModel::getById(['id' => $basket['owner_user_id'], 'select' => ['user_id']]);
                $basketClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'login' => $basketOwner['user_id']]);
                if (!empty($basketsClause)) {
                    $basketsClause .= ' or ';
                }
                $basketsClause .= "({$basketClause})";
            }
        }

        if (!empty($basketsClause)) {
            try {
                $res = ResModel::getOnView(['select' => [1], 'where' => ['res_id in (?)', "({$basketsClause})"], 'data' => [$resources]]);
                if (!empty($res) && count($res) == $resourcesNumber) {
                    return true;
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        $entities = UserModel::getEntitiesByLogin(['login' => $user['user_id']]);
        $entities = array_column($entities, 'id');

        $foldersWithResources = FolderModel::getWithEntitiesAndResources([
            'select'    => ['DISTINCT(resources_folders.res_id)'],
            'where'     => ['resources_folders.res_id in (?)', '(entities_folders.entity_id in (?) OR folders.user_id = ?)'],
            'data'      => [$resources, $entities, $args['userId']]
        ]);
        if (!empty($foldersWithResources) && count($foldersWithResources) == $resourcesNumber) {
            return true;
        }

        return false;
    }

    private static function createAdjacentData(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId', 'body']);
        ValidatorModel::intVal($args, ['resId']);
        ValidatorModel::arrayType($args, ['body']);

        $body = $args['body'];

        if (!empty($body['diffusionList'])) {
            foreach ($body['diffusionList'] as $diffusion) {
                if ($diffusion['mode'] == 'dest') {
                    ResModel::update(['set' => ['dest_user' => $diffusion['id']], 'where' => ['res_id = ?'], 'data' => [$args['resId']]]);
                }
                ListInstanceModel::create([
                    'res_id'            => $args['resId'],
                    'sequence'          => 0,
                    'item_id'           => $diffusion['id'],
                    'item_type'         => $diffusion['type'] == 'user' ? 'user_id' : 'entity_id',
                    'item_mode'         => $diffusion['mode'],
                    'added_by_user'     => $GLOBALS['userId'],
                    'difflist_type'     => 'entity_id'
                ]);
            }
        }
        if (!empty($body['customFields'])) {
            foreach ($body['customFields'] as $key => $value) {
                ResourceCustomFieldModel::create(['res_id' => $args['resId'], 'custom_field_id' => $key, 'value' => json_encode($value)]);
            }
        }
        if (!empty($body['folders'])) {
            foreach ($body['folders'] as $folder) {
                ResourceFolderModel::create(['res_id' => $args['resId'], 'folder_id' => $folder]);
            }
        }
        if (!empty($body['tags'])) {
            foreach ($body['tags'] as $tag) {
                TagResModel::create(['res_id' => $args['resId'], 'tag_id' => $tag]);
            }
        }

        return true;
    }

    private static function updateAdjacentData(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId', 'body']);
        ValidatorModel::intVal($args, ['resId']);
        ValidatorModel::arrayType($args, ['body']);

        $body = $args['body'];

        if (!empty($body['diffusionList'])) {
            ListInstanceModel::delete(['where' => ['res_id = ?', 'difflist_type = ?'], 'data' => [$args['resId'], 'entity_id']]);
            foreach ($body['diffusionList'] as $diffusion) {
                if ($diffusion['mode'] == 'dest') {
                    ResModel::update(['set' => ['dest_user' => $diffusion['id']], 'where' => ['res_id = ?'], 'data' => [$args['resId']]]);
                }
                ListInstanceModel::create([
                    'res_id'            => $args['resId'],
                    'sequence'          => 0,
                    'item_id'           => $diffusion['id'],
                    'item_type'         => $diffusion['type'] == 'user' ? 'user_id' : 'entity_id',
                    'item_mode'         => $diffusion['mode'],
                    'added_by_user'     => $GLOBALS['userId'],
                    'difflist_type'     => 'entity_id'
                ]);
            }
        }
        if (!empty($body['customFields'])) {
            ResourceCustomFieldModel::delete(['where' => ['res_id = ?'], 'data' => [$args['resId']]]);
            foreach ($body['customFields'] as $key => $value) {
                ResourceCustomFieldModel::create(['res_id' => $args['resId'], 'custom_field_id' => $key, 'value' => json_encode($value)]);
            }
        }
        if (!empty($body['folders'])) {
            $entities = EntityModel::getWithUserEntities([
                'select' => ['entities.id'],
                'where'  => ['user_id = ?'],
                'data'   => [$GLOBALS['userId']]
            ]);
            $entities = array_column($entities, 'id');
            $idToDelete = FolderModel::getWithEntitiesAndResources([
                'select'    => ['resources_folders.id'],
                'where'     => ['resources_folders.res_id = ?', '(entities_folders.entity_id in (?) OR folders.user_id = ?)'],
                'data'      => [$args['resId'], $entities, $GLOBALS['id']]
            ]);
            $idToDelete = array_column($idToDelete, 'id');
            if (!empty($idToDelete)) {
                ResourceFolderModel::delete(['where' => ['id in (?)'], 'data' => [$idToDelete]]);
            }

            foreach ($body['folders'] as $folder) {
                ResourceFolderModel::create(['res_id' => $args['resId'], 'folder_id' => $folder]);
            }
        }
        if (!empty($body['tags'])) {
            TagResModel::delete(['where' => ['res_id = ?'], 'data' => [$args['resId']]]);
            foreach ($body['tags'] as $tag) {
                TagResModel::create(['res_id' => $args['resId'], 'tag_id' => $tag]);
            }
        }

        return true;
    }

    private static function controlResource(array $args)
    {
        $currentUser = UserModel::getById(['id' => $GLOBALS['id'], 'select' => ['loginmode']]);
        $isWebServiceUser = $currentUser['loginmode'] == 'restMode';

        $body = $args['body'];

        if (empty($body)) {
            return ['errors' => 'Body is not set or empty'];
        } elseif (!Validator::intVal()->notEmpty()->validate($body['doctype'])) {
            return ['errors' => 'Body doctype is empty or not an integer'];
        } elseif (!Validator::intVal()->notEmpty()->validate($body['modelId'])) {
            return ['errors' => 'Body modelId is empty or not an integer'];
        } elseif ($isWebServiceUser && !Validator::stringType()->notEmpty()->validate($body['status'])) {
            return ['errors' => 'Body status is empty or not a string'];
        }

        $doctype = DoctypeModel::getById(['id' => $body['doctype'], 'select' => [1]]);
        if (empty($doctype)) {
            return ['errors' => 'Body doctype does not exist'];
        }

        $indexingModel = IndexingModelModel::getById(['id' => $body['modelId'], 'select' => ['master', 'enabled']]);
        if (empty($indexingModel)) {
            return ['errors' => 'Body modelId does not exist'];
        } elseif (!$indexingModel['enabled']) {
            return ['errors' => 'Body modelId is disabled'];
        } elseif (!empty($indexingModel['master'])) {
            return ['errors' => 'Body modelId is not public'];
        }

        $control = ResController::controlFileData(['body' => $body]);
        if (!empty($control['errors'])) {
            return ['errors' => $control['errors']];
        }

        $control = ResController::controlAdjacentData(['body' => $body, 'isWebServiceUser' => $isWebServiceUser]);
        if (!empty($control['errors'])) {
            return ['errors' => $control['errors']];
        }

        if (!$isWebServiceUser) {
            $control = ResController::controlIndexingModelFields(['body' => $body]);
            if (!empty($control['errors'])) {
                return ['errors' => $control['errors']];
            }

            if (!empty($body['initiator'])) {
                $userEntities = UserModel::getEntitiesByLogin(['login' => $GLOBALS['userId']]);
                $userEntities = array_column($userEntities, 'id');
                if (!in_array($body['initiator'], $userEntities)) {
                    return ['errors' => "Body initiator does not belong to your entities"];
                }
            }
        }

        $control = ResController::controlDestination(['body' => $body]);
        if (!empty($control['errors'])) {
            return ['errors' => $control['errors']];
        }
        $control = ResController::controlDates(['body' => $body]);
        if (!empty($control['errors'])) {
            return ['errors' => $control['errors']];
        }

        if (!empty($body['status'])) {
            $status = StatusModel::getById(['id' => $body['status'], 'select' => [1]]);
            if (empty($status)) {
                return ['errors' => 'Body status does not exist'];
            }
        }

        return true;
    }

    private static function controlUpdateResource(array $args)
    {
        $body = $args['body'];

        $resource = ResModel::getById(['resId' => $args['resId'], 'select' => ['status', 'model_id']]);
        if (empty($resource['status'])) {
            return ['errors' => 'Resource status is empty. It can not be modified'];
        }
        $status = StatusModel::getById(['id' => $resource['status'], 'select' => ['can_be_modified']]);
        if ($status['can_be_modified'] != 'Y') {
            return ['errors' => 'Resource can not be modified because of status'];
        }

        if (empty($body)) {
            return ['errors' => 'Body is not set or empty'];
        } elseif (!Validator::intVal()->notEmpty()->validate($body['doctype'])) {
            return ['errors' => 'Body doctype is empty or not an integer'];
        }

        $doctype = DoctypeModel::getById(['id' => $body['doctype'], 'select' => [1]]);
        if (empty($doctype)) {
            return ['errors' => 'Body doctype does not exist'];
        }

        $control = ResController::controlFileData(['body' => $body]);
        if (!empty($control['errors'])) {
            return ['errors' => $control['errors']];
        }

        $control = ResController::controlAdjacentData(['body' => $body, 'isWebServiceUser' => false]);
        if (!empty($control['errors'])) {
            return ['errors' => $control['errors']];
        }

        $body['modelId'] = $resource['model_id'];
        $control = ResController::controlIndexingModelFields(['body' => $body]);
        if (!empty($control['errors'])) {
            return ['errors' => $control['errors']];
        }

        if (!empty($body['initiator'])) {
            $userEntities = UserModel::getEntitiesByLogin(['login' => $GLOBALS['userId']]);
            $userEntities = array_column($userEntities, 'id');
            if (!in_array($body['initiator'], $userEntities)) {
                return ['errors' => "Body initiator does not belong to your entities"];
            }
        }

        $control = ResController::controlDestination(['body' => $body]);
        if (!empty($control['errors'])) {
            return ['errors' => $control['errors']];
        }
        $control = ResController::controlDates(['body' => $body, 'resId' => $args['resId']]);
        if (!empty($control['errors'])) {
            return ['errors' => $control['errors']];
        }

        return true;
    }

    private static function controlFileData(array $args)
    {
        $body = $args['body'];

        if (!empty($body['encodedFile'])) {
            if (!Validator::stringType()->notEmpty()->validate($body['format'])) {
                return ['errors' => 'Body format is empty or not a string'];
            }

            $file     = base64_decode($body['encodedFile']);
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($file);
            if (!StoreController::isFileAllowed(['extension' => $body['format'], 'type' => $mimeType])) {
                return ['errors' => "Format with this mimeType is not allowed : {$body['format']} {$mimeType}"];
            }
        }

        return true;
    }

    private static function controlAdjacentData(array $args)
    {
        $body = $args['body'];

        if (!empty($body['customFields'])) {
            if (!Validator::arrayType()->notEmpty()->validate($body['customFields'])) {
                return ['errors' => 'Body customFields is not an array'];
            }
            $customFields = CustomFieldModel::get(['select' => ['count(1)'], 'where' => ['id in (?)'], 'data' => [array_keys($body['customFields'])]]);
            if (count($body['customFields']) != $customFields[0]['count']) {
                return ['errors' => 'Body tags : One or more custom fields do not exist'];
            }
        }
        if (!empty($body['folders'])) {
            if (!Validator::arrayType()->notEmpty()->validate($body['folders'])) {
                return ['errors' => 'Body folders is not an array'];
            }
            if (!FolderController::hasFolders(['folders' => $body['folders'], 'userId' => $GLOBALS['id']])) {
                return ['errors' => 'Body folders : One or more folders do not exist or are out of perimeter'];
            }
        }
        if (!empty($body['tags'])) {
            if (!Validator::arrayType()->notEmpty()->validate($body['tags'])) {
                return ['errors' => 'Body tags is not an array'];
            }
            $tags = TagModel::get(['select' => ['count(1)'], 'where' => ['id in (?)'], 'data' => [$body['tags']]]);
            if (count($body['tags']) != $tags[0]['count']) {
                return ['errors' => 'Body tags : One or more tags do not exist'];
            }
        }
        if (!empty($body['diffusionList'])) {
            if (!Validator::arrayType()->notEmpty()->validate($body['diffusionList'])) {
                return ['errors' => 'Body diffusionList is not an array'];
            }
            $destFound = false;
            foreach ($body['diffusionList'] as $key => $diffusion) {
                if ($diffusion['mode'] == 'dest') {
                    if ($destFound) {
                        return ['errors' => "Body diffusionList has multiple dest"];
                    }
                    $destFound = true;
                }
                if ($diffusion['type'] == 'user' || $diffusion['mode'] == 'dest') {
                    $user = UserModel::getByLogin(['login' => $diffusion['id'], 'select' => [1]]);
                    if (empty($user)) {
                        return ['errors' => "Body diffusionList[{$key}] id does not exist"];
                    }
                } else {
                    $entity = EntityModel::getByEntityId(['entityId' => $diffusion['id'], 'select' => [1]]);
                    if (empty($entity)) {
                        return ['errors' => "Body diffusionList[{$key}] id does not exist"];
                    }
                }
            }
            if (!$destFound) {
                return ['errors' => 'Body diffusion has no dest'];
            }
        }
        if (!$args['isWebServiceUser'] && !empty($body['destination']) && empty($destFound)) {
            return ['errors' => 'Body diffusion has no dest'];
        }

        return true;
    }

    private static function controlIndexingModelFields(array $args)
    {
        $body = $args['body'];

        $indexingModelFields = IndexingModelFieldModel::get(['select' => ['identifier', 'mandatory'], 'where' => ['model_id = ?'], 'data' => [$body['modelId']]]);
        foreach ($indexingModelFields as $indexingModelField) {
            if (strpos($indexingModelField['identifier'], 'indexingCustomField_') !== false) {
                $customFieldId = explode('_', $indexingModelField['identifier'])[1];
                if ($indexingModelField['mandatory'] && empty($body['customFields'][$customFieldId])) {
                    return ['errors' => "Body customFields[{$customFieldId}] is empty"];
                }
                if (!empty($body['customFields'][$customFieldId])) {
                    $customField = CustomFieldModel::getById(['id' => $customFieldId, 'select' => ['type', 'values']]);
                    $possibleValues = empty($customField['values']) ? [] : json_decode($customField['values']);
                    if (($customField['type'] == 'select' || $customField['type'] == 'radio') && !in_array($body['customFields'][$customFieldId], $possibleValues)) {
                        return ['errors' => "Body customFields[{$customFieldId}] has wrong value"];
                    } elseif ($customField['type'] == 'checkbox') {
                        if (!is_array($body['customFields'][$customFieldId])) {
                            return ['errors' => "Body customFields[{$customFieldId}] is not an array"];
                        }
                        foreach ($body['customFields'][$customFieldId] as $value) {
                            if (!in_array($value, $possibleValues)) {
                                return ['errors' => "Body customFields[{$customFieldId}] has wrong value"];
                            }
                        }
                    } elseif ($customField['type'] == 'string' && !Validator::stringType()->notEmpty()->validate($body['customFields'][$customFieldId])) {
                        return ['errors' => "Body customFields[{$customFieldId}] is not a string"];
                    } elseif ($customField['type'] == 'integer' && !Validator::intVal()->notEmpty()->validate($body['customFields'][$customFieldId])) {
                        return ['errors' => "Body customFields[{$customFieldId}] is not an integer"];
                    } elseif ($customField['type'] == 'date' && !Validator::date()->notEmpty()->validate($body['customFields'][$customFieldId])) {
                        return ['errors' => "Body customFields[{$customFieldId}] is not a date"];
                    }
                }
            } elseif ($indexingModelField['mandatory'] && empty($body[$indexingModelField['identifier']])) {
                return ['errors' => "Body {$indexingModelField['identifier']} is empty"];
            }
        }

        return true;
    }

    private static function controlDates(array $args)
    {
        $body = $args['body'];

        if (!empty($body['documentDate'])) {
            if (!Validator::date()->notEmpty()->validate($body['documentDate'])) {
                return ['errors' => "Body documentDate is not a date"];
            }

            $documentDate = new \DateTime($body['documentDate']);
            $tmr = new \DateTime('tomorrow');
            if ($documentDate > $tmr) {
                return ['errors' => "Body documentDate is not a valid date"];
            }
        }
        if (!empty($body['arrivalDate'])) {
            if (!Validator::date()->notEmpty()->validate($body['arrivalDate'])) {
                return ['errors' => "Body arrivalDate is not a date"];
            }

            $arrivalDate = new \DateTime($body['arrivalDate']);
            $tmr = new \DateTime('tomorrow');
            if ($arrivalDate > $tmr) {
                return ['errors' => "Body arrivalDate is not a valid date"];
            }
        }
        if (!empty($body['departureDate'])) {
            if (!Validator::date()->notEmpty()->validate($body['departureDate'])) {
                return ['errors' => "Body departureDate is not a date"];
            }
            $departureDate = new \DateTime($body['departureDate']);
            if (!empty($documentDate) && $departureDate < $documentDate) {
                return ['errors' => "Body departureDate is not a valid date"];
            }
        }
        if (!empty($body['processLimitDate'])) {
            if (!Validator::date()->notEmpty()->validate($body['processLimitDate'])) {
                return ['errors' => "Body processLimitDate is not a date"];
            }

            if (!empty($args['resId'])) {
                $resource = ResModel::getById(['resId' => $args['resId'], 'select' => ['process_limit_date']]);
                if (!empty($resource['process_limit_date'])) {
                    $originProcessLimitDate = new \DateTime($resource['process_limit_date']);
                }
            }
            $processLimitDate = new \DateTime($body['processLimitDate']);
            if (empty($originProcessLimitDate) || $originProcessLimitDate != $processLimitDate) {
                $today = new \DateTime();
                $today->setTime(00, 00, 00);
                if ($processLimitDate < $today) {
                    return ['errors' => "Body processLimitDate is not a valid date"];
                }
            }
        } elseif (!empty($body['priority'])) {
            $priority = PriorityModel::getById(['id' => $body['priority'], 'select' => [1]]);
            if (empty($priority)) {
                return ['errors' => "Body priority does not exist"];
            }
        }

        return true;
    }

    private static function controlDestination(array $args)
    {
        $body = $args['body'];

        if (!empty($body['destination'])) {
            $groups = UserGroupModel::getWithGroups([
                'select'    => ['usergroups.indexation_parameters'],
                'where'     => ['usergroup_content.user_id = ?', 'usergroups.can_index = ?'],
                'data'      => [$GLOBALS['id'], true]
            ]);

            $clauseToProcess = '';
            $allowedEntities = [];
            foreach ($groups as $group) {
                $group['indexation_parameters'] = json_decode($group['indexation_parameters'], true);
                foreach ($group['indexation_parameters']['keywords'] as $keywordValue) {
                    if (strpos($clauseToProcess, IndexingController::KEYWORDS[$keywordValue]) === false) {
                        if (!empty($clauseToProcess)) {
                            $clauseToProcess .= ', ';
                        }
                        $clauseToProcess .= IndexingController::KEYWORDS[$keywordValue];
                    }
                }
                $allowedEntities = array_merge($allowedEntities, $group['indexation_parameters']['entities']);
                $allowedEntities = array_unique($allowedEntities);
            }

            if (!empty($clauseToProcess)) {
                $preparedClause = PreparedClauseController::getPreparedClause(['clause' => $clauseToProcess, 'login' => $GLOBALS['userId']]);
                $preparedEntities = EntityModel::get(['select' => ['id'], 'where' => ['enabled = ?', "entity_id in {$preparedClause}"], 'data' => ['Y']]);
                $preparedEntities = array_column($preparedEntities, 'id');
                $allowedEntities = array_merge($allowedEntities, $preparedEntities);
                $allowedEntities = array_unique($allowedEntities);
            }

            if (!in_array($body['destination'], $allowedEntities)) {
                return ['errors' => "Body destination is out of your indexing parameters"];
            }
        }

        return true;
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
            if (!Validator::boolType()->validate($data['withFile'])) {
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

        $sve_start_date = false;
        $keySve = array_search('sve_start_date', array_map('trim', $select));
        if ($keySve !== false) {
            unset($select[$keySve]);
            $sve_start_date = true;
        }

        if ($sve_start_date && empty($select)) {
            $select[] = 'res_id';
        }

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
        if (!empty($resources) && $data['withFile'] === true) {
            foreach ($resources as $key => $res) {
                $document = ResModel::getById(['resId' => $res['res_id'], 'select' => ['path', 'filename', 'docserver_id']]);
                $docserver = DocserverModel::getByDocserverId(['docserverId' => $document['docserver_id'], 'select' => ['path_template', 'docserver_type_id']]);
                if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
                    continue;
                }
                $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $document['path']) . $document['filename'];
                if (!file_exists($pathToDocument)) {
                    continue;
                }
                $file = file_get_contents($pathToDocument);
                $base64Content = base64_encode($file);
                $resources[$key]['fileBase64Content'] = $base64Content;
            }
        }
        if (!empty($resources) && $sve_start_date) {
            $aResId = [];
            foreach ($resources as $res) {
                $aResId[] = $res['res_id'];
            }
            $aSveStartDate = AcknowledgementReceiptModel::getByResIds([
                'select'  => ['res_id', 'min(send_date) as send_date'],
                'resIds'  => $aResId,
                'where'   => ['send_date IS NOT NULL', 'send_date != \'\''],
                'groupBy' => ['res_id']
            ]);
            foreach ($resources as $key => $res) {
                $resources[$key]['sve_start_date'] = null;
                foreach ($aSveStartDate as $valueSveStartDate) {
                    if ($res['res_id'] == $valueSveStartDate['res_id']) {
                        $resources[$key]['sve_start_date'] = $valueSveStartDate['send_date'];
                        break;
                    }
                }
            }
        }

        return $response->withJson(['resources' => $resources, 'count' => count($resources)]);
    }
}
