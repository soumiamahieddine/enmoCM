<?php

//declaration of descriptions services vars
if(!isset($SOAP_dispatch_map)) {
    $SOAP_dispatch_map = Array();
}
if(!isset($SOAP_typedef)) {
    $SOAP_typedef = Array();
}

/****************************************************************
 *                                                              *
 * Définition de la classe qui présente les webservices en SOAP *
 * (Elle ne sera instanciée que si il y en a besoin)            *
 *                                                              *
 ****************************************************************/
class MySoapServer{
    var $__dispatch_map;
    var $__typedef;

    function __construct(){
        global $SOAP_dispatch_map, $SOAP_typedef;
        $this->__dispatch_map = $SOAP_dispatch_map;
        $this->__typedef = $SOAP_typedef;
    }

    function __dispatch($methodname){
        if (isset($this->__dispatch_map[$methodname])){
            return $this->__dispatch_map[$methodname];
        }
        return null;
 	}

    public function __call($method, $args){
        return call_user_func_array($method, $args);
    }    
		/*********************************
	 *                               *
	 * definition of wrapper (DRY) *
	 *                               *
	 *********************************/
	function importSOAPLibs(){
		require('SOAP/Server.php');
		require('SOAP/Disco.php');
	}

	function launchSOAPServer(){
		$server = new SOAP_Server();
		$webservice = new MySoapServer();
		$server->addObjectMap($webservice,'urn:MySoapServer');
		return $server;
	}

	function makeWSDL(){
		$this->importSOAPLibs();
		$server = $this->launchSOAPServer();
		$disco = new SOAP_DISCO_Server($server,'MySoapServer');
		header("Content-type: text/xml");
		echo $disco->getWSDL();
	}

	function makeSOAPServer(){
		global $HTTP_RAW_POST_DATA;
		$this->importSOAPLibs();
		$server = $this->launchSOAPServer();
		$server->service($HTTP_RAW_POST_DATA);
	}

	function makeDISCO(){
		$this->importSOAPLibs();
		$server = $this->launchSOAPServer();
		$disco = new SOAP_DISCO_Server($server,'MySoapServer');
		header("Content-type: text/xml");
		echo $disco->getDISCO();
	}
}


?>
