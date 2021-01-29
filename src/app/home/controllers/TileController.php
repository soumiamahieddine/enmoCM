<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Tile Controller
 * @author dev@maarch.org
 */

namespace Home\controllers;

use History\controllers\HistoryController;
use Home\models\TileModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class TileController
{
    const TYPES = ['myLastResources', 'basket', 'searchTemplate', 'followedMail', 'folder', 'externalSignatoryBook', 'shortcut'];
    const VIEWS = ['list', 'resume', 'chart'];

    public function get(Request $request, Response $response)
    {
        $tiles = TileModel::get([
            'select'    => ['*'],
            'where'     => ['user_id = ?'],
            'data'      => [$GLOBALS['id']]
        ]);

        foreach ($tiles as $key => $tile) {
            $tiles[$key]['parameters'] = json_decode($tile['parameters'], true);
        }

        return $response->withJson(['tiles' => $tiles]);
    }

    public function create(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is empty']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['type'] ?? null) || !in_array($body['type'], TileController::TYPES)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body type is empty, not a string or not valid']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['view'] ?? null) || !in_array($body['view'], TileController::VIEWS)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body view is empty, not a string or not valid']);
        } elseif (!Validator::intVal()->validate($body['position'] ?? null)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body position is not set or not an integer']);
        }

        $tiles = TileModel::get([
            'select'    => [1],
            'where'     => ['user_id = ?'],
            'data'      => [$GLOBALS['id']]
        ]);
        if (count($tiles) >= 6) {
            return $response->withStatus(400)->withJson(['errors' => 'Too many tiles (limited to 6)']);
        }

        $id = TileModel::create([
            'user_id'       => $GLOBALS['id'],
            'type'          => $body['type'],
            'view'          => $body['view'],
            'position'      => $body['position'],
            'parameters'    => empty($body['parameters']) ? '{}' : json_encode($body['parameters'])
        ]);

        HistoryController::add([
            'tableName'    => 'tiles',
            'recordId'     => $id,
            'eventType'    => 'ADD',
            'eventId'      => 'tileCreation',
            'info'         => 'tile creation'
        ]);

        return $response->withJson(['id' => $id]);
    }

    public function update(Request $request, Response $response, array $args)
    {
        $tile = TileModel::getById(['select' => ['user_id'], 'id' => $args['id']]);
        if (empty($tile) || $tile['user_id'] != $GLOBALS['id']) {
            return $response->withStatus(400)->withJson(['errors' => 'Tile out of perimeter']);
        }

        $body = $request->getParsedBody();

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is empty']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['view'] ?? null) || !in_array($body['view'], TileController::VIEWS)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body view is empty, not a string or not valid']);
        }

        TileModel::update([
            'set'   => [
                'view'          => $body['view'],
                'parameters'    => empty($body['parameters']) ? '{}' : json_encode($body['parameters'])
            ],
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'tableName'    => 'tiles',
            'recordId'     => $args['id'],
            'eventType'    => 'UP',
            'eventId'      => 'tileModification',
            'info'         => 'tile modification'
        ]);

        return $response->withStatus(204);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        $tile = TileModel::getById(['select' => ['user_id'], 'id' => $args['id']]);
        if (empty($tile) || $tile['user_id'] != $GLOBALS['id']) {
            return $response->withStatus(400)->withJson(['errors' => 'Tile out of perimeter']);
        }

        TileModel::delete([
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'tableName'    => 'tiles',
            'recordId'     => $args['id'],
            'eventType'    => 'DEL',
            'eventId'      => 'tileSuppression',
            'info'         => 'tile suppression'
        ]);

        return $response->withStatus(204);
    }

    public function updatePositions(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is empty']);
        } elseif (!Validator::arrayType()->notEmpty()->validate($body['tiles'] ?? null)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body tiles is empty not not an array']);
        }

        $userTiles = TileModel::get(['select' => ['id'], 'where' => ['user_id = ?'], 'data' => [$GLOBALS['id']]]);
        if (count($userTiles) != count($body['tiles'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body tiles do not match user tiles']);
        }
        $allTiles = array_column($userTiles, 'id');
        foreach ($body['tiles'] as $tile) {
            if (!in_array($tile['id'], $allTiles)) {
                return $response->withStatus(400)->withJson(['errors' => 'Tiles out of perimeter']);
            }
        }

        foreach ($body['tiles'] as $key => $tile) {
            TileModel::update([
                'set'   => [
                    'position' => $tile['position'],
                ],
                'where' => ['id = ?'],
                'data'  => [$tile['id']]
            ]);
        }

        return $response->withStatus(204);
    }
}
