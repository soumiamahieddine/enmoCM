<?php 
global $SOAP_dispatch_map;
global $XMLRPC_dispatch_map;
global $SOAP_typedef;

$SOAP_dispatch_map['storeAttachmentResource'] = array(
    'in'  => array(
        'resId' => 'long',
        'collId' => 'string',
        'encodedContent' => 'string',
        'fileFormat' => 'string',
        'fileName' => 'string',
),
    'out' => array('out' => '{urn:MySoapServer}returnArray'),
    'method' => "modules/attachments#attachments::storeAttachmentResource"
);
