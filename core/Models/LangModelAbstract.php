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
    public static function getParametersLang()
    {
        $aLang = [
                'admin'                 => _ADMIN,
                'parameter'             =>  _PARAMETER,
                'parameters'            =>  _PARAMETERS,
                'identifier'            =>  _PARAMETER_IDENTIFIER,
                'description'           =>  _DESCRIPTION,
                'value'                 =>  _VALUE,
                'type'                  =>  _TYPE,
                'string'                =>  _STRING,
                'integer'               =>  _INTEGER,
                'date'                  =>  _DATE,
                'validate'              =>  _VALIDATE,
                'cancel'                =>  _CANCEL,
                'modify'                =>  _MODIFY,
                'delete'                =>  _DELETE,
                'page'                  =>  _PAGE,
                'outOf'                 =>  _OUT_OF,
                'search'                =>  _SEARCH,
                'recordsPerPage'        =>  _RECORDS_PER_PAGE,
                'display'               =>  _DISPLAY,
                'noRecord'              =>  _NO_RECORD,
                'noResult'              =>  _NO_RESULTS,
                'available'             =>  _AVAILABLE,
                'filteredFrom'          =>  _FILTERED_FROM,
                'records'               =>  _RECORDS,
                'record'                =>  _RECORD,
                'first'                 =>  _FIRST,
                'last'                  =>  _LAST,
                'next'                  =>  _NEXT,
                'previous'              =>  _PREVIOUS,
                'paramCreatedSuccess'   =>  _PARAM_CREATED_SUCCESS,
                'paramUpdatedSuccess'   =>  _PARAM_UPDATED_SUCCESS,
                'deleteConfirm'         =>  _DELETE_CONFIRM,
                'controlTechnicalParams'=>  _CONTROL_TECHNICAL_PARAMS
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
    public static function getReportsLang()
    {
        
        $aLang = [
            'folder'               => _FOLDER,
            'entities'             => _ENTITIES,
            'mappli'               => _MAARCH_APPLICATION,
            'group'                => _GROUP,
            'selectGroup'          => _SELECT_GROUP,
            'validate'             => _VALIDATE,
            'cancel'               => _CANCEL

            ];

        return $aLang;
    }

    public static function getUsersAdministrationLang()
    {
        $aLang = [
            'back'                  => _BASK_BACK,
            'addUser'               => _ADD_USER,
            'lastname'              => _LASTNAME,
            'firstname'             => _FIRSTNAME,
            'identifier'            => _ID,
            'status'                => _STATUS,
            'mail'                  => _MAIL,
            'edit'                  => _MODIFY,
            'suspend'               => _SUSPEND,
            'authorize'             => _AUTHORIZE,
            'delete'                => _DELETE,
            'users'                 => _USERS,
            'admin'                 => _ADMIN,
            'noResult'              => _NO_RESULTS,
            'noRecord'              => _NO_RECORD,
            'previous'              => _PREVIOUS_PAGE,
            'next'                  => _NEXT_PAGE,
            'record'                => _RECORD,
            'search'                => _SEARCH,
            'deleteMsg'             => _REALLY_DELETE,
            'suspendMsg'            => _REALLY_SUSPEND,
            'authorizeMsg'          => _REALLY_AUTHORIZE,
            'checkListDiffMsg'      => _PLEASE_CHECK_LISTDIFF,
            'user'                  => _USER,
            'userModification'      => _ADMIN_USER_MODIFICATION,
            'reinitPassword'        => _REINITIALIZE_PASSWORD,
            'manageBaskets'         => _MANAGE_BASKETS,
            'manageAbsences'        => _MANAGE_ABSENCES,
            'manageSignatures'      => _MANAGE_SIGNATURES,
            'primaryEntity'         => _PRIMARY_ENTITY,
            'secondaryEntity'       => _SECONDARY_ENTITY,
            'userId'                => _ID,
            'initials'              => _INITIALS,
            'phoneNumber'           => _PHONE_NUMBER,
            'email'                 => _EMAIL,
            'fingerprint'           => _DIGITAL_FINGERPRINT,
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
            'validate'              => _VALIDATE,
            'cancel'                => _CANCEL,
            'to'                    => _TO,
            'activateAbs'           => _ACTIVATE_ABSENCE,
            'deactivateAbs'         => _DEACTIVATE_ABSENCE,
            'basketToRedirect'      => _CHOOSE_BASKET_TO_REDIRECT,
            'autoLogout'            => _AUTO_LOGOUT_AFTER_BASKETS_REDIRECTIONS,
            'abs'                   => _ABS,
            'active'                => _ACTIVE,
            'inactive'              => _INACTIVE,
        ];

        return $aLang;
    }

}
