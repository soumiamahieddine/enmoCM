<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief MaarchParapheur Controller
 * @author dev@maarch.org
 */


class MaarchParapheurController
{
    public static function getModal($config)
    {
        $initializeDatas = MaarchParapheurController::getInitializeDatas($config);

        $html .= '<label for="processingUser">' . _USER_MAARCH_PARAPHEUR . '</label><select name="processingUser" id="processingUser">';
        if (!empty($initializeDatas['users'])) {
            foreach ($initializeDatas['users'] as $value) {
                $html .= '<option value="';
                $html .= $value['id'];
                $html .= '">';
                $html .= $value['firstname'] . ' ' . $value['lastname'];
                $html .= '</option>';
            }
        }
        $html .= '</select><br /><br /><br /><br />';
        $html .= _NOTE . '<input type="radio" name="mode" id="mode" value="annotation" checked="checked" />' . _SIGNATURE .'<input type="radio" name="mode" id="mode" value="signature" /><br /><br />';

        return $html;
    }

    public static function getInitializeDatas($config)
    {
        $rawResponse['users'] = MaarchParapheurController::getUsers(['config' => $config]);

        return $rawResponse;
    }

    public static function getUsers($aArgs)
    {
        $response = \SrcCore\models\CurlModel::exec([
            'url'      => $aArgs['config']['data']['url'] . '/rest/users',
            'user'     => $aArgs['config']['data']['userId'],
            'password' => $aArgs['config']['data']['password'],
            'method'   => 'GET'
        ]);

        return $response['users'];
    }

    public static function sendDatas($aArgs)
    {
        $attachments = \Attachment\models\AttachmentModel::getOnView([
            'select'    => [
                'res_id', 'res_id_version', 'title', 'identifier', 'attachment_type',
                'status', 'typist', 'docserver_id', 'path', 'filename', 'creation_date',
                'validation_date', 'relation', 'attachment_id_master'
            ],
            'where'     => ["res_id_master = ?", "attachment_type not in (?)", "status not in ('DEL', 'OBS', 'FRZ', 'TMP')", "in_signature_book = 'true'"],
            'data'      => [$aArgs['resIdMaster'], ['converted_pdf', 'incoming_mail_attachment', 'print_folder', 'signed_response']]
        ]);

        $attachmentToFreeze = [];

        $adrMainInfo              = \Convert\models\AdrModel::getConvertedDocumentById(['resId' => $aArgs['resIdMaster'], 'collId' => 'letterbox_coll', 'type' => 'PDF']);
        $docserverMainInfo        = \Docserver\models\DocserverModel::getByDocserverId(['docserverId' => $adrMainInfo['docserver_id']]);
        $arrivedMailMainfilePath  = $docserverMainInfo['path_template'] . str_replace('#', '/', $adrMainInfo['path']) . $adrMainInfo['filename'];
        $encodedMainZipFile       = MaarchParapheurController::createZip(['filepath' => $arrivedMailMainfilePath, 'filename' => 'courrier_arrivee.pdf']);

        $mainResource = \Resource\models\ResModel::getOnView([
            'select' => ['process_limit_date', 'status', 'category_id', 'alt_identifier', 'subject', 'priority', 'contact_firstname', 'contact_lastname', 'contact_society'],
            'where'  => ['res_id = ?'],
            'data'   => [$aArgs['resIdMaster']]
        ]);
        if (empty($mainResource[0]['process_limit_date'])) {
            $processLimitDate = date('Y-m-d', strtotime(date("Y-m-d"). ' + 14 days'));
        } else {
            $processLimitDateTmp = explode(" ", $mainResource[0]['process_limit_date']);
            $processLimitDate = $processLimitDateTmp[0];
        }

        $processingUser      = $aArgs['processingUser'];
        $status              = $aArgs['config']['data'][$aArgs['mode']];
        $priority            = \Priority\models\PriorityModel::getById(['select' => ['label'], 'id' => $mainResource[0]['priority']]);
        $sender              = \User\models\UserModel::getByUserId(['select' => ['firstname', 'lastname'], 'userId' => $aArgs['userId']]);
        $senderPrimaryEntity = \User\models\UserModel::getPrimaryEntityByUserId(['userId' => $aArgs['userId']]);

        foreach ($attachments as $value) {
            if (!empty($value['res_id'])) {
                $resId  = $value['res_id'];
                $collId = 'attachments_coll';
            } else {
                $resId  = $value['res_id_master'];
                $collId = 'attachments_version_coll';
            }
            $adrInfo       = \Convert\models\AdrModel::getConvertedDocumentById(['resId' => $resId, 'collId' => $collId, 'type' => 'PDF']);
            $docserverInfo = \Docserver\models\DocserverModel::getByDocserverId(['docserverId' => $adrInfo['docserver_id']]);
            $filePath      = $docserverInfo['path_template'] . str_replace('#', '/', $adrInfo['path']) . $adrInfo['filename'];

            $encodedZipDocument = MaarchParapheurController::createZip(['filepath' => $filePath, 'filename' => $adrInfo['filename']]);

            $attachments = [
                'encodedZipDocument' => $encodedMainZipFile,
                'subject'            => $mainResource[0]['subject'],
                'reference'          => $mainResource[0]['alt_identifier']
            ];

            $bodyData = [
                'reference'          => $value['identifier'],
                'subject'            => $value['title'],
                'status'             => $status,
                'priority'           => $priority['label'],
                'sender'             => trim($sender['firstname'] . ' ' .$sender['lastname']),
                'sender_entity'      => $senderPrimaryEntity['entity_label'],
                'processing_user'    => $processingUser,
                'recipient'          => trim($mainResource[0]['contact_firstname'] . ' ' . $mainResource[0]['contact_lastname'] . ' ' . $mainResource[0]['contact_society']),
                'limit_data'         => $processLimitDate ,
                'encodedZipDocument' => $encodedZipDocument,
                'attachments'        => $attachments
            ];

            $response = \SrcCore\models\CurlModel::exec([
                'url'      => $aArgs['config']['data']['url'] . '/rest/documents',
                'user'     => $aArgs['config']['data']['userId'],
                'password' => $aArgs['config']['data']['password'],
                'method'   => 'POST',
                'bodyData' => $bodyData
            ]);

            $attachmentToFreeze[$value['res_id']] = $response['id'];
        }

        return $attachmentToFreeze;
    }

