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
    public function get(Request $request, Response $response, array $aArgs)
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
            $user = UserModel::getById(['select' => ['firstname', 'lastname', 'user_id'], 'id' => $aNote['user_id']]);
            $primaryEntity = UserModel::getPrimaryEntityByUserId(['userId' => $user['user_id']]);
            $aNotes[$key]['firstname'] = $user['firstname'];
            $aNotes[$key]['lastname'] = $user['lastname'];
            $aNotes[$key]['entity_label'] = $primaryEntity['entity_label'];
        }

        return $response->withJson($aNotes);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        if (!ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        if (!NoteController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Note out of perimeter']);
        }
        $note = NoteModel::getById(['id' => $args['id']]);

        $entities = NoteEntityModel::get(['select' => ['item_id'], 'where' => ['note_id = ?'], 'data' => [$args['id']]]);
        $entities = array_column($entities, 'item_id');

        $note['entities'] = $entities;

        return $response->withJson($note);
    }

    public function create(Request $request, Response $response, array $aArgs)
    {
        if (!ResController::hasRightByResId(['resId' => [$aArgs['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['value'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Data value is empty or not a string']);
        }

        if (!empty($body['entities'])) {
            if (!Validator::arrayType()->validate($body['entities'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Body entities is not an array']);
            }
            $entities = Entitymodel::get(['select' => ['count(1)'], 'where' => ['entity_id in (?)'], 'data' => [$body['entities']]]);
            if ($entities[0]['count'] != count($body['entities'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Body entities : one or more entities do not exist']);
            }
        }

        $noteId = NoteModel::create([
            'resId'     => $aArgs['resId'],
            'user_id'   => $GLOBALS['id'],
            'note_text' => $body['value']
        ]);
    
        if (!empty($noteId) && !empty($body['entities'])) {
            foreach ($body['entities'] as $entity) {
                NoteEntityModel::create(['item_id' => $entity, 'note_id' => $noteId]);
            }
        }

        HistoryController::add([
            'tableName' => "notes",
            'recordId'  => $noteId,
            'eventType' => "ADD",
            'info'      => _NOTE_ADDED . " (" . $noteId . ")",
            'moduleId'  => 'notes',
            'eventId'   => 'noteadd'
        ]);

        return $response->withJson(['noteId' => $noteId]);
    }

    public function update(Request $request, Response $response, array $args)
    {
        if (!ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $note = NoteModel::getById(['select' => ['user_id'], 'id' => $args['id']]);
        if (empty($note) || $note['user_id'] != $GLOBALS['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'Note out of perimeter']);
        }

        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['value'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body value is empty or not a string']);
        }

        if (!empty($body['entities'])) {
            if (!Validator::arrayType()->validate($body['entities'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Body entities is not an array']);
            }
            $entities = Entitymodel::get(['select' => ['count(1)'], 'where' => ['entity_id in (?)'], 'data' => [$body['entities']]]);
            if ($entities[0]['count'] != count($body['entities'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Body entities : one or more entities do not exist']);
            }
        }

        NoteModel::update([
            'set' => [
                'note_text' => $body['value']
            ],
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        NoteEntityModel::delete([
            'where' => ['note_id = ?'],
            'data'  => [$args['id']]
        ]);

        if (!empty($body['entities'])) {
            foreach ($body['entities'] as $entity) {
                NoteEntityModel::create(['item_id' => $entity, 'note_id' => $args['id']]);
            }
        }

        HistoryController::add([
            'tableName' => 'notes',
            'recordId'  => $args['id'],
            'eventType' => "UP",
            'info'      => _NOTE_UPDATED,
            'moduleId'  => 'notes',
            'eventId'   => 'noteModification'
        ]);

        return $response->withStatus(204);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        if (!ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $note = NoteModel::getById(['select' => ['user_id'], 'id' => $args['id']]);
        if (empty($note) || $note['user_id'] != $GLOBALS['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'Note out of perimeter']);
        }

        NoteModel::delete([
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);
        NoteEntityModel::delete([
            'where' => ['note_id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'tableName' => 'notes',
            'recordId'  => $args['id'],
            'eventType' => "DEL",
            'info'      => _NOTE_DELETED,
            'moduleId'  => 'notes',
            'eventId'   => 'noteSuppression'
        ]);

        return $response->withStatus(204);
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

            $user = UserModel::getById(['id' => $note['user_id'], 'select' => ['firstname', 'lastname']]);
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

    public static function hasRightById(array $args)
    {
        ValidatorModel::notEmpty($args, ['id', 'userId']);
        ValidatorModel::intVal($args, ['id', 'userId']);

        $note = NoteModel::getById(['select' => ['user_id'], 'id' => $args['id']]);
        if (empty($note)) {
            return false;
        }
        if ($note['user_id'] == $args['userId']) {
            return true;
        }

        $user = UserModel::getById(['select' => ['user_id'], 'id' => $args['userId']]);
        $userEntities = EntityModel::getByLogin(['login' => $user['user_id'], 'select' => ['entity_id']]);
        $userEntities = array_column($userEntities, 'entity_id');
        if (empty($userEntities)) {
            return false;
        }

        $noteEntities = NoteEntityModel::get(['select' => [1], 'where' => ['note_id = ?', 'item_id in (?)'], 'data' => [$args['id'], $userEntities]]);
        if (empty($noteEntities)) {
            return false;
        }

        return true;
    }
}
