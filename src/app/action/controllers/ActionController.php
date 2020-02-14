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

use Basket\models\GroupBasketRedirectModel;
use CustomField\models\CustomFieldModel;
use Group\controllers\GroupController;
use Group\controllers\PrivilegeController;
use Group\models\GroupModel;
use History\controllers\HistoryController;
use Resource\models\ResModel;
use Respect\Validation\Validator;
use Action\models\ActionModel;
use Status\models\StatusModel;
use Slim\Http\Request;
use Slim\Http\Response;

class ActionController
{
    public function get(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_actions', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $actions = ActionModel::get();

        foreach ($actions as $key => $action) {
            $actions[$key]['requiredFields'] = json_decode($action['required_fields'], true);
            unset($actions[$key]['required_fields']);
        }

        return $response->withJson(['actions' => $actions]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $action['action'] = ActionModel::getById(['id' => $aArgs['id']]);
        if (empty($action['action'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Action does not exist']);
        }

        $categories = ActionModel::getCategoriesById(['id' => $aArgs['id']]);

        $action['action']['history'] = ($action['action']['history'] == 'Y');
        $action['action']['is_system'] = ($action['action']['is_system'] == 'Y');

        $action['action']['actionCategories'] = [];
        foreach ($categories as $category) {
            $action['action']['actionCategories'][] = $category['category_id'];
        }

        $action['categoriesList'] = ResModel::getCategories();
        if (empty($action['action']['actionCategories'])) {
            foreach ($action['categoriesList'] as $category) {
                $action['action']['actionCategories'][] = $category['id'];
            }
        }

        $action['statuses'] = StatusModel::get();
        array_unshift($action['statuses'], ['id' => '_NOSTATUS_', 'label_status' => _UNCHANGED]);
        $action['actionPages'] = ActionModel::getActionPages();
        $action['keywordsList'] = ActionModel::getKeywords();

        foreach ($action['actionPages'] as $actionPage) {
            if ($actionPage['id'] == $action['action']['action_page']) {
                $action['action']['actionPageId'] = $actionPage['id'];
            }
        }

        $action['action']['requiredFields'] = json_decode($action['action']['required_fields'], true);
        unset($action['action']['required_fields']);

        return $response->withJson($action);
    }

    public function create(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_actions', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $body = $request->getParsedBody();
        $body = $this->manageValue($body);
        
        $errors = $this->control($body, 'create');
        if (!empty($errors)) {
            return $response->withStatus(400)->withJson(['errors' => $errors]);
        }

        unset($body['action_page']);
        $actionPages = ActionModel::getActionPages();
        foreach ($actionPages as $actionPage) {
            if ($actionPage['id'] == $body['actionPageId']) {
                $body['action_page'] = $actionPage['name'];
                $body['component'] = $actionPage['component'];
            }
        }
        if (empty($body['action_page'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Data actionPageId does not exist']);
        }

        unset($body['actionPageId']);

        if (!empty($body['required_fields'])) {
            if (!Validator::arrayType()->validate($body['required_fields'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Data required_fields is not an array']);
            }
            $customFields = CustomFieldModel::get(['select' => ['id']]);
            $customFields = array_column($customFields, 'id');
            $requiredFields = [];
            foreach ($body['required_fields'] as $key => $requiredField) {
                if (strpos($requiredField, 'indexingCustomField_') !== false) {
                    $idCustom = explode("_", $requiredField);
                    $idCustom = $idCustom[1];
                    if (!in_array($idCustom, $customFields)) {
                        return $response->withStatus(400)->withJson(['errors' => 'Data custom field does not exist']);
                    }
                    $requiredFields[] = $requiredField;
                }
            }

            $body['required_fields'] = json_encode($requiredFields);
        }

        $id = ActionModel::create($body);
        if (!empty($body['actionCategories'])) {
            ActionModel::createCategories(['id' => $id, 'categories' => $body['actionCategories']]);
        }

        HistoryController::add([
            'tableName' => 'actions',
            'recordId'  => $id,
            'eventType' => 'ADD',
            'eventId'   => 'actionadd',
            'info'      => _ACTION_ADDED . ' : ' . $body['label_action']
        ]);

        return $response->withJson(['actionId' => $id]);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_actions', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $body = $request->getParsedBody();
        $body['id'] = $aArgs['id'];

        $body    = $this->manageValue($body);
        $errors = $this->control($body, 'update');
        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }

        unset($body['action_page']);
        $actionPages = ActionModel::getActionPages();
        foreach ($actionPages as $actionPage) {
            if ($actionPage['id'] == $body['actionPageId']) {
                $body['action_page'] = $actionPage['id'];
                $body['component'] = $actionPage['component'];
            }
        }
        if (empty($body['action_page'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Data actionPageId does not exist']);
        }

        $requiredFields = [];
        if (!empty($body['required_fields'])) {
            if (!Validator::arrayType()->validate($body['required_fields'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Data required_fields is not an array']);
            }
            $customFields = CustomFieldModel::get(['select' => ['id']]);
            $customFields = array_column($customFields, 'id');
            foreach ($body['required_fields'] as $key => $requiredField) {
                if (strpos($requiredField, 'indexingCustomField_') !== false) {
                    $idCustom = explode("_", $requiredField);
                    $idCustom = $idCustom[1];
                    if (!in_array($idCustom, $customFields)) {
                        return $response->withStatus(400)->withJson(['errors' => 'Data custom field does not exist']);
                    }
                    $requiredFields[] = $requiredField;
                }
            }
        }
        $body['required_fields'] = json_encode($requiredFields);

        ActionModel::update($body);
        ActionModel::deleteCategories(['id' => $aArgs['id']]);
        if (!empty($body['actionCategories'])) {
            ActionModel::createCategories(['id' => $aArgs['id'], 'categories' => $body['actionCategories']]);
        }

        if (!in_array($body['component'], GroupController::INDEXING_ACTIONS)) {
            GroupModel::update([
                'postSet'   => ['indexation_parameters' => "jsonb_set(indexation_parameters, '{actions}', (indexation_parameters->'actions') - '{$aArgs['id']}')"],
                'where'     => ['1=1']
            ]);
        }

        HistoryController::add([
            'tableName' => 'actions',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'eventId'   => 'actionup',
            'info'      => _ACTION_UPDATED. ' : ' . $body['label_action']
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_actions', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }
        $action = ActionModel::getById(['id' => $args['id'], 'select' => ['label_action']]);
        if (empty($action)) {
            return $response->withStatus(400)->withJson(['errors' => 'Action does not exist']);
        }

        ActionModel::delete(['id' => $args['id']]);
        ActionModel::deleteCategories(['id' => $args['id']]);
        GroupBasketRedirectModel::delete(['where' => ['action_id = ?'], 'data' => [$args['id']]]);

        GroupModel::update([
            'postSet'   => ['indexation_parameters' => "jsonb_set(indexation_parameters, '{actions}', (indexation_parameters->'actions') - '{$args['id']}')"],
            'where'     => ['1=1']
        ]);

        HistoryController::add([
            'tableName' => 'actions',
            'recordId'  => $args['id'],
            'eventType' => 'DEL',
            'eventId'   => 'actiondel',
            'info'      => _ACTION_DELETED. ' : ' . $action['label_action']
        ]);

        return $response->withJson(['actions' => ActionModel::get()]);
    }

    protected function control($aArgs, $mode)
    {
        $errors = [];
      
        $objs = StatusModel::get();
        $status = array_column($objs, 'id');
        array_unshift($status, '_NOSTATUS_');

        if (!(in_array($aArgs['id_status'], $status))) {
            $errors[]= 'Invalid Status';
        }

        if ($mode == 'update') {
            if (!Validator::intVal()->validate($aArgs['id'])) {
                $errors[] = 'Id is not a numeric';
            } else {
                $obj = ActionModel::getById(['id' => $aArgs['id'], 'select' => [1]]);
            }
           
            if (empty($obj)) {
                $errors[] = 'Id ' .$aArgs['id']. ' does not exist';
            }
        }
           
        if (!Validator::notEmpty()->validate($aArgs['label_action']) ||
            !Validator::length(1, 255)->validate($aArgs['label_action'])) {
            $errors[] = 'Invalid label action';
        }
        if (!Validator::stringType()->notEmpty()->validate($aArgs['actionPageId'])) {
            $errors[] = 'Invalid page action';
        }

        if (!Validator::notEmpty()->validate($aArgs['id_status'])) {
            $errors[] = 'id_status is empty';
        }

        if (!Validator::notEmpty()->validate($aArgs['history']) || ($aArgs['history'] != 'Y' && $aArgs['history'] != 'N')) {
            $errors[]= 'Invalid history value';
        }

        return $errors;
    }

    public function initAction(Request $request, Response $response)
    {
        $obj['action']['history']          = true;
        $obj['action']['keyword']          = '';
        $obj['action']['actionPageId']     = 'confirm_status';
        $obj['action']['id_status']        = '_NOSTATUS_';
        $obj['categoriesList']             = ResModel::getCategories();

        $obj['action']['actionCategories'] = array_column($obj['categoriesList'], 'id');

        $obj['statuses'] = StatusModel::get();
        array_unshift($obj['statuses'], ['id'=>'_NOSTATUS_','label_status'=> _UNCHANGED]);
        $obj['actionPages'] = ActionModel::getActionPages();
        $obj['keywordsList'] = ActionModel::getKeywords();
        
        return $response->withJson($obj);
    }

    protected function manageValue($request)
    {
        foreach ($request as $key => $value) {
            if (in_array($key, ['history'])) {
                if (empty($value)) {
                    $request[$key] = 'N';
                } else {
                    $request[$key] = 'Y';
                }
            }
        }
        return $request;
    }
}
