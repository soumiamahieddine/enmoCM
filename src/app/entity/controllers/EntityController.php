<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Entity Controller
* @author dev@maarch.org
*/

namespace Entity\controllers;

use Core\Models\ServiceModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class EntityController
{
    public function get(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'manage_entities', 'userId' => $GLOBALS['userId'], 'location' => 'entities', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson(['baskets' => BasketModel::get()]);
    }
}
