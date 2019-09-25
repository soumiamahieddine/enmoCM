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
            'category'  => 'incoming',
            'private'   => true,
            'fields'    => [
                [
                    'identifier'    => 'subject',
                    'mandatory'     => true,
                    'default_value' => 'tika',
                ],
                [
                    'identifier'    => 'name',
                    'mandatory'     => true,
                    'default_value' => 'massala',
                ]
            ]
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);

        $response     = $indexingModelController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(200, $response->getStatusCode());

        self::$id = $responseBody->id;

        // GET BY ID
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $indexingModelController->getById($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('mon model d indexation', $responseBody->indexingModel->label);
        $this->assertSame(false, $responseBody->indexingModel->default);
        $this->assertSame(true, $responseBody->indexingModel->private);
        $this->assertSame('subject', $responseBody->indexingModel->fields[0]->identifier);
        $this->assertSame(true, $responseBody->indexingModel->fields[0]->mandatory);
        $this->assertSame('tika', $responseBody->indexingModel->fields[0]->default_value);
        $this->assertSame('name', $responseBody->indexingModel->fields[1]->identifier);
        $this->assertSame(true, $responseBody->indexingModel->fields[1]->mandatory);
        $this->assertSame('massala', $responseBody->indexingModel->fields[1]->default_value);


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
            'category'  => 'incoming',
            'default'   => false,
            'fields'    => [
                [
                    'identifier'    => 'subject',
                    'mandatory'     => true,
                    'default_value' => 'butter',
                ],
                [
                    'identifier'    => 'siret',
                    'mandatory'     => false,
                    'default_value' => 'chicken',
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
        $this->assertSame(false, $responseBody->indexingModel->default);
        $this->assertSame(true, $responseBody->indexingModel->private);
        $this->assertSame('subject', $responseBody->indexingModel->fields[0]->identifier);
        $this->assertSame(true, $responseBody->indexingModel->fields[0]->mandatory);
        $this->assertSame('butter', $responseBody->indexingModel->fields[0]->default_value);
        $this->assertSame('siret', $responseBody->indexingModel->fields[1]->identifier);
        $this->assertSame(false, $responseBody->indexingModel->fields[1]->mandatory);
        $this->assertSame('chicken', $responseBody->indexingModel->fields[1]->default_value);


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
