<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Users Followed Resources Controller
 * @author dev@maarch.org
 */

namespace Resource\controllers;


use Resource\models\UsersFollowedResourcesModel;
use Slim\Http\Request;
use Slim\Http\Response;

class UsersFollowedResourcesController
{
    public function follow(Request $request, Response $response, array $args)
    {
        if (!ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])){
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        UsersFollowedResourcesController::followResource($args);

        return $response->withStatus(204);
    }

    public function unFollow(Request $request, Response $response, array $args)
    {
        UsersFollowedResourcesController::unFollowResource($args);

        return $response->withStatus(204);
    }

    public function getFollowedResources(Request $request, Response $response, array $args)
    {
        $followed = UsersFollowedResourcesModel::get([
           'where' => ['user_id = ?'],
           'data' => [$GLOBALS['id']]
        ]);

        return $response->withJson($followed);
    }

    public static function followResource(array $args)
    {
        $following = UsersFollowedResourcesModel::get([
            'where' => ['user_id = ?', 'res_id = ?'],
            'data' => [$GLOBALS['id'], $args['resId']]
        ]);

        if (!empty($following)) {
            return true;
        }

        UsersFollowedResourcesModel::create([
            'userId' => $GLOBALS['id'],
            'resId' => $args['resId']
        ]);

        return true;
    }

    public static function unFollowResource(array $args)
    {
        $following = UsersFollowedResourcesModel::get([
            'where' => ['user_id = ?', 'res_id = ?'],
            'data' => [$GLOBALS['id'], $args['resId']]
        ]);

        if (empty($following)) {
            return true;
        }

        UsersFollowedResourcesModel::delete([
            'userId' => $GLOBALS['id'],
            'resId' => $args['resId']
        ]);

        return true;
    }
}
