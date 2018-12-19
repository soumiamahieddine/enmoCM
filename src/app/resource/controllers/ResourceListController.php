<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Resource List Controller
* @author dev@maarch.org
*/

namespace Resource\controllers;

use Attachment\models\AttachmentModel;
use Basket\models\BasketModel;
use Basket\models\GroupBasketModel;
use Basket\models\RedirectBasketModel;
use Group\models\GroupModel;
use Note\models\NoteModel;
use Resource\models\ResModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use User\models\UserModel;

class ResourceListController
{
    public function get(Request $request, Response $response, array $aArgs)
    {
        $data = $request->getQueryParams();

        if (empty($data['offset']) || !is_numeric($data['offset'])) {
            $data['offset'] = 0;
        }
        if (empty($data['limit']) || !is_numeric($data['limit'])) {
            $data['limit'] = 0;
        }

        $group = GroupModel::getById(['id' => $aArgs['groupSerialId'], 'select' => ['group_id']]);
        $basket = BasketModel::getById(['id' => $aArgs['basketId'], 'select' => ['basket_clause', 'basket_res_order']]);
        if (empty($group) || empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group or basket does not exist']);
        }

        $user = UserModel::getById(['id' => $aArgs['userId'], 'select' => ['user_id']]);
        if ($user['user_id'] == $GLOBALS['userId']) {
            $redirectedBasket = RedirectBasketModel::get([
                'select'    => [1],
                'where'     => ['owner_user_id = ?', 'basket_id = ?', 'group_id = ?'],
                'data'      => [$aArgs['userId'], $aArgs['basketId'], $aArgs['groupSerialId']]
            ]);
            if (!empty($redirectedBasket[0])) {
                return $response->withStatus(403)->withJson(['errors' => 'Basket out of perimeter (redirected)']);
            }
        } else {
            $currentUser = UserModel::getByUserId(['userId' => $GLOBALS['userId'], 'select' => ['id']]);
            $redirectedBasket = RedirectBasketModel::get([
                'select'    => ['actual_user_id'],
                'where'     => ['owner_user_id = ?', 'basket_id = ?', 'group_id = ?'],
                'data'      => [$aArgs['userId'], $aArgs['basketId'], $aArgs['groupSerialId']]
            ]);
            if (empty($redirectedBasket[0]) || $redirectedBasket[0]['actual_user_id'] != $currentUser['id']) {
                return $response->withStatus(403)->withJson(['errors' => 'Basket out of perimeter']);
            }
        }

        $groups = UserModel::getGroupsByUserId(['userId' => $user['user_id']]);
        $groupFound = false;
        foreach ($groups as $value) {
            if ($value['id'] == $aArgs['groupSerialId']) {
                $groupFound = true;
            }
        }
        if (!$groupFound) {
            return $response->withStatus(400)->withJson(['errors' => 'Group is not linked to this user']);
        }

        $isBasketLinked = GroupBasketModel::get(['select' => [1], 'where' => ['basket_id = ?', 'group_id = ?'], 'data' => [$aArgs['basketId'], $group['group_id']]]);
        if (empty($isBasketLinked)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group is not linked to this basket']);
        }
        //END OF CONTROL

        $whereClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'login' => $user['user_id']]);

        $rawResources = ResModel::getOnView([
            'select'    => ['count(1) OVER()', 'res_id'],
            'where'     => [$whereClause],
            'order_by'  => [$basket['basket_res_order']],
            'offset'    => (int)$data['offset'],
            'limit'     => (int)$data['limit']
        ]);
        $count = empty($rawResources[0]['count']) ? 0 : $rawResources[0]['count'];
        $resIds = [];
        foreach ($rawResources as $resource) {
            $resIds[] = $resource['res_id'];
        }

        $attachments = AttachmentModel::getOnView([
            'select'    => ['COUNT(res_id)', 'res_id_master'],
            'where'     => ['res_id_master in (?)', 'status not in (?)'],
            'data'      => [$resIds, ['DEL', 'OBS']],
            'groupBy'   => ['res_id_master']
        ]);

        $resources = ResModel::getForList(['resIds' => $resIds]);

        foreach ($resources as $key => $resource) {
            $resources[$key]['countAttachments'] = 0;
            foreach ($attachments as $attachment) {
                if ($attachment['res_id_master'] == $resource['res_id']) {
                    $resources[$key]['countAttachments'] = $attachment['count'];
                }
            }
            $resources[$key]['countNotes'] = NoteModel::countByResId(['resId' => $resource['res_id'], 'login' => $GLOBALS['userId']]);
        }

        return $response->withJson(['resources' => $resources, 'count' => $count]);
    }
}
