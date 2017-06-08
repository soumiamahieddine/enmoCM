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

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'POST',
                'QUERY_STRING'   => $query,
            ]
        );

        $parameter = new \Core\Controllers\ParametersController();
        $response = $parameter->create(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response());

        $compare = '[{"id":"TEST",'
                    .'"description":"papa",'
                    .'"param_value_string":"abcd",'
                    .'"param_value_int":null,'
                    .'"param_value_date":null}]';
        $this->assertSame($compare,(string)$response->getBody());

        $client = new \GuzzleHttp\Client([
            'base_uri' => $_SESSION['config']['coreurl'] . 'rest/parameters',
            // You can set any number of default request options.
            'timeout'  => 2.0,
            ]);
       
        //TEST EXISTE DEJA
        $aArgs = [
            'id'                 => 'TEST',
            'description'        => null,
            'param_value_string' => null,
            'param_value_int'    => 1234,
            'param_value_date'   => null                
        ];

        $response = $client->request('POST', $_SESSION['config']['coreurl'] . 'rest/parameters', [
            'auth'        => ['superadmin','superadmin'],
            'form_params' => $aArgs
        ]);

        $compare = '{"errors":["Identifiant TEST existe d\u00e9j\u00e0 !"]}';
        
        $this->assertSame($compare,(string)$response->getBody());

        //AUCUN PARAMETRE
        
        $aArgs = [
            'id'                 => 'TEST3',
            'description'        => null,
            'param_value_string' => null,
            'param_value_int'    => null,
            'param_value_date'   => null                
        ];
        
        $response = $client->request('POST', $_SESSION['config']['coreurl'] . 'rest/parameters', [
            'auth'        => ['superadmin','superadmin'],
            'form_params' => $aArgs
        ]);

        $compare = '{"errors":["_PARAM_VALUE_IS_EMPTY"]}';
        
        $this->assertSame($compare,(string)$response->getBody());

        //AUCUN ARGUMENTS
        $aArgs = [ ];

        $response = $client->request('POST', $_SESSION['config']['coreurl'] . 'rest/parameters', [
            'auth'        => ['superadmin','superadmin'],
            'form_params' => $aArgs
        ]);

        $compare = '{"errors":["_ID_IS_EMPTY_CONTROLLER","_PARAM_VALUE_IS_EMPTY"]}';

        $this->assertSame($compare,(string)$response->getBody());

        //DATE MAUVAIS FORMAT

        $aArgs = [
            'id'                 => 'TEST4',
            'description'        => null,
            'param_value_string' => null,
            'param_value_int'    => null,
            'param_value_date'   => '123456'                
        ];

        $response = $client->request('POST', $_SESSION['config']['coreurl'] . 'rest/parameters', [
            'auth'        => ['superadmin','superadmin'],
            'form_params' => $aArgs
        ]);

        $compare = '{"errors":["PARAMETRE DATE INVALIDE."]}';
        $this->assertSame($compare,(string)$response->getBody());

        //TEST ID MAUVAIS FORMAT (REGEX)

        $aArgs = [
            'id'                 => 'A*-#==',
            'description'        => "*///*//",
            'param_value_string' => "//-//**",
            'param_value_int'    => null,
            'param_value_date'   => null                
        ];

        $response = $client->request('POST', $_SESSION['config']['coreurl'] . 'rest/parameters', [
            'auth'        => ['superadmin','superadmin'],
            'form_params' => $aArgs
        ]);

        $compare ='{"errors":["ID INVALIDE","DESCRIPTION INVALIDE","PARAM STRING INVALIDE"]}';
        $this->assertSame($compare,(string)$response->getBody());            
    }

    public function testGetList()
    {
        $parameters = new \Core\Controllers\ParametersController();

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
            ]
        );

        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $response = $parameters->getList($request, $response);

        $this->assertNotNull((string)$response->getBody());
    }
    
    public function testGetById()
    {           

        $aArgs = [
            'id' => 'TEST'
        ];

        $client = new \GuzzleHttp\Client([
            'base_uri' => $_SESSION['config']['coreurl'] . 'rest/parameters',
            // You can set any number of default request options.
            'timeout'  => 2.0,
            ]);

        $response = $client->request('GET', $_SESSION['config']['coreurl'] . 'rest/parameters/'.$aArgs['id'], [
            'auth'=> ['superadmin','superadmin']
        ]);
        $compare = '[[{"id":"TEST",'
                    .'"description":null,'
                    .'"param_value_string":"abcd",'
                    .'"param_value_int":null,'
                    .'"param_value_date":null}]]';

        $this->assertNotNull((string)$response->getBody());
    }

    public function testUpdate()
    {

        $query  = 'id=TEST&';
        $query .= 'description=TEST AFTER UP';

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'PUT',
                'QUERY_STRING'   => $query,
            ]
        );

        $parameter = new \Core\Controllers\ParametersController();
        $response = $parameter->update(\Slim\Http\Request::createFromEnvironment($environment), new \Slim\Http\Response());

            $compare = '[{"id":"TEST",'
                        .'"description":"TEST AFTER UP",'
                        .'"param_value_string":"abcd",'
                        .'"param_value_int":null,'
                        .'"param_value_date":null}]';
        
        $this->assertSame((string)$response->getBody(), $compare);

        //TEST ID NULL

        $client = new \GuzzleHttp\Client([
            'base_uri' => $_SESSION['config']['coreurl'] . 'rest/parameters',
            // You can set any number of default request options.
            'timeout'  => 2.0,
            ]);

        $aArgs = [
            'id' => 'NEW'
        ];
        $response = $client->request('PUT', $_SESSION['config']['coreurl'] . 'rest/parameters/'.$aArgs['id'], [
            'auth'        => ['superadmin','superadmin'],
            'form_params' => $aArgs
        ]);

        $compare = '{"errors":["Identifiant n\'existe pas"]}';
        
        $this->assertSame($compare,(string)$response->getBody());

    }

    public function testDelete()
    {
        $aArgs = [
            'id'=> 'TEST'
        ];

        $client = new \GuzzleHttp\Client([
            'base_uri' => $_SESSION['config']['coreurl'] . 'rest/parameters',
            // You can set any number of default request options.
            'timeout'  => 2.0,
            ]);

        $response = $client->request('DELETE', $_SESSION['config']['coreurl'] . 'rest/parameters/'.$aArgs['id'], [
            'auth'        => ['superadmin','superadmin'],
            'form_params' => $aArgs
        ]);
        $compare = 'true';
        $this->assertSame($compare,(string)$response->getBody());
    }

}

?>