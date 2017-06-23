<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

namespace MaarchTest;

require_once __DIR__.'/define.php';

class StatusControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetList()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $status      = new \Core\Controllers\StatusController();

        $response  = $status->getList($request, new \Slim\Http\Response());

        $this->assertNotNull((string)$response->getBody());
    }

    public function testGetById()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $status      = new \Core\Controllers\StatusController();

        $response = $status->getById($request, new \Slim\Http\Response(), ['id' => 'NEW']);
        $compare = '[[{"id":"NEW","label_status":"Nouveau",'
            . '"is_system":"Y","is_folder_status":"N","img_filename":'
            . '"fm-letter-status-new","maarch_module":"apps",'
            . '"can_be_searched":"Y","can_be_modified":"Y"}]]';

        $this->assertSame((string)$response->getBody(), $compare);
    }

    public function testCreate()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $status      = new \Core\Controllers\StatusController();

        $aArgs = [
            'id'           => 'TEST',
            'label_status' => 'TEST'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response = $status->create($fullRequest, new \Slim\Http\Response());

        $compare = '[[{"id":"TEST","label_status":"TEST",'
            . '"is_system":"Y","is_folder_status":"N","img_filename":null,'
            . '"maarch_module":"apps","can_be_searched":"Y",'
            . '"can_be_modified":"Y"}]]';
        
        $this->assertSame((string)$response->getBody(), $compare);
    }

    public function testUpdate()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $status      = new \Core\Controllers\StatusController();

        $aArgs = [
            'id'           => 'TEST',
            'label_status' => 'TEST AFTER UP'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response = $status->update($fullRequest, new \Slim\Http\Response());

        $compare = '[[{"id":"TEST","label_status":"TEST AFTER UP",'
            . '"is_system":"Y","is_folder_status":"N","img_filename":null,'
            . '"maarch_module":"apps","can_be_searched":"Y",'
            . '"can_be_modified":"Y"}]]';
        
        $this->assertSame((string)$response->getBody(), $compare);
    }

    public function testDelete()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $status      = new \Core\Controllers\StatusController();

        $response = $status->delete($request, new \Slim\Http\Response(), ['id'=> 'TEST']);


        $this->assertSame((string)$response->getBody(), '[true]');
    }
}
