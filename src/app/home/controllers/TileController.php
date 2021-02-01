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

use Basket\models\BasketModel;
use Doctype\models\DoctypeModel;
use Group\models\GroupModel;
use History\controllers\HistoryController;
use Home\models\TileModel;
use Resource\models\ResModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use User\models\UserModel;

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

    public function getById(Request $request, Response $response, array $args)
    {
        $tile = TileModel::getById([
            'select'    => ['*'],
            'id'        => [$args['id']]
        ]);
        if (empty($tile) || $tile['user_id'] != $GLOBALS['id']) {
            return $response->withStatus(400)->withJson(['errors' => 'Tile out of perimeter']);
        }

        $tile['parameters'] = json_decode($tile['parameters'], true);

        $control = TileController::getDetails($tile);
        if (!empty($control['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
        }

        return $response->withJson(['tile' => $tile]);
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
        $control = TileController::controlParameters($body);
        if (!empty($control['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
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

    private static function controlParameters(array $args)
    {
        if ($args['type'] == 'basket') {
            if (!Validator::arrayType()->notEmpty()->validate($args['parameters'] ?? null)) {
                return ['errors' => 'Body parameters is empty or not an array'];
            } elseif (!Validator::intVal()->validate($args['parameters']['basketId'] ?? null)) {
                return ['errors' => 'Body[parameters] basketId is empty or not an integer'];
            } elseif (!Validator::intVal()->validate($args['parameters']['groupId'] ?? null)) {
                return ['errors' => 'Body[parameters] groupId is empty or not an integer'];
            }
            if (!BasketModel::hasGroup(['id' => $args['parameters']['basketId'], 'groupId' => $args['parameters']['groupId']])) {
                return ['errors' => 'Basket is not linked to this group'];
            } elseif (!UserModel::hasGroup(['id' => $GLOBALS['id'], 'groupId' => $args['parameters']['groupId']])) {
                return ['errors' => 'User is not linked to this group'];
            }
        }

        return true;
    }

    private static function getDetails(array &$tile)
    {
        if ($tile['type'] == 'basket') {
            if (!BasketModel::hasGroup(['id' => $tile['parameters']['basketId'], 'groupId' => $tile['parameters']['groupId']])) {
                return ['errors' => 'Basket is not linked to this group'];
            } elseif (!UserModel::hasGroup(['id' => $GLOBALS['id'], 'groupId' => $tile['parameters']['groupId']])) {
                return ['errors' => 'User is not linked to this group'];
            }

            $basket = BasketModel::getById(['select' => ['basket_clause', 'basket_name'], 'id' => $tile['parameters']['basketId']]);
            $group = GroupModel::getById(['select' => ['group_desc'], 'id' => $tile['parameters']['groupId']]);
            $tile['basketName'] = $basket['basket_name'];
            $tile['groupName'] = $group['group_desc'];
            if ($tile['view'] == 'resume') {
                $tile['resourceNumber'] = BasketModel::getResourceNumberByClause(['userId' => $GLOBALS['id'], 'clause' => $basket['basket_clause']]);
            } elseif ($tile['view'] == 'list') {
                //TODO WIP
            } elseif ($tile['view'] == 'chart') {
                $resources = ResModel::getOnView([
                    'select'    => ['COUNT(type_id)'],
                    'where'     => [PreparedClauseController::getPreparedClause(['userId' => $GLOBALS['id'], 'clause' => $basket['basket_clause']])],
                    'groupBy'   => ['type_id']
                ]);
                $tile['resources'] = [];
                foreach ($resources as $resource) {
                    $doctype = DoctypeModel::getById(['select' => ['description'], 'id' => $resource['type_id']]);
                    $tile['resources'][] = ['name' => $doctype['description'], 'value' => $resource['count']];
                }
            }
        }

        return true;
    }
}
