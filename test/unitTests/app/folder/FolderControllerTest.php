<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class FolderControllerTest extends TestCase
{
    private static $id = null;


    public function testCreate()
    {
        $folderController = new \Folder\controllers\FolderController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'label'      => 'Mon premier dossier'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $folderController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        self::$id = $responseBody->folder;

        $this->assertIsInt(self::$id);

        // Create SubFolder
        $aArgs = [
            'label'     => 'Mon deuxieme dossier',
            'parent_id' => self::$id
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $folderController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertIsInt($responseBody->folder);

        //  Error

        $aArgs = [
            'label' => ''
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $folderController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Body label is empty or not a string', $responseBody->errors);
    }

    public function testUpdate()
    {
        $folderController = new \Folder\controllers\FolderController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'label' => 'Mon deuxieme dossier renomme',
            'parent_id'  => 0
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $folderController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);

        $this->assertSame(200, $response->getStatusCode());

        //ERROR
        $aArgs = [
            'label' => 'Mon deuxieme dossier renomme 2',
            'parent_id'  => 999999
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $folderController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('parent_id does not exist or Id is a parent of parent_id', $responseBody->errors);
    }

    public function testGetById()
    {
        $folderController = new \Folder\controllers\FolderController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $folderController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->folder->id);
        $this->assertSame('Mon deuxieme dossier renomme', $responseBody->folder->label);
        $this->assertSame(false, $responseBody->folder->public);
        $this->assertSame(null, $responseBody->folder->parent_id);
        $this->assertSame(0, $responseBody->folder->level);
        $this->assertIsArray($responseBody->folder->sharing->entities);
        $this->assertIsInt($responseBody->folder->user_id);
        $this->assertNotEmpty($responseBody->folder->user_id);
        $this->assertNotEmpty($responseBody->folder->ownerDisplayName);

        // ERROR
        $response     = $folderController->getById($request, new \Slim\Http\Response(), ['id' => '123456789']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Folder not found or out of your perimeter', $responseBody->errors);
    }

    public function testGet()
    {
        $folderController = new \Folder\controllers\FolderController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $folderController->get($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->folders);

        foreach ($responseBody->folders as $value) {
            $this->assertNotEmpty($value->name);
            $this->assertNotEmpty($value->id);
            $this->assertIsInt($value->id);
            $this->assertNotEmpty($value->label);
            $this->assertIsBool($value->public);
            $this->assertIsInt($value->user_id);
            if (!empty($value->parent_id)) {
                $this->assertIsInt($value->parent_id);
            }
            $this->assertIsInt($value->level);
            $this->assertIsInt($value->countResources);
        }
    }

    public function testUnpinFolder() {
        $folderController = new \Folder\controllers\FolderController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $args = [
            'id' => self::$id
        ];

        $response     = $folderController->unpinFolder($request, new \Slim\Http\Response(), $args);

        $this->assertSame(204, $response->getStatusCode());

        // ERROR

        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $folderController->unpinFolder($fullRequest, new \Slim\Http\Response(), $args);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Folder is not pinned', $responseBody->errors);


        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $folderController->unpinFolder($fullRequest, new \Slim\Http\Response(), ['id' => self::$id + 100]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Folder not found or out of your perimeter', $responseBody->errors);

        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $folderController->unpinFolder($fullRequest, new \Slim\Http\Response(), ['id' => 'test']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Route id not found or is not an integer', $responseBody->errors);
    }

    public function testPinFolder() {
        $folderController = new \Folder\controllers\FolderController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $args = [
            'id' => self::$id
        ];

        $response     = $folderController->pinFolder($request, new \Slim\Http\Response(), $args);

        $this->assertSame(204, $response->getStatusCode());

        // ERROR

        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $folderController->pinFolder($fullRequest, new \Slim\Http\Response(), $args);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Folder is already pinned', $responseBody->errors);


        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $folderController->pinFolder($fullRequest, new \Slim\Http\Response(), ['id' => self::$id + 100]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Folder not found or out of your perimeter', $responseBody->errors);

        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $folderController->pinFolder($fullRequest, new \Slim\Http\Response(), ['id' => 'test']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Route id not found or is not an integer', $responseBody->errors);
    }

    public function testGetPinned()
    {
        $folderController = new \Folder\controllers\FolderController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $folderController->getPinnedFolders($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->folders);

        foreach ($responseBody->folders as $value) {
            $this->assertNotEmpty($value->name);
            $this->assertNotEmpty($value->id);
            $this->assertIsInt($value->id);
            $this->assertNotEmpty($value->label);
            $this->assertIsBool($value->public);
            $this->assertIsInt($value->user_id);
            if (!empty($value->parent_id)) {
                $this->assertIsInt($value->parent_id);
            }
            $this->assertIsInt($value->level);
            $this->assertIsInt($value->countResources);
        }
    }

    public function testDelete()
    {
        $folderController = new \Folder\controllers\FolderController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $folderController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);

        $this->assertSame(204, $response->getStatusCode());

        //  DELETE ERROR
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $folderController->delete($request, new \Slim\Http\Response(), ['id' => 999999]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('Cannot delete because at least one folder is out of your perimeter', $responseBody->errors);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $folderController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('Folder not found or out of your perimeter', $responseBody->errors);
    }
}
