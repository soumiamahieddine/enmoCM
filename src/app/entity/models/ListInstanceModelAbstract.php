<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief List Instance Model Abstract
 * @author dev@maarch.org
 */

namespace Entity\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

abstract class ListInstanceModelAbstract
{
    public static function get(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy', 'groupBy']);
        ValidatorModel::intType($aArgs, ['limit']);

        $aListInstances = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['listinstance'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'order_by'  => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'groupBy'   => empty($aArgs['groupBy']) ? [] : $aArgs['groupBy'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $aListInstances;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aListinstance = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance'],
            'where'     => ['listinstance_id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        if (empty($aListinstance[0])) {
            return [];
        }

        return $aListinstance[0];
    }

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['res_id', 'item_id', 'item_type', 'item_mode', 'added_by_user', 'added_by_entity', 'difflist_type']);
        ValidatorModel::intVal($args, ['res_id', 'item_id', 'sequence']);
        ValidatorModel::stringType($args, ['item_type', 'item_mode', 'added_by_user', 'added_by_entity', 'difflist_type', 'process_date', 'process_comment']);

        DatabaseModel::insert([
            'table'         => 'listinstance',
            'columnsValues' => [
                'coll_id'                   => 'letterbox_coll',
                'res_id'                    => $args['res_id'],
                'listinstance_type'         => 'DOC',
                'sequence'                  => $args['sequence'],
                'item_id'                   => $args['item_id'],
                'item_type'                 => $args['item_type'],
                'item_mode'                 => $args['item_mode'],
                'added_by_user'             => $args['added_by_user'],
                'added_by_entity'           => $args['added_by_entity'],
                'visible'                   => 'Y',
                'viewed'                    => 0,
                'difflist_type'             => $args['difflist_type'],
                'process_date'              => $args['process_date'],
                'process_comment'           => $args['process_comment']
            ]
        ]);

        return true;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['where', 'data']);
        ValidatorModel::arrayType($aArgs, ['set', 'postSet', 'where', 'data']);

        DatabaseModel::update([
            'table'     => 'listinstance',
            'set'       => $aArgs['set'],
            'postSet'   => $aArgs['postSet'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data']
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['where', 'data']);
        ValidatorModel::arrayType($aArgs, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'listinstance',
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }

    public static function getVisaCircuitByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aListinstance = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance', 'users', 'users_entities', 'entities'],
            'left_join' => ['listinstance.item_id = users.user_id', 'users_entities.user_id = users.user_id', 'entities.entity_id = users_entities.entity_id'],
            'where'     => ['res_id = ?', 'item_type = ?', 'difflist_type = ?', 'primary_entity = ?'],
            'data'      => [$aArgs['id'], 'user_id', 'VISA_CIRCUIT', 'Y'],
            'order_by'  => ['listinstance_id ASC'],
        ]);

        return $aListinstance;
    }

    public static function getAvisCircuitByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aListinstance = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance', 'users', 'users_entities', 'entities'],
            'left_join' => ['listinstance.item_id = users.user_id', 'users_entities.user_id = users.user_id', 'entities.entity_id = users_entities.entity_id'],
            'where'     => ['res_id = ?', 'item_type = ?', 'difflist_type = ?', 'primary_entity = ?'],
            'data'      => [$aArgs['id'], 'user_id', 'AVIS_CIRCUIT', 'Y'],
            'order_by'  => ['listinstance_id ASC'],
        ]);

        return $aListinstance;
    }

    public static function getCurrentStepByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aListinstance = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance'],
            'where'     => ['res_id = ?', 'difflist_type = ?', 'process_date is null'],
            'data'      => [$aArgs['resId'], 'VISA_CIRCUIT'],
            'order_by'  => ['listinstance_id ASC'],
            'limit'     => 1
        ]);

        if (empty($aListinstance[0])) {
            return [];
        }

        return $aListinstance[0];
    }

    public static function getWithConfidentiality(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['entityId', 'userId']);
        ValidatorModel::stringType($aArgs, ['entityId', 'userId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aListInstances = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance, res_letterbox, mlb_coll_ext'],
            'where'     => ['listinstance.res_id = res_letterbox.res_id', 'mlb_coll_ext.res_id = res_letterbox.res_id', 'confidentiality = ?', 'destination = ?', 'item_id = ?', 'closing_date is null'],
            'data'      => ['Y', $aArgs['entityId'], $aArgs['userId']]
        ]);

        return $aListInstances;
    }

    public static function getWhenOpenMailsByLogin(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['login', 'itemMode']);
        ValidatorModel::stringType($aArgs, ['login', 'itemMode']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $listInstances = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listinstance', 'res_letterbox', 'mlb_coll_ext'],
            'left_join' => ['listinstance.res_id = res_letterbox.res_id', 'res_letterbox.res_id = mlb_coll_ext.res_id'],
            'where'     => ['listinstance.item_id = ?', 'listinstance.difflist_type = ?', 'listinstance.item_type = ?', 'listinstance.item_mode = ?', 'mlb_coll_ext.closing_date is null', 'res_letterbox.status != ?'],
            'data'      => [$aArgs['login'], 'entity_id', 'user_id', $aArgs['itemMode'], 'DEL']
        ]);

        return $listInstances;
    }
}
