<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief User Model
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

require_once 'apps/maarch_entreprise/services/Table.php';

class UserModelAbstract extends \Apps_Table_Service
{
    public static function getById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']],
        ]);

        return $aReturn[0];
    }

    public static function getByEmail(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['mail']);
        static::checkString($aArgs, ['mail']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => ['mail = ? and status = ?'],
            'data'      => [$aArgs['mail'], 'OK'],
            'limit'     => 1,
        ]);

        return $aReturn;
    }

    public static function update(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['user']);
        static::checkRequired($aArgs['user'], ['user_id', 'firstname', 'lastname']);
        static::checkString($aArgs['user'], ['user_id', 'firstname', 'lastname', 'mail', 'initials', 'thumbprint', 'phone']);

        $aReturn = parent::update([
            'table'     => 'users',
            'set'       => [
                'firstname'     => $aArgs['user']['firstname'],
                'lastname'      => $aArgs['user']['lastname'],
                'mail'          => $aArgs['user']['mail'],
                'phone'         => $aArgs['user']['phone'],
                'initials'      => $aArgs['user']['initials'],
                'thumbprint'    => $aArgs['user']['thumbprint']
            ],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['user']['user_id']]
        ]);

        return $aReturn;
    }

    public static function getSignaturesById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['user_signatures'],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']],
        ]);

        return $aReturn;
    }

    public static function getEmailSignaturesById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users_email_signatures'],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']],
        ]);

        return $aReturn;
    }

    public static function getLabelledUserById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);


        $rawUser = self::getById(['userId' => $aArgs['id'], 'select' => ['firstname', 'lastname']]);

        $labelledUser = '';
        if (!empty($rawUser)) {
            $labelledUser = $rawUser['firstname']. ' ' .$rawUser['lastname'];
        }

        return $labelledUser;
    }

    public static function getSignatureForCurrentUser()
    {
        //TODO No Session
        if (empty($_SESSION['user']['pathToSignature'][0]) || !file_exists($_SESSION['user']['pathToSignature'][0])) {
            return [];
        }

        $aSignature = [
            'signaturePath' => $_SESSION['user']['signature_path'],
            'signatureFileName' => $_SESSION['user']['signature_file_name'],
            'pathToSignature' => $_SESSION['user']['pathToSignature'][0]
        ];

        $extension = explode('.', $aSignature['pathToSignature']);
        $extension = $extension[count($extension) - 1];
        $fileNameOnTmp = 'tmp_file_' . $_SESSION['user']['UserId'] . '_' . rand() . '.' . strtolower($extension);
        $filePathOnTmp = $_SESSION['config']['tmppath'] . $fileNameOnTmp;
        if (!copy($aSignature['pathToSignature'], $filePathOnTmp)) {
            return $aSignature;
        }

        $aSignature['pathToSignatureOnTmp'] = $_SESSION['config']['businessappurl'] . '/tmp/' . $fileNameOnTmp;

        return $aSignature;
    }

    public static function getCurrentConsigneById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['resId']);
        static::checkNumeric($aArgs, ['resId']);


        $aReturn = static::select([
            'select'    => ['process_comment'],
            'table'     => ['listinstance'],
            'where'     => ['res_id = ?', 'process_date is null', 'item_mode in (?)'],
            'data'      => [$aArgs['resId'], ['visa', 'sign']],
            'order_by'  => 'listinstance_id ASC',
            'limit'     => 1
        ]);

        if (empty($aReturn[0])) {
            return '';
        }

        return $aReturn[0]['process_comment'];
    }

    public static function getGroupsById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);


        $aGroups = static::select([
            'select'    => ['usergroup_content.group_id', 'usergroups.group_desc', 'usergroup_content.primary_group'],
            'table'     => ['usergroup_content, usergroups'],
            'where'     => ['usergroup_content.group_id = usergroups.group_id', 'usergroup_content.user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        return $aGroups;
    }

    public static function getEntitiesById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);


        $aEntities = static::select([
            'select'    => ['users_entities.entity_id', 'entities.entity_label', 'users_entities.primary_entity'],
            'table'     => ['users_entities, entities'],
            'where'     => ['users_entities.entity_id = entities.entity_id', 'users_entities.user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        return $aEntities;
    }
}
