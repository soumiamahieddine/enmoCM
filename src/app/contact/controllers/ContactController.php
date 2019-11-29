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

use Contact\models\ContactFillingModel;
use Contact\models\ContactModel;
use Entity\models\EntityModel;
use Group\controllers\PrivilegeController;
use Resource\controllers\ResController;
use Resource\models\ResModel;
use SrcCore\models\CoreConfigModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\DatabaseModel;
use SrcCore\models\TextFormatModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class ContactController
{
    public function get(Request $request, Response $response)
    {
        //TODO privileges

        return $response->withJson(['contacts' => ContactModel::get(['select' => ['id', 'firstname', 'lastname', 'company']])]);
    }

    public function create(Request $request, Response $response)
    {
        //TODO privileges

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

        return $response->withJson(['id' => $id]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        //TODO privileges

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
        //TODO privileges

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

        return $response->withStatus(204);
    }

    public function updateActivation(Request $request, Response $response, array $args)
    {
        //TODO privileges

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
        //TODO privileges

        $contact = ContactModel::getById(['id' => $args['id'], 'select' => [1]]);
        if (empty($contact)) {
            return $response->withStatus(400)->withJson(['errors' => 'Contact does not exist']);
        }

        ContactModel::delete([
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        return $response->withStatus(204);
    }

    public function getCommunicationByContactId(Request $request, Response $response, array $aArgs)
    {
        $contact = ContactModel::getCommunicationByContactId([
            'contactId' => $aArgs['contactId'],
        ]);

        return $response->withJson([$contact]);
    }

    public function getFilling(Request $request, Response $response)
    {
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

        $resource = ResModel::getById(['select' => ['*'], 'resId' => $args['resId']]);

        if (empty($resource)) {
            return $response->withStatus(404)->withJson(['errors' => 'Document does not exist']);
        }

        $queryParams = $request->getQueryParams();

        $contacts = [];
        if ($queryParams['type'] == 'senders') {
            if ($resource['category_id'] == 'outgoing') {
                $contacts = ContactController::getFormattedContacts(['resource' => $resource, 'tableMulti' => 'resource_contacts', 'columnRes' => null]);
            } else {
                $contacts = ContactController::getFormattedContacts(['resource' => $resource, 'tableMulti' => 'contacts_res', 'columnRes' => 'exp_user_id']);
            }
        } elseif ($queryParams['type'] == 'recipients') {
            if ($resource['category_id'] == 'outgoing') {
                $contacts = ContactController::getFormattedContacts(['resource' => $resource, 'tableMulti' => 'contact_res', 'columnRes' => 'exp_user_id']);
            } else {
                $contacts = ContactController::getFormattedContacts(['resource' => $resource, 'tableMulti' => 'resource_contacts', 'columnRes' => null]);
            }
        }

        return $response->withJson(['contacts' => $contacts]);
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

    public static function getContactAfnor(array $aArgs)
    {
        $afnorAddress = ['Afnor',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        if ($aArgs['is_corporate_person'] == 'Y') {
            // Ligne 1
            $afnorAddress[1] = substr($aArgs['society'], 0, 38);

            // Ligne 2
            if (!empty($aArgs['title']) || !empty($aArgs['firstname']) || !empty($aArgs['lastname'])) {
                $afnorAddress[2] = ContactController::controlLengthNameAfnor([
                    'title'         => $aArgs['title'],
                    'fullName'      => $aArgs['firstname'].' '.$aArgs['lastname'],
                    'strMaxLength'  => 38
                ]);
            }
        } else {
            // Ligne 2
            if (!empty($aArgs['contact_title']) || !empty($aArgs['contact_firstname']) || !empty($aArgs['contact_lastname'])) {
                $afnorAddress[2] = ContactController::controlLengthNameAfnor([
                    'title'         => $aArgs['contact_title'],
                    'fullName'      => $aArgs['contact_firstname'].' '.$aArgs['contact_lastname'],
                    'strMaxLength'  => 38
                ]);
            }

        }
        // Ligne 3
        if (!empty($aArgs['address_complement'])) {
            $afnorAddress[3] = substr($aArgs['address_complement'], 0, 38);
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
        $afnorAddress[4] = substr($aArgs['address_num'].' '.$aArgs['address_street'], 0, 38);

        // Ligne 5
        $afnorAddress[5] = '';

        // Ligne 6
        $aArgs['address_postal_code'] = strtoupper($aArgs['address_postal_code']);
        $aArgs['address_town'] = strtoupper($aArgs['address_town']);
        $afnorAddress[6] = substr($aArgs['address_postal_code'].' '.$aArgs['address_town'], 0, 38);

        return $afnorAddress;
    }

    public static function controlLengthNameAfnor(array $aArgs)
    {
        $aCivility = ContactModel::getCivilities();
        if (strlen($aArgs['title'].' '.$aArgs['fullName']) > $aArgs['strMaxLength']) {
            $aArgs['title'] = $aCivility[$aArgs['title']]['abbreviation'];
        } else {
            $aArgs['title'] = $aCivility[$aArgs['title']]['label'];
        }

        return substr($aArgs['title'].' '.$aArgs['fullName'], 0, $aArgs['strMaxLength']);
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

    public static function getFormattedContacts(array $args)
    {
        ValidatorModel::notEmpty($args, ['resource', 'tableMulti']);
        ValidatorModel::arrayType($args, ['resource']);
        ValidatorModel::stringType($args, ['tableMulti', 'columnRes']);

        $resource = $args['resource'];

        $rawContacts = [];
        if ($resource['is_multicontacts'] == 'Y' || !isset($args['columnRes'])) {
            if ($args['tableMulti'] == 'contacts_res') {
                $multiContacts = DatabaseModel::select([
                    'select' => ['contact_id', 'address_id', 'mode'],
                    'table' => ['contacts_res'],
                    'where' => ['res_id = ?'],
                    'data' => [$resource['res_id']]
                ]);

                foreach ($multiContacts as $multiContact) {
                    $rawContacts[] = [
                        'login'         => $multiContact['contact_id'],
                        'address_id'    => $multiContact['address_id'],
                        'mode'          => $multiContact['mode']
                    ];
                }
            } elseif ($args['tableMulti'] == 'resource_contacts') {
                $multiContacts = DatabaseModel::select([
                    'select' => ['item_id', 'type', 'mode'],
                    'table' => ['resource_contacts'],
                    'where' => ['res_id = ?'],
                    'data' => [$resource['res_id']]
                ]);

                foreach ($multiContacts as $multiContact) {
                    $rawContacts[] = [
                        'user_id' => $multiContact['type'] == 'user' ? $multiContact['item_id'] : null,
                        'address_id' => $multiContact['type'] == 'contact' ? $multiContact['item_id'] : null,
                        'entity_id' => $multiContact['type'] == 'entity' ? $multiContact['item_id'] : null,
                    ];
                }
            }
        } else {
            $rawContacts[] = [
                'login'         => $resource[$args['columnRes']],
                'address_id'    => $resource['address_id'],
            ];
        }

        $contacts = [];

        foreach ($rawContacts as $rawContact) {
            if (!empty($rawContact['address_id'])) {
                $contactView = ContactModel::getOnView([
                    'select' => [
                        'is_corporate_person', 'lastname', 'firstname', 'address_num', 'address_street', 'address_complement',
                        'address_town', 'address_postal_code', 'address_country', 'ca_id', 'society', 'website', 'phone',
                        'contact_firstname', 'contact_lastname', 'address_country', 'email', 'function', 'contact_other_data',
                        'occupancy'
                    ],
                    'where'     => ['ca_id = ?'],
                    'data'      => [$rawContact['address_id']]
                ]);

                $contactView = $contactView[0];

                if (!empty($rawContact['mode']) && $rawContact['mode'] == 'third') {
                    $mode = 'third';
                } else {
                    $mode = $contactView['is_corporate_person'] == 'Y' ? 'corporate' : 'physical';
                }

                $contact = [
                    'mode'      => $mode,
                    'firstname' => $contactView['firstname'] ?? '',
                    'lastname'  => $contactView['lastname'] ?? '',
                    'email'     => $contactView['email'] ?? '',
                    'phone'     => $contactView['phone'] ?? '',
                    'society'   => $contactView['society'] ?? '',
                    'function'  => $contactView['function'] ?? '',
                    'num'       => $contactView['address_num'] ?? '',
                    'street'    => $contactView['address_street'] ?? '',
                    'complement'=> $contactView['address_complement'] ?? '',
                    'town'      => $contactView['address_town'] ?? '',
                    'postalCode'=> $contactView['address_postal_code'] ?? '',
                    'country'   => $contactView['address_country'] ?? '',
                    'otherData' => $contactView['contact_other_data'] ?? '',
                    'website'   => $contactView['website'] ?? '',
                    'occupancy' => $contactView['occupancy'] ?? '',
                    'department' => $contactView['departement'] ?? ''
                ];

                $filling = ContactController::getFillingRate(['contact' => $contact]);

                $contact['filling'] = $filling['color'];

                $contacts[] = $contact;
            } elseif (!empty($rawContact['login'] || !empty($rawContact['user_id']))) {
                if (!empty($rawContact['login'])) {
                    $user = UserModel::getByLowerLogin(['login' => $rawContact['login']]);
                } else {
                    $user = UserModel::getById(['id' => $rawContact['user_id']]);
                }

                $phone = '';
                if (!empty($phone) && ($user['id'] == $GLOBALS['id']
                        || PrivilegeController::hasPrivilege(['privilegeId' => 'view_personal_data', 'userId' => $GLOBALS['id']]))) {
                    $phone = $user['phone'];
                }

                $primaryEntity = UserModel::getPrimaryEntityById(['id' => $user['id']]);

                $userEntities = UserModel::getNonPrimaryEntitiesById(['id' => $user['id']]);
                $userEntities = array_column($userEntities, 'entity_label');

                $nonPrimaryEntities = implode(', ', $userEntities);

                $contact = [
                    'mode'      => 'internal',
                    'firstname' => $user['firstname'],
                    'lastname'  => $user['lastname'],
                    'email'     => $user['mail'],
                    'phone'     => $phone,
                    'society'   => '',
                    'function'  => '',
                    'num'       => '',
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

                $filling = ContactController::getFillingRate(['contact' => $contact]);

                $contact['filling'] = $filling['color'];

                $contacts[] = $contact;
            } elseif (!empty($rawContact['entity_id'])) {
                $entity = EntityModel::getById(['id' => $rawContact['entity_id']]);

                $contact = [
                    'mode'      => 'entity',
                    'firstname' => '',
                    'lastname'  => $entity['entity_label'],
                    'email'     => $entity['email'],
                    'phone'     => '',
                    'society'   => '',
                    'function'  => '',
                    'num'       => '',
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

                $filling = ContactController::getFillingRate(['contact' => $contact]);

                $contact['filling'] = $filling['color'];

                $contacts[] = $contact;
            }
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

        return true;
    }
}
