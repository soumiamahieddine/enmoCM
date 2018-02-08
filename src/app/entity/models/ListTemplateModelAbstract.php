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

use Core\Models\DatabaseModel;
use Core\Models\ValidatorModel;

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
