<?php

require '../../vendor/autoload.php';

chdir('../..');

$customs =  scandir('custom');

foreach ($customs as $custom) {
    if ($custom == 'custom.xml' || $custom == '.' || $custom == '..') {
        continue;
    }

    \SrcCore\models\DatabasePDO::reset();
    new \SrcCore\models\DatabasePDO(['customId' => $custom]);

    // TRUNCATE CONTACTS TABLES
    \SrcCore\models\DatabaseModel::delete([
        'table' => 'contacts',
        'where' => ['id > 0']
    ]);
    \SrcCore\models\DatabaseModel::delete([
        'table' => 'contacts_custom_fields_list',
        'where' => ['id > 0']
    ]);
    \SrcCore\models\DatabaseModel::delete([
        'table' => 'contacts_parameters',
        'where' => ['id > 0']
    ]);

    $migrated = 0;
    $contactsInfo = \SrcCore\models\DatabaseModel::select([
        'select' => ['contact_id', 'society', 'contact_firstname', 'contact_lastname', 'contact_title', 'contact_function', 'contact_other_data',
            'creation_date', 'update_date', 'ca_id', 'departement', 'firstname', 'lastname', 'title', 'function', 'occupancy',
            'address_num', 'address_street', 'address_complement', 'address_town', 'address_postal_code', 'address_country', 'phone', 'email', 'other_data',
            'user_id', 'enabled', 'external_id', 'society_short', 'contact_purpose_label', 'contact_type_label', 'website', 'salutation_header', 'salutation_footer'
        ],
        'table' => ['view_contacts']
    ]);

    // Fields not migrated
    //contact_user_id, contact_enabled, contact_entity_id, entity_id, is_private, is_corporate_person, contact_type, contact_purpose_id

    $contactTypes = \SrcCore\models\DatabaseModel::select([
        'select' => ['label'],
        'table'  => ['contact_types']
    ]);
    $contactTypes = array_column($contactTypes, 'label');

    $contactPurposes = \SrcCore\models\DatabaseModel::select([
        'select' => ['label'],
        'table'  => ['contact_purposes']
    ]);
    $contactPurposes = array_column($contactPurposes, 'label');

    $customFields= [
        ['oldId' => 'salutation_header',     'label' => 'Formule de politesse (Début)',     'type' => 'string',     'value' => ['']],
        ['oldId' => 'salutation_footer',     'label' => 'Formule de politesse (Fin)',       'type' => 'string',     'value' => ['']],
        ['oldId' => 'website',               'label' => 'Site internet',                    'type' => 'string',     'value' => ['']],
        ['oldId' => 'contact_type_label',    'label' => 'Type de contact',                  'type' => 'select',     'value' => $contactTypes],
        ['oldId' => 'contact_purpose_label', 'label' => 'Dénomination',                     'type' => 'select',     'value' => $contactPurposes],
        ['oldId' => 'society_short',         'label' => 'Sigle de la structure',            'type' => 'string',     'value' => ['']],
    ];

    $newCustomFields = addCustomFields(['customFields' => $customFields]);

    $firstMan = \User\models\UserModel::get(['select' => ['id'], 'orderBy' => ['id'], 'limit' => 1, 'where' => ['status = ?'], 'data' => ['OK']]);

    foreach ($contactsInfo as $contactInfo) {
        $oldContactId = $contactInfo['contact_id'];
        $oldAddressId = $contactInfo['ca_id'];
        unset($contactInfo['contact_id']);
        unset($contactInfo['ca_id']);

        // Civility
        $contactInfo['civility'] = !empty($contactInfo['contact_title']) ? $contactInfo['contact_title'] : $contactInfo['title'];
        unset($contactInfo['contact_title']);
        unset($contactInfo['title']);

        // Firstname
        $contactInfo['firstname'] = !empty($contactInfo['contact_firstname']) ? $contactInfo['contact_firstname'] : $contactInfo['firstname'];
        unset($contactInfo['contact_firstname']);

        // Lastname
        $contactInfo['lastname'] = !empty($contactInfo['contact_lastname']) ? $contactInfo['contact_lastname'] : $contactInfo['lastname'];
        unset($contactInfo['contact_lastname']);

        // Company
        $contactInfo['company'] = $contactInfo['society'];
        unset($contactInfo['society']);

        // Function
        $contactInfo['function'] = !empty($contactInfo['contact_function']) ? $contactInfo['contact_function'] : $contactInfo['function'];
        unset($contactInfo['contact_function']);

        // Department
        $contactInfo['department'] = $contactInfo['departement'];
        unset($contactInfo['departement']);

        // Address
        $contactInfo['address_number'] = $contactInfo['address_num'];
        unset($contactInfo['address_num']);
        $contactInfo['address_additional1'] = $contactInfo['occupancy'];
        unset($contactInfo['occupancy']);
        $contactInfo['address_additional2'] = $contactInfo['address_complement'];
        unset($contactInfo['address_complement']);
        $contactInfo['address_postcode'] = $contactInfo['address_postal_code'];
        unset($contactInfo['address_postal_code']);

        //Moyen de communication
        $communicationMeans = \SrcCore\models\DatabaseModel::select(['select' => ['value'], 'table' => ['contact_communication'], 'where' => ['contact_id = ?'], 'data' => [$oldContactId]]);
        if (!empty($communicationMeans)) {
            $communicationMeans = $communicationMeans[0]['value'];
            if (filter_var($communicationMeans, FILTER_VALIDATE_EMAIL)) {
                $aCommunicationMeans = ['email' => $communicationMeans];
            } elseif (filter_var($communicationMeans, FILTER_VALIDATE_URL)) {
                $aCommunicationMeans = ['url' => $communicationMeans];
            }
        }
        $contactInfo['communication_means'] = !empty($communicationMeans) ? json_encode($aCommunicationMeans) : null;

        // Notes
        $contactInfo['notes'] = !empty($contactInfo['contact_other_data']) ? $contactInfo['contact_other_data'] : $contactInfo['other_data'];
        unset($contactInfo['contact_other_data']);
        unset($contactInfo['other_data']);

        // Creator
        $creator = \User\models\UserModel::getByLogin(['select' => ['id'], 'login' => $contactInfo['user_id']]);
        if (empty($creator)) {
            $contactInfo['creator'] = $firstMan[0]['id'];
        } else {
            $contactInfo['creator'] = $creator['id'];
        }
        unset($contactInfo['user_id']);

        // Modification date
        $contactInfo['modification_date'] = $contactInfo['update_date'];
        unset($contactInfo['update_date']);

        // Enabled
        $contactInfo['enabled'] = $contactInfo['enabled'] == 'Y' ? 'true' : 'false';

        $contactCustomInfo = [
            'salutation_header'     => $contactInfo['salutation_header'],
            'salutation_footer'     => $contactInfo['salutation_footer'],
            'website'               => $contactInfo['website'],
            'contact_type_label'    => $contactInfo['contact_type_label'],
            'contact_purpose_label' => $contactInfo['contact_purpose_label'],
            'society_short'         => $contactInfo['society_short'],
        ];

        unset($contactInfo['salutation_header']);
        unset($contactInfo['salutation_footer']);
        unset($contactInfo['website']);
        unset($contactInfo['contact_type_label']);
        unset($contactInfo['contact_purpose_label']);
        unset($contactInfo['society_short']);
        $id = \Contact\models\ContactModel::create($contactInfo);

        migrateCustomField(['newContactId' => $id, 'contactCustomInfo' => $contactCustomInfo, 'newCustomFields' => $newCustomFields]);
        migrateAcknowledgementReceipt(['oldAddressId' => $oldAddressId, 'newContactId' => $id]);
        migrateContactGroupsLists(['oldAddressId' => $oldAddressId, 'newContactId' => $id]);
        migrateContactRes(['oldAddressId' => $oldAddressId, 'oldContactId' => $oldContactId, 'newContactId' => $id]);
        migrateResourceContacts(['oldAddressId' => $oldAddressId, 'newContactId' => $id]);
        migrateResAttachments(['oldAddressId' => $oldAddressId, 'oldContactId' => $oldContactId, 'newContactId' => $id]);
        migrateResletterbox(['oldAddressId' => $oldAddressId, 'newContactId' => $id]);

        $migrated++;
    }

    migrateContactRes_Users();
    migrateResletterbox_Users();
    migrateResattachments_Users();
    migrateContactParameters();
    migrateContactPrivileges();
    \SrcCore\models\DatabaseModel::update([
        'set'   => ['type' => 'contact'],
        'table' => 'resource_contacts',
        'where' => ['type = ?'],
        'data'  => ['contact_v3']
    ]);

    printf("Migration version attachement (CUSTOM {$custom}) : " . $migrated . " Contact(s) trouvée(s) et migrée(s).\n");
}

