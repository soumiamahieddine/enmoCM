<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Contact Custom Field Controller
 * @author dev@maarch.org
 */

namespace Contact\controllers;

use Contact\models\ContactCustomFieldListModel;
use Contact\models\ContactCustomFieldModel;
use Contact\models\ContactParameterModel;
use Group\controllers\PrivilegeController;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class ContactCustomFieldController
{
    public function get(Request $request, Response $response)
    {
        $customFields = ContactCustomFieldListModel::get(['orderBy' => ['label']]);

        foreach ($customFields as $key => $customField) {
            $customFields[$key]['values'] = json_decode($customField['values'], true);
        }

        return $response->withJson(['customFields' => $customFields]);
    }

    public function create(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_contacts', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['type'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body type is empty or not a string']);
        } elseif (!empty($body['values']) && !Validator::arrayType()->notEmpty()->validate($body['values'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body values is not an array']);
        }

        $fields = ContactCustomFieldListModel::get(['select' => [1], 'where' => ['label = ?'], 'data' => [$body['label']]]);
        if (!empty($fields)) {
            return $response->withStatus(400)->withJson(['errors' => 'Custom field with this label already exists']);
        }

        $id = ContactCustomFieldListModel::create([
            'label'         => $body['label'],
            'type'          => $body['type'],
            'values'        => empty($body['values']) ? '[]' : json_encode($body['values'])
        ]);

        HistoryController::add([
            'tableName' => 'contacts_custom_fields_list',
            'recordId'  => $id,
            'eventType' => 'ADD',
            'info'      => _CONTACT_CUSTOMFIELDS_CREATION . " : {$body['label']}",
            'moduleId'  => 'contactCustomFieldList',
            'eventId'   => 'contactCustomFieldListCreation',
        ]);

        return $response->withJson(['id' => $id]);
    }

    public function update(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_contacts', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Param id is empty or not an integer']);
        }

        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string']);
        } elseif (!empty($body['values']) && !Validator::arrayType()->notEmpty()->validate($body['values'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body values is not an array']);
        }

        if (count(array_unique($body['values'])) < count($body['values'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Some values have the same name']);
        }

        $field = ContactCustomFieldListModel::getById(['select' => [1], 'id' => $args['id']]);
        if (empty($field)) {
            return $response->withStatus(400)->withJson(['errors' => 'Custom field not found']);
        }

        $fields = ContactCustomFieldListModel::get(['select' => [1], 'where' => ['label = ?', 'id != ?'], 'data' => [$body['label'], $args['id']]]);
        if (!empty($fields)) {
            return $response->withStatus(400)->withJson(['errors' => 'Custom field with this label already exists']);
        }

        ContactCustomFieldListModel::update([
            'set'   => [
                'label'  => $body['label'],
                'values' => empty($body['values']) ? '[]' : json_encode($body['values'])
            ],
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'tableName' => 'contacts_custom_fields_list',
            'recordId'  => $args['id'],
            'eventType' => 'UP',
            'info'      => _CONTACT_CUSTOMFIELDS_MODIFICATION . " : {$body['label']}",
            'moduleId'  => 'contactCustomFieldList',
            'eventId'   => 'contactCustomFieldListModification',
        ]);

        return $response->withStatus(204);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_contacts', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Param id is empty or not an integer']);
        }

        $field = ContactCustomFieldListModel::getById(['select' => ['label'], 'id' => $args['id']]);

        ContactCustomFieldModel::delete(['where' => ['custom_field_id = ?'], 'data' => [$args['id']]]);
        ContactParameterModel::delete(['where' => ['identifier = ?'], 'data' => ['contactCustomField_' . $args['id']]]);

        ContactCustomFieldListModel::delete([
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'tableName' => 'contacts_custom_fields_list',
            'recordId'  => $args['id'],
            'eventType' => 'DEL',
            'info'      => _CONTACT_CUSTOMFIELDS_SUPPRESSION . " : {$field['label']}",
            'moduleId'  => 'contactCustomFieldList',
            'eventId'   => 'contactCustomFieldListSuppression',
        ]);

        return $response->withStatus(204);
    }
}
