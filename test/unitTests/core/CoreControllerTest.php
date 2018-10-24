<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ActionsControllerTest
* @author  dev <dev@maarch.org>
* @ingroup core
*/

use PHPUnit\Framework\TestCase;

class CoreControllerTest extends TestCase
{
    public function testInitialize()
    {
        $coreController = new \SrcCore\controllers\CoreController();

        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $coreController->getAdministration($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->administrations->organisation);
        $this->assertNotEmpty($responseBody->administrations->classement);
        $this->assertNotEmpty($responseBody->administrations->production);
        $this->assertNotEmpty($responseBody->administrations->supervision);
    }

    public function testrenderJnlp()
    {
        $coreController = new \ContentManagement\controllers\JnlpController();

        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $coreController->renderJnlp($request, new \Slim\Http\Response(), ['jnlpUniqueId' => 'superadmin_maarchCM_12345.js']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('File extension forbidden', $responseBody->errors);
    }
}
