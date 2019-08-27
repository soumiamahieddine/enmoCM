<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class IndexingModelControllerTest extends TestCase
{
    private static $id = null;

    public function testCreate()
    {
        $indexingModelController = new \IndexingModel\controllers\IndexingModelController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $args = [
            'label'     => 'mon model d indexation',
            'private'   => true,
            'fields'    => [
                [
                    'type'          => 'standard',
                    'identifier'    => 1,
                    'mandatory'     => true,
                    'value'         => 'tika',
                ],
                [
                    'type'          => 'standard',
                    'identifier'    => 2,
                    'mandatory'     => true,
                    'value'         => 'massala',
                ]
            ]
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);

        $response     = $indexingModelController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody());

        self::$id = $responseBody->id;

        // GET BY ID
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $indexingModelController->getById($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('mon model d indexation', $responseBody->indexingModel->label);
        $this->assertSame(true, $responseBody->indexingModel->private);
        $this->assertSame('standard', $responseBody->indexingModel->fields[0]->type);
        $this->assertSame(1, $responseBody->indexingModel->fields[0]->identifier);
        $this->assertSame(true, $responseBody->indexingModel->fields[0]->mandatory);
        $this->assertSame('tika', $responseBody->indexingModel->fields[0]->value);
        $this->assertSame('standard', $responseBody->indexingModel->fields[1]->type);
        $this->assertSame(2, $responseBody->indexingModel->fields[1]->identifier);
        $this->assertSame(true, $responseBody->indexingModel->fields[1]->mandatory);
        $this->assertSame('massala', $responseBody->indexingModel->fields[1]->value);


        //  Errors
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        unset($args['label']);
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);

        $response     = $indexingModelController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Body label is empty or not a string', $responseBody->errors);
    }

    public function testUpdate()
    {
        $indexingModelController = new \IndexingModel\controllers\IndexingModelController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $args = [
            'label'     => 'mon model d indexation modifié',
            'fields'    => [
                [
                    'type'          => 'standard',
                    'identifier'    => 4,
                    'mandatory'     => true,
                    'value'         => 'butter',
                ],
                [
                    'type'          => 'custom',
                    'identifier'    => 8,
                    'mandatory'     => false,
                    'value'         => 'chicken',
                ]
            ]
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);

        $response     = $indexingModelController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(204, $response->getStatusCode());

        // GET BY ID
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $indexingModelController->getById($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('mon model d indexation modifié', $responseBody->indexingModel->label);
        $this->assertSame(true, $responseBody->indexingModel->private);
        $this->assertSame('standard', $responseBody->indexingModel->fields[0]->type);
        $this->assertSame(4, $responseBody->indexingModel->fields[0]->identifier);
        $this->assertSame(true, $responseBody->indexingModel->fields[0]->mandatory);
        $this->assertSame('butter', $responseBody->indexingModel->fields[0]->value);
        $this->assertSame('custom', $responseBody->indexingModel->fields[1]->type);
        $this->assertSame(8, $responseBody->indexingModel->fields[1]->identifier);
        $this->assertSame(false, $responseBody->indexingModel->fields[1]->mandatory);
        $this->assertSame('chicken', $responseBody->indexingModel->fields[1]->value);


        //  Errors
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        unset($args['label']);
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);

        $response     = $indexingModelController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Body label is empty or not a string', $responseBody->errors);
    }

    public function testGet()
    {
        $indexingModelController = new \IndexingModel\controllers\IndexingModelController();

        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $indexingModelController->get($request, new \Slim\Http\Response());
        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->indexingModels);
    }

    public function testDelete()
    {
        $indexingModelController = new \IndexingModel\controllers\IndexingModelController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);


        $response     = $indexingModelController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(204, $response->getStatusCode());

        //  Errors
        $response     = $indexingModelController->delete($request, new \Slim\Http\Response(), ['id' => 99999]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Model not found', $responseBody->errors);
    }
}