function addCustomFields($args = [])
{
    $fillingValues = \SrcCore\models\DatabaseModel::select([
        'select' => ['rating_columns'],
        'table'  => ['contacts_filling']
    ]);

    $fillingValues = json_decode($fillingValues[0]['rating_columns']);

    $customFields = [];
    foreach ($args['customFields'] as $value) {
        $customFieldId = \Contact\models\ContactCustomFieldListModel::create([
            'label'  => $value['label'],
            'type'   => $value['type'],
            'values' => json_encode($value['value'])
        ]);

        $filling = 'false';
        if (in_array($value['oldId'], $fillingValues)) {
            $filling = 'true';
        }
        \SrcCore\models\DatabaseModel::insert([
            'table'         => 'contacts_parameters',
            'columnsValues' => [
                'identifier'  => 'contactCustomField_' . $customFieldId,
                'mandatory'   => 'false',
                'filling'     => $filling,
                'searchable'  => 'false',
                'displayable' => 'false',
            ]
        ]);
        $customFields[$value['oldId']] = $customFieldId;
    }
    
    return $customFields;
}

function migrateCustomField($args = [])
{
    foreach ($args['contactCustomInfo'] as $key => $value) {
        if (!empty($value)) {
            $contact = \Contact\models\ContactModel::getById(['id' => $args['newContactId'], 'select' => ['custom_fields']]);
            $value = json_encode($value);
            if (empty($contact['custom_fields'])) {
                \Contact\models\ContactModel::update([
                    'postSet' => ['custom_fields' => "jsonb_set('{}', '{{$args['newCustomFields'][$key]}}', '{$value}')"],
                    'where' => ['id = ?'],
                    'data' => [$args['newContactId']]
                ]);
            } else {
                \Contact\models\ContactModel::update([
                    'postSet' => ['custom_fields' => "jsonb_set(custom_fields, '{{$args['newCustomFields'][$key]}}', '{$value}')"],
                    'where' => ['id = ?'],
                    'data' => [$args['newContactId']]
                ]);
            }
        }
    }
}

