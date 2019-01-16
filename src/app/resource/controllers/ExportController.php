<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Export Controller
* @author dev@maarch.org
*/

namespace Resource\controllers;

use Attachment\models\AttachmentModel;
use Basket\models\BasketModel;
use Basket\models\GroupBasketModel;
use Basket\models\RedirectBasketModel;
use Entity\models\EntityModel;
use Group\models\GroupModel;
use Note\models\NoteModel;
use Priority\models\PriorityModel;
use Resource\models\ExportTemplateModel;
use Resource\models\ResModel;
use Resource\models\ResourceContactModel;
use Resource\models\ResourceListModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use SrcCore\models\ValidatorModel;
use Status\models\StatusModel;
use User\models\UserModel;

class ExportController
{
    public function getExport(Request $request, Response $response, array $aArgs)
    {
        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $errors = ResourceListController::listControl(['groupId' => $aArgs['groupId'], 'userId' => $aArgs['userId'], 'basketId' => $aArgs['basketId'], 'currentUserId' => $currentUser['id']]);
        if (!empty($errors['errors'])) {
            return $response->withStatus($errors['code'])->withJson(['errors' => $errors['errors']]);
        }

        $basket = BasketModel::getById(['id' => $aArgs['basketId'], 'select' => ['basket_clause', 'basket_res_order', 'basket_name']]);
        $user = UserModel::getById(['id' => $aArgs['userId'], 'select' => ['user_id']]);

        $data = $request->getQueryParams();

        if (!Validator::stringType()->notEmpty()->validate($data['delimiter'])) {
            return ['errors' => 'Delimiter is not set', 'code' => 400];
        } elseif (!Validator::arrayType()->notEmpty()->validate($data['data'])) {
            return ['errors' => 'Data is not an array or empty', 'code' => 400];
        }
        foreach ($data['data'] as $value) {
            if (!Validator::stringType()->notEmpty()->validate($value['value'])) {
                return ['errors' => 'Value is not set', 'code' => 400];
            } elseif (!Validator::boolType()->validate($value['isFunction'])) {
                return ['errors' => 'Data is not an array or empty', 'code' => 400];
            }
        }

        $template = ExportTemplateModel::getByUserId(['select' => [1], 'userId' => $currentUser['id']]);
        if (empty($template)) {
            //create
        } else {
            //update
        }
        $table = [];
        $leftJoin = [];
        $whereClause = PreparedClauseController::getPreparedClause(['clause' => $basket['basket_clause'], 'login' => $user['user_id']]);
        $where = [$whereClause];
        $queryData = [];

        if (!empty($data['delayed']) && $data['delayed'] == 'true') {
            $where[] = 'process_limit_date < CURRENT_TIMESTAMP';
        }
        if (!empty($data['search']) && mb_strlen($data['search']) >= 2) {
            $where[] = '(alt_identifier ilike ? OR translate(subject, \'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ\', \'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyrr\') ilike translate(?, \'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ\', \'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyrr\'))';
            $queryData[] = "%{$data['search']}%";
            $queryData[] = "%{$data['search']}%";
        }
        if (isset($data['priorities'])) {
            if (empty($data['priorities'])) {
                $where[] = 'priority is null';
            } else {
                $replace = preg_replace('/(^,)|(,$)/', '', $data['priorities']);
                $replace = preg_replace('/(,,)/', ',', $replace);
                if ($replace != $data['priorities']) {
                    $where[] = '(priority is null OR priority in (?))';
                } else {
                    $where[] = 'priority in (?)';
                }
                $queryData[] = explode(',', $replace);
            }
        }
        if (isset($data['categories'])) {
            if (empty($data['categories'])) {
                $where[] = 'category_id is null';
            } else {
                $replace = preg_replace('/(^,)|(,$)/', '', $data['categories']);
                $replace = preg_replace('/(,,)/', ',', $replace);
                if ($replace != $data['categories']) {
                    $where[] = '(category_id is null OR category_id in (?))';
                } else {
                    $where[] = 'category_id in (?)';
                }
                $queryData[] = explode(',', $replace);
            }
        }
        if (!empty($data['statuses'])) {
            $where[] = 'status in (?)';
            $queryData[] = explode(',', $data['statuses']);
        }
        if (isset($data['entities'])) {
            if (empty($data['entities'])) {
                $where[] = 'destination is null';
            } else {
                $replace = preg_replace('/(^,)|(,$)/', '', $data['entities']);
                $replace = preg_replace('/(,,)/', ',', $replace);
                if ($replace != $data['entities']) {
                    $where[] = '(destination is null OR destination in (?))';
                } else {
                    $where[] = 'destination in (?)';
                }
                $queryData[] = explode(',', $replace);
            }
        }
        if (!empty($data['entitiesChildren'])) {
            $entities = explode(',', $data['entitiesChildren']);
            $entitiesChildren = [];
            foreach ($entities as $entity) {
                $children = EntityModel::getEntityChildren(['entityId' => $entity]);
                $entitiesChildren = array_merge($entitiesChildren, $children);
            }
            if (!empty($entitiesChildren)) {
                $where[] = 'destination in (?)';
                $queryData[] = $entitiesChildren;
            }
        }

        if (!empty($data['order']) && strpos($data['order'], 'alt_identifier') !== false) {
            $data['order'] = 'order_alphanum(alt_identifier) ' . explode(' ', $data['order'])[1];
        }
        if (!empty($data['order']) && strpos($data['order'], 'priority') !== false) {
            $data['order'] = 'priorities.order ' . explode(' ', $data['order'])[1];
            $table = ['priorities'];
            $leftJoin = ['res_view_letterbox.priority = priorities.id'];
        }

        $select = ['res_id'];
        foreach ($data['data'] as $value) {
            if (!$value['isFunction']) {
                $select[] = $value['value'];
            } else {
                if ($value['value'] == 'getStatus') {
                    $select[] = 'status';
                } elseif ($value['value'] == 'getPriority') {
                    $select[] = 'priority';
                } elseif ($value['value'] == 'getParentFolder') {
                    $select[] = 'fold_parent_id';
                } elseif ($value['value'] == 'getCategory') {
                    $select[] = 'category_id';
                } elseif ($value['value'] == 'getInitiatorEntity') {
                    $select[] = 'initiator';
                } elseif ($value['value'] == 'getDestinationEntity') {
                    $select[] = 'destination';
                } elseif ($value['value'] == 'getContactType') {
                    //TODO
                } elseif ($value['value'] == 'getContactCivility') {
                    //TODO
                } elseif ($value['value'] == 'getContactFunction') {
                    //TODO
                }
            }
        }

        $rawResources = ResourceListModel::getOnView([
            'select'    => $select,
            'table'     => $table,
            'leftJoin'  => $leftJoin,
            'where'     => $where,
            'data'      => $queryData,
            'orderBy'   => empty($data['order']) ? [$basket['basket_res_order']] : [$data['order']]
        ]);
        $resIds = [];
        foreach ($rawResources as $resource) {
            $resIds[] = $resource['res_id'];
        }

        if ($value['value'] == 'getStatus') {

        } elseif ($value['value'] == 'getPriority') {

        } elseif ($value['value'] == 'getCopyEntities') {

        } elseif ($value['value'] == 'getDetailLink') {

        } elseif ($value['value'] == 'getParentFolder') {

        } elseif ($value['value'] == 'getCategory') {

        } elseif ($value['value'] == 'getInitiatorEntity') {

        } elseif ($value['value'] == 'getDestinationEntity') {

        } elseif ($value['value'] == 'getContactType') {

        } elseif ($value['value'] == 'getContactCivility') {

        } elseif ($value['value'] == 'getContactFunction') {

        } elseif ($value['value'] == 'getTags') {

        } elseif ($value['value'] == 'getSignatories') {

        } elseif ($value['value'] == 'getSignatureDates') {

        }


        $resources = [];
        if (!empty($resIds)) {

            $resources = ResourceListModel::get(['resIds' => $resIds]);

        }

        return $response->withJson(['resources' => $resources, 'basketLabel' => $basket['basket_name']]);
    }
}
