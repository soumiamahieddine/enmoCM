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
use chillerlan\QRCode\QROptions;
use Entity\models\EntityModel;
use Entity\models\ListInstanceModel;
use Note\models\NoteEntityModel;
use Note\models\NoteModel;
use Priority\models\PriorityModel;
use Resource\models\ResModel;
use Resource\models\ResourceListModel;
use Respect\Validation\Validator;
use setasign\Fpdi\Tcpdf\Fpdi;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\TextFormatModel;
use SrcCore\models\ValidatorModel;
use Status\models\StatusModel;
use User\models\UserModel;

class SummarySheetController
{
    public function createList(Request $request, Response $response, array $aArgs)
    {
        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $errors = ResourceListController::listControl(['groupId' => $aArgs['groupId'], 'userId' => $aArgs['userId'], 'basketId' => $aArgs['basketId'], 'currentUserId' => $currentUser['id']]);
        if (!empty($errors['errors'])) {
            return $response->withStatus($errors['code'])->withJson(['errors' => $errors['errors']]);
        }

        $bodyData = $request->getParsedBody();
        $units = empty($bodyData['units']) ? [] : $bodyData['units'];

        if (!Validator::arrayType()->notEmpty()->validate($bodyData['resources'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Resources out of perimeter']);
        }

        $basket = BasketModel::getById(['id' => $aArgs['basketId'], 'select' => ['basket_clause', 'basket_res_order', 'basket_name']]);
        $user = UserModel::getById(['id' => $aArgs['userId'], 'select' => ['user_id']]);

        $whereClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'login' => $user['user_id']]);
        $rawResourcesInBasket = ResModel::getOnView([
            'select'    => ['res_id'],
            'where'     => [$whereClause]
        ]);
        $allResourcesInBasket = [];
        foreach ($rawResourcesInBasket as $resource) {
            $allResourcesInBasket[] = $resource['res_id'];
        }

        $order = 'CASE res_view_letterbox.res_id ';
        foreach ($bodyData['resources'] as $key => $resId) {
            if (!in_array($resId, $allResourcesInBasket)) {
                return $response->withStatus(403)->withJson(['errors' => 'Resources out of perimeter']);
            }
            $order .= "WHEN {$resId} THEN {$key} ";
        }
        $order .= 'END';

        $select = ['res_id', 'subject', 'alt_identifier'];
        foreach ($units as $unit) {
            $unit = (array)$unit;
            if ($unit['unit'] == 'primaryInformations') {
                $informations = ['admission_date', 'creation_date', 'nature_id', 'doc_date', 'type_label', 'initiator', 'typist'];
                $select = array_merge($select, $informations);
            } elseif ($unit['unit'] == 'secondaryInformations') {
                $informations = ['category_id', 'priority', 'process_limit_date', 'status'];
                $select = array_merge($select, $informations);
            } elseif ($unit['unit'] == 'diffusionList') {
                $informations = ['destination'];
                $select = array_merge($select, $informations);
            }
        }

        $resources = ResModel::getOnView([
            'select'    => $select,
            'where'     => ['res_view_letterbox.res_id in (?)'],
            'data'      => [$bodyData['resources']],
            'orderBy'   => [$order]
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
        $pdf->SetFont('', 'B', 12);
        $pdf->Cell(0, 20, _SUMMARY_SHEET, 0, 2, 'C', false);
        $pdf->SetY($pdf->GetY() - 20);
        $pdf->SetFont('', '', 8);
        $pdf->Cell(0, 20, "$appName / " . date('d-m-Y'), 0, 2, 'R', false);
        $pdf->Cell(0, 1, $resource['alt_identifier'], 0, 2, 'C', false);

        $pdf->SetY($pdf->GetY() + 15);
        $pdf->SetFont('', 'B', 16);
        $pdf->MultiCell(0, 1, $resource['subject'], 1, 'C', false);

        foreach ($units as $key => $unit) {
            $units[$key] = (array)$unit;
            $unit = (array)$unit;
            if ($unit['unit'] == 'qrcode') {
                $options = new QROptions([
                    'imageBase64'    => false,
                ]);
                $qrcode = new QRCode($options);
                $qrcodeBlob = $qrcode->render($resource['res_id']);
                $pdf->Image('@'.$qrcodeBlob, 21, 10, 50, 50);
            }
        }
        foreach ($units as $key => $unit) {
            if ($unit['unit'] == 'primaryInformations') {
                $nature = ResModel::getNatureLabel(['natureId' => $resource['nature_id']]);
                $nature = empty($nature) ? '<i>'._UNDEFINED.'</i>' : "<b>{$nature}</b>";
                $admissionDate = TextFormatModel::formatDate($resource['admission_date'], 'd-m-Y');
                $admissionDate = empty($admissionDate) ? '<i>'._UNDEFINED.'</i>' : "<b>{$admissionDate}</b>";
                $creationdate = TextFormatModel::formatDate($resource['creation_date'], 'd-m-Y');
                $creationdate = empty($creationdate) ? '<i>'._UNDEFINED.'</i>' : "<b>{$creationdate}</b>";
                $docDate = TextFormatModel::formatDate($resource['doc_date'], 'd-m-Y');
                $docDate = empty($docDate) ? '<i>'._UNDEFINED.'</i>' : "<b>{$docDate}</b>";
                $initiator = EntityModel::getByEntityId(['entityId' => $resource['initiator'], 'select' => ['short_label']]);
                $initiatorEntity = empty($initiator) ? '' : "({$initiator['short_label']})";
                $typist = UserModel::getLabelledUserById(['login' => $resource['typist']]);
                $doctype = empty($resource['type_label']) ? '<i>'._UNDEFINED.'</i>' : "<b>{$resource['type_label']}</b>";

                $pdf->SetY($pdf->GetY() + 40);
                if (($pdf->GetY() + 77) > $bottomHeight) {
                    $pdf->AddPage();
                }
                if (!($key == 0 || ($key == 1 && $units[0]['unit'] == 'qrcode'))) {
                    $pdf->SetFont('', 'B', 11);
                    $pdf->Cell(0, 15, $unit['label'], 0, 2, 'L', false);
                    $pdf->SetY($pdf->GetY() + 2);
                }

                $pdf->SetFont('', '', 10);
                $pdf->MultiCell($widthNoMargins / 10 * 4.5, 15, _DOC_DATE . " : {$docDate}", 0, 'L', false, 0, '', '', true, 0, true);
                $pdf->Cell($widthNoMargins / 10, 15, '', 0, 0, 'L', false);
                $pdf->MultiCell($widthNoMargins / 10 * 4.5, 15, _ADMISSION_DATE . " : {$admissionDate}", 0, 'L', false, 1, '', '', true, 0, true);
                $pdf->MultiCell($widthNoMargins / 10 * 4.5, 15, _NATURE . " : {$nature}", 0, 'L', false, 0, '', '', true, 0, true);
                $pdf->Cell($widthNoMargins / 10, 15, '', 0, 0, 'L', false);
                $pdf->MultiCell($widthNoMargins / 10 * 4.5, 15, _CREATED . " : {$creationdate}", 0, 'L', false, 1, '', '', true, 0, true);
                $pdf->MultiCell($widthNoMargins / 10 * 4.5, 15, _DOCTYPE . " : {$doctype}", 0, 'L', false, 0, '', '', true, 0, true);
                $pdf->Cell($widthNoMargins / 10, 15, '', 0, 0, 'L', false);
                $pdf->MultiCell($widthNoMargins / 10 * 4.5, 15, _TYPIST . " : <b>{$typist} {$initiatorEntity}</b>", 0, 'L', false, 1, '', '', true, 0, true);
            } elseif ($unit['unit'] == 'secondaryInformations') {
                $category = ResModel::getCategoryLabel(['categoryId' => $resource['category_id']]);
                $category = empty($category) ? '<i>'._UNDEFINED.'</i>' : "<b>{$category}</b>";
                $status = StatusModel::getById(['id' => $resource['status'], 'select' => ['label_status']]);
                $status = empty($status['label_status']) ? '<i>'._UNDEFINED.'</i>' : "<b>{$status['label_status']}</b>";
                $priority = '';
                if (!empty($resource['priority'])) {
                    $priority = PriorityModel::getById(['id' => $resource['priority'], 'select' => ['label']]);
                }
                $priority = empty($priority['label']) ? '<i>'._UNDEFINED.'</i>' : "<b>{$priority['label']}</b>";
                $processLimitDate = TextFormatModel::formatDate($resource['process_limit_date'], 'd-m-Y');
                $processLimitDate = empty($processLimitDate) ? '<i>'._UNDEFINED.'</i>' : "<b>{$processLimitDate}</b>";

                $pdf->SetY($pdf->GetY() + 40);
                if (($pdf->GetY() + 57) > $bottomHeight) {
                    $pdf->AddPage();
                }
                $pdf->SetFont('', 'B', 11);
                $pdf->Cell(0, 15, $unit['label'], 0, 2, 'L', false);
                $pdf->SetY($pdf->GetY() + 2);

                $pdf->SetFont('', '', 10);
                $pdf->MultiCell($widthNoMargins / 2, 20, _CATEGORY . " : {$category}", 1, 'L', false, 0, '', '', true, 0, true);
                $pdf->MultiCell($widthNoMargins / 2, 20, _STATUS . " : {$status}", 1, 'L', false, 1, '', '', true, 0, true);
                $pdf->MultiCell($widthNoMargins / 2, 20, _PRIORITY . " : {$priority}", 1, 'L', false, 0, '', '', true, 0, true);
                $pdf->MultiCell($widthNoMargins / 2, 20, _PROCESS_LIMIT_DATE . " : {$processLimitDate}", 1, 'L', false, 1, '', '', true, 0, true);
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
                $destination = EntityModel::getByEntityId(['entityId' => $resource['destination'], 'select' => ['short_label']]);
                $destinationEntity = empty($destination) ? '' : "({$destination['short_label']})";

                $pdf->SetY($pdf->GetY() + 40);
                if (($pdf->GetY() + 37 + count($copies) * 20) > $bottomHeight) {
                    $pdf->AddPage();
                }
                $pdf->SetFont('', 'B', 11);
                $pdf->Cell(0, 15, $unit['label'], 0, 2, 'L', false);
                $pdf->SetY($pdf->GetY() + 2);

                $pdf->SetFont('', '', 10);
                $pdf->MultiCell($widthNoMargins / 6, 20, _ASSIGNEE, 1, 'C', false, 0, '', '', true, 0, false, true, 20, 'M');
                $pdf->SetFont('', 'B', 10);
                $pdf->Cell($widthNoMargins / 6 * 5, 20, "- {$assignee} {$destinationEntity}", 1, 1, 'L', false);
                if (!empty($copies)) {
                    $pdf->SetFont('', '', 10);
                    $pdf->MultiCell($widthNoMargins / 6, count($copies) * 20, _TO_CC, 1, 'C', false, 0, '', '', true, 0, false, true, count($copies) * 20, 'M');
                    foreach ($copies as $copy) {
                        $pdf->Cell($widthNoMargins / 6 * 5, 20, "- {$copy}", 1, 2, 'L', false);
                    }
                }
            } elseif ($unit['unit'] == 'visaWorkflow') {
                $users = [];
                $listInstances = ListInstanceModel::get([
                    'select'    => ['item_id', 'requested_signature', 'process_date'],
                    'where'     => ['difflist_type = ?', 'res_id = ?'],
                    'data'      => ['VISA_CIRCUIT', $resource['res_id']],
                    'orderBy'   => ['listinstance_id']
                ]);
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
                    $pdf->Cell($widthNoMargins / 4 * 3, 20, _USERS, 1, 0, 'L', false);
                    $pdf->Cell($widthNoMargins / 4, 20, _ACTION_DATE, 1, 1, 'L', false);
                    foreach ($users as $keyUser => $user) {
                        $pdf->Cell($widthNoMargins / 4 * 3, 20, $keyUser + 1 . ". {$user['user']} ({$user['mode']})", 1, 0, 'L', false);
                        $pdf->Cell($widthNoMargins / 4, 20, $user['date'], 1, 1, 'L', false);
                    }
                }
            } elseif ($unit['unit'] == 'opinionWorkflow') {
                $users = [];
                $listInstances = ListInstanceModel::get([
                    'select'    => ['item_id', 'process_date'],
                    'where'     => ['difflist_type = ?', 'res_id = ?'],
                    'data'      => ['AVIS_CIRCUIT', $resource['res_id']],
                    'orderBy'   => ['listinstance_id']
                ]);
                foreach ($listInstances as $listInstance) {
                    $users[] = [
                        'user'  => UserModel::getLabelledUserById(['login' => $listInstance['item_id']]),
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
                    $pdf->Cell($widthNoMargins / 4 * 3, 20, _USERS, 1, 0, 'L', false);
                    $pdf->Cell($widthNoMargins / 4, 20, _ACTION_DATE, 1, 1, 'L', false);
                    foreach ($users as $keyUser => $user) {
                        $pdf->Cell($widthNoMargins / 4 * 3, 20, $keyUser + 1 . ". {$user['user']}", 1, 0, 'L', false);
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
                        $pdf->SetFont('', 'B', 10);
                        $pdf->Cell($widthNoMargins / 2, 20, $note['user'], 1, 0, 'L', false);
                        $pdf->SetFont('', '', 10);
                        $pdf->Cell($widthNoMargins / 2, 20, $note['date'], 1, 1, 'L', false);
                        $pdf->MultiCell(0, 40, $note['note'], 1, 'L', false);
                        $pdf->SetY($pdf->GetY() + 5);
                    }
                }
            } elseif (strpos($unit['unit'], 'freeField') !== false) {
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
