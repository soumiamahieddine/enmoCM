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
    private static $idSub = null;

    private static $idFirstResource = null;
    private static $idSecondResource = null;
    private static $idThirdResource = null;


    public function testCreate()
    {
        $GLOBALS['userId'] = 'aackermann';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

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
        self::$idSub = $responseBody->folder;

        //  Error

        $aArgs = [
            'label' => ''
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $folderController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Body label is empty or not a string', $responseBody->errors);

        $GLOBALS['userId'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testUpdate()
    {
        $GLOBALS['userId'] = 'aackermann';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

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

        $GLOBALS['userId'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testGetById()
    {
        $GLOBALS['userId'] = 'aackermann';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

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

        $GLOBALS['userId'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testGet()
    {
        $GLOBALS['userId'] = 'aackermann';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

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

        $GLOBALS['userId'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testUnpinFolder()
    {
        $GLOBALS['userId'] = 'aackermann';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

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

        $GLOBALS['userId'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testPinFolder()
    {
        $GLOBALS['userId'] = 'aackermann';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

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

        $GLOBALS['userId'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testGetPinned()
    {
        $GLOBALS['userId'] = 'aackermann';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

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

        $GLOBALS['userId'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testAddResourcesById()
    {
        // Create resources to add to folder

        $GLOBALS['userId'] = 'cchaplin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

        $resController = new \Resource\controllers\ResController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $fileContent = file_get_contents('test/unitTests/samples/test.txt');
        $encodedFile = base64_encode($fileContent);

        $argsMailNew = [
            'modelId'          => 1,
            'status'           => 'NEW',
            'encodedFile'      => $encodedFile,
            'format'           => 'txt',
            'confidentiality'  => false,
            'documentDate'     => '2019-01-01 17:18:47',
            'arrivalDate'      => '2019-01-01 17:18:47',
            'processLimitDate' => '2029-01-01',
            'doctype'          => 102,
            'destination'      => 15,
            'initiator'        => 15,
            'subject'          => 'Breaking News : Superman is alive - PHP unit',
            'typist'           => 19,
            'priority'         => 'poiuytre1357nbvc',
            'followed'         => true,
            'diffusionList'    => [
                [
                    'id'   => 11,
                    'type' => 'user',
                    'mode' => 'dest'
                ]
            ]
        ];

        $argsMailATra = [
            'modelId'          => 1,
            'status'           => 'A_TRA',
            'encodedFile'      => $encodedFile,
            'format'           => 'txt',
            'confidentiality'  => false,
            'documentDate'     => '2019-01-01 17:18:47',
            'arrivalDate'      => '2019-01-01 17:18:47',
            'processLimitDate' => '2029-01-01',
            'doctype'          => 102,
            'destination'      => 15,
            'initiator'        => 15,
            'subject'          => 'Breaking News : Superman is alive - PHP unit',
            'typist'           => 19,
            'priority'         => 'poiuytre1357nbvc',
            'followed'         => true,
            'diffusionList'    => [
                [
                    'id'   => 11,
                    'type' => 'user',
                    'mode' => 'dest'
                ]
            ]
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($argsMailNew, $request);

        $response     = $resController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody(), true);
        self::$idFirstResource = $responseBody['resId'];
        $this->assertIsInt(self::$idFirstResource);

        $response     = $resController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody(), true);
        self::$idSecondResource = $responseBody['resId'];
        $this->assertIsInt(self::$idFirstResource);

        $fullRequest = \httpRequestCustom::addContentInBody($argsMailATra, $request);
        $response     = $resController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody(), true);
        self::$idThirdResource = $responseBody['resId'];
        $this->assertIsInt(self::$idFirstResource);

        // Actual test
        $GLOBALS['userId'] = 'aackermann';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

        $folderController = new \Folder\controllers\FolderController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        // Errors
        $response     = $folderController->addResourcesById($request, new \Slim\Http\Response(), ['id' => 'wrong format']);
        $this->assertNotEmpty(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Route id is not an integer', $responseBody['errors']);

        $body = [];

        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response     = $folderController->addResourcesById($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertNotEmpty(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body resources is empty or not an array', $responseBody['errors']);

        // Success
        $body = ['resources' => [self::$idFirstResource, self::$idSecondResource, self::$idThirdResource]];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response     = $folderController->addResourcesById($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertIsInt($responseBody['countResources']);
        $this->assertSame(3, $responseBody['countResources']);


        // Other errors
        $response     = $folderController->addResourcesById($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertIsInt($responseBody['countResources']);
        $this->assertSame(3, $responseBody['countResources']);


        $response     = $folderController->addResourcesById($fullRequest, new \Slim\Http\Response(), ['id' => self::$id * 1000]);
        $this->assertNotEmpty(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Folder out of perimeter', $responseBody['errors']);

        $body = ['resources' => [self::$idFirstResource * 1000, self::$idSecondResource * 1000, self::$idThirdResource * 1000]];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response     = $folderController->addResourcesById($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertNotEmpty(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertSame('Resources out of perimeter', $responseBody['errors']);

        $GLOBALS['userId'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testGetResourceById()
    {
        $GLOBALS['userId'] = 'aackermann';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

        $folderController = new \Folder\controllers\FolderController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        // Errors
        $response     = $folderController->getResourcesById($request, new \Slim\Http\Response(), ['id' => 'wrong format']);
        $this->assertNotEmpty(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Route id is not an integer', $responseBody['errors']);

        $response     = $folderController->getResourcesById($request, new \Slim\Http\Response(), ['id' => self::$id * 1000]);
        $this->assertNotEmpty(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Folder out of perimeter', $responseBody['errors']);

        // Success
        $queryParams = [];
        $fullRequest = $request->withQueryParams($queryParams);

        $response     = $folderController->getResourcesById($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertIsInt($responseBody['countResources']);
        $this->assertSame(3, $responseBody['countResources']);

        $this->assertIsArray($responseBody['allResources']);
        $this->assertSame(3, count($responseBody['allResources']));
        $this->assertSame(self::$idFirstResource, $responseBody['allResources'][0]);
        $this->assertSame(self::$idSecondResource, $responseBody['allResources'][1]);
        $this->assertSame(self::$idThirdResource, $responseBody['allResources'][2]);

        $this->assertSame(self::$idFirstResource, $responseBody['resources'][0]['resId']);
        $this->assertEmpty($responseBody['resources'][0]['chrono']);
        $this->assertEmpty($responseBody['resources'][0]['barcode']);
        $this->assertSame('Breaking News : Superman is alive - PHP unit', $responseBody['resources'][0]['subject']);
        $this->assertEmpty($responseBody['resources'][0]['confidentiality']);
        $this->assertSame('Nouveau courrier pour le service', $responseBody['resources'][0]['statusLabel']);
        $this->assertSame('fm-letter-status-new', $responseBody['resources'][0]['statusImage']);
        $this->assertSame('#009dc5', $responseBody['resources'][0]['priorityColor']);
        $this->assertEmpty($responseBody['resources'][0]['closing_date']);
        $this->assertSame(0, $responseBody['resources'][0]['countAttachments']);
        $this->assertSame(true, $responseBody['resources'][0]['hasDocument']);
        $this->assertSame(false, $responseBody['resources'][0]['mailTracking']);
        $this->assertEmpty($responseBody['resources'][0]['integrations']);
        $this->assertSame(0, $responseBody['resources'][0]['countNotes']);
        $this->assertIsArray($responseBody['resources'][0]['folders']);
        $this->assertSame(1, count($responseBody['resources'][0]['folders']));
        $this->assertIsArray($responseBody['resources'][0]['display']);
        $this->assertEmpty($responseBody['resources'][0]['display']);

        $GLOBALS['userId'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testGetBaskets()
    {
        $GLOBALS['userId'] = 'aackermann';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

        $folderController = new \Folder\controllers\FolderController();

        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        // Errors
        $response     = $folderController->getBasketsFromFolder($request, new \Slim\Http\Response(), ['id' => 'wrong format']);
        $this->assertNotEmpty(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Route id is not an integer', $responseBody['errors']);

        $response     = $folderController->getBasketsFromFolder($request, new \Slim\Http\Response(), ['id' => self::$id * 1000]);
        $this->assertNotEmpty(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Folder out of perimeter', $responseBody['errors']);

        $response     = $folderController->getBasketsFromFolder($request, new \Slim\Http\Response(), ['id' => self::$id, 'resId' => self::$idFirstResource * 100]);
        $this->assertNotEmpty(403, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Resource out of perimeter', $responseBody['errors']);

        // Success
        $response     = $folderController->getBasketsFromFolder($request, new \Slim\Http\Response(), ['id' => self::$id, 'resId' => self::$idFirstResource]);
        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($responseBody['groupsBaskets']);
        $this->assertNotEmpty($responseBody['groupsBaskets']);

        $this->assertSame(2, count($responseBody['groupsBaskets']));

        $this->assertSame(2, $responseBody['groupsBaskets'][0]['groupId']);
        $this->assertSame('Utilisateur', $responseBody['groupsBaskets'][0]['groupName']);
        $this->assertSame(6, $responseBody['groupsBaskets'][0]['basketId']);
        $this->assertSame('AR en masse : non envoyés', $responseBody['groupsBaskets'][0]['basketName']);

        $this->assertSame(2, $responseBody['groupsBaskets'][1]['groupId']);
        $this->assertSame('Utilisateur', $responseBody['groupsBaskets'][1]['groupName']);
        $this->assertSame(4, $responseBody['groupsBaskets'][1]['basketId']);
        $this->assertSame('Courriers à traiter', $responseBody['groupsBaskets'][1]['basketName']);

        $GLOBALS['userId'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testGetFilters()
    {
        $GLOBALS['userId'] = 'aackermann';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

        $folderController = new \Folder\controllers\FolderController();

        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        // Errors
        $response     = $folderController->getFilters($request, new \Slim\Http\Response(), ['id' => 'wrong format']);
        $this->assertNotEmpty(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Route id is not an integer', $responseBody['errors']);

        $response     = $folderController->getFilters($request, new \Slim\Http\Response(), ['id' => self::$id * 1000]);
        $this->assertNotEmpty(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Folder out of perimeter', $responseBody['errors']);

        $response     = $folderController->getFilters($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($responseBody['entities']);
        $this->assertEmpty($responseBody['entities']);
        $this->assertIsArray($responseBody['priorities']);
        $this->assertEmpty($responseBody['priorities']);
        $this->assertIsArray($responseBody['categories']);
        $this->assertEmpty($responseBody['categories']);

        $this->assertIsArray($responseBody['statuses']);

        $this->assertSame(2, count($responseBody['statuses']));

        $this->assertSame('NEW', $responseBody['statuses'][0]['id']);
        $this->assertSame('Nouveau courrier pour le service', $responseBody['statuses'][0]['label']);
        $this->assertSame(2, $responseBody['statuses'][0]['count']);

        $this->assertSame('A_TRA', $responseBody['statuses'][1]['id']);
        $this->assertSame('PJ à traiter', $responseBody['statuses'][1]['label']);
        $this->assertSame(1, $responseBody['statuses'][1]['count']);

        $this->assertIsArray($responseBody['entitiesChildren']);
        $this->assertEmpty($responseBody['entitiesChildren']);
        $this->assertIsArray($responseBody['entitiesChildren']);
        $this->assertEmpty($responseBody['entitiesChildren']);
        $this->assertIsArray($responseBody['doctypes']);
        $this->assertEmpty($responseBody['doctypes']);
        $this->assertIsArray($responseBody['folders']);
        $this->assertEmpty($responseBody['folders']);

        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $folderController->getFilters($request, new \Slim\Http\Response(), ['id' => self::$idSub]);
        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($responseBody['entities']);
        $this->assertEmpty($responseBody['entities']);
        $this->assertIsArray($responseBody['priorities']);
        $this->assertEmpty($responseBody['priorities']);
        $this->assertIsArray($responseBody['categories']);
        $this->assertEmpty($responseBody['categories']);
        $this->assertIsArray($responseBody['statuses']);
        $this->assertEmpty($responseBody['statuses']);
        $this->assertIsArray($responseBody['entitiesChildren']);
        $this->assertEmpty($responseBody['entitiesChildren']);
        $this->assertIsArray($responseBody['entitiesChildren']);
        $this->assertEmpty($responseBody['entitiesChildren']);
        $this->assertIsArray($responseBody['doctypes']);
        $this->assertEmpty($responseBody['doctypes']);
        $this->assertIsArray($responseBody['folders']);
        $this->assertEmpty($responseBody['folders']);

        $GLOBALS['userId'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testRemoveResourcesById()
    {
        $GLOBALS['userId'] = 'aackermann';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

        $folderController = new \Folder\controllers\FolderController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        // Errors
        $response     = $folderController->removeResourcesById($request, new \Slim\Http\Response(), ['id' => 'wrong format']);
        $this->assertNotEmpty(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Route id is not an integer', $responseBody['errors']);

        $response     = $folderController->removeResourcesById($request, new \Slim\Http\Response(), ['id' => self::$id * 1000]);
        $this->assertNotEmpty(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Folder out of perimeter', $responseBody['errors']);

        $body = [];

        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response     = $folderController->removeResourcesById($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertNotEmpty(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body resources is empty or not an array', $responseBody['errors']);

        // Success
        $body = ['resources' => [self::$idFirstResource, self::$idSecondResource]];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response     = $folderController->removeResourcesById($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertIsInt($responseBody['countResources']);
        $this->assertSame(1, $responseBody['countResources']);


        // Other errors
        $response     = $folderController->removeResourcesById($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertIsInt($responseBody['countResources']);
        $this->assertSame(1, $responseBody['countResources']);

        $GLOBALS['userId'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testDelete()
    {
        $GLOBALS['userId'] = 'aackermann';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

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

        // DELETE TEST RESOURCES
        \Resource\models\ResModel::delete([
            'where' => ['res_id in (?)'],
            'data' => [[self::$idFirstResource, self::$idSecondResource, self::$idThirdResource]]
        ]);

        $res = \Resource\models\ResModel::getById(['resId' => self::$idFirstResource, 'select' => ['*']]);
        $this->assertIsArray($res);
        $this->assertEmpty($res);

        $res = \Resource\models\ResModel::getById(['resId' => self::$idSecondResource, 'select' => ['*']]);
        $this->assertIsArray($res);
        $this->assertEmpty($res);

        $res = \Resource\models\ResModel::getById(['resId' => self::$idThirdResource, 'select' => ['*']]);
        $this->assertIsArray($res);
        $this->assertEmpty($res);

        $GLOBALS['userId'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }
}