function migrateAcknowledgementReceipt($args = [])
{
    \SrcCore\models\DatabaseModel::update([
        'set'   => ['contact_id' => $args['newContactId']],
        'table' => 'acknowledgement_receipts',
        'where' => ['contact_address_id = ?'],
        'data'  => [$args['oldAddressId']]
    ]);
}

function migrateContactGroupsLists($args = [])
{
    \SrcCore\models\DatabaseModel::update([
        'set'   => ['contact_id' => $args['newContactId']],
        'table' => 'contacts_groups_lists',
        'where' => ['contact_addresses_id = ?'],
        'data'  => [$args['oldAddressId']]
    ]);
}

function migrateContactRes($args = [])
{
    $contactRes = \SrcCore\models\DatabaseModel::select([
        'select' => ['res_id'],
        'table'  => ['contacts_res'],
        'where'  => ['contact_id = ?', 'address_id = ?'],
        'data'  => [$args['oldContactId'], $args['oldAddressId']],
    ]);

    foreach ($contactRes as $value) {
        $resInfo = \SrcCore\models\DatabaseModel::select([
            'select' => ['category_id'],
            'table'  => ['res_letterbox'],
            'where'  => ['res_id = ?'],
            'data'   => [$value['res_id']]
        ]);

        $mode = 'sender';
        if ($resInfo[0]['category_id'] == 'outgoing') {
            $mode = 'recipient';
        }

        \Resource\models\ResourceContactModel::create([
            'res_id'   => $value['res_id'],
            'item_id'  => $args['newContactId'],
            'type'     => 'contact_v3',
            'mode'     => $mode
        ]);
    }
}

function migrateResourceContacts($args = [])
{
    \SrcCore\models\DatabaseModel::update([
        'set'   => ['item_id' => $args['newContactId'], 'type' => 'contact_v3'],
        'table' => 'resource_contacts',
        'where' => ['item_id = ?', 'type = ?'],
        'data'  => [$args['oldAddressId'], 'contact']
    ]);
}

function migrateResAttachments($args = [])
{
    \SrcCore\models\DatabaseModel::update([
        'set'   => ['recipient_id' => $args['newContactId'], 'recipient_type' => 'contact'],
        'table' => 'res_attachments',
        'where' => ['dest_contact_id = ?', 'dest_address_id = ?'],
        'data'  => [$args['oldContactId'], $args['oldAddressId']],
    ]);
}

