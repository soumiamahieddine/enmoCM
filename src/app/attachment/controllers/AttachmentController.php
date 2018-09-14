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
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator;
use Resource\controllers\ResController;
use Docserver\models\DocserverModel;
use SrcCore\models\CoreConfigModel;
use setasign\Fpdi\TcpdfFpdi;
use History\controllers\HistoryController;
use Convert\controllers\ConvertPdfController;
use Convert\models\AdrModel;
use Convert\controllers\ConvertThumbnailController;

class AttachmentController
{
    public function getAttachmentsListById(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $attachments = AttachmentModel::getListByResIdMaster(['id' => $aArgs['resId']]);
        $attachmentTypes = AttachmentModel::getAttachmentsTypesByXML();

        return $response->withJson(['attachments'  => $attachments, 'attachment_types'  => $attachmentTypes]);
    }

    public function setInSignatureBook(Request $request, Response $response, array $aArgs)
    {
        //TODO Controle de droit de modification de cet attachment

        $data = $request->getParams();

        $attachment = AttachmentModel::getById(['id' => $aArgs['id'], 'isVersion' => $data['isVersion']]);

        if (empty($attachment)) {
            return $response->withStatus(400)->withJson(['errors' => 'Attachment not found']);
        }

        AttachmentModel::setInSignatureBook(['id' => $aArgs['id'], 'isVersion' => $data['isVersion'], 'inSignatureBook' => !$attachment['in_signature_book']]);

        return $response->withJson(['success' => 'success']);
    }

    public function getThumbnailContent(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !Validator::intVal()->validate($aArgs['resIdMaster']) || !ResController::hasRightByResId(['resId' => $aArgs['resIdMaster'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $pathToThumbnail = 'apps/maarch_entreprise/img/noThumbnail.png';

        $attachment = AttachmentModel::getOnView([
            'select'    => ['res_id', 'res_id_version', 'docserver_id', 'path', 'filename'],
            'where'     => ['res_id = ? or res_id_version = ?', 'res_id_master = ?', 'status not in (?)'],
            'data'      => [$aArgs['resId'], $aArgs['resId'], $aArgs['resIdMaster'], ['DEL', 'OBS']],
            'limit'     => 1
        ]);

        if (empty($attachment[0])) {
            return $response->withStatus(403)->withJson(['errors' => 'Attachment not found']);
        }

        $attachmentTodisplay = $attachment[0];
        $id = (empty($attachmentTodisplay['res_id']) ? $attachmentTodisplay['res_id_version'] : $attachmentTodisplay['res_id']);
        $isVersion = empty($attachmentTodisplay['res_id']);
        if ($isVersion) {
            $collId = "attachments_version_coll";
        } else {
            $collId = "attachments_coll";
        }

        $tnlAdr = AdrModel::getTypedAttachAdrByResId([
            'select'    => ['docserver_id', 'path', 'filename'],
            'resId'     => $aArgs['resId'],
            'type'      => 'TNL',
            'isVersion' => $isVersion
        ]);

        if (empty($tnlAdr)) {
            $result = ConvertThumbnailController::convert(['collId' => $collId, 'resId' => $aArgs['resId'], 'isVersion' => $isVersion]);
            
            $tnlAdr = AdrModel::getTypedAttachAdrByResId([
                'select'    => ['docserver_id', 'path', 'filename'],
                'resId'     => $aArgs['resId'],
                'type'      => 'TNL',
                'isVersion' => $isVersion
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
    
    public function getFileContent(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resIdMaster']) || !ResController::hasRightByResId(['resId' => $aArgs['resIdMaster'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $attachment = AttachmentModel::getOnView([
            'select'    => ['res_id', 'res_id_version', 'docserver_id', 'path', 'filename'],
            'where'     => ['res_id = ? or res_id_version = ?', 'res_id_master = ?', 'status not in (?)'],
            'data'      => [$aArgs['resId'], $aArgs['resId'], $aArgs['resIdMaster'], ['DEL', 'OBS']],
            'limit'     => 1
        ]);

        if (empty($attachment[0])) {
            return $response->withStatus(403)->withJson(['errors' => 'Attachment not found']);
        }
   
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

        $docserver = DocserverModel::getByDocserverId(['docserverId' => $document['docserver_id'], 'select' => ['path_template']]);
        if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $document['path']) . $document['filename'];

        if (!file_exists($pathToDocument)) {
            return $response->withStatus(404)->withJson(['errors' => 'Attachment not found on docserver']);
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
                            $backFromView = AttachmentModel::getOnView(['select' => [$value], 'where' => ['res_id = ?'], 'data' => [$aArgs['resId']]]);
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
            'tableName' => 'res_attachments',
            'recordId'  => $aArgs['resId'],
            'eventType' => 'VIEW',
            'info'      => _ATTACH_DISPLAYING . " : {$id}",
            'moduleId'  => 'attachments',
            'eventId'   => 'resview',
        ]);

        return $response->withHeader('Content-Type', $mimeType);
    }
}
