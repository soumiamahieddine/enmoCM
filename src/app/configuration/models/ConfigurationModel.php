<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Configuration Model
* @author dev@maarch.org
*/

namespace Configuration\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class ConfigurationModel
{
    public static function getByName(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['name']);
        ValidatorModel::stringType($aArgs, ['name']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $configuration = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['configuration'],
            'where'     => ['name = ?'],
            'data'      => [$aArgs['name']],
        ]);

        if (empty($configuration[0])) {
            return [];
        }

        return $configuration[0];
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['set', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'configuration',
            'set'   => $aArgs['set'],
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }
}
