<?php 

if(!isset($SOAP_dispatch_map)) {
    $SOAP_dispatch_map = Array();
}
if (!isset($XMLRPC_dispatch_map)) {
    $XMLRPC_dispatch_map = Array();
}
if (!isset($SOAP_typedef)) {
    $SOAP_typedef = Array();
}

$SOAP_dispatch_map['echoString'] = Array(
                                     'in'  => Array('in' => 'string'),
                                     'out' => Array('out' => 'string')
                                   );
function echoString($in) {
    return $in;
}

/*
$SOAP_dispatch_map['stringToArray'] = Array(
                                     'in'  => Array('in' => 'string'),
                                     'out' => Array('out' => 'Array')
                                   );
function stringToArray($in){
    return array($in, 'tableau');
}

$SOAP_dispatch_map['intToNumberData'] = Array(
                                         'in'  => Array('in' => 'int'),
                                         'out' => Array('out' => '{urn:MySoapServer}numberData')
                                       );
$SOAP_typedef['numberData'] = Array('nombre'=>'int',
                                    'double'=>'int',
                                    'carre'=>'int');
function intToNumberData($in){
    return array('nombre' => $in,
                 'double' => $in*2,
                 'carre'=> $in*$in);
}

$SOAP_dispatch_map['wordsToString'] = Array(
                                     'in'  => Array('in' => 'Array'),
                                     'out' => Array('out' => 'string')
                                   );
function wordsToString($in){
    return implode(' ', $in);
}*/

/*
$SOAP_dispatch_map['twoWordsToString'] = Array(
                                     'in'  => Array('in' => '{urn:MySoapServer}twoWords'),
                                     'out' => Array('out' => 'string')
                                   );
$SOAP_typedef['twoWords'] = Array('premier'=>'string',
                                               'dernier'=>'string');
function twoWordsToString($in){
    return $in->premier.' + blah + '.$in->dernier;
}*/




/*$SOAP_dispatch_map['getImage'] = Array(
                                 'in' => Array(),
                                 'out' => Array('out' => 'base64')
                               );
function getImage(){
    $content = file_get_contents('logo.gif',FILE_BINARY);
    $encodedContent = base64_encode($content);
    return $encodedContent;
}*/

?>
