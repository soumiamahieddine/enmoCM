<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

namespace MaarchTest;

require_once __DIR__.'/define.php';

class ParametersControllerTest extends \PHPUnit_Framework_TestCase
{

    public function testCreate()
    {
        $query  = 'id=TEST&';
        $query .= 'param_value_string=abcd&';
        $query .= 'description=papa';

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'QUERY_STRING'   => $query,
        ]);

        $parameter = new \Core\Controllers\ParametersController();
        $response  = $parameter->create(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response());

        $compare = '[{"id":"TEST",'
                    .'"description":"papa",'
                    .'"param_value_string":"abcd",'
                    .'"param_value_int":null,'
                    .'"param_value_date":null}]';

        $this->assertSame($compare,(string)$response->getBody());
       
        //TEST EXISTE DEJA

        $response = $parameter->create(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response());

        $compare = '{"errors":["Identifiant TEST existe d\u00e9j\u00e0 !"]}';
        
        $this->assertSame($compare,(string)$response->getBody());

        //AUCUN PARAMETRE

        $query  = 'id=TEST3&';
        $query .= 'param_value_string=&';
        $query .= 'param_value_int=&';
        $query .= 'param_value_date=&';
        $query .= 'description=';

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'QUERY_STRING'   => $query,
        ]);

        $response = $parameter->create(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response());

        $compare = '{"errors":["_PARAM_VALUE_IS_EMPTY"]}';
        
        $this->assertSame($compare,(string)$response->getBody());

        //AUCUN ARGUMENTS

        $query  = '';

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'QUERY_STRING'   => $query,
        ]);

        $response = $parameter->create(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response());

        $compare = '{"errors":["_ID_IS_EMPTY_CONTROLLER","_PARAM_VALUE_IS_EMPTY"]}';

        $this->assertSame($compare,(string)$response->getBody());

        //DATE MAUVAIS FORMAT

        $query  = 'id=TEST4&';
        $query .= 'param_value_string=&';
        $query .= 'param_value_int=&';
        $query .= 'param_value_date=123456&';
        $query .= 'description=';

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'QUERY_STRING'   => $query,
        ]);

        $response = $parameter->create(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response());

        $compare = '{"errors":["PARAMETRE DATE INVALIDE."]}';
        $this->assertSame($compare,(string)$response->getBody());

        //TEST ID MAUVAIS FORMAT (REGEX)

        $query  = 'id=A*-#==&';
        $query .= 'param_value_string=//-//**&';
        $query .= 'param_value_int=&';
        $query .= 'param_value_date=&';
        $query .= 'description=*///*//';

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'QUERY_STRING'   => $query,
        ]);

        $response = $parameter->create(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response());

        $compare = '{"errors":["ID INVALIDE","DESCRIPTION INVALIDE","PARAM STRING INVALIDE"]}';
        $this->assertSame($compare,(string)$response->getBody());            
    }

    public function testGetList()
    {
        $parameters = new \Core\Controllers\ParametersController();

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET'
        ]);

        $response = $parameters->getList(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response());

        $this->assertNotNull((string)$response->getBody());
    }
    
    public function testGetById()
    {
        $query = 'id=TEST';

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING'   => $query,
        ]);

        $parameters = new \Core\Controllers\ParametersController();
        $response = $parameters->getById(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response(), ['id' => 'TEST']);

        $compare = '[{"id":"TEST",'
                    .'"description":"papa",'
                    .'"param_value_string":"abcd",'
                    .'"param_value_int":null,'
                    .'"param_value_date":null}]';

        $this->assertNotNull((string)$response->getBody());
        $this->assertSame($compare,(string)$response->getBody()); 
    }

    public function testUpdate()
    {
        $query  = 'id=TEST&';
        $query .= 'description=TEST AFTER UP';

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'PUT',
            'QUERY_STRING'   => $query,
        ]);

        $parameter = new \Core\Controllers\ParametersController();
        $response = $parameter->update(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response(), ['id' => 'TEST']);

        $compare = '[{"id":"TEST",'
                    .'"description":"TEST AFTER UP",'
                    .'"param_value_string":"abcd",'
                    .'"param_value_int":null,'
                    .'"param_value_date":null}]';
        
        $this->assertSame((string)$response->getBody(), $compare);

        //TEST ID NULL
        $query = 'id=NEWWW';

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'PUT',
            'QUERY_STRING'   => $query,
        ]);

        $response = $parameter->update(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response(), ['id' => 'NEWWW']);

        $compare = '{"errors":["Identifiant n\'existe pas"]}';
        
        $this->assertSame($compare,(string)$response->getBody());

    }

    public function testDelete()
    {
        $query = 'id=TEST';

        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'DELETE',
            'QUERY_STRING'   => $query,
        ]);

        $parameter = new \Core\Controllers\ParametersController();
        $response = $parameter->delete(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response(), ['id' => 'TEST']);

        $compare = 'true';
        $this->assertSame($compare,(string)$response->getBody());
    }

}
