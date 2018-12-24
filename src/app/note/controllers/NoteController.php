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
use Note\models\NoteEntitieModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use History\controllers\HistoryController;

class NoteController
{
    public function getByResId(Request $request, Response $response)
    {
        $check = Validator::intVal()->validate($aArgs['resId']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $aNotes = NoteModel::getByResId(['select' => ['notes.id', 'firstname', 'lastname', 'entity_label', 'note_text', 'date_note'], 'resId' => $aArgs['resId'], 'orderBy' => ['date_note DESC']]);

        return $response->withJson($aNotes);
    }

    public function create(Request $request, Response $response)
    {
        $data = $request->getParams();
        
        //Insert note in notes table and recover last insert ID
        $check_note = Validator::stringType()->notEmpty()->validate($data['note_text']);
        $check_note = $check_note && Validator::intVal()->notEmpty()->validate($data['identifier']); //correspond to res_id
        $check_note = $check_note && Validator::stringType()->notEmpty()->validate($data['user_id']);
        
        if (!$check_note) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $data['note_id'] = NoteModel::create($data);
                
        //Insert relation note with entities in note_entities_table
        $check_entities = Validator::intVal()->notEmpty()->validate($data['note_id']);
        $check_entities = $check_entities && Validator::arrayType()->notEmpty()->validate($data['entities_chosen']);
        
        if (!$check_entities) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        } else {
            for ($i=0; $i<count($data['entities_chosen']); $i++) 
            {  
                $data['note_entities_id'][$i] = NoteEntitieModel::create( ['item_id' => $data['entities_chosen'][$i], 'note_id' => $data['note_id'] ]);
            }
        }      

        //Insert in history
        HistoryController::add( [
            'tableName' => "notes",
            'recordId'  => $data['note_id'],
            'eventType' => "ADD",
            'userId'    => $data['user_id'],
            'info'      => "Annotation ajoutée (" . $data['note_id'] . ")",
            'moduleId'  => 'notes',
            'eventId'   => 'nateadd']
        );

        return true; 
    }
}
