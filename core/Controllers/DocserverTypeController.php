<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief DocerverType Controller
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\DocserverTypeModel;

class DocserverTypeController
{
    public function getList(RequestInterface $request, ResponseInterface $response)
    {
        $obj = DocserverTypeModel::getList();
        
        $datas = [
            [
                'DocserverType' => $obj,
            ]
        ];
        
        return $response->withJson($datas);
    }

    public function getById(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (isset($aArgs['id'])) {
            $id = $aArgs['id'];
            $obj = DocserverTypeModel::getById([
                'id' => $id
            ]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _ID . ' ' . _IS_EMPTY]);
        }
        
        $datas = [
            [
                'DocserverType' => $obj,
            ]
        ];

        return $response->withJson($datas);
    }

    public function create(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $errors = [];

        $errors = $this->control($request, 'create');

        if (!empty($errors)) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => $errors]);
        }

        $aArgs = $request->getQueryParams();

        $return = DocserverTypeModel::create($aArgs);

        if ($return) {
            $id = $aArgs['id'];
            $obj = DocserverTypeModel::getById([
                'id' => $id
            ]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_CREATE]);
        }

        $datas = [
            [
                'DocserverType' => $obj,
            ]
        ];

        return $response->withJson($datas);
    }

    public function update(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $errors = [];

        $errors = $this->control($request, 'update');

        if (!empty($errors)) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => $errors]);
        }

        $aArgs = $request->getQueryParams();

        $return = DocserverTypeModel::update($aArgs);

        if ($return) {
            $id = $aArgs['id'];
            $obj = DocserverTypeModel::getById([
                'id' => $id
            ]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_UPDATE]);
        }

        $datas = [
            [
                'DocserverType' => $obj,
            ]
        ];

        return $response->withJson($datas);
    }

    public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (isset($aArgs['id'])) {
            $id = $aArgs['id'];
            $obj = DocserverTypeModel::delete([
                'id' => $id
            ]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_DELETE]);
        }
        
        $datas = [
            [
                'DocserverType' => $obj,
            ]
        ];

        return $response->withJson($datas);
    }

    protected function control($request, $mode)
    {
        $errors = [];

        if ($mode == 'update') {
            $obj = DocserverTypeModel::getById([
                'id' => $request->getParam('id')
            ]);
            if (empty($obj)) {
                array_push(
                    $errors,
                    _ID . ' ' . $request->getParam('id') . ' ' . _NOT_EXISTS
                );
            }
        }

        if (!Validator::notEmpty()->validate($request->getParam('id'))) {
            array_push($errors, _ID . ' ' . _IS_EMPTY);
        } elseif ($mode == 'create') {
            $obj = DocserverTypeModel::getById([
                'id' => $request->getParam('id')
            ]);
            if (!empty($obj)) {
                array_push(
                    $errors,
                    _ID . ' ' . $obj[0]['id'] . ' ' . _ALREADY_EXISTS
                );
            }
        }

        if (!Validator::regex('/^[\w.-]*$/')->validate($request->getParam('id'))) {
            array_push($errors, _ID . ' ' . _NOT . ' ' . _VALID);
        }

        if (!Validator::notEmpty()->validate($request->getParam('label_status'))) {
            array_push($errors, _LABEL_STATUS . ' ' . _IS_EMPTY);
        }

        if (Validator::notEmpty()
                ->validate($request->getParam('is_system')) &&
            !Validator::contains('Y')
                ->validate($request->getParam('is_system')) &&
            !Validator::contains('N')
                ->validate($request->getParam('is_system'))
        ) {
            array_push($errors, _IS_SYSTEM . ' ' . _NOT . ' ' . _VALID);
        }

        if (Validator::notEmpty()
                ->validate($request->getParam('is_folder_status')) &&
            !Validator::contains('Y')
                ->validate($request->getParam('is_folder_status')) &&
            !Validator::contains('N')
                ->validate($request->getParam('is_folder_status'))
        ) {
            array_push($errors, _IS_FOLDER_STATUS . ' ' . _NOT . ' ' . _VALID);
        }

        if (Validator::notEmpty()
                ->validate($request->getParam('img_filename')) &&
            (!Validator::regex('/^[\w-.]+$/')
                ->validate($request->getParam('img_filename')) ||
            !Validator::length(null, 255)
                ->validate($request->getParam('img_filename')))
        ) {
            array_push($errors, _IMG_FILENAME . ' ' . _NOT . ' ' . _VALID);
        }

        if (Validator::notEmpty()
                ->validate($request->getParam('maarch_module')) &&
            !Validator::length(null, 255)
                ->validate($request->getParam('maarch_module'))
        ) {
            array_push($errors, _MAARCH_MODULE . ' ' . _NOT . ' ' . _VALID);
        }

        if (Validator::notEmpty()
                ->validate($request->getParam('can_be_searched')) &&
            !Validator::contains('Y')
                ->validate($request->getParam('can_be_searched')) &&
            !Validator::contains('N')
                ->validate($request->getParam('can_be_searched'))
        ) {
            array_push($errors, _CAN_BE_SEARCHED . ' ' . _NOT . ' ' . _VALID);
        }

        if (Validator::notEmpty()
                ->validate($request->getParam('can_be_modified')) &&
            !Validator::contains('Y')
                ->validate($request->getParam('can_be_modified')) &&
            !Validator::contains('N')
                ->validate($request->getParam('can_be_modified'))
        ) {
            array_push($errors, _CAN_BE_MODIFIED . ' ' . _NOT . ' ' . _VALID);
        }

        return $errors;
    }
}
