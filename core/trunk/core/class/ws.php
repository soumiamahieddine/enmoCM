<?php 
global $SOAP_dispatch_map;
global $XMLRPC_dispatch_map;
global $SOAP_typedef;

$SOAP_dispatch_map['docserversWsFunction'] = array(
										'in'  => array('theArg' => 'string'),
										'out' => array('theReturn' => 'string'),
										'method' => "core#docservers::docserverWs"
									);
$SOAP_dispatch_map['docserverSave'] = array(
										'in'  => array('docserver' => '{urn:MySoapServer}docserverObject', 'mode' => 'string'),
										'out' => array('out' => '{urn:MySoapServer}returnArray'),
										'method' => "core#docservers::save"
									);
$SOAP_typedef['docserverArray'] = array('docserver_id'=>'string',
										'docserver_type_id'=>'string',
										'device_label'=>'string',
										'is_readonly'=>'boolean',
										'size_limit_number'=>'bigint',
										'path_template'=>'string',
										'coll_id'=>'string',
										'priority_number'=>'integer',
										'docserver_location_id'=>'string',
										'adr_priority_number'=>'integer'
									);
$SOAP_typedef['docserverObject'] = array('item' => '{urn:MySoapServer}docserverArray');
$SOAP_typedef['returnArray'] = array(	'status'=>'string',
										'value'=>'string',
										'error'=>'string'
									);
?>
