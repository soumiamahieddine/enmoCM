<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief XParaph Controller
* @author dev@maarch.org
*/

namespace ExternalSignatoryBook\controllers;

use Attachment\models\AttachmentModel;
use Convert\controllers\ConvertPdfController;
use Docserver\models\DocserverModel;
use SrcCore\models\CurlModel;

class XParaphController
{
    public static function sendDatas($aArgs)
    {
        $attachments = AttachmentModel::getOnView([
            'select'    => [
                'res_id', 'res_id_version', 'title', 'identifier', 'attachment_type',
                'status', 'typist', 'docserver_id', 'path', 'filename', 'creation_date',
                'validation_date', 'relation', 'attachment_id_master', 'filesize'
            ],
            'where'     => ["res_id_master = ?", "attachment_type not in (?)", "status not in ('DEL', 'OBS', 'FRZ', 'TMP')", "in_signature_book = 'true'"],
            'data'      => [$aArgs['resIdMaster'], ['converted_pdf', 'incoming_mail_attachment', 'print_folder', 'signed_response']]
        ]);

        $attachmentToFreeze = [];

        foreach ($attachments as $value) {
            if (!empty($value['res_id'])) {
                $resId  = $value['res_id'];
                $collId = 'attachments_coll';
                $is_version = false;
            } else {
                $resId  = $value['res_id_version'];
                $collId = 'attachments_version_coll';
                $is_version = true;
            }
            $adrInfo       = ConvertPdfController::getConvertedPdfById(['resId' => $resId, 'collId' => $collId, 'isVersion' => $is_version]);
            $docserverInfo = DocserverModel::getByDocserverId(['docserverId' => $adrInfo['docserver_id']]);
            $filePath      = $docserverInfo['path_template'] . str_replace('#', '/', $adrInfo['path']) . $adrInfo['filename'];
            $fileContent = file_get_contents($filePath);

            $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
            <soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:parafwsdl" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">
                <soapenv:Header/>
                <soapenv:Body>
                <urn:XPRF_Deposer soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
                    <params xsi:type="urn:XPRF_Deposer_Param">
                        <reponse xsi:type="xsd:string">SOAP</reponse>
                        <siret xsi:type="xsd:string">'.$aArgs['config']['data']['siret'].'</siret>
                        <login xsi:type="xsd:string">'.$aArgs['config']['data']['login'].'</login>
                        <password xsi:type="xsd:string">'.$aArgs['config']['data']['password'].'</password>
                        <docutype xsi:type="xsd:string">'.$aArgs['config']['data']['docutype'].'</docutype>
                        <docustype xsi:type="xsd:string">'.$aArgs['config']['data']['docustype'].'</docustype>
                        <objet xsi:type="xsd:string">'.$value['title'].'</objet>
                        <contenu xsi:type="xsd:base64Binary">'.base64_encode($fileContent).'</contenu>
                        <nom xsi:type="xsd:string">'.$value['title'].'</nom>
                        <taille xsi:type="xsd:int">'.$value['filesize'].'</taille>
                        <pml xsi:type="xsd:string">0</pml>
                    </params>
                </urn:XPRF_Deposer>
                </soapenv:Body>
            </soapenv:Envelope>';

            $response = CurlModel::execSOAP([
                'soapAction'    => 'urn:parafwsdl#paraf',
                'url'           => $aArgs['config']['data']['url'],
                'xmlPostString' => $xmlPostString,
                'options'       => [CURLOPT_SSL_VERIFYPEER => false]
            ]);

            $isError = $response['response']->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
            if (!empty($isError->Fault[0])) {
                $error = $response['response']->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->Fault[0]->children()->detail;
                return $error;
            } else {
                // $response = $response['response']->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children('http://sei.ws.fast.cdc.com/')->downloadResponse->children()->return;
                // $returnedDocumentId = (string) $response->documentId;
                // if ($aArgs['documentId'] !== $returnedDocumentId) {
                //     // TODO gestion d'une potentiel erreur
                //     return false;
                // } else {
                //     $b64FileContent = $response->content;
                //     return ['b64FileContent' => (string)$b64FileContent, 'documentId' => $returnedDocumentId];
                // }
            }

            // $attachmentToFreeze[$collId][$resId] = (string)$response;
        }

        return $attachmentToFreeze;
    }
}
