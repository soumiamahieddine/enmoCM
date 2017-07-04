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
    public function testCreate()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $status      = new \Core\Controllers\StatusController();

        $aArgs = [
            'id'           => 'TEST',
            'label_status' => 'TEST',
            'img_filename' => 'fm-letter-end'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response = $status->create($fullRequest, new \Slim\Http\Response());

        $compare = '[[{"id":"TEST","label_status":"TEST",'
            . '"is_system":"N","is_folder_status":"N","img_filename":"fm-letter-end",'
            . '"maarch_module":"apps","can_be_searched":"Y",'
            . '"can_be_modified":"Y"}]]';

        $this->assertNotNull((string)$response->getBody());
        // $this->assertSame((string)$response->getBody(), $compare);
    }

    public function testGetListUpdateDelete()
    {
        #####GET LIST#####
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $status      = new \Core\Controllers\StatusController();

        $response  = $status->getList($request, new \Slim\Http\Response());

        $responseBody = json_decode((string)$response->getBody());
        $this->assertNotNull($responseBody->statusList);
        $this->assertNotNull($responseBody->lang);

        $elem = $responseBody->statusList;
        end($elem);
        $key = key($elem);
        $lastIdentifier = $elem[$key]->identifier;


        #####UPDATE#####
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $status      = new \Core\Controllers\StatusController();

        $aArgs = [
            'id'           => 'TEST',
            'label_status' => 'TEST AFTER UP'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response = $status->update($fullRequest, new \Slim\Http\Response(), ['identifier' => $lastIdentifier]);

        $compare = '[[{"id":"TEST","label_status":"TEST AFTER UP",'
            . '"is_system":"N","is_folder_status":"N","img_filename":"fm-letter-end",'
            . '"maarch_module":"apps","can_be_searched":"Y",'
            . '"can_be_modified":"Y","identifier":'.$lastIdentifier.'}]]';
        
        $this->assertSame((string)$response->getBody(), $compare);


        #####DELETE#####
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $status      = new \Core\Controllers\StatusController();

        $response = $status->delete($request, new \Slim\Http\Response(), ['identifier'=> $lastIdentifier]);

        $this->assertSame((string)$response->getBody(), '[true]');
    }
}
