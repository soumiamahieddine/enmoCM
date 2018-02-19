<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   TemplateDoctypeModelAbstract
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Doctype\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;

class TemplateDoctypeModelAbstract
{

    public static function getById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $aReturn = DatabaseModel::select(
            [
            'select' => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'  => ['templates_doctype_ext'],
            'where'  => ['type_id = ?'],
            'data'   => [$aArgs['id']]
            ]
        );

        if (empty($aReturn[0])) {
            return [];
        }

        $aReturn = $aReturn[0];
       
        return $aReturn;
    }

    // public static function create(array $aArgs)
    // {
    //     ValidatorModel::notEmpty($aArgs, ['description', 'doctypes_first_level_id', 'doctypes_second_level_id', 'coll_id']);
    //     ValidatorModel::intVal($aArgs, ['doctypes_first_level_id', 'doctypes_second_level_id']);

    //     $aArgs['type_id'] = DatabaseModel::getNextSequenceValue(['sequenceId' => 'doctypes_type_id_seq']);
    //     DatabaseModel::insert([
    //         'table'         => 'doctypes',
    //         'columnsValues' => $aArgs
    //     ]);

    //     return $aArgs;
    // }

    // public static function update(array $aArgs)
    // {
    //     ValidatorModel::notEmpty($aArgs, ['type_id']);
    //     ValidatorModel::intVal($aArgs, ['type_id']);
        
    //     DatabaseModel::update([
    //         'table'     => 'mlb_doctype_ext',
    //         'set'       => $aArgs,
    //         'where'     => ['type_id = ?'],
    //         'data'      => [$aArgs['type_id']]
    //     ]);

    //     return true;
    // }

}
