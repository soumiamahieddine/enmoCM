<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class GroupControllerTest extends TestCase
{
    private static $id = null;


    public function testCreate()
    {
        $groupController = new \Group\controllers\GroupController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'group_id'      => 'TEST-JusticeLeague',
            'group_desc'    => 'Beyond the darkness',
            'security'      => [
                'where_clause'      => '1=2',
                'maarch_comment'    => 'commentateur du dimanche'
            ]
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $groupController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        self::$id = $responseBody->group;

        $this->assertIsInt($responseBody->group);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $groupController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('TEST-JusticeLeague', $responseBody->group->group_id);
        $this->assertSame('Beyond the darkness', $responseBody->group->group_desc);
        $this->assertSame('1=2', $responseBody->group->security->where_clause);
        $this->assertSame('commentateur du dimanche', $responseBody->group->security->maarch_comment);
        $this->assertIsArray($responseBody->group->users);
        $this->assertIsArray($responseBody->group->baskets);
        $this->assertEmpty($responseBody->group->users);
        $this->assertEmpty($responseBody->group->baskets);
        $this->assertSame(true, $responseBody->group->canAdminUsers);
        $this->assertSame(true, $responseBody->group->canAdminBaskets);
    }

    public function testUpdate()
    {
        $groupController = new \Group\controllers\GroupController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'description' => 'Beyond the darkness #2',
            'security'  => [
                'where_clause'   => '1=3',
                'maarch_comment' => 'commentateur du dimanche #2'
            ]
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $groupController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $groupController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('TEST-JusticeLeague', $responseBody->group->group_id);
        $this->assertSame('Beyond the darkness #2', $responseBody->group->group_desc);
        $this->assertSame('1=3', $responseBody->group->security->where_clause);
        $this->assertSame('commentateur du dimanche #2', $responseBody->group->security->maarch_comment);
        $this->assertIsArray($responseBody->group->users);
        $this->assertIsArray($responseBody->group->baskets);
        $this->assertEmpty($responseBody->group->users);
        $this->assertEmpty($responseBody->group->baskets);
        $this->assertSame(true, $responseBody->group->canAdminUsers);
        $this->assertSame(true, $responseBody->group->canAdminBaskets);
    }

    public function testGetById()
    {
        $groupController = new \Group\controllers\GroupController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $groupController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->group);

        $this->assertSame(self::$id, $responseBody->group->id);
        $this->assertSame('TEST-JusticeLeague', $responseBody->group->group_id);
        $this->assertSame('Beyond the darkness #2', $responseBody->group->group_desc);

        // ERROR
        $response     = $groupController->getById($request, new \Slim\Http\Response(), ['id' => '123456789']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Group not found', $responseBody->errors);
    }

    public function testGet()
    {
        $groupController = new \Group\controllers\GroupController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $groupController->get($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->groups);

        foreach ($responseBody->groups as $value) {
            $this->assertNotEmpty($value->group_id);
            $this->assertNotEmpty($value->group_desc);
            $this->assertNotNull($value->users);
            $this->assertIsInt($value->id);
        }
    }

    public function testAddPrivilege() {
        $privilegeController = new \Group\controllers\PrivilegeController();

        //  Add privilege
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $args = [
            'privilegeId'      => 'reports',
            'id'    => self::$id
        ];

        $response     = $privilegeController->addPrivilege($request, new \Slim\Http\Response(), $args);
        $this->assertSame(204, $response->getStatusCode());

        // Add privilege again

        $response     = $privilegeController->addPrivilege($request, new \Slim\Http\Response(), $args);
        $this->assertSame(204, $response->getStatusCode());

        // Error : group does not exist
        $args = [
            'privilegeId'      => 'reports',
            'id'    => self::$id * 100
        ];

        $response     = $privilegeController->addPrivilege($request, new \Slim\Http\Response(), $args);
        $this->assertSame(400, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody());
        $this->assertIsString($responseBody->errors);
        $this->assertSame('Group not found', $responseBody->errors);
    }

    public function testRemovePrivilege() {
        $privilegeController = new \Group\controllers\PrivilegeController();

        //  Remove privilege
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $args = [
            'privilegeId'      => 'reports',
            'id'    => self::$id
        ];

        $response     = $privilegeController->removePrivilege($request, new \Slim\Http\Response(), $args);
        $this->assertSame(204, $response->getStatusCode());

        // Remove privilege again

        $response     = $privilegeController->removePrivilege($request, new \Slim\Http\Response(), $args);
        $this->assertSame(204, $response->getStatusCode());

        // Error : group does not exist
        $args = [
            'privilegeId'      => 'reports',
            'id'    => self::$id * 100
        ];

        $response     = $privilegeController->addPrivilege($request, new \Slim\Http\Response(), $args);
        $this->assertSame(400, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody());
        $this->assertIsString($responseBody->errors);
        $this->assertSame('Group not found', $responseBody->errors);
    }

    public function testDelete()
    {
        $groupController = new \Group\controllers\GroupController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $groupController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertIsArray($responseBody->groups);
        $this->assertNotEmpty($responseBody->groups);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $groupController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('Group not found', $responseBody->errors);
    }
}
