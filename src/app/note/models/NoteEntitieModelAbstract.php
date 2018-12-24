<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
*/

/**
 * @brief Note Model
 * @author dev@maarch.org
 */

namespace Note\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

abstract class NoteEntitieModelAbstract
{
    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['note_id', 'item_id']);
        ValidatorModel::intVal($aArgs, ['note_id']);
        ValidatorModel::stringType($aArgs, ['item_id']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'notes_entities_id_seq']);

        DatabaseModel::insert([
            'table' => 'note_entities',
            'columnsValues' => [
                'id'        => $nextSequenceId,
                'note_id'   => $aArgs['note_id'],
                'item_id'   => $aArgs['item_id']
            ]
        ]);

        return $nextSequenceId;
    }
}
