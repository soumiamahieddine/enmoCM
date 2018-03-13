<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Contact Controller
 * @author dev@maarch.org
 */

namespace Contact\controllers;

use Contact\models\ContactModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class ContactController
{
    public function create(Request $request, Response $response)
    {
        $data = $request->getParams();

        $check = Validator::notEmpty()->validate($data['firstname']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['lastname']);
        $check = $check && Validator::intVal()->notEmpty()->validate($data['contactType']);
        $check = $check && Validator::intVal()->notEmpty()->validate($data['contactPurposeId']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['isCorporatePerson']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['email']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        if (empty($data['userId'])) {
            $data['userId'] = 'superadmin';
        }
        if (empty($data['entityId'])) {
            $data['entityId'] = 'SUPERADMIN';
        }
        if ($data['isCorporatePerson'] != 'Y') {
            $data['isCorporatePerson'] = 'N';
        } else {
            $data['addressFirstname'] = $data['firstname'];
            $data['addressLastname'] = $data['lastname'];
            unset($data['firstname'], $data['lastname']);
        }

        if (empty($data['isPrivate'])) {
            $data['isPrivate'] = 'N';
        } elseif ($data['isPrivate'] != 'N') {
            $data['isPrivate'] = 'Y';
        }

        $contact = ContactModel::getByEmail(['email' => $data['email'], 'select' => ['contacts_v2.contact_id', 'contact_addresses.id']]);
        if (!empty($contact['id'])) {
            return $response->withJson(['contactId' => $contact['contact_id'], 'addressId' => $contact['id']]);
        }

        $contactId = ContactModel::create($data);

        $data['contactId'] = $contactId;
        $addressId = ContactModel::createAddress($data);

        if (empty($contactId) || empty($addressId)) {
            return $response->withStatus(500)->withJson(['errors' => '[ContactController create] Contact creation has failed']);
        }

        return $response->withJson(['contactId' => $contactId, 'addressId' => $addressId]);
    }

    public function getCheckCommunication(Request $request, Response $response, $aArgs) {
        $data = $request->getParams();

        if (isset($data['contactId'])) {
            $contactId = $data['contactId'];
            $obj = ContactModel::getCommunicationByContactId([
                'contactId' => $contactId
            ]);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _ID . ' ' . _IS_EMPTY]);
        }

        $data = [
            $obj,
        ];

        return $response->withJson($data);
    }


}
