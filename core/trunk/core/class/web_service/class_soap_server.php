<?php

/*
*   Copyright 2010 Maarch
*
*  	This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Maarch SOAP class
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

//declaration of descriptions services vars
if(!isset($SOAP_dispatch_map)) {
    $SOAP_dispatch_map = Array();
}
if(!isset($SOAP_typedef)) {
    $SOAP_typedef = Array();
}

/**
 * Class for manage SOAP web service
 */
class MySoapServer {
	
    var $__dispatch_map;
    var $__typedef;

    function __construct() {
        global $SOAP_dispatch_map, $SOAP_typedef;
        $this->__dispatch_map = $SOAP_dispatch_map;
        $this->__typedef = $SOAP_typedef;
    }

    function __dispatch($methodname) {
        if (isset($this->__dispatch_map[$methodname])) {
            return $this->__dispatch_map[$methodname];
        }
        return null;
 	}

    public function __call($method, $args) {
        return call_user_func_array($method, $args);
    }

	/**
	 * import of the SOAP library
	 */
	function importSOAPLibs() {
		require('SOAP/Server.php');
		require('SOAP/Disco.php');
	}
	
	/**
	 * launch SOAP server
	 */
	function launchSOAPServer() {
		$server = new SOAP_Server();
		$webservice = new MySoapServer();
		$server->addObjectMap($webservice,'urn:MySoapServer');
		return $server;
	}

	/**
	 * generate WSDL
	 */
	function makeWSDL() {
		$this->importSOAPLibs();
		$server = $this->launchSOAPServer();
		$disco = new SOAP_DISCO_Server($server,'MySoapServer');
		header("Content-type: text/xml");
		echo $disco->getWSDL();
	}
	
	/**
	 * generate SOAP server
	 */
	function makeSOAPServer() {
		global $HTTP_RAW_POST_DATA;
		$this->importSOAPLibs();
		$server = $this->launchSOAPServer();
		$server->service($HTTP_RAW_POST_DATA);
	}

	/**
	 * discover server
	 */
	function makeDISCO() {
		$this->importSOAPLibs();
		$server = $this->launchSOAPServer();
		$disco = new SOAP_DISCO_Server($server,'MySoapServer');
		header("Content-type: text/xml");
		echo $disco->getDISCO();
	}
}


?>
