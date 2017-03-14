<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Contacts Model
* @author dev@maarch.org
* @ingroup apps
*/

//namespace Apps\Models\Contacts;

require_once 'apps/maarch_entreprise/services/Table.php';

class ContactsModelAbstract extends Apps_Table_Service
{
    public static function getById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkNumeric($aArgs, ['id']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['contacts_v2'],
            'where'     => ['contact_id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        return $aReturn;
    }

    public static function getWithAddress(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['contactId', 'addressId']);
        static::checkNumeric($aArgs, ['contactId', 'addressId']);


        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['contact_addresses'],
            'where'     => ['id = ?', 'contact_id = ?'],
            'data'      => [$aArgs['addressId'], $aArgs['contactId']],
        ]);

        return $aReturn;
    }

    public static function getLabelledContactWithAddress(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['contactId', 'addressId']);
        static::checkNumeric($aArgs, ['contactId', 'addressId']);


        $rawContact = self::getWithAddress(['contactId' => $aArgs['contactId'], 'addressId' => $aArgs['addressId'], 'select' => ['firstname', 'lastname']]);

        $labelledContact = '';
        if (!empty($rawContact[0])) {
            $labelledContact = $rawContact[0]['firstname']. ' ' .$rawContact[0]['lastname'];
        }

        return $labelledContact;
    }

    public static function getByEmail(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['email']);
        static::checkString($aArgs, ['email']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['view_contacts'],
            'where'     => ['email = ? and enabled = ?'],
            'data'      => [$aArgs['email'], 'Y'],
            'order'     => ['creation_date'],
        ]);

        return $aReturn;
    }
}