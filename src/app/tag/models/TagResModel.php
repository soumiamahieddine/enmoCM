<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Tag Res Model
 * @author dev@maarch.org
 */

namespace Tag\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class TagResModel
{

    public static function get(array $aArgs)
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($aArgs, ['limit']);

        $tags = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['tag_res'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'order_by'  => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $tags;
    }

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['res_id', 'tag_id']);
        ValidatorModel::intVal($args, ['res_id', 'tag_id']);

        DatabaseModel::insert([
            'table'         => 'tag_res',
            'columnsValues' => [
                'res_id'    => $args['res_id'],
                'tag_id'    => $args['tag_id']
            ]
        ]);

        return true;
    }

    public static function update(array $args)
    {
        ValidatorModel::notEmpty($args, ['where']);
        ValidatorModel::arrayType($args, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table'     => 'tag_res',
            'set'       => empty($args['set']) ? [] : $args['set'],
            'where'     => $args['where'],
            'data'      => empty($args['data']) ? [] : $args['data']
        ]);

        return true;
    }

    public static function delete(array $args)
    {
        ValidatorModel::notEmpty($args, ['where', 'data']);
        ValidatorModel::arrayType($args, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'tag_res',
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }
}
