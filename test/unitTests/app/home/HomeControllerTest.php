<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

*
* @brief   HomeControllerTest
*
* @author  dev <dev@maarch.org>
* @ingroup core
*/

use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{
    private static $id = null;

    public function testGet()
    {
        $homeController = new \Home\controllers\HomeController();

        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        $response = $homeController->get($request, new \Slim\Http\Response());
        $responseBody = json_decode((string) $response->getBody());
        
        $this->assertNotNull($responseBody->regroupedBaskets);
        $this->assertNotNull($responseBody->assignedBaskets);
        $this->assertNotEmpty($responseBody->homeMessage);
    }

    public function testGetLastRessources()
    {
        $homeController = new \Home\controllers\HomeController();

        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        $response = $homeController->getLastRessources($request, new \Slim\Http\Response());
        $responseBody = json_decode((string) $response->getBody());
        
        $this->assertIsArray($responseBody->lastResources);
    }
}
