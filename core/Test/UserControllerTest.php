<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

namespace MaarchTest;
use PHPUnit\Framework\TestCase;

use Core\Models\DatabaseModel;

class UserControllerTest extends TestCase
{
    private static $id = null;


    public function testCreate()
    {
        $userController = new \Core\Controllers\UserController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'userId'    => 'TEST-CKENT',
            'firstname' => 'TEST-CLARK',
            'lastname'  => 'TEST-KENT'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        self::$id = $responseBody->user->id;

        $this->assertInternalType('int', self::$id);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->id);
        $this->assertSame('TEST-CKENT', $responseBody->user_id);
        $this->assertSame('TEST-CLARK', $responseBody->firstname);
        $this->assertSame('TEST-KENT', $responseBody->lastname);
        $this->assertSame('OK', $responseBody->status);
        $this->assertSame('Y', $responseBody->enabled);
        $this->assertSame(null, $responseBody->phone);
        $this->assertSame(null, $responseBody->mail);
        $this->assertSame(null, $responseBody->initials);
        $this->assertSame(null, $responseBody->thumbprint);
    }

    public function testUpdate()
    {
        $userController = new \Core\Controllers\UserController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'user_id'    => 'TEST-CKENT',
            'firstname' => 'TEST-CLARK2',
            'lastname'  => 'TEST-KENT2',
            'mail'      => 'ck@dailyP.com',
            'phone'     => '0122334455',
            'initials'  => 'CK',
            'enabled'   => 'N',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(_USER_UPDATED, $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->id);
        $this->assertSame('TEST-CKENT', $responseBody->user_id);
        $this->assertSame('TEST-CLARK2', $responseBody->firstname);
        $this->assertSame('TEST-KENT2', $responseBody->lastname);
        $this->assertSame('OK', $responseBody->status);
        $this->assertSame('N', $responseBody->enabled);
        $this->assertSame('0122334455', $responseBody->phone);
        $this->assertSame('ck@dailyP.com', $responseBody->mail);
        $this->assertSame('CK', $responseBody->initials);
        $this->assertSame(null, $responseBody->thumbprint);
    }

    public function testDelete()
    {
        $userController = new \Core\Controllers\UserController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $userController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame(_DELETED_USER, $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $userController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->id);
        $this->assertSame('TEST-CKENT', $responseBody->user_id);
        $this->assertSame('TEST-CLARK2', $responseBody->firstname);
        $this->assertSame('TEST-KENT2', $responseBody->lastname);
        $this->assertSame('DEL', $responseBody->status);
        $this->assertSame('N', $responseBody->enabled);
        $this->assertSame('0122334455', $responseBody->phone);
        $this->assertSame('ck@dailyP.com', $responseBody->mail);
        $this->assertSame('CK', $responseBody->initials);
        $this->assertSame(null, $responseBody->thumbprint);

        //  REAL DELETE
        DatabaseModel::delete([
            'table' => 'users',
            'where' => ['id = ?'],
            'data'  => [self::$id]
        ]);
    }

}
