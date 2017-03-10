<?php

namespace MaarchTest;

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

require_once __DIR__.'/define.php';

class DocserverTypeControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetList()
    {
        $action = new \Core\Controllers\DocserverTypeController();

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                //'REQUEST_URI' => '/docserverType',
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
        $action = new \Core\Controllers\DocserverTypeController();

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
            ]
        );

        $aArgs = [
            'id'=> 'FASTHD'
        ];

        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $response = $action->getById($request, $response, $aArgs);
        $compare = '[{"DocserverType":[{"docserver_type_id":"FASTHD",'
            . '"docserver_type_label":"FASTHD","enabled":"Y",'
            . '"is_container":"N","container_max_number":0,'
            . '"is_compressed":"N","compression_mode":"NONE",'
            . '"is_meta":"N","meta_template":"NONE",'
            . '"is_logged":"N","log_template":"NONE",'
            . '"is_signed":"Y","fingerprint_mode":"SHA256"}]}]';

        $this->assertSame((string)$response->getBody(), $compare);
    }

    // public function testCreate()
    // {
    //     $action = new \Core\Controllers\DocserverTypeController();

    //     $query  = 'id=TEST&';
    //     $query .= 'label_status=TEST';

    //     $environment = \Slim\Http\Environment::mock(
    //         [
    //             'REQUEST_METHOD' => 'POST',
    //             'QUERY_STRING'=> $query,
    //         ]
    //     );
        
    //     $aArgs = [
    //         'id'=> 'NEW'
    //     ];

    //     $request = \Slim\Http\Request::createFromEnvironment($environment);
    //     $response = new \Slim\Http\Response();
    //     $response = $action->create($request, $response, $aArgs);

    //     $compare = '[{"docserverType":[{"id":"TEST","label_status":"TEST",'
    //         . '"is_system":"Y","is_folder_status":"N","img_filename":null,'
    //         . '"maarch_module":"apps","can_be_searched":"Y",'
    //         . '"can_be_modified":"Y"}]}]';
        
    //     $this->assertSame((string)$response->getBody(), $compare);
    // }

    // public function testUpdate()
    // {
    //     $action = new \Core\Controllers\DocserverTypeController();

    //     $query  = 'id=TEST&';
    //     $query .= 'label_status=TEST AFTER UP';

    //     $environment = \Slim\Http\Environment::mock(
    //         [
    //             'REQUEST_METHOD' => 'PUT',
    //             'QUERY_STRING'=> $query,
    //         ]
    //     );
        
    //     $aArgs = [
    //         'id'=> 'NEW'
    //     ];

    //     $request = \Slim\Http\Request::createFromEnvironment($environment);
    //     $response = new \Slim\Http\Response();
    //     $response = $action->update($request, $response, $aArgs);

    //     $compare = '[{"docserverType":[{"id":"TEST","label_status":"TEST AFTER UP",'
    //         . '"is_system":"Y","is_folder_status":"N","img_filename":null,'
    //         . '"maarch_module":"apps","can_be_searched":"Y",'
    //         . '"can_be_modified":"Y"}]}]';
        
    //     $this->assertSame((string)$response->getBody(), $compare);
    // }

    // public function testDelete()
    // {
    //     $action = new \Core\Controllers\DocserverTypeController();

    //     $environment = \Slim\Http\Environment::mock(
    //         [
    //             'REQUEST_METHOD' => 'DELETE',
    //         ]
    //     );

    //     $aArgs = [
    //         'id'=> 'TEST'
    //     ];

    //     $request = \Slim\Http\Request::createFromEnvironment($environment);
    //     $response = new \Slim\Http\Response();
    //     $response = $action->delete($request, $response, $aArgs);
        
    //     $this->assertSame((string)$response->getBody(), '[{"docserverType":true}]');
    // }
}
