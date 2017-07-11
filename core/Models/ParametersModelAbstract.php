<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ParametersModelAbstract
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Core\Models;

require_once 'apps/maarch_entreprise/services/Table.php';
require_once 'core/class/class_functions.php';



class ParametersModelAbstract extends \Apps_Table_Service
{
    public static function getList()
    {
        $func = new \functions();

        $aReturn = static::select(
            ['select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['parameters']]
        );

        foreach ($aReturn as $key => $parameter) {

            if ($parameter['param_value_date'] != null) {
                $aReturn[$key]['param_value_date'] = $func->format_date($aReturn[$key]['param_value_date']);
            }                
        }

        return $aReturn;
    }
    
    public static function getParametersLang()
    {
        $aLang = LangModel::getParametersLang();
        return $aLang;
    }

    public static function getById(array $aArgs = [])
    {
        $func = new \functions();
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     =>['parameters'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);
        if ($aReturn[0]['param_value_date']!=null) {
            $aReturn[0]['param_value_date']=$func->format_date($aReturn[0]['param_value_date']);
        }
        
        return $aReturn[0];

    }

    public static function create(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        $aReturn = static::insertInto($aArgs, 'parameters');

        return $aReturn;
    }

    public static function update(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        $where['id'] = $aArgs['id'];

        $aReturn = static::updateTable(
            $aArgs,
            'parameters',
            $where
        );

        return $aReturn;
    }

    public static function delete(array $aArgs = [])
    {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);

        $aReturn = static::deleteFrom([
                'table' => 'parameters',
                'where' => ['id = ?'],
                'data'  => [$aArgs['id']]
            ]);

        return $aReturn;
    }

}
?>