<?php

$confirm = true;

$etapes = ['form'];

function get_form_txt($values, $path_manage_action,  $id_action, $table, $module, $coll_id, $mode)
{
    if (file_exists("custom/{$_SESSION['custom_override_id']}/modules/visa/xml/remoteSignatoryBooks.xml")) {
        $path = "custom/{$_SESSION['custom_override_id']}/modules/visa/xml/remoteSignatoryBooks.xml";
    } else {
        $path = 'modules/visa/xml/remoteSignatoryBooks.xml';
    }

    $config = [];
    if (file_exists($path)) {
        $loadedXml = simplexml_load_file($path);
        if ($loadedXml) {
            $config['id'] = (string)$loadedXml->signatoryBookEnabled;
        }
    }

    if ($config['id'] == 'ixbus') {
        include_once 'modules/visa/class/IxbusController.php';

        $html = IxbusController::getModal();
    }

    return addslashes($html);
}

function manage_send($aId)
{
    $result = '';

    $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>
                        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                            <soap:Body>
                                <storeResourceRequest xmlns="http://connecting.website.com/WSDL_Service">
                                    <PRICE>tata</PRICE>
                                </storeResourceRequest>
                            </soap:Body>
                        </soap:Envelope>';
                            
    $opts = [
        CURLOPT_URL => '',
        CURLOPT_HTTPHEADER => [
            'content-type:text/xml;charset=\"utf-8\"',
            'accept:text/xml',
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"http://tracmedia.org/InTheLife\""
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => 'superadmin:superadmin',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS  => $xmlPostString
    ];

    $curl = curl_init();
    curl_setopt_array($curl, $opts);
    $rawResponse = curl_exec($curl);


    return ['result' => $result, 'history_msg' => ''];
}
