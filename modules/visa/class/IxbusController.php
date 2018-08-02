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

        $html .= '<select id="nature"><option value="val1">Valeur 1</option><option value="val2">Valeur 2</option><option value="val3">Valeur 3</option></select>';

        $initializeDatas['messagesModel'] = ['12' => 'modele courrier', '34' => 'DRH'];

        $html .= '<select name="messageModel" id="messageModel">';
        $html .= '<option value="">' . _SELECT_MESSAGE_MODEL_IXBUS . '</option>';
        foreach ($initializeDatas['messagesModel'] as $key => $value) {
            $html .= '<option value="';
            $html .= $key;
            $html .= '">';
            $html .= $value;
        }
        $html .= '</select><br />';

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
            'url'           => 'http://parapheur.orleans.fr/parapheurws/service.asmx',
            'soapAction'    => 'http://www.srci.fr/CreateSession'
        ]);

        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->CreateSessionResponse;

        return $response;
    }

    public static function getInitializeDatas($config)
    {
        $sessionId = IxbusController::createSession($config);
        $rawResponse['natures'] = IxbusController::getNature(['config' => $config, 'sessionId' => $sessionId]);
        $rawResponse['usersList'] = IxbusController::getUsersList(['config' => $config, 'sessionId' => $sessionId]);
        $rawResponse['messagesModel'] = IxbusController::getMessagesModel(['config' => $config, 'sessionId' => $sessionId]);

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
        CURLOPT_URL => 'http://parapheur.orleans.fr/parapheurws/service.asmx',
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
        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->GetNaturesAvecDroitsCreerResponse;

        return $response;
    }

    public static function getUsersList($aArgs)
    {
        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
                        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                        <soap:Body>
                            <GetListeUtilisateursDroitCreer xmlns="http://www.srci.fr">
                            <organisationID>'.$aArgs['config']['data']['organizationId'].'</organisationID>
                            </GetListeUtilisateursDroitCreer>
                        </soap:Body>
                        </soap:Envelope>';

        $opts = [
        CURLOPT_URL => 'http://parapheur.orleans.fr/parapheurws/service.asmx',
        CURLOPT_HTTPHEADER => [
        'content-type:text/xml;charset=\"utf-8\"',
        'accept:text/xml',
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "Content-length: ".strlen($xmlPostString),
        "Cookie:".$aArgs['sessionId'],
        "SOAPAction: \"http://www.srci.fr/GetListeUtilisateursDroitCreer\""
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS  => $xmlPostString
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $opts);
        $rawResponse = curl_exec($curl);

        $data = simplexml_load_string($rawResponse);
        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->GetListeUtilisateursDroitCreerResponse;

        return $response;
    }

    public static function getMessagesModel($aArgs)
    {
        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <GetMessagesModel xmlns="http://www.srci.fr">
              <utilisateurID>123</utilisateurID>
              <organisationID>'.$aArgs['config']['data']['organizationId'].'</organisationID>
              <serviceID>123</serviceID>
              <typeMessage>Test</typeMessage>
            </GetMessagesModel>
          </soap:Body>
        </soap:Envelope>';

        $opts = [
        CURLOPT_URL => 'http://parapheur.orleans.fr/parapheurws/service.asmx',
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
        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->GetMessagesModelResponse;

        return $response;
    }

    public static function getMessageNature($aArgs)
    {
        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <GetMessageNature xmlns="http://www.srci.fr">
              <messageID>123</messageID>
            </GetMessageNature>
          </soap:Body>
        </soap:Envelope>';

        $opts = [
        CURLOPT_URL => 'http://parapheur.orleans.fr/parapheurws/service.asmx',
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
        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->GetMessageNatureResponse;

        return $response;
    }

    public static function sendDatas($aArgs)
    {
        $attachments = \Attachment\models\AttachmentModel::getOnView([
            'select'    => [
                'res_id', 'res_id_version', 'title', 'identifier', 'attachment_type',
                'status', 'typist', 'path', 'filename', 'updated_by', 'creation_date',
                'validation_date', 'format', 'relation', 'dest_user', 'dest_contact_id',
                'dest_address_id', 'origin', 'doc_date', 'attachment_id_master'
            ],
            'where'     => ['res_id_master = ?', 'attachment_type not in (?)', "status not in ('DEL', 'OBS')", 'in_signature_book = TRUE'],
            'data'      => [$aArgs['resIdMaster'], ['incoming_mail_attachment', 'print_folder']]
        ]);

        $attachmentToFreeze = [];
        foreach ($attachments as $value) {
            $attachmentToFreeze[] = $value['res_id'];
        }

        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <Transmettre xmlns="http://www.srci.fr">
              <NomUtilisateur>'.$config['data']['userId'].'</NomUtilisateur>
              <MotdePasse>'.$config['data']['password'].'</MotdePasse>
              <IdentifiantOrganisation>'.$config['data']['organizationId'].'</IdentifiantOrganisation>
              <attach>
                <Document>
                  <Attachment>
                    <Name>string</Name>
                    <Content>base64Binary</Content>
                    <Size>int</Size>
                  </Attachment>
                  <Attachment>
                    <Name>string</Name>
                    <Content>base64Binary</Content>
                    <Size>int</Size>
                  </Attachment>
                </Document>
                <Description>xml</Description>
              </attach>
              <NomDossier>string</NomDossier>
              <DateLimite>dateTime</DateLimite>
              <MessageModele>int</MessageModele>
              <IdClasseur>int</IdClasseur>
              <Responsable>int</Responsable>
            </Transmettre>
          </soap:Body>
        </soap:Envelope>';

        $opts = [
        CURLOPT_URL => 'http://parapheur.orleans.fr/parapheurws/3.21/MessagerieImprimante.asmx',
        CURLOPT_HTTPHEADER => [
        'content-type:text/xml;charset=\"utf-8\"',
        'accept:text/xml',
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "Content-length: ".strlen($xmlPostString),
        "Cookie:".$aArgs['sessionId'],
        "SOAPAction: \"http://www.srci.fr/Transmettre\""
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS  => $xmlPostString
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $opts);
        $rawResponse = curl_exec($curl);

        $data = simplexml_load_string($rawResponse);
        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->TransmettreResponse;

        return $attachmentToFreeze;
    }
}
