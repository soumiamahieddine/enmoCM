<?php 
global $SOAP_dispatch_map;
global $XMLRPC_dispatch_map;
global $SOAP_typedef;


// COMMON
$SOAP_typedef['returnArray'] = array(   'status'=>'string',
                                        'value'=>'string',
                                        'error'=>'string'
                                    );
/**************************************************************************************************/
// DOCSERVERS
$SOAP_typedef['docservers'] = array(    'docserver_id'=>'string',
                                        'docserver_type_id'=>'string',
                                        'device_label'=>'string',
                                        'is_readonly'=>'string',
                                        'size_limit_number'=>'string',
                                        'path_template'=>'string',
                                        'coll_id'=>'string',
                                    );
$SOAP_typedef['returnViewResource'] = array('status'=>'string',
                                            'mime_type'=>'string',
                                            'ext'=>'string',
                                            'file_content'=>'string',
                                            'tmp_path'=>'string',
                                            'file_path'=>'string',
                                            'called_by_ws'=>'boolean',
                                            'error'=>'string'
                                    );
$SOAP_dispatch_map['viewResource'] = array(
                                        'in'  => Array('gedId' => 'string', 'tableName' => 'string', 'adrTableName' => 'string', 'calledByWS' => 'string'),
                                        'out' => Array('return' => '{urn:MaarchSoapServer}returnViewResource'),
                                        'method' => "core#docservers::viewResource"
                                    );
/**************************************************************************************************/
// DOCSERVERS TYPES
$SOAP_typedef['docserverTypes'] = array(    'docserver_type_id'=>'string',
                                            'docserver_type_label'=>'string',
                                            'fingerprint_mode'=>'string'
                                            );
/**************************************************************************************************/
// USERS
$SOAP_typedef['users'] = array(    'user_id'=>'string',
                                'password'=>'string',
                                'firstname'=>'string',
                                'lastname'=>'string',
                                'phone'=>'string',
                                'mail'=>'string',
                                'loginmode'=>'string'
                                );

$SOAP_dispatch_map['userDelete'] = array(
                                        'in'  => array('user' => '{urn:MaarchSoapServer}users'),
                                        'out' => array('out' => '{urn:MaarchSoapServer}returnArray'),
                                        'method' => "core#users::delete"
                                    );

$SOAP_typedef['returnArrayUser'] = array(   'userEntities'=>'{urn:MaarchSoapServer}arrayOfEntities',
                                            'isUser'=>'boolean',
                                    );

$SOAP_typedef['arrayOfEntities'] = array(
    array(
        'arrayOfEntitiesContent' => '{urn:MaarchSoapServer}arrayOfEntitiesContent'
    )
);

$SOAP_typedef['arrayOfEntitiesContent'] = array(
    'USER_ID' => 'string',
    'ENTITY_ID' => 'string',
    'PRIMARY' => 'string',
    'ROLE' => 'string',
);

$SOAP_dispatch_map['checkUserMail'] = array(
                                        'in'  => array('userMail' => 'string'),
                                        'out' => array('out' => '{urn:MaarchSoapServer}returnArrayUser'),
                                        'method' => "core#users::checkUserMail"
                                        );

#####################################
## Web Service de versement de données issue du gros scanner
#####################################
$SOAP_typedef['arrayOfData'] = array(
    array(
        'arrayOfDataContent' => '{urn:MaarchSoapServer}arrayOfDataContent'
    )
);

$SOAP_typedef['arrayOfDataContent'] = array(
    'column' => 'string',
    'value' => 'string',
    'type' => 'string',
);

$SOAP_typedef['returnResArray'] = array(
    'returnCode'=> 'int',
    'resId' => 'string',
    'error' => 'string'
);

$SOAP_typedef['searchParams'] = array(
    'country' => 'string',
    'docDate' => 'date',
);

$SOAP_typedef['listOfResources'] = array(
    'resid' => 'long',
    'identifier' => 'string',
    'contactName' => 'string',
    'country' => 'int',
    'amount' => 'string',
    'customer' => 'string',
    'docDate' => 'string',
);

$SOAP_typedef['docListReturnArray'] = array(
    'status'=>'string',
    'value'=>'{urn:MaarchSoapServer}listOfResources',
    'error'=>'string',
);

$SOAP_typedef['returnRetrieveMasterResByChrono'] = array(
    'returnCode'=> 'int',
    'resId' => 'string',
    'title' => 'string',
    'identifier' => 'string',
    'status' => 'string',
    'attachment_type' => 'string',
    'dest_contact_id' => 'long',
    'dest_address_id' => 'long',
    'error' => 'string'
);

$SOAP_dispatch_map['retrieveMasterResByChrono'] = array(
    'in'  => array(
        'identifier' => 'string',
        'collId' => 'string',
    ),
    'out' => array('return' => '{urn:MaarchSoapServer}returnRetrieveMasterResByChrono'),
    'method' => "core#resources::retrieveMasterResByChrono",
);
