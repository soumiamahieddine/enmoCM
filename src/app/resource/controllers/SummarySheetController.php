<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Summary Sheet Controller
* @author dev@maarch.org
*/

namespace Resource\controllers;

use Basket\models\BasketModel;
use chillerlan\QRCode\QRCode;
use Entity\models\EntityModel;
use Entity\models\ListInstanceModel;
use Note\models\NoteEntityModel;
use Note\models\NoteModel;
use Priority\models\PriorityModel;
use Resource\models\ResModel;
use Resource\models\ResourceListModel;
use setasign\Fpdi\Tcpdf\Fpdi;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\TextFormatModel;
use SrcCore\models\ValidatorModel;
use Status\models\StatusModel;
use User\models\UserModel;

class SummarySheetController
{
    public function getList(Request $request, Response $response, array $aArgs)
    {
        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $errors = ResourceListController::listControl(['groupId' => $aArgs['groupId'], 'userId' => $aArgs['userId'], 'basketId' => $aArgs['basketId'], 'currentUserId' => $currentUser['id']]);
        if (!empty($errors['errors'])) {
            return $response->withStatus($errors['code'])->withJson(['errors' => $errors['errors']]);
        }

        $queryParamsData = $request->getQueryParams();
//        $queryParamsData['units'] = base64_encode(json_encode([
//            ['label' => 'Informations sur le courrier', 'unit' => 'informations'],
//            ['label' => 'Liste de diffusion', 'unit' => 'diffusionList'],
//            ['label' => 'Ptit avis les potos.', 'unit' => 'freeField'],
//            ['label' => 'Circuit de visa', 'unit' => 'visaWorkflow'],
//            ['label' => 'Annotation(s)', 'unit' => 'notes'],
//            ['label' => 'Commentaires', 'unit' => 'freeField'],
//            ['unit' => 'qrcode']
//        ]));
        $units = empty($queryParamsData['units']) ? [] : (array)json_decode(base64_decode($queryParamsData['units']));

        $basket = BasketModel::getById(['id' => $aArgs['basketId'], 'select' => ['basket_clause', 'basket_res_order', 'basket_name']]);
        $user = UserModel::getById(['id' => $aArgs['userId'], 'select' => ['user_id']]);

        $allQueryData = ResourceListController::getResourcesListQueryData(['data' => $queryParamsData, 'basketClause' => $basket['basket_clause'], 'login' => $user['user_id']]);
        if (!empty($allQueryData['order'])) {
            $queryParamsData['order'] = $allQueryData['order'];
        }

        $select = ['res_id', 'subject'];
        foreach ($units as $unit) {
            $unit = (array)$unit;
            if ($unit['unit'] == 'informations') {
                $informations = [
                    'alt_identifier', 'category_id', 'priority', 'admission_date', 'process_limit_date', 'creation_date', 'nature_id',
                    'doc_date', 'type_label', 'status', 'subject', 'initiator', 'destination', 'typist', 'closing_date'
                ];
                $select = array_merge($select, $informations);
            }
        }
        $resources = ResourceListModel::getOnView([
            'select'    => $select,
            'table'     => $allQueryData['table'],
            'leftJoin'  => $allQueryData['leftJoin'],
            'where'     => $allQueryData['where'],
            'data'      => $allQueryData['queryData'],
            'orderBy'   => empty($queryParamsData['order']) ? [$basket['basket_res_order']] : [$queryParamsData['order']]
        ]);

        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);

        foreach ($resources as $resource) {
            SummarySheetController::createSummarySheet($pdf, ['resource' => $resource, 'units' => $units, 'login' => $GLOBALS['userId']]);
        }

        $fileContent = $pdf->Output('', 'S');
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($fileContent);

        $response->write($fileContent);
        $response = $response->withAddedHeader('Content-Disposition', "inline; filename=maarch.pdf");

