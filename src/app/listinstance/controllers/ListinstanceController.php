<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Listinstance Controller
 * @author dev@maarch.org
 */

namespace Listinstance\controllers;

use Listinstance\models\ListinstanceModel;
use Slim\Http\Request;
use Slim\Http\Response;


class ListinstanceController
{
    public function getById(Request $request, Response $response, array $aArgs)
    {
        $listinstance = ListinstanceModel::getById(['id' => $aArgs['id']]);

        return $response->withJson($listinstance);
    }
}
