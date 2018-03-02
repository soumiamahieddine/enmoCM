<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class HistoryControllerTest extends TestCase
{
    public function testGetHistoryByUserId()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $history     = new \History\controllers\HistoryController();

        $response = $history->getByUserId($request, new \Slim\Http\Response(), ['userSerialId' => 1]);

        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotNull($responseBody->histories);
    }

    public function testGetHistory()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $history     = new \History\controllers\HistoryController();

        $response = $history->get($request, new \Slim\Http\Response(), ['date' => '2018-01-02']);

        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotNull($responseBody->filters->users);
        $this->assertNotNull($responseBody->filters->eventType);
        $this->assertNotNull($responseBody->historyList);
    }

    public function testGetHistoryBatch()
    {
        $environment  = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request      = \Slim\Http\Request::createFromEnvironment($environment);
        $historyBatch = new \History\controllers\HistoryBatchController();

        $response = $historyBatch->get($request, new \Slim\Http\Response(), ['date' => '2018-01-02']);

        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotNull($responseBody->filters->modules);
        $this->assertNotNull($responseBody->historyList);
    }
}
