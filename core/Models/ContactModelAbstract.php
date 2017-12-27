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
* @ingroup core
*/

namespace Core\Models;


class ContactModelAbstract
{
    public static function getById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $aContact = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['contacts_v2'],
            'where'     => ['contact_id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        if (empty($aContact[0])) {
            return [];
        }

        return $aContact[0];
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['firstname', 'lastname', 'contactType', 'isCorporatePerson', 'userId', 'entityId']);
        ValidatorModel::intVal($aArgs, ['contactType']);
        ValidatorModel::stringType($aArgs, [
            'firstname', 'lastname', 'isCorporatePerson', 'society',
            'societyShort', 'title', 'function', 'otherData', 'userId', 'entityId'
        ]);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'contact_v2_id_seq']);

        DatabaseModel::insert([
            'table'         => 'contacts_v2',
            'columnsValues' => [
                'contact_id'            => $nextSequenceId,
                'contact_type'          => $aArgs['contactType'],
                'is_corporate_person'   => $aArgs['isCorporatePerson'],
                'society'               => $aArgs['society'],
                'society_short'         => $aArgs['societyShort'],
                'firstname'             => $aArgs['firstname'],
                'lastname'              => $aArgs['lastname'],
                'title'                 => $aArgs['title'],
                'function'              => $aArgs['function'],
                'other_data'            => $aArgs['otherData'],
                'user_id'               => $aArgs['userId'],
                'entity_id'             => $aArgs['entityId'],
                'creation_date'         => 'CURRENT_TIMESTAMP',
                'enabled'               => 'Y'

            ]
        ]);

        return $nextSequenceId;
    }

    public static function createAddress(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['contactId', 'contactPurposeId', 'userId', 'entityId', 'isPrivate', 'email']);
        ValidatorModel::intVal($aArgs, ['contactId', 'contactPurposeId']);
        ValidatorModel::stringType($aArgs, [
            'departement', 'addressFirstname', 'addressLastname', 'addressTitle', 'addressFunction', 'occupancy', 'addressNum', 'addressStreet', 'addressComplement',
            'addressTown', 'addressZip', 'addressCountry', 'phone', 'email', 'website', 'salutationHeader', 'salutationFooter', 'addressOtherData',
            'userId', 'entityId', 'isPrivate'
        ]);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'contact_addresses_id_seq']);

        DatabaseModel::insert([
            'table'         => 'contact_addresses',
            'columnsValues' => [
                'id'                    => $nextSequenceId,
                'contact_id'            => $aArgs['contactId'],
                'contact_purpose_id'    => $aArgs['contactPurposeId'],
                'departement'           => $aArgs['departement'],
                'firstname'             => $aArgs['addressFirstname'],
                'lastname'              => $aArgs['addressLastname'],
                'title'                 => $aArgs['addressTitle'],
                'function'              => $aArgs['addressFunction'],
                'occupancy'             => $aArgs['occupancy'],
                'address_num'           => $aArgs['addressNum'],
                'address_street'        => $aArgs['addressStreet'],
                'address_complement'    => $aArgs['addressComplement'],
                'address_town'          => $aArgs['addressTown'],
                'address_postal_code'   => $aArgs['addressZip'],
                'address_country'       => $aArgs['addressCountry'],
                'phone'                 => $aArgs['phone'],
                'email'                 => $aArgs['email'],
                'website'               => $aArgs['website'],
                'salutation_header'     => $aArgs['salutationHeader'],
                'salutation_footer'     => $aArgs['salutationFooter'],
                'other_data'            => $aArgs['otherData'],
                'user_id'               => $aArgs['userId'],
                'entity_id'             => $aArgs['entityId'],
                'is_private'            => $aArgs['isPrivate'],
                'enabled'               => 'Y'

            ]
        ]);

        return $nextSequenceId;
    }

    public static function getByAddressId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['contactId', 'addressId']);
        ValidatorModel::intVal($aArgs, ['contactId', 'addressId']);

        $aContact = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['contact_addresses'],
            'where'     => ['id = ?', 'contact_id = ?'],
            'data'      => [$aArgs['addressId'], $aArgs['contactId']],
        ]);

        if (empty($aContact[0])) {
            return [];
        }

        return $aContact[0];
    }

    public static function getLabelledContactWithAddress(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['contactId', 'addressId']);
        ValidatorModel::intVal($aArgs, ['contactId', 'addressId']);

        $rawContact = ContactModel::getByAddressId(['contactId' => $aArgs['contactId'], 'addressId' => $aArgs['addressId'], 'select' => ['firstname', 'lastname']]);

        $labelledContact = '';
        if (!empty($rawContact)) {
            if (empty($rawContact['firstname']) && empty($rawContact['lastname'])) {
                $rawContact = ContactModel::getById(['id' => $aArgs['contactId'], 'select' => ['firstname', 'lastname']]);
            }
            $labelledContact = $rawContact['firstname']. ' ' .$rawContact['lastname'];
        }

        return $labelledContact;
    }

    public static function getByEmail(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['email']);
        ValidatorModel::stringType($aArgs, ['email']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aContacts = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['contact_addresses, contacts_v2'],
            'where'     => ['email = ?', 'contact_addresses.enabled = ?', 'contact_addresses.contact_id = contacts_v2.contact_id'],
            'data'      => [$aArgs['email'], 'Y'],
            'order_by'  => ['creation_date'],
        ]);

        return $aContacts;
    }

    public static function purgeContact($aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $aReturn = DatabaseModel::select([
            'select'    => ['count(*) as count'],
            'table'     => ['res_view_letterbox'],
            'where'     => ['contact_id = ?'],
            'data'      => [$aArgs['id']],
        ]);
        
        $aReturnBis = DatabaseModel::select([
            'select'    => ['count(*) as count'],
            'table'     => ['contacts_res'],
            'where'     => ['contact_id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        if ($aReturn[0]['count'] < 1 && $aReturnBis[0]['count'] < 1) {
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
}
