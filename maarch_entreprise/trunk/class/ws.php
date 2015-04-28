<?php
global $SOAP_dispatch_map;
global $XMLRPC_dispatch_map;
global $SOAP_typedef;
global $REST_dispatch_map;

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
/*
 * Récupère la liste des contacts
 */

$SOAP_typedef['searchParams'] = array(
    'whereClause' => 'string'
);

$SOAP_typedef['complexContactOut'] = array( 'status'=>'string',
                                            'value'=>'{urn:MaarchSoapServer}listOfContacts',
                                            'error'=>'string'
                                           );

$SOAP_typedef['listOfContacts'] = array(    'contact_id'=>'long',
                                            'lastname'=>'string',
                                            'firstname'=>'string',
                                            'society'=>'string',
                                            'function'=>'string',
                                            'address_num'=>'string',
                                            'address_street'=>'string',
                                            'address_complement'=>'string',
                                            'address_town'=>'string',
                                            'address_postal_code'=>'string',
                                            'address_country'=>'string',
                                            'email'=>'string',
                                            'phone'=>'string',
                                            'other_data'=>'string',
                                            'is_corporate_person'=>'string',
                                            'user_id'=>'string',
                                            'title'=>'string',
                                            'enabled'=>'string'
                                        );

$SOAP_dispatch_map['listContacts'] = Array(
                                     'in' => Array('searchParams'=>'{urn:MaarchSoapServer}searchParams'),
                                     'out' => Array('out'=> '{urn:MaarchSoapServer}complexContactOut'),
                                     'method' => "apps#contacts::listContacts"
                                     );

$SOAP_typedef['returnId'] = array( 'returnCode'=>'int',
                                            'contactId'=>'string',
                                            'contactId'=>'string',
                                            'error'=>'string'
                                           );

$SOAP_typedef['arrayOfDataContact'] = array(
    array(
        'arrayOfDataContent' => '{urn:MaarchSoapServer}arrayOfDataContactContent'
    )
);

$SOAP_typedef['arrayOfDataContactContent'] = array(
    'column' => 'string',
    'value' => 'string',
    'type' => 'string',
    'table' => 'string',
);

$SOAP_dispatch_map['CreateContact'] = Array(
                                     'in' => Array('data' => '{urn:MaarchSoapServer}arrayOfDataContact'),
                                     'out' => Array('out' => '{urn:MaarchSoapServer}returnId'),
                                     'method' => "apps#contacts::CreateContact"
                                     );

$REST_dispatch_map['res'] = Array(
    'pathToController' => "apps/maarch_entreprise/class/cmis_res_controller.php"
);
