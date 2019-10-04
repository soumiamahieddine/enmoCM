<?php 
global $SOAP_dispatch_map;
global $XMLRPC_dispatch_map;
global $SOAP_typedef;
global $REST_dispatch_map;

$REST_dispatch_map['basket'] = Array(
    'pathToController' => "modules/basket/class/cmis/cmis_basket_controller.php"
);