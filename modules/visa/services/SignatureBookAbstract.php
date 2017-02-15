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
require_once 'modules/basket/class/class_modules_tools.php';
require_once 'modules/attachments/services/Attachments.php';


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
        static::checkRequired($aArgs, ['resId']);
        static::checkNumeric($aArgs, ['resId']);

        $resId = $aArgs['resId'];
        $collId = 'letterbox_coll';

        $basket = new basket();
        $actions = $basket->get_actions_from_current_basket($resId, $collId, 'PAGE_USE', false);

        $actionsData = [];
        $actionsData[] = ['value' => '', 'label' => _CHOOSE_ACTION];
        foreach($actions as $value) {
            $actionsData[] = ['value' => $value['VALUE'], 'label' => $value['LABEL']];
        }

        $thumbnailsAttachments = Attachments_Attachments_Service::getAttachmentsForThumbnails(['resIdMaster' => $resId]);
        $viewerAttachments = Attachments_Attachments_Service::getAttachmentsForViewer(['resIdMaster' => $resId]);


        $datas = [];
        $datas['view'] = file_get_contents('modules/visa/Views/signatureBook.html');
        $datas['datas'] = [];
        $datas['datas']['resId'] = $resId;
        $datas['datas']['actions'] = $actionsData;
        $datas['datas']['thumbnailsAttachments'] = $thumbnailsAttachments;
        $datas['datas']['selectedThumbnail'] = 0;
        $datas['datas']['viewerAttachments'] = $viewerAttachments;
        $datas['datas']['viewerLink'] = $viewerAttachments[0]['viewerLink'];
        $datas['datas']['linkNotes'] = 'index.php?display=true&module=notes&page=notes&identifier=' .$resId. '&origin=document&coll_id=' .$collId. '&load&size=medium';
        $datas['datas']['displayLeftMainDoc'] = 'index.php?display=true&dir=indexing_searching&page=view_resource_controler&visu&id=' .$resId. '&collid=' .$collId;
        $datas['datas']['headerTab'] = 1;

        return $datas;
    }
}