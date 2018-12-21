<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class ResourceListControllerTest extends TestCase
{
    public function testGet()
    {
        $GLOBALS['userId'] = 'bbain';

        $resListController = new \Resource\controllers\ResourceListController();

        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $resListController->get($fullRequest, new \Slim\Http\Response(), ['userId' => 19, 'groupSerialId' => 2, 'basketId' => 'MyBasket']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('int', $responseBody->count);
        $this->assertNotNull( $responseBody->basketLabel);

        //  ERRORS
        $response     = $resListController->get($request, new \Slim\Http\Response(), ['userId' => 19, 'groupSerialId' => 777, 'basketId' => 'MyBasket']);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Group or basket does not exist', $responseBody->errors);

        $response     = $resListController->get($request, new \Slim\Http\Response(), ['userId' => 19, 'groupSerialId' => 2, 'basketId' => 'basketNoExist777']);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Group or basket does not exist', $responseBody->errors);

        $response     = $resListController->get($request, new \Slim\Http\Response(), ['userId' => 777, 'groupSerialId' => 2, 'basketId' => 'MyBasket']);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Basket out of perimeter', $responseBody->errors);

        $GLOBALS['userId'] = 'superadmin';
    }
}
