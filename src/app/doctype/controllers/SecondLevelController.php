<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   SecondLevelController
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Doctype\controllers;

use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Doctype\models\FirstLevelModel;
use Doctype\models\SecondLevelModel;
use Doctype\models\DoctypeModel;
use Core\Models\ServiceModel;
use Slim\Http\Request;
use Slim\Http\Response;

class SecondLevelController
{

    public function getById(Request $request, Response $response, $aArgs)
    {

        if (!Validator::intVal()->validate($aArgs['id']) || !Validator::notEmpty()->validate($aArgs['id'])) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'wrong format for id']);
        }

        $obj = SecondLevelModel::getById(['id' => $aArgs['id']]);

        if(!empty($obj)){
            if ($obj['enabled'] == 'Y') {
                $obj['enabled'] = true;
            } else {
                $obj['enabled'] = false;
            }
        }
  
        return $response->withJson($obj);
    }

    public function initSecondLevel(Request $request, Response $response, $aArgs)
    {
        $obj['secondLevel'] = FirstLevelModel::get(['select' => ['doctypes_first_level_id', 'doctypes_first_level_label']]);
        return $response->withJson($obj);
    }

    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_architecture', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $data = $this->manageValue($data);
        
        $errors = $this->control($data, 'create');
        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }
    
        $obj = SecondLevelModel::create($data);

        HistoryController::add([
            'tableName' => 'doctypes_second_level',
            'recordId'  => $obj['doctypes_second_level_id'],
            'eventType' => 'ADD',
            'eventId'   => 'subfolderadd',
            'info'      => _DOCTYPE_SECONDLEVEL_ADDED . ' : ' . $obj['doctypes_second_level_label']
        ]);

        return $response->withJson(
            [
            'secondLevel'  => $obj
            ]
        );
    }

    public function update(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_architecture', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data                             = $request->getParams();
        $data['doctypes_second_level_id'] = $aArgs['id'];

        $data   = $this->manageValue($data);
        $errors = $this->control($data, 'update');
      
        if (!empty($errors)) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => $errors]);
        }

        $obj = SecondLevelModel::update($data);

        HistoryController::add([
            'tableName' => 'doctypes_second_level',
            'recordId'  => $obj['doctypes_second_level_id'],
            'eventType' => 'UP',
            'eventId'   => 'subfolderup',
            'info'      => _DOCTYPE_SECONDLEVEL_UPDATED. ' : ' . $obj['doctypes_second_level_label']
        ]);

        return $response->withJson(
            [
            'secondLevel'  => $obj
            ]
        );
    }

    public function delete(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_architecture', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->validate($aArgs['id'])) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'Id is not a numeric']);
        }

        $secondLevel = SecondLevelModel::getById(['id' => $aArgs['id']]);
        SecondLevelModel::update(['doctypes_second_level_id' => $aArgs['id'], 'enabled' => 'N']);
        DoctypeModel::disabledSecondLevel(['doctypes_second_level_id' => $aArgs['id'], 'enabled' => 'N']);

        HistoryController::add([
            'tableName' => 'doctypes_second_level',
            'recordId'  => $aArgs['doctypes_second_level_id'],
            'eventType' => 'DEL',
            'eventId'   => 'subfolderdel',
            'info'      => _DOCTYPE_SECONDLEVEL_DELETED. ' : ' . $secondLevel['doctypes_second_level_label']
        ]);

        return $response->withJson(['secondLevel' => $secondLevel]);
    }

    protected function control($aArgs, $mode)
    {
        $errors = [];

        if ($mode == 'update') {
            if (!Validator::intVal()->validate($aArgs['doctypes_second_level_id'])) {
                $errors[] = 'Id is not a numeric';
            } else {
                $obj = SecondLevelModel::getById(['id' => $aArgs['doctypes_second_level_id']]);
            }
           
            if (empty($obj)) {
                $errors[] = 'Id ' .$aArgs['doctypes_second_level_id']. ' does not exists';
            }
        }
           
        if (!Validator::notEmpty()->validate($aArgs['doctypes_second_level_label']) ||
            !Validator::length(1, 255)->validate($aArgs['doctypes_second_level_label'])) {
            $errors[] = 'Invalid doctypes_second_level_label';
        }

        if (!Validator::notEmpty()->validate($aArgs['doctypes_first_level_id']) ||
            !Validator::intVal()->validate($aArgs['doctypes_first_level_id'])) {
            $errors[] = 'Invalid doctypes_first_level_id';
        }

        if (!Validator::notEmpty()->validate($aArgs['enabled']) || ($aArgs['enabled'] != 'Y' && $aArgs['enabled'] != 'N')) {
            $errors[]= 'Invalid history value';
        }

        return $errors;
    }

    protected function manageValue($request)
    {
        foreach ($request  as $key => $value) {
            if (in_array($key, ['enabled'])) {
                if (empty($value)) {
                    $request[$key] = 'N';
                } else {
                    $request[$key] = 'Y';
                }
            }
        }
        return $request;
    }
}
