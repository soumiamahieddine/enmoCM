<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Export Template Model
* @author dev@maarch.org
*/

namespace Resource\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class ExportTemplateModel
{
    public static function getByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::intVal($aArgs, ['userId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $exportTemplates = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['exports_templates'],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        return $exportTemplates;
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'type', 'data']);
        ValidatorModel::stringType($aArgs, ['type', 'delimiter', 'data']);
        ValidatorModel::intVal($aArgs, ['userId']);

        DatabaseModel::insert([
            'table'         => 'exports_templates',
            'columnsValues' => [
                'user_id'   => $aArgs['userId'],
                'type'      => $aArgs['type'],
                'delimiter' => empty($aArgs['delimiter']) ? null : $aArgs['delimiter'],
                'data'      => $aArgs['data']
            ]
        ]);

        return true;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['set', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'exports_templates',
            'set'   => $aArgs['set'],
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }
}
