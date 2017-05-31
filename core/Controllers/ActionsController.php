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

class ActionsController
{
    public function getList(RequestInterface $request, ResponseInterface $response)
    {
        $obj = ActionsModel::getList();
        
        $datas = [
            $obj,
        ];
        
        return $response->withJson($datas);
    }

    public function getById(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (isset($aArgs['id'])) {
            $id = $aArgs['id'];
            $obj = ActionsModel::getById([
                'id' => $id
            ]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _ID . ' ' . _IS_EMPTY]);
        }
        
        $datas = [
            $obj,
        ];

        return $response->withJson($datas);
    }

    public function create(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $errors = [];

        $aArgs = $request->getParams();

        $errors = $this->control($aArgs, 'create');
        

        if (!empty($errors)) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => $errors]);
        }

        $return = ActionsModel::create($aArgs);

        if ($return) {
            $id = $aArgs['id'];

            $obj = end(ActionsModel::getList());
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

        foreach ($objs as $obj){
            $status[]=$obj['id'];
        }
       
        if(!(in_array($aArgs['id_status'], $status))){
            $errors[]=_STATUS. ' ' . _NOT . ' ' . _VALID;
        }
        if (!Validator::regex('/^[\w ]+$/')->validate($aArgs['keyword'])) {
            $errors[]= _KEYWORD. ' ' . _NOT . ' ' . _VALID;
        }

        if ($mode == 'update') {
            $obj = ActionsModel::getById(['id' => $aArgs['id']]);
           
            if (empty($obj)) {
        
                $errors[]=_ID . ' ' .$aArgs['id']. ' ' . _NOT_EXISTS;

            }
        }

        if ($mode == 'create') {
           
            if (!Validator::notEmpty()->validate($aArgs['label_action'])) {
                $errors[]=_NO_RIGHT.' '._DESC;
            }

            if (!Validator::notEmpty()->validate($aArgs['id_status'])) {
                $errors[]=CHOOSE_STATUS;
            }

            if (!Validator::notEmpty()->validate($aArgs['action_page'])) {
                $errors[]=_CHOOSE_ACTION;
            }

            if (!Validator::notEmpty()->validate($aArgs['history']) || ($aArgs['history']!='Y' && $aArgs['history']!='N') ) 
            {
                $errors[]= _ACTION_HISTORY . ' ' . _NOT . ' ' . _VALID;
            }

            if (!Validator::notEmpty()->validate($aArgs['keyword'])) {
                $errors[]=_TRACE_ACT;
            }
            

            if (!Validator::notEmpty()->validate($aArgs['is_system']) || ($aArgs['is_system']!='Y' && $aArgs['is_system']!='N') ) 
            {
                $errors[]= _IS_SYSTEM . ' ' . _NOT . ' ' . _VALID;
            }

        }

        return $errors;
    }
}
