<?php

namespace MaarchTest;

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

require_once __DIR__.'/define.php';

class StatusControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetList()
    {
        $action = new \Core\Controllers\StatusController();

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                //'REQUEST_URI' => '/status',
                //'QUERY_STRING'=>'foo=bar',
            ]
        );

        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $response = $action->getList($request, $response, []);

        $this->assertNotNull((string)$response->getBody());
    }

    public function testGetById()
    {
        $action = new \Core\Controllers\StatusController();

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
            ]
        );

        $aArgs = [
            'id'=> 'NEW'
        ];

        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $response = $action->getById($request, $response, $aArgs);
        $compare = '[[{"id":"NEW","label_status":"Nouveau",'
            . '"is_system":"Y","is_folder_status":"N","img_filename":'
            . '"fm-letter-status-new","maarch_module":"apps",'
            . '"can_be_searched":"Y","can_be_modified":"Y"}]]';

        $this->assertSame((string)$response->getBody(), $compare);
    }

    public function testCreate()
    {
        $action = new \Core\Controllers\StatusController();

        $query  = 'id=TEST&';
        $query .= 'label_status=TEST';

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'POST',
                'QUERY_STRING'=> $query,
            ]
        );
        
        $aArgs = [
            'id'=> 'NEW'
        ];

        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $response = $action->create($request, $response, $aArgs);

        $compare = '[[{"id":"TEST","label_status":"TEST",'
            . '"is_system":"Y","is_folder_status":"N","img_filename":null,'
            . '"maarch_module":"apps","can_be_searched":"Y",'
            . '"can_be_modified":"Y"}]]';
        
        $this->assertSame((string)$response->getBody(), $compare);
    }

    public function testUpdate()
    {
        $action = new \Core\Controllers\StatusController();

        $query  = 'id=TEST&';
        $query .= 'label_status=TEST AFTER UP';

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'PUT',
                'QUERY_STRING'=> $query,
            ]
        );
        
        $aArgs = [
            'id'=> 'NEW'
        ];

        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $response = $action->update($request, $response, $aArgs);

        $compare = '[[{"id":"TEST","label_status":"TEST AFTER UP",'
            . '"is_system":"Y","is_folder_status":"N","img_filename":null,'
            . '"maarch_module":"apps","can_be_searched":"Y",'
            . '"can_be_modified":"Y"}]]';
        
        $this->assertSame((string)$response->getBody(), $compare);
    }

    public function testDelete()
    {
        $action = new \Core\Controllers\StatusController();

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'DELETE',
            ]
        );

        $aArgs = [
            'id'=> 'TEST'
        ];

        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $response = $action->delete($request, $response, $aArgs);
        
        $this->assertSame((string)$response->getBody(), '[true]');
    }
}
