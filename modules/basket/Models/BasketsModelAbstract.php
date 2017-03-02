<?php

/*
*    Copyright 2015 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once 'apps/maarch_entreprise/services/Table.php';

class BasketsModelAbstract extends Apps_Table_Service {

    public static function getResListById(array $aArgs = []) {
        static::checkRequired($aArgs, ['basketId']);
        static::checkString($aArgs, ['basketId']);


        $aBasket = static::select([
            'select'    => ['basket_clause'],
            'table'     => ['baskets'],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['basketId']]
        ]);

        if (empty($aBasket[0]) || empty($aBasket[0]['basket_clause'])) {
            return [];
        }

        $where = str_replace('@user', "'" .$_SESSION['user']['UserId']. "'", $aBasket[0]['basket_clause']);

        $aResList = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['res_view_letterbox'],
            'where'     => [$where],
            'order_by'  => "creation_date DESC",
        ]);

        return $aResList;
    }

    public static function getActionByActionId(array $aArgs = []) {
        static::checkRequired($aArgs, ['actionId']);
        static::checkNumeric($aArgs, ['actionId']);


        $aAction = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['actions'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['actionId']]
        ]);

        return $aAction[0];
    }

    public static function getActionIdById(array $aArgs = []) {
        static::checkRequired($aArgs, ['basketId']);
        static::checkString($aArgs, ['basketId']);


        $aAction = static::select([
            'select'    => ['id_action'],
            'table'     => ['actions_groupbaskets'],
            'where'     => ['basket_id = ?'],
            'data'      => [$aArgs['basketId']]
        ]);

        if (empty($aAction[0])) {
            return '';
        }

        return $aAction[0]['id_action'];
    }

}