<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Contact Group Controller
 * @author dev@maarch.org
 */

namespace Contact\controllers;

use Contact\models\ContactGroupModel;
use Group\models\ServiceModel;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use User\models\UserModel;

class ContactGroupController
{
    public function get(Request $request, Response $response)
    {
        $contactsGroups = ContactGroupModel::get();

        foreach ($contactsGroups as $key => $contactsGroup) {
            $contactsGroups[$key]['labelledOwner'] = UserModel::getLabelledUserById(['id' => $contactsGroup['id']]);
        }

        return $response->withJson(['contactsGroups' => $contactsGroups]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        $contactsGroup = ContactGroupModel::getById(['id' => $aArgs['id']]);

        if (empty($contactsGroup)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contacts group not found']);
        }

        $contactsGroup['labelledOwner'] = UserModel::getLabelledUserById(['id' => $contactsGroup['id']]);

        return $response->withJson(['contactsGroup' => $contactsGroup]);
    }

    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_contacts', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['description']);
        $check = $check && Validator::boolType()->validate($data['public']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $data['public'] = $data['public'] ? 'true' : 'false';
        $data['owner'] = $GLOBALS['userId'];
        $data['entity_owner'] = UserModel::getPrimaryEntityByUserId(['userId' => $GLOBALS['userId']])['entity_id'];

        $id = ContactGroupModel::create($data);

        HistoryController::add([
            'tableName' => 'contacts_groups',
            'recordId'  => $id,
            'eventType' => 'ADD',
            'info'      => _CONTACTS_GROUP_ADDED . " : {$data['label']}",
            'moduleId'  => 'contact',
            'eventId'   => 'contactsGroupCreation',
        ]);

        return $response->withJson(['contactsGroup' => $id]);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_contacts', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['description']);
        $check = $check && Validator::boolType()->validate($data['public']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $data['id'] = $aArgs['id'];
        $data['public'] = $data['public'] ? 'true' : 'false';

        ContactGroupModel::update($data);

        HistoryController::add([
            'tableName' => 'contacts_groups',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _CONTACTS_GROUP_UPDATED . " : {$data['label']}",
            'moduleId'  => 'contact',
            'eventId'   => 'contactsGroupModification',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_contacts', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        ContactGroupModel::delete(['id' => $aArgs['id']]);

        HistoryController::add([
            'tableName' => 'contacts_groups',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'info'      => _CONTACTS_GROUP_DELETED . " : {$aArgs['id']}",
            'moduleId'  => 'contact',
            'eventId'   => 'contactsGroupSuppression',
        ]);

        $contactsGroups = ContactGroupModel::get();
        foreach ($contactsGroups as $key => $contactsGroup) {
            $contactsGroups[$key]['labelledOwner'] = UserModel::getLabelledUserById(['id' => $contactsGroup['id']]);
        }

        return $response->withJson(['contactsGroups' => $contactsGroups]);
    }
}
