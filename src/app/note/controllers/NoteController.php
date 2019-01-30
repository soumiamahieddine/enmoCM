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

class NoteController
{
    public function getByResId(Request $request, Response $response, array $aArgs)
    {
        $check = Validator::intVal()->validate($aArgs['resId']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $aNotes = NoteModel::getByResId(['select' => ['notes.id', 'firstname', 'lastname', 'entity_label', 'note_text', 'date_note'], 'resId' => $aArgs['resId'], 'orderBy' => ['date_note DESC']]);

        return $response->withJson($aNotes);
    }

    public function create(Request $request, Response $response, array $aArgs)
    {
        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['note_text']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request note text']);
        }
        
        if (isset($data['entities_chosen'])) {
            if (!Validator::arrayType()->validate($data['entities_chosen'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Bad Request entities chosen']);
            }
            foreach ($data['entities_chosen'] as $entityId) {
                if ($entityId == null) {
                    return $response->withStatus(400)->withJson(['errors' => 'Bad Request entities chosen']);
                }
                
                $entity = entitymodel::getByEntityId(['select' => ['id'], 'entityId' => $entityId]);
                if (empty($entity['id'])) {
                    return $response->withStatus(400)->withJson(['errors' => 'Bad Request entities chosen']);
                }
            }
        }

        if (!ResController::hasRightByResId(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }
        
        $data['identifier'] = $aArgs['resId'];
        
        $noteId = NoteModel::create($data);
    
        //Insert relation note with entities in note_entities_table
        if (!empty($noteId) && !empty($data['entities_chosen'])) {
            foreach ($data['entities_chosen'] as $entity) {
                NoteEntityModel::create(['item_id' => $entity, 'note_id' => $noteId ]);
            }
        }

        HistoryController::add(
            [
            'tableName' => "notes",
            'recordId'  => $noteId,
            'eventType' => "ADD",
            'userId'    => $GLOBALS['userId'],
            'info'      => _NOTE_ADDED . " (" . $noteId . ")",
            'moduleId'  => 'notes',
            'eventId'   => 'noteadd']
        );

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
            $note = NoteModel::getById(['id' => $noteId, 'select' => ['note_text', 'date_note', 'user_id']]);

            $user = UserModel::getByLogin(['login' => $note['user_id'], 'select' => ['firstname', 'lastname']]);
            $date = new \DateTime($note['date_note']);
            $date = $date->format('d-m-Y H:i');

            $pdf->Cell(0, 20, "{$user['firstname']} {$user['lastname']} : {$date}", 1, 2, 'C', false);
            $pdf->MultiCell(0, 20, $note['note_text'], 1, 'L', false);
            $pdf->SetY($pdf->GetY() + 40);
        }
        $fileContent = $pdf->Output('', 'S');

        return ['encodedDocument' => base64_encode($fileContent)];
    }
}
