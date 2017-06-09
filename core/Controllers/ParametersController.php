<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Parameters Controller
* @author dev@maarch.org
* @ingroup core
*/

    namespace Core\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\ParametersModel;

    require_once 'core/class/class_db_pdo.php';
    require_once 'modules/notes/Models/NotesModel.php';

    class ParametersController
    {

        public function getList(RequestInterface $request, ResponseInterface $response)
        {
            $obj = ParametersModel::getList();
            
            return $response->withJson($obj);
        }

        public function getById(RequestInterface $request, ResponseInterface $response, $aArgs)
        {

            $obj = ParametersModel::getById(['id' => $aArgs['id']]);
            return $response->withJson($obj);
        }
        public function create(RequestInterface $request, ResponseInterface $response)
        {
            $errors = $this->control($request, 'create');

            if (!empty($errors)) {
                return $response
                    ->withJson(['errors' => $errors]);
            }           
            
            $datas = $request->getParams();

            $return = ParametersModel::create($datas);

            if ($return) {
                $obj = ParametersModel::getById([
                    'id' => $datas['id']
                ]);
            } else {
                return $response
                    ->withStatus(500)
                    ->withJson(['errors' => _NOT_CREATE]);
            }

            return $response->withJson($obj);
        }

        public function update(RequestInterface $request, ResponseInterface $response, $aArgs)
        {
            $errors = $this->control($request, 'update');

            if (!empty($errors)) {
                return $response
                    ->withJson(['errors' => $errors]);
            }      

            $aArgs = $request->getParams();
            $return = ParametersModel::update($aArgs);

            if ($return) {
                $obj = ParametersModel::getById([
                    'id' => $aArgs['id']
                ]);
            } else {
                return $response
                    ->withStatus(500)
                    ->withJson(['errors' => _NOT_UPDATE]);
            }

            return $response->withJson($obj);
        }

        public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
        {
            $obj = ParametersModel::delete(['id' => $aArgs['id']]);
            return $response->withJson($obj);
        }

        protected function control($request, $mode)
        {
            $errors = [];

            if ($mode == 'update') {
                $obj = ParametersModel::getById([
                    'id' => $request->getParam('id'),
                    'param_value_int' => $request->getParam('param_value_int')
                ]);
                if (empty($obj)) {
                    array_push(
                        $errors,
                        _ID . ' '. _NOT_EXISTS
                    );                    
                }
                
            }
            if (!Validator::notEmpty()->validate($request->getParam('id'))) {
                array_push($errors, '_ID_IS_EMPTY_CONTROLLER');
            } elseif ($mode == 'create') {  
                if(!Validator::regex('/^[\w.-]*$/')->validate($request->getParam('id'))){
                    array_push($errors,'ID INVALIDE');
                }
                if(!Validator::regex('/^[\w.-]*$/')->validate($request->getParam('description'))&&$request->getParam('description')!=null){
                    array_push($errors,'DESCRIPTION INVALIDE');
                }
                if (!Validator::regex('/^[\w.-]*$/')->validate($request->getParam('param_value_string'))&&$request->getParam('param_value_string')!=null) {
                    array_push($errors,'PARAM STRING INVALIDE');
                }
                $obj = ParametersModel::getById([
                    'id' => $request->getParam('id')
                ]);
                if (!empty($obj)) {
                    array_push(
                        $errors,
                        _ID . ' ' . $obj[0]['id'] . ' ' . _ALREADY_EXISTS
                    );
                }
            }
            if ($request->getParam('param_value_date')!=null) {
                if (date('Y-m-d H:i:s', strtotime($request->getParam('param_value_date'))) != $request->getParam('param_value_date')) {
                    array_push(
                            $errors,
                            'PARAMETRE DATE INVALIDE.'
                        );
                }
            }
            if ($mode=='create'&&!Validator::notEmpty()->validate($request->getParam('param_value_int'))&&
            !Validator::notEmpty()->validate($request->getParam('param_value_string'))&&
            !Validator::notEmpty()->validate($request->getParam('param_value_date'))
            ) {
                array_push($errors, '_PARAM_VALUE_IS_EMPTY');
            }          

            return $errors;
        }

    }
