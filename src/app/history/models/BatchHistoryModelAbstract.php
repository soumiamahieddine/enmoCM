<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Batch History Model Abstract
* @author dev@maarch.org
*/

namespace History\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

abstract class BatchHistoryModelAbstract
{
    public static function get(array $args)
    {
        ValidatorModel::notEmpty($args, ['select']);
        ValidatorModel::arrayType($args, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intVal($args, ['offset', 'limit']);

        $history = DatabaseModel::select([
            'select'    => $args['select'],
            'table'     => ['history_batch'],
            'where'     => $args['where'] ?? [],
            'data'      => $args['data'] ?? [],
            'order_by'  => $args['orderBy'] ?? [],
            'offset'    => $args['offset'] ?? 0,
            'limit'     => $args['limit'] ?? 0
        ]);

        return $history;
    }
}
