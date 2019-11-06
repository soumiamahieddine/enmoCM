<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Attachment Controller
* @author dev@maarch.org
*/

namespace Attachment\controllers;

use Attachment\models\AttachmentModel;
use Contact\models\ContactModel;
use Convert\controllers\ConvertPdfController;
use Convert\controllers\ConvertThumbnailController;
use Convert\models\AdrModel;
use Docserver\models\DocserverModel;
use Docserver\models\DocserverTypeModel;
use Group\controllers\PrivilegeController;
use History\controllers\HistoryController;
use Resource\controllers\ResController;
use Resource\controllers\StoreController;
use Resource\models\ChronoModel;
use Resource\models\ResModel;
use Respect\Validation\Validator;
use setasign\Fpdi\Tcpdf\Fpdi;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\AutoCompleteController;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use Template\controllers\TemplateController;
use User\models\UserModel;

class AttachmentController
{
    public function create(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is not set or empty']);
        } elseif (!Validator::notEmpty()->validate($body['encodedFile'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body encodedFile is empty']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['format'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body format is empty or not a string']);
        }

        $attachmentsTypes = AttachmentModel::getAttachmentsTypesByXML();
        $generateChrono = false;
        $mandatoryColumns = ['res_id_master', 'attachment_type'];
        foreach ($body['data'] as $key => $value) {
            foreach ($mandatoryColumns as $columnKey => $column) {
                if ($column == $value['column'] && !empty($value['value'])) {
                    if ($column == 'res_id_master') {
                        if (!ResController::hasRightByResId(['resId' => [$value['value']], 'userId' => $GLOBALS['id']])) {
                            return $response->withStatus(403)->withJson(['errors' => 'ResId master out of perimeter']);
                        }
                        $resId = $value['value'];
                    } elseif ($column == 'attachment_type') {
                        if (empty($attachmentsTypes[$value['value']])) {
                            return $response->withStatus(400)->withJson(['errors' => 'Attachment Type does not exist']);
                        } elseif ($attachmentsTypes[$value['value']]['chrono']) {
                            $generateChrono = true;
                        }
                    }
                    unset($mandatoryColumns[$columnKey]);
                }
            }
            if (in_array($value['column'], ['identifier'])) {
                unset($body['data'][$key]);
            }
        }
        if (!empty($mandatoryColumns)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body data array needs column(s) [' . implode(', ', $mandatoryColumns) . ']']);
        } elseif (empty($resId)) {
            return $response->withStatus(400)->withJson(['errors' => 'ResId master is missing']);
        }

        if ($generateChrono) {
            $resource = ResModel::getById(['select' => ['destination', 'type_id'], 'resId' => $resId]);
            $chrono = ChronoModel::getChrono(['id' => 'outgoing', 'entityId' => $resource['destination'], 'typeId' => $resource['type_id'], 'resId' => $resId]);
            $body['data'][] = ['column' => 'identifier', 'value' => $chrono];
        }

        $body['status'] = 'A_TRA';
        $body['collId'] = 'letterbox_coll';
        $body['data'][] = ['column' => 'coll_id', 'value' => 'letterbox_coll'];
        $body['data'][] = ['column' => 'type_id', 'value' => '0'];
        $body['data'][] = ['column' => 'relation', 'value' => '1'];
        $body['fileFormat'] = $body['format'];
        $resId = StoreController::storeAttachment($body);

        if (empty($resId) || !empty($resId['errors'])) {
            return $response->withStatus(500)->withJson(['errors' => '[AttachmentController create] ' . $resId['errors']]);
        }

        $collId = empty($body['version']) ? 'attachments_coll' : 'attachments_version_coll';
        ConvertPdfController::convert([
            'resId'     => $resId,
            'collId'    => $collId
        ]);

        $customId = CoreConfigModel::getCustomId();
        $customId = empty($customId) ? 'null' : $customId;
        $user = UserModel::getByLogin(['select' => ['id'], 'login' => $GLOBALS['userId']]);
        exec("php src/app/convert/scripts/FullTextScript.php --customId {$customId} --resId {$resId} --collId {$collId} --userId {$user['id']} > /dev/null &");

