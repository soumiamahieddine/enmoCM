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
        'where' => ['id > 1']
    ]);
    \SrcCore\models\DatabaseModel::delete([
        'table' => 'contacts_custom_fields',
        'where' => ['id > 1']
    ]);
    \SrcCore\models\DatabaseModel::delete([
        'table' => 'contacts_custom_fields_list',
        'where' => ['id > 1']
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

    //RM
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
    $contactTypes = array_column($contactTypes, 'label');

    $customFields= [
        ['oldId' => 'salutation_header',     'label' => 'Formule de politesse (Début)',     'type' => 'string',     'value' => ['']],
        ['oldId' => 'salutation_footer',     'label' => 'Formule de politesse (Fin)',       'type' => 'string',     'value' => ['']],
        ['oldId' => 'website',               'label' => 'Site internet',                    'type' => 'string',     'value' => ['']],
        ['oldId' => 'contact_type_label',    'label' => 'Type de contact',                  'type' => 'select',     'value' => $contactTypes],
        ['oldId' => 'contact_purpose_label', 'label' => 'Dénomination',                     'type' => 'string',     'value' => $contactPurposes],
        ['oldId' => 'society_short',         'label' => 'Sigle de la structure',            'type' => 'string',     'value' => ['']],
    ];

    $newCustomFields = addCustomFields(['customFields' => $customFields]);

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
            $firstMan = \User\models\UserModel::get(['select' => ['id'], 'orderBy' => ['id'], 'limit' => 1, 'where' => ['status = ?'], 'data' => ['OK']]);
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

        $id = \Contact\models\ContactModel::create($contactInfo);

        $contactCustomInfo= [
            'salutation_header' =>     $contactInfo['salutation_header'],
            'salutation_footer' =>     $contactInfo['salutation_footer'],
            'website' =>               $contactInfo['website'],
            'contact_type_label' =>    $contactInfo['contact_type_label'],
            'contact_purpose_label' => $contactInfo['contact_purpose_label'],
            'society_short' =>         $contactInfo['society_short'],
        ];

        migrateCustomField(['newContactId' => $id, 'contactCustomInfo' => $contactCustomInfo, 'newCustomFields' => $newCustomFields]);

        $migrated++;
    }

    printf("Migration version attachement (CUSTOM {$custom}) : " . $migrated . " Version(s) trouvée(s) et migrée(s).\n");
}

function addCustomFields($args = [])
{
    $customFields = [];
    foreach ($args['customFields'] as $value) {
        $customFieldId = \Contact\models\ContactCustomFieldListModel::create([
            'label'  => $value['label'],
            'type'   => $value['type'],
            'values' => json_encode($value['value'])
        ]);
        $customFields[$customFields['oldId']] = $customFieldId;
    }
    
    return $customFields;
}

function migrateCustomField($args = [])
{
    foreach ($args['contactCustomInfo'] as $key => $value) {
        \Contact\models\ContactCustomFieldModel::create([
            'contactId'  => $args['newContactId'],
            'custom_field_id'   => $args['newCustomFields'][$key],
            'value' => json_encode($value)
        ]);
    }
}
