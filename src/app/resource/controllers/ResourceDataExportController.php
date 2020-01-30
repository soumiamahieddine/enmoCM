<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Resource Data Export Controller
 * @author dev@maarch.org
 */

namespace Resource\controllers;


use AcknowledgementReceipt\models\AcknowledgementReceiptModel;
use Attachment\models\AttachmentModel;
use Contact\controllers\ContactController;
use Contact\models\ContactModel;
use Convert\controllers\ConvertPdfController;
use Docserver\models\DocserverModel;
use Docserver\models\DocserverTypeModel;
use Doctype\models\DoctypeModel;
use Email\models\EmailModel;
use IndexingModel\models\IndexingModelFieldModel;
use Note\models\NoteModel;
use Resource\models\ResModel;
use Respect\Validation\Validator;
use setasign\Fpdi\Tcpdf\Fpdi;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;
use Status\models\StatusModel;
use User\models\UserModel;

class ResourceDataExportController
{
    public static function generateFile(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        if (!Validator::notEmpty()->arrayType()->validate($body['resources'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body resources is empty']);
        }

        // Array containing all path to the pdf files to merge
        $documentPaths = [];

        $withSeparators = !empty($body['withSeparator']);

        $unitsSummarySheet = [];
        if (!empty($body['summarySheet'])) {
            $unitsSummarySheet = $body['summarySheet'];
        }

        $forceSummarySheet = count($body['resources']) > 1;

        $resIds = array_column($body['resources'], 'resId');

        if (!ResController::hasRightByResId(['resId' => $resIds, 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }
        foreach ($body['resources'] as $resource) {
            $withSummarySheet = $forceSummarySheet || !empty($unitsSummarySheet);
            if (!$withSummarySheet) {
                $withSummarySheet = !empty($resource['summarySheet']);
            }
            if ($withSummarySheet) {
                if (!empty($resource['summarySheet']) && is_array($resource['summarySheet'])) {
                    $units = $resource['summarySheet'];
                } else if (!empty($unitsSummarySheet)) {
                    $units = $unitsSummarySheet;
                } else {
                    $units = [
                        [
                            "unit" => "qrcode",
                            "label" => ""
                        ],
                        [
                            "unit" => "primaryInformations",
                            "label" => "Informations pricipales"
                        ],
                        [
                            "unit" => "senderRecipientInformations",
                            "label" => "Informations de destination"
                        ],
                        [
                            "unit" => "secondaryInformations",
                            "label" => "Informations secondaires"
                        ],
                        [
                            "unit" => "diffusionList",
                            "label" => "Liste de diffusion"
                        ],
                        [
                            "unit" => "opinionWorkflow",
                            "label" => "Circuit d'avis"
                        ],
                        [
                            "unit" => "visaWorkflow",
                            "label" => "Circuit de visa"
                        ]
                    ];
                }

                $documentPaths[] = ResourceDataExportController::getSummarySheet(['units' => $units, 'resId' => $resource['resId']]);
            }

            if (!empty($resource['document'])) {
                $document = ResModel::getById([
                    'select' => ['res_id', 'docserver_id', 'path', 'filename', 'fingerprint', 'category_id', 'alt_identifier'],
                    'resId'  => $resource['resId']
                ]);
                if (empty($document)) {
                    return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
                }

                if (empty($document['filename'])) {
                    return $response->withStatus(400)->withJson(['errors' => 'Document has no file']);
                }

                $path = ResourceDataExportController::getDocumentFilePath(['document' => $document, 'collId' => 'letterbox_coll']);
                if (!empty($path['errors'])) {
                    return $response->withStatus($path['code'])->withJson(['errors' => $path['errors']]);
                }

                $documentPaths[] = $path;
            }

            if (!empty($resource['attachments'])) {
                $attachmentsIds = [];
                if (!empty($resource['attachments']['resIds'])) {
                    $attachmentsIds = $resource['attachments']['resIds'];
                }
                if (!empty($resource['attachments']['types'])) {
                    if (in_array("ALL", $resource['attachments']['types'])) { // TODO rm types
                        $attachmentsIds = AttachmentModel::get([
                            'select' => ['res_id'],
                            'where'  => ['res_id_master = ?'],
                            'data'   => [$resource['resId']]
                        ]);
                        $attachmentsIds = array_column($attachmentsIds, 'res_id');
                    } else {
                        $ids = AttachmentModel::get([
                            'select' => ['res_id'],
                            'where'  => ['attachment_type in (?)', 'res_id_master = ?'],
                            'data'   => [$resource['attachments']['types'], $resource['resId']]
                        ]);
                        $ids = array_column($ids, 'res_id');
                        $attachmentsIds = array_merge($attachmentsIds, $ids);
                    }
                }

                if (!empty($attachmentsIds)) {
                    $attachments = AttachmentModel::get([
                        'select'  => ['res_id', 'res_id_master', 'recipient_type', 'recipient_id', 'typist', 'status', 'attachment_type',
                                      'creation_date', 'identifier', 'title', 'format', 'docserver_id'],
                        'where'   => ['res_id in (?)', 'status not in (?)'],
                        'data'    => [$attachmentsIds, ['DEL', 'OBS']],
                        'orderBy' => ['creation_date desc']
                    ]);

                    if (count($attachments) < count($attachmentsIds)) { // TODO do not count
                        return $response->withStatus(400)->withJson(['errors' => 'Attachment(s) not found']);
                    }

                    $chronoResource = ResModel::getById(['select' => ['alt_identifier'], 'resId' => $resource['resId']]);
                    $chronoResource = $chronoResource['alt_identifier'];

                    foreach ($attachments as $attachment) {
                        if ($attachment['res_id_master'] != $resource['resId']) {
                            return $response->withStatus(400)->withJson(['errors' => 'Attachment not linked to resource']);
                        }

                        if ($withSeparators) {
                            $documentPaths[] = ResourceDataExportController::getAttachmentSeparator([
                                'attachment'     => $attachment,
                                'chronoResource' => $chronoResource
                            ]);
                        }

                        $path = ResourceDataExportController::getDocumentFilePath(['document' => $attachment, 'collId' => 'attachments_coll']);

                        if (!empty($path['errors'])) {
                            return $response->withStatus($path['code'])->withJson(['errors' => $path['errors']]);
                        }

                        $documentPaths[] = $path;
                    }
                }
            }

            if (!empty($resource['notes'])) {
                if (is_array($resource['notes'])) {
                    foreach ($resource['notes'] as $note) {
                        if (!Validator::intVal()->validate($note)) {
                            return $response->withStatus(400)->withJson(['errors' => 'Note id is not an integer']);
                        }
                    }
                    $notesIds = $resource['notes'];
                } else {
                    $notesIds = NoteModel::get([
                        'select' => ['id'],
                        'where' => ['identifier = ? '],
                        'data' => [$resource['resId']]
                    ]);
                    $notesIds = array_column($notesIds, 'id');
                }

                if (!empty($notesIds)) {
                    $noteFilePath = ResourceDataExportController::getNotesFilePath(['notes' => $notesIds, 'resId' => $resource['resId']]);

                    if (!empty($noteFilePath['errors'])) {
                        return $response->withStatus($noteFilePath['code'])->withJson(['errors' => $noteFilePath['errors']]);
                    }

                    if (file_exists($noteFilePath)) {
                        $documentPaths[] = $noteFilePath;
                    } else {
                        return $response->withStatus(500)->withJson(['errors' => 'Notes file not created']);
                    }
                }
            }

            if (!empty($resource['acknowledgementReceipts'])) {
                if (is_array($resource['acknowledgementReceipts'])) {
                    foreach ($resource['acknowledgementReceipts'] as $acknowledgementReceipt) {
                        if (!Validator::intVal()->validate($acknowledgementReceipt)) {
                            return $response->withStatus(400)->withJson(['errors' => 'Acknowledgement Receipt id is not an integer']);
                        }
                    }
                    $acknowledgementReceiptsIds = $resource['acknowledgementReceipts'];
                } else {
                    $acknowledgementReceiptsIds = AcknowledgementReceiptModel::get([
                        'select' => ['id'],
                        'where'  => ['res_id = ?'],
                        'data'   => [$resource['resId']]
                    ]);
                    $acknowledgementReceiptsIds = array_column($acknowledgementReceiptsIds, 'id');
                }

                if (!empty($acknowledgementReceiptsIds)) {
                    $acknowledgementReceipts = AcknowledgementReceiptModel::getByIds([
                        'select' => ['id', 'res_id', 'format', 'contact_id', 'user_id', 'creation_date', 'send_date', 'docserver_id', 'path',
                                     'fileneme', 'fingerprint'],
                        'ids'    => $acknowledgementReceiptsIds
                    ]);

                    if (count($acknowledgementReceipts) < count($acknowledgementReceiptsIds)) {
                        return $response->withStatus(400)->withJson(['errors' => 'Acknowledgement Receipt(s) not found']);
                    }

                    foreach ($acknowledgementReceipts as $acknowledgementReceipt) {
                        if ($acknowledgementReceipt['res_id'] != $resource['resId']) {
                            return $response->withStatus(400)->withJson(['errors' => 'Acknowledgement Receipt not linked to resource']);
                        }

                        if ($withSeparators) {
                            $documentPaths[] = ResourceDataExportController::getAcknowledgementReceiptSeparator(['acknowledgementReceipt' => $acknowledgementReceipt]);
                        }
                        $path = ResourceDataExportController::getDocumentFilePath(['document' => $acknowledgementReceipt]);

                        if ($acknowledgementReceipt['format'] == 'html') {
                            $path = ResourceDataExportController::getPathConvertedAcknowledgementReceipt([
                                'acknowledgementReceipt' => $acknowledgementReceipt,
                                'pathHtml'               => $path
                            ]);
                        }

                        $documentPaths[] = $path;
                    }
                }
            }

            if (!empty($resource['emails'])) {
                if (is_array($resource['emails'])) {
                    foreach ($resource['emails'] as $email) {
                        if (!Validator::intVal()->validate($email)) {
                            return $response->withStatus(400)->withJson(['errors' => 'Email id is not an integer']);
                        }
                    }
                    $emailsIds = $resource['emails'];
                } else {
                    $emailsIds = EmailModel::get([
                        'select' => ['id'],
                        'where'  => ["cast(document->'id' as INT) = ? "], // TODO might not work if no emails in table (or if no 'id' in document ?) -> if document  is not null ?
                        'data'   => [$resource['resId']]
                    ]);
                    $emailsIds = array_column($emailsIds, 'id');
                }

                if (!empty($emailsIds)) {
                    $emailsModel = EmailModel::get([
                        'select'  => ['id', 'user_id', 'sender', 'recipients', 'cc', 'cci', 'object', 'body', 'document', 'send_date'],
                        'where'   => ['id in (?)'],
                        'data'    => [$emailsIds],
                        'orderBy' => ['creation_date desc']
                    ]);

                    if (count($emailsModel) < count($emailsIds)) {
                        return $response->withStatus(400)->withJson(['errors' => 'Email(s) not found']);
                    }

                    foreach ($emailsModel as $email) {
                        $emailDocument = json_decode($email['document'], true);
                        if (!empty($emailDocument['id']) && $emailDocument['id'] != $resource['resId']) {
                            return $response->withStatus(400)->withJson(['errors' => 'Email not linked to resource']);
                        }
                        $emailFilePath = ResourceDataExportController::getEmailFilePath(['email' => $email, 'resId' => $resource['resId']]);

                        if (file_exists($emailFilePath)) {
                            $documentPaths[] = $emailFilePath;
                        } else {
                            return $response->withStatus(500)->withJson(['errors' => 'Email file not created']);
                        }
                    }
                }
            }
        }

        if (!empty($documentPaths)) {
            $tmpDir = CoreConfigModel::getTmpPath();
            $filePathOnTmp = $tmpDir . 'mergedFile.pdf';
            $command = "pdfunite " . implode(" ", $documentPaths) . ' ' . $filePathOnTmp;

            exec($command . ' 2>&1', $output, $return);

            if (!file_exists($filePathOnTmp)) {
                return $response->withStatus(500)->withJson(['errors' => 'Merged file not created']);
            } else {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);

                $fileContent = file_get_contents($filePathOnTmp);
                $mimeType = $finfo->buffer($fileContent);

                $response->write($fileContent);

                $response = $response->withAddedHeader('Content-Disposition', "inline; filename=maarch.pdf");
                return $response->withHeader('Content-Type', $mimeType);
            }
        }

        return $response->withStatus(400)->withJson(['errors' => 'No document to merge']);
    }

    private static function getDocumentFilePath(array $args)
    {
        ValidatorModel::notEmpty($args, ['document']);
        ValidatorModel::arrayType($args, ['document']);
        ValidatorModel::stringType($args, ['collId']);

        $resourceDocument = $args['document'];

        if (in_array($args['collId'], ['letterbox_coll', 'attachments_coll'])) {
            $document = ConvertPdfController::getConvertedPdfById(['resId' => $resourceDocument['res_id'], 'collId' => $args['collId']]);
            if (!empty($document['errors'])) {
                return ['errors' => 'Conversion error : ' . $document['errors'], 'code' => 400];
            }

            if ($document['docserver_id'] == $resourceDocument['docserver_id']) {
                return ['errors' => 'Document can not be converted', 'code' => 400];
            }
        } else {
            $document = $resourceDocument;
        }

        $docserver = DocserverModel::getByDocserverId([
            'docserverId' => $document['docserver_id'], 'select' => ['path_template', 'docserver_type_id']
        ]);
        if (empty($docserver['path_template']) || !file_exists($docserver['path_template'])) {
            return ['errors' => 'Docserver does not exist', 'code' => 400];
        }

        $pathToDocument = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $document['path']) . $document['filename'];

        if (!file_exists($pathToDocument)) {
            return ['errors' => 'Document not found on docserver', 'code' => 404];
        }

        $docserverType = DocserverTypeModel::getById(['id' => $docserver['docserver_type_id'], 'select' => ['fingerprint_mode']]);
        $fingerprint = StoreController::getFingerPrint(['filePath' => $pathToDocument, 'mode' => $docserverType['fingerprint_mode']]);
        if (!empty($document['fingerprint']) && $document['fingerprint'] != $fingerprint) {
            return ['errors' => 'Fingerprints do not match', 'code' => 400];
        }

        return $pathToDocument;
    }

    private static function getNotesFilePath(array $args)
    {
        ValidatorModel::notEmpty($args, ['notes', 'resId']);
        ValidatorModel::arrayType($args, ['notes']);
        ValidatorModel::intVal($args, ['resId']);

        $notes = [];

        $notesModel = NoteModel::get([ // TODO put this in a param -> query before
            'where'   => ['id in (?)'],
            'data'    => [$args['notes']],
            'orderBy' => ['creation_date desc']
        ]);

        if (count($notesModel) < count($args['notes'])) { // TODO useless here ?
            return ['errors' => 'Note(s) not found', 'code' => 400];
        }

        foreach ($notesModel as $note) {
            if ($note['identifier'] != $args['resId']) {
                return ['errors' => 'Note not linked to resource', 'code' => 400];
            }

            $user = UserModel::getById(['id' => $note['user_id'], 'select' => ['firstname', 'lastname']]);
            $userName = $user['firstname'] . ' ' . $user['lastname'];

            $noteText = str_replace('←', '<=', $note['note_text']); // TODO might not work (Alex had '?')

            $date = explode('-', date('d-m-Y', strtotime($note['creation_date'])));
            $date = $date[0].'/'.$date[1].'/'.$date[2].' '.date('H:i', strtotime($note['creation_date']));

            $notes[] = ['user' => $userName, 'note' => $noteText, 'date' => $date];
        }

        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);
        $pdf->AddPage();

        $dimensions     = $pdf->getPageDimensions();
        $widthNoMargins = $dimensions['w'] - $dimensions['rm'] - $dimensions['lm'];
        $bottomHeight   = $dimensions['h'] - $dimensions['bm'];
        $widthNotes     = $widthNoMargins / 2;

        $pdf->SetY($pdf->GetY() + 40);
        if (($pdf->GetY() + 80) > $bottomHeight) {
            $pdf->AddPage();
        }

        $pdf->SetFont('', 'B', 11);
        $pdf->Cell(0, 15, _NOTES_COMMENT, 0, 2, 'L', false);

        $pdf->SetY($pdf->GetY() + 2);
        $pdf->SetFont('', '', 10);

        foreach ($notes as $note) {
            if (($pdf->GetY() + 65) > $bottomHeight) {
                $pdf->AddPage();
            }
            $pdf->SetFont('', 'B', 10);
            $pdf->Cell($widthNotes, 20, $note['user'], 1, 0, 'L', false);
            $pdf->SetFont('', '', 10);
            $pdf->Cell($widthNotes, 20, $note['date'], 1, 1, 'L', false);
            $pdf->MultiCell(0, 40, $note['note'], 1, 'L', false);
            $pdf->SetY($pdf->GetY() + 5);
        }

        $tmpDir = CoreConfigModel::getTmpPath();
        $filePathOnTmp = $tmpDir . 'listNotes_' . $GLOBALS['id'] . '.pdf';
        $pdf->Output($filePathOnTmp, 'F');

        return $filePathOnTmp;
    }

    private static function getAcknowledgementReceiptSeparator(array $args)
    {
        ValidatorModel::notEmpty($args, ['acknowledgementReceipt']);
        ValidatorModel::arrayType($args, ['acknowledgementReceipt']);

        $acknowledgementReceipt = $args['acknowledgementReceipt'];

        $contact = ContactModel::getById([
            'select' => ['id', 'firstname', 'lastname', 'email', 'address_number', 'address_street', 'address_postcode',
                         'address_town', 'address_country', 'company'],
            'id'     => $acknowledgementReceipt['contact_id']
        ]);
        if ($acknowledgementReceipt['format'] == 'html') {
            $displayContact = $contact['firstname'] . ' ' . $contact['lastname'] . ' (' . $contact['email'] . ')';
        } else {
            $displayContact = ContactController::getFormattedContactWithAddress([
                'contact' => $contact
            ]);
            $displayContact = $displayContact['contact']['otherInfo'];
        }

        $creator = UserModel::getById(['id' => $acknowledgementReceipt['user_id']]);

        $creationDate = new \DateTime($acknowledgementReceipt['creation_date']);
        $creationDate = $creationDate->format('d-m-Y H:i');

        if (!empty($acknowledgementReceipt['send_date'])) {
            $sendDate = new \DateTime($acknowledgementReceipt['send_date']);
            $sendDate = $sendDate->format('d-m-Y H:i');
        } else {
            $sendDate = _UNDEFINED;
        }

        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);
        $pdf->AddPage();

        $dimensions     = $pdf->getPageDimensions();
        $widthNoMargins = $dimensions['w'] - $dimensions['rm'] - $dimensions['lm'];
        $width          = $widthNoMargins / 2;

        $pdf->SetFont('', 'B', 12);
        $pdf->Cell($width, 15, _ACKNOWLEDGEMENT_RECEIPT, 0, 1, 'L', false);

        $pdf->SetY($pdf->GetY() + 40);

        $pdf->SetFont('', '', 10);
        $pdf->MultiCell($width, 30, '<b>' . _CREATED_BY . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $creator['firstname'] . ' ' . $creator['lastname'] , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($width, 30, '<b>' . _CREATED . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $creationDate , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($width, 30, '<b>' . _SENT_DATE . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $sendDate, 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($width, 30, '<b>' . _FORMAT . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $acknowledgementReceipt['format'] , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($width, 30, '<b>' . _SENT_TO . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $displayContact , 1, 'L', false, 1, '', '', true, 0, true);


        $tmpDir = CoreConfigModel::getTmpPath();
        $filePathOnTmp = $tmpDir . 'convertedAr_' . $acknowledgementReceipt['id'] . '_SEPARATOR_' . $GLOBALS['id'] . '.pdf';
        $pdf->Output($filePathOnTmp, 'F');

        return $filePathOnTmp;
    }

    private static function getPathConvertedAcknowledgementReceipt(array $args)
    {
        ValidatorModel::notEmpty($args, ['acknowledgementReceipt', 'pathHtml']);
        ValidatorModel::arrayType($args, ['acknowledgementReceipt']);
        ValidatorModel::stringType($args, ['pathHtml']);

        $acknowledgementReceipt = $args['acknowledgementReceipt'];

        $contentHtml = file_get_contents($args['pathHtml']);

        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);
        $pdf->AddPage();

        $pdf->writeHTML($contentHtml);

        $tmpDir = CoreConfigModel::getTmpPath();
        $filePathOnTmp = $tmpDir . 'convertedAr_' . $acknowledgementReceipt['id'] . '_' . $GLOBALS['id'] . '.pdf';
        $pdf->Output($filePathOnTmp, 'F');

        return $filePathOnTmp;
    }

    private static function getAttachmentSeparator(array $args)
    {
        ValidatorModel::notEmpty($args, ['attachment', 'chronoResource']);
        ValidatorModel::arrayType($args, ['attachment']);
        ValidatorModel::stringType($args, ['chronoResource']);

        $attachment = $args['attachment'];
        $chronoResource = $args['chronoResource'];

        if ($attachment['recipient_type'] == 'user') {
            $displayContact = UserModel::getLabelledUserById(['id' => $attachment['recipient_id']]);
        } else if ($attachment['recipient_type'] == 'contact') {
            $contact = ContactModel::getById([
                'select' => ['id', 'firstname', 'lastname', 'email', 'address_number', 'address_street', 'address_postcode',
                             'address_town', 'address_country', 'company'],
                'id'     => $attachment['recipient_id']
            ]);
            $displayContact = ContactController::getFormattedContactWithAddress([
                'contact' => $contact
            ]);
            $displayContact = $displayContact['contact']['otherInfo'];
        }

        $creator = UserModel::getByLogin(['login' => $attachment['typist']]);

        $status = StatusModel::getById(['id' => $attachment['status'], 'select' => ['label_status']]);
        $status = $status['label_status'];

        $attachmentTypes = AttachmentModel::getAttachmentsTypesByXML();
        $attachmentType = $attachmentTypes[$attachment['attachment_type']]['label'];

        $creationDate = new \DateTime($attachment['creation_date']);
        $creationDate = $creationDate->format('d-m-Y H:i');

        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);
        $pdf->AddPage();

        $dimensions     = $pdf->getPageDimensions();
        $widthNoMargins = $dimensions['w'] - $dimensions['rm'] - $dimensions['lm'];
        $width          = $widthNoMargins / 2;

        $pdf->SetFont('', 'B', 12);
        $pdf->Cell($width, 15, _ATTACHMENT, 0, 0, 'L', false);
        $pdf->Cell($width, 15, $attachment['identifier'], 0, 1, 'C', false);

        $pdf->SetY($pdf->GetY() + 40);
        $pdf->SetFont('', '', 10);

        $pdf->MultiCell($width, 30, '<b>' . _CHRONO_NUMBER_MASTER . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $chronoResource , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($width, 30, '<b>' . _SUBJECT . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $attachment['title'], 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($width, 30, '<b>' . _CREATED_BY . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $creator['firstname'] . ' ' . $creator['lastname'] , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($width, 30, '<b>' . _CREATED . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $creationDate , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($width, 30, '<b>' . _FORMAT . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $attachment['format'] , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($width, 30, '<b>' . _STATUS . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $status , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($width, 30, '<b>' . _DOCTYPE . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $attachmentType , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($width, 30, '<b>' . _CONTACT . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $displayContact , 1, 'L', false, 1, '', '', true, 0, true);


        $tmpDir = CoreConfigModel::getTmpPath();
        $filePathOnTmp = $tmpDir . 'attachment_' . $attachment['res_id'] . '_SEPARATOR_' . $GLOBALS['id'] . '.pdf';
        $pdf->Output($filePathOnTmp, 'F');

        return $filePathOnTmp;
    }

    private static function getEmailFilePath(array $args)
    {
        ValidatorModel::notEmpty($args, ['email', 'resId']);
        ValidatorModel::arrayType($args, ['email']);
        ValidatorModel::intVal($args, ['resId']);

        $email = $args['email'];

        $date = new \DateTime($email['send_date']);
        $date = $date->format('d-m-Y H:i');

        $sentDate = _SENT_DATE . ' ' . $date;

        $sentBy = UserModel::getLabelledUserById(['id' => $email['user_id']]);

        $sender = json_decode($email['sender'], true);
        $sender = $sender['email'] ?? _UNDEFINED;

        $recipients = json_decode($email['recipients'], true);
        $recipients = implode(", ", $recipients);
        $recipients = !empty($recipients) ? $recipients : _UNDEFINED;

        $recipientsCopy = json_decode($email['cc'], true);
        $recipientsCopy = implode(", ", $recipientsCopy);
        $recipientsCopy = !empty($recipientsCopy) ? $recipientsCopy : _UNDEFINED;

        $recipientsCopyHidden = json_decode($email['cci'], true);
        $recipientsCopyHidden = implode(", ", $recipientsCopyHidden);
        $recipientsCopyHidden = !empty($recipientsCopyHidden) ? $recipientsCopyHidden : _UNDEFINED;

        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);
        $pdf->AddPage();

        $dimensions        = $pdf->getPageDimensions();
        $widthNoMargins    = $dimensions['w'] - $dimensions['rm'] - $dimensions['lm'];
        $width             = $widthNoMargins / 2;
        $widthQuarter      = $widthNoMargins / 4;
        $widthThreeQuarter = $widthQuarter * 3;

        $pdf->SetFont('', 'B', 12);
        $pdf->Cell($width, 15, _EMAIL, 0, 0, 'L', false);
        $pdf->SetFont('', '', 11);
        $pdf->Cell($width, 15, $sentDate, 0, 1, 'R', false);

        $pdf->SetY($pdf->GetY() + 5);
        $pdf->SetFont('', '', 10);

        $pdf->MultiCell($width, 15, '<b>' . _SENT_BY.'</b>', 1, 'C', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 15, '<b>' . _SENDER.'</b>', 1, 'C', false, 1, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $sentBy, 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $sender, 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($widthQuarter, 30, '<b>' . _RECIPIENTS . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($widthThreeQuarter, 30, $recipients, 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($widthQuarter, 30, '<b>' . _TO_CC . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($widthThreeQuarter, 30, $recipientsCopy , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($widthQuarter, 30, '<b>' . _TO_CCI . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($widthThreeQuarter, 30, $recipientsCopyHidden , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($widthQuarter, 30, '<b>' . _SUBJECT . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($widthThreeQuarter, 30, $email['object'] , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->SetY($pdf->GetY() + 5);

        $pdf->writeHTML($email['body']);

        $tmpDir = CoreConfigModel::getTmpPath();
        $filePathOnTmp = $tmpDir . 'email_' . $email['id'] . '_' . $GLOBALS['id'] . '.pdf';
        $pdf->Output($filePathOnTmp, 'F');

        return $filePathOnTmp;
    }

    private static function getSummarySheet(array $args)
    {
        ValidatorModel::notEmpty($args, ['units', 'resId']);
        ValidatorModel::arrayType($args, ['units']);
        ValidatorModel::intVal($args, ['resId']);

        $units = $args['units'];
        $resId = $args['resId'];

        $resource = ResModel::getById([
            'select' => ['res_id', 'alt_identifier', 'type_id', 'model_id', 'subject', 'admission_date', 'creation_date',
                         'doc_date', 'initiator', 'typist', 'category_id', 'status', 'priority', 'process_limit_date', 'destination'],
            'resId'  => $resId
        ]);

        $doctype = DoctypeModel::getById(['select' => ['description'], 'id' => $resource['type_id']]);
        $resource['type_label'] = $doctype['description'];

        $data = SummarySheetController::prepareData(['units' => $units, 'resourcesIds' => [$resId]]);

        $indexingFields = IndexingModelFieldModel::get([
            'select' => ['identifier', 'unit'],
            'where'  => ['model_id = ?'],
            'data'   => [$resource['model_id']]
        ]);
        $fieldsIdentifier = array_column($indexingFields, 'identifier');

        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);

        SummarySheetController::createSummarySheet($pdf, [ // TODO check missing border for date limite de traitement
            'resource'         => $resource,
            'units'            => $units,
            'login'            => $GLOBALS['userId'],
            'data'             => $data,
            'fieldsIdentifier' => $fieldsIdentifier
        ]);

        $tmpDir = CoreConfigModel::getTmpPath();
        $filePathOnTmp = $tmpDir . 'summarySheet_' . $resId . '_' . $GLOBALS['id'] . '.pdf';
        $pdf->Output($filePathOnTmp, 'F');

        return $filePathOnTmp;
    }
}
