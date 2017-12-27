<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Docserver Controller
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\DocserverModel;

class DocserverController
{
    public function get(RequestInterface $request, ResponseInterface $response)
    {
        return $response->withJson(['docservers' => DocserverModel::get()]);
    }

    public function getById(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $docserver = DocserverModel::getById(['id' => $aArgs['id']]);

        if(empty($docserver)){
            return $response->withStatus(400)->withJson(['errors' => 'Docserver not found']);
        }

        return $response->withJson($docserver);
    }

    public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        //TODO Droit de suppression
        $docserver = DocserverModel::getById(['id' => $aArgs['id']]);

        if(empty($docserver)){
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        DocserverModel::delete(['id' => $aArgs['id']]);

        return $response->withJson(['docservers' => DocserverModel::get()]);
    }


    // Modèle pour le create et update
//    protected function control($request, $mode)
//    {
//        $errors = [];
//
//        if ($mode == 'update') {
//            $obj = DocserverModel::getById([
//                'id' => $request->getParam('id')
//            ]);
//            if (empty($obj)) {
//                array_push(
//                    $errors,
//                    _ID . ' ' . $request->getParam('id') . ' ' . _NOT_EXISTS
//                );
//            }
//        }
//
//        if (!Validator::notEmpty()->validate($request->getParam('id'))) {
//            array_push($errors, _ID . ' ' . _IS_EMPTY);
//        } elseif ($mode == 'create') {
//            $obj = DocserverModel::getById([
//                'id' => $request->getParam('id')
//            ]);
//            if (!empty($obj)) {
//                array_push(
//                    $errors,
//                    _ID . ' ' . $obj[0]['id'] . ' ' . _ALREADY_EXISTS
//                );
//            }
//        }
//
//        if (!Validator::regex('/^[\w.-]*$/')->validate($request->getParam('id'))) {
//            array_push($errors, _ID . ' ' . _NOT . ' ' . _VALID);
//        }
//
//        if (!Validator::notEmpty()
//                ->validate($request->getParam('label_status'))) {
//            array_push($errors, _LABEL_STATUS . ' ' . _IS_EMPTY);
//        }
//
//        if (Validator::notEmpty()
//                ->validate($request->getParam('is_system')) &&
//            !Validator::contains('Y')
//                ->validate($request->getParam('is_system')) &&
//            !Validator::contains('N')
//                ->validate($request->getParam('is_system'))
//        ) {
//            array_push($errors, _IS_SYSTEM . ' ' . _NOT . ' ' . _VALID);
//        }
//
//        if (Validator::notEmpty()
//                ->validate($request->getParam('is_folder_status')) &&
//            !Validator::contains('Y')
//                ->validate($request->getParam('is_folder_status')) &&
//            !Validator::contains('N')
//                ->validate($request->getParam('is_folder_status'))
//        ) {
//            array_push($errors, _IS_FOLDER_STATUS . ' ' . _NOT . ' ' . _VALID);
//        }
//
//        if (Validator::notEmpty()
//                ->validate($request->getParam('img_filename')) &&
//            (!Validator::regex('/^[\w-.]+$/')
//                ->validate($request->getParam('img_filename')) ||
//            !Validator::length(null, 255)
//                ->validate($request->getParam('img_filename')))
//        ) {
//            array_push($errors, _IMG_FILENAME . ' ' . _NOT . ' ' . _VALID);
//        }
//
//        if (Validator::notEmpty()
//                ->validate($request->getParam('maarch_module')) &&
//            !Validator::length(null, 255)
//                ->validate($request->getParam('maarch_module'))
//        ) {
//            array_push($errors, _MAARCH_MODULE . ' ' . _NOT . ' ' . _VALID);
//        }
//
//        if (Validator::notEmpty()
//                ->validate($request->getParam('can_be_searched')) &&
//            !Validator::contains('Y')
//                ->validate($request->getParam('can_be_searched')) &&
//            !Validator::contains('N')
//                ->validate($request->getParam('can_be_searched'))
//        ) {
//            array_push($errors, _CAN_BE_SEARCHED . ' ' . _NOT . ' ' . _VALID);
//        }
//
//        if (Validator::notEmpty()
//                ->validate($request->getParam('can_be_modified')) &&
//            !Validator::contains('Y')
//                ->validate($request->getParam('can_be_modified')) &&
//            !Validator::contains('N')
//                ->validate($request->getParam('can_be_modified'))
//        ) {
//            array_push($errors, _CAN_BE_MODIFIED . ' ' . _NOT . ' ' . _VALID);
//        }
//
//        return $errors;
//    }
}
