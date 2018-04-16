<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Contact Controller
 *
 * @author dev@maarch.org
 */

namespace Contact\controllers;

use Contact\models\ContactModel;
use SrcCore\models\CoreConfigModel;
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

    public function getCommunicationByContactId(Request $request, Response $response, array $aArgs)
    {
        $contact = ContactModel::getCommunicationByContactId([
            'contactId' => $aArgs['contactId'],
        ]);

        return $response->withJson([$contact]);
    }

    public static function formatContactAddressAfnor(array $aArgs)
    {
        $formattedAddress = '';

        // Entete pour societe
        if ($aArgs['is_corporate_person'] == 'Y') {
            // Ligne 1
            $formattedAddress .= substr($aArgs['society'], 0, 38)."\n";

            // Ligne 2
            $formattedAddress .= self::controlLengthNameAfnor([
                                    'title' => $aArgs['title'],
                                    'fullName' => $aArgs['firstname'].' '.$aArgs['lastname'],
                                    'strMaxLength' => 38, ])."\n";

            // Ligne 3
            if (!empty($aArgs['address_complement'])) {
                $formattedAddress .= substr($aArgs['address_complement'], 0, 38)."\n";
            }
        } else {
            // Ligne 1
            $formattedAddress .= self::controlLengthNameAfnor([
                                    'title' => $aArgs['contact_title'],
                                    'fullName' => $aArgs['contact_firstname'].' '.$aArgs['contact_lastname'],
                                    'strMaxLength' => 38, ])."\n";

            // Ligne 2
            if (!empty($aArgs['occupancy'])) {
                $formattedAddress .= substr($aArgs['occupancy'], 0, 38)."\n";
            }

            // Ligne 3
            if (!empty($aArgs['address_complement'])) {
                $formattedAddress .= substr($aArgs['address_complement'], 0, 38)."\n";
            }
        }
        // Ligne 4
        $formattedAddress .= substr($aArgs['address_num'].' '.$aArgs['address_street'], 0, 38)."\n";

        // Ligne 5
        // $formattedAddress .= "\n";

        // Ligne 6
        $formattedAddress .= substr($aArgs['address_postal_code'].' '.$aArgs['address_town'], 0, 38);

        return $formattedAddress;
    }

    public static function controlLengthNameAfnor(array $aArgs)
    {
        $aCivility = self::getContactCivility();
        if (strlen($aArgs['title'].' '.$aArgs['fullName']) > $aArgs['strMaxLength']) {
            $aArgs['title'] = $aCivility[$aArgs['title']]['abbreviation'];
        } else {
            $aArgs['title'] = $aCivility[$aArgs['title']]['label'];
        }

        return substr($aArgs['title'].' '.$aArgs['fullName'], 0, $aArgs['strMaxLength']);
    }

    public static function getContactCivility()
    {
        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/entreprise.xml']);

        if ($loadedXml != false) {
            $result = $loadedXml->xpath('/ROOT/titles');
            $aCivility = [];
            foreach ($result as $title) {
                foreach ($title as $value) {
                    $aCivility[(string) $value->id] = [
                        'label' => (string) $value->label,
                        'abbreviation' => (string) $value->abbreviation,
                    ];
                }
            }
        }

        return $aCivility;
    }

    public function avaiblaibleReferential()
    {
        $banDirectory = 'referential/';
        $empty_folder = true;
        $empty_files = true;

        if (is_dir($banDirectory)) {
            $empty_folder = false;
        }
        if ($files = glob($banDirectory.'ban/indexes/'.'/*')) {
            $empty_files = false;
        }

        if (!$empty_folder && !$empty_files) {
            return true;
        } else {
            return false;
        }
    }
}
