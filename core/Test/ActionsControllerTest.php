<?php

namespace MaarchTest;

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

require_once __DIR__.'/define.php';

class ActionsControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetList()
    {
        $action = new \Core\Controllers\ActionsController();

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
            ]
        );

        $client = new \GuzzleHttp\Client([
                'base_uri' => '127.0.0.1/MaarchCourrier/rest/actions',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('GET', '', [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);

        $this->assertNotNull((string)$response->getBody());
    }

    public function testGetById()
    {
        $action = new \Core\Controllers\ActionsController();

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
            ]
        );

        $aArgs = [
            'id'=>'1'
        ];

       $client = new \GuzzleHttp\Client([
                'base_uri' => '127.0.0.1/MaarchCourrier/rest/actions/',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('GET', ''.$aArgs['id'], [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);

        
        $compare = '[[{"id":1,"keyword":"redirect","label_action":"Rediriger","id_status":"_NOSTATUS_","is_system":"Y","is_folder_action":"N","enabled":"Y","action_page":"redirect","history":"Y","origin":"entities","create_id":"N","category_id":null}]]';

        $this->assertSame((string)$response->getBody(), $compare);
    }

    public function testCreate()
    {
        $action = new \Core\Controllers\ActionsController();

        $query = 'keyword=redirect&';
        $query .= 'label_action=test&';
        $query .= 'id_status=_NOSTATUS_&';
        $query .= 'is_system=Y&';
        $query .= 'is_folder_action=N&';
        $query .= 'enabled=Y&';
        $query .= 'action_page=redirect&';
        $query .= 'history=>Y&';
        $query .= 'origin=entities&';
        $query .= 'create_id=N';


        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'POST',
                'QUERY_STRING'=> $query,
            ]
        );
        
        $aArgs = [
            'keyword' => 'test',
            'label_action' => 'test',
            'id_status' => 'ESIG',
            'is_system'=> 'Y',
            'is_folder_action' => 'N',
            'enabled' => 'Y',
            'action_page' => 'redirect',
            'history' => 'Y',
            'origin' => 'entities',
            'create_id' => 'N'
        ];
        $client = new \GuzzleHttp\Client([
                'base_uri' => '127.0.0.1/MaarchCourrier/rest/actions',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('POST', '', [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);

        $compare = '[{"keyword":"test","label_action":"test","id_status":"ESIG","is_system":"Y","is_folder_action":"N","enabled":"Y","action_page":"redirect","history":"Y","origin":"entities","create_id":"N","category_id":null}]';
        $obj=json_decode((string)$response->getBody());
        unset($obj[0]->id);

        $this->assertSame(json_encode($obj), $compare);
    }
    public function testDelete()
    {
        $action = new \Core\Controllers\ActionsController();

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'DELETE',
            ]
        );

        $aArgs = [
            'id'=> '5'
        ];

        $client = new \GuzzleHttp\Client([
                'base_uri' => '127.0.0.1/MaarchCourrier/rest/actions/',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('DELETE', ''.$aArgs['id'], [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);
        
        $this->assertSame((string)$response->getBody(), '[true]');
    }

     public function testUpdate()
     {

        $action = new \Core\Controllers\ActionsController();

        $query  = 'id=19&';
        $query .= 'keyword=test&';
        $query .= 'label_action=Test AFTER update&';
        $query .= 'id_status=ESIG&';
        $query .= 'is_folder_action=N&';
        $query .= 'action_page=redirect&';
        $query .= 'history=Y&';


        $aArgs = [
            'id'=>'19',
            'keyword'=>'test',
            'label_action'=>'Test AFTER update',
            'id_status'=> 'ESIG',
            'is_folder_action' => 'N',
            'action_page' => 'redirect',
            'history' => 'Y'];
        
        $environment = \Slim\Http\Environment::mock(
             [
                'REQUEST_METHOD' => 'PUT',
                'QUERY_STRING'=> $query,
             ]
         );
        
        $client = new \GuzzleHttp\Client([
                'base_uri' => '127.0.0.1/MaarchCourrier/rest/actions/',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('PUT', ''.$aArgs['id'], [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);

        $compare = '[[{"id":19,"keyword":"test","label_action":"Test AFTER update","id_status":"ESIG","is_system":"N","is_folder_action":"N","enabled":"Y","action_page":"redirect","history":"Y","origin":"apps","create_id":"N","category_id":null}]]';
        
        $this->assertSame((string)$response->getBody(), $compare);
     }

}
