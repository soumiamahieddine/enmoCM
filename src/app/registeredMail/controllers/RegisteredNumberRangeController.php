<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*/

/**
 * @brief Registered Number Range Controller
 * @author dev@maarch.org
 */

namespace RegisteredMail\controllers;

use Group\controllers\PrivilegeController;
use History\controllers\HistoryController;
use RegisteredMail\models\IssuingSiteModel;
use RegisteredMail\models\RegisteredNumberRangeModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class RegisteredNumberRangeController
{
    public function get(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_registered_mail', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $ranges = RegisteredNumberRangeModel::get();

        foreach ($ranges as $key => $range) {
            $fullness = $range['currentNumber'] - $range['range_start'];
            $rangeSize = $range['range_end'] - $range['range_start'];
            $fullness = ($fullness / $rangeSize) * 100;

            $site = IssuingSiteModel::getById(['id' => $range['site_id']]);
            $ranges[$key] = [
                'id'                    => $range['id'],
                'registeredMailType'    => $range['type'],
                'trackerNumber'         => $range['tracking_account_number'],
                'rangeStart'            => $range['range_start'],
                'rangeEnd'              => $range['range_end'],
                'creator'               => $range['creator'],
                'created'               => $range['created'],
                'siteId'                => $range['site_id'],
                'status'                => $range['status'],
                'customerAccountNumber' => $site['account_number'],
                'currentNumber'         => $range['currentNumber'],
                'fullness'              => $fullness
            ];
        }

        return $response->withJson(['ranges' => $ranges]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_registered_mail', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $range = RegisteredNumberRangeModel::getById(['id' => $args['id']]);

        if (empty($range)) {
            return $response->withStatus(400)->withJson(['errors' => 'Range not found']);
        }

        $site = IssuingSiteModel::getById(['id' => $range['site_id']]);

        $fullness = $range['currentNumber'] - $range['range_start'];
        $rangeSize = $range['range_end'] - $range['range_start'];
        $fullness = ($fullness / $rangeSize) * 100;

        $range = [
            'id'                    => $range['id'],
            'registeredMailType'    => $range['type'],
            'trackerNumber'         => $range['tracking_account_number'],
            'rangeStart'            => $range['range_start'],
            'rangeEnd'              => $range['range_end'],
            'creator'               => $range['creator'],
            'created'               => $range['created'],
            'siteId'                => $range['site_id'],
            'status'                => $range['status'],
            'customerAccountNumber' => $site['account_number'],
            'currentNumber'         => $range['currentNumber'],
            'fullness'              => $fullness
        ];

        return $response->withJson(['range' => $range]);
    }

    public function create(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_registered_mail', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['registeredMailType'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body registeredMailType is empty or not a string']);
        }
        if (!Validator::stringType()->notEmpty()->validate($body['trackerNumber'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body trackerNumber is empty or not a string']);
        }
        if (!Validator::intVal()->notEmpty()->validate($body['rangeStart'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body rangeStart is empty or not an integer']);
        }
        if (!Validator::intVal()->notEmpty()->validate($body['rangeEnd'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body rangeEnd is empty or not an integer']);
        }
        if (!Validator::intVal()->notEmpty()->validate($body['siteId'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body siteId is empty or not an integer']);
        }

        $site = IssuingSiteModel::getById(['id' => $body['siteId']]);
        if (empty($site)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body siteId does not exist']);
        }

        $id = RegisteredNumberRangeModel::create([
            'type'                  => $body['registeredMailType'],
            'trackingAccountNumber' => $body['trackerNumber'],
            'rangeStart'            => $body['rangeStart'],
            'rangeEnd'              => $body['rangeEnd'],
            'creator'               => $GLOBALS['id'],
            'siteId'                => $body['siteId'],
            'status'                => $body['status']
        ]);

        HistoryController::add([
            'tableName' => 'registered_number_range',
            'recordId'  => $id,
            'eventType' => 'ADD',
            'info'      => _REGISTERED_NUMBER_RANGE_CREATED . " : {$id}",
            'moduleId'  => 'registered_number_range',
            'eventId'   => 'registered_number_rangeCreation',
        ]);

        return $response->withJson(['id' => $id]);
    }

    public function update(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_registered_mail', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $range = RegisteredNumberRangeModel::getById(['id' => $args['id']]);
        if (empty($range)) {
            return $response->withStatus(400)->withJson(['errors' => 'Range not found']);
        }

        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['registeredMailType'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body registeredMailType is empty or not a string']);
        }
        if (!Validator::stringType()->notEmpty()->validate($body['trackerNumber'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body trackerNumber is empty or not a string']);
        }
        if (!Validator::intVal()->notEmpty()->validate($body['rangeStart'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body rangeStart is empty or not an integer']);
        }
        if (!Validator::intVal()->notEmpty()->validate($body['rangeEnd'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body rangeEnd is empty or not an integer']);
        }
        if (!Validator::intVal()->notEmpty()->validate($body['siteId'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body siteId is empty or not an integer']);
        }

        $site = IssuingSiteModel::getById(['id' => $body['siteId']]);
        if (empty($site)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body siteId does not exist']);
        }

        RegisteredNumberRangeModel::update([
            'set'   => [
                'type'                    => $body['registeredMailType'],
                'tracking_account_number' => $body['trackerNumber'],
                'range_start'             => $body['rangeStart'],
                'range_end'               => $body['rangeEnd'],
                'creator'                 => $GLOBALS['id'],
                'site_id'                 => $body['siteId'],
                'status'                  => $body['status']
            ],
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'tableName' => 'issuing_sites',
            'recordId'  => $args['id'],
            'eventType' => 'UP',
            'info'      => _REGISTERED_NUMBER_RANGE_UPDATED . " : {$args['id']}",
            'moduleId'  => 'issuing_sites',
            'eventId'   => 'issuingSitesModification',
        ]);

        return $response->withStatus(204);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_registered_mail', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $site = RegisteredNumberRangeModel::getById(['id' => $args['id']]);
        if (empty($site)) {
            return $response->withStatus(204);
        }

        RegisteredNumberRangeModel::delete([
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'tableName' => 'registered_number_range',
            'recordId'  => $args['id'],
            'eventType' => 'DEL',
            'info'      => _REGISTERED_NUMBER_RANGE_DELETED . " : {$args['id']}",
            'moduleId'  => 'registered_number_range',
            'eventId'   => 'registeredNumberRangeSuppression',
        ]);

        return $response->withStatus(204);
    }
}
