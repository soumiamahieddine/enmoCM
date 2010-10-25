<?php

class webService {

	function WSCoreCatalog() {
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php')) {
			require($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php');
		} elseif(file_exists($_SESSION['config']['corepath'].DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php')) {
			require('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php');
		}
	}

	function WSAppsCatalog() {
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['businessapps'][0]['appid'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php')) {
			require($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['businessapps'][0]['appid'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php');
		} else {
			require('apps'.DIRECTORY_SEPARATOR.$_SESSION['businessapps'][0]['appid'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php');
		}
	}

	function WSModulesCatalog() {
		for($cptModules=0;$cptModules<count($_SESSION['modules']);$cptModules++) {
			if($_SESSION['modules'][$cptModules]['moduleid'] <> "" && file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules'][$cptModules]['moduleid'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php')) {
				require($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules'][$cptModules]['moduleid'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php');
			} elseif($_SESSION['modules'][$cptModules]['moduleid'] <> "" && file_exists($_SESSION['config']['corepath'].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$_SESSION['modules'][$cptModules]['moduleid'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php')) {
				require('apps'.DIRECTORY_SEPARATOR.$_SESSION['businessapps'][0]['appid'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php');
			}
			if($_SESSION['modules'][$cptModules]['moduleid'] <> "" && file_exists($_SESSION['pathtomodules'].$_SESSION['modules'][$cptModules]['moduleid'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php')){
				require($_SESSION['pathtomodules'].$_SESSION['modules'][$cptModules]['moduleid'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'ws.php');
			}
		}
	}
	
	function WScustomCatalog() {
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'ws.php')) {
			require($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'ws.php');
		}
	}

	function authentication() {
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
		return $authenticated;
	}
	
	function launchWs() {
		require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."web_service".DIRECTORY_SEPARATOR."class_soap_server.php");
		require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."web_service".DIRECTORY_SEPARATOR."class_xmlrpc_server.php");
		$soapServer = new MySoapServer();
		$xmlRPC = new MyXmlRPCServer();
		//if WSDL
		if(isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'],'wsdl') == 0) {
			$soapServer->makeWSDL();
		} elseif(isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'],'xmlrpc') == 0) {
			//XMLRPC
			$xmlRPC->makeXMLRPCServer();
		} else {
			//if Soap
			if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST') {
				$soapServer->makeSOAPServer();
			} else {
				//default : xml Discovery
				$soapServer->makeDISCO();
			}
		}
	}
}
?>
