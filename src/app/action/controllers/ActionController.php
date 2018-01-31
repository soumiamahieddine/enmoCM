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

use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Action\models\ActionModel;
use Status\models\StatusModel;
use Core\Models\ServiceModel;
use Core\Models\CoreConfigModel;
use Slim\Http\Request;
use Slim\Http\Response;

class ActionController
{
    public function get(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_actions', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $obj ['actions']= ActionModel::get();
       
        return $response->withJson($obj);
    }

    public function getById(Request $request, Response $response, $aArgs)
    {
        if (isset($aArgs['id'])) {
            $id = $aArgs['id'];
            $obj['action'] = ActionModel::getById(['id' => $id]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'id is empty']);
        }

        if(!empty($obj['action'])){
            if ($obj['action']['is_folder_action'] == 'Y') {
                $obj['action']['is_folder_action'] = true;
            } else {
                $obj['action']['is_folder_action'] = false;
            }

            if ($obj['action']['history'] == 'Y') {
                $obj['action']['history'] = true;
            } else {
                $obj['action']['history'] = false;
            }

            if ($obj['action']['is_system'] == 'Y') {
                $obj['action']['is_system'] = true;
            } else {
                $obj['action']['is_system'] = false;
            }

            if ($obj['action']['create_id'] == 'Y') {
                $obj['action']['create_id'] = true;
            } else {
                $obj['action']['create_id'] = false;
            }

            //array of id categoriesList
            foreach ($obj['action']['actionCategories'] as $key => $category) {
                $arrActionCategories[] = $category['category_id'];
            }
            $obj['action']['actionCategories'] = $arrActionCategories;

            $obj['categoriesList'] = CoreConfigModel:: getLettersBoxCategories();

            //array of id categoriesList
            foreach ($obj['categoriesList'] as $key => $category) {
                $arrCategoriesList[] = $category['id'];
            }

            //array of id actionCategories
            if (empty($obj['action']['actionCategories'])) {
                $obj['action']['actionCategories'] = $arrCategoriesList;
            }
        
            $obj['statuses'] = StatusModel::get();
            array_unshift($obj['statuses'], ['id'=>'_NOSTATUS_','label_status'=> _UNCHANGED]);
            $obj['action_pagesList'] = ActionModel::getAction_pages();
            array_unshift($obj['action_pagesList']['actionsPageList'], ['id'=>'','label'=> _NO_PAGE, 'name'=>'', 'origin'=>'']);
            $obj['keywordsList'] = ActionModel::getKeywords();
        }
  
        return $response->withJson($obj);
    }

    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_actions', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $errors = [];
        $aArgs  = $request->getParams();
        $aArgs  = $this->manageValue($aArgs);
        
        $errors = $this->control($aArgs, 'create');

        if (!empty($errors)) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => $errors]);
        }
    
        $return = ActionModel::create($aArgs);

        if ($return) {
            $obj = max(ActionModel::get());
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'Problem during action creation']);
        }

        HistoryController::add([
            'tableName' => 'actions',
            'recordId'  => $obj['id'],
            'eventType' => 'ADD',
            'eventId'   => 'actionadd',
            'info'      => _ACTION. ' "' . $obj['label_action'] .'" ' ._ADDED
        ]);

        return $response->withJson(
            [
            'action'  => $obj
            ]
        );
    }

    public function update(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_actions', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $errors = [];

        $obj       = $request->getParams();
        $obj['id'] = $aArgs['id'];

        $obj    = $this->manageValue($obj);
        $errors = $this->control($obj, 'update');
      
        if (!empty($errors)) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => $errors]);
        }

        $return = ActionModel::update($obj);

        if ($return) {
            $id  = $aArgs['id'];
            $obj = ActionModel::getById(['id' => $id]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'Problem during action update']);
        }

        HistoryController::add([
            'tableName' => 'actions',
            'recordId'  => $obj['id'],
            'eventType' => 'UP',
            'eventId'   => 'actionup',
            'info'      => _ACTION. ' "' . $obj['label_action'] .'" ' ._UPDATED
        ]);

        return $response->withJson(
            [
            'action'  => $obj
            ]
        );
    }

    public function delete(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_actions', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->validate($aArgs['id'])) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'Id is not a numeric']);
        }

        if (isset($aArgs['id'])) {
            $id  = $aArgs['id'];
            $obj = ActionModel::getById(['id' => $id]);
            ActionModel::delete(['id' => $id]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'id is empty']);
        }

        HistoryController::add([
            'tableName' => 'actions',
            'recordId'  => $id,
            'eventType' => 'DEL',
            'eventId'   => 'actiondel',
            'info'      => _ACTION. ' "' . $obj['label_action'] .'" ' ._DELETED
        ]);

        return $response->withJson(
            [
            'action' => ActionModel::get()
            ]
        );
    }

    protected function control($aArgs, $mode)
    {
        $errors = [];
      
        $objs = StatusModel::get();

        foreach ($objs as $obj) {
            $status[] = $obj['id'];
        }
        array_unshift($status, '_NOSTATUS_');

        if (!(in_array($aArgs['id_status'], $status))) {
            $errors[]= 'Invalid Status';
        }

        if ($mode == 'update') {
            if (!Validator::intVal()->validate($aArgs['id'])) {
                $errors[] = 'Id is not a numeric';
            } else {
                $obj = ActionModel::getById(['id' => $aArgs['id']]);
            }
           
            if (empty($obj)) {
                $errors[] = 'Id ' .$aArgs['id']. ' does not exists';
            }
        }
           
        if (!Validator::notEmpty()->validate($aArgs['label_action']) ||
            !Validator::length(1, 255)->validate($aArgs['label_action'])) {
            $errors[] = 'Invalid label action';
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
        //default data
        $obj['action']['history']          = true;
        $obj['action']['keyword']          = '';
        $obj['action']['is_folder_action'] = false;
        $obj['action']['action_page']      = '';
        $obj['action']['id_status']        = '_NOSTATUS_';
        $obj['categoriesList']             = CoreConfigModel::getLettersBoxCategories();

        foreach ($obj['categoriesList'] as $key => $value) {
            $obj['categoriesList'][$key]['selected'] = true;
        }

        $obj['statuses'] = StatusModel::get();
        array_unshift($obj['statuses'], ['id'=>'_NOSTATUS_','label_status'=> _UNCHANGED]);
        $obj['action_pagesList'] = ActionModel::getAction_pages();
        array_unshift($obj['action_pagesList']['actionsPageList'], ['id'=>'','label'=> _NO_PAGE, 'name'=>'', 'origin'=>'']);
        $obj['keywordsList'] = ActionModel::getKeywords();
        
        return $response->withJson($obj);
    }

    protected function manageValue($request)
    {
        foreach ($request  as $key => $value) {
            if (in_array($key, ['is_folder_action', 'history'])) {
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
