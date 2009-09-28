<?php 
global $SOAP_dispatch_map;
global $XMLRPC_dispatch_map;
global $SOAP_typedef;

$XMLRPC_dispatch_map['sendDocumentForECA'] = Array(
                            'function' => 'sendDocument',
                            'signature' => Array(Array('integer', 'string', 'base64')),
                            'docstring' => 'Send a Maarch document');
$SOAP_dispatch_map['sendDocumentForECA'] = Array(
                                 'in'  => Array('gedId' => 'integer', 'tableName' => 'string'),
                                 'out' => Array('out' => 'array')
                               );
function sendDocumentForECA($gedId, $tableName){
    return sendDocument($gedId, $tableName);
}
?>
