<?php 
global $SOAP_dispatch_map;
global $XMLRPC_dispatch_map;
global $SOAP_typedef;

$XMLRPC_dispatch_map['basketSample'] = Array(
                            'function' => 'basketSample',
                            'signature' => array(array('string','string')),
                            'docstring' => '',
                            'method' => "modules/basket#basket::save"
                            );

$SOAP_dispatch_map['basketSample'] = Array(
                                     'in'  => Array('in' => 'string'),
                                     'out' => Array('out' => 'string'),
                                     'method' => "modules/basket#basket::save"
                                     );

?>
