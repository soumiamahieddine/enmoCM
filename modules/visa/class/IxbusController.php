<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Ixbus Controller
 * @author dev@maarch.org
 */


class IxbusController
{
    public static function getModal($config)
    {
        $initializeDatas = IxbusController::getInitializeDatas($config);

        $html .= '<label for="nature">' . _NATURE_IXBUS . '</label><select name="nature" id="nature">';
        if (!empty($initializeDatas['natures']->Classeur)) {
            foreach ($initializeDatas['natures']->Classeur as $value) {
                $html .= '<option value="';
                $html .= $value->Libelle;
                $html .= '">';
                $html .= $value->Libelle;
                $html .= '</option>';
            }
        }
        $html .= '</select><br /><br />';

        // $initializeDatas['messagesModel'] = ['12' => 'modele courrier', '34' => 'DRH'];

        $html .= '<label for="messageModel">' . _WORKFLOW_MODEL_IXBUS . '</label><select name="messageModel" id="messageModel">';
        foreach ($initializeDatas['messagesModel'] as $key => $value) {
            $html .= '<option value="';
            $html .= $value;
            $html .= '">';
            $html .= $value;
            $html .= '</option>';
        }
        $html .= '</select><br /><br />';
        $html .= '<label for="loginIxbus">'._ID_IXBUS.'</label><input name="loginIxbus" id="loginIxbus"/><br /><br />';
        $html .= '<label for="passwordIxbus">'._PASSWORD_IXBUS.'</label><input type="password" name="passwordIxbus" id="passwordIxbus"/><br /><br />';

        return $html;
    }

    public static function createSession($config)
    {
        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
                        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                        <soap:Body>
                            <CreateSession xmlns="http://www.srci.fr">
                            <NomUtilisateur>'.$config['data']['userId'].'</NomUtilisateur>
                            <MotdePasse>'.$config['data']['password'].'</MotdePasse>
                            <OrganisationID>'.(int)$config['data']['organizationId'].'</OrganisationID>
                            </CreateSession>
                        </soap:Body>
                        </soap:Envelope>';

        $data = \SrcCore\models\CurlModel::execSOAP([
            'xmlPostString' => $xmlPostString,
            'url'           => $config['data']['url'] . '/parapheurws/service.asmx',
            'soapAction'    => 'http://www.srci.fr/CreateSession',
            'options'       => [CURLOPT_HEADER => 1]
        ]);

        $cookie = '';
        foreach ($data['cookies'] as $key => $value) {
            $cookie = $key . '=' . $value . ';';
        }

        return $cookie;
    }

    public static function getInitializeDatas($config)
    {
        $sessionId = IxbusController::createSession($config);
        $rawResponse['natures']       = IxbusController::getNature(['config' => $config, 'sessionId' => $sessionId]);
        // $rawResponse['usersList']     = IxbusController::getUsersList(['config' => $config, 'sessionId' => $sessionId]);
        $messagesModels = IxbusController::getMessagesModel(['config' => $config, 'sessionId' => $sessionId]);

        $rawResponse['messagesModel'] = [];
        if (!empty($rawResponse['natures']->Classeur)) {
            foreach ($rawResponse['natures']->Classeur as $nature) {
                foreach ($messagesModels->Message as $message) {
                    if ($message->Identifiant == 392213) {
                        $messageModel = IxbusController::getMessageNature(['config' => $config, 'messageId' => $message->Identifiant, 'sessionId' => $sessionId]);
                        if ((string)$messageModel->IdentifiantClasseur == (string)$nature->Identifiant) {
                            $rawResponse['messagesModel'][(string)$messageModel->IdentifiantMessage] = (string)$message->IdentifiantSpecifique;
                        }
                    }
                }
            }
        }

        return $rawResponse;
    }

    public static function getNature($aArgs)
    {
        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
                        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                        <soap:Body>
                            <GetNaturesAvecDroitsCreer xmlns="http://www.srci.fr">
                            <organisationID>'.$aArgs['config']['data']['organizationId'].'</organisationID>
                            </GetNaturesAvecDroitsCreer>
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
        "SOAPAction: \"http://www.srci.fr/GetNaturesAvecDroitsCreer\""
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS  => $xmlPostString
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $opts);
        $rawResponse = curl_exec($curl);

        $data = simplexml_load_string($rawResponse);
        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->GetNaturesAvecDroitsCreerResponse->GetNaturesAvecDroitsCreerResult;