    public static function createZip($aArgs)
    {
        $zip = new ZipArchive();

        $pathInfo    = pathinfo($aArgs['filepath'], PATHINFO_FILENAME);
        $tmpPath     = \SrcCore\models\CoreConfigModel::getTmpPath();
        $zipFilename = $tmpPath . $pathInfo."_".rand().".zip";

        if ($zip->open($zipFilename, ZipArchive::CREATE) === true) {
            $zip->addFile($aArgs['filepath'], $aArgs['filename']);

            $zip->close();

            $fileContent = file_get_contents($zipFilename);
            $base64 =  base64_encode($fileContent);
            return $base64;
        } else {
            echo 'Impossible de créer l\'archive;';
        }
    }

    public static function retrieveSignedMails($aArgs)
    {
        $sessionId = MaarchParapheurController::createSession($aArgs['config']);

        foreach (['noVersion', 'isVersion'] as $version) {
            foreach ($aArgs['idsToRetrieve'][$version] as $resId => $value) {
                $etatDossier = MaarchParapheurController::getEtatDossier(['config' => $aArgs['config'], 'sessionId' => $sessionId, 'dossier_id' => $value->external_id]);
    
                // Refused
                if ((string)$etatDossier == $aArgs['config']['data']['ixbusIdEtatRefused']) {
                    $aArgs['idsToRetrieve'][$version][$resId]->status = 'refused';
                    $notes = MaarchParapheurController::getDossier(['config' => $aArgs['config'], 'sessionId' => $sessionId, 'dossier_id' => $value->external_id]);
                    $aArgs['idsToRetrieve'][$version][$resId]->noteContent = (string)$notes->MotifRefus;
                // Validated
                } elseif ((string)$etatDossier == $aArgs['config']['data']['ixbusIdEtatValidated']) {
                    $aArgs['idsToRetrieve'][$version][$resId]->status = 'validated';
                    $signedDocument = MaarchParapheurController::getAnnexes(['config' => $aArgs['config'], 'sessionId' => $sessionId, 'dossier_id' => $value->external_id]);
                    $aArgs['idsToRetrieve'][$version][$resId]->format = 'pdf'; // format du fichier récupéré
                    $aArgs['idsToRetrieve'][$version][$resId]->encodedFile = (string)$signedDocument->Fichier;

                    $notes = MaarchParapheurController::getAnnotations(['config' => $aArgs['config'], 'sessionId' => $sessionId, 'dossier_id' => $value->external_id]);
                    $aArgs['idsToRetrieve'][$version][$resId]->noteContent = (string)$notes->Annotation->Texte;
                } else {
                    unset($aArgs['idsToRetrieve'][$version][$resId]);
                }
            }
        }

        // retourner seulement les mails récupérés (validés ou signés)
        return $aArgs['idsToRetrieve'];
    }

