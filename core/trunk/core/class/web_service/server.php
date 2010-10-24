<?php
$session_name=md5(time());
session_name($session_name);

if(!isset($SOAP_dispatch_map)) {
    $SOAP_dispatch_map = Array();
}
if(!isset($XMLRPC_dispatch_map)) {
    $XMLRPC_dispatch_map = Array();
}
if(!isset($SOAP_typedef)) {
    $SOAP_typedef = Array();
}

chdir('../../../');

unset($_SESSION['config']);
unset($_SESSION['businessapps']);

/***********************
 *                                           *
 * Import des services        *
 *                                           *
 ***********************/
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_functions.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_portal.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_db.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_core_tools.php");

$portal = new portal();
$portal->unset_session();
$portal->build_config();

$coreTools = new core_tools();
$coreTools->build_core_config("core".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml");
$_SESSION["config"]["app_id"] = $_SESSION["businessapps"][0]["appid"];
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION["businessapps"][0]["appid"].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_business_app_tools.php");

$businessAppTools = new business_app_tools();
$businessAppTools->build_business_app_config();

//retrieveWSApps();
//retrieveWSModules();
if($_SERVER["PHP_AUTH_USER"] && $_SERVER["PHP_AUTH_PW"] && preg_match("/^Basic /", $_SERVER["HTTP_AUTHORIZATION"])) {
    list($_SERVER["PHP_AUTH_USER"], $_SERVER["PHP_AUTH_PW"]) = explode(":", base64_decode(substr($_SERVER["HTTP_AUTHORIZATION"], 6)));
}

$authenticated = false;
if($_SERVER["PHP_AUTH_USER"] || $_SERVER["PHP_AUTH_PW"]) {
	$connexion = new dbquery();
	$connexion->connect();
	$connexion->query("select * from ".$_SESSION['tablename']['users']." where user_id = '".$_SERVER["PHP_AUTH_USER"]."' and password = '".md5($_SERVER["PHP_AUTH_PW"])."' and STATUS <> 'DEL'");
	if($connexion->nb_result() > 0) {
		$authenticated = true;
	}
}

if(!$authenticated) {
    header("WWW-Authenticate: Basic realm=\"Maarch WebServer Engine\"");
    if (preg_match("/Microsoft/", $_SERVER["SERVER_SOFTWARE"])) {
    	header("Status: 401 Unauthorized");
    	exit();
	} else {
    	header("HTTP/1.0 401 Unauthorized");
    	echo "Access denied";
    	exit;
    }
}

/*function retrieveWSApps() {
	if(file_exists($_SESSION['config']['businessapppath'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php')) {
		require($_SESSION['config']['businessapppath'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php');
	}
}*/

/*function retrieveWSModules() {
	for($cptModules=0;$cptModules<count($_SESSION['modules']);$cptModules++) {
		if($_SESSION['modules'][$cptModules]['moduleid']<> "" && file_exists($_SESSION['pathtomodules'].$_SESSION['modules'][$cptModules]['moduleid'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php')){
			require($_SESSION['pathtomodules'].$_SESSION['modules'][$cptModules]['moduleid'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php');
		}
	}
}*/

require('services.php');

/********************************************************
 *                                                      *
 * Initiation des variables de description des services *
 *                                                      *
 ********************************************************/
if(!isset($SOAP_dispatch_map)) {
	$SOAP_dispatch_map = Array();
}
if(!isset($SOAP_typedef)) {
	$SOAP_typedef = Array();
}
if(!isset($XMLRPC_dispatch_map)) {
	$XMLRPC_dispatch_map = Array();
}

/****************************************************************
 *                                                              *
 * Définition de la classe qui présente les webservices en SOAP *
 * (Elle ne sera instanciée que si il y en a besoin)            *
 *                                                              *
 ****************************************************************/
class MySoapServer {
    var $__dispatch_map;
    var $__typedef;

    function __construct() {
        global $SOAP_dispatch_map, $SOAP_typedef;
        $this->__dispatch_map = $SOAP_dispatch_map;
        $this->__typedef = $SOAP_typedef;
    }

    function __dispatch($methodname) {
        if (isset($this->__dispatch_map[$methodname])){
            return $this->__dispatch_map[$methodname];
        }
        return null;
 	}

    public function __call($method, $args) {
        return call_user_func_array($method, $args);
    }
}

/*********************************
 *                               *
 * définition des wrappers (DRY) *
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
	importSOAPLibs();
	$server = launchSOAPServer();
	$disco = new SOAP_DISCO_Server($server,'MySoapServer');
	header("Content-type: text/xml");
	echo $disco->getWSDL();
}

function makeSOAPServer(){
	global $HTTP_RAW_POST_DATA;
	importSOAPLibs();
	$server = launchSOAPServer();
	$server->service($HTTP_RAW_POST_DATA);
}

function makeDISCO(){
	importSOAPLibs();
	$server = launchSOAPServer();
	$disco = new SOAP_DISCO_Server($server,'MySoapServer');
	header("Content-type: text/xml");
	echo $disco->getDISCO();
}

/*************************************
 *                                   *
 * Début du traitement de la requête *
 *                                   *
 *************************************/
// Si on sert le wsdl
if (isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'],'wsdl') == 0) {
	makeWSDL();
} else {
	// Si on sert une requete SOAP
	if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST') {
		makeSOAPServer();
	} else {
		//Par défault, on sert un xml Discovery
		makeDISCO();
	}
}
?>
