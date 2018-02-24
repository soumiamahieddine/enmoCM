<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   DoctypeController
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Doctype\controllers;

use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Doctype\models\FirstLevelModel;
use Doctype\models\SecondLevelModel;
use Doctype\models\DoctypeModel;
use Doctype\models\DoctypeExtModel;
use Doctype\models\DoctypeIndexesModel;
use Doctype\models\TemplateDoctypeModel;
use Folder\models\FolderTypeModel;
use Core\Models\ServiceModel;
use Template\models\TemplateModel;
use Slim\Http\Request;
use Slim\Http\Response;
use Resource\models\ResModel;

class DoctypeController
{
    public function getById(Request $request, Response $response, $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['id']) || !Validator::notEmpty()->validate($aArgs['id'])) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'wrong format for id']);
        }

        $obj['doctype'] = DoctypeModel::getById(['id' => $aArgs['id']]);

        if (!empty($obj['doctype'])) {
            if ($obj['doctype']['enabled'] == 'Y') {
                $obj['doctype']['enabled'] = true;
            } else {
                $obj['doctype']['enabled'] = false;
            }
        }
  
        $doctypeExt                    = DoctypeExtModel::getById(['id' => $obj['doctype']['type_id']]);
        $template                      = TemplateDoctypeModel::getById(["id" => $obj['doctype']['type_id']]);
        $obj['doctype']                = array_merge($obj['doctype'], $doctypeExt, $template);
        $obj['secondLevel']            = SecondLevelModel::get(['select' => ['doctypes_second_level_id', 'doctypes_second_level_label']]);
        $obj['processModes']           = DoctypeModel::getProcessMode();
        $obj['models']                 = TemplateModel::getByTarget(['select' => ['template_id', 'template_label', 'template_comment'], 'template_target' => 'doctypes']);
        $obj['indexes']                = DoctypeIndexesModel::getAllIndexes();
        $obj['indexesSelected']        = DoctypeIndexesModel::getById(['id' => $obj['doctype']['type_id']]);

        return $response->withJson($obj);
    }

    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_architecture', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        
        $errors = $this->control($data, 'create');
        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }
    
        $doctypeId = DoctypeModel::create([
            'coll_id'                     => 'letterbox_coll',
            'description'                 => $data['description'],
            'doctypes_first_level_id'     => $data['doctypes_first_level_id'],
            'doctypes_second_level_id'    => $data['doctypes_second_level_id'],
            'retention_final_disposition' => $data['retention_final_disposition'],
            'retention_rule'              => $data['retention_rule'],
            'duration_current_use'        => $data['duration_current_use']
        ]);

        DoctypeExtModel::create([
            "type_id"       => $doctypeId,
            "process_delay" => $data['process_delay'],
            "delay1"        => $data['delay1'],
            "delay2"        => $data['delay2'],
            "process_mode"  => $data['process_mode'],
        ]);

        TemplateDoctypeModel::create([
            "template_id"  => $data["template_id"],
            "type_id"      => $doctypeId,
            "is_generated" => $data["is_generated"]
        ]);

        if (!empty($data['indexes'])) {
            foreach ($data['indexes'] as $fieldName => $mandatory) {
                DoctypeIndexesModel::create([
                    "type_id"    => $doctypeId,
                    "coll_id"    => 'letterbox_coll',
                    "field_name" => $fieldName,
                    "mandatory"  => $mandatory
                ]);
            }
        }

        HistoryController::add([
            'tableName' => 'doctypes',
            'recordId'  => $doctypeId,
            'eventType' => 'ADD',
            'eventId'   => 'typesadd',
            'info'      => _DOCTYPE_ADDED . ' : ' . $data['description']
        ]);

        return $response->withJson(
            [
            'doctype'  => $doctypeId
            ]
        );
    }

    public function update(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_architecture', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data            = $request->getParams();
        $data['type_id'] = $aArgs['id'];
        
        $errors = $this->control($data, 'update');
        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }
    
        DoctypeModel::update([
            'type_id'                     => $data['type_id'],
            'coll_id'                     => 'letterbox_coll',
            'description'                 => $data['description'],
            'doctypes_first_level_id'     => $data['doctypes_first_level_id'],
            'doctypes_second_level_id'    => $data['doctypes_second_level_id'],
            'retention_final_disposition' => $data['retention_final_disposition'],
            'retention_rule'              => $data['retention_rule'],
            'duration_current_use'        => $data['duration_current_use']
        ]);

        DoctypeExtModel::update([
            "type_id"       => $data['type_id'],
            "process_delay" => $data['process_delay'],
            "delay1"        => $data['delay1'],
            "delay2"        => $data['delay2'],
            "process_mode"  => $data['process_mode'],
        ]);

        TemplateDoctypeModel::update([
            "template_id"  => $data["template_id"],
            "type_id"      => $data['type_id'],
            "is_generated" => $data["is_generated"]
        ]);

        DoctypeIndexesModel::delete(["type_id" => $data['type_id']]);

        if (!empty($data['indexes'])) {
            foreach ($data['indexes'] as $fieldName => $mandatory) {
                DoctypeIndexesModel::create([
                    "type_id"    => $data['type_id'],
                    "coll_id"    => 'letterbox_coll',
                    "field_name" => $fieldName,
                    "mandatory"  => $mandatory
                ]);
            }
        }

        HistoryController::add([
            'tableName' => 'doctypes',
            'recordId'  => $data['type_id'],
            'eventType' => 'UP',
            'eventId'   => 'typesadd',
            'info'      => _DOCTYPE_UPDATED . ' : ' . $data['description']
        ]);

        return $response->withJson(
            [
            'doctype'  => $data['type_id']
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

        if (empty(ResModel::get([
            'select' => ['res_id'],
            'where'  => ['type_id = ?'],
            'data'   => [$aArgs['id']]]))
        ) {
            DoctypeController::deleteAllDoctypeData(['type_id' => $aArgs['id']]);
            $docTypes = '';
            $deleted  = true;
        } else {
            $docTypes = DoctypeModel::get();
            $deleted  = false;
        }

        return $response->withJson([
            'deleted'  => $deleted,
            'doctypes' => $docTypes,
        ]);
    }

    public function deleteRedirect(Request $request, Response $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_architecture', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data            = $request->getParams();
        $data['type_id'] = $aArgs['id'];

        if (!Validator::intVal()->validate($data['type_id'])) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'Id is not a numeric']);
        }

        if (!Validator::intVal()->validate($data['new_type_id']) || !Validator::notEmpty()->validate($data['new_type_id'])) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'wrong format for new_type_id']);
        }

        if (empty(DoctypeModel::getById(['id' => $data['new_type_id']]))) {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'new_type_id does not exists']);
        }

        ResModel::update([
            'set'   => ['type_id' => $data['new_type_id']],
            'where' => ['type_id = ?'],
            'data'  => [$data['type_id']]
        ]);
        DoctypeController::deleteAllDoctypeData(['type_id' => $data['type_id']]);

        return $response->withJson(
            [
            'doctype'  => true
            ]
        );
    }

    protected function deleteAllDoctypeData(array $aArgs = [])
    {
        $doctypeInfo = DoctypeModel::getById(['id' => $aArgs['type_id']]);
        DoctypeModel::delete(['type_id' => $aArgs['type_id']]);
        DoctypeExtModel::delete(["type_id" => $aArgs['type_id']]);
        TemplateDoctypeModel::delete(["type_id" => $aArgs['type_id']]);
        DoctypeIndexesModel::delete(["type_id" => $aArgs['type_id']]);

        HistoryController::add([
            'tableName' => 'doctypes',
            'recordId'  => $doctypeInfo['type_id'],
            'eventType' => 'DEL',
            'eventId'   => 'typesdel',
            'info'      => _DOCTYPE_DELETED. ' : ' . $doctypeInfo['description']
        ]);
    }

    protected function control($aArgs, $mode)
    {
        $errors = [];

        if ($mode == 'update') {
            if (!Validator::intVal()->validate($aArgs['type_id'])) {
                $errors[] = 'type_id is not a numeric';
            } else {
                $obj = DoctypeModel::getById(['id' => $aArgs['type_id']]);
            }
           
            if (empty($obj)) {
                $errors[] = 'Id ' .$aArgs['type_id']. ' does not exists';
            }
        }
           
        if (!Validator::notEmpty()->validate($aArgs['description']) ||
            !Validator::length(1, 255)->validate($aArgs['description'])) {
            $errors[] = 'Invalid description';
        }

        if (!Validator::notEmpty()->validate($aArgs['doctypes_first_level_id']) ||
            !Validator::intVal()->validate($aArgs['doctypes_first_level_id'])) {
            $errors[] = 'Invalid doctypes_first_level_id';
        }

        if (!Validator::notEmpty()->validate($aArgs['doctypes_second_level_id']) ||
            !Validator::intVal()->validate($aArgs['doctypes_second_level_id'])) {
            $errors[]= 'Invalid doctypes_second_level_id value';
        }
        if (!Validator::notEmpty()->validate($aArgs['process_delay']) ||
            !Validator::intVal()->validate($aArgs['process_delay'])) {
            $errors[]= 'Invalid process_delay value';
        }
        if (!Validator::notEmpty()->validate($aArgs['delay1']) ||
            !Validator::intVal()->validate($aArgs['delay1'])) {
            $errors[]= 'Invalid delay1 value';
        }
        if (!Validator::notEmpty()->validate($aArgs['delay2']) ||
            !Validator::intVal()->validate($aArgs['delay2'])) {
            $errors[]= 'Invalid delay2 value';
        }

        return $errors;
    }
}
