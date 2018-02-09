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

namespace SrcCore\controllers;

use Core\Models\ServiceModel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\DocserverTypeModel;

class DocserverTypeController
{
    public function get(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_docservers', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson(['docserverTypes' => DocserverTypeModel::get()]);
    }

    public function getById(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_docservers', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $docserverType = DocserverTypeModel::getById(['id' => $aArgs['id']]);

        if(empty($docserverType)){
            return $response->withStatus(400)->withJson(['errors' => 'Docserver Type not found']);
        }

        return $response->withJson($docserverType);
    }

    public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_docservers', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $docserverType = DocserverTypeModel::getById(['id' => $aArgs['id']]);

        if(empty($docserverType)){
            return $response->withStatus(400)->withJson(['errors' => 'Docserver Type does not exist']);
        }

        DocserverTypeModel::delete(['id' => $aArgs['id']]);

        return $response->withJson(['docserverTypes' => DocserverTypeModel::get()]);
    }

    protected function control($request, $mode)
    {
        $errors = [];

        if ($mode == 'update') {
            $obj = DocserverTypeModel::getById([
                'docserver_type_id' => $request->getParam('docserver_type_id')
            ]);
            if (empty($obj)) {
                array_push(
                    $errors,
                    _DOCSERVER_TYPE_ID . ' ' . $request->getParam('docserver_type_id') . ' ' . _NOT_EXISTS
                );
            }
        }

        if (!Validator::notEmpty()->validate($request->getParam('docserver_type_id'))) {
            array_push($errors, _DOCSERVER_TYPE_ID . ' ' . _IS_EMPTY);
        } elseif ($mode == 'create') {
            $obj = DocserverTypeModel::getById([
                'docserver_type_id' => $request->getParam('docserver_type_id')
            ]);
            if (!empty($obj)) {
                array_push(
                    $errors,
                    _DOCSERVER_TYPE_ID . ' ' . $obj[0]['docserver_type_id'] . ' ' . _ALREADY_EXISTS
                );
            }
        }

        if (!Validator::regex('/^[\w.-]*$/')->validate($request->getParam('docserver_type_id'))) {
            array_push($errors, _DOCSERVER_TYPE_ID . ' ' . _NOT . ' ' . _VALID);
        }

        if (!Validator::notEmpty()->validate($request->getParam('docserver_type_label'))) {
            array_push($errors, _DOCSERVER_TYPE_LABEL . ' ' . _IS_EMPTY);
        }

        if (Validator::notEmpty()
                ->validate($request->getParam('is_container')) &&
            !Validator::contains('Y')
                ->validate($request->getParam('is_container')) &&
            !Validator::contains('N')
                ->validate($request->getParam('is_container'))
        ) {
            array_push($errors, _IS_CONTAINER . ' ' . _NOT . ' ' . _VALID);
        }

        if (Validator::notEmpty()
                ->validate($request->getParam('is_compressed')) &&
            !Validator::contains('Y')
                ->validate($request->getParam('is_compressed')) &&
            !Validator::contains('N')
                ->validate($request->getParam('is_compressed'))
        ) {
            array_push($errors, _IS_COMPRESSED . ' ' . _NOT . ' ' . _VALID);
        }

        if (Validator::notEmpty()
                ->validate($request->getParam('is_meta')) &&
            !Validator::contains('Y')
                ->validate($request->getParam('is_meta')) &&
            !Validator::contains('N')
                ->validate($request->getParam('is_meta'))
        ) {
            array_push($errors, _IS_META . ' ' . _NOT . ' ' . _VALID);
        }

        if (Validator::notEmpty()
                ->validate($request->getParam('is_logged')) &&
            !Validator::contains('Y')
                ->validate($request->getParam('is_logged')) &&
            !Validator::contains('N')
                ->validate($request->getParam('is_logged'))
        ) {
            array_push($errors, _IS_LOGGED . ' ' . _NOT . ' ' . _VALID);
        }

        if (Validator::notEmpty()
                ->validate($request->getParam('is_signed')) &&
            !Validator::contains('Y')
                ->validate($request->getParam('is_signed')) &&
            !Validator::contains('N')
                ->validate($request->getParam('is_signed'))
        ) {
            array_push($errors, _IS_SIGNED . ' ' . _NOT . ' ' . _VALID);
        }

        return $errors;
    }
}