        HistoryController::add([
            'tableName' => 'res_attachments',
            'recordId'  => $resId,
            'eventType' => 'ADD',
            'info'      => _DOC_ADDED,
            'moduleId'  => 'attachment',
            'eventId'   => 'attachmentAdd',
        ]);

        return $response->withJson(['resId' => $resId]);
    }

    public function getByResId(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => [$aArgs['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $queryParams = $request->getQueryParams();
        if (!empty($queryParams['limit']) && !Validator::intVal()->validate($queryParams['limit'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Query limit is not an int val']);
        }

        $excludeAttachmentTypes = ['converted_pdf', 'print_folder'];
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'view_documents_with_notes', 'userId' => $GLOBALS['id']])) {
            $excludeAttachmentTypes[] = 'document_with_notes';
        }

        $attachments = AttachmentModel::getListByResIdMaster([
            'resId'                     => $aArgs['resId'],
            'login'                     => $GLOBALS['userId'],
            'excludeAttachmentTypes'    => $excludeAttachmentTypes,
            'orderBy'                   => ['res_id DESC'],
            'limit'                     => (int)$queryParams['limit']
        ]);
        $attachmentsTypes = AttachmentModel::getAttachmentsTypesByXML();
        foreach ($attachments as $key => $attachment) {
            $attachments[$key]['contact'] = '';
            if (!empty($attachment['dest_address_id'])) {
                $contact = ContactModel::getOnView([
                    'select' => [
                        'is_corporate_person', 'lastname', 'firstname',
                        'ca_id', 'society', 'contact_firstname', 'contact_lastname'
                    ],
                    'where' => ['ca_id = ?'],
                    'data'  => [$attachment['dest_address_id']]
                ]);
                if (!empty($contact[0])) {
                    $contact = AutoCompleteController::getFormattedContact(['contact' => $contact[0]]);
                    $attachments[$key]['contact'] = $contact['contact']['contact'];
                }
            }
            if (!empty($attachmentsTypes[$attachment['attachment_type']]['label'])) {
                $attachments[$key]['typeLabel'] = $attachmentsTypes[$attachment['attachment_type']]['label'];
            }
        }

        $mailevaConfig = CoreConfigModel::getMailevaConfiguration();
        $mailevaEnabled = false;
        if (!empty($mailevaConfig) && $mailevaConfig['enabled']) {
            $mailevaEnabled = true;
        }

        return $response->withJson(['attachments' => $attachments, 'mailevaEnabled' => $mailevaEnabled]);
    }

    public function setInSignatureBook(Request $request, Response $response, array $aArgs)
    {
        $attachment = AttachmentModel::getById(['id' => $aArgs['id'], 'select' => ['in_signature_book', 'res_id_master']]);
        if (empty($attachment)) {
            return $response->withStatus(400)->withJson(['errors' => 'Attachment not found']);
        }

        if (!ResController::hasRightByResId(['resId' => [$attachment['res_id_master']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        AttachmentModel::setInSignatureBook(['id' => $aArgs['id'], 'inSignatureBook' => !$attachment['in_signature_book']]);

        return $response->withJson(['success' => 'success']);
    }

    public function setInSendAttachment(Request $request, Response $response, array $aArgs)
    {
        $attachment = AttachmentModel::getById(['id' => $aArgs['id'], 'select' => ['in_send_attach', 'res_id_master']]);
        if (empty($attachment)) {
            return $response->withStatus(400)->withJson(['errors' => 'Attachment not found']);
        }

        if (!ResController::hasRightByResId(['resId' => [$attachment['res_id_master']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        AttachmentModel::setInSendAttachment(['id' => $aArgs['id'], 'inSendAttachment' => !$attachment['in_send_attach']]);

        return $response->withJson(['success' => 'success']);
    }

    public function getThumbnailContent(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $attachment = AttachmentModel::get([
            'select'    => ['res_id', 'docserver_id', 'path', 'filename', 'res_id_master'],
            'where'     => ['res_id = ?', 'status not in (?)'],
            'data'      => [$args['id'], ['DEL', 'OBS']],
            'limit'     => 1
        ]);
        if (empty($attachment[0])) {
            return $response->withStatus(403)->withJson(['errors' => 'Attachment not found']);
        }

        if (!ResController::hasRightByResId(['resId' => [$attachment[0]['res_id_master']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $pathToThumbnail = 'apps/maarch_entreprise/img/noThumbnail.png';
        $attachmentTodisplay = $attachment[0];
        $collId = "attachments_coll";

        $tnlAdr = AdrModel::getTypedAttachAdrByResId([
            'select'    => ['docserver_id', 'path', 'filename'],
            'resId'     => $args['id'],
            'type'      => 'TNL'
        ]);

        if (empty($tnlAdr)) {
            ConvertThumbnailController::convert(['collId' => $collId, 'resId' => $args['id']]);
            
            $tnlAdr = AdrModel::getTypedAttachAdrByResId([
                'select'    => ['docserver_id', 'path', 'filename'],
                'resId'     => $args['id'],
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
    
    public function getFileContent(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $attachment = AttachmentModel::get([
            'select'    => ['res_id', 'docserver_id', 'path', 'filename'],
            'where'     => ['res_id = ?', 'status not in (?)'],
            'data'      => [$args['id'], ['DEL']],
            'limit'     => 1
        ]);
        if (empty($attachment[0])) {
            return $response->withStatus(403)->withJson(['errors' => 'Attachment not found']);
        }

        if (!ResController::hasRightByResId(['resId' => [$attachment[0]['res_id_master']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $attachmentTodisplay = $attachment[0];
        $id = $attachmentTodisplay['res_id'];

        $convertedAttachment = ConvertPdfController::getConvertedPdfById(['resId' => $id, 'collId' => 'attachments_coll']);
        if (empty($convertedAttachment['errors'])) {
            $attachmentTodisplay = $convertedAttachment;
        }
        $document['docserver_id'] = $attachmentTodisplay['docserver_id'];
        $document['path'] = $attachmentTodisplay['path'];
        $document['filename'] = $attachmentTodisplay['filename'];
        $document['fingerprint'] = $attachmentTodisplay['fingerprint'];

        $docserver = DocserverModel::getByDocserverId(['docserverId' => $document['docserver_id'], 'select' => ['path_template', 'docserver_type_id']]);
        if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $document['path']) . $document['filename'];

        if (!file_exists($pathToDocument)) {
            return $response->withStatus(404)->withJson(['errors' => 'Attachment not found on docserver']);
        }

        $docserverType = DocserverTypeModel::getById(['id' => $docserver['docserver_type_id'], 'select' => ['fingerprint_mode']]);
        $fingerprint = StoreController::getFingerPrint(['filePath' => $pathToDocument, 'mode' => $docserverType['fingerprint_mode']]);
        if (!empty($document['fingerprint']) && $document['fingerprint'] != $fingerprint) {
            return $response->withStatus(400)->withJson(['errors' => 'Fingerprints do not match']);
        }

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'modules/attachments/xml/config.xml']);
        if ($loadedXml) {
            $watermark = (array)$loadedXml->CONFIG->watermark;
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
                        } else {
                            $backFromView = AttachmentModel::get(['select' => [$value], 'where' => ['res_id = ?'], 'data' => [$args['id']]]);
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

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($fileContent);
        $pathInfo = pathinfo($pathToDocument);

        $response->write($fileContent);
        $response = $response->withAddedHeader('Content-Disposition', "inline; filename=maarch.{$pathInfo['extension']}");

        HistoryController::add([
            'tableName' => 'res_attachments',
            'recordId'  => $args['id'],
            'eventType' => 'VIEW',
            'info'      => _ATTACH_DISPLAYING . " : {$id}",
            'moduleId'  => 'attachments',
            'eventId'   => 'resview',
        ]);

        return $response->withHeader('Content-Type', $mimeType);
    }

    public function getOriginalFileContent(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $attachment = AttachmentModel::get([
            'select'    => ['res_id', 'docserver_id', 'path', 'filename', 'res_id_master'],
            'where'     => ['res_id = ?', 'status not in (?)'],
            'data'      => [$args['id'], ['DEL']],
            'limit'     => 1
        ]);
        if (empty($attachment[0])) {
            return $response->withStatus(403)->withJson(['errors' => 'Attachment not found']);
        }

        if (!ResController::hasRightByResId(['resId' => [$attachment[0]['res_id_master']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $attachmentTodisplay = $attachment[0];
        $id = $attachmentTodisplay['res_id'];

        $document['docserver_id'] = $attachmentTodisplay['docserver_id'];
        $document['path'] = $attachmentTodisplay['path'];
        $document['filename'] = $attachmentTodisplay['filename'];
        $document['fingerprint'] = $attachmentTodisplay['fingerprint'];

        $docserver = DocserverModel::getByDocserverId(['docserverId' => $document['docserver_id'], 'select' => ['path_template', 'docserver_type_id']]);
        if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $document['path']) . $document['filename'];

        if (!file_exists($pathToDocument)) {
            return $response->withStatus(404)->withJson(['errors' => 'Attachment not found on docserver']);
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
            return $response->withStatus(400)->withJson(['errors' => 'Document not found on docserver']);
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($fileContent);
        $pathInfo = pathinfo($pathToDocument);

        $response->write($fileContent);
        $response = $response->withAddedHeader('Content-Disposition', "attachment; filename=maarch.{$pathInfo['extension']}");

        HistoryController::add([
            'tableName' => 'res_attachments',
            'recordId'  => $args['id'],
            'eventType' => 'VIEW',
            'info'      => _ATTACH_DISPLAYING . " : {$id}",
            'moduleId'  => 'attachments',
            'eventId'   => 'resview',
        ]);

        return $response->withHeader('Content-Type', $mimeType);
    }

    public function getAttachmentsTypes(Request $request, Response $response)
    {
        $attachmentsTypes = AttachmentModel::getAttachmentsTypesByXML();

        return $response->withJson(['attachmentsTypes' => $attachmentsTypes]);
    }

    public static function getEncodedDocument(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::boolType($aArgs, ['original']);

        $document = AttachmentModel::getById(['select' => ['docserver_id', 'path', 'filename', 'title'], 'id' => $aArgs['id']]);

        if (empty($aArgs['original'])) {
            $convertedDocument = ConvertPdfController::getConvertedPdfById(['resId' => $aArgs['id'], 'collId' => 'attachments_coll']);

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

        if (!empty($document['title'])) {
            $document['title'] = preg_replace(utf8_decode('@[\\/:*?"<>|]@i'), '_', substr($document['title'], 0, 30));
        }

        $pathInfo = pathinfo($pathToDocument);
        $fileName = (empty($document['title']) ? 'document' : $document['title']) . ".{$pathInfo['extension']}";

        return ['encodedDocument' => $encodedDocument, 'fileName' => $fileName];
    }

    public static function generateAttachForMailing(array $aArgs)
    {
        $attachments = AttachmentModel::get([
            'select'    => ['*'],
            'where'     => ['res_id_master = ?', 'status = ?', 'in_signature_book = ?'],
            'data'      => [$aArgs['resIdMaster'], 'SEND_MASS', true]
        ]);

        $contactsForMailing = DatabaseModel::select([
            'select'    => ['*'],
            'table'     => ['contacts_res'],
            'where'     => ['res_id = ?', 'address_id <> 0'],
            'data'      => [$aArgs['resIdMaster']]
        ]);

        if (!empty($attachments[0])) {
            foreach ($attachments as $attachment) {
                $docserver = DocserverModel::getCurrentDocserver(['typeId' => 'DOC', 'collId' => 'letterbox_coll', 'select' => ['path_template']]);
                $pathToAttachmentToCopy = $docserver['path_template'] . str_replace('#', '/', $attachment['path']) . $attachment['filename'];

                foreach ($contactsForMailing as $keyContact => $contactForMailing) {
                    $chronoPubli = $attachment['identifier'].'-'.($keyContact+1);

                    $dataValue = [];

                    $dataValue[] = [
                        'column'    => 'coll_id',
                        'value'     => 'letterbox_coll',
                        'type'      => 'string'
                    ];
                    array_push($dataValue, [
                        'column' => 'res_id_master',
                        'value' => $aArgs['resIdMaster'],
                        'type' => 'integer'
                    ]);
                    array_push($dataValue, [
                        'column' => 'attachment_type',
                        'value' => $attachment['attachment_type'],
                        'type' => 'string'
                    ]);
                    array_push($dataValue, [
                        'column' => 'identifier',
                        'value' => $chronoPubli,
                        'type' => 'string'
                    ]);
                    array_push($dataValue, [
                        'column' => 'title',
                        'value' => $attachment['title'],
                        'type' => 'string'
                    ]);
                    array_push($dataValue, [
                        'column' => 'type_id',
                        'value' => $attachment['type_id'],
                        'type' => 'integer'
                    ]);
                    array_push($dataValue, [
                        'column' => 'format',
                        'value' => $attachment['format'],
                        'type' => 'string'
                    
                    ]);
                    array_push($dataValue, [
                        'column' => 'typist',
                        'value' => $attachment['typist'],
                        'type' => 'string'
                    ]);
                    array_push($dataValue, [
                        'column' => 'relation',
                        'value' => $attachment['relation'],
                        'type' => 'integer'
                    ]);
                    array_push($dataValue, [
                        'column' => 'dest_contact_id',
                        'value' => $contactForMailing['contact_id'],
                        'type' => 'integer'
                    ]);
                    array_push($dataValue, [
                        'column' => 'dest_address_id',
                        'value' => $contactForMailing['address_id'],
                        'type' => 'integer'
                    ]);
                    array_push($dataValue, [
                        'column' => 'in_signature_book',
                        'value' => 'true',
                    ]);

                    $params = [
                        'userId'           => $aArgs['userId'],
                        'res_id'           => $aArgs['resIdMaster'],
                        'coll_id'          => 'letterbox_coll',
                        'res_view'         => 'res_attachments',
                        'res_table'        => 'res_attachments',
                        'res_contact_id'   => $contactForMailing['contact_id'],
                        'res_address_id'   => $contactForMailing['address_id'],
                        'pathToAttachment' => $pathToAttachmentToCopy,
                        'chronoAttachment' => $chronoPubli,
                    ];

                    $filePathOnTmp = TemplateController::mergeDatasource($params);

                    $allDatas = [
                        "encodedFile" => base64_encode(file_get_contents($filePathOnTmp)),
                        "data"        => $dataValue,
                        "collId"      => "letterbox_coll",
                        "table"       => "res_attachments",
                        "fileFormat"  => $attachment['format'],
                        "status"      => 'A_TRA'
                    ];

                    StoreController::storeAttachment($allDatas);
                }
                
                AttachmentModel::update([
                    'set'       => [
                        'status'  => 'DEL',
                    ],
                    'where'     => ['res_id = ?'],
                    'data'      => [$attachment['res_id']]
                ]);
            }
        }

        return ['success' => 'success'];
    }

    public static function isMailingAttach(array $aArgs)
    {
        $user = UserModel::getByLogin(['login' => $aArgs['login'], 'select' => ['id']]);

        if (!Validator::intVal()->validate($aArgs['resIdMaster']) || !ResController::hasRightByResId(['resId' => [$aArgs['resIdMaster']], 'userId' => $user['id']])) {
            return ['errors' => 'Document out of perimeter'];
        }

        $attachments = AttachmentModel::get([
            'select' => ['res_id'],
            'where' => ['res_id_master = ?', 'status = ?'],
            'data' => [$aArgs['resIdMaster'],'SEND_MASS']
        ]);

        $return['nbAttach'] = count($attachments);

        if ($return['nbAttach'] == 0) {
            return false;
        }

        $return['nbContacts'] = ResModel::getNbContactsByResId(["resId" => $aArgs['resIdMaster']]);

        return $return;
    }
}
