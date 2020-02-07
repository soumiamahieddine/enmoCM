<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Res Model
* @author dev@maarch.org
*/

namespace Resource\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

abstract class ResModelAbstract
{
    public static function getOnView(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy', 'groupBy']);
        ValidatorModel::intType($aArgs, ['limit', 'offset']);

        $aResources = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['res_view_letterbox'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'order_by'  => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'groupBy'   => empty($aArgs['groupBy']) ? [] : $aArgs['groupBy'],
            'offset'    => empty($aArgs['offset']) ? 0 : $aArgs['offset'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $aResources;
    }

    public static function get(array $args)
    {
        ValidatorModel::notEmpty($args, ['select']);
        ValidatorModel::arrayType($args, ['select', 'where', 'data', 'orderBy', 'groupBy']);
        ValidatorModel::intType($args, ['limit']);

        $resources = DatabaseModel::select([
            'select'    => $args['select'],
            'table'     => ['res_letterbox'],
            'where'     => empty($args['where']) ? [] : $args['where'],
            'data'      => empty($args['data']) ? [] : $args['data'],
            'order_by'  => empty($args['orderBy']) ? [] : $args['orderBy'],
            'limit'     => empty($args['limit']) ? 0 : $args['limit'],
            'groupBy'   => empty($args['groupBy']) ? [] : $args['groupBy'],
        ]);

        return $resources;
    }

    public static function getById(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        $resource = DatabaseModel::select([
            'select'    => $args['select'],
            'table'     => ['res_letterbox'],
            'where'     => ['res_id = ?'],
            'data'      => [$args['resId']]
        ]);

        if (empty($resource[0])) {
            return [];
        }

        return $resource[0];
    }

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['res_id', 'model_id', 'category_id', 'typist', 'creation_date']);
        ValidatorModel::stringType($args, ['category_id', 'creation_date', 'format', 'docserver_id', 'path', 'filename', 'fingerprint']);
        ValidatorModel::intVal($args, ['res_id', 'model_id', 'typist', 'filesize']);

        DatabaseModel::insert([
            'table'         => 'res_letterbox',
            'columnsValues' => $args
        ]);

        return true;
    }

    public static function update(array $args)
    {
        ValidatorModel::notEmpty($args, ['where', 'data']);
        ValidatorModel::arrayType($args, ['set', 'postSet', 'where', 'data']);

        DatabaseModel::update([
            'table'     => 'res_letterbox',
            'set'       => $args['set'],
            'postSet'   => $args['postSet'],
            'where'     => $args['where'],
            'data'      => $args['data']
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        DatabaseModel::update([
            'table' => 'res_letterbox',
            'set'   => [
                'status'    => 'DEL'
            ],
            'where' => ['res_id = ?'],
            'data'  => [$aArgs['resId']]
        ]);

        return true;
    }

    public static function getLastResources(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['limit', 'userId', 'select']);
        ValidatorModel::intType($aArgs, ['limit']);
        ValidatorModel::stringType($aArgs, ['userId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $resources = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['history, res_letterbox r, status, priorities'],
            'where'     => [
                'history.user_id = ?', 'history.table_name IN (?)',
                'history.record_id IS NOT NULL', 'history.record_id != ?',
                'history.event_id != ?', 'history.event_id NOT LIKE ?',
                'CAST(history.record_id AS INT) = r.res_id',
                'r.res_id = r.res_id', 'r.status != ?',
                'r.status = status.id',
                'r.priority = priorities.id'
            ],
            'data'      => [$aArgs['userId'], ['res_letterbox', 'res_view_letterbox'], 'none', 'linkup', 'attach%', 'DEL'],
            'groupBy'   => ['r.subject', 'r.creation_date', 'r.res_id', 'r.alt_identifier', 'r.closing_date', 'r.process_limit_date', 'status.id', 'status.label_status', 'status.img_filename', 'priorities.color', 'priorities.label'],
            'order_by'  => ['MAX(history.event_date) DESC'],
            'limit'     => $aArgs['limit']
        ]);

        return $resources;
    }

    public static function getDocsByClause(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['clause']);

        if (!empty($aArgs['table'])) {
            $table = $aArgs['table'];
        } else {
            $table = 'res_view_letterbox';
        }

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => [$table],
            'where'     => [$aArgs['clause']],
            'order_by'  => ['res_id']
        ]);

        return $aReturn;
    }

    public static function getByAltIdentifier(array $args)
    {
        ValidatorModel::notEmpty($args, ['altIdentifier']);
        ValidatorModel::stringType($args, ['altIdentifier']);

        $resource = DatabaseModel::select([
            'select'    => empty($args['select']) ? ['*'] : $args['select'],
            'table'     => ['res_letterbox'],
            'where'     => ['alt_identifier = ?'],
            'data'      => [$args['altIdentifier']]
        ]);

        if (empty($resource[0])) {
            return [];
        }

        return $resource[0];
    }

    public static function getCategories()
    {
        $categories = [
            [
                'id'              => 'incoming',
                'label'           => _INCOMING
            ],
            [
                'id'              => 'outgoing',
                'label'           =>  _OUTGOING
            ],
            [
                'id'              => 'internal',
                'label'           => _INTERNAL
            ],
            [
                'id'              => 'ged_doc',
                'label'           => _GED_DOC
            ]
        ];

        return $categories;
    }

    public static function getCategoryLabel(array $args)
    {
        ValidatorModel::stringType($args, ['categoryId']);

        $categories = ResModel::getCategories();
        foreach ($categories as $category) {
            if ($category['id'] == $args['categoryId']) {
                return $category['label'];
            }
        }

        return '';
    }

    public static function getNbContactsByResId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        $aResources = DatabaseModel::select([
            'select'    => ['count(1) as nb_contacts'],
            'table'     => ['contacts_res'],
            'where'     => ['res_id = ?', 'mode = ?'],
            'data'      => [$aArgs['resId'], 'multi']
        ]);
        return $aResources[0]['nb_contacts'];
    }
}
