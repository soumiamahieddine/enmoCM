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
use Entity\models\EntityModel;
use Entity\models\ListInstanceModel;
use Resource\models\ExportTemplateModel;
use Resource\models\ResModel;
use Resource\models\ResourceListModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Stream;
use SrcCore\models\ValidatorModel;
use Tag\models\TagModel;
use User\models\UserModel;

require_once 'core/class/Url.php';

class ExportController
{
    public function getExportTemplate(Request $request, Response $response)
    {
        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $template     = ExportTemplateModel::getByUserId(['userId' => $currentUser['id']]);
        $delimiter    = '';
        $templateData = [];
        if (!empty($template)) {
            $delimiter    = $template['delimiter'];
            $templateData = (array)json_decode($template['data']);
        }

        return $response->withJson(['template' => $templateData, 'delimiter' => $delimiter]);
    }

    public function updateExport(Request $request, Response $response, array $aArgs)
    {
        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $errors = ResourceListController::listControl(['groupId' => $aArgs['groupId'], 'userId' => $aArgs['userId'], 'basketId' => $aArgs['basketId'], 'currentUserId' => $currentUser['id']]);
        if (!empty($errors['errors'])) {
            return $response->withStatus($errors['code'])->withJson(['errors' => $errors['errors']]);
        }

        $basket = BasketModel::getById(['id' => $aArgs['basketId'], 'select' => ['basket_clause', 'basket_res_order', 'basket_name']]);
        $user = UserModel::getById(['id' => $aArgs['userId'], 'select' => ['user_id']]);

        $queryParamsData = $request->getQueryParams();
        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['delimiter']) || !in_array($body['delimiter'], [',', ';', 'TAB'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Delimiter is not set or not set well']);
        } elseif (!Validator::arrayType()->notEmpty()->validate($body['data'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Data is not an array or empty']);
        }
        foreach ($body['data'] as $value) {
            if (!isset($value['value']) || !Validator::stringType()->notEmpty()->validate($value['label']) || !Validator::boolType()->validate($value['isFunction'])) {
                return $response->withStatus(400)->withJson(['errors' => 'One data is not set well']);
            }
        }

        $template = ExportTemplateModel::getByUserId(['select' => [1], 'userId' => $currentUser['id']]);
        if (empty($template)) {
            ExportTemplateModel::create([
                'userId'    => $currentUser['id'],
                'delimiter' => $body['delimiter'],
                'data'      => json_encode($body['data'])
            ]);
        } else {
            ExportTemplateModel::update([
                'set'   => [
                    'delimiter' => $body['delimiter'],
                    'data'      => json_encode($body['data'])
                ],
                'where' => ['user_id = ?'],
                'data'  => [$currentUser['id']]
            ]);
        }

        $allQueryData = ResourceListController::getResourcesListQueryData(['data' => $queryParamsData, 'basketClause' => $basket['basket_clause'], 'login' => $user['user_id']]);
        if (!empty($allQueryData['order'])) {
            $queryParamsData['order'] = $allQueryData['order'];
        }
        $rawResources = ResourceListModel::getOnView([
            'select'    => ['res_id'],
            'table'     => $allQueryData['table'],
            'leftJoin'  => $allQueryData['leftJoin'],
            'where'     => $allQueryData['where'],
            'data'      => $allQueryData['queryData'],
            'orderBy'   => empty($queryParamsData['order']) ? [$basket['basket_res_order']] : [$queryParamsData['order']]
        ]);

        $resIds = [];
        foreach ($rawResources as $resource) {
            $resIds[] = $resource['res_id'];
        }

        $select = ['res_id'];
        $tableFunction = [];
        $leftJoinFunction = [];
        $csvHead = [];
        foreach ($body['data'] as $value) {
            $csvHead[] = $value['label'];
            if (empty($value['value'])) {
                continue;
            }
            if ($value['isFunction']) {
                if ($value['value'] == 'getStatus') {
                    $select[] = 'status.label_status AS "status.label_status"';
                    $tableFunction[] = 'status';
                    $leftJoinFunction[] = 'res_view_letterbox.status = status.id';
                } elseif ($value['value'] == 'getPriority') {
                    $select[] = 'priorities.label AS "priorities.label"';
                    $tableFunction[] = 'priorities';
                    $leftJoinFunction[] = 'res_view_letterbox.priority = priorities.id';
                } elseif ($value['value'] == 'getParentFolder') {
                    $select[] = 'folders.folder_name AS "folders.folder_name"';
                    $tableFunction[] = 'folders';
                    $leftJoinFunction[] = 'res_view_letterbox.fold_parent_id = folders.folders_system_id';
                } elseif ($value['value'] == 'getCategory') {
                    $select[] = 'res_view_letterbox.category_id';
                } elseif ($value['value'] == 'getInitiatorEntity') {
                    $select[] = 'enone.short_label AS "enone.short_label"';
                    $tableFunction[] = 'entities enone';
                    $leftJoinFunction[] = 'res_view_letterbox.initiator = enone.entity_id';
                } elseif ($value['value'] == 'getDestinationEntity') {
                    $select[] = 'entwo.short_label AS "entwo.short_label"';
                    $tableFunction[] = 'entities entwo';
                    $leftJoinFunction[] = 'res_view_letterbox.destination = entwo.entity_id';
                } elseif ($value['value'] == 'getDestinationEntityType') {
                    $select[] = 'enthree.entity_type AS "enthree.entity_type"';
                    $tableFunction[] = 'entities enthree';
                    $leftJoinFunction[] = 'res_view_letterbox.destination = enthree.entity_id';
                } elseif ($value['value'] == 'getTypist') {
                    $select[] = 'res_view_letterbox.typist';
                } elseif ($value['value'] == 'getAssignee') {
                    $select[] = 'res_view_letterbox.dest_user';
                }
            } else {
                $select[] = "res_view_letterbox.{$value['value']}";
            }
        }

        $resources = [];
        if (!empty($resIds)) {
            $order = 'CASE res_view_letterbox.res_id ';
            foreach ($resIds as $key => $resId) {
                $order .= "WHEN {$resId} THEN {$key} ";
            }
            $order .= 'END';

            $resources = ResourceListModel::getOnView([
                'select'    => $select,
                'table'     => $tableFunction,
                'leftJoin'  => $leftJoinFunction,
                'where'     => ['res_view_letterbox.res_id in (?)'],
                'data'      => [$resIds],
                'orderBy'   => [$order]
            ]);
        }

        $file = fopen('php://memory', 'w');
        $delimiter = ($body['delimiter'] == 'TAB' ? "\t" : $body['delimiter']);

        fputcsv($file, $csvHead, $delimiter);

        foreach ($resources as $resource) {
            $csvContent = [];
            foreach ($body['data'] as $value) {
                if (empty($value['value'])) {
                    $csvContent[] = '';
                    continue;
                }
                if ($value['isFunction']) {
                    if ($value['value'] == 'getStatus') {
                        $csvContent[] = $resource['status.label_status'];
                    } elseif ($value['value'] == 'getPriority') {
                        $csvContent[] = $resource['priorities.label'];
                    } elseif ($value['value'] == 'getCopyEntities') {
                        $csvContent[] = ExportController::getCopyEntities(['resId' => $resource['res_id']]);
                    } elseif ($value['value'] == 'getDetailLink') {
                        $csvContent[] = str_replace('rest/', "apps/maarch_entreprise/index.php?page=details&dir=indexing_searching&id={$resource['res_id']}", \Url::coreurl());
                    } elseif ($value['value'] == 'getParentFolder') {
                        $csvContent[] = $resource['folders.folder_name'];
                    } elseif ($value['value'] == 'getCategory') {
                        $csvContent[] = ExportController::getCategory(['categoryId' => $resource['category_id']]);
                    } elseif ($value['value'] == 'getInitiatorEntity') {
                        $csvContent[] = $resource['enone.short_label'];
                    } elseif ($value['value'] == 'getDestinationEntity') {
                        $csvContent[] = $resource['entwo.short_label'];
                    } elseif ($value['value'] == 'getDestinationEntityType') {
                        $csvContent[] = $resource['enthree.entity_type'];
                    } elseif ($value['value'] == 'getSender') {
                        //TODO
                        $csvContent[] = '';
                    } elseif ($value['value'] == 'getRecipient') {
                        //TODO
                        $csvContent[] = '';
                    } elseif ($value['value'] == 'getTypist') {
                        $csvContent[] = UserModel::getLabelledUserById(['login' => $resource['typist']]);
                    } elseif ($value['value'] == 'getAssignee') {
                        $csvContent[] = UserModel::getLabelledUserById(['login' => $resource['dest_user']]);
                    } elseif ($value['value'] == 'getTags') {
                        $csvContent[] = ExportController::getTags(['resId' => $resource['res_id']]);
                    } elseif ($value['value'] == 'getSignatories') {
                        $csvContent[] = ExportController::getSignatories(['resId' => $resource['res_id']]);
                    } elseif ($value['value'] == 'getSignatureDates') {
                        $csvContent[] = ExportController::getSignatureDates(['resId' => $resource['res_id']]);
                    }
                } else {
                    $csvContent[] = $resource[$value['value']];
                }
            }
            fputcsv($file, $csvContent, $delimiter);
        }

        rewind($file);
        $response->write(stream_get_contents($file));
        $response = $response->withAddedHeader('Content-Disposition', 'attachment; filename=export_maarch.csv');

        return $response->withHeader('Content-Type', 'application/vnd.ms-excel');
    }

    private static function getCategory(array $args)
    {
        ValidatorModel::stringType($args, ['categoryId']);

        static $categories;
        if (empty($categories)) {
            $categories = ResModel::getCategories();
        }

        foreach ($categories as $category) {
            if ($category['id'] == $args['categoryId']) {
                return $category['label'];
            }
        }

        return '';
    }

    private static function getCopyEntities(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        $listInstances = ListInstanceModel::get([
            'select'    => ['item_id'],
            'where'     => ['res_id = ?', 'item_type = ?', 'item_mode = ?'],
            'data'      => [$args['resId'], 'entity_id', 'cc']
        ]);

        $copyEntities = '';
        foreach ($listInstances as $listInstance) {
            $entity = EntityModel::getByEntityId(['entityId' => $listInstance['item_id'], 'select' => ['short_label']]);
            if (!empty($copyEntities)) {
                $copyEntities .= ' ; ';
            }
            $copyEntities .= $entity['short_label'];
        }

        return $copyEntities;
    }

    private static function getTags(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        $tagsRes = TagModel::getTagRes([
            'select'    => ['tag_id'],
            'where'     => ['res_id = ?'],
            'data'      => [$args['resId']],
        ]);

        $tags = '';
        foreach ($tagsRes as $value) {
            $tag = TagModel::getById(['id' => $value['tag_id'], 'select' => ['tag_label']]);
            if (!empty($tags)) {
                $tags .= ' ; ';
            }
            $tags .= $tag['tag_label'];
        }

        return $tags;
    }

    private static function getSignatories(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        $listInstances = ListInstanceModel::get([
            'select'    => ['item_id'],
            'where'     => ['res_id = ?', 'item_type = ?', 'signatory = ?'],
            'data'      => [$args['resId'], 'user_id', true]
        ]);

        $signatories = '';
        foreach ($listInstances as $listInstance) {
            $user = UserModel::getByLogin(['login' => $listInstance['item_id'], 'select' => ['firstname', 'lastname']]);
            if (!empty($signatories)) {
                $signatories .= ' ; ';
            }
            $signatories .= "{$user['firstname']} {$user['lastname']}";
        }

        return $signatories;
    }

    private static function getSignatureDates(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        $attachments = AttachmentModel::getOnView([
            'select'    => ['creation_date'],
            'where'     => ['res_id = ?', 'attachment_type = ?', 'status = ?'],
            'data'      => [$args['resId'], 'signed_response', 'TRA']
        ]);

        $dates = '';
        foreach ($attachments as $attachment) {
            $date = new \DateTime($attachment['creation_date']);
            $date = $date->format('d-m-Y H:i');

            if (!empty($dates)) {
                $dates .= ' ; ';
            }
            $dates .= $date;
        }

        return $dates;
    }
}
