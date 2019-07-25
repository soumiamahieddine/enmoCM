<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Note Controller
 * @author dev@maarch.org
 * @ingroup core
 */

namespace Note\controllers;

use Note\models\NoteModel;
use Note\models\NoteEntityModel;
use Entity\models\EntityModel;
use Respect\Validation\Validator;
use setasign\Fpdi\Tcpdf\Fpdi;
use Slim\Http\Request;
use Slim\Http\Response;
use History\controllers\HistoryController;
use Resource\controllers\ResController;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;
use Template\models\TemplateModel;
use Resource\models\ResModel;

class NoteController
{
    public function getByResId(Request $request, Response $response, array $aArgs)
    {
        $check = Validator::intVal()->notEmpty()->validate($aArgs['resId']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'resId is empty or not an integer']);
        }

        if (!ResController::hasRightByResId(['resId' => [$aArgs['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $user = UserModel::getByLogin(['select' => ['id'], 'login' => $GLOBALS['userId']]);
        $aNotes = NoteModel::getByUserIdForResource(['select' => ['*'], 'resId' => $aArgs['resId'], 'userId' => $user['id']]);
        
        foreach ($aNotes as $key => $aNote) {
            $aUser = UserModel::getByLogin(['select' => ['firstname', 'lastname'], 'login' => $aNote['user_id']]);
            $primaryEntity = UserModel::getPrimaryEntityByUserId(['userId' => $aNote['user_id']]);
            $aNotes[$key]['firstname'] = $aUser['firstname'];
            $aNotes[$key]['lastname'] = $aUser['lastname'];
            $aNotes[$key]['entity_label'] = $primaryEntity['entity_label'];
        }

        return $response->withJson($aNotes);
    }

    public function create(Request $request, Response $response, array $aArgs)
    {
        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['note_text']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Data note_text is empty or not a string']);
        }

        if (!ResController::hasRightByResId(['resId' => [$aArgs['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }
        
        if (isset($data['entities_chosen'])) {
            if (!Validator::arrayType()->validate($data['entities_chosen'])) {
                return $response->withStatus(400)->withJson(['errors' => 'entities_chosen is not an array']);
            }
            foreach ($data['entities_chosen'] as $entityId) {
                if ($entityId == null) {
                    return $response->withStatus(400)->withJson(['errors' => 'Bad Request entities chosen']);
                }
                
                $entity = Entitymodel::getByEntityId(['select' => ['id'], 'entityId' => $entityId]);
                if (empty($entity['id'])) {
                    return $response->withStatus(400)->withJson(['errors' => 'Bad Request entities chosen']);
                }
            }
        }

        $noteId = NoteModel::create([
            'resId'     => $aArgs['resId'],
            'login'     => $GLOBALS['userId'],
            'note_text' => $data['note_text']
        ]);
    
        if (!empty($noteId) && !empty($data['entities_chosen'])) {
            foreach ($data['entities_chosen'] as $entity) {
                NoteEntityModel::create(['item_id' => $entity, 'note_id' => $noteId]);
            }
        }

        HistoryController::add([
            'tableName' => "notes",
            'recordId'  => $noteId,
            'eventType' => "ADD",
            'userId'    => $GLOBALS['userId'],
            'info'      => _NOTE_ADDED . " (" . $noteId . ")",
            'moduleId'  => 'notes',
            'eventId'   => 'noteadd'
        ]);

        return $response->withJson(['noteId' => $noteId]);
    }

    public static function getEncodedPdfByIds(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['ids']);
        ValidatorModel::arrayType($aArgs, ['ids']);

        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);
        $pdf->AddPage();

        foreach ($aArgs['ids'] as $noteId) {
            $note = NoteModel::getById(['id' => $noteId, 'select' => ['note_text', 'creation_date', 'user_id']]);

            $user = UserModel::getByLogin(['login' => $note['user_id'], 'select' => ['firstname', 'lastname']]);
            $date = new \DateTime($note['creation_date']);
            $date = $date->format('d-m-Y H:i');

            $pdf->Cell(0, 20, "{$user['firstname']} {$user['lastname']} : {$date}", 1, 2, 'C', false);
            $pdf->MultiCell(0, 20, $note['note_text'], 1, 'L', false);
            $pdf->SetY($pdf->GetY() + 40);
        }
        $fileContent = $pdf->Output('', 'S');

        return ['encodedDocument' => base64_encode($fileContent)];
    }

    public static function getTemplates(Request $request, Response $response)
    {
        $query = $request->getQueryParams();

        if (!empty($query['resId']) && is_numeric($query['resId'])) {
            if (!ResController::hasRightByResId(['resId' => [$query['resId']], 'userId' => $GLOBALS['id']])) {
                return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
            }

            $resource = ResModel::getById(['resId' => $query['resId'], 'select' => ['destination']]);

            if (!empty($resource['destination'])) {
                $templates = TemplateModel::getWithAssociation([
                    'select'    => ['DISTINCT(templates.template_id), template_label', 'template_content'],
                    'where'     => ['template_target = ?', 'value_field = ?', 'templates.template_id = templates_association.template_id'],
                    'data'      => ['notes', $resource['destination']],
                    'orderBy'   => ['template_label']
                ]);
            } else {
                $templates = TemplateModel::getByTarget(['template_target' => 'notes', 'select' => ['template_label', 'template_content']]);
            }
        } else {
            $templates = TemplateModel::getByTarget(['template_target' => 'notes', 'select' => ['template_label', 'template_content']]);
        }

        return $response->withJson(['templates' => $templates]);
    }
}
