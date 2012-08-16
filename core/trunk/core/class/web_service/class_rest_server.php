<?php

/*
*   Copyright 2012 Maarch
*
*   This file is part of Maarch Framework.
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
* @brief Maarch REST class
*
* @file
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

//declaration of descriptions services vars
if (!isset ($REST_dispatch_map)) {
    $REST_dispatch_map = Array ();
}

/**
 * Class for manage REST web service
 */
class MyRestServer extends webService
{
    var $dispatchMap;
    var $crudMethod;
    var $resController;
    var $requestedResource;
    var $requestedResourceId;
    var $atomFileContent;
    
    function __construct()
    {
        global $REST_dispatch_map;
        $this->dispatchMap = $REST_dispatch_map;
        //$this->retrieveHttpMethod();
        $this->parseTheRequest();
    }
    
    /*function retrieveHttpMethod()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->crudMethod = 'create';
        } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->crudMethod = 'retrieve';
        } if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            $this->crudMethod = 'update';
        } if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            $this->crudMethod = 'delete';
        }
    }*/
    
    function parseTheRequest()
    {
        $restRequest = explode('/', $_SERVER['QUERY_STRING']);
        if ($restRequest[1] <> '') {
            $this->requestedResource = $restRequest[1];
        }
        if ($restRequest[2] <> '') {
            $this->requestedResourceId = $restRequest[2];
        }
        if (
            isset($_REQUEST['atomFileContent']) 
            && !empty($_REQUEST['atomFileContent'])
        ) {
            $this->atomFileContent = $_REQUEST['atomFileContent'];
        }
    }
    
    /**
     * parse the requested resource object and call the good method of the requested resource controller
     * @return call of the good method
     */
    public function call()
    {
        //echo $this->dispatchMap[$this->requestedResource]['pathToController'] . '<br>';
        if (file_exists($this->dispatchMap[$this->requestedResource]['pathToController'])) {
            require_once($this->dispatchMap[$this->requestedResource]['pathToController']);
            $objectControllerName = $this->requestedResource . 'CMIS';
            $objectController = new $objectControllerName();
            $args = array(
                'atomFileContent' => $this->atomFileContent,
                'requestedResourceId' => $this->requestedResourceId
            );
            return call_user_func_array(array($objectController, 'entryMethod'), $args);
        }
    }
    
    /**
     * generate REST server
     */
    function makeRESTServer()
    {
        /*echo '<pre>';
        var_dump($_SERVER);
        var_dump($this->dispatchMap);
        echo '</pre>';*/
        //echo 'method ? ' . $this->crudMethod . '<br>';
        echo 'requested resource : ' . $this->requestedResource . '<br>';
        echo 'requested resource id : ' . $this->requestedResourceId . '<br>';
        echo 'result of the function call : ' . $this->call();
    }
}
