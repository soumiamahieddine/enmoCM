<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Contact Controller
 * @author dev@maarch.org
 */

namespace Contact\controllers;

use Contact\models\ContactCustomFieldListModel;
use Contact\models\ContactCustomFieldModel;
use Contact\models\ContactFillingModel;
use Contact\models\ContactModel;
use Entity\models\EntityModel;
use Group\controllers\PrivilegeController;
use History\controllers\HistoryController;
use Resource\controllers\ResController;
use Resource\models\ResModel;
use Resource\models\ResourceContactModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\TextFormatModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class ContactController
{
    public function get(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_contacts', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson(['contacts' => ContactModel::get(['select' => ['id', 'firstname', 'lastname', 'company']])]);
    }

    public function create(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'create_contacts', 'userId' => $GLOBALS['id']])
            && !PrivilegeController::hasPrivilege(['privilegeId' => 'admin_contacts', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $body = $request->getParsedBody();

        $control = ContactController::controlContact(['body' => $body]);
        if (!empty($control['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
        }

        if (!empty($body['email'])) {
            $contact = ContactModel::get(['select' => ['id'], 'where' => ['email = ?'], 'data' => [$body['email']]]);
            if (!empty($contact[0]['id'])) {
                return $response->withJson(['id' => $contact[0]['id']]);
            }
        }

        if (!empty($body['communicationMeans'])) {
            if (filter_var($body['communicationMeans'], FILTER_VALIDATE_EMAIL)) {
                $body['communicationMeans'] = ['email' => $body['communicationMeans']];
            } elseif (filter_var($body['communicationMeans'], FILTER_VALIDATE_URL)) {
                $body['communicationMeans'] = ['url' => $body['communicationMeans']];
            }
        }
        if (!empty($body['externalId']) && is_array($body['externalId'])) {
            $externalId = json_encode($body['externalId']);
        } else {
            $externalId = '{}';
        }

        $id = ContactModel::create([
            'civility'              => $body['civility'] ?? null,
            'firstname'             => $body['firstname'] ?? null,
            'lastname'              => $body['lastname'] ?? null,
            'company'               => $body['company'] ?? null,
            'department'            => $body['department'] ?? null,
            'function'              => $body['function'] ?? null,
            'address_number'        => $body['addressNumber'] ?? null,
            'address_street'        => $body['addressStreet'] ?? null,
            'address_postcode'      => $body['addressPostcode'] ?? null,
            'address_town'          => $body['addressTown'] ?? null,
            'address_country'       => $body['addressCountry'] ?? null,
            'email'                 => $body['email'] ?? null,
            'phone'                 => $body['phone'] ?? null,
            'communication_means'   => !empty($body['communicationMeans']) ? json_encode($body['communicationMeans']) : null,
            'notes'                 => $body['notes'] ?? null,
            'creator'               => $GLOBALS['id'],
            'enabled'               => 'true',
            'external_id'           => $externalId
        ]);

        $historyInfoContact = '';
        if (!empty($body['firstname']) || !empty($body['lastname'])) {
            $historyInfoContact .= $body['firstname'] . ' ' . $body['lastname'];
        }
        if (!empty($historyInfoContact) && !empty($body['company'])) {
            $historyInfoContact .= ' (' . $body['company'] . ')';
        } else {
            $historyInfoContact .= $body['company'];
        }

        HistoryController::add([
            'tableName' => 'contacts',
            'recordId'  => $id,
            'eventType' => 'ADD',
            'info'      => _CONTACT_CREATION . " : " . trim($historyInfoContact),
            'moduleId'  => 'contact',
            'eventId'   => 'contactCreation',
        ]);

        ContactController::createAdjacentData(['body' => $body, 'id' => $id]);

        return $response->withJson(['id' => $id]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $rawContact = ContactModel::getById(['id' => $args['id'], 'select' => ['*']]);
        if (empty($rawContact)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contact does not exist']);
        }

        $contact = [
            'id'                    => $rawContact['id'],
            'civility'              => $rawContact['civility'],
            'firstname'             => $rawContact['firstname'],
            'lastname'              => $rawContact['lastname'],
            'company'               => $rawContact['company'],
            'department'            => $rawContact['department'],
            'function'              => $rawContact['function'],
            'addressNumber'         => $rawContact['address_number'],
            'addressStreet'         => $rawContact['address_street'],
            'addressPostcode'       => $rawContact['address_postcode'],
            'addressTown'           => $rawContact['address_town'],
            'addressCountry'        => $rawContact['address_country'],
            'email'                 => $rawContact['email'],
            'phone'                 => $rawContact['phone'],
            'communicationMeans'    => !empty($rawContact['communication_means']) ? json_decode($rawContact['communication_means']) : null,
            'notes'                 => $rawContact['notes'],
            'creator'               => $rawContact['creator'],
            'creatorLabel'          => UserModel::getLabelledUserById(['id' => $rawContact['creator']]),
            'enabled'               => $rawContact['enabled'],
            'creationDate'          => $rawContact['creation_date'],
            'modificationDate'      => $rawContact['modification_date'],
            'externalId'            => json_decode($rawContact['external_id'], true)
        ];

        return $response->withJson($contact);
    }

    public function update(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'update_contacts', 'userId' => $GLOBALS['id']])
            && !PrivilegeController::hasPrivilege(['privilegeId' => 'admin_contacts', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $body = $request->getParsedBody();

        $control = ContactController::controlContact(['body' => $body]);
        if (!empty($control['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
        }

        $contact = ContactModel::getById(['id' => $args['id'], 'select' => [1]]);
        if (empty($contact)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contact does not exist']);
        }

        if (!empty($body['communicationMeans'])) {
            if (filter_var($body['communicationMeans'], FILTER_VALIDATE_EMAIL)) {
                $body['communicationMeans'] = ['email' => $body['communicationMeans']];
            } elseif (filter_var($body['communicationMeans'], FILTER_VALIDATE_URL)) {
                $body['communicationMeans'] = ['url' => $body['communicationMeans']];
            }
        }
        if (!empty($body['externalId']) && is_array($body['externalId'])) {
            $externalId = json_encode($body['externalId']);
        } else {
            $externalId = '{}';
        }

        ContactModel::update([
            'set'   => [
                    'civility'              => $body['civility'] ?? null,
                    'firstname'             => $body['firstname'] ?? null,
                    'lastname'              => $body['lastname'] ?? null,
                    'company'               => $body['company'] ?? null,
                    'department'            => $body['department'] ?? null,
                    'function'              => $body['function'] ?? null,
                    'address_number'        => $body['addressNumber'] ?? null,
                    'address_street'        => $body['addressStreet'] ?? null,
                    'address_postcode'      => $body['addressPostcode'] ?? null,
                    'address_town'          => $body['addressTown'] ?? null,
                    'address_country'       => $body['addressCountry'] ?? null,
                    'email'                 => $body['email'] ?? null,
                    'phone'                 => $body['phone'] ?? null,
                    'communication_means'   => !empty($body['communicationMeans']) ? json_encode($body['communicationMeans']) : null,
                    'notes'                 => $body['notes'] ?? null,
                    'modification_date'     => 'CURRENT_TIMESTAMP',
                    'external_id'           => $externalId
                ],
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        $historyInfoContact = '';
        if (!empty($body['firstname']) || !empty($body['lastname'])) {
            $historyInfoContact .= $body['firstname'] . ' ' . $body['lastname'];
        }
        if (!empty($historyInfoContact) && !empty($body['company'])) {
            $historyInfoContact .= ' (' . $body['company'] . ')';
        } else {
            $historyInfoContact .= $body['company'];
        }

        HistoryController::add([
            'tableName' => 'contacts',
            'recordId'  => $args['id'],
            'eventType' => 'UP',
            'info'      => _CONTACT_MODIFICATION . " : " . trim($historyInfoContact),
            'moduleId'  => 'contact',
            'eventId'   => 'contactModification',
        ]);

        return $response->withStatus(204);
    }

    public function updateActivation(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_contacts', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $contact = ContactModel::getById(['id' => $args['id'], 'select' => [1]]);
        if (empty($contact)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contact does not exist']);
        }

        $body = $request->getParsedBody();

        ContactModel::update([
            'set'   => ['enabled' => empty($body['enabled']) ? 'false' : 'true'],
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        return $response->withStatus(204);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_contacts', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $contact = ContactModel::getById(['id' => $args['id'], 'select' => ['lastname', 'firstname', 'company']]);
        if (empty($contact)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contact does not exist']);
        }

        ContactModel::delete([
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        $historyInfoContact = '';
        if (!empty($contact[0]['firstname']) || !empty($contact[0]['lastname'])) {
            $historyInfoContact .= $contact[0]['firstname'] . ' ' . $contact[0]['lastname'];
        }
        if (!empty($historyInfoContact) && !empty($contact[0]['company'])) {
            $historyInfoContact .= ' (' . $contact[0]['company'] . ')';
        } else {
            $historyInfoContact .= $contact[0]['company'];
        }

        HistoryController::add([
            'tableName' => 'contacts',
            'recordId'  => $args['id'],
            'eventType' => 'DEL',
            'info'      => _CONTACT_SUPPRESSION . " : " . trim($historyInfoContact),
            'moduleId'  => 'contact',
            'eventId'   => 'contactSuppression',
        ]);

        return $response->withStatus(204);
    }

    public function getFilling(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_contacts', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $contactsFilling = ContactFillingModel::get();
        $contactsFilling['rating_columns'] = json_decode($contactsFilling['rating_columns']);

        return $response->withJson(['contactsFilling' => $contactsFilling]);
    }

    public function updateFilling(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_contacts', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $check = Validator::boolType()->validate($data['enable']);
        $check = $check && Validator::arrayType()->validate($data['rating_columns']);
        $check = $check && Validator::intVal()->notEmpty()->validate($data['first_threshold']) && $data['first_threshold'] > 0 && $data['first_threshold'] < 99;
        $check = $check && Validator::intVal()->notEmpty()->validate($data['second_threshold']) && $data['second_threshold'] > 1 && $data['second_threshold'] < 100;
        $check = $check && $data['first_threshold'] < $data['second_threshold'];
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $data['rating_columns'] = json_encode($data['rating_columns']);

        ContactFillingModel::update($data);

        return $response->withJson(['success' => 'success']);
    }

    public function getByResId(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['resId']) || !ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $resource = ResModel::getById(['select' => ['res_id'], 'resId' => $args['resId']]);

        if (empty($resource)) {
            return $response->withStatus(404)->withJson(['errors' => 'Document does not exist']);
        }

        $queryParams = $request->getQueryParams();

        $contacts = [];
        if ($queryParams['type'] == 'senders') {
            $contacts = ContactController::getParsedContacts(['resId' => $resource['res_id'], 'mode' => 'sender']);
        } elseif ($queryParams['type'] == 'recipients') {
            $contacts = ContactController::getParsedContacts(['resId' => $resource['res_id'], 'mode' => 'recipient']);
        }

        return $response->withJson(['contacts' => $contacts]);
    }

    public static function getLightFormattedContact(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query params id is not an integer']);
        }

        if ($args['type'] == 'contact') {
            $contact = ContactModel::getById([
                'select'    => [
                    'firstname', 'lastname', 'company', 'address_number as "addressNumber"', 'address_street as "addressStreet"',
                    'address_postcode as "addressPostcode"', 'address_town as "addressTown"', 'address_country as "addressCountry"'],
                'id'        => $args['id']
            ]);
        } elseif ($args['type'] == 'user') {
            $contact = UserModel::getById(['id' => $args['id'], 'select' => ['firstname', 'lastname']]);
        } elseif ($args['type'] == 'entity') {
            $contact = EntityModel::getById(['id' => $args['id'], 'select' => ['entity_label as label']]);
        }

        if (empty($contact)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contact does not exist']);
        }

        return $response->withJson(['contact' => $contact]);
    }

    public static function getFillingRate(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['contact']);
        ValidatorModel::arrayType($aArgs, ['contact']);

        $contactsFilling = ContactFillingModel::get();
        $contactsFilling['rating_columns'] = json_decode($contactsFilling['rating_columns']);

        if ($contactsFilling['enable'] && !empty($contactsFilling['rating_columns'])) {
            if ($aArgs['contact']['is_corporate_person'] == 'N') {
                foreach ($contactsFilling['rating_columns'] as $key => $value) {
                    if (in_array($value, ['firstname', 'lastname', 'title', 'function'])) {
                        $contactsFilling['rating_columns'][$key] = 'contact_' . $value;
                    }
                }
            }
            $percent = 0;
            foreach ($contactsFilling['rating_columns'] as $ratingColumn) {
                if (!empty($aArgs['contact'][$ratingColumn])) {
                    $percent++;
                }
            }
            $percent = $percent * 100 / count($contactsFilling['rating_columns']);
            if ($percent <= $contactsFilling['first_threshold']) {
                $color = '#ff9e9e';
            } elseif ($percent <= $contactsFilling['second_threshold']) {
                $color = '#f6cd81';
            } else {
                $color = '#ccffcc';
            }

            return ['rate' => $percent, 'color' => $color];
        }

        return [];
    }

    public static function formatContactAddressAfnor(array $aArgs)
    {
        $formattedAddress = '';

        // Entete pour societe
        if ($aArgs['is_corporate_person'] == 'Y') {
            // Ligne 1
            $formattedAddress .= substr($aArgs['society'], 0, 38)."\n";

            // Ligne 2
            if (!empty($aArgs['title']) || !empty($aArgs['firstname']) || !empty($aArgs['lastname'])) {
                $formattedAddress .= ContactController::controlLengthNameAfnor([
                    'title' => $aArgs['title'],
                    'fullName' => $aArgs['firstname'].' '.$aArgs['lastname'],
                    'strMaxLength' => 38, ])."\n";
            }

            // Ligne 3
            if (!empty($aArgs['address_complement'])) {
                $formattedAddress .= substr($aArgs['address_complement'], 0, 38)."\n";
            }
        } else {
            // Ligne 1
            $formattedAddress .= ContactController::controlLengthNameAfnor([
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
        if (!empty($aArgs['address_num'])) {
            $aArgs['address_num'] = TextFormatModel::normalize(['string' => $aArgs['address_num']]);
            $aArgs['address_num'] = preg_replace('/[^\w]/s', ' ', $aArgs['address_num']);
            $aArgs['address_num'] = strtoupper($aArgs['address_num']);
        }

        if (!empty($aArgs['address_street'])) {
            $aArgs['address_street'] = TextFormatModel::normalize(['string' => $aArgs['address_street']]);
            $aArgs['address_street'] = preg_replace('/[^\w]/s', ' ', $aArgs['address_street']);
            $aArgs['address_street'] = strtoupper($aArgs['address_street']);
        }

        $formattedAddress .= substr($aArgs['address_num'].' '.$aArgs['address_street'], 0, 38)."\n";

        // Ligne 5
        // $formattedAddress .= "\n";

        // Ligne 6
        $aArgs['address_postal_code'] = strtoupper($aArgs['address_postal_code']);
        $aArgs['address_town'] = strtoupper($aArgs['address_town']);
        $formattedAddress .= substr($aArgs['address_postal_code'].' '.$aArgs['address_town'], 0, 38);

        return $formattedAddress;
    }

    public static function getContactAfnor(array $args)
    {
        $afnorAddress = ['Afnor',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        if (!empty($args['company'])) {
            // Ligne 1
            $afnorAddress[1] = substr($args['company'], 0, 38);
        }

        // Ligne 2
        if (!empty($args['civility']) || !empty($args['firstname']) || !empty($args['lastname'])) {
            $afnorAddress[2] = ContactController::controlLengthNameAfnor([
                'civility'      => $args['civility'],
                'fullName'      => $args['firstname'].' '.$args['lastname'],
                'strMaxLength'  => 38
            ]);
        }

        // Ligne 3
        if (!empty($args['address_additional1'])) {
            $afnorAddress[3] = substr($args['address_additional1'], 0, 38);
        }

        // Ligne 4
        if (!empty($args['address_number'])) {
            $args['address_number'] = TextFormatModel::normalize(['string' => $args['address_number']]);
            $args['address_number'] = preg_replace('/[^\w]/s', ' ', $args['address_number']);
            $args['address_number'] = strtoupper($args['address_number']);
        }
        if (!empty($args['address_street'])) {
            $args['address_street'] = TextFormatModel::normalize(['string' => $args['address_street']]);
            $args['address_street'] = preg_replace('/[^\w]/s', ' ', $args['address_street']);
            $args['address_street'] = strtoupper($args['address_street']);
        }
        $afnorAddress[4] = substr($args['address_number'].' '.$args['address_street'], 0, 38);

        // Ligne 5
        $afnorAddress[5] = '';

        // Ligne 6
        $args['address_postcode'] = strtoupper($args['address_postcode']);
        $args['address_town'] = strtoupper($args['address_town']);
        $afnorAddress[6] = substr($args['address_postcode'].' '.$args['address_town'], 0, 38);

        return $afnorAddress;
    }

    public static function controlLengthNameAfnor(array $args)
    {
        $aCivility = ContactModel::getCivilities();
        if (strlen($args['civility'].' '.$args['fullName']) > $args['strMaxLength']) {
            $args['civility'] = $aCivility[$args['civility']]['abbreviation'];
        } else {
            $args['civility'] = $aCivility[$args['civility']]['label'];
        }

        return substr($args['civility'].' '.$args['fullName'], 0, $args['strMaxLength']);
    }

    public function availableReferential()
    {
        $customId = CoreConfigModel::getCustomId();

        $referentialDirectory = 'referential/ban/indexes';
        if (is_dir("custom/{$customId}/".$referentialDirectory)) {
            $customFilesDepartments = scandir("custom/{$customId}/".$referentialDirectory);
        }
        if (is_dir($referentialDirectory)) {
            $filesDepartments = scandir($referentialDirectory);
        }

        $departments = [];
        if (!empty($customFilesDepartments)) {
            foreach ($customFilesDepartments as $value) {
                if ($value != '.' && $value != '..' && is_writable("custom/{$customId}/".$referentialDirectory.'/'.$value)) {
                    $departments[] = $value;
                }
            }
        }
        if (!empty($filesDepartments)) {
            foreach ($filesDepartments as $value) {
                if ($value != '.' && $value != '..' && !in_array($value, $departments) && is_writable($referentialDirectory.'/'.$value)) {
                    $departments[] = $value;
                }
            }
        }

        if (!empty($departments)) {
            sort($departments, SORT_NUMERIC);

            return $departments;
        } else {
            return false;
        }
    }

    public static function getParsedContacts(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId', 'mode']);
        ValidatorModel::intVal($args, ['resId']);
        ValidatorModel::stringType($args, ['mode']);

        $contacts = [];

        $resourceContacts = ResourceContactModel::get([
            'where'     => ['res_id = ?', 'mode = ?'],
            'data'      => [$args['resId'], $args['mode']]
        ]);

        foreach ($resourceContacts as $resourceContact) {
            $contact = [];
            if ($resourceContact['type'] == 'contact') {
                $contactRaw = ContactModel::getById([
                    'select'    => ['*'],
                    'id'        => $resourceContact['item_id']
                ]);

                $contact = [
                    'mode'      => 'physical', // ??
                    'firstname' => $contactRaw['firstname'] ?? '',
                    'lastname'  => $contactRaw['lastname'] ?? '',
                    'email'     => $contactRaw['email'] ?? '',
                    'phone'     => $contactRaw['phone'] ?? '',
                    'company'   => $contactRaw['company'] ?? '',
                    'function'  => $contactRaw['function'] ?? '',
                    'number'       => $contactRaw['address_number'] ?? '',
                    'street'    => $contactRaw['address_street'] ?? '',
                    'complement'=> '', // <-- ??
                    'town'      => $contactRaw['address_town'] ?? '',
                    'postalCode'=> $contactRaw['address_postcode'] ?? '',
                    'country'   => $contactRaw['address_country'] ?? '',
                    'otherData' => '', // <-- ??
                    'website'   => '', // ??
                    'occupancy' => '', // <-- ??
                    'department' => $contactRaw['department'] ?? ''
                ];

                $filling = ContactController::getFillingRate(['contact' => $contact]);

                $contact['filling'] = $filling['color'];
            } elseif ($resourceContact['type'] == 'user') {
                $user = UserModel::getById(['id' => $resourceContact['item_id']]);

                $phone = '';
                if (!empty($phone) && ($user['id'] == $GLOBALS['id']
                        || PrivilegeController::hasPrivilege(['privilegeId' => 'view_personal_data', 'userId' => $GLOBALS['id']]))) {
                    $phone = $user['phone'];
                }

                $primaryEntity = UserModel::getPrimaryEntityById(['select' => ['entity_label'], 'id' => $user['id']]);

                $userEntities = UserModel::getNonPrimaryEntitiesById(['id' => $user['id']]);
                $userEntities = array_column($userEntities, 'entity_label');

                $nonPrimaryEntities = implode(', ', $userEntities);

                $contact = [
                    'mode'      => 'internal',
                    'firstname' => $user['firstname'],
                    'lastname'  => $user['lastname'],
                    'email'     => $user['mail'],
                    'phone'     => $phone,
                    'company'   => '',
                    'function'  => '',
                    'number'       => '',
                    'street'    => '',
                    'complement'=> '',
                    'town'      => '',
                    'postalCode'=> '',
                    'country'   => '',
                    'otherData' => '',
                    'website'   => '',
                    'occupancy' => $nonPrimaryEntities,
                    'department' => $primaryEntity['entity_label']
                ];
            } elseif ($resourceContact['type'] == 'entity') {
                $entity = EntityModel::getById(['id' => $resourceContact['item_id'], 'select' => ['entity_label', 'email']]);

                $contact = [
                    'mode'      => 'entity',
                    'firstname' => '',
                    'lastname'  => $entity['entity_label'],
                    'email'     => $entity['email'],
                    'phone'     => '',
                    'company'   => '',
                    'function'  => '',
                    'number'       => '',
                    'street'    => '',
                    'complement'=> '',
                    'town'      => '',
                    'postalCode'=> '',
                    'country'   => '',
                    'otherData' => '',
                    'website'   => '',
                    'occupancy' => '',
                    'department' => ''
                ];
            }

            $contacts[] = $contact;
        }

        return $contacts;
    }

    public static function getFormattedContacts(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId', 'mode']);
        ValidatorModel::intVal($args, ['resId']);
        ValidatorModel::stringType($args, ['mode']);
        ValidatorModel::boolType($args, ['onlyContact']);

        $contacts = [];

        $resourceContacts = ResourceContactModel::get([
            'where'     => ['res_id = ?', 'mode = ?'],
            'data'      => [$args['resId'], $args['mode']]
        ]);

        foreach ($resourceContacts as $resourceContact) {
            $contact = '';
            if ($resourceContact['type'] == 'contact') {
                $contactRaw = ContactModel::getById([
                    'select'    => ['*'],
                    'id'        => $resourceContact['item_id']
                ]);

                if (isset($args['onlyContact']) && $args['onlyContact']) {
                    $contactToDisplay = ContactController::getFormattedOnlyContact(['contact' => $contactRaw]);
                } else {
                    $contactToDisplay = ContactController::getFormattedContactWithAddress(['contact' => $contactRaw]);
                }

                $contactToDisplay = $contactToDisplay['contact']['otherInfo'];

                $contact = $contactToDisplay;
            } elseif ($resourceContact['type'] == 'user') {
                $contact = UserModel::getLabelledUserById(['id' => $resourceContact['item_id']]);
            } elseif ($resourceContact['type'] == 'entity') {
                $entity = EntityModel::getById(['id' => $resourceContact['item_id'], 'select' => ['entity_label']]);
                $contact = $entity['entity_label'];
            }

            $contacts[] = $contact;
        }

        return $contacts;
    }

    private static function controlContact(array $args)
    {
        $body = $args['body'];

        if (empty($body)) {
            return ['errors' => 'Body is not set or empty'];
        } elseif (!Validator::stringType()->notEmpty()->validate($body['lastname']) && !Validator::stringType()->notEmpty()->validate($body['company'])) {
            return ['errors' => 'Body lastname or company is mandatory'];
        } elseif (!empty($body['email']) && !filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            return ['errors' => 'Body email is not valid'];
        } elseif (!empty($body['phone']) && !preg_match("/\+?((|\ |\.|\(|\)|\-)?(\d)*)*\d$/", $body['phone'])) {
            return ['errors' => 'Body phone is not valid'];
        }

        $lengthFields = [
            'civility',
            'firstname',
            'lastname',
            'company',
            'department',
            'function',
            'addressNumber',
            'addressStreet',
            'addressPostcode',
            'addressTown',
            'addressCountry',
            'email',
            'phone'
        ];

        foreach ($lengthFields as $field) {
            if (!empty($body[$field]) && !Validator::stringType()->length(1, 256)->validate($body[$field])) {
                return ['errors' => "Body {$field} length is not valid (1..256)"];
            }
        }

        if (!empty($body['customFields'])) {
            if (!Validator::arrayType()->notEmpty()->validate($body['customFields'])) {
                return ['errors' => 'Body customFields is not an array'];
            }
            $customFields = ContactCustomFieldListModel::get(['select' => ['count(1)'], 'where' => ['id in (?)'], 'data' => [array_keys($body['customFields'])]]);
            if (count($body['customFields']) != $customFields[0]['count']) {
                return ['errors' => 'Body customFields : One or more custom fields do not exist'];
            }
        }

        return true;
    }

    private static function createAdjacentData(array $args)
    {
        ValidatorModel::notEmpty($args, ['id', 'body']);
        ValidatorModel::intVal($args, ['id']);
        ValidatorModel::arrayType($args, ['body']);

        $body = $args['body'];

        if (!empty($body['customFields'])) {
            foreach ($body['customFields'] as $key => $value) {
                ContactCustomFieldModel::create(['contact_id' => $args['id'], 'custom_field_id' => $key, 'value' => json_encode($value)]);
            }
        }

        return true;
    }

    public static function getFormattedOnlyContact(array $args)
    {
        ValidatorModel::notEmpty($args, ['contact']);
        ValidatorModel::arrayType($args, ['contact']);

        $contactName = '';
        if (!empty($args['contact']['firstname'])) {
            $contactName .= $args['contact']['firstname'] . ' ';
        }
        if (!empty($args['contact']['lastname'])) {
            $contactName .= $args['contact']['lastname'] . ' ';
        }

        $company = '';
        if (!empty($args['contact']['company'])) {
            $company = $args['contact']['company'];

            if (!empty($contactName)) {
                $company = '(' . $company . ') ';
            }
        }

        $contactToDisplay = $contactName . $company;

        $contact = [
            'type'          => 'onlyContact',
            'id'            => $args['contact']['id'],
            'idToDisplay'   => $contactToDisplay,
            'otherInfo'     => $contactToDisplay,
            'rateColor'     => ''
        ];

        return ['contact' => $contact];
    }

    public static function getFormattedContactWithAddress(array $args)
    {
        ValidatorModel::notEmpty($args, ['contact']);
        ValidatorModel::arrayType($args, ['contact']);
        ValidatorModel::boolType($args, ['color']);

        if (!empty($args['color'])) {
            $rate = ContactController::getFillingRate(['contact' => $args['contact']]);
        }
        $rateColor = empty($rate['color']) ? '' : $rate['color'];

        $address = '';

        if (!empty($args['contact']['address_number'])) {
            $address.= $args['contact']['address_number'] . ' ';
        }
        if (!empty($args['contact']['address_street'])) {
            $address.= $args['contact']['address_street'] . ' ';
        }
        if (!empty($args['contact']['address_postcode'])) {
            $address.= $args['contact']['address_postcode'] . ' ';
        }
        if (!empty($args['contact']['address_town'])) {
            $address.= $args['contact']['address_town'] . ' ';
        }
        if (!empty($args['contact']['address_country'])) {
            $address.= $args['contact']['address_country'];
        }

        $contactName = '';
        if (!empty($args['contact']['firstname'])) {
            $contactName .= $args['contact']['firstname'] . ' ';
        }
        if (!empty($args['contact']['lastname'])) {
            $contactName .= $args['contact']['lastname'] . ' ';
        }

        $company = '';
        if (!empty($args['contact']['company'])) {
            $company = $args['contact']['company'];

            if (!empty($contactName)) {
                $company = '(' . $company . ')';
            }
        }

        $contactToDisplay = trim($contactName . $company);

        $otherInfo = empty($address) ? "{$contactToDisplay}" : "{$contactToDisplay} - {$address}";
        $contact = [
            'type'          => 'contact',
            'id'            => $args['contact']['id'],
            'contact'       => $contactToDisplay,
            'address'       => $address,
            'idToDisplay'   => "{$contactToDisplay}<br/>{$address}",
            'otherInfo'     => $otherInfo,
            'rateColor'     => $rateColor
        ];

        return ['contact' => $contact];
    }
}