        return $response;
    }

    public static function getMessagesModel($aArgs)
    {
        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <GetMessagesModel xmlns="http://www.srci.fr">
              <utilisateurID>8</utilisateurID>
              <organisationID>'.$aArgs['config']['data']['organizationId'].'</organisationID>
              <serviceID>-1</serviceID>
              <typeMessage>Production</typeMessage>
            </GetMessagesModel>
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
        "SOAPAction: \"http://www.srci.fr/GetMessagesModel\""
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS  => $xmlPostString
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $opts);
        $rawResponse = curl_exec($curl);

        $data = simplexml_load_string($rawResponse);
        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->GetMessagesModelResponse->GetMessagesModelResult;

        return $response;
    }

    public static function getMessageNature($aArgs)
    {
        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <GetMessageNature xmlns="http://www.srci.fr">
              <messageID>'.$aArgs['messageId'].'</messageID>
            </GetMessageNature>
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
        "SOAPAction: \"http://www.srci.fr/GetMessageNature\""
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS  => $xmlPostString
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $opts);
        $rawResponse = curl_exec($curl);

        $data = simplexml_load_string($rawResponse);
        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->GetMessageNatureResponse->GetMessageNatureResult;

        return $response;
    }
    
    public static function getInfoUtilisateur($aArgs)
    {
        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <getUtilisateur xmlns="http://www.srci.fr">
              <NomUtilisateur>' . $aArgs['login'] . '</NomUtilisateur>
              <MotdePasse>' . $aArgs['password'] . '</MotdePasse>
            </getUtilisateur>
          </soap:Body>
        </soap:Envelope>';

        $opts = [
        CURLOPT_URL => $aArgs['config']['data']['url'] . '/ixbuswebws/Utilisateur.asmx',
        CURLOPT_HTTPHEADER => [
        'content-type:text/xml;charset=\"utf-8\"',
        'accept:text/xml',
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "Content-length: ".strlen($xmlPostString),
        "SOAPAction: \"http://www.srci.fr/getUtilisateur\""
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS  => $xmlPostString
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $opts);
        $rawResponse = curl_exec($curl);

        $data = simplexml_load_string($rawResponse);
        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->getUtilisateurResponse->getUtilisateurResult;

        return $response;
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
            'where'     => ['res_id_master = ?', 'attachment_type not in (?)', "status not in ('DEL', 'OBS', 'FRZ')", 'in_signature_book = TRUE'],
            'data'      => [$aArgs['resIdMaster'], ['incoming_mail_attachment', 'print_folder']]
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

            $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
              <soap:Body>
                <SendDossier xmlns="http://www.srci.fr">
                  <ContenuDocumentZip>'. $encodedZipFile .'</ContenuDocumentZip>
                  <NomDocumentPrincipal>'. $adrInfo['filename'] . '</NomDocumentPrincipal>
                  <NomDossier>'. $value['title'] .'</NomDossier>
                  <NomModele>'. $aArgs['messageModel'] .'</NomModele>
                  <NomNature>'. $aArgs['classeurName'] .'</NomNature>
                  <DateLimite>2019-12-17</DateLimite>
                  <LoginResponsable>'. $userInfo->NomUtilisateur .'</LoginResponsable>
                  <Confidentiel>false</Confidentiel>
                  <DocumentModifiable>true</DocumentModifiable>
                  <AnnexesSignables>false</AnnexesSignables>
                  <SignatureManuscrite>false</SignatureManuscrite>
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
                if ($etatDossier == $aArgs['config']['ixbusIdRefused']) {
                    $aArgs['idsToRetrieve'][$version][$resId]->status = 'refused';
                    $notes = IxbusController::getAnnotations(['config' => $aArgs['config'], 'sessionId' => $sessionId, 'dossier_id' => $value->external_id]);
                    $aArgs['idsToRetrieve'][$version][$resId]->noteContent = $notes->Annotation;
                // Validated
                } elseif ($etatDossier == $aArgs['config']['ixbusIdValidated']) {
                    $aArgs['idsToRetrieve'][$version][$resId]->status = 'validated';
                    $signedDocument = IxbusController::getAnnexes(['config' => $aArgs['config'], 'sessionId' => $sessionId, 'dossier_id' => $value->external_id]);
                    $notes = IxbusController::getAnnotations(['config' => $aArgs['config'], 'sessionId' => $sessionId, 'dossier_id' => $value->external_id]);
                    $aArgs['idsToRetrieve'][$version][$resId]->noteContent = $notes->Annotation->Texte;
                // $aArgs['idsToRetrieve'][$version]['923']->format = 'jpg'; // format du fichier récupéré
                    // $aArgs['idsToRetrieve'][$version]['923']->encodedFile = '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';
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
