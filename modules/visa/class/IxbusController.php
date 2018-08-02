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
    public static function getModal(array $config)
    {
        $initializeDatas = IxbusController::getInitializeDatas($config);

        $html = '<form name="sendToExternalSB" id="sendToExternalSB" method="post" class="forms" action="#">';
        $html .= '<input type="hidden" name="chosen_action" id="chosen_action" value="end_action" />';
        $html .= '<select id="nature"><option value="val1">Valeur 1</option><option value="val2">Valeur 2</option><option value="val3">Valeur 3</option></select>';

        $html .='<div align="center">';
        $html .=' <input type="button" name="validate" id="validate" value="Valider" class="button" ' .
                'onclick="valid_action_form(\'sendToExternalSB\', \'' . $config['getFormData']['pathManageAction'] .
                '\', \'' . $config['getFormData']['actionId'] . '\', \'value\', \'res_letterbox\', \'null\', \'letterbox_coll\', \'' .
                $config['getFormData']['mode'] . '\');" />';
        $html .='<input type="button" name="cancel" id="cancel" class="button" value="annuler"/>';
        $html .='</div>';
        $html .='</form>';

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

        $opts = [
        CURLOPT_URL => 'http://parapheur.orleans.fr/parapheurws/service.asmx',
        CURLOPT_HTTPHEADER => [
        'content-type:text/xml;charset="utf-8"',
        'accept:text/xml',
        'Cache-Control: no-cache',
        'Pragma: no-cache',
        'Content-length: ' . strlen($xmlPostString),
        'SOAPAction: "http://www.srci.fr/CreateSession"'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS  => $xmlPostString
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $opts);
        $rawResponse = curl_exec($curl);

        $data = simplexml_load_string($rawResponse);
        $response = $data->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->CreateSessionResponse;

        return $response;
    }

    public static function getInitializeDatas($config)
    {
        $sessionId = IxbusController::createSession($config);
        $rawResponse['natures'] = IxbusController::getNature(['config' => $config, 'sessionId' => $sessionId]);
        $rawResponse['usersList'] = IxbusController::getUsersList(['config' => $config, 'sessionId' => $sessionId]);

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

        return $rawResponse;
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

        return $rawResponse;
    }
}
