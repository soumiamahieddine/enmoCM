<?php 

require('SOAP/Server.php');
require('SOAP/Disco.php');
require('services.php');

if (!isset($SOAP_dispatch_map)) {
    $SOAP_dispatch_map = Array();
}

class MySoapServer {
    var $__dispatch_map;
    var $__typedef;

    function __construct() {
        global $SOAP_dispatch_map, $SOAP_typedef;
        $this->__dispatch_map = $SOAP_dispatch_map;
        $this->__typedef = $SOAP_typedef;
    }

    function __dispatch($methodname) {
        if(isset($this->__dispatch_map[$methodname])) {
        	return $this->__dispatch_map[$methodname];
        }
        return null;
 	} 

    public static function __call($method, $args) {
        return call_user_func_array($method, $args);
    }

}

$server = new SOAP_Server();
$webservice = new MySoapServer();
$server->addObjectMap($webservice,'urn:MySoapServer');

if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST') {
     $server->service($HTTP_RAW_POST_DATA);
} else {
     $disco = new SOAP_DISCO_Server($server,'MySoapServer');
     header("Content-type: text/xml");
     if (isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'],'wsdl') == 0) {
         echo $disco->getWSDL();
     } else {
         echo $disco->getDISCO();
     }
}

?>
