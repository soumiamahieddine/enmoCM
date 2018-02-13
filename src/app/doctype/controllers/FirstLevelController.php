<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   DoctypeController
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Doctype\controllers;

use History\controllers\HistoryController;
use Respect\Validation\Validator;
use SrcCore\models\CoreConfigModel;
use Doctype\models\FirstLevelModel;
use Doctype\models\SecondLevelModel;
use Doctype\models\DoctypeModel;
use Core\Models\ServiceModel;
use Slim\Http\Request;
use Slim\Http\Response;

class DoctypeController
{
    public function get(Request $request, Response $response)
    {

        $firstLevels  = FirstLevelModel::get();
        $secondLevels = SecondLevelModel::get();
        $docTypes     = DoctypeModel::get();

        $structure = [];
        foreach($firstLevels as $firstLevelValue){
            foreach ($secondLevels as $secondLevelValue) {
                if($firstLevelValue['doctypes_first_level_id'] == $secondLevelValue['doctypes_first_level_id']){
                    foreach ($docTypes as $doctypeValue) {
                        if($secondLevelValue['doctypes_second_level_id'] == $doctypeValue['doctypes_second_level_id']){
                            $secondLevelValue['doctypes'][] = $doctypeValue;
                        }
                    }
                    $firstLevelValue['secondeLevels'][] = $secondLevelValue;
                }
            }
            array_push($structure, $firstLevelValue);
        }

        return $response->withJson([
            'structure' => $structure,
        ]);
    }

    public function getFirstLevelById(Request $request, Response $response, $aArgs)
    {

        if (!Validator::intVal()->validate($aArgs['id']) || !Validator::notEmpty()->validate($aArgs['id'])) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'wrong format for id'.$aArgs['id']]);
        }

        $obj = FirstLevelModel::getById(['id' => $aArgs['id']]);

        if(!empty($obj)){
            if ($obj['enabled'] == 'Y') {
                $obj['enabled'] = true;
            } else {
                $obj['enabled'] = false;
            }
        }
  
        return $response->withJson($obj);
    }

    public function getSecondLevelById(Request $request, Response $response, $aArgs)
    {

        if (!Validator::intVal()->validate($aArgs['id']) || !Validator::notEmpty()->validate($aArgs['id'])) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'wrong format for id']);
        }

        $obj = SecondLevelModel::getById(['id' => $aArgs['id']]);

        if(!empty($obj)){
            if ($obj['enabled'] == 'Y') {
                $obj['enabled'] = true;
            } else {
                $obj['enabled'] = false;
            }
        }
  
        return $response->withJson($obj);
    }

    public function getDoctypeById(Request $request, Response $response, $aArgs)
    {

        if (!Validator::intVal()->validate($aArgs['id']) || !Validator::notEmpty()->validate($aArgs['id'])) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'wrong format for id']);
        }

        $obj = DoctypeModel::getById(['id' => $aArgs['id']]);

        if(!empty($obj)){
            if ($obj['enabled'] == 'Y') {
                $obj['enabled'] = true;
            } else {
                $obj['enabled'] = false;
            }
        }
  
        return $response->withJson($obj);
    }






    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_actions', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $data  = $this->manageValue($data);
        
        $errors = $this->control($data, 'create');
        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }
    
        ActionModel::create($data);

        $obj = max(ActionModel::get());

        HistoryController::add([
            'tableName' => 'actions',
            'recordId'  => $obj['id'],
            'eventType' => 'ADD',
            'eventId'   => 'actionadd',
            'info'      => _ACTION_ADDED . ' : ' . $obj['label_action']
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
            'info'      => _ACTION_UPDATED. ' : ' . $obj['label_action']
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

        $action = ActionModel::getById(['id' => $aArgs['id']]); // TODO select label_action
        ActionModel::delete(['id' => $aArgs['id']]);

        HistoryController::add([
            'tableName' => 'actions',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'eventId'   => 'actiondel',
            'info'      => _ACTION_DELETED. ' : ' . $action['label_action']
        ]);

        return $response->withJson(['action' => ActionModel::get()]);
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
