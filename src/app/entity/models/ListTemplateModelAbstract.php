<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief List Template Model Abstract
 * @author dev@maarch.org
 */

namespace Entity\models;

use Core\Models\ValidatorModel;
use SrcCore\models\DatabaseModel;

class ListTemplateModelAbstract
{
    public static function get(array $aArgs = [])
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data']);

        $aListTemplates = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listmodels'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data']
        ]);

        return $aListTemplates;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $basicTemplate = DatabaseModel::select([
            'select'    => ['object_id', 'object_type'],
            'table'     => ['listmodels'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);
        if (empty($basicTemplate)) {
            return [];
        }

        $aListTemplates = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['listmodels'],
            'where'     => ['object_id = ?', 'object_type = ?'],
            'data'      => [$basicTemplate[0]['object_id'], $basicTemplate[0]['object_type']]
        ]);

        return $aListTemplates;
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['object_id', 'object_type', 'item_id', 'item_type', 'item_mode']);
        ValidatorModel::stringType($aArgs, ['object_id', 'object_type', 'item_id', 'item_type', 'title', 'description']);
        ValidatorModel::intVal($aArgs, ['sequence']);

        DatabaseModel::insert([
            'table'         => 'listmodels',
            'columnsValues' => [
                'object_id'     => $aArgs['object_id'],
                'object_type'   => $aArgs['object_type'],
                'sequence'      => $aArgs['sequence'],
                'item_id'       => $aArgs['item_id'],
                'item_type'     => $aArgs['item_type'],
                'item_mode'     => $aArgs['item_mode'],
                'title'         => $aArgs['title'],
                'description'   => $aArgs['description'],
                'visible'       => 'Y',
            ]
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['where', 'data']);
        ValidatorModel::arrayType($aArgs, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'listmodels',
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }
}
