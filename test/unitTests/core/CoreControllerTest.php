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
    // scandir(dist): failed to open dir: No such file or directory
    // public function testInitialize()
    // {
    //     $CoreController = new \SrcCore\controllers\CoreController();

    //     $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
    //     $request     = \Slim\Http\Request::createFromEnvironment($environment);

    //     $response     = $CoreController->initialize($request, new \Slim\Http\Response());
    //     $responseBody = json_decode((string)$response->getBody());

    //     $this->assertNotEmpty($responseBody->coreUrl);
    //     $this->assertNotEmpty($responseBody->applicationName);
    //     $this->assertNotEmpty($responseBody->applicationMinorVersion);
    //     $version = explode(".", $responseBody->applicationMinorVersion);
    //     $this->assertSame('18', $version[0]);
    //     $this->assertSame('10', $version[1]);
    //     $this->assertInternalType('int', (int)$version[2]);
    //     $this->assertSame('fr', $responseBody->lang);
    //     $this->assertNotEmpty($responseBody->user);
    //     $this->assertInternalType('int', $responseBody->user->id);
    //     $this->assertSame('superadmin', $responseBody->user->user_id);
    //     $this->assertSame('Super', $responseBody->user->firstname);
    //     $this->assertNotEmpty($responseBody->scriptsToinject);
    // }

    public function testGetAdministration()
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
