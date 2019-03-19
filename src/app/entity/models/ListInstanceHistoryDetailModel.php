<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief List Instance History Detail Model Abstract
 * @author dev@maarch.org
 */

namespace Entity\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

class ListInstanceHistoryDetailModel
{
    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['listinstance_history_id', 'resId', 'item_id', 'item_type', 'item_mode', 'added_by_user', 'added_by_entity', 'difflist_type']);
        ValidatorModel::intVal($args, ['listinstance_history_id', 'resId', 'item_id', 'sequence']);
        ValidatorModel::stringType($args, ['item_type', 'item_mode', 'added_by_user', 'added_by_entity', 'difflist_type', 'process_date', 'process_comment']);

        DatabaseModel::insert([
            'table'         => 'listinstance_history_details',
            'columnsValues' => [
                'listinstance_history_id'   => $args['listinstance_history_id'],
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
}
