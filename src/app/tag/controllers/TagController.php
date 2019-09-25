<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*/

/**
 * @brief Tag Controller
 * @author dev@maarch.org
 */

namespace Tag\controllers;

use Group\models\ServiceModel;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use Tag\models\TagModel;

class TagController
{
    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_tag', 'userId' => $GLOBALS['userId'], 'location' => 'tags', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string']);
        }

        $id = TagModel::create([
            'label' => $body['label']
        ]);

        HistoryController::add([
            'tableName' => 'tags',
            'recordId'  => $id,
            'eventType' => 'ADD',
            'info'      =>  _TAG_ADDED . " : {$body['label']}",
            'eventId'   => 'tagCreation',
        ]);

        return $response->withJson(['id' => $id]);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        if (!ServiceModel::hasService(['id' => 'admin_tag', 'userId' => $GLOBALS['userId'], 'location' => 'tags', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $tag = TagModel::getById(['select' => ['label'], 'id' => $args['id']]);
        if (empty($tag)) {
            return $response->withStatus(400)->withJson(['errors' => 'Tag does not exist']);
        }

        TagModel::delete([
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'tableName' => 'tags',
            'recordId'  => $args['id'],
            'eventType' => 'DEL',
            'info'      =>  _TAG_DELETED . " : {$tag['label']}",
            'eventId'   => 'tagSuppression',
        ]);

        return $response->withStatus(201);
    }
}
