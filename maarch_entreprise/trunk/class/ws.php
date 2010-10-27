<?php 
global $SOAP_dispatch_map;
global $XMLRPC_dispatch_map;
global $SOAP_typedef;

$XMLRPC_dispatch_map['addContact'] = Array(
                            'function' => 'addContact',
                            'signature' => array(array('string','string')),
                            'docstring' => '',
                            'method' => "apps#contacts::save"
                            );

$SOAP_dispatch_map['addContact'] = Array(
                                     'in'  => Array('in' => 'string'),
                                     'out' => Array('out' => 'string'),
                                     'method' => "apps#contacts::save"
                                     );
?>
