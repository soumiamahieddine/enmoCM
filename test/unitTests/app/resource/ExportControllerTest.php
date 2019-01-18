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

    public function testUpdateExport()
    {
        $GLOBALS['userId'] = 'bbain';

        $ExportController = new \Resource\controllers\ExportController();

        //  PUT
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            "delimiter" => ';',
            "data" => [
                [
                    "value" => "subject",
                    "label" => "Sujet",
                    "isFunction" => false
                ],
                [
                    "value" => "getStatus",
                    "label" => "Status",
                    "isFunction" => true
                ],
                [
                    "value" => "getPriority",
                    "label" => "Priorité",
                    "isFunction" => true
                ],
                [
                    "value" => "getCopyEntities",
                    "label" => "Copies",
                    "isFunction" => true
                ],
                [
                    "value" => "getDetailLink",
                    "label" => "Lien page détaillé",
                    "isFunction" => true
                ],
                [
                    "value" => "getParentFolder",
                    "label" => "Dossier",
                    "isFunction" => true
                ],
                [
                    "value" => "getInitiatorEntity",
                    "label" => "Entité initiatrice",
                    "isFunction" => true
                ],
                [
                    "value" => "getDestinationEntity",
                    "label" => "Entité traitante",
                    "isFunction" => true
                ],
                [
                    "value" => "getCategory",
                    "label" => "Catégorie",
                    "isFunction" => true
                ],
            ]
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $ExportController->updateExport($fullRequest, new \Slim\Http\Response(), ['userId' => 19, 'groupId' => 2, 'basketId' => 'MyBasket']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(null, $responseBody);

        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $ExportController->getExportTemplate($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $template = (array)$responseBody->template;
        foreach ($template as $key => $value) {
            $template[$key] = (array)$value;
        }
        $this->assertSame($aArgs['data'], $template);
        $this->assertSame(';', $responseBody->delimiter);


        //ERRORS
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        unset($aArgs['data'][2]['label']);
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response = $ExportController->updateExport($fullRequest, new \Slim\Http\Response(), ['userId' => 19, 'groupId' => 2, 'basketId' => 'MyBasket']);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('One data is not set well', $responseBody->errors);

        unset($aArgs['data']);
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response = $ExportController->updateExport($fullRequest, new \Slim\Http\Response(), ['userId' => 19, 'groupId' => 2, 'basketId' => 'MyBasket']);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Data is not an array or empty', $responseBody->errors);

        $aArgs['delimiter'] = 't';
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response = $ExportController->updateExport($fullRequest, new \Slim\Http\Response(), ['userId' => 19, 'groupId' => 2, 'basketId' => 'MyBasket']);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Delimiter is not set or not set well', $responseBody->errors);

        $GLOBALS['userId'] = 'superadmin';
    }
}
