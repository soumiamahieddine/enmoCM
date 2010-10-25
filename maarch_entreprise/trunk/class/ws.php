<?php 
global $SOAP_dispatch_map;
global $XMLRPC_dispatch_map;
global $SOAP_typedef;

$XMLRPC_dispatch_map['echoStringSample'] = Array(
                            'function' => 'echoStringSample',
                            'signature' => array(array('string','string')),
                            'docstring' => ''
                            );

$SOAP_dispatch_map['echoStringSample'] = Array(
                                     'in'  => Array('in' => 'string'),
                                     'out' => Array('out' => 'string')
                                     );
function echoStringSample($in) {
    return $in;
}
?>
