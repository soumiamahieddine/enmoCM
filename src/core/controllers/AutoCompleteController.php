<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Auto Complete Controller
* @author dev@maarch.org
*/

namespace SrcCore\controllers;

use Contact\controllers\ContactController;
use Contact\models\ContactGroupModel;
use Contact\models\ContactModel;
use Entity\models\EntityModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\CurlModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\TextFormatModel;
use SrcCore\models\ValidatorModel;
use Status\models\StatusModel;
use Tag\models\TagModel;
use User\models\UserModel;
use Folder\models\FolderModel;
use Folder\controllers\FolderController;

class AutoCompleteController
{
    const LIMIT = 50;
    const TINY_LIMIT = 10;

    public static function getContacts(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        if (!Validator::stringType()->notEmpty()->validate($queryParams['search'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query params search is empty']);
        }

        $fields = ['firstname', 'lastname', 'company', 'address_number', 'address_street', 'address_town', 'address_postcode'];
        $fields = AutoCompleteController::getUnsensitiveFieldsForRequest(['fields' => $fields]);
        $requestData = AutoCompleteController::getDataForRequest([
            'search'        => $queryParams['search'],
            'fields'        => $fields,
            'where'         => ['enabled = ?'],
            'data'          => [true],
            'fieldsNumber'  => 7,
        ]);

        $contacts = ContactModel::get([
            'select'    => ['*'],
            'where'     => $requestData['where'],
            'data'      => $requestData['data'],
            'limit'     => self::TINY_LIMIT
        ]);

        $color = isset($queryParams['color']) && filter_var($queryParams['color'], FILTER_VALIDATE_BOOLEAN);
        $autocompleteData = [];
        foreach ($contacts as $contact) {
            $autocompleteData[] = AutoCompleteController::getFormattedContact(['contact' => $contact, 'color' => $color])['contact'];
        }

        return $response->withJson($autocompleteData);
    }

    public static function getUsers(Request $request, Response $response)
    {
        $data = $request->getQueryParams();
        $check = Validator::stringType()->notEmpty()->validate($data['search']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $excludedUsers = ['superadmin'];

        $fields = ['firstname', 'lastname'];
        $fields = AutoCompleteController::getUnsensitiveFieldsForRequest(['fields' => $fields]);

        $requestData = AutoCompleteController::getDataForRequest([
            'search'        => $data['search'],
            'fields'        => $fields,
            'where'         => ['status not in (?)', 'user_id not in (?)'],
            'data'          => [['DEL', 'SPD'], $excludedUsers],
            'fieldsNumber'  => 2,
        ]);

        $users = UserModel::get([
            'select'    => ['id', 'user_id', 'firstname', 'lastname'],
            'where'     => $requestData['where'],
            'data'      => $requestData['data'],
            'orderBy'   => ['lastname'],
            'limit'     => self::LIMIT
        ]);

        $data = [];
        foreach ($users as $value) {
            $primaryEntity = UserModel::getPrimaryEntityByUserId(['userId' => $value['user_id']]);
            $data[] = [
                'type'                  => 'user',
                'id'                    => $value['user_id'],
                'serialId'              => $value['id'],
                'idToDisplay'           => "{$value['firstname']} {$value['lastname']}",
                'descriptionToDisplay'  => empty($primaryEntity) ? '' : $primaryEntity['entity_label'],
                'otherInfo'             => ''
            ];
        }

        return $response->withJson($data);
    }

    public static function getMaarchParapheurUsers(Request $request, Response $response)
    {
        $data = $request->getQueryParams();
        $check = Validator::stringType()->notEmpty()->validate($data['search']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'search is empty']);
        }

        if (!empty($data['exludeAlreadyConnected'])) {
            $usersAlreadyConnected = UserModel::get([
                'select' => ['external_id->>\'maarchParapheur\' as external_id'],
                'where' => ['external_id->>\'maarchParapheur\' is not null']
            ]);
            $excludedUsers = array_column($usersAlreadyConnected, 'external_id');
        }

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'modules/visa/xml/remoteSignatoryBooks.xml']);

