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
use Contact\models\ContactModel;
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
        if (!ServiceModel::hasService(['id' => 'admin_contacts', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $contactsGroups = ContactGroupModel::get();

        foreach ($contactsGroups as $key => $contactsGroup) {
            $contactsGroups[$key]['labelledOwner'] = UserModel::getLabelledUserById(['id' => $contactsGroup['id']]);
        }

        return $response->withJson(['contactsGroups' => $contactsGroups]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_contacts', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $contactsGroup = ContactGroupModel::getById(['id' => $aArgs['id']]);
        if (empty($contactsGroup)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contacts group not found']);
        }

        $contactsGroup['labelledOwner'] = UserModel::getLabelledUserById(['id' => $contactsGroup['id']]);
        $contactsGroup['contacts'] = ContactGroupController::getFormattedListById(['id' => $aArgs['id']])['list'];

        return $response->withJson(['contactsGroup' => $contactsGroup]);
    }

    public function create(Request $request, Response $response)
    {
        $data = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['description']);
        $check = $check && Validator::boolType()->validate($data['public']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $user = UserModel::getByUserId(['select' => ['id'], 'userId' => $GLOBALS['userId']]);
        $existingGroup = ContactGroupModel::get(['select' => [1], 'where' => ['label = ?', 'owner = ?'], 'data' => [$data['label'], $user['id']]]);
        if (!empty($existingGroup)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group with this label already exists']);
        }

        $data['public'] = $data['public'] ? 'true' : 'false';
        $data['owner'] = $user['id'];
        $data['entity_owner'] = ($GLOBALS['userId'] == 'superadmin' ? 'superadmin' : UserModel::getPrimaryEntityByUserId(['userId' => $GLOBALS['userId']])['entity_id']);

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

    public function addContacts(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_contacts', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $check = Validator::arrayType()->notEmpty()->validate($data['contacts']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $contactsGroup = ContactGroupModel::getById(['select' => ['label'], 'id' => $aArgs['id']]);
        if (empty($contactsGroup)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contacts Group does not exist']);
        }

        $rawList = ContactGroupModel::getListById(['select' => ['contact_addresses_id'], 'id' => $aArgs['id']]);
        $list = [];
        foreach ($rawList as $rawListItem) {
            $list[] = $rawListItem['contact_addresses_id'];
        }

        foreach ($data['contacts'] as $addressId) {
            if (!in_array($addressId, $list)) {
                ContactGroupModel::addContact(['id' => $aArgs['id'], 'addressId' => $addressId]);
            }
        }

        HistoryController::add([
            'tableName' => 'contacts_groups_lists',
            'recordId'  => $aArgs['id'],
            'eventType' => 'ADD',
            'info'      => _CONTACTS_GROUP_LIST_ADDED . " : {$contactsGroup['label']}",
            'moduleId'  => 'contact',
            'eventId'   => 'contactsGroupListCreation',
        ]);

        $contactsGroup = ContactGroupModel::getById(['id' => $aArgs['id']]);
        $contactsGroup['labelledOwner'] = UserModel::getLabelledUserById(['id' => $contactsGroup['id']]);
        $contactsGroup['contacts'] = ContactGroupController::getFormattedListById(['id' => $aArgs['id']])['list'];

        return $response->withJson(['contactsGroup' => $contactsGroup]);
    }

    public function deleteContact(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_contacts', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $contactsGroup = ContactGroupModel::getById(['select' => ['label'], 'id' => $aArgs['id']]);
        if (empty($contactsGroup)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contacts Group does not exist']);
        }

        ContactGroupModel::deleteContact(['id' => $aArgs['id'], 'addressId' => $aArgs['addressId']]);

        HistoryController::add([
            'tableName' => 'contacts_groups_lists',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'info'      => _CONTACTS_GROUP_LIST_DELETED . " : {$contactsGroup['label']}",
            'moduleId'  => 'contact',
            'eventId'   => 'contactsGroupListSuppression',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public static function getFormattedListById(array $aArgs)
    {
        $list = ContactGroupModel::getListById(['select' => ['contact_addresses_id'], 'id' => $aArgs['id']]);

        $contacts = [];
        foreach ($list as $listItem) {
            $contact = ContactModel::getOnView([
                'select'    => [
                    'ca_id', 'firstname', 'lastname', 'contact_lastname', 'contact_firstname', 'society', 'address_num',
                    'address_street', 'address_town', 'address_postal_code', 'is_corporate_person'
                ],
                'where'     => ['ca_id = ?'],
                'data'      => [$listItem['contact_addresses_id']]
            ]);

            if (!empty($contact[0])) {
                $contact = $contact[0];
                if ($contact['is_corporate_person'] == 'Y') {
                    $contacts[] = [
                        'addressId' => $contact['ca_id'],
                        'contact'   => $contact['society'],
                        'address'   => "{$contact['firstname']} {$contact['lastname']}, {$contact['address_num']} {$contact['address_street']} {$contact['address_town']} {$contact['address_postal_code']}",
                    ];
                } else {
                    $contacts[] = [
                        'addressId' => $contact['ca_id'],
                        'contact'   => "{$contact['contact_firstname']} {$contact['contact_lastname']} {$contact['society']}",
                        'address'   => "{$contact['address_num']} {$contact['address_street']} {$contact['address_town']} {$contact['address_postal_code']}",
                    ];
                }
            }
        }

        return ['list' => $contacts];
    }
}
