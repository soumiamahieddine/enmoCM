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

use Respect\Validation\Validator;
use Action\models\ActionModel;
use Status\models\StatusModel;
use Core\Models\LangModel;
use Slim\Http\Request;
use Slim\Http\Response;

class ActionController
{
    public function get(Request $request, Response $response)
    {
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
                ->withJson(['errors' => _ID . ' ' . _IS_EMPTY]);
        }

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

        $obj['categoriesList'] = ActionModel:: getLettersBoxCategories();

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
  
        return $response->withJson($obj);
    }

    public function create(Request $request, Response $response, $aArgs)
    {
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
            $id = $aArgs['id'];

            $obj = max(ActionModel::get());
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_CREATE]);
        }

        return $response->withJson(
            [
            'success'   =>  _ACTION. ' <b>' . $obj['id'] .'</b> ' ._ADDED,
            'action'      => $obj
            ]
        );
    }

    public function update(Request $request, Response $response, $aArgs)
    {
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
            $id = $aArgs['id'];
            $obj = ActionModel::getById(['id' => $id]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_UPDATE]);
        }

        return $response->withJson(
            [
            'success'   => _ACTION. ' <b>' . $id .'</b> ' ._UPDATED,
            'action'      => $obj
            ]
        );
    }

    public function delete(Request $request, Response $response, $aArgs)
    {
        if (isset($aArgs['id'])) {
            $id = $aArgs['id'];
            ActionModel::delete(['id' => $id]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_DELETE]);
        }
        
        return $response->withJson(
            [
            'success'   => _ACTION. ' <b>' . $id .'</b> ' ._DELETED,
            'action'      => ActionModel::get()
            ]
        );
    }

    protected function control($aArgs, $mode)
    {
        $errors = [];
      
        $objs = StatusModel::get();

        foreach ($objs as $obj) {
            $status[]=$obj['id'];
        }
        array_unshift($status, '_NOSTATUS_');

        if (!(in_array($aArgs['id_status'], $status))) {
            $errors[]=_STATUS. ' ' . _NOT_VALID;
        }

        if ($mode == 'update') {
            $obj = ActionModel::getById(['id' => $aArgs['id']]);
           
            if (empty($obj)) {
                $errors[]=_ID . ' ' .$aArgs['id']. ' ' . _NOT_EXISTS;
            }
        }
           
        if (!Validator::notEmpty()->validate($aArgs['label_action'])) {
            $errors[]=_NO_RIGHT.' '._DESC;
        }

        if (!Validator::notEmpty()->validate($aArgs['id_status'])) {
            $errors[]=CHOOSE_STATUS;
        }

        if (!Validator::notEmpty()->validate($aArgs['create_id']) || ($aArgs['create_id']!='Y' && $aArgs['create_id']!='N')) {
            $errors[]= _CREATE_ID . ' ' . _NOT_VALID;
        }

        if (!Validator::notEmpty()->validate($aArgs['history']) || ($aArgs['history']!='Y' && $aArgs['history']!='N')) {
            $errors[]= _ACTION_HISTORY . ' ' . _NOT_VALID;
        }
        

        if (!Validator::notEmpty()->validate($aArgs['is_system']) || ($aArgs['is_system']!='Y' && $aArgs['is_system']!='N')) {
            $errors[]= _IS_SYSTEM . ' ' . _NOT_VALID;
        }


        return $errors;
    }

    public function initAction(Request $request, Response $response)
    {
        //default data
        $obj['action']['history']          = true;
        $obj['action']['keyword']          = '';
        $obj['action']['is_folder_action'] = false;
        $obj['action']['is_system']        = false;
        $obj['action']['action_page']      = '';
        $obj['action']['id_status']        = '_NOSTATUS_';
        $obj['action']['create_id']        = false;
        $obj['categoriesList']             = ActionModel::getLettersBoxCategories();

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
            if (in_array($key, ['is_system', 'is_folder_action', 'history', 'create_id'])) {
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
