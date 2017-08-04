<?php
/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Priority Abstract Model
 * @author dev@maarch.org
 * @ingroup core
 */

namespace Core\Models;

abstract class PriorityModelAbstract
{
    public static function get(array $aArgs = [])
    {
        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['priorities'],
        ]);

        return $aReturn;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);

        $aPriority = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['priorities'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        if (empty($aPriority[0])) {
            return [];
        }

        return $aPriority[0];
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['label', 'color', 'delays', 'working_days']);
        ValidatorModel::stringType($aArgs, ['label', 'color', 'working_days']);
        ValidatorModel::intVal($aArgs, ['delays']);

        //working_days => true (monday to friday) => false (monday to sunday)
        $id = DatabaseModel::uniqueId();
        DatabaseModel::insert([
            'table'         => 'priorities',
            'columnsValues' => [
                'id'            => $id,
                'label'         => $aArgs['label'],
                'color'         => $aArgs['color'],
                'working_days'  => $aArgs['working_days'],
                'delays'        => $aArgs['delays']
            ]
        ]);

        return $id;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'label', 'color', 'delays', 'working_days']);
        ValidatorModel::stringType($aArgs, ['id', 'label', 'color', 'working_days']);
        ValidatorModel::intVal($aArgs, ['delays']);

        //working_days => true (monday to friday) => false (monday to sunday)
        DatabaseModel::update([
            'table'     => 'priorities',
            'set'       => [
                'label'         => $aArgs['label'],
                'color'         => $aArgs['color'],
                'working_days'  => $aArgs['working_days'],
                'delays'        => $aArgs['delays']
            ],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);

        DatabaseModel::delete([
            'table' => 'priorities',
            'where' => ['id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        
        return true;
    }   
    
}

