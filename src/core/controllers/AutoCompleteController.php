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

use Contact\controllers\ContactGroupController;
use Contact\models\ContactModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use Entity\models\EntityModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\TextFormatModel;
use Status\models\StatusModel;
use User\models\UserEntityModel;
use User\models\UserModel;

class AutoCompleteController
{
    public static function getContacts(Request $request, Response $response)
    {
        $data = $request->getQueryParams();

        $check = Validator::stringType()->notEmpty()->validate($data['search']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['type']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $searchItems = explode(' ', $data['search']);

        $fields = '(contact_firstname ilike ? OR contact_lastname ilike ? OR firstname ilike ? OR lastname ilike ? OR society ilike ? 
                    OR address_num ilike ? OR address_street ilike ? OR address_town ilike ? OR address_postal_code ilike ?)';
        $where = [];
        $requestData = [];
        if ($data['type'] != 'all') {
            $where = ['contact_type = ?'];
            $requestData = [$data['type']];
        }
        foreach ($searchItems as $item) {
            if (strlen($item) >= 2) {
                $where[] = $fields;
                for ($i = 0; $i < 9; $i++) {
                    $requestData[] = "%{$item}%";
                }
            }
        }

        $contacts = ContactModel::getOnView([
            'select'    => [
                'ca_id', 'firstname', 'lastname', 'contact_lastname', 'contact_firstname', 'society', 'address_num',
                'address_street', 'address_town', 'address_postal_code', 'is_corporate_person'
            ],
            'where'     => $where,
            'data'      => $requestData,
            'limit'     => 1000
        ]);

        $data = [];
        foreach ($contacts as $contact) {
            $data[] = ContactGroupController::getFormattedContact(['contact' => $contact])['contact'];
        }

        return $response->withJson($data);
    }

    public static function getUsers(Request $request, Response $response)
    {
        $excludedUsers = ['superadmin'];

        $users = UserModel::get([
            'select'    => ['user_id', 'firstname', 'lastname'],
            'where'     => ['enabled = ?', 'status != ?', 'user_id not in (?)'],
            'data'      => ['Y', 'DEL', $excludedUsers],
            'orderBy'   => ['lastname']
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

    public static function getUsersForAdministration(Request $request, Response $response)
    {
        if ($GLOBALS['userId'] != 'superadmin') {
            $entities = EntityModel::getAllEntitiesByUserId(['userId' => $GLOBALS['userId']]);
            $users = UserEntityModel::getUsersByEntities([
                'select'    => ['DISTINCT users.user_id', 'users.id', 'users.firstname', 'users.lastname'],
                'entities'  => $entities
            ]);
            $usersNoEntities = UserEntityModel::getUsersWithoutEntities(['select' => ['users.id', 'users.user_id', 'users.firstname', 'users.lastname']]);
            $users = array_merge($users, $usersNoEntities);
        } else {
            $excludedUsers = ['superadmin'];

            $users = UserModel::get([
                'select'    => ['id', 'user_id', 'firstname', 'lastname'],
                'where'     => ['enabled = ?', 'status != ?', 'user_id not in (?)'],
                'data'      => ['Y', 'DEL', $excludedUsers],
                'orderBy'   => ['lastname']
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
        $excludedUsers = ['superadmin'];

        $users = DatabaseModel::select([
            'select'    => ['DISTINCT users.user_id', 'users.firstname', 'users.lastname'],
            'table'     => ['users, usergroup_content, usergroups_services'],
            'where'     => [
                'usergroup_content.group_id = usergroups_services.group_id',
                'usergroup_content.user_id = users.user_id',
                'usergroups_services.service_id in (?)',
                'users.user_id not in (?)',
                'users.enabled = ?',
                'users.status != ?'
            ],
            'data'      => [['visa_documents', 'sign_document'], $excludedUsers, 'Y', 'DEL'],
            'order_by'  => ['users.lastname']
        ]);

        $data = [];
        foreach ($users as $key => $value) {
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
        $entities = EntityModel::get([
            'select'    => ['entity_id', 'entity_label', 'short_label'],
            'where'     => ['enabled = ?'],
            'data'      => ['Y'],
            'orderBy'   => ['entity_label']
        ]);

        $data = [];
        foreach ($entities as $value) {
            $data[] = [
                'type'          => 'entity',
                'id'            => $value['entity_id'],
                'idToDisplay'   => $value['entity_label'],
                'otherInfo'     => $value['short_label']
            ];
        }

        return $response->withJson($data);
    }

    public static function getStatuses(Request $request, Response $response)
    {
        $statuses = StatusModel::get([
            'select'    => ['id', 'label_status', 'img_filename']
        ]);

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
}
