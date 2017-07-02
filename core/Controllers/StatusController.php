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

        $request = $request->getParams();
        $aArgs   = self::manageValue($request);
        $errors  = $this->control($aArgs, 'create');

        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }

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

        $request = $request->getParams();
        $aArgs   = self::manageValue($request);
        $errors  = $this->control($aArgs, 'update');

        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }


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

    protected function manageValue($request){
        foreach ($request  as $key => $value) {
            if(in_array($key, ['is_system', 'is_folder_status', 'can_be_searched', 'can_be_modified'])){
                if(empty($value)){
                    $request[$key] = 'N';
                } else {
                    $request[$key] = 'Y';
                }
            }
        }

        $request['is_system'] = 'N';

        return $request;
    }

    protected function control($request, $mode)
    {
        $errors = [];

        if ($mode == 'update') {
            $obj = StatusModel::getById([
                'id' => $request['id']
            ]);
            if (empty($obj)) {
                array_push(
                    $errors,
                    _ID . ' ' . $request['id'] . ' ' . _NOT_EXISTS
                );
            }
        }

        if (!Validator::notEmpty()->validate($request['id'])) {
            array_push($errors, _ID . ' ' . _IS_EMPTY);
        } elseif ($mode == 'create') {
            $obj = StatusModel::getById([
                'id' => $request['id']
            ]);
            if (!empty($obj)) {
                array_push(
                    $errors,
                    _ID . ' ' . $obj[0]['id'] . ' ' . _ALREADY_EXISTS
                );
            }
        }

        if (!Validator::regex('/^[\w.-]*$/')->validate($request['id']) ||
            !Validator::length(1, 10)->validate($request['id'])) {
            array_push($errors, 'id not valid');
        }

        if (!Validator::notEmpty()->validate($request['label_status']) ||
            !Validator::length(1, 50)->validate($request['label_status'])) {
            array_push($errors, 'label_status not valid');
        }

        if ( Validator::notEmpty()->validate($request['is_system']) &&
            !Validator::contains('Y')->validate($request['is_system']) &&
            !Validator::contains('N')->validate($request['is_system'])
        ) {
            array_push($errors, 'is_system not valid');
        }

        if ( Validator::notEmpty()->validate($request['is_folder_status']) &&
            !Validator::contains('Y')->validate($request['is_folder_status']) &&
            !Validator::contains('N')->validate($request['is_folder_status'])
        ) {
            array_push($errors, 'is_folder_status not valid');
        }

        if ( Validator::notEmpty()->validate($request['img_filename']) &&
            (!Validator::regex('/^[\w-.]+$/')->validate($request['img_filename']) ||
            !Validator::length(1, 255)->validate($request['img_filename']))
        ) {
            array_push($errors, 'img_filename not valid');
        }

        if ( Validator::notEmpty()->validate($request['maarch_module']) &&
            !Validator::length(null, 255)->validate($request['maarch_module'])
        ) {
            array_push($errors, 'maarch_module not valid');
        }

        if ( Validator::notEmpty()->validate($request['can_be_searched']) &&
            !Validator::contains('Y')->validate($request['can_be_searched']) &&
            !Validator::contains('N')->validate($request['can_be_searched'])
        ) {
            array_push($errors, 'can_be_searched not valid');
        }

        if ( Validator::notEmpty()->validate($request['can_be_modified']) &&
            !Validator::contains('Y')->validate($request['can_be_modified']) &&
            !Validator::contains('N')->validate($request['can_be_modified'])
        ) {
            array_push($errors, 'can_be_modified');
        }

        return $errors;

    }
}
