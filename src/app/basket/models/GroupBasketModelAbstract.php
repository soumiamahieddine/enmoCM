<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief   GroupBasket Model Abstract
* @author  <dev@maarch.org>
*/

namespace Basket\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

class GroupBasketModelAbstract
{
    public static function get(array $aArgs)
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);

        $aGroupsBaskets = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['groupbasket'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data'],
            'order_by'  => $aArgs['orderBy']
        ]);

        return $aGroupsBaskets;
    }

    public static function createGroupBasket(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['basketId', 'groupId', 'resultPage']);
        ValidatorModel::stringType($aArgs, ['basketId', 'groupId', 'resultPage']);

        DatabaseModel::insert([
            'table'         => 'groupbasket',
            'columnsValues' => [
                'basket_id'         => $aArgs['basketId'],
                'group_id'          => $aArgs['groupId'],
                'result_page'       => $aArgs['resultPage']
            ]
        ]);

        return true;
    }

    public static function deleteGroupBasket(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['basketId', 'groupId']);
        ValidatorModel::stringType($aArgs, ['basketId', 'groupId']);

        DatabaseModel::delete([
            'table' => 'groupbasket',
            'where' => ['basket_id = ?', 'group_id = ?'],
            'data'  => [$aArgs['basketId'], $aArgs['groupId']]
        ]);
        DatabaseModel::delete([
            'table' => 'actions_groupbaskets',
            'where' => ['basket_id = ?', 'group_id = ?'],
            'data'  => [$aArgs['basketId'], $aArgs['groupId']]
        ]);
        DatabaseModel::delete([
            'table' => 'groupbasket_redirect',
            'where' => ['basket_id = ?', 'group_id = ?'],
            'data'  => [$aArgs['basketId'], $aArgs['groupId']]
        ]);
        DatabaseModel::delete([
            'table' => 'groupbasket_status',
            'where' => ['basket_id = ?', 'group_id = ?'],
            'data'  => [$aArgs['basketId'], $aArgs['groupId']]
        ]);

        return true;
    }
}