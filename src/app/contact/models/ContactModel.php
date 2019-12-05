<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Contact Model
* @author dev@maarch.org
*/

namespace Contact\models;

use Resource\models\ResModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class ContactModel
{
    public static function get(array $args)
    {
        ValidatorModel::notEmpty($args, ['select']);
        ValidatorModel::arrayType($args, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($args, ['limit']);

        $contacts = DatabaseModel::select([
            'select'    => $args['select'],
            'table'     => ['contacts'],
            'where'     => empty($args['where']) ? [] : $args['where'],
            'data'      => empty($args['data']) ? [] : $args['data'],
            'order_by'  => empty($args['orderBy']) ? [] : $args['orderBy'],
            'limit'     => empty($args['limit']) ? 0 : $args['limit']
        ]);

        return $contacts;
    }

    public static function getById(array $args)
    {
        ValidatorModel::notEmpty($args, ['id', 'select']);
        ValidatorModel::intVal($args, ['id']);
        ValidatorModel::arrayType($args, ['select']);

        $contact = DatabaseModel::select([
            'select'    => $args['select'],
            'table'     => ['contacts'],
            'where'     => ['id = ?'],
            'data'      => [$args['id']],
        ]);

        if (empty($contact[0])) {
            return [];
        }

        return $contact[0];
    }

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['creator']);
        ValidatorModel::intVal($args, ['creator']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'contacts_id_seq']);
        $args['id'] = $nextSequenceId;

        DatabaseModel::insert([
            'table'         => 'contacts',
            'columnsValues' => $args
        ]);

        return $nextSequenceId;
    }

    public static function update(array $args)
    {
        ValidatorModel::notEmpty($args, ['set', 'where', 'data']);
        ValidatorModel::arrayType($args, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'contacts',
            'set'   => $args['set'],
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }

    public static function delete(array $args)
    {
        ValidatorModel::notEmpty($args, ['where', 'data']);
        ValidatorModel::arrayType($args, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'contacts',
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }

    public static function getContactCommunication(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['contactId']);
        ValidatorModel::intVal($aArgs, ['contactId']);

        $aReturn = DatabaseModel::select([
            'select'    => ['*'],
            'table'     => ['contact_communication'],
            'where'     => ['contact_id = ?'],
            'data'      => [$aArgs['contactId']],
        ]);

        if (empty($aReturn)) {
            return "";
        } else {
            $aReturn[0]['value'] = trim(trim($aReturn[0]['value']), '/');
            return $aReturn[0];
        }
    }

    public static function getContactIdByCommunicationValue(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['communicationValue']);

        $aReturn = DatabaseModel::select([
            'select'    => ['*'],
            'table'     => ['contact_communication'],
            'where'     => ['value = ?'],
            'data'      => [$aArgs['communicationValue']],
        ]);

        if (empty($aReturn)) {
            return '';
        } else {
            return $aReturn[0];
        }
    }

    public static function createContactCommunication(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['contactId', 'type', 'value']);
        ValidatorModel::intVal($aArgs, ['contactId']);

        DatabaseModel::insert([
            'table' => 'contact_communication',
            'columnsValues' => [
                'contact_id' => $aArgs['contactId'],
                'type'       => $aArgs['type'],
                'value'      => trim(trim($aArgs['value']), '/')
            ]
        ]);

        return true;
    }

    public static function getLabelledContactWithAddress(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['contactId', 'addressId']);
        ValidatorModel::intVal($aArgs, ['contactId', 'addressId']);

        $rawContact = ContactModel::getByAddressId(['addressId' => $aArgs['addressId'], 'select' => ['firstname', 'lastname']]);

        $labelledContact = '';
        if (!empty($rawContact)) {
            if (empty($rawContact['firstname']) && empty($rawContact['lastname'])) {
                $rawContact = ContactModel::getById(['id' => $aArgs['contactId'], 'select' => ['firstname', 'lastname']]);
            }
            $labelledContact = $rawContact['firstname']. ' ' .$rawContact['lastname'];
        }

        return $labelledContact;
    }

    public static function purgeContact($aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $firstCount = ResModel::getOnView([
            'select'    => ['count(*) as count'],
            'where'     => ['contact_id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        $secondCount = DatabaseModel::select([
            'select'    => ['count(*) as count'],
            'table'     => ['contacts_res'],
            'where'     => ['contact_id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        if ($firstCount[0]['count'] < 1 && $secondCount[0]['count'] < 1) {
            DatabaseModel::delete([
                'table' => 'contact_addresses',
                'where' => ['contact_id = ?'],
                'data'  => [$aArgs['id']]
            ]);
            DatabaseModel::delete([
                'table' => 'contacts_v2',
                'where' => ['contact_id = ?'],
                'data'  => [$aArgs['id']]
            ]);
        }
    }

    public static function getByAddressId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['addressId']);
        ValidatorModel::intVal($aArgs, ['addressId']);

        $aContact = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['contact_addresses'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['addressId']],
        ]);

        if (empty($aContact[0])) {
            return [];
        }

        return $aContact[0];
    }

    public static function getCivilities()
    {
        static $civilities;

        if (!empty($civilities)) {
            return $civilities;
        }

        $civilities = [];

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/entreprise.xml']);
        if ($loadedXml != false) {
            $result = $loadedXml->xpath('/ROOT/titles');
            foreach ($result as $title) {
                foreach ($title as $value) {
                    $civilities[(string) $value->id] = [
                        'label'         => (string)$value->label,
                        'abbreviation'  => (string)$value->abbreviation,
                    ];
                }
            }
        }

        return $civilities;
    }

    public static function getCivilityLabel(array $args)
    {
        ValidatorModel::stringType($args, ['civilityId']);

        $civilities = ContactModel::getCivilities();
        if (!empty($civilities[$args['civilityId']])) {
            return $civilities[$args['civilityId']]['label'];
        }

        return '';
    }

    public static function createContactM2M(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['data', 'contactCommunication']);

        $currentContactId    = "0";
        $currentAddressId    = "0";
        $formatedDataContact = [];
        $formatedDataAddress = [];

        $contact_exists = false;
        foreach ($aArgs['data'] as $key => $value) {
            // On regarde si le contact existe déjà
            if (strtoupper($value['column']) == strtoupper('external_id') && ($value['value'] <> "" || $value['value'] <> null)) {
                try {
                    $res = DatabaseModel::select([
                        'select' => ['contact_id', 'ca_id'],
                        'table'  => ['contacts'],
                        'where'  => ["external_id->>'m2m' = ?", 'enabled = ?'],
                        'data'   => [$value['value'], 'Y'],
                    ]);

                    $res = $res[0];
                    if (!empty($res['ca_id'])) {
                        $contact_exists   = true;
                        $currentContactId = $res['contact_id'];
                        $currentAddressId = $res['ca_id'];
                    } else {
                        $contact_exists = false;
                    }
                } catch (\Exception $e) {
                    $returnResArray = [
                        'returnCode'  => (int) -1,
                        'contactId'   => '',
                        'addressId'   => '',
                        'contactInfo' => '',
                        'error'       => 'unknown error: ' . $e->getMessage()
                    ];
                    return $returnResArray;
                }
            }

            $aArgs['data'][$key]['column'] = strtolower($value['column']);

            if ($value['column'] == 'external_id') {
                $formatedDataAddress[$value['column']] = json_encode(['m2m' => $value['value']]);
            } elseif ($value['table'] == "contacts_v2") {
                $formatedDataContact[$value['column']] = $value['value'];
            } elseif ($value['table'] == "contact_addresses") {
                $formatedDataAddress[$value['column']] = $value['value'];
            }
        }

        // Si le contact n'existe pas, on le créé
        if (!$contact_exists) {
            $contactInfo = ContactModel::getContactIdByCommunicationValue(['communicationValue' => $aArgs['contactCommunication']]);
            if (!empty($contactInfo)) {
                $currentContactId = $contactInfo['contact_id'];
            } else {
                try {
                    $currentContactId                     = DatabaseModel::getNextSequenceValue(['sequenceId' => 'contact_v2_id_seq']);
                    $formatedDataContact['user_id']       = 'superadmin';
                    $formatedDataContact['entity_id']     = 'SUPERADMIN';
                    $formatedDataContact['creation_date'] = 'CURRENT_TIMESTAMP';
                    $formatedDataContact['contact_id']    = $currentContactId;

                    DatabaseModel::insert([
                        'table'         => 'contacts_v2',
                        'columnsValues' => $formatedDataContact
                    ]);
                } catch (\Exception $e) {
                    $returnResArray = [
                        'returnCode'  => (int) -1,
                        'contactId'   => 'ERROR',
                        'addressId'   => 'ERROR',
                        'contactInfo' => '',
                        'error'       => 'contact creation error : '. $e->getMessage(),
                    ];
                    
                    return $returnResArray;
                }
            }
            try {
                $currentAddressId                  = DatabaseModel::getNextSequenceValue(['sequenceId' => 'contact_addresses_id_seq']);
                $formatedDataAddress['user_id']    = 'superadmin';
                $formatedDataAddress['entity_id']  = 'SUPERADMIN';
                $formatedDataAddress['contact_id'] = $currentContactId;
                $formatedDataAddress['id']         = $currentAddressId;

                DatabaseModel::insert([
                        'table'         => 'contact_addresses',
                        'columnsValues' => $formatedDataAddress
                    ]);
            } catch (\Exception $e) {
                $returnResArray = [
                    'returnCode'  => (int) -1,
                    'contactId'   => $currentContactId,
                    'addressId'   => 'ERROR',
                    'contactInfo' => '',
                    'error'       => 'address creation error : '. $e->getMessage(),
                ];
                
                return $returnResArray;
            }
            $returnResArray = [
                'returnCode'  => (int) 0,
                'contactId'   => $currentContactId,
                'addressId'   => $currentAddressId,
                'contactInfo' => 'contact created and attached to doc ... ',
                'error'       => ''
            ];
            
            return $returnResArray;
        } else {
            $returnResArray = [
                'returnCode'  => (int) 0,
                'contactId'   => $currentContactId,
                'addressId'   => $currentAddressId,
                'contactInfo' => 'contact already exist, attached to doc ... ',
                'error'       => ''
            ];
            
            return $returnResArray;
        }
    }
}
