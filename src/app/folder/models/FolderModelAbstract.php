<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   FolderModelAbstract
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Folder\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

class FolderModelAbstract
{
    public static function get(array $aArgs = [])
    {
        ValidatorModel::arrayType($aArgs, ['select']);

        $folders = DatabaseModel::select([
            'select' => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'  => ['folders']
        ]);

        return $folders;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $folder = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['folders'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return $folder[0];
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['user_id', 'label']);
        ValidatorModel::stringType($aArgs, ['label']);
        ValidatorModel::intVal($aArgs, ['user_id', 'parent_id']);
        ValidatorModel::boolType($aArgs, ['public']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'folders_id_seq']);

        DatabaseModel::insert([
            'table'     => 'folders',
            'columnsValues'     => [
                'id'         => $nextSequenceId,
                'label'      => $aArgs['label'],
                'public'     => empty($aArgs['public']) ? 'false' : 'true',
                'user_id'    => $aArgs['user_id'],
                'parent_id'  => $aArgs['parent_id']
            ]
        ]);

        return $nextSequenceId;
    }

    public static function update(array $args)
    {
        ValidatorModel::notEmpty($args, ['where']);
        ValidatorModel::arrayType($args, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table'     => 'folders',
            'set'       => empty($args['set']) ? [] : $args['set'],
            'where'     => $args['where'],
            'data'      => empty($args['data']) ? [] : $args['data']
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        DatabaseModel::delete([
            'table' => 'folders',
            'where' => ['id = ?'],
            'data'  => [$aArgs['id']]
        ]);

        return true;
    }
}
