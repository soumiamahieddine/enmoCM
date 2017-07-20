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

    public static function getAttachmentsForSignatureBook(array $aArgs = []) {
        static::checkRequired($aArgs, ['resIdMaster']);
        static::checkNumeric($aArgs, ['resIdMaster']);


        $attachments = static::select([
            'select'    => ['res_id', 'res_id_version', 'title', 'identifier', 'attachment_type', 'status', 'typist', 'path', 'filename'],
            'table'     => ['res_view_attachments'],
            'where'     => ['res_id_master = ?', 'attachment_type not in (?)', 'status not in (?)'],
            'data'      => [$aArgs['resIdMaster'], ['incoming_mail_attachment'], ['DEL', 'TMP', 'OBS']]
        ]);

        foreach ($attachments as $key => $value) {
            if ($value['attachment_type'] == 'converted_pdf') {
                continue;
            }

            $collId = '';
            $realId = 0;
            if ($value['res_id'] == 0) {
                $collId = 'version_attachments_coll';
                $realId = $value['res_id_version'];
            } elseif ($value['res_id_version'] == 0) {
                $collId = 'attachments_coll';
                $realId = $value['res_id'];
            }

            $viewerId = $realId;
            $pathToFind = $value['path'] . str_replace(strrchr($value['filename'], '.'), '.pdf', $value['filename']);
            foreach ($attachments as $tmpKey => $tmpValue) {
                if ($tmpValue['attachment_type'] == 'converted_pdf' && ($tmpValue['path'] . $tmpValue['filename'] == $pathToFind)) {
                    $viewerId = $tmpValue['res_id'];
                    unset($attachments[$tmpKey]);
                }
            }

            $attachments[$key]['thumbnailLink'] = "index.php?page=doc_thumb&module=thumbnails&res_id={$realId}&coll_id={$collId}&display=true&advanced=true";
            $attachments[$key]['viewerLink'] = "index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master={$aArgs['resIdMaster']}&id={$viewerId}";

            unset($attachments[$key]['res_id']);
            unset($attachments[$key]['res_id_version']);
            unset($attachments[$key]['path']);
            unset($attachments[$key]['filename']);
        }

        return $attachments;
    }

    public static function getIncomingMailAttachmentsForSignatureBook(array $aArgs = []) {
        static::checkRequired($aArgs, ['resIdMaster', 'collIdMaster']);
        static::checkNumeric($aArgs, ['resIdMaster']);


        $incomingMail = static::select([
            'select'    => ['subject'],
            'table'     => ['res_letterbox'],
            'where'     => ['res_id = ?'],
            'data'      => [$aArgs['resIdMaster']]
        ]);
        $attachments = static::select([
            'select'    => ['res_id', 'title'],
            'table'     => ['res_attachments'],
            'where'     => ['res_id_master = ?', 'attachment_type = ?', 'status not in (?)'],
            'data'      => [$aArgs['resIdMaster'], 'incoming_mail_attachment', ['DEL', 'TMP', 'OBS']]
        ]);

        $aReturn = [
            [
                'title'         => $incomingMail[0]['subject'],
                'truncateTitle' => ((strlen($incomingMail[0]['subject']) > 10) ? (substr($incomingMail[0]['subject'], 0, 10) . '...') : $incomingMail[0]['subject']),
                'viewerLink'    => "index.php?display=true&dir=indexing_searching&page=view_resource_controler&visu&id={$aArgs['resIdMaster']}&collid={$aArgs['collIdMaster']}",
                'thumbnailLink' => "index.php?page=doc_thumb&module=thumbnails&res_id={$aArgs['resIdMaster']}&coll_id={$aArgs['collIdMaster']}&display=true&advanced=true"
            ]
        ];
        foreach ($attachments as $value) {
            $aReturn[] = [
                'title'         => $value['title'],
                'truncateTitle' => ((strlen($value['title']) > 10) ? (substr($value['title'], 0, 10) . '...') : $value['title']),
                'viewerLink'    => "index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master={$aArgs['resIdMaster']}&id={$value['res_id']}",
                'thumbnailLink' => "index.php?page=doc_thumb&module=thumbnails&res_id={$value['res_id']}&coll_id=attachments_coll&display=true&advanced=true"
            ];
        }

        return $aReturn;
    }
}