    public static function getEtatDossier($aArgs)
    {
        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <GetEtatDossier xmlns="http://www.srci.fr">
              <DossierID>'.$aArgs['dossier_id'].'</DossierID>
            </GetEtatDossier>
          </soap:Body>
        </soap:Envelope>';

        $opts = [
        CURLOPT_URL => $aArgs['config']['data']['url'] . '/parapheurws/service.asmx',
        CURLOPT_HTTPHEADER => [
        'content-type:text/xml;charset=\"utf-8\"',
        'accept:text/xml',
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "Content-length: ".strlen($xmlPostString),
        "Cookie:".$aArgs['sessionId'],
        "SOAPAction: \"http://www.srci.fr/GetEtatDossier\""
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS  => $xmlPostString
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $opts);
        $rawResponse = curl_exec($curl);

        $data = simplexml_load_string($rawResponse);
        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->GetEtatDossierResponse->GetEtatDossierResult;

        return $response;
    }

    public static function getAnnotations($aArgs)
    {
        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <GetAnnotations xmlns="http://www.srci.fr">
              <messageID>'.$aArgs['dossier_id'].'</messageID>
            </GetAnnotations>
          </soap:Body>
        </soap:Envelope>';

        $opts = [
        CURLOPT_URL => $aArgs['config']['data']['url'] . '/parapheurws/service.asmx',
        CURLOPT_HTTPHEADER => [
        'content-type:text/xml;charset=\"utf-8\"',
        'accept:text/xml',
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "Content-length: ".strlen($xmlPostString),
        "Cookie:".$aArgs['sessionId'],
        "SOAPAction: \"http://www.srci.fr/GetAnnotations\""
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS  => $xmlPostString
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $opts);
        $rawResponse = curl_exec($curl);

        $data = simplexml_load_string($rawResponse);
        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->GetAnnotationsResponse->GetAnnotationsResult;

        return $response;
    }

    public static function getDossier($aArgs)
    {
        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <GetDossier xmlns="http://www.srci.fr">
              <messageID>'.$aArgs['dossier_id'].'</messageID>
            </GetDossier>
          </soap:Body>
        </soap:Envelope>';

        $opts = [
        CURLOPT_URL => $aArgs['config']['data']['url'] . '/parapheurws/service.asmx',
        CURLOPT_HTTPHEADER => [
        'content-type:text/xml;charset=\"utf-8\"',
        'accept:text/xml',
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "Content-length: ".strlen($xmlPostString),
        "Cookie:".$aArgs['sessionId'],
        "SOAPAction: \"http://www.srci.fr/GetDossier\""
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS  => $xmlPostString
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $opts);
        $rawResponse = curl_exec($curl);

        $data = simplexml_load_string($rawResponse);
        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->GetDossierResponse->GetDossierResult;

        return $response;
    }

    public static function getAnnexes($aArgs)
    {
        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <GetAnnexe xmlns="http://www.srci.fr">
              <messageID>'.$aArgs['dossier_id'].'</messageID>
              <extension>pdf</extension>
            </GetAnnexe>
          </soap:Body>
        </soap:Envelope>';

        $opts = [
        CURLOPT_URL => $aArgs['config']['data']['url'] . '/parapheurws/service.asmx',
        CURLOPT_HTTPHEADER => [
        'content-type:text/xml;charset=\"utf-8\"',
        'accept:text/xml',
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "Content-length: ".strlen($xmlPostString),
        "Cookie:".$aArgs['sessionId'],
        "SOAPAction: \"http://www.srci.fr/GetAnnexe\""
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS  => $xmlPostString
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $opts);
        $rawResponse = curl_exec($curl);

        $data = simplexml_load_string($rawResponse);
        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->GetAnnexeResponse->GetAnnexeResult;

        return $response;
    }
}
