<?php 
global $SOAP_dispatch_map;
global $XMLRPC_dispatch_map;
global $SOAP_typedef;

// COMMON
$SOAP_typedef['returnArray'] = array(	'status'=>'string',
										'value'=>'string',
										'error'=>'string'
									);
// DOCSERVERS
$SOAP_typedef['docservers'] = array(	'docserver_id'=>'string',
										'docserver_type_id'=>'string',
										'device_label'=>'string',
										'is_readonly'=>'boolean',
										'size_limit_number'=>'string',
										'path_template'=>'string',
										'coll_id'=>'string',
										'priority_number'=>'string',
										'docserver_location_id'=>'string',
										'adr_priority_number'=>'string'
									);
$SOAP_typedef['returnViewResource'] = array('status'=>'string',
										'mime_type'=>'string',
										'ext'=>'string',
										'file_content'=>'string',
										'tmp_path'=>'string',
										'error'=>'string'
									);
$SOAP_dispatch_map['docserverSave'] = array(
										'in'  => array('docserver' => '{urn:MySoapServer}docservers', 'mode' => 'string'),
										'out' => array('out' => '{urn:MySoapServer}returnArray'),
										'method' => "core#docservers::save"
									);
$SOAP_dispatch_map['docserverDelete'] = array(
										'in'  => array('docserver' => '{urn:MySoapServer}docservers'),
										'out' => array('out' => '{urn:MySoapServer}returnArray'),
										'method' => "core#docservers::delete"
									);
$SOAP_dispatch_map['docserverEnable'] = array(
										'in'  => array('docserver' => '{urn:MySoapServer}docservers'),
										'out' => array('out' => '{urn:MySoapServer}returnArray'),
										'method' => "core#docservers::enable"
									);									
$SOAP_dispatch_map['docserverDisable'] = array(
										'in'  => array('docserver' => '{urn:MySoapServer}docservers'),
										'out' => array('out' => '{urn:MySoapServer}returnArray'),
										'method' => "core#docservers::disable"
									);
$SOAP_dispatch_map['docserverGet'] = array(
										'in'  => array('docserverId' => 'string'),
										'out' => array('out' => '{urn:MySoapServer}docservers'),
										'method' => "core#docservers::getWs"
									);
$SOAP_dispatch_map['viewResource'] = array(
										'in'  => Array('gedId' => 'integer', 'tableName' => 'string'),
										'out' => Array('out' => '{urn:MySoapServer}returnViewResource'),
										'method' => "core#docservers::viewResource"
									);
// DOCSERVERS LOCATIONS
$SOAP_typedef['docserversLocations'] = array(	'docserver_location_id'=>'string',
												'ipv4'=>'string',
												'ipv6'=>'string',
												'net_domain'=>'string',
												'mask'=>'string',
												'net_link'=>'string'
											);
$SOAP_dispatch_map['docserverLocationSave'] = array(
										'in'  => array('docserver_location' => '{urn:MySoapServer}docserversLocations', 'mode' => 'string'),
										'out' => array('out' => '{urn:MySoapServer}returnArray'),
										'method' => "core#docservers_locations::save"
										);
/*$SOAP_dispatch_map['docserverLocationDelete'] = array(
										'in'  => array('docserver_location' => '{urn:MySoapServer}docserversLocations'),
										'out' => array('out' => '{urn:MySoapServer}returnArray'),
										'method' => "core#docservers_locations::delete"
									);*/
?>
