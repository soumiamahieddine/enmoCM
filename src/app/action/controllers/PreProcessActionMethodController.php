<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ActionController
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Action\controllers;

use Basket\models\BasketModel;
use Basket\models\GroupBasketRedirectModel;
use Entity\models\EntityModel;
use Group\models\GroupModel;
use Parameter\models\ParameterModel;
use Resource\controllers\ResourceListController;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use User\models\UserEntityModel;
use User\models\UserModel;

class PreProcessActionMethodController
{
    public static function getRedirectInformations(Request $request, Response $response, array $args)
    {
        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $primaryEntity = UserModel::getPrimaryEntityByUserId(['userId' => $GLOBALS['userId']]);

        $errors = ResourceListController::listControl(['groupId' => $args['groupId'], 'userId' => $args['userId'], 'basketId' => $args['basketId'], 'currentUserId' => $currentUser['id']]);
        if (!empty($errors['errors'])) {
            return $response->withStatus($errors['code'])->withJson(['errors' => $errors['errors']]);
        }

        $basket = BasketModel::getById(['id' => $args['basketId'], 'select' => ['basket_id']]);
        $group = GroupModel::getById(['id' => $args['groupId'], 'select' => ['group_id']]);
        $user = UserModel::getById(['id' => $args['userId'], 'select' => ['user_id']]);

        $keywords = [
            'ALL_ENTITIES'          => '@all_entities',
            'ENTITIES_JUST_BELOW'   => '@immediate_children[@my_primary_entity]',
            'ENTITIES_BELOW'        => '@subentities[@my_entities]',
            'ALL_ENTITIES_BELOW'    => '@subentities[@my_primary_entity]',
            'ENTITIES_JUST_UP'      => '@parent_entity[@my_primary_entity]',
            'MY_ENTITIES'           => '@my_entities',
            'MY_PRIMARY_ENTITY'     => '@my_primary_entity',
            'SAME_LEVEL_ENTITIES'   => '@sisters_entities[@my_primary_entity]'
        ];

        if ($args['mode'] == 'users') {
            $mode = 'USERS';
        } else {
            $mode = 'ENTITY';
        }

        $entityRedirects = GroupBasketRedirectModel::get([
            'select'    => ['entity_id', 'keyword'],
            'where'     => ['basket_id = ?', 'group_id = ?', 'action_id = ?', 'redirect_mode = ?'],
            'data'      => [$basket['basket_id'], $group['group_id'], $args['actionId'], $mode]
        ]);

        $allowedEntities = [];
        $clauseToProcess = '';
        foreach ($entityRedirects as $entityRedirect) {
            if (!empty($entityRedirect['entity_id'])) {
                $allowedEntities[] = $entityRedirect['entity_id'];
            } elseif (!empty($entityRedirect['keyword'])) {
                if (!empty($keywords[$entityRedirect['keyword']])) {
                    if (!empty($clauseToProcess)) {
                        $clauseToProcess .= ', ';
                    }
                    $clauseToProcess .= $keywords[$entityRedirect['keyword']];
                }
            }
        }

        if (!empty($clauseToProcess)) {
            $preparedClause = PreparedClauseController::getPreparedClause(['clause' => $clauseToProcess, 'login' => $user['user_id']]);
            $preparedEntities = EntityModel::get(['select' => ['entity_id'], 'where' => ['enabled = ?', "entity_id in {$preparedClause}"], 'data' => ['Y']]);
            foreach ($preparedEntities as $preparedEntity) {
                $allowedEntities[] = $preparedEntity['entity_id'];
            }
        }

        $allowedEntities = array_unique($allowedEntities);

        $redirectInformations = [];
        if ($args['mode'] == 'users') {
            $users = [];
            if (!empty($allowedEntities)) {
                $users = UserEntityModel::getWithUsers([
                    'select'    => ['DISTINCT users.id', 'users.user_id', 'firstname', 'lastname'],
                    'where'     => ['users_entities.entity_id in (?)', 'status not in (?)'],
                    'data'      => [$allowedEntities, ['DEL', 'ABS']]
                ]);

                foreach ($users as $key => $user) {
                    $users[$key]['labelToDisplay'] = "{$user['firstname']} {$user['lastname']}";
                }
            }
            $redirectInformations['users'] = $users;
        } else {
            $primaryEntity = UserModel::getPrimaryEntityByUserId(['userId' => $GLOBALS['userId']]);

            $allEntities = EntityModel::get(['select' => ['id', 'entity_id', 'entity_label', 'parent_entity_id'], 'where' => ['enabled = ?'], 'data' => ['Y'], 'orderBy' => ['parent_entity_id']]);
            foreach ($allEntities as $key => $value) {
                $allEntities[$key]['id'] = $value['entity_id'];
                $allEntities[$key]['serialId'] = $value['id'];
                if (empty($value['parent_entity_id'])) {
                    $allEntities[$key]['parent'] = '#';
                    $allEntities[$key]['icon'] = "fa fa-building";
                } else {
                    $allEntities[$key]['parent'] = $value['parent_entity_id'];
                    $allEntities[$key]['icon'] = "fa fa-sitemap";
                }
                if (in_array($value['entity_id'], $allowedEntities)) {
                    $allEntities[$key]['allowed'] = true;
                    $allEntities[$key]['state']['opened'] = true;
                    if ($primaryEntity['entity_id'] == $value['entity_id']) {
                        $allEntities[$key]['state']['selected'] = true;
                    }
                } else {
                    $allEntities[$key]['allowed'] = false;
                    $allEntities[$key]['state']['disabled'] = true;
                    $allEntities[$key]['state']['opened'] = false;
                }
                $allEntities[$key]['text'] = $value['entity_label'];
            }
            $redirectInformations['entities'] = $allEntities;
        }

        $parameter = ParameterModel::getById(['select' => ['param_value_int'], 'id' => 'keepDestForRedirection']);

        $redirectInformations['keepDestForRedirection'] = !empty($parameter['param_value_int']);

        return $response->withJson($redirectInformations);
    }
}
