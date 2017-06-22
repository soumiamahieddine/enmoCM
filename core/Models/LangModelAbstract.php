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
    public static function getUserAdministrationLang()
    {
        $aLang = [
            'userModification'      => _ADMIN_USER_MODIFICATION,
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
            'cancel'                => _CANCEL,
            'to'                    => _TO,
            'activateAbs'           => _ACTIVATE_ABSENCE,
            'user'                  => _USER,
            'delete'                => _DELETE,
            'autoLogout'            => _AUTO_LOGOUT_AFTER_BASKETS_REDIRECTIONS
        ];

        return $aLang;
    }

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
            'cancel'                => _CANCEL,
            'to'                    => _TO,
            'activateAbs'           => _ACTIVATE_ABSENCE,
            'user'                  => _USER,
            'delete'                => _DELETE,
            'autoLogout'            => _AUTO_LOGOUT_AFTER_BASKETS_REDIRECTIONS
        ];

        return $aLang;
    }

    public static function getSignatureBookLang()
    {
        $aLang = [
            'mail'              => _DEFINE_MAIL,
            'notes'             => _NOTES,
            'visaWorkflow'      => _VISA_WORKFLOW,
            'progression'       => _PROGRESSION,
            'links'             => _LINK_TAB,
            'linkDetails'       => _ACCESS_TO_DETAILS,
            'validate'          => _VALIDATE,
            'chrono'            => _CHRONO_NUMBER,
            'olyChrono'         => _CHRONO,
            'object'            => _OBJECT,
            'contactInfo'       => _CONTACT_INFO,
            'arrDate'           => _RECEIVING_DATE,
            'processLimitDate'  => _PROCESS_LIMIT_DATE,
            'mailAttachments'   => _SB_INCOMING_MAIL_ATTACHMENTS,
            'dlAttachment'      => _DOWNLOAD_ATTACHMENT,
            'signed'            => _SIGNED,
            'for'               => _DEFINE_FOR,
            'createBy'          => _CREATE_BY,
            'createOn'          => _CREATED_ON,
            'back'              => _BASK_BACK,
            'details'           => _PROPERTIES,
            'draft'             => _DRAFT,
            'createAtt'         => _CREATE_PJ,
            'updateAtt'         => _UPDATE_ATTACHMENT,
            'deleteAtt'         => _DELETE_ATTACHMENT,
            'displayAtt'        => _DISPLAY_ATTACHMENTS,
        ];

        return $aLang;
    }

    public static function getStatusLang()
    {
        $aLang = [
            'id'          => _IDENTIFIANT,
            'description' => _DESCRIPTION,
        ];
        return $aLang;
    }

    public static function getUsersForAdministrationLang()
    {
        $aLang = [
            'myProfile'             => _MY_INFO,
            'back'                  => _BASK_BACK,
            'addUser'               => _ADD_USER,
            'lastname'              => _LASTNAME,
            'firstname'             => _FIRSTNAME,
            'identifier'            => _ID,
            'status'                => _STATUS,
            'mail'                  => _MAIL,
            'enabled'               => _VISIBLE,
            'disabled'              => _NOT_VISIBLE,
            'absent'                => _MISSING,
            'edit'                  => _MODIFY,
            'suspend'               => _SUSPEND,
            'delete'                => _DELETE,
            'users'                 => _USERS,
            'admin'                 => _ADMIN,
            'noResult'              => _NO_RESULTS,
            'noRecord'              => _NO_RECORD,
            'previous'              => _PREVIOUS_PAGE,
            'next'                  => _NEXT_PAGE,
            'record'                => _RECORD,
            'search'                => _SEARCH
        ];

        return $aLang;
    }

}
