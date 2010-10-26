<?php 
global $SOAP_dispatch_map;
global $XMLRPC_dispatch_map;
global $SOAP_typedef;

$SOAP_dispatch_map['docserversWsFunction'] = Array(
										'in'  => Array('theArg' => 'string'),
										'out' => Array('theReturn' => 'string'),
										'method' => "core#docservers::docserverWs"
                                     );
?>
