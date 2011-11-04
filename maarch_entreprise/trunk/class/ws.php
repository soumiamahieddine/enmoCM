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
/*
 * Récupère la liste des contacts
 */

$SOAP_typedef['searchParams'] = array(
    'whereClause' => 'string'
);

$SOAP_typedef['complexContactOut'] = array( 'status'=>'string',
                                            'value'=>'{urn:MySoapServer}listOfContacts',
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
                                     'in' => Array('searchParams'=>'{urn:MySoapServer}searchParams'),
                                     'out' => Array('out'=> '{urn:MySoapServer}complexContactOut'),
                                     'method' => "apps#contacts::listContacts"
                                     );
