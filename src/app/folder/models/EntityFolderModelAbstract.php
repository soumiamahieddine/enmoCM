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

class EntityFolderModelAbstract
{
    public static function getByFolderId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['folder_id']);
        ValidatorModel::intVal($aArgs, ['folder_id']);

        $entitiesFolder = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['entities_folders'],
            'where'     => ['folder_id = ?'],
            'data'      => [$aArgs['folder_id']]
        ]);

        return $entitiesFolder;
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['folder_id', 'entity_id', 'edition']);
        ValidatorModel::intVal($aArgs, ['entity_id', 'folder_id']);
        ValidatorModel::boolType($aArgs, ['edition']);

        DatabaseModel::insert([
            'table'     => 'entities_folders',
            'columnsValues' => [
                'folder_id'  => $aArgs['folder_id'],
                'entity_id'  => $aArgs['entity_id'],
                'edition'    => empty($aArgs['edition']) ? 'false' : 'true'
            ]
        ]);

        return true;
    }

    public static function deleteByFolderId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['folder_id']);
        ValidatorModel::intVal($aArgs, ['folder_id']);

        DatabaseModel::delete([
            'table' => 'entities_folders',
            'where' => ['folder_id = ?'],
            'data'  => [$aArgs['folder_id']]
        ]);

        return true;
    }
}
