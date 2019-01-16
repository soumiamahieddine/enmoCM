<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class ExportControllerTest extends TestCase
{
    public function testGetExportTemplate()
    {
        $ExportController = new \Resource\controllers\ExportController();

        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $ExportController->getExportTemplate($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('string', $responseBody->template);
        $this->assertInternalType('string', $responseBody->delimiter);
    }
}