        return $response->withHeader('Content-Type', $mimeType);
    }

    private static function createSummarySheet(Fpdi $pdf, array $args)
    {
        ValidatorModel::notEmpty($args, ['resource', 'login']);
        ValidatorModel::arrayType($args, ['resource', 'units']);
        ValidatorModel::stringType($args, ['login']);

        $resource = $args['resource'];
        $units = $args['units'];

        $pdf->AddPage();
        $dimensions = $pdf->getPageDimensions();
        $widthNoMargins = $dimensions['w'] - $dimensions['rm'] - $dimensions['lm'];
        $bottomHeight = $dimensions['h'] - $dimensions['bm'];

        $appName = CoreConfigModel::getApplicationName();
        $pdf->SetFont('', '', 8);
        $pdf->Cell(0, 20, $appName, 0, 2, 'C', false);
        $pdf->SetY($pdf->GetY() - 20);
        $pdf->Cell(0, 20, date('d-m-Y'), 0, 2, 'R', false);

        $pdf->SetY($pdf->GetY() + 10);
        $pdf->SetFont('', 'B', 18);
        $pdf->Cell(0, 20, "Fiche de liaison", 1, 2, 'C', false);

        $pdf->SetY($pdf->GetY() + 30);
        $pdf->SetFont('', 'I', 14);
        $pdf->MultiCell(0, 1, $resource['subject'], 0, 'L', false);

        foreach ($units as $unit) {
            $unit = (array)$unit;
            if ($unit['unit'] == 'qrcode') {
                $qrcode = new QRCode();
                $qrcodePath = CoreConfigModel::getTmpPath() . rand() . '_qr.png';
                $qrcode->render($resource['res_id'], $qrcodePath);
                $pdf->Image($qrcodePath, 21, 10, 50, 50);
            }
        }
        foreach ($units as $unit) {
            $unit = (array)$unit;

            if ($unit['unit'] == 'informations') {
                $category = ResModel::getCategoryLabel(['categoryId' => $resource['category_id']]);
                $nature = ResModel::getNatureLabel(['natureId' => $resource['nature_id']]);
                $status = StatusModel::getById(['id' => $resource['status'], 'select' => ['label_status']]);
                $priority = '';
                if (!empty($resource['priority'])) {
                    $priority = PriorityModel::getById(['id' => $resource['priority'], 'select' => ['label']])['label'];
                }

                $admissionDate = TextFormatModel::formatDate($resource['admission_date'], 'd-m-Y');
                $processLimitDate = TextFormatModel::formatDate($resource['process_limit_date'], 'd-m-Y');
                $creationdate = TextFormatModel::formatDate($resource['creation_date'], 'd-m-Y');
                $docDate = TextFormatModel::formatDate($resource['doc_date'], 'd-m-Y');
                $closingDate = TextFormatModel::formatDate($resource['closing_date'], 'd-m-Y');

                $initiator = EntityModel::getByEntityId(['entityId' => $resource['initiator'], 'select' => ['short_label']]);
                $initiatorEntity = empty($initiator) ? '' : $initiator['short_label'];
                $destination = EntityModel::getByEntityId(['entityId' => $resource['destination'], 'select' => ['short_label']]);
                $destinationEntity = empty($destination) ? '' : $destination['short_label'];
                $typist = UserModel::getLabelledUserById(['login' => $resource['typist']]);

                $pdf->SetY($pdf->GetY() + 40);
                if (($pdf->GetY() + 157) > $bottomHeight) {
                    $pdf->AddPage();
                }
                $pdf->SetFont('', 'B', 11);
                $pdf->Cell(0, 15, $unit['label'], 0, 2, 'L', false);
                $pdf->SetY($pdf->GetY() + 2);

                $pdf->SetFont('', '', 10);
                $pdf->Cell($widthNoMargins / 2, 20, "Catégorie : {$category}", 1, 0, 'L', false);
                $pdf->Cell($widthNoMargins / 2, 20, "Numéro chrono : {$resource['alt_identifier']}", 1, 1, 'L', false);
                $pdf->Cell($widthNoMargins / 2, 20, "Entité initiatrice : {$initiatorEntity}", 1, 0, 'L', false);
                $pdf->Cell($widthNoMargins / 2, 20, "Entité traitante : {$destinationEntity}", 1, 1, 'L', false);
                $pdf->Cell($widthNoMargins / 2, 20, "Créé le : {$creationdate}", 1, 0, 'L', false);
                $pdf->Cell($widthNoMargins / 2, 20, "Date du courrier : {$docDate}", 1, 1, 'L', false);
                $pdf->Cell($widthNoMargins / 2, 20, "Date d'arrivée : {$admissionDate}", 1, 0, 'L', false);
                $pdf->Cell($widthNoMargins / 2, 20, "Date limite de traitement : {$processLimitDate}", 1, 1, 'L', false);
                $pdf->Cell($widthNoMargins / 2, 20, "Priorité : {$priority}", 1, 0, 'L', false);
                $pdf->Cell($widthNoMargins / 2, 20, "Statut : {$status['label_status']}", 1, 1, 'L', false);
                $pdf->Cell($widthNoMargins / 2, 20, "Type de document : {$resource['type_label']}", 1, 0, 'L', false);
                $pdf->Cell($widthNoMargins / 2, 20, "Nature : {$nature}", 1, 1, 'L', false);
                $pdf->Cell($widthNoMargins / 2, 20, "Opérateur : {$typist}", 1, 0, 'L', false);
                $pdf->Cell($widthNoMargins / 2, 20, "Date de clôture : {$closingDate}", 1, 1, 'L', false);
            } elseif ($unit['unit'] == 'diffusionList') {
                $assignee = '';
                $copies = [];
                $listInstances = ListInstanceModel::get(['select' => ['item_id', 'item_type', 'item_mode'], 'where' => ['difflist_type = ?', 'res_id = ?'], 'data' => ['entity_id', $resource['res_id']]]);
                foreach ($listInstances as $listInstance) {

                    $item = '';
                    if ($listInstance['item_type'] == 'user_id') {
                        $item = UserModel::getLabelledUserById(['login' => $listInstance['item_id']]);
                    } elseif ($listInstance['item_type'] == 'entity_id') {
                        $item = $listInstance['item_id'];
                        $entity = EntityModel::getByEntityId(['entityId' => $listInstance['item_id'], 'select' => ['short_label']]);
                        if (!empty($entity)) {
                            $item = "{$entity['short_label']} ({$item})";
                        }
                    }
                    if ($listInstance['item_mode'] == 'dest') {
                        $assignee = $item;
                    } else {
                        $copies[] = $item;
                    }
                }

                $pdf->SetY($pdf->GetY() + 40);
                if (($pdf->GetY() + 37 + count($copies) * 20) > $bottomHeight) {
                    $pdf->AddPage();
                }
                $pdf->SetFont('', 'B', 11);
                $pdf->Cell(0, 15, $unit['label'], 0, 2, 'L', false);
                $pdf->SetY($pdf->GetY() + 2);

                $pdf->SetFont('', '', 10);
                $pdf->MultiCell($widthNoMargins / 6, 20, "Attributaire", 1, 'C', false, 0, '', '', true, 0, false, true, 20, 'M');
                $pdf->Cell($widthNoMargins / 6 * 5, 20, "- {$assignee}", 1, 1, 'L', false);
                if (!empty($copies)) {
                    $pdf->MultiCell($widthNoMargins / 6, count($copies) * 20, "En copie", 1, 'C', false, 0, '', '', true, 0, false, true, count($copies) * 20, 'M');
                    foreach ($copies as $copy) {
                        $pdf->Cell($widthNoMargins / 6 * 5, 20, "- {$copy}", 1, 2, 'L', false);
                    }
                }
            } elseif ($unit['unit'] == 'visaWorkflow') {
                $users = [];
                $listInstances = ListInstanceModel::get(['select' => ['item_id', 'requested_signature', 'process_date'], 'where' => ['difflist_type = ?', 'res_id = ?'], 'data' => ['VISA_CIRCUIT', $resource['res_id']]]);
                foreach ($listInstances as $listInstance) {
                    $users[] = [
                        'user'  => UserModel::getLabelledUserById(['login' => $listInstance['item_id']]),
                        'mode'  => $listInstance['requested_signature'] ? 'Signataire' : 'Viseur',
                        'date'  => TextFormatModel::formatDate($listInstance['process_date']),
                    ];
                }

                if (!empty($users)) {
                    $pdf->SetY($pdf->GetY() + 40);
                    if (($pdf->GetY() + 37 + count($users) * 20) > $bottomHeight) {
                        $pdf->AddPage();
                    }
                    $pdf->SetFont('', 'B', 11);
                    $pdf->Cell(0, 15, $unit['label'], 0, 2, 'L', false);
                    $pdf->SetY($pdf->GetY() + 2);

                    $pdf->SetFont('', '', 10);
                    $pdf->Cell($widthNoMargins / 4 * 3, 20, "Utilisateurs", 1, 0, 'L', false);
                    $pdf->Cell($widthNoMargins / 4, 20, "Date d'action", 1, 1, 'L', false);
                    foreach ($users as $key => $user) {
                        $pdf->Cell($widthNoMargins / 4 * 3, 20, $key + 1 . ". {$user['user']} ({$user['mode']})", 1, 0, 'L', false);
                        $pdf->Cell($widthNoMargins / 4, 20, $user['date'], 1, 1, 'L', false);
                    }
                }
            } elseif ($unit['unit'] == 'notes') {
                $notes = [];
                $userEntities = [];
                $rawNotes = NoteModel::get(['select' => ['id', 'note_text', 'user_id', 'date_note'], 'where' => ['identifier = ?'], 'data' => [$resource['res_id']]]);
                if (!empty($rawNotes)) {
                    $rawUserEntities = EntityModel::getByLogin(['login' => $args['login'], 'select' => ['entity_id']]);
                    foreach ($rawUserEntities as $rawUserEntity) {
                        $userEntities[] = $rawUserEntity['entity_id'];
                    }
                }
                foreach ($rawNotes as $rawNote) {
                    $allowed = false;
                    if ($rawNote['user_id'] == $args['login']) {
                        $allowed = true;
                    } else {
                        $noteEntities = NoteEntityModel::get(['select' => ['item_id'], 'where' => ['note_id = ?'], 'data' => [$rawNote['id']]]);
                        if (!empty($noteEntities)) {
                            foreach ($noteEntities as $noteEntity) {
                                if (in_array($noteEntity['item_id'], $userEntities)) {
                                    $allowed = true;
                                    break;
                                }
                            }
                        } else {
                            $allowed = true;
                        }
                    }
                    if ($allowed) {
                        $notes[] = [
                            'user'  => UserModel::getLabelledUserById(['login' => $rawNote['user_id']]),
                            'date'  => TextFormatModel::formatDate($rawNote['date_note']),
                            'note'  => $rawNote['note_text']
                        ];
                    }
                }

                if (!empty($notes)) {
                    $pdf->SetY($pdf->GetY() + 40);
                    if (($pdf->GetY() + 80) > $bottomHeight) {
                        $pdf->AddPage();
                    }

                    $pdf->SetFont('', 'B', 11);
                    $pdf->Cell(0, 15, $unit['label'], 0, 2, 'L', false);

                    $pdf->SetY($pdf->GetY() + 2);
                    $pdf->SetFont('', '', 10);

                    foreach ($notes as $note) {
                        if (($pdf->GetY() + 65) > $bottomHeight) {
                            $pdf->AddPage();
                        }
                        $pdf->Cell($widthNoMargins / 2, 20, $note['user'], 1, 0, 'L', false);
                        $pdf->Cell($widthNoMargins / 2, 20, $note['date'], 1, 1, 'L', false);
                        $pdf->MultiCell(0, 40, $note['note'], 1, 'L', false);
                        $pdf->SetY($pdf->GetY() + 5);
                    }
                }
            } elseif ($unit['unit'] == 'freeField') {
                $pdf->SetY($pdf->GetY() + 40);
                if (($pdf->GetY() + 77) > $bottomHeight) {
                    $pdf->AddPage();
                }
                $pdf->SetFont('', 'B', 11);
                $pdf->Cell(0, 15, $unit['label'], 0, 2, 'L', false);

                $pdf->SetY($pdf->GetY() + 2);
                $pdf->Cell(0, 60, '', 1, 2, 'L', false);
            }
        }
    }
}
