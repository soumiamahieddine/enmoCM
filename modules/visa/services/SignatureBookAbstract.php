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

class Visa_SignatureBookAbstract_Service extends Apps_Table_Service {

    /**
     * Récupération de la liste des méthodes disponibles via api
     *
     * @return string[] La liste des méthodes
     */
    public static function getApiMethod() {
        $aApiMethod = parent::getApiMethod();
        $aApiMethod['getViewDatas'] = 'getViewDatas';

        return $aApiMethod;
    }

    public static function getViewDatas(array $aArgs = []) {
        $datas = [];
        $datas['view'] = file_get_contents('modules/visa/Views/signatureBook.html');
        $datas['datas'] = [];
        $datas['datas']['resId'] = $aArgs['resId'];
        $datas['datas']['linkNotes'] = 'index.php?display=true&module=notes&page=notes&identifier='.$aArgs['resId'].'&origin=document&coll_id=letterbox_coll&load&size=medium';
        $datas['datas']['headerTab'] = 1;

        return $datas;
    }
}