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

class Attachments_AttachmentsAbstract_Service extends Apps_Table_Service {

    /**
     * Récupération de la liste des méthodes disponibles via api
     *
     * @return string[] La liste des méthodes
     */
    public static function getApiMethod() {
        $aApiMethod = parent::getApiMethod();

        return $aApiMethod;
    }

    public static function getAttachmentsForThumbnails(array $aArgs = []) {
        static::checkRequired($aArgs, ['resIdMaster']);
        static::checkNumeric($aArgs, ['resIdMaster']);


        $attachments = static::select([
            'select'    => ['res_id', 'res_id_version', 'attachment_type'],
            'table'     => ['res_view_attachments'],
            'where'     => ['res_id_master = ?', 'attachment_type != ?', 'status not in (?)'],
            'data'      => [$aArgs['resIdMaster'], 'converted_pdf', ['DEL', 'TMP', 'OBS']]
        ]);

        foreach ($attachments as $key => $value) {
            $attachments[$key]['collId'] = '';
            $attachments[$key]['realId'] = 0;

            if ($value['res_id'] == 0) {
                $attachments[$key]['collId'] = 'version_attachments_coll';
                $attachments[$key]['realId'] = $value['res_id_version'];
            } elseif ($value['res_id_version'] == 0) {
                $attachments[$key]['collId'] = 'attachments_coll';
                $attachments[$key]['realId'] = $value['res_id'];
            }

            $attachments[$key]['thumbnailLink'] = "index.php?page=doc_thumb&module=thumbnails&res_id={$attachments[$key]['realId']}&coll_id={$attachments[$key]['collId']}&display=true&advanced=true";

            unset($attachments[$key]['res_id']);
            unset($attachments[$key]['res_id_version']);
        }

        return $attachments;
    }

    public static function getAttachmentsForViewer(array $aArgs = []) {
        static::checkRequired($aArgs, ['resIdMaster']);
        static::checkNumeric($aArgs, ['resIdMaster']);


        $attachments = static::select([
            'select'    => ['res_id', 'res_id_version', 'title', 'identifier', 'attachment_type', 'status', 'typist', 'path', 'filename'],
            'table'     => ['res_view_attachments'],
            'where'     => ['res_id_master = ?', 'status not in (?)'],
            'data'      => [$aArgs['resIdMaster'], ['DEL', 'TMP', 'OBS']]
        ]);

        foreach ($attachments as $key => $value) {
            if ($value['attachment_type'] == 'converted_pdf') {
                continue;
            }

            $attachments[$key]['realId'] = 0;
            if ($value['res_id'] == 0) {
                $attachments[$key]['realId'] = $value['res_id_version'];
            } elseif ($value['res_id_version'] == 0) {
                $attachments[$key]['realId'] = $value['res_id'];
            }

            $viewerId = $attachments[$key]['realId'];
            $pathToFind = $value['path'] . str_replace(strrchr($value['filename'], '.'), '.pdf', $value['filename']);
            foreach ($attachments as $tmpKey => $tmpValue) {
                if ($tmpValue['attachment_type'] == 'converted_pdf' && ($tmpValue['path'] . $tmpValue['filename'] == $pathToFind)) {
                    $viewerId = $tmpValue['res_id'];
                    unset($attachments[$tmpKey]);
                }
            }

            $attachments[$key]['viewerLink'] = "index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master={$aArgs['resIdMaster']}&id={$viewerId}";

            unset($attachments[$key]['res_id']);
            unset($attachments[$key]['res_id_version']);
            unset($attachments[$key]['path']);
            unset($attachments[$key]['filename']);
        }

        return $attachments;
    }
}