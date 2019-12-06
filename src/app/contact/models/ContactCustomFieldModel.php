<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ResourceFolderModel
* @author  dev <dev@maarch.org>
* @ingroup core
*/

/**
 * @brief Contact Custom Field Model
 * @author dev@maarch.org
 */

namespace Contact\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

class ContactCustomFieldModel
{
    public static function get(array $aArgs)
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($aArgs, ['limit']);

        $customFields = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['contacts_custom_fields'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'order_by'  => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $customFields;
    }

    public static function getByContactId(array $args)
    {
        ValidatorModel::notEmpty($args, ['contactId', 'select']);
        ValidatorModel::intVal($args, ['contactId']);
        ValidatorModel::arrayType($args, ['select']);

        $contact = DatabaseModel::select([
            'select'    => $args['select'],
            'table'     => ['contacts_custom_fields'],
            'where'     => ['contact_id = ?'],
            'data'      => [$args['contactId']],
        ]);

        if (empty($contact[0])) {
            return [];
        }

        return $contact[0];
    }

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['contact_id', 'custom_field_id', 'value']);
        ValidatorModel::intVal($args, ['contact_id', 'custom_field_id']);
        ValidatorModel::stringType($args, ['value']);

        DatabaseModel::insert([
            'table'         => 'contacts_custom_fields',
            'columnsValues' => [
                'contact_id'        => $args['contact_id'],
                'custom_field_id'   => $args['custom_field_id'],
                'value'             => $args['value'],
            ]
        ]);

        return true;
    }

    public static function delete(array $args)
    {
        ValidatorModel::notEmpty($args, ['where', 'data']);
        ValidatorModel::arrayType($args, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'contacts_custom_fields',
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }
}
