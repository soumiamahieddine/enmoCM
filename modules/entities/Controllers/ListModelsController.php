<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Resource Controller
* @author dev@maarch.org
* @ingroup core
*/

namespace Entities\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Entities\Models\ListModelsModel;
use Core\Models\UserModel;


class ListModelsController
{

    public function getListModelsDiffListDestByUserId(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $return['listModels'] = ListModelsModel::getDiffListByUserId(['select' => ['object_id','title'], 'itemId' => $aArgs['itemId'],'objectType' => $aArgs['objectType'],'itemMode' => $aArgs['itemMode']]);

        return $response->withJson($return);
    }

    public function updateListModelsDiffListDestByUserId(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $data = $request->getParams();

        foreach ($data['redirectListModels'] as $listModel) {
            $user = UserModel::getByUserId(['userId' => $listModel['redirectUserId']]);
            $userId = $listModel['redirectUserId'];
            if (empty($user)) {
                return $response->withStatus(404)->withJson(['errors' => "User « $userId » not found"]);
            }

            $r = ListModelsModel::update(['set' =>['item_id' => $listModel['redirectUserId']] ,'where' => ['item_id = ?', 'object_id = ?', 'object_type = ?', 'item_mode = ?'], 'data' => [$data['user_id'], $listModel['object_id'], 'entity_id', 'dest']]);
            if (!$r) {
                return $response->withStatus(500)->withJson(['errors' => 'ListModels Update Error']);
            }
        }
        return $response->withJson(['success' => 'ListModels Updated']);
    }
}