function migrateResletterbox($args = [])
{
    $resInfo = \SrcCore\models\DatabaseModel::select([
        'select' => ['res_id', 'category_id'],
        'table'  => ['res_letterbox'],
        'where'  => ['address_id = ?'],
        'data'   => [$args['oldAddressId']],
    ]);

    foreach ($resInfo as $value) {
        $mode = 'sender';
        if ($value['category_id'] == 'outgoing') {
            $mode = 'recipient';
        }

        \Resource\models\ResourceContactModel::create([
            'res_id'   => $value['res_id'],
            'item_id'  => $args['newContactId'],
            'type'     => 'contact_v3',
            'mode'     => $mode
        ]);
    }
}

function migrateContactRes_Users()
{
    $userContactRes = \SrcCore\models\DatabaseModel::select([
        'select' => ['res_id', 'contact_id'],
        'table'  => ['contacts_res'],
        'where'  => ['address_id = 0']
    ]);

    $firstMan = \User\models\UserModel::get(['select' => ['id'], 'orderBy' => ['id'], 'limit' => 1, 'where' => ['status = ?'], 'data' => ['OK']]);
    foreach ($userContactRes as $value) {
        $resInfo = \SrcCore\models\DatabaseModel::select([
            'select' => ['category_id'],
            'table'  => ['res_letterbox'],
            'where'  => ['res_id = ?'],
            'data'   => [$value['res_id']]
        ]);

        $user = \User\models\UserModel::getByLogin(['login' => $value['contact_id'], 'select' => ['id']]);
        if (empty($user)) {
            $user = $firstMan[0]['id'];
        } else {
            $user = $user['id'];
        }

        $mode = 'sender';
        if ($resInfo[0]['category_id'] == 'outgoing') {
            $mode = 'recipient';
        }

        \Resource\models\ResourceContactModel::create([
            'res_id'   => $value['res_id'],
            'item_id'  => $user,
            'type'     => 'user',
            'mode'     => $mode
        ]);
    }
}

function migrateResletterbox_Users()
{
    $userContact = \SrcCore\models\DatabaseModel::select([
        'select' => ['res_id', 'exp_user_id', 'dest_user_id'],
        'table'  => ['res_letterbox'],
        'where'  => ['(exp_user_id != \'\' and exp_user_id is not null) or (dest_user_id != \'\' and dest_user_id is not null)']
    ]);

    $firstMan = \User\models\UserModel::get(['select' => ['id'], 'orderBy' => ['id'], 'limit' => 1, 'where' => ['status = ?'], 'data' => ['OK']]);
    foreach ($userContact as $value) {
        if (!empty($value['exp_user_id'])) {
            $login = $value['exp_user_id'];
            $mode = 'sender';
        } else {
            $login = $value['dest_user_id'];
            $mode = 'recipient';
        }
        $user = \User\models\UserModel::getByLogin(['login' => $login, 'select' => ['id']]);
        if (empty($user)) {
            $user = $firstMan[0]['id'];
        } else {
            $user = $user['id'];
        }

        \Resource\models\ResourceContactModel::create([
            'res_id'   => $value['res_id'],
            'item_id'  => $user,
            'type'     => 'user',
            'mode'     => $mode
        ]);
    }
}

function migrateResattachments_Users()
{
    $attachments = \SrcCore\models\DatabaseModel::select([
        'select' => ['dest_user', 'res_id'],
        'table'  => ['res_attachments'],
        'where'  => ['dest_user != \'\' and dest_user is not null']
    ]);

    $firstMan = \User\models\UserModel::get(['select' => ['id'], 'orderBy' => ['id'], 'limit' => 1, 'where' => ['status = ?'], 'data' => ['OK']]);
    foreach ($attachments as $value) {
        $user = \User\models\UserModel::getByLogin(['login' => $value['dest_user'], 'select' => ['id']]);
        if (empty($user)) {
            $user = $firstMan[0]['id'];
        } else {
            $user = $user['id'];
        }

        \SrcCore\models\DatabaseModel::update([
            'set'   => ['recipient_id' => $user, 'recipient_type' => 'user'],
            'table' => 'res_attachments',
            'where' => ['res_id = ?'],
            'data'  => [$value['res_id']],
        ]);
    }
}

