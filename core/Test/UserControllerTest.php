<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;


class UserControllerTest extends TestCase
{
    private static $id = null;


    public function testCreate()
    {
        $userController = new \User\controllers\UserController();

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
        $userController = new \User\controllers\UserController();

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

        $this->assertSame('success', $responseBody->success);

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

    public function testRead()
    {
        $userController = new \User\controllers\UserController();
        $parameterController = new \Parameter\controllers\ParameterController();
        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'description'           => 'User quota',
            'param_value_int'       => 0
        ];
        $fullRequest    = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response       = $parameterController->update($fullRequest, new \Slim\Http\Response(), ['id' => 'user_quota']);

        // READ in case of deactivated user_quota
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $userController->get($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertNotNull($responseBody->users);
        $this->assertNull($responseBody->quota->userQuota);

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'description'           => 'User quota',
            'param_value_int'       => 20
        ];
        $fullRequest    = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response       = $parameterController->update($fullRequest, new \Slim\Http\Response(), ['id' => 'user_quota']);

        // READ in case of enabled user_quotat
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $userController->get($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertNotNull($responseBody->users);
        $this->assertNotNull($responseBody->quota);
        $this->assertSame(20, $responseBody->quota->userQuota);
        $this->assertNotNull($responseBody->quota->actives);
        $this->assertInternalType('int', $responseBody->quota->inactives);

    }

    public function testUserQuota()
    {
        $userController = new \User\controllers\UserController();
        $parameterController = new \Parameter\controllers\ParameterController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'userId'    => 'TEST-CKENTquota',
            'firstname' => 'TEST-CLARKquota',
            'lastname'  => 'TEST-KENTquota'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());
        $userId = $responseBody->user->id;

        $this->assertInternalType('int', $userId);

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'user_id'    => 'TEST-CKENTquota',
            'firstname' => 'TEST-CLARKquota2',
            'lastname'  => 'TEST-KENTquota2',
            'mail'      => 'ck@dailyP.com',
            'phone'     => '0122334455',
            'initials'  => 'CK',
            'enabled'   => 'N',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->update($fullRequest, new \Slim\Http\Response(), ['id' =>$userId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  UPDATE disabled user for user_quota (avoid notification sending)
        $aArgs = [
            'user_id'    => 'TEST-CKENTquota',
            'firstname' => 'TEST-CLARKquota2',
            'lastname'  => 'TEST-KENTquota2',
            'mail'      => 'ck@dailyP.com',
            'phone'     => '0122334455',
            'initials'  => 'CK',
            'enabled'   => 'Y',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->update($fullRequest, new \Slim\Http\Response(), ['id' =>$userId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  DELETE
        //  REAL DELETE
        \SrcCore\models\DatabaseModel::delete([
            'table' => 'users',
            'where' => ['id = ?'],
            'data'  => [$userId]
        ]);

        //  UPDATE
        $aArgs = [
            'description'           => 'User quota',
            'param_value_int'       => 0
        ];
        $fullRequest    = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response       = $parameterController->update($fullRequest, new \Slim\Http\Response(), ['id' => 'user_quota']);
    }

    public function testDelete()
    {
        $userController = new \User\controllers\UserController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $userController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

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
        \SrcCore\models\DatabaseModel::delete([
            'table' => 'users',
            'where' => ['id = ?'],
            'data'  => [self::$id]
        ]);
    }

}
