<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ParametersController
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Core\Controllers;

use Core\Models\ParameterModel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;

class ParametersController
{
    public function getParametersForAdministration(RequestInterface $request, ResponseInterface $response)
    {
        $obj = [
                'parametersList'    =>  ParameterModel::getList(),
                'lang'              =>  ParameterModel::getParametersLang()
        ];
        return $response->withJson($obj);
    }

    public function getParameterForAdministration(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $obj['parameter'] = ParameterModel::getById(['id' => $aArgs['id']]);
        
        if (empty($obj)) {
            return $response->withStatus(400)->withJson(['errors' => 'User not found']);
        }

        if ($obj['parameter']['param_value_date']) {
            $obj['type'] = 'date';
        } else if ($obj['parameter']['param_value_int']) {
            $obj['type'] = 'int';
        } else {
            $obj['type'] = 'string';
        }

        $obj['lang'] = ParameterModel::getParametersLang();

        return $response->withJson($obj);
    }

    public function getNewParameterForAdministration(RequestInterface $request, ResponseInterface $response)
    {
        $obj['lang'] = ParameterModel::getParametersLang();
        return $response->withJson($obj);
    }

    public function getById(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $obj = ParameterModel::getById(['id' => $aArgs['id']]);
        return $response->withJson($obj);
    }
    public function create(RequestInterface $request, ResponseInterface $response)
    {
        $errors = $this->control($request, 'create');

        if (!empty($errors)) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => $errors]);
        }
        
        $datas = $request->getParams();

        $return = ParameterModel::create($datas);

        if ($return) {
            $obj = ParameterModel::getById(['id' => $datas['id']]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_CREATE]);
        }

        return $response->withJson(
            [
            'success'   =>  _PARAMETER. ' <b>' . $obj['id'] .'</b> ' ._ADDED
            ]
        );
    }

    public function update(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $errors = $this->control($request, 'update');

        if (!empty($errors)) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => $errors]);
        }

        $aArgs = $request->getParams();
        $return = ParameterModel::update($aArgs);

        if ($return) {
            $obj = ParameterModel::getById(['id' => $aArgs['id']]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_UPDATE]);
        }

        return $response->withJson(
            [
            'success'   =>  _PARAMETER. ' <b>' . $aArgs['id'] .'</b> ' ._UPDATED,
            ]
        );
    }

    public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $obj = ParameterModel::delete(['id' => $aArgs['id']]);
        return $response->withJson(
            [
            'success'   =>  _PARAMETER. ' <b>' . $aArgs['id'] .'</b> ' ._DELETED,
            'parameters'    =>  ParameterModel::getList()
            ]
        );
    }

    protected function control($request, $mode)
    {
        $errors = [];

        if ($mode == 'update') {
            $obj = ParameterModel::getById(
                [
                'id' => $request->getParam('id'),
                'param_value_int' => $request->getParam('param_value_int')
                ]
            );
            if (empty($obj)) {
                array_push($errors, _ID . ' '. _NOT_EXISTS);
            }
        }
        if (!Validator::notEmpty()->validate($request->getParam('id'))) {
            array_push($errors, _ID_IS_EMPTY_CONTROLLER);
        } elseif ($mode == 'create') {
            if (!Validator::regex('/^[\w.-]*$/')->validate($request->getParam('id'))) {
                array_push($errors, _INVALID_ID);
            }
            if (!Validator::regex('/^[\w.-]*$/')->validate($request->getParam('description'))&&$request->getParam('description')!=null) {
                array_push($errors, _INVALID_DESCRIPTION);
            }
            if (!Validator::regex('/^[\w.-]*$/')->validate($request->getParam('param_value_string'))&&$request->getParam('param_value_string')!=null) {
                array_push($errors, _INVALID_STRING);
            }
            if (!Validator::regex('/^[0-9]*$/')->validate($request->getParam('param_value_int')) && $request->getParam('param_value_int')!=null) {
                array_push($errors, _INVALID_INTEGER);
            }
            $obj = ParameterModel::getById(['id' => $request->getParam('id')]);
            if (!empty($obj)) {
                array_push($errors, _ID . ' ' . $obj[0]['id'] . ' ' . _ALREADY_EXISTS);
            }
        }
        if ($request->getParam('param_value_date')!=null) {
            if (date('d-m-Y', strtotime($request->getParam('param_value_date'))) != $request->getParam('param_value_date')) {
                array_push($errors, _INVALID_PARAM_DATE);
            }
        }
        if (!Validator::notEmpty()->validate($request->getParam('param_value_int'))
            && !Validator::notEmpty()->validate($request->getParam('param_value_string'))
            && !Validator::notEmpty()->validate($request->getParam('param_value_date'))
        ) {
            array_push($errors, _PARAM_VALUE_IS_EMPTY);
        }

        return $errors;
    }
}
