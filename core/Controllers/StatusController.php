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
use Core\Models\StatusModel;
use Core\Models\ServiceModel;

class StatusController
{
    public function getList(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_status', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $datas = [
            'statusList' => StatusModel::getList(),
            'lang'       => StatusModel::getStatusLang()
        ];
        
        return $response->withJson($datas);
    }

    public function getLang(RequestInterface $request, ResponseInterface $response){
        $obj = StatusModel::getStatusLang();
        return $response->withJson($obj);
    }

    public function getById(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!empty($aArgs['id'])) {
            $obj = StatusModel::getById([
                'id' => $aArgs['id']
            ]);

            if (empty($obj)) {
                return $response->withStatus(404)->withJson(['errors' => 'Id not found']);
            }

            return $response->withJson([
                'status' => $obj,
                'lang'   =>  StatusModel::getStatusLang()
            ]);

        } else {
            return $response->withStatus(400)->withJson(['errors' => _ID . ' ' . _IS_EMPTY]);
        }

    }

    public function create(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_status', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $this->control($request, 'create');

        $aArgs = $request->getParams();

        $return = StatusModel::create($aArgs);

        if ($return) {
            $id = $aArgs['id'];
            $obj = StatusModel::getById([
                'id' => $id
            ]);
            return $response->withJson([$obj]);

        } else {
            return $response->withStatus(500)->withJson(['errors' => _NOT_CREATE]);
        }
    }

    public function update(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_status', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $this->control($request, 'update');

        $aArgs = $request->getParams();

        $return = StatusModel::update($aArgs);

        if ($return) {
            $id = $aArgs['id'];
            $obj = StatusModel::getById([
                'id' => $id
            ]);

        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_UPDATE]);
        }

        return $response->withJson([$obj]);
    }

    public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_status', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (isset($aArgs['id'])) {
            $id = $aArgs['id'];
            $obj = StatusModel::delete([
                'id' => $id
            ]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_DELETE]);
        }

        return $response->withJson([$obj]);
    }

    protected function control($request, $mode)
    {
        $errors = [];

        if ($mode == 'update') {
            $obj = StatusModel::getById([
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
            $obj = StatusModel::getById([
                'id' => $request->getParam('id')
            ]);
            if (!empty($obj)) {
                array_push(
                    $errors,
                    _ID . ' ' . $obj[0]['id'] . ' ' . _ALREADY_EXISTS
                );
            }
        }

        if (!Validator::regex('/^[\w.-]*$/')->validate($request->getParam('id')) ||
            !Validator::length(null, 10)->validate($request->getParam('id'))) {
            array_push($errors, _ID . ' ' . _NOT . ' ' . _VALID);
        }

        if (!Validator::notEmpty()->validate($request->getParam('label_status')) ||
            !Validator::length(null, 50)->validate($request->getParam('label_status'))) {
            array_push($errors, _LABEL_STATUS . ' ' . _IS_EMPTY);
        }

        if ( Validator::notEmpty()->validate($request->getParam('is_system')) &&
            !Validator::contains('Y')->validate($request->getParam('is_system')) &&
            !Validator::contains('N')->validate($request->getParam('is_system'))
        ) {
            array_push($errors, _IS_SYSTEM . ' ' . _NOT . ' ' . _VALID);
        }

        if ( Validator::notEmpty()->validate($request->getParam('is_folder_status')) &&
            !Validator::contains('Y')->validate($request->getParam('is_folder_status')) &&
            !Validator::contains('N')->validate($request->getParam('is_folder_status'))
        ) {
            array_push($errors, _IS_FOLDER_STATUS . ' ' . _NOT . ' ' . _VALID);
        }

        if ( Validator::notEmpty()->validate($request->getParam('img_filename')) &&
            (!Validator::regex('/^[\w-.]+$/')->validate($request->getParam('img_filename')) ||
            !Validator::length(null, 255)->validate($request->getParam('img_filename')))
        ) {
            array_push($errors, _IMG_FILENAME . ' ' . _NOT . ' ' . _VALID);
        }

        if ( Validator::notEmpty()->validate($request->getParam('maarch_module')) &&
            !Validator::length(null, 255)->validate($request->getParam('maarch_module'))
        ) {
            array_push($errors, _MAARCH_MODULE . ' ' . _NOT . ' ' . _VALID);
        }

        if ( Validator::notEmpty()->validate($request->getParam('can_be_searched')) &&
            !Validator::contains('Y')->validate($request->getParam('can_be_searched')) &&
            !Validator::contains('N')->validate($request->getParam('can_be_searched'))
        ) {
            array_push($errors, _CAN_BE_SEARCHED . ' ' . _NOT . ' ' . _VALID);
        }

        if ( Validator::notEmpty()->validate($request->getParam('can_be_modified')) &&
            !Validator::contains('Y')->validate($request->getParam('can_be_modified')) &&
            !Validator::contains('N')->validate($request->getParam('can_be_modified'))
        ) {
            array_push($errors, _CAN_BE_MODIFIED . ' ' . _NOT . ' ' . _VALID);
        }

        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }

    }
}
