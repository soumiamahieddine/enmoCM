<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Auto Complete Controller
* @author dev@maarch.org
*/

namespace SrcCore\controllers;

use Core\Models\ServiceModel;
use Slim\Http\Request;
use Slim\Http\Response;
use Entity\models\EntityModel;
use Status\models\StatusModel;
use User\models\UserModel;

class AutoCompleteController
{
    public static function getUsers(Request $request, Response $response)
    {
        $excludedUsers = ['superadmin'];

        $users = UserModel::get([
            'select'    => ['user_id', 'firstname', 'lastname'],
            'where'     => ['enabled = ?', 'status != ?', 'user_id not in (?)'],
            'data'      => ['Y', 'DEL', $excludedUsers],
            'orderBy'   => ['lastname']
        ]);

        $data = [];
        foreach ($users as $key => $value) {
            $primaryEntity = UserModel::getPrimaryEntityByUserId(['userId' => $value['user_id']]);
            $data[] = [
                'type'          => 'user',
                'id'            => $value['user_id'],
                'idToDisplay'   => "{$value['firstname']} {$value['lastname']}",
                'otherInfo'     => $primaryEntity['entity_label']
            ];
        }

        return $response->withJson($data);
    }

    public static function getUsersForVisa(Request $request, Response $response)
    {
        $excludedUsers = ['superadmin'];

        $users = UserModel::get([
            'select'    => ['user_id', 'firstname', 'lastname'],
            'where'     => ['enabled = ?', 'status != ?', 'user_id not in (?)'],
            'data'      => ['Y', 'DEL', $excludedUsers],
            'orderBy'   => ['lastname']
        ]);

        $data = [];
        foreach ($users as $key => $value) {
            if (ServiceModel::hasService(['id' => 'visa_documents', 'userId' => $value['user_id'], 'location' => 'visa', 'type' => 'use'])) {
                $primaryEntity = UserModel::getPrimaryEntityByUserId(['userId' => $value['user_id']]);
                $data[] = [
                    'type'          => 'user',
                    'id'            => $value['user_id'],
                    'idToDisplay'   => "{$value['firstname']} {$value['lastname']}",
                    'otherInfo'     => $primaryEntity['entity_label']
                ];
            }
        }

        return $response->withJson($data);
    }

    public static function getEntities(Request $request, Response $response)
    {
        $entities = EntityModel::get([
            'select'    => ['entity_id', 'entity_label', 'short_label'],
            'where'     => ['enabled = ?'],
            'data'      => ['Y'],
            'orderBy'   => ['entity_label']
        ]);

        $data = [];
        foreach ($entities as $key => $value) {
            $data[] = [
                'type'          => 'entity',
                'id'            => $value['entity_id'],
                'idToDisplay'   => $value['entity_label'],
                'otherInfo'     => $value['short_label']
            ];
        }

        return $response->withJson($data);
    }

    public static function getStatuses(Request $request, Response $response)
    {
        $statuses = StatusModel::get([
            'select'    => ['id', 'label_status']
        ]);

        $data = [];
        foreach ($statuses as $key => $value) {
            $data[] = [
                'type'          => 'status',
                'id'            => $value['id'],
                'idToDisplay'   => $value['label_status'],
                'otherInfo'     => $value['label_status']
            ];
        }

        return $response->withJson($data);
    }
}
