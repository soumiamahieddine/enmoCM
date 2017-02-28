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

class UsersModelAbstract extends Apps_Table_Service {

    public static function getById(array $aArgs = []) {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);


        $aReturn = static::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        return $aReturn;
    }

    public static function getLabelledUserById(array $aArgs = []) {
        static::checkRequired($aArgs, ['id']);
        static::checkString($aArgs, ['id']);


        $rawUser = self::getById(['id' => $aArgs['id'], 'select' => ['firstname', 'lastname']]);

        $labelledUser = '';
        if (!empty($rawUser[0])) {
            $labelledUser = $rawUser[0]['firstname']. ' ' .$rawUser[0]['lastname'];
        }

        return $labelledUser;
    }

}