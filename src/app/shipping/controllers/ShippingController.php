<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ShippingController
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Shipping\controllers;

use Entity\models\EntityModel;
use Group\models\ServiceModel;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Shipping\models\ShippingModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\PasswordModel;

class ShippingController
{
    public function get(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_shippings', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson(['shippings' => ShippingModel::get(['id', 'label', 'description', 'options', 'fee', 'entities'])]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_shippings', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'id is not an integer']);
        }

        $shippingInfo = ShippingModel::getById(['id' => $aArgs['id']]);
        if (empty($shippingInfo)) {
            return $response->withStatus(400)->withJson(['errors' => 'Shipping does not exist']);
        }
        
        $shippingInfo['account'] = (array)json_decode($shippingInfo['account']);
        $shippingInfo['account']['password'] = '';
        $shippingInfo['options']  = (array)json_decode($shippingInfo['options']);
        $shippingInfo['fee']      = (array)json_decode($shippingInfo['fee']);
        $shippingInfo['entities'] = (array)json_decode($shippingInfo['entities']);

        $allEntities = EntityModel::get([
            'select'    => ['e1.id', 'e1.entity_id', 'e1.entity_label', 'e2.id as parent_id'],
            'table'     => ['entities e1', 'entities e2'],
            'left_join' => ['e1.parent_entity_id = e2.entity_id'],
            'where'     => ['e1.enabled = ?'],
            'data'      => ['Y']
        ]);

        foreach ($allEntities as $key => $value) {
            $allEntities[$key]['id'] = $value['id'];
            if (empty($value['parent_id'])) {
                $allEntities[$key]['parent'] = '#';
                $allEntities[$key]['icon']   = "fa fa-building";
            } else {
                $allEntities[$key]['parent'] = $value['parent_id'];
                $allEntities[$key]['icon']   = "fa fa-sitemap";
            }
            $allEntities[$key]['state']['opened'] = false;
            $allEntities[$key]['allowed']         = true;
            if (in_array($value['id'], $shippingInfo['entities'])) {
                $allEntities[$key]['state']['opened']   = true;
                $allEntities[$key]['state']['selected'] = true;
            }

            $allEntities[$key]['text'] = $value['entity_label'];
        }

        return $response->withJson(['shipping' => $shippingInfo, 'entities' => $allEntities]);
    }

    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_shippings', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $body = $request->getParsedBody();
        
        $errors = ShippingController::checkData($body, 'create');
        if (!empty($errors)) {
            return $response->withStatus(400)->withJson(['errors' => $errors]);
        }

        if (!empty($body['account']['password'])) {
            $body['account']['password'] = PasswordModel::encrypt(['password' => $body['account']['password']]);
        }

        $body['options']  = json_encode($body['options']);
        $body['fee']      = json_encode($body['fee']);
        $body['entities'] = json_encode($body['entities']);
        $body['account']  = json_encode($body['account']);
        $id = ShippingModel::create($body);

        HistoryController::add([
            'tableName' => 'shippings',
            'recordId'  => $id,
            'eventType' => 'ADD',
            'eventId'   => 'shippingadd',
            'info'      => _SHIPPING_ADDED . ' : ' . $body['label']
        ]);

        return $response->withJson(['shippingId' => $id]);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_shippings', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $body = $request->getParsedBody();
        $body['id'] = $aArgs['id'];

        $errors = ShippingController::checkData($body, 'update');
        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }

        if (!empty($body['account']['password'])) {
            $body['account']['password'] = PasswordModel::encrypt(['password' => $body['account']['password']]);
        } else {
            $shippingInfo = ShippingModel::getById(['id' => $aArgs['id'], 'select' => ['account']]);
            $body['account']['password'] = $shippingInfo['account']->password;
        }

        $body['options']  = json_encode($body['options']);
        $body['fee']      = json_encode($body['fee']);
        $body['entities'] = json_encode($body['entities']);
        $body['account']  = json_encode($body['account']);

        ShippingModel::update($body);

        HistoryController::add([
            'tableName' => 'shippings',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'eventId'   => 'shippingup',
            'info'      => _SHIPPING_UPDATED. ' : ' . $body['label']
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_shippings', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'id is not an integer']);
        }

        $shippingInfo = ShippingModel::getById(['id' => $aArgs['id'], 'select' => ['label']]);
        ShippingModel::delete(['id' => $aArgs['id']]);

        HistoryController::add([
            'tableName' => 'shippings',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'eventId'   => 'shippingdel',
            'info'      => _SHIPPING_DELETED. ' : ' . $shippingInfo['label']
        ]);

        $shippings = ShippingModel::get(['select' => ['id', 'label', 'description', 'options', 'fee', 'entities']]);
        return $response->withJson(['shippings' => $shippings]);
    }

    protected function checkData($aArgs, $mode)
    {
        $errors = [];

        if ($mode == 'update') {
            if (!Validator::intVal()->validate($aArgs['id'])) {
                $errors[] = 'Id is not a numeric';
            } else {
                $shippingInfo = ShippingModel::getById(['id' => $aArgs['id']]);
            }
            if (empty($shippingInfo)) {
                $errors[] = 'Shipping does not exist';
            }
        } else {
            if (!empty($aArgs['account'])) {
                if (!Validator::notEmpty()->validate($aArgs['account']['id']) || !Validator::notEmpty()->validate($aArgs['account']['password'])) {
                    $errors[] = 'account id or password is empty';
                }
            }
        }
           
        if (!Validator::notEmpty()->validate($aArgs['label']) ||
            !Validator::length(1, 64)->validate($aArgs['label'])) {
            $errors[] = 'label is empty or too long';
        }
        if (!Validator::notEmpty()->validate($aArgs['description']) ||
            !Validator::length(1, 255)->validate($aArgs['description'])) {
            $errors[] = 'description is empty or too long';
        }

        if (!empty($aArgs['entities'])) {
            if (!Validator::arrayType()->validate($aArgs['entities'])) {
                $errors[] = 'entities must be an array';
            }
            foreach ($aArgs['entities'] as $entity) {
                $info = EntityModel::getById(['id' => $entity, 'select' => ['id']]);
                if (empty($info)) {
                    $errors[] = $entity . ' does not exists';
                }
            }
        }

        return $errors;
    }

    public function initShipping(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_shippings', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $allEntities = EntityModel::get([
            'select'    => ['e1.id', 'e1.entity_id', 'e1.entity_label', 'e2.id as parent_id'],
            'table'     => ['entities e1', 'entities e2'],
            'left_join' => ['e1.parent_entity_id = e2.entity_id'],
            'where'     => ['e1.enabled = ?'],
            'data'      => ['Y']
        ]);

        print_r($allEntities);
        foreach ($allEntities as $key => $value) {
            $allEntities[$key]['id'] = (string)$value['id'];
            if (empty($value['parent_id'])) {
                $allEntities[$key]['parent'] = '#';
                $allEntities[$key]['icon']   = "fa fa-building";
            } else {
                $allEntities[$key]['parent'] = (string)$value['parent_id'];
                $allEntities[$key]['icon']   = "fa fa-sitemap";
            }

            $allEntities[$key]['allowed']           = true;
            $allEntities[$key]['state']['opened']   = true;

            $allEntities[$key]['text'] = $value['entity_label'];
        }

        return $response->withJson([
            'entities' => $allEntities,
        ]);
    }
}
