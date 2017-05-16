<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Lang Model
 * @author dev@maarch.org
 * @ingroup core
 */

namespace Core\Models;

class LangModelAbstract
{
    public static function getProfileLang()
    {
        $aLang = [
            'myProfile'             => _MY_INFO,
            'back'                  => _BASK_BACK,
            'manageAbsences'        => _MY_ABS,
            'manageSignatures'      => _MANAGE_SIGNATURES,
            'myGroups'              => _MY_GROUPS,
            'primaryGroup'          => _PRIMARY_GROUP,
            'secondaryGroup'        => _SECONDARY_GROUP,
            'myEntities'            => _MY_ENTITIES,
            'primaryEntity'         => _PRIMARY_ENTITY,
            'secondaryEntity'       => _SECONDARY_ENTITY,
            'myInformations'        => _MY_INFORMATIONS,
            'firstname'             => _FIRSTNAME,
            'lastname'              => _LASTNAME,
            'userId'                => _ID,
            'initials'              => _INITIALS,
            'phoneNumber'           => _PHONE_NUMBER,
            'email'                 => _EMAIL,
            'fingerprint'           => _DIGITAL_FINGERPRINT,
            'changePsw'             => _UPDATE_PSW,
            'currentPsw'            => _CURRENT_PSW,
            'newPsw'                => _NEW_PSW,
            'renewPsw'              => _REENTER_PSW,
            'saveModification'      => _SAVE_MODIFICATION,
            'emailSignatures'       => _EMAIL_SIGNATURES,
            'sbSignatures'          => _SB_SIGNATURES,
            'newSignature'          => _DEFINE_NEW_SIGNATURE,
            'signatureLabel'        => _SIGNATURE_LABEL,
            'updateSignature'       => _UPDATE_SIGNATURE,
            'deleteSignature'       => _DELETE_SIGNATURE,
            'clickOn'               => _CLICK_ON,
            'toSignature'           => _TO_ADD_SIGNATURE,
            'toUpdateSignature'     => _TO_UPDATE_SIGNATURE,
            'cancel'                => _CANCEL
        ];

        return $aLang;
    }
}
