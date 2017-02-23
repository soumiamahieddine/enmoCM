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

require_once('apps/maarch_entreprise/services/Table.php');

class Basket_BasketsAbstract_Service extends Apps_Table_Service {

    /**
     * Récupération de la liste des méthodes disponibles via api
     *
     * @return string[] La liste des méthodes
     */
    public static function getApiMethod() {
        $aApiMethod = parent::getApiMethod();

        return $aApiMethod;
    }

    public static function getServiceFromActionId(array $aArgs = []) {
        static::checkRequired($aArgs, ['id']);
        static::checkNumeric($aArgs, ['id']);


        $actionstable = static::select([
            'select'    => ['action_page'],
            'table'     => ['actions'],
            'where'     => ['id = ? AND enabled = ?'],
            'data'      => [$aArgs['id'], 'Y']
        ]);
        $aReturn = [];
        $aReturn['actionPage'] = $actionstable[0]['action_page'];

        return $aReturn;
    }
}