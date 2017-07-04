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

use Entities\Models\EntityModel;

require_once 'apps/maarch_entreprise/services/Table.php';

class UserModelAbstract extends \Apps_Table_Service
{
    public static function get(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['where', 'data']);
        static::checkArray($aArgs, ['where', 'data']);

        $aUsers = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data']
        ]);

        return $aUsers;
    }

    public static function getById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkNumeric($aArgs, ['id']);

        $aUser = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        if (empty($aUser)) {
            return [];
        }

        return $aUser[0];
    }

    public static function create(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['user']);
        static::checkRequired($aArgs['user'], ['userId', 'firstname', 'lastname']);
        static::checkString($aArgs['user'], ['userId', 'firstname', 'lastname', 'mail', 'initials', 'thumbprint', 'phone']);

        parent::insertInto(
            [
                'user_id'           => $aArgs['user']['userId'],
                'firstname'         => $aArgs['user']['firstname'],
                'lastname'          => $aArgs['user']['lastname'],
                'mail'              => $aArgs['user']['mail'],
                'phone'             => $aArgs['user']['phone'],
                'initials'          => $aArgs['user']['initials'],
                'thumbprint'        => $aArgs['user']['thumbprint'],
                'enabled'           => 'Y',
                'status'            => 'OK',
                'change_password'   => 'Y',
                'loginmode'         => 'standard',
                'password'          => SecurityModel::getPasswordHash('maarch')
            ],
            'users'
        );

        return true;
    }

    public static function update(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['user', 'userId']);
        static::checkRequired($aArgs['user'], ['firstname', 'lastname']);
        static::checkString($aArgs['user'], ['firstname', 'lastname', 'mail', 'initials', 'thumbprint', 'phone', 'status', 'enabled']);

        $isUpdated = parent::update([
            'table'     => 'users',
            'set'       => [
                'firstname'     => $aArgs['user']['firstname'],
                'lastname'      => $aArgs['user']['lastname'],
                'mail'          => $aArgs['user']['mail'],
                'phone'         => $aArgs['user']['phone'],
                'initials'      => $aArgs['user']['initials'],
                'status'        => $aArgs['user']['status'],
                'enabled'       => $aArgs['user']['enabled'],
                'thumbprint'    => $aArgs['user']['thumbprint']
            ],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        return $isUpdated;
    }

    public static function delete(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);

        $isUpdated = parent::update([
            'table'     => 'users',
            'set'       => [
                'status'        => 'DEL',
            ],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        return $isUpdated;
    }

    public static function getByUserId(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);

        $aUser = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        if (empty($aUser)) {
            return [];
        }

        return $aUser[0];
    }

    public static function getByEntities(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['entities']);
        static::checkArray($aArgs, ['entities']);

        $aUsers = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users, users_entities'],
            'where'     => ['users.user_id = users_entities.user_id', 'users_entities.entity_id in (?)'],
            'data'      => [$aArgs['entities']]
        ]);

        return $aUsers;
    }

    public static function getByEmail(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['mail']);
        static::checkString($aArgs, ['mail']);

        $aUser = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => ['mail = ? and status = ?'],
            'data'      => [$aArgs['mail'], 'OK'],
            'limit'     => 1
        ]);

        return $aUser;
    }

    public static function updatePassword(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId', 'password']);
        static::checkString($aArgs, ['userId', 'password']);

        $isUpdated = parent::update([
            'table'     => 'users',
            'set'       => [
                'password'  => SecurityModel::getPasswordHash($aArgs['password'])
            ],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        return $isUpdated;
    }

    public static function resetPassword(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);

        $isUpdated = parent::update([
            'table'     => 'users',
            'set'       => [
                'password'  => SecurityModel::getPasswordHash('maarch')
            ],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        return $isUpdated;
    }

    public static function checkPassword(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId', 'password']);
        static::checkString($aArgs, ['userId', 'password']);

        $aReturn = parent::select([
            'select'    => ['password'],
            'table'     => 'users',
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        if ($aReturn[0]['password'] === SecurityModel::getPasswordHash($aArgs['password'])) {
            return true;
        } else {
            return false;
        }
    }

    public static function createSignature(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userSerialId', 'signatureLabel', 'signaturePath', 'signatureFileName']);
        static::checkString($aArgs, ['signatureLabel', 'signaturePath', 'signatureFileName']);
        static::checkNumeric($aArgs, ['userSerialId']);

        parent::insertInto(
            [
                'user_serial_id'        => $aArgs['userSerialId'],
                'signature_label'       => $aArgs['signatureLabel'],
                'signature_path'        => $aArgs['signaturePath'],
                'signature_file_name'   => $aArgs['signatureFileName']
            ],
            'user_signatures'
        );

        return true;
    }

    public static function updateSignature(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['signatureId', 'userSerialId', 'label']);
        static::checkString($aArgs, ['label']);
        static::checkNumeric($aArgs, ['signatureId', 'userSerialId']);

        parent::update([
            'table'     => 'user_signatures',
            'set'       => [
                'signature_label'   => $aArgs['label']
            ],
            'where'     => ['user_serial_id = ?', 'id = ?'],
            'data'      => [$aArgs['userSerialId'], $aArgs['signatureId']]
        ]);

        return true;
    }

    public static function deleteSignature(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['signatureId', 'userSerialId']);
        static::checkNumeric($aArgs, ['signatureId', 'userSerialId']);

        parent::deleteFrom([
            'table'     => 'user_signatures',
            'where'     => ['user_serial_id = ?', 'id = ?'],
            'data'      => [$aArgs['userSerialId'], $aArgs['signatureId']],
        ]);

        return true;
    }

    public static function createEmailSignature(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId', 'title', 'htmlBody']);
        static::checkString($aArgs, ['userId', 'title', 'htmlBody']);

        parent::insertInto(
            [
                'user_id'   => $aArgs['userId'],
                'title'     => $aArgs['title'],
                'html_body' => $aArgs['htmlBody']
            ],
            'users_email_signatures'
        );

        return true;
    }

    public static function updateEmailSignature(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id','userId', 'title', 'htmlBody']);
        static::checkString($aArgs, ['userId', 'title', 'htmlBody']);
        static::checkNumeric($aArgs, ['id']);

        parent::update([
            'table'     => 'users_email_signatures',
            'set'       => [
                'title'     => $aArgs['title'],
                'html_body' => $aArgs['htmlBody'],
            ],
            'where'     => ['user_id = ?', 'id = ?'],
            'data'      => [$aArgs['userId'], $aArgs['id']]
        ]);

        return true;
    }

    public static function deleteEmailSignature(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id', 'userId']);
        static::checkString($aArgs, ['userId']);

        parent::deleteFrom([
            'table'     => 'users_email_signatures',
            'where'     => ['user_id = ?', 'id = ?'],
            'data'      => [$aArgs['userId'], $aArgs['id']]
        ]);

        return true;
    }

    public static function getSignaturesById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkNumeric($aArgs, ['id']);

        $aReturn = static::select([
            'select'    => ['id', 'user_serial_id', 'signature_label', 'signature_path', 'signature_file_name'],
            'table'     => ['user_signatures'],
            'where'     => ['user_serial_id = ?'],
            'data'      => [$aArgs['id']],
            'order_by'  => 'id'
        ]);

        if (!empty($aReturn)) {
            $docserver = DocserverModel::getByTypeId(['docserver_type_id' => 'TEMPLATES', 'select' => ['path_template']]);
        }

        foreach($aReturn as $key => $value) {
            $pathToSignature = $docserver[0]['path_template'] . str_replace('#', '/', $value['signature_path']) . $value['signature_file_name'];

            $extension = explode('.', $pathToSignature);
            $extension = $extension[count($extension) - 1];
            $fileNameOnTmp = 'tmp_file_' . $aArgs['id'] . '_' . rand() . '.' . strtolower($extension);
            $filePathOnTmp = $_SESSION['config']['tmppath'] . $fileNameOnTmp; // TODO No Session
            if (file_exists($pathToSignature) && copy($pathToSignature, $filePathOnTmp)) {
                $aReturn[$key]['pathToSignatureOnTmp'] = $_SESSION['config']['businessappurl'] . '/tmp/' . $fileNameOnTmp; // TODO No Session
            } else {
                $aReturn[$key]['pathToSignatureOnTmp'] = '';
            }
            $aReturn[$key]['pathToSignature'] = $pathToSignature;

            unset($aReturn[$key]['signature_path'], $aReturn[$key]['signature_file_name']);
        }

        return $aReturn;
    }

    public static function getSignatureWithSignatureIdById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id', 'signatureId']);
        static::checkNumeric($aArgs, ['id','signatureId']);

        $aReturn = static::select([
            'select'    => ['id', 'user_serial_id', 'signature_label'],
            'table'     => ['user_signatures'],
            'where'     => ['user_serial_id = ?', 'id = ?'],
            'data'      => [$aArgs['id'], $aArgs['signatureId']],
        ]);

        return $aReturn[0];
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
            'order_by'  => 'id'
        ]);

        return $aReturn;
    }

    public static function getEmailSignatureWithSignatureIdById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId', 'signatureId']);
        static::checkString($aArgs, ['userId']);
        static::checkNumeric($aArgs, ['signatureId']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users_email_signatures'],
            'where'     => ['user_id = ?', 'id = ?'],
            'data'      => [$aArgs['userId'], $aArgs['signatureId']],
        ]);

        return $aReturn[0];
    }

    public static function getLabelledUserById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);


        $rawUser = static::getById(['userId' => $aArgs['id'], 'select' => ['firstname', 'lastname']]);

        $labelledUser = '';
        if (!empty($rawUser)) {
            $labelledUser = $rawUser['firstname']. ' ' .$rawUser['lastname'];
        }

        return $labelledUser;
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

    public static function getPrimaryGroupById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);


        $aGroup = static::select([
            'select'    => ['usergroup_content.group_id', 'usergroups.group_desc'],
            'table'     => ['usergroup_content, usergroups'],
            'where'     => ['usergroup_content.group_id = usergroups.group_id', 'usergroup_content.user_id = ?', 'usergroup_content.primary_group = ?'],
            'data'      => [$aArgs['userId'], 'Y']
        ]);

        if (empty($aGroup[0])) {
            return [];
        }

        return $aGroup[0];
    }

    public static function getPrimaryEntityById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);

        $aEntity = static::select([
            'select'    => ['users_entities.entity_id', 'entities.entity_label', 'users_entities.user_role', 'users_entities.primary_entity'],
            'table'     => ['users_entities, entities'],
            'where'     => ['users_entities.entity_id = entities.entity_id', 'users_entities.user_id = ?', 'users_entities.primary_entity = ?'],
            'data'      => [$aArgs['userId'], 'Y']
        ]);

        if (empty($aEntity[0])) {
            return [];
        }

        return $aEntity[0];
    }

    public static function getGroupsByUserId(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);


        $aGroups = static::select([
            'select'    => ['usergroup_content.group_id', 'usergroups.group_desc', 'usergroup_content.primary_group', 'usergroup_content.role'],
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
            'select'    => ['users_entities.entity_id', 'entities.entity_label', 'users_entities.user_role', 'users_entities.primary_entity'],
            'table'     => ['users_entities, entities'],
            'where'     => ['users_entities.entity_id = entities.entity_id', 'users_entities.user_id = ?'],
            'data'      => [$aArgs['userId']],
            'order_by'  => 'users_entities.primary_entity DESC'
        ]);

        return $aEntities;
    }

    public static function getServicesById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);


        $aServices = static::select([
            'select'    => ['usergroups_services.service_id'],
            'table'     => ['usergroup_content, usergroups_services'],
            'where'     => ['usergroup_content.group_id = usergroups_services.group_id', 'usergroup_content.user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        return $aServices;
    }

    public static function activateAbsenceById(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);


        parent::update([
            'table'     => 'users',
            'set'       => [
                'status'    => 'ABS'
            ],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        return true;
    }

    public static function hasGroup(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId', 'groupId']);
        static::checkString($aArgs, ['userId', 'groupId']);


        $groups = self::getGroupsByUserId(['userId' => $aArgs['userId']]);
        foreach ($groups as $value) {
            if ($value['group_id'] == $aArgs['groupId']) {
                return true;
            }
        }

        return false;
    }

    public static function addGroup(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId', 'groupId']);
        static::checkString($aArgs, ['userId', 'groupId', 'role']);


        parent::insertInto(
            [
                'user_id'       => $aArgs['userId'],
                'group_id'      => $aArgs['groupId'],
                'role'          => $aArgs['role'],
                'primary_group' => 'Y'
            ],
            'usergroup_content'
        );

        return true;
    }

    public static function updateGroup(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId', 'groupId']);
        static::checkString($aArgs, ['userId', 'groupId', 'role']);


        parent::update([
            'table'     => 'usergroup_content',
            'set'       => [
                'role'      => $aArgs['role']
            ],
            'where'     => ['user_id = ?', 'group_id = ?'],
            'data'      => [$aArgs['userId'], $aArgs['groupId']]
        ]);

        return true;
    }

    public static function deleteGroup(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId', 'groupId']);
        static::checkString($aArgs, ['userId', 'groupId']);


        parent::deleteFrom([
            'table'     => 'usergroup_content',
            'where'     => ['group_id = ?', 'user_id = ?'],
            'data'      => [$aArgs['groupId'], $aArgs['userId']]
        ]);

        return true;
    }

    public static function hasEntity(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId', 'entityId']);
        static::checkString($aArgs, ['userId', 'entityId']);


        $entities = self::getEntitiesById(['userId' => $aArgs['userId']]);
        foreach ($entities as $value) {
            if ($value['entity_id'] == $aArgs['entityId']) {
                return true;
            }
        }

        return false;
    }

    public static function addEntity(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId', 'entityId', 'primaryEntity']);
        static::checkString($aArgs, ['userId', 'entityId', 'role', 'primaryEntity']);


        parent::insertInto(
            [
                'user_id'           => $aArgs['userId'],
                'entity_id'         => $aArgs['entityId'],
                'user_role'         => $aArgs['role'],
                'primary_entity'    => $aArgs['primaryEntity']
            ],
            'users_entities'
        );

        return true;
    }

    public static function updateEntity(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId', 'entityId']);
        static::checkString($aArgs, ['userId', 'entityId', 'role']);


        parent::update([
            'table'     => 'users_entities',
            'set'       => [
                'user_role'      => $aArgs['role']
            ],
            'where'     => ['user_id = ?', 'entity_id = ?'],
            'data'      => [$aArgs['userId'], $aArgs['entityId']]
        ]);

        return true;
    }

    public static function updatePrimaryEntity(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId', 'entityId']);
        static::checkString($aArgs, ['userId', 'entityId']);


        $entities = EntityModel::getByUserId(['userId' => $aArgs['userId']]);
        foreach ($entities as $entity) {
            if ($entity['primary_entity'] == 'Y') {
                parent::update([
                    'table'     => 'users_entities',
                    'set'       => [
                        'primary_entity'    => 'N'
                    ],
                    'where'     => ['user_id = ?', 'entity_id = ?'],
                    'data'      => [$aArgs['userId'], $entity['entity_id']]
                ]);
            }
        }

        parent::update([
            'table'     => 'users_entities',
            'set'       => [
                'primary_entity'    => 'Y'
            ],
            'where'     => ['user_id = ?', 'entity_id = ?'],
            'data'      => [$aArgs['userId'], $aArgs['entityId']]
        ]);

        return true;
    }

    public static function reassignPrimaryEntity(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId']);
        static::checkString($aArgs, ['userId']);


        $entities = EntityModel::getByUserId(['userId' => $aArgs['userId']]);

        if (!empty($entities[0])) {
            parent::update([
                'table'     => 'users_entities',
                'set'       => [
                    'primary_entity'    => 'Y'
                ],
                'where'     => ['user_id = ?', 'entity_id = ?'],
                'data'      => [$aArgs['userId'], $entities[0]['entity_id']]
            ]);
        }

        return true;
    }

    public static function deleteEntity(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['userId', 'entityId']);
        static::checkString($aArgs, ['userId', 'entityId']);


        parent::deleteFrom([
            'table'     => 'users_entities',
            'where'     => ['entity_id = ?', 'user_id = ?'],
            'data'      => [$aArgs['entityId'], $aArgs['userId']]
        ]);

        return true;
    }

}
