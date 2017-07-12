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

class ParametersModelAbstract
{
    public static function getList()
    {
        $func = new \functions();

        $aReturn = DatabaseModel::select(
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
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);

        $aReturn = DatabaseModel::select(
            [
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     =>['parameters'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
            ]
        );
        if ($aReturn[0]['param_value_date'] != null) {
            $aReturn[0]['param_value_date'] = TextFormatModel::format_date($aReturn[0]['param_value_date']);
        }
        
        return $aReturn[0];
    }

    public static function create(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);

        $aReturn = DatabaseModel::insert(
            [
            'table'         => 'parameters',
            'columnsValues' => $aArgs
            ]
        );

        return $aReturn;
    }

    public static function update(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);

        $aReturn = DatabaseModel::update(
            [
            'table'     => 'parameters',
            'set'       => $aArgs,
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
            ]
        );

        return $aReturn;
    }

    public static function delete(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);

        $aReturn = DatabaseModel::delete(
            [
            'table' => 'parameters',
            'where' => ['id = ?'],
            'data'  => [$aArgs['id']]
            ]
        );

        return $aReturn;
    }

}
?>