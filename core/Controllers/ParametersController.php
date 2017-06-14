<?php

    namespace Core\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\LangModel;
use Core\Models\ParametersModel;

    require_once 'core/class/class_db_pdo.php';
    require_once 'modules/notes/Models/NotesModel.php';

    class ParametersController
    {

        public function getList(RequestInterface $request, ResponseInterface $response)
        {

            $obj = [
                    'parametersList'    =>  ParametersModel::getList(),
                    'lang'              =>  ParametersModel::getParametersLang()
            ];
            return $response->withJson($obj);
        }

        public function getLang(RequestInterface $request, ResponseInterface $response){
            $obj = ParametersModel::getParametersLang();
            return $response->withJson($obj);
        }

        public function getById(RequestInterface $request, ResponseInterface $response, $aArgs)
        {                    
            $obj = ParametersModel::getById(['id' => $aArgs['id']]);
            return $response->withJson($obj);             
        }
        public function create(RequestInterface $request, ResponseInterface $response)
        {
            $datas = $request->getParams();

            $errors = $this->control($request, 'create',$datas);

            if (!empty($errors)) {
                return $response
                    ->withJson(['errors' => $errors]);
            }           
            
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
            $datas = $request->getParams();

            $errors = $this->control($request, 'update',$datas);

            if (!empty($errors)) {
                return $response
                    ->withJson(['errors' => $errors]);
            }

            $return = ParametersModel::update($datas);

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

        protected function control($request, $mode, $aArgs)
        {
            $errors = [];

            if ($mode == 'update') {
                $obj = ParametersModel::getById([
                    'id' => $aArgs['id'],
                    'param_value_int' => $aArgs['param_value_int']
                ]);
                if (empty($obj)) {
                    array_push(
                        $errors,
                        _ID . ' '. _NOT_EXISTS
                    );                    
                }
                
            }
            if (!Validator::notEmpty()->validate($aArgs['id'])) {
                array_push($errors, _ID_IS_EMPTY_CONTROLLER);
            } elseif ($mode == 'create') {  
                if(!Validator::regex('/^[\w.-]*$/')->validate($request->getParam('id'))){
                    array_push($errors,_INVALID_ID);
                }
                if(!Validator::regex('/^[\w.-]*$/')->validate($request->getParam('description'))&&$request->getParam('description')!=null){
                    array_push($errors,_INVALID_DESCRIPTION);
                }
                if (!Validator::regex('/^[\w.-]*$/')->validate($request->getParam('param_value_string'))&&$request->getParam('param_value_string')!=null) {
                    array_push($errors,_INVALID_STRING);
                }
                if (!Validator::regex('/^[0-9]*$/')->validate($request->getParam('param_value_int')) && $request->getParam('param_value_int')!=null){
                    array_push($errors,_INVALID_INTEGER);
                }
                $obj = ParametersModel::getById([
                    'id' => $aArgs['id']
                ]);
                if (!empty($obj)) {
                    array_push(
                        $errors,
                        _ID . ' ' . $obj[0]['id'] . ' ' . _ALREADY_EXISTS
                    );
                }
            }
            if ($aArgs['param_value_date']!=null) {
                if (date('d-m-Y', strtotime($aArgs['param_value_date'])) != $aArgs['param_value_date']) {
                    array_push(
                            $errors,
                            _INVALID_PARAM_DATE
                        );
                }
            }
            if (!Validator::notEmpty()->validate($aArgs['param_value_int'])&&
            !Validator::notEmpty()->validate($aArgs['param_value_string'])&&
            !Validator::notEmpty()->validate($aArgs['param_value_date'])
            ) {
                array_push($errors, _PARAM_VALUE_IS_EMPTY);
            }          

            return $errors;
        }

    }

?>