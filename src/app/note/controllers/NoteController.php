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
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use History\controllers\HistoryController;
use Resource\controllers\ResController;

class NoteController
{
    public function getByResId(Request $request, Response $response, $aArgs)
    {
        $check = Validator::intVal()->validate($aArgs['resId']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $aNotes = NoteModel::getByResId(['select' => ['notes.id', 'firstname', 'lastname', 'entity_label', 'note_text', 'date_note'], 'resId' => $aArgs['resId'], 'orderBy' => ['date_note DESC']]);

        return $response->withJson($aNotes);
    }

    public function create(Request $request, Response $response, $aArgs)
    {
        $data = $request->getParams();

        //Check data
        $check = Validator::stringType()->notEmpty()->validate($data['note_text']);
        $check = $check && Validator::intVal()->notEmpty()->validate($aArgs['resId']); //correspond to res_id
        $check = $check && Validator::stringType()->notEmpty()->validate($GLOBALS['userId']);
        
        if (isset($data['entities_chosen'])) {
            $check = $check && Validator::arrayType()->validate($data['entities_chosen']);
        }
        
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        if (!ResController::hasRightByResId(['resId' => $aArgs['resId'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }
        
        $data['identifier'] = $aArgs['resId'];
        
        //Insert note in notes table and recover last insert ID
        $noteId = NoteModel::create($data);
    
        //Insert relation note with entities in note_entities_table
        if (!empty($noteId) && !empty($data['entities_chosen'])) {
            foreach($data['entities_chosen'] as $entity) {  
               NoteEntityModel::create( ['item_id' => $entity, 'note_id' => $noteId ]);
            }
        }

        //Insert in history
        HistoryController::add( [
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
}
