<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;
use SrcCore\models\DatabaseModel;

class NoteControllerTest extends TestCase
{
    private static $noteId = null;
    private static $noteId2 = null;
    private static $resId = null;

    public function testCreate()
    {
        //get last notes
        $getResId = DatabaseModel::select([
            'select'    => ['res_id'],
            'table'     => ['res_letterbox'],
            'order_by'  => ['res_id DESC'],
            'limit'     => 1
        ]);

        self::$resId = $getResId[0]['res_id'];

        $this->assertInternalType('int', self::$resId);

        $noteController = new \Note\controllers\NoteController();

        // CREATE WITH ALL DATA -> OK
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'value'    => "Test d'ajout d'une note par php unit",
            'entities' => ['COU', 'CAB']
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $noteController->create($fullRequest, new \Slim\Http\Response(), ['resId' => self::$resId]);
        $responseBody = json_decode((string)$response->getBody());

        self::$noteId = $responseBody->noteId;

        $this->assertInternalType('int', self::$noteId);

        // CREATE WITHOUT ENTITIES -> OK
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'value' => "Test d'ajout d'une note par php unit"
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $noteController->create($fullRequest, new \Slim\Http\Response(), ['resId' => self::$resId]);
        $responseBody = json_decode((string)$response->getBody());

        self::$noteId2 = $responseBody->noteId;

        $this->assertInternalType('int', self::$noteId);

        // CREATE WITH NOTE_TEXT MISSING -> NOT OK
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'entities' => ["COU", "CAB"]
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $noteController->create($fullRequest, new \Slim\Http\Response(), ['resId' => self::$resId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Data value is empty or not a string', $responseBody->errors);
    }

    public function testUpdate()
    {
        $noteController = new \Note\controllers\NoteController();

        //  Update working
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'value'      => "Test modification d'une note par php unit",
            'entities'   => ['COU', 'DGS']
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $noteController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$noteId, 'resId' => self::$resId]);

        $this->assertSame(204, $response->getStatusCode());

        // Update fail
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'value' => ''
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $noteController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$noteId, 'resId' => self::$resId]);

        $this->assertSame(400, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('string', $responseBody->errors);
        $this->assertSame('Body value is empty or not a string', $responseBody->errors);
    }

    public function testGetById()
    {
        $noteController = new \Note\controllers\NoteController();

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $noteController->getById($request, new \Slim\Http\Response(), ['id' => self::$noteId, 'resId' => self::$resId]);

        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('string', $responseBody->value);
        $this->assertSame("Test modification d'une note par php unit", $responseBody->value);
        $this->assertInternalType('array', $responseBody->entities);

        $response = $noteController->getById($request, new \Slim\Http\Response(), ['id' => 999999999, 'resId' => self::$resId]);

        $this->assertSame(403, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Note out of perimeter', $responseBody->errors);
    }

    public function testGet()
    {
        $noteController = new \Note\controllers\NoteController();

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $noteController->get($request, new \Slim\Http\Response(), ['resId' => self::$resId]);

        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody);
        $this->assertNotEmpty($responseBody);

        foreach ($responseBody as $value) {
            $this->assertInternalType('int', $value->id);
            $this->assertInternalType('int', $value->identifier);
            $this->assertInternalType('string', $value->value);
            $this->assertNotEmpty($value->value);
            $this->assertInternalType('int', $value->user_id);
            $this->assertInternalType('string', $value->firstname);
            $this->assertNotEmpty($value->firstname);
            $this->assertInternalType('string', $value->lastname);
            $this->assertNotEmpty($value->lastname);
        }
    }

    public function testDelete()
    {
        //  DELETE
        $environment  = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request      = \Slim\Http\Request::createFromEnvironment($environment);

        $noteController = new \Note\controllers\NoteController();
        $response         = $noteController->delete($request, new \Slim\Http\Response(), ['id' => self::$noteId, 'resId' => self::$resId]);

        $this->assertSame(204, $response->getStatusCode());

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $noteController->getById($request, new \Slim\Http\Response(), ['id' => self::$noteId, 'resId' => self::$resId]);

        $this->assertSame(403, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('string', $responseBody->errors);
        $this->assertSame('Note out of perimeter', $responseBody->errors);

        // FAIL DELETE
        $noteController = new \Note\controllers\NoteController();
        $response         = $noteController->delete($request, new \Slim\Http\Response(), ['id' => self::$noteId, 'resId' => self::$resId]);
        $responseBody     = json_decode((string)$response->getBody());

        $this->assertSame('Note out of perimeter', $responseBody->errors);
        $this->assertSame(403, $response->getStatusCode());

        $noteController->delete($request, new \Slim\Http\Response(), ['id' => self::$noteId2, 'resId' => self::$resId]);
    }
}
