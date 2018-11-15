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

        $html .= '<label for="nature">' . _USERS . '</label><select name="nature" id="nature">';
        if (!empty($initializeDatas['users'])) {
            foreach ($initializeDatas['users'] as $value) {
                $html .= '<option value="';
                $html .= $value['id'];
                $html .= '">';
                $html .= $value['firstname'] . ' ' . $value['lastname'];
                $html .= '</option>';
            }
        }
        $html .= '</select><br /><br />';

        // $html .= '<label for="messageModel">' . _WORKFLOW_MODEL_IXBUS . '</label><select name="messageModel" id="messageModel">';
        // foreach ($initializeDatas['messagesModel'] as $value) {
        //     $html .= '<option value="';
        //     $html .= $value;
        //     $html .= '">';
        //     $html .= $value;
        //     $html .= '</option>';
        // }
        // $html .= '</select><br /><br />';
        // $html .= '<label for="loginIxbus">'._ID_IXBUS.'</label><input name="loginIxbus" id="loginIxbus"/><br /><br />';
        // $html .= '<label for="passwordIxbus">'._PASSWORD_IXBUS.'</label><input type="password" name="passwordIxbus" id="passwordIxbus"/><br /><br />';
        // $html .= _ESIGN . '<input type="radio" name="mansignature" id="mansignature" value="false" checked="checked" />' . _HANDWRITTEN_SIGN .'<input type="radio" name="mansignature" id="mansignature" value="true" /><br /><br />';

        return $html;
    }

    public static function getInitializeDatas($config)
    {
        $rawResponse['users'] = MaarchParapheurController::getUsers(['config' => $config]);
        // $rawResponse['usersList']     = IxbusController::getUsersList(['config' => $config, 'sessionId' => $sessionId]);
        // $messagesModels = IxbusController::getMessagesModel(['config' => $config, 'sessionId' => $sessionId]);

        // $rawResponse['messagesModel'] = [];
        // if (!empty($rawResponse['natures']->Classeur)) {
        //     foreach ($rawResponse['natures']->Classeur as $nature) {
        //         foreach ($messagesModels->Message as $message) {
        //             if ($message->Identifiant == 392213) {
        //                 $messageModel = IxbusController::getMessageNature(['config' => $config, 'messageId' => $message->Identifiant, 'sessionId' => $sessionId]);
        //                 if ((string)$messageModel->IdentifiantClasseur == (string)$nature->Identifiant) {
        //                     $rawResponse['messagesModel'][(string)$messageModel->IdentifiantMessage] = (string)$message->IdentifiantSpecifique;
        //                 }
        //             }
        //         }
        //     }
        // }

        return $rawResponse;
    }

    public static function getUsers($aArgs)
    {
        $response = \SrcCore\models\CurlModel::exec(['url' => $aArgs['config']['data']['url'] . '/rest/users', 'user' => $aArgs['config']['data']['userId'], 'password' => $aArgs['config']['data']['password'], 'method' => 'GET']);

        return $response['users'];
    }

    public static function sendDatas($aArgs)
    {
        $sessionId = IxbusController::createSession($aArgs['config']);
        $userInfo  = IxbusController::getInfoUtilisateur(['config' => $aArgs['config'], 'login' => $aArgs['loginIxbus'], 'password' => $aArgs['passwordIxbus']]);

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

            $encodedZipFile = IxbusController::createZip(['filepath' => $filePath, 'filename' => $adrInfo['filename'], 'res_id_master' => $aArgs['resIdMaster']]);

            $mainResource = \Resource\models\ResModel::getExtById(['resId' => $aArgs['resIdMaster'], 'select' => ['process_limit_date']]);
            if (empty($mainResource['process_limit_date'])) {
                $processLimitDate = date('Y-m-d', strtotime(date("Y-m-d"). ' + 14 days'));
            } else {
                $processLimitDateTmp = explode(" ", $mainResource['process_limit_date']);
                $processLimitDate = $processLimitDateTmp[0];
            }

            $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
              <soap:Body>
                <SendDossier xmlns="http://www.srci.fr">
                  <ContenuDocumentZip>'. $encodedZipFile .'</ContenuDocumentZip>
                  <NomDocumentPrincipal>'. $adrInfo['filename'] . '</NomDocumentPrincipal>
                  <NomDossier>'. $value['title'] .'</NomDossier>
                  <NomModele>'. $aArgs['messageModel'] .'</NomModele>
                  <NomNature>'. $aArgs['classeurName'] .'</NomNature>
                  <DateLimite>'.$processLimitDate.'</DateLimite>
                  <LoginResponsable>'. $userInfo->NomUtilisateur .'</LoginResponsable>
                  <Confidentiel>false</Confidentiel>
                  <DocumentModifiable>true</DocumentModifiable>
                  <AnnexesSignables>false</AnnexesSignables>
                  <SignatureManuscrite>'.$aArgs['manSignature'].'</SignatureManuscrite>
                </SendDossier>
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
                    "Cookie:".$sessionId,
                    "SOAPAction: \"http://www.srci.fr/SendDossier\""
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS  => $xmlPostString
            ];
    
            $curl = curl_init();
            curl_setopt_array($curl, $opts);
            $rawResponse = curl_exec($curl);
    
            $data = simplexml_load_string($rawResponse);
            $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->SendDossierResponse->SendDossierResult;

            $attachmentToFreeze[$value['res_id']] = (string)$response;
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
            
            $adrInfo             = \Convert\models\AdrModel::getConvertedDocumentById(['resId' => $aArgs['res_id_master'], 'collId' => 'letterbox_coll', 'type' => 'PDF']);
            $docserverInfo       = \Docserver\models\DocserverModel::getByDocserverId(['docserverId' => $adrInfo['docserver_id']]);
            $arrivedMailfilePath = $docserverInfo['path_template'] . str_replace('#', '/', $adrInfo['path']) . $adrInfo['filename'];
            $zip->addFile($arrivedMailfilePath, 'courrier_arrivee.pdf');

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
        $sessionId = IxbusController::createSession($aArgs['config']);

        foreach (['noVersion', 'isVersion'] as $version) {
            foreach ($aArgs['idsToRetrieve'][$version] as $resId => $value) {
                $etatDossier = IxbusController::getEtatDossier(['config' => $aArgs['config'], 'sessionId' => $sessionId, 'dossier_id' => $value->external_id]);
    
                // Refused
                if ((string)$etatDossier == $aArgs['config']['data']['ixbusIdEtatRefused']) {
                    $aArgs['idsToRetrieve'][$version][$resId]->status = 'refused';
                    $notes = IxbusController::getDossier(['config' => $aArgs['config'], 'sessionId' => $sessionId, 'dossier_id' => $value->external_id]);
                    $aArgs['idsToRetrieve'][$version][$resId]->noteContent = (string)$notes->MotifRefus;
                // Validated
                } elseif ((string)$etatDossier == $aArgs['config']['data']['ixbusIdEtatValidated']) {
                    $aArgs['idsToRetrieve'][$version][$resId]->status = 'validated';
                    $signedDocument = IxbusController::getAnnexes(['config' => $aArgs['config'], 'sessionId' => $sessionId, 'dossier_id' => $value->external_id]);
                    $aArgs['idsToRetrieve'][$version][$resId]->format = 'pdf'; // format du fichier récupéré
                    $aArgs['idsToRetrieve'][$version][$resId]->encodedFile = (string)$signedDocument->Fichier;

                    $notes = IxbusController::getAnnotations(['config' => $aArgs['config'], 'sessionId' => $sessionId, 'dossier_id' => $value->external_id]);
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
