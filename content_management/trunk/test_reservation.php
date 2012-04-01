<?php

//$_SESSION['user']['UserId'] = 'toto';

//Create XML
function createXML($rootName, $parameters)
{
    global $debug, $debugFile;
    $rXml = new DomDocument("1.0","UTF-8");
    $rRootNode = $rXml->createElement($rootName);
    $rXml->appendChild($rRootNode);
    if (is_array($parameters)) {
        foreach ($parameters as $kPar => $dPar) {
            $node = $rXml->createElement($kPar,$dPar);
            $rRootNode->appendChild($node);
        }
    } else {
        $rRootNode->nodeValue = $parameters;
    }
    if ($debug) {
        $rXml->save($debugFile);
    }
    //header("content-type: application/xml");
    echo $rXml->saveXML();
    $text = $rXml->saveXML();
    $inF = fopen('wsresult.log','a');
    fwrite($inF, $text);
    fclose($inF);
    exit;
}

require_once 'modules/content_management/class/class_content_manager_tools.php';
$cM = new content_management_tools();
$cM->deleteExpiredCM();
$reservedBy = array();
$reservedBy = $cM->isReservedBy(
    $_REQUEST['objectTable'],
    $_REQUEST['objectId']
);

if (
    $reservedBy['status'] == 'ok' 
    && $reservedBy['user_id'] != $_SESSION['user']['UserId']
) {
    if ($reservedBy['fullname'] <> 'empty') {
        createXML(
            'ERROR',
            _RESPONSE_ALREADY_RESERVED . ' ' . _BY . ' : ' 
            . $reservedBy['fullname']
        );
    } else {
        createXML('ERROR', _RESPONSE_ALREADY_RESERVED);
    }
}

$reservationId = $cM->reserveObject(
    $_REQUEST['objectTable'],
    $_REQUEST['objectId'],
    $_SESSION['user']['UserId']
);
echo $reservationId;
$cM->closeReservation($reservationId);

//$_SESSION['user']['UserId'] = 'pparker';
