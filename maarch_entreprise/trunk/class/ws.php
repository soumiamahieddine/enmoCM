<?php 
global $SOAP_dispatch_map;
global $XMLRPC_dispatch_map;
global $SOAP_typedef;

$XMLRPC_dispatch_map['echoStringSample'] = Array(
                            'function' => 'echoStringSample',
                            'signature' => array(array('string','string')),
                            'docstring' => '',
                            'method' => "apps#users::save"
                            );

$SOAP_dispatch_map['echoStringSample'] = Array(
                                     'in'  => Array('in' => 'string'),
                                     'out' => Array('out' => 'string'),
                                     'method' => "apps#users::save"
                                     );
function echoStringSample($in) {
    return $in;
}
?>
