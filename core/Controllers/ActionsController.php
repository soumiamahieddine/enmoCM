<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Status Controller
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\ActionsModel;
use Core\Models\StatusModel;
use Core\Models\LangModel;

class ActionsController
{
    public function getForAdministration(RequestInterface $request, ResponseInterface $response){
        
        $obj['lang'] = LangModel::getActionsForAdministrationLang();
        $obj ['actions']= ActionsModel::getList();
       
        return $response->withJson($obj);
    }

    public function getByIdForAdministration(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (isset($aArgs['id'])) {
            $id = $aArgs['id'];
            $obj['action'] = ActionsModel::getById(['id' => $id]);
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


        $obj['categoriesList'] = ActionsModel:: getLettersBoxCategories();

        //array of id categoriesList
        foreach ($obj['categoriesList'] as $key => $category) {
            $arrCategoriesList[] = $category['id'];
        }
        //array of id actionCategories
        if (!empty($obj['action']['actionCategories'])) {
            foreach ($obj['action']['actionCategories'] as $actionCategories) {
                $arrActionCategories[] = $actionCategories['category_id'];
            }
            //check
            foreach ($arrActionCategories as $key => $category_id) {
                if (in_array($category_id, $arrCategoriesList)) {
                    $obj['categoriesList'][$key]['selected'] = true;
                } else {
                    $obj['categoriesList'][$key]['selected'] = false;
                }
            }
        } else {
            foreach ($obj['categoriesList'] as $key => $category) {
                $obj['categoriesList'][$key]['selected'] = false;
            }
        }
    
        $obj['statusList'] = StatusModel::getList();
        array_unshift($obj['statusList'], ['id'=>'_NOSTATUS_','label_status'=> _UNCHANGED]);
        $obj['action_pagesList']=ActionsModel::getAction_pages();
        array_unshift($obj['action_pagesList']['actionsPageList'], ['id'=>'','label'=> _NO_PAGE, 'name'=>'', 'origin'=>'']);
        $obj['keywordsList']=ActionsModel::getKeywords();
        $obj['lang'] = LangModel::getActionsForAdministrationLang();
  
        return $response->withJson($obj);
    }

    public function create(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $errors = [];

        $aArgs = $request->getParams();

        $aArgs = $this->manageValue($aArgs);
        
        $errors = $this->control($aArgs, 'create');
        

        if (!empty($errors)) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => $errors]);
        }
    
        $return = ActionsModel::create($aArgs);

        if ($return) {
            $id = $aArgs['id'];

            $obj = max(ActionsModel::getList());
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_CREATE]);
        }

        $datas = [
            $obj,
        ];

        return  $response->withJson($datas);
    }

    public function update(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $errors = [];

        $obj = $request->getParams();
        $obj['id']=$aArgs['id'];

        $obj = $this->manageValue($obj);

        $errors = $this->control($obj, 'update');
      
        if (!empty($errors)) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => $errors]);
        }

        $return = ActionsModel::update($obj);

        if ($return) {
            $id = $aArgs['id'];
            $obj = ActionsModel::getById([
                'id' => $id
            ]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_UPDATE]);
        }

        $datas = [
            $obj,
        ];

        return $response->withJson($datas);
    }


    public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (isset($aArgs['id'])) {
            $id = $aArgs['id'];
            $obj = ActionsModel::delete([
                'id' => $id
            ]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_DELETE]);
        }
        
        $datas = [
            $obj,
        ];

        return $response->withJson($datas);
    }

    protected function control($aArgs, $mode)
    {
        $errors = [];
      
        $objs = StatusModel::getList();

        foreach ($objs as $obj) {
            $status[]=$obj['id'];
        }
        array_unshift($status, '_NOSTATUS_');

        if (!(in_array($aArgs['id_status'], $status))) {
            $errors[]=_STATUS. ' ' . _NOT_VALID;
        }

        if ($mode == 'update') {
            $obj = ActionsModel::getById(['id' => $aArgs['id']]);
           
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

        if (!Validator::notEmpty()->validate($aArgs['create_id']) || ($aArgs['create_id']!='Y' && $aArgs['create_id']!='N') ) {
            $errors[]= _CREATE_ID . ' ' . _NOT_VALID;
        }

        if (!Validator::notEmpty()->validate($aArgs['history']) || ($aArgs['history']!='Y' && $aArgs['history']!='N') ) {
            $errors[]= _ACTION_HISTORY . ' ' . _NOT_VALID;
        }
        

        if (!Validator::notEmpty()->validate($aArgs['is_system']) || ($aArgs['is_system']!='Y' && $aArgs['is_system']!='N') ) {
            $errors[]= _IS_SYSTEM . ' ' . _NOT_VALID;
        }


        return $errors;
    }

    public function initAction(RequestInterface $request, ResponseInterface $response)
    {
        //default data
        $obj['action']['history'] = true;
        $obj['action']['keyword'] = '';
        $obj['action']['is_folder_action'] = false;
        $obj['action']['is_system'] = false;
        $obj['action']['action_page'] = '';
        $obj['action']['id_status'] = '_NOSTATUS_';
        $obj['action']['create_id'] = false;
        $obj['categoriesList'] = ActionsModel::getLettersBoxCategories();
        foreach ($obj['categoriesList'] as $key => $value) {
            $obj['categoriesList'][$key]['selected'] = true;
        }

        $obj['statusList'] = StatusModel::getList();
        array_unshift($obj['statusList'], ['id'=>'_NOSTATUS_','label_status'=> _UNCHANGED]);
        $obj['action_pagesList'] = ActionsModel::getAction_pages();
        array_unshift($obj['action_pagesList']['actionsPageList'], ['id'=>'','label'=> _NO_PAGE, 'name'=>'', 'origin'=>'']);
        $obj['keywordsList']=ActionsModel::getKeywords();
        $obj['lang'] = LangModel::getActionsForAdministrationLang();
        
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