function migrateContactParameters()
{
    $fillingValues = \SrcCore\models\DatabaseModel::select([
        'select' => ['rating_columns'],
        'table'  => ['contacts_filling']
    ]);

    $fillingValues = json_decode($fillingValues[0]['rating_columns']);

    $contactParameters = [
        ['oldIdentifier' => 'title',                'identifier' => 'civility',            'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'false', 'displayable' => 'false'],
        ['oldIdentifier' => 'firstname',            'identifier' => 'firstname',           'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'true', 'displayable' => 'true'],
        ['oldIdentifier' => 'lastname',             'identifier' => 'lastname',            'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'true', 'displayable' => 'true'],
        ['oldIdentifier' => 'society',              'identifier' => 'company',             'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'true', 'displayable' => 'true'],
        ['oldIdentifier' => 'departement',          'identifier' => 'department',          'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'false', 'displayable' => 'false'],
        ['oldIdentifier' => 'function',             'identifier' => 'function',            'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'false', 'displayable' => 'false'],
        ['oldIdentifier' => 'address_num',          'identifier' => 'address_number',      'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'true', 'displayable' => 'true'],
        ['oldIdentifier' => 'address_street',       'identifier' => 'address_street',      'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'true', 'displayable' => 'true'],
        ['oldIdentifier' => 'occupancy',            'identifier' => 'address_additional1', 'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'false', 'displayable' => 'false'],
        ['oldIdentifier' => 'address_complement',   'identifier' => 'address_additional2', 'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'false', 'displayable' => 'false'],
        ['oldIdentifier' => 'address_postal_code',  'identifier' => 'address_postcode',    'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'false', 'displayable' => 'false'],
        ['oldIdentifier' => 'address_town',         'identifier' => 'address_town',        'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'true', 'displayable' => 'true'],
        ['oldIdentifier' => 'address_country',      'identifier' => 'address_country',     'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'false', 'displayable' => 'false'],
        ['oldIdentifier' => 'email',                'identifier' => 'email',               'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'false', 'displayable' => 'false'],
        ['oldIdentifier' => 'phone',                'identifier' => 'phone',               'mandatory' => 'false', 'filling' => 'false', 'searchable' => 'false', 'displayable' => 'false'],
    ];
    
    foreach ($contactParameters as $value) {
        $filling = 'false';
        if (in_array($value['oldIdentifier'], ['lastname', 'society']) || in_array($value['oldIdentifier'], $fillingValues)) {
            $filling = 'true';
        }
        \SrcCore\models\DatabaseModel::insert([
            'table'         => 'contacts_parameters',
            'columnsValues' => [
                'identifier'  => $value['identifier'],
                'mandatory'   => $value['mandatory'],
                'filling'     => $filling,
                'searchable'  => $value['searchable'],
                'displayable' => $value['displayable'],
            ]
        ]);
    }
}

function migrateContactPrivileges()
{
    $usergroupServices = \SrcCore\models\DatabaseModel::select([
        'select' => ['group_id'],
        'table'  => ['usergroups_services'],
        'where'  => ['service_id = ?'],
        'data'   => ['create_contacts']
    ]);

    foreach ($usergroupServices as $usergroupService) {
        $servicesEnabled = \SrcCore\models\DatabaseModel::select([
            'select' => ['group_id'],
            'table'  => ['usergroups_services'],
            'where'  => ['service_id = ?', 'group_id = ?'],
            'data'   => ['update_contacts', $usergroupService['group_id']]
        ]);
        if (empty($servicesEnabled)) {
            \SrcCore\models\DatabaseModel::insert([
                'table'         => 'usergroups_services',
                'columnsValues' => [
                    'service_id' => 'update_contacts',
                    'group_id'   => $usergroupService['group_id']
                ]
            ]);
        }
    }

    foreach (['my_contacts_menu', 'my_contacts'] as $service) {
        $usergroupServices = \SrcCore\models\DatabaseModel::select([
            'select' => ['group_id'],
            'table'  => ['usergroups_services'],
            'where'  => ['service_id = ?'],
            'data'   => [$service]
        ]);
    
        foreach ($usergroupServices as $usergroupService) {
            $servicesEnabled = \SrcCore\models\DatabaseModel::select([
                'select' => ['group_id', 'service_id'],
                'table'  => ['usergroups_services'],
                'where'  => ['service_id = ?', 'group_id = ?'],
                'data'   => ['create_contacts', $usergroupService['group_id']]
            ]);
            if (empty($servicesEnabled)) {
                \SrcCore\models\DatabaseModel::insert([
                    'table'         => 'usergroups_services',
                    'columnsValues' => [
                        'service_id' => 'create_contacts',
                        'group_id'   => $usergroupService['group_id']
                    ]
                ]);
            }
        }

        \SrcCore\models\DatabaseModel::delete([
            'table' => 'usergroups_services',
            'where' => ['service_id = ?'],
            'data'  => [$service]
        ]);
    }
}
