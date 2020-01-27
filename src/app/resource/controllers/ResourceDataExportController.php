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
    public static function get(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['resId']) || !ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $queryParams = $request->getQueryParams();

        $documentPaths = [];

        if (!empty($queryParams['document'])) {
            $document = ResModel::getById([
                'select' => ['res_id', 'docserver_id', 'path', 'filename', 'fingerprint', 'category_id', 'alt_identifier'],
                'resId'  => $args['resId']
            ]);
            if (empty($document)) {
                return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
            }

            if (empty($document['filename'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Document has no file']);
            }

            $path = ResourceDataExportController::getDocumentFilePath(['document' => $document, 'collId' => 'letterbox_coll']);

            $documentPaths[] = $path;
        }

        if (!empty($queryParams['attachments'])) {
            if (!Validator::arrayType()->validate($queryParams['attachments'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Query param attachments is not an array']);
            }
            if (!ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
                return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
            }
            $attachments = AttachmentModel::get([
                'select' => ['*'],
                'where'  => ['res_id in (?)', 'status not in (?)'],
                'data'   => [$queryParams['attachments'], ['DEL']],
            ]);

            if (count($attachments) < count($queryParams['attachments'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Attachment(s) not found']);
            }

            foreach ($attachments as $attachment) {
                if ($attachment['res_id_master'] != $args['resId']) {
                    return $response->withStatus(400)->withJson(['errors' => 'Attachment not linked to resource']);
                }

                $documentPaths[] = ResourceDataExportController::getAttachmentSeparator(['attachment' => $attachment]);

                $path = ResourceDataExportController::getDocumentFilePath(['document' => $attachment, 'collId' => 'attachments_coll']);

                if (!empty($path['errors'])) {
                    return $response->withStatus($path['code'])->withJson(['errors' => $path['errors']]);
                }

                $documentPaths[] = $path;
            }
        }

        if (!empty($queryParams['notes'])) {
            if (!Validator::arrayType()->validate($queryParams['notes'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Query param notes is not an array']);
            }

            $noteFilePath = ResourceDataExportController::getNotesFilePath(['notes' => $queryParams['notes'], 'resId' => $args['resId']]);

            if (!empty($noteFilePath['errors'])) {
                return $response->withStatus($noteFilePath['code'])->withJson(['errors' => $noteFilePath['errors']]);
            }

            if (file_exists($noteFilePath)) {
                $documentPaths[] = $noteFilePath;
            } else {
                return $response->withStatus(500)->withJson(['errors' => 'Notes file not created']);
            }
        }

        if (!empty($queryParams['acknowledgementReceipts'])) {
            if (!Validator::arrayType()->validate($queryParams['acknowledgementReceipts'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Query param acknowledgementReceipts is not an array']);
            }

            $acknowledgementReceipts = AcknowledgementReceiptModel::getByIds([
                'select' => ['*'],
                'ids'    => $queryParams['acknowledgementReceipts']
            ]);

            if (count($acknowledgementReceipts) < count($queryParams['acknowledgementReceipts'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Acknowledgement Receipt(s) not found']);
            }

            foreach ($acknowledgementReceipts as $acknowledgementReceipt) {
                if ($acknowledgementReceipt['res_id'] != $args['resId']) {
                    return $response->withStatus(400)->withJson(['errors' => 'Acknowledgement Receipt not linked to resource']);
                }

                $documentPaths[] = ResourceDataExportController::getAcknowledgementReceiptSeparator(['acknowledgementReceipt' => $acknowledgementReceipt]);

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

        if (!empty($documentPaths)) {
            $tmpDir = CoreConfigModel::getTmpPath();
            $filePathOnTmp = $tmpDir . 'mergedFile2.pdf';
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

        $notesModel = NoteModel::get([
            'where' => ['id in (?)'],
            'data' => [$args['notes']]
        ]);

        if (count($notesModel) < count($args['notes'])) {
            return ['errors' => 'Note(s) not found', 'code' => 400];
        }

        foreach ($notesModel as $note) {
            if ($note['identifier'] != $args['resId']) {
                return ['errors' => 'Note not linked to resource', 'code' => 400];
            }

            $user = UserModel::getById(['id' => $note['user_id'], 'select' => ['firstname', 'lastname']]);
            $userName = $user['firstname'] . ' ' . $user['lastname'];

            $noteText = str_replace('←', '<=', $note['note_text']);

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
        $acknowledgementReceipt = $args['acknowledgementReceipt'];

        $contact = ContactModel::getById([
            'select' => ['*'],
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
        $pdf->MultiCell($width, 30, $acknowledgementReceipt['creation_date'] , 1, 'L', false, 1, '', '', true, 0, true);

        if ($acknowledgementReceipt['format'] == 'html') {
            $pdf->MultiCell($width, 30, '<b>' . _SENT_DATE . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
            $pdf->MultiCell($width, 30, $acknowledgementReceipt['send_date'], 1, 'L', false, 1, '', '', true, 0, true);
        }

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
        $acknowledgementReceipt = $args['acknowledgementReceipt'];
        $pathHtml = $args['pathHtml'];

        $contentHtml = file_get_contents($pathHtml);

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
        $attachment = $args['attachment'];

        if ($attachment['recipient_type'] == 'user') {
            $displayContact = UserModel::getLabelledUserById(['id' => $attachment['recipient_id']]);
        } else if ($attachment['recipient_type'] == 'contact') {
            $contact = ContactModel::getById([
                'select' => ['*'],
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

        $pdf->MultiCell($width, 30, '<b>' . _CHRONO_NUMBER . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $attachment['identifier'] , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($width, 30, '<b>' . _SUBJECT . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $attachment['title'] . ' ' . $creator['lastname'] , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($width, 30, '<b>' . _CREATED_BY . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $creator['firstname'] . ' ' . $creator['lastname'] , 1, 'L', false, 1, '', '', true, 0, true);

        $pdf->MultiCell($width, 30, '<b>' . _CREATED . '</b>', 1, 'L', false, 0, '', '', true, 0, true);
        $pdf->MultiCell($width, 30, $attachment['creation_date'] , 1, 'L', false, 1, '', '', true, 0, true);

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
}
