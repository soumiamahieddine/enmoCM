<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ActionController
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Action\controllers;

use Contact\models\ContactModel;
use Resource\models\ResModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

trait ActionMethodTraitAcknowledgementReceipt
{
    public static function createAcknowledgementReceipts(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);


        $ext = ResModel::getExtById(['select' => ['category_id', 'address_id', 'is_multicontacts'], 'resId' => $aArgs['resId']]);

        if (empty($ext) || $ext['category_id'] != 'incoming') {
            return [];
        }

        $contactsToProcess = [];
        if ($ext['is_multicontacts'] == 'Y') {
            $multiContacts = DatabaseModel::select([
                'select'    => ['address_id'],
                'table'     => ['contacts_res'],
                'where'     => ['res_id = ?', 'mode = ?', 'address_id != ?'],
                'data'      => [$aArgs['resId'], 'multi', 0]
            ]);
            foreach ($multiContacts as $multiContact) {
                $contactsToProcess[] = $multiContact['address_id'];
            }
        } else {
            $contactsToProcess[] = $ext['address_id'];
        }

        if (empty($contactsToProcess)) {
            return [];
        }

        foreach ($contactsToProcess as $contactToProcess) {
            $contact = ContactModel::getByAddressId(['addressId' => $contactToProcess, 'select' => ['email', 'address_street', 'address_town', 'address_postal_code']]);

            //TODO check si pas adresse

            if (!empty($contact['email'])) {

            }
        }


        return true;
    }
}
