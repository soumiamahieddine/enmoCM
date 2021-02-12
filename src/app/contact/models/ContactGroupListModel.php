<?php
/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Contact Group List Model
 * @author dev@maarch.org
 */

namespace Contact\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

class ContactGroupListModel
{
    public static function delete(array $args)
    {
        ValidatorModel::arrayType($args, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'contacts_groups_lists',
            'where'     => $args['where'],
            'data'      => $args['data']
        ]);

        return true;
    }
}