        if ($loadedXml->signatoryBookEnabled == 'maarchParapheur') {
            foreach ($loadedXml->signatoryBook as $value) {
                if ($value->id == "maarchParapheur") {
                    $url      = $value->url;
                    $userId   = $value->userId;
                    $password = $value->password;
                    break;
                }
            }

            $curlResponse = CurlModel::execSimple([
                'url'           => rtrim($url, '/') . '/rest/autocomplete/users?search='.urlencode($data['search']),
                'basicAuth'     => ['user' => $userId, 'password' => $password],
                'headers'       => ['content-type:application/json'],
                'method'        => 'GET'
            ]);

            if ($curlResponse['code'] != '200') {
                if (!empty($curlResponse['response']['errors'])) {
                    $errors =  $curlResponse['response']['errors'];
                } else {
                    $errors =  $curlResponse['errors'];
                }
                if (empty($errors)) {
                    $errors = 'An error occured. Please check your configuration file.';
                }
                return $response->withStatus(400)->withJson(['errors' => $errors]);
            }

            foreach ($curlResponse['response'] as $key => $value) {
                if (!empty($data['exludeAlreadyConnected']) && in_array($value['id'], $excludedUsers)) {
                    unset($curlResponse['response'][$key]);
                    continue;
                }
                $curlResponse['response'][$key]['idToDisplay'] = $value['firstname'] . ' ' . $value['lastname'];
                $curlResponse['response'][$key]['externalId']['maarchParapheur'] = $value['id'];
            }
            return $response->withJson($curlResponse['response']);
        } else {
            return $response->withStatus(403)->withJson(['errors' => 'maarchParapheur is not enabled']);
        }
    }

    public static function getAll(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        if (!Validator::stringType()->notEmpty()->validate($queryParams['search'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query params search is empty']);
        }

        //Contacts
        $autocompleteContacts = [];
        if (empty($queryParams['noContacts'])) {
            $fields = ['firstname', 'lastname', 'company', 'address_number', 'address_street', 'address_town', 'address_postcode'];
            $fields = AutoCompleteController::getUnsensitiveFieldsForRequest(['fields' => $fields]);
            $requestData = AutoCompleteController::getDataForRequest([
                'search'        => $queryParams['search'],
                'fields'        => $fields,
                'where'         => ['enabled = ?'],
                'data'          => [true],
                'fieldsNumber'  => 7,
            ]);

            $contacts = ContactModel::get([
                'select'    => ['*'],
                'where'     => $requestData['where'],
                'data'      => $requestData['data'],
                'orderBy'   => ['company', 'lastname'],
                'limit'     => self::TINY_LIMIT
            ]);

            $color = isset($queryParams['color']) && filter_var($queryParams['color'], FILTER_VALIDATE_BOOLEAN);

            foreach ($contacts as $contact) {
                $autocompleteContacts[] = ContactController::getFormattedContactWithAddress(['contact' => $contact, 'color' => $color])['contact'];
            }
        }

        //Users
        $autocompleteUsers = [];
        if (empty($queryParams['noUsers'])) {
            $fields = ['firstname', 'lastname'];
            $fields = AutoCompleteController::getUnsensitiveFieldsForRequest(['fields' => $fields]);
            $requestData = AutoCompleteController::getDataForRequest([
                'search'        => $queryParams['search'],
                'fields'        => $fields,
                'where'         => ['status not in (?)', 'user_id not in (?)'],
                'data'          => [['DEL', 'SPD'], ['superadmin']],
                'fieldsNumber'  => 2,
            ]);

            $users = UserModel::get([
                'select'    => ['id', 'firstname', 'lastname'],
                'where'     => $requestData['where'],
                'data'      => $requestData['data'],
                'orderBy'   => ['lastname'],
                'limit'     => self::TINY_LIMIT
            ]);

            foreach ($users as $user) {
                $autocompleteUsers[] = [
                    'type'          => 'user',
                    'id'            => $user['id'],
                    'idToDisplay'   => "{$user['firstname']} {$user['lastname']}",
                    'otherInfo'     => "{$user['firstname']} {$user['lastname']}"
                ];
            }
        }

        //Entities
        $autocompleteEntities = [];
        if (empty($queryParams['noEntities'])) {
            $fields = ['entity_label'];
            $fields = AutoCompleteController::getUnsensitiveFieldsForRequest(['fields' => $fields]);
            $requestData = AutoCompleteController::getDataForRequest([
                'search'        => $queryParams['search'],
                'fields'        => $fields,
                'where'         => ['enabled = ?'],
                'data'          => ['Y'],
                'fieldsNumber'  => 1,
            ]);

            $entities = EntityModel::get([
                'select'    => ['id', 'entity_id', 'entity_label', 'short_label'],
                'where'     => $requestData['where'],
                'data'      => $requestData['data'],
                'orderBy'   => ['entity_label'],
                'limit'     => self::TINY_LIMIT
            ]);

            foreach ($entities as $value) {
                $autocompleteEntities[] = [
                    'type'          => 'entity',
                    'id'            => $value['id'],
                    'idToDisplay'   => $value['entity_label'],
                    'otherInfo'     => $value['short_label']
                ];
            }
        }

        //Contacts Groups
        $autocompleteContactsGroups = [];
        if (empty($queryParams['noContactsGroups'])) {
            $fields = ['label'];
            $fields = AutoCompleteController::getUnsensitiveFieldsForRequest(['fields' => $fields]);
            $requestData = AutoCompleteController::getDataForRequest([
                'search'        => $queryParams['search'],
                'fields'        => $fields,
                'where'         => ['(public = ? OR owner = ?)'],
                'data'          => [true, $GLOBALS['id']],
                'fieldsNumber'  => 1,
            ]);

            $contactsGroups = ContactGroupModel::get([
                'select'    => ['id', 'label'],
                'where'     => $requestData['where'],
                'data'      => $requestData['data'],
                'orderBy'   => ['label'],
                'limit'     => self::TINY_LIMIT
            ]);

            foreach ($contactsGroups as $value) {
                $autocompleteContactsGroups[] = [
                    'type'          => 'contactGroup',
                    'id'            => $value['id'],
                    'idToDisplay'   => $value['label'],
                    'otherInfo'     => $value['label']
                ];
            }
        }

        $total = count($autocompleteContacts) + count($autocompleteUsers) + count($autocompleteEntities) + count($autocompleteContactsGroups);
        if ($total > self::TINY_LIMIT) {
            $divider = $total / self::TINY_LIMIT;
            $autocompleteContacts = array_slice($autocompleteContacts, 0, round(count($autocompleteContacts) / $divider));
            $autocompleteUsers = array_slice($autocompleteUsers, 0, round(count($autocompleteUsers) / $divider));
            $autocompleteEntities = array_slice($autocompleteEntities, 0, round(count($autocompleteEntities) / $divider));
            $autocompleteContactsGroups = array_slice($autocompleteContactsGroups, 0, round(count($autocompleteContactsGroups) / $divider));
        }
        $autocompleteData = array_merge($autocompleteContacts, $autocompleteUsers, $autocompleteEntities, $autocompleteContactsGroups);

        return $response->withJson($autocompleteData);
    }

    public static function getUsersForAdministration(Request $request, Response $response)
    {
        $data = $request->getQueryParams();
        $check = Validator::stringType()->notEmpty()->validate($data['search']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $excludedUsers = ['superadmin'];

        if ($GLOBALS['userId'] != 'superadmin') {
            $entities = EntityModel::getAllEntitiesByUserId(['userId' => $GLOBALS['userId']]);

            $fields = ['users.firstname', 'users.lastname'];
            $fields = AutoCompleteController::getUnsensitiveFieldsForRequest(['fields' => $fields]);

            $requestData = AutoCompleteController::getDataForRequest([
                'search'        => $data['search'],
                'fields'        => $fields,
                'where'         => [
                    'users.user_id = users_entities.user_id',
                    'users_entities.entity_id in (?)',
                    'users.status not in (?)'
                ],
                'data'          => [$entities, ['DEL', 'SPD']],
                'fieldsNumber'  => 2,
            ]);

            $users = DatabaseModel::select([
                'select'    => ['DISTINCT users.user_id', 'users.id', 'users.firstname', 'users.lastname'],
                'table'     => ['users, users_entities'],
                'where'     => $requestData['where'],
                'data'      => $requestData['data'],
                'limit'     => self::LIMIT
            ]);

            if (count($users) < self::LIMIT) {
                $fields = ['users.firstname', 'users.lastname'];
                $fields = AutoCompleteController::getUnsensitiveFieldsForRequest(['fields' => $fields]);

                $requestData = AutoCompleteController::getDataForRequest([
                    'search'        => $data['search'],
                    'fields'        => $fields,
                    'where'         => [
                        'users_entities IS NULL',
                        'users.user_id not in (?)',
                        'users.status not in (?)'
                    ],
                    'data'          => [$excludedUsers, ['DEL', 'SPD']],
                    'fieldsNumber'  => 2,
                ]);

                $usersNoEntities = DatabaseModel::select([
                    'select'    => ['users.id', 'users.user_id', 'users.firstname', 'users.lastname'],
                    'table'     => ['users', 'users_entities'],
                    'left_join' => ['users.user_id = users_entities.user_id'],
                    'where'     => $requestData['where'],
                    'data'      => $requestData['data'],
                    'limit'     => (self::LIMIT - count($users))
                ]);

                $users = array_merge($users, $usersNoEntities);
            }
        } else {
            $requestData = AutoCompleteController::getDataForRequest([
                'search'        => $data['search'],
                'fields'        => '(firstname ilike ? OR lastname ilike ?)',
                'where'         => ['status not in (?)', 'user_id not in (?)'],
                'data'          => [['DEL', 'SPD'], $excludedUsers],
                'fieldsNumber'  => 2,
            ]);

            $users = UserModel::get([
                'select'    => ['id', 'user_id', 'firstname', 'lastname'],
                'where'     => $requestData['where'],
                'data'      => $requestData['data'],
                'orderBy'   => ['lastname'],
                'limit'     => self::LIMIT
            ]);
        }

        $data = [];
        foreach ($users as $value) {
            $data[] = [
                'type'          => 'user',
                'id'            => $value['id'],
                'idToDisplay'   => "{$value['firstname']} {$value['lastname']}",
                'otherInfo'     => $value['user_id']
            ];
        }

        return $response->withJson($data);
    }

    public static function getUsersForVisa(Request $request, Response $response)
    {
        $data = $request->getQueryParams();
        $check = Validator::stringType()->notEmpty()->validate($data['search']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $excludedUsers = ['superadmin'];

        $fields = ['users.firstname', 'users.lastname'];
        $fields = AutoCompleteController::getUnsensitiveFieldsForRequest(['fields' => $fields]);

        $requestData = AutoCompleteController::getDataForRequest([
            'search'        => $data['search'],
            'fields'        => $fields,
            'where'         => [
                'usergroups.group_id = usergroups_services.group_id',
                'usergroups.id = usergroup_content.group_id',
                'usergroup_content.user_id = users.id',
                'usergroups_services.service_id in (?)',
                'users.user_id not in (?)',
                'users.status not in (?)'
            ],
            'data'          => [['visa_documents', 'sign_document'], $excludedUsers, ['DEL', 'SPD']],
            'fieldsNumber'  => 2,
        ]);

        $users = DatabaseModel::select([
            'select'    => ['DISTINCT users.user_id', 'users.firstname', 'users.lastname'],
            'table'     => ['users, usergroup_content, usergroups, usergroups_services'],
            'where'     => $requestData['where'],
            'data'      => $requestData['data'],
            'order_by'  => ['users.lastname'],
            'limit'     => self::LIMIT
        ]);

        $data = [];
        foreach ($users as $value) {
            $data[] = [
                'type'          => 'user',
                'id'            => $value['user_id'],
                'idToDisplay'   => "{$value['firstname']} {$value['lastname']}",
                'otherInfo'     => ''
            ];
        }

        return $response->withJson($data);
    }

    public static function getEntities(Request $request, Response $response)
    {
        $data = $request->getQueryParams();
        $check = Validator::stringType()->notEmpty()->validate($data['search']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $fields = ['entity_label'];
        $fields = AutoCompleteController::getUnsensitiveFieldsForRequest(['fields' => $fields]);

        $requestData = AutoCompleteController::getDataForRequest([
            'search'        => $data['search'],
            'fields'        => $fields,
            'where'         => ['enabled = ?'],
            'data'          => ['Y'],
            'fieldsNumber'  => 1,
        ]);

        $entities = EntityModel::get([
            'select'    => ['id', 'entity_id', 'entity_label', 'short_label'],
            'where'     => $requestData['where'],
            'data'      => $requestData['data'],
            'orderBy'   => ['entity_label'],
            'limit'     => self::LIMIT
        ]);

        $data = [];
        foreach ($entities as $value) {
            $data[] = [
                'type'          => 'entity',
                'id'            => $value['entity_id'],
                'serialId'      => $value['id'],
                'idToDisplay'   => $value['entity_label'],
                'otherInfo'     => $value['short_label']
            ];
        }

        return $response->withJson($data);
    }

    public static function getStatuses(Request $request, Response $response)
    {
        $statuses = StatusModel::get(['select' => ['id', 'label_status', 'img_filename']]);

        $data = [];
        foreach ($statuses as $value) {
            $data[] = [
                'type'          => 'status',
                'id'            => $value['id'],
                'idToDisplay'   => $value['label_status'],
                'otherInfo'     => $value['img_filename']
            ];
        }

        return $response->withJson($data);
    }

    public static function getContactsForGroups(Request $request, Response $response)
    {
        $data = $request->getQueryParams();

        $check = Validator::stringType()->notEmpty()->validate($data['search']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $searchItems = explode(' ', $data['search']);

        $fields = '(firstname ilike ? OR lastname ilike ? OR company ilike ? 
                    OR address_number ilike ? OR address_street ilike ? OR address_town ilike ? OR address_postcode ilike ?)';
        $where = [];
        $requestData = [];
        foreach ($searchItems as $item) {
            if (strlen($item) >= 2) {
                $where[] = $fields;
                for ($i = 0; $i < 7; $i++) {
                    $requestData[] = "%{$item}%";
                }
            }
        }

        $contacts = ContactModel::get([
            'select'    => [
                'id', 'firstname', 'lastname', 'company', 'address_number', 'address_street', 'address_town', 'address_postcode'
            ],
            'where'     => $where,
            'data'      => $requestData,
            'limit'     => 1000
        ]);

        $data = [];
        foreach ($contacts as $contact) {
            $data[] = ContactController::getFormattedContactWithAddress(['contact' => $contact])['contact'];
        }

        return $response->withJson($data);
    }

    public static function getBanAddresses(Request $request, Response $response)
    {
        $data = $request->getQueryParams();

        $check = Validator::stringType()->notEmpty()->validate($data['address']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['department']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }
        $customId = CoreConfigModel::getCustomId();

        if (is_dir("custom/{$customId}/referential/ban/indexes/{$data['department']}")) {
            $path = "custom/{$customId}/referential/ban/indexes/{$data['department']}";
        } elseif (is_dir('referential/ban/indexes/' . $data['department'])) {
            $path = 'referential/ban/indexes/' . $data['department'];
        } else {
            return $response->withStatus(400)->withJson(['errors' => 'Department indexes do not exist']);
        }

        \Zend_Search_Lucene_Analysis_Analyzer::setDefault(new \Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());
        \Zend_Search_Lucene_Search_QueryParser::setDefaultOperator(\Zend_Search_Lucene_Search_QueryParser::B_AND);
        \Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');

        $index = \Zend_Search_Lucene::open($path);
        \Zend_Search_Lucene::setResultSetLimit(100);

        $data['address'] = str_replace(['*', '~', '-', '\''], ' ', $data['address']);
        $aAddress = explode(' ', $data['address']);
        foreach ($aAddress as $key => $value) {
            if (strlen($value) <= 2 && !is_numeric($value)) {
                unset($aAddress[$key]);
                continue;
            }
            if (strlen($value) >= 3 && $value != 'rue' && $value != 'avenue' && $value != 'boulevard') {
                $aAddress[$key] .= '*';
            }
        }
        $data['address'] = implode(' ', $aAddress);
        if (empty($data['address'])) {
            return $response->withJson([]);
        }

        $hits = $index->find(TextFormatModel::normalize(['string' => $data['address']]));

        $addresses = [];
        foreach ($hits as $key => $hit) {
            $addresses[] = [
                'banId'         => $hit->banId,
                'number'        => $hit->streetNumber,
                'afnorName'     => $hit->afnorName,
                'postalCode'    => $hit->postalCode,
                'city'          => $hit->city,
                'address'       => "{$hit->streetNumber} {$hit->afnorName}, {$hit->city} ({$hit->postalCode})"
            ];
        }

        return $response->withJson($addresses);
    }

    public static function getFolders(Request $request, Response $response)
    {
        $data = $request->getQueryParams();

        if (!Validator::stringType()->notEmpty()->validate($data['search'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query params search is empty']);
        }

        $scopedFolders = FolderController::getScopeFolders(['login' => $GLOBALS['userId']]);

        $arrScopedFoldersIds = array_column($scopedFolders, 'id');

        $fields = ['label'];
        $fields = AutoCompleteController::getUnsensitiveFieldsForRequest(['fields' => $fields]);

        $selectedFolders = FolderModel::get([
            'where'    => ["{$fields} AND id in (?)"],
            'data'     => [ '%'.$data['search'].'%', $arrScopedFoldersIds],
            'orderBy'  => ['label']
        ]);

        $data = [];
        foreach ($selectedFolders as $value) {
            $data[] = [
                'id'            => $value['id'],
                'idToDisplay'   => $value['label'],
                'isPublic'      => $value['public'],
                'otherInfo'     => ''
            ];
        }

        return $response->withJson($data);
    }

    public static function getTags(Request $request, Response $response)
    {
        $data = $request->getQueryParams();

        if (!Validator::stringType()->notEmpty()->validate($data['search'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query params search is empty']);
        }

        $fields = ['label'];
        $fields = AutoCompleteController::getUnsensitiveFieldsForRequest(['fields' => $fields]);

        $requestData = AutoCompleteController::getDataForRequest([
            'search'        => $data['search'],
            'fields'        => $fields,
            'where'         => ['1 = ?'],
            'data'          => ['1'],
            'fieldsNumber'  => 1,
        ]);

        $tags = TagModel::get([
            'select'    => ['id', 'label'],
            'where'     => $requestData['where'],
            'data'      => $requestData['data'],
            'orderBy'   => ['label'],
            'limit'     => self::LIMIT
        ]);

        $data = [];
        foreach ($tags as $value) {
            $data[] = [
                'id'            => $value['id'],
                'idToDisplay'   => $value['label']
            ];
        }

        return $response->withJson($data);
    }

    private static function getDataForRequest(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['search', 'fields', 'where', 'data', 'fieldsNumber']);
        ValidatorModel::stringType($aArgs, ['search', 'fields']);
        ValidatorModel::arrayType($aArgs, ['where', 'data']);
        ValidatorModel::intType($aArgs, ['fieldsNumber']);

        $searchItems = explode(' ', $aArgs['search']);

        foreach ($searchItems as $item) {
            if (strlen($item) >= 2) {
                $aArgs['where'][] = $aArgs['fields'];
                for ($i = 0; $i < $aArgs['fieldsNumber']; $i++) {
                    $aArgs['data'][] = "%{$item}%";
                }
            }
        }

        return ['where' => $aArgs['where'], 'data' => $aArgs['data']];
    }

    private static function getUnsensitiveFieldsForRequest(array $args)
    {
        ValidatorModel::notEmpty($args, ['fields']);
        ValidatorModel::arrayType($args, ['fields']);

        $fields = [];
        foreach ($args['fields'] as $key => $field) {
            $fields[$key] = "translate({$field}, 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ', 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr')";
            $fields[$key] .= "ilike translate(?, 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ', 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr')";
        }
        $fields = implode(' OR ', $fields);
        $fields = "($fields)";

        return $fields;
    }

    public static function getFormattedContact(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['contact']);
        ValidatorModel::arrayType($aArgs, ['contact']);
        ValidatorModel::boolType($aArgs, ['color']);

        if (!empty($aArgs['color'])) {
            $rate = ContactController::getFillingRate(['contact' => $aArgs['contact']]);
        }
        $rateColor = empty($rate['color']) ? '' : $rate['color'];

        $address = '';
        if ($aArgs['contact']['is_corporate_person'] == 'Y') {
            $address.= $aArgs['contact']['firstname'];
            $address.= (empty($address) ? $aArgs['contact']['lastname'] : " {$aArgs['contact']['lastname']}");
            $address .= ', ';
            if (!empty($aArgs['contact']['address_num'])) {
                $address.= $aArgs['contact']['address_num'] . ' ';
            }
            if (!empty($aArgs['contact']['address_street'])) {
                $address.= $aArgs['contact']['address_street'] . ' ';
            }
            if (!empty($aArgs['contact']['address_postal_code'])) {
                $address.= $aArgs['contact']['address_postal_code'] . ' ';
            }
            if (!empty($aArgs['contact']['address_town'])) {
                $address.= $aArgs['contact']['address_town'] . ' ';
            }
            if (!empty($aArgs['contact']['address_country'])) {
                $address.= $aArgs['contact']['address_country'];
            }
            $address = rtrim($address, ', ');
            $otherInfo = empty($address) ? "{$aArgs['contact']['society']}" : "{$aArgs['contact']['society']} - {$address}";
            $contact = [
                'type'          => 'contact',
                'id'            => $aArgs['contact']['ca_id'],
                'contact'       => $aArgs['contact']['society'],
                'address'       => $address,
                'idToDisplay'   => "{$aArgs['contact']['society']}<br/>{$address}",
                'otherInfo'     => $otherInfo,
                'rateColor'     => $rateColor
            ];
        } else {
            if (!empty($aArgs['contact']['address_num'])) {
                $address.= $aArgs['contact']['address_num'] . ' ';
            }
            if (!empty($aArgs['contact']['address_street'])) {
                $address.= $aArgs['contact']['address_street'] . ' ';
            }
            if (!empty($aArgs['contact']['address_postal_code'])) {
                $address.= $aArgs['contact']['address_postal_code'] . ' ';
            }
            if (!empty($aArgs['contact']['address_town'])) {
                $address.= $aArgs['contact']['address_town'] . ' ';
            }
            if (!empty($aArgs['contact']['address_country'])) {
                $address.= $aArgs['contact']['address_country'];
            }
            $contactToDisplay = "{$aArgs['contact']['contact_firstname']} {$aArgs['contact']['contact_lastname']}";
            if (!empty($aArgs['contact']['society'])) {
                $contactToDisplay .= " ({$aArgs['contact']['society']})";
            }

            $otherInfo = empty($address) ? "{$contactToDisplay}" : "{$contactToDisplay} - {$address}";
            $contact = [
                'type'          => 'contact',
                'id'            => $aArgs['contact']['ca_id'],
                'contact'       => $contactToDisplay,
                'address'       => $address,
                'idToDisplay'   => "{$contactToDisplay}<br/>{$address}",
                'otherInfo'     => $otherInfo,
                'rateColor'     => $rateColor
            ];
        }

        return ['contact' => $contact];
    }
}
