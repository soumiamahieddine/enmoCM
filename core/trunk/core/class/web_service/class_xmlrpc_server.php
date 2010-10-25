<?php

//declaration of descriptions services vars
if(!isset($XMLRPC_dispatch_map)) {
    $XMLRPC_dispatch_map = Array();
}

/****************************************************************
 *                                                              *
 * Définition de la classe qui présente les webservices en XMLRPC *
 * (Elle ne sera instanciée que si il y en a besoin)            *
 *                                                              *
 ****************************************************************/
class MyXmlRPCServer{
	var $__dispatch_map;

	function __construct(){
		global $XMLRPC_dispatch_map;
		$this->__dispatch_map = $XMLRPC_dispatch_map;
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

	function importXMLRPCLibs(){
		include("lib/xmlrpc.inc");
		include("lib/xmlrpcs.inc");
		include("lib/xmlrpc_wrappers.inc");
	}

	function makeXMLRPCServer(){
		global $XMLRPC_dispatch_map;
		$this->importXMLRPCLibs();
		$server = new xmlrpc_server($XMLRPC_dispatch_map, false);
		$server->functions_parameters_type = 'phpvals';
		$server->service();
	}
}

?>
