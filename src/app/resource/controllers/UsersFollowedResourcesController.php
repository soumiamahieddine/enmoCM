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
        UsersFollowedResourcesModel::create([
            'userId' => $GLOBALS['id'],
            'resId' => $args['resId']
        ]);

        return $response->withStatus(204);
    }

    public function unFollow(Request $request, Response $response, array $args)
    {
        UsersFollowedResourcesModel::delete([
            'userId' => $GLOBALS['id'],
            'resId' => $args['resId']
        ]);

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
}
