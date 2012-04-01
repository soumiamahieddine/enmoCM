<?php

//print_r($_REQUEST);

//Create XML
function createXML($root_name,$parameters) 
{
    global $debug, $debug_file;
    $r_xml = new DomDocument("1.0","UTF-8");
    $r_root_node = $r_xml->createElement($root_name);
    $r_xml->appendChild($r_root_node);
    if (is_array($parameters)) {
        foreach ($parameters as $k_par => $d_par) {
            $node = $r_xml->createElement($k_par,$d_par);
            $r_root_node->appendChild($node);
        }
    } else {
        $r_root_node->nodeValue = $parameters;
    }
    if ($debug) {
        $r_xml->save($debug_file);
    }
    header("content-type: application/xml");
    echo $r_xml->saveXML();
    $text = $r_xml->saveXML();
    $inF = fopen('wsresult.log','a');
    fwrite($inF, $text);
    fclose($inF);
    exit;
}

$result = array(
	'STATUS' => 'ok',
	'ID' => '106',
	'APP_PATH' => 'c:\\programmes\\',
	'FILENAME' => 'test.odt',
	'ERROR' => '',
);

createXML('ERROR', 'an error');

//createXML('SUCCESS', $result);
