<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

use PHPUnit\Framework\TestCase;

class RegisteredNumberRangeControllerTest extends TestCase
{
    private static $id = null;
    private static $siteId = null;

    public function testCreate()
    {
        $registeredNumberRangeController = new \RegisteredMail\controllers\RegisteredNumberRangeController();
        $issuingSiteController = new \RegisteredMail\controllers\IssuingSiteController();

        //  CREATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        $body = [
            'siteLabel'          => 'Scranton',
            'postOfficeLabel'    => 'Scranton Post Office',
            'accountNumber'      => 42,
            'addressStreet'      => '1725',
            'addressAdditional1' => null,
            'addressAdditional2' => null,
            'addressPostcode'    => '18505',
            'addressTown'        => 'Scranton',
            'addressCountry'     => 'USA',
            'entities'           => [6]
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $issuingSiteController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertIsInt($responseBody['id']);

        self::$siteId = $responseBody['id'];

        $body = [
            'registeredMailType' => 'B01',
            'trackerNumber'      => 'AZPOKF30KDZP',
            'rangeStart'         => 1,
            'rangeEnd'           => 1000,
            'siteId'             => self::$siteId
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertIsInt($responseBody['id']);

        self::$id = $responseBody['id'];

        //  READ
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = $registeredNumberRangeController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertNotEmpty($responseBody['range']);
        $this->assertSame(self::$id, $responseBody['range']['id']);
        $this->assertSame('B01', $responseBody['range']['registeredMailType']);
        $this->assertSame('AZPOKF30KDZP', $responseBody['range']['trackerNumber']);
        $this->assertSame(1, $responseBody['range']['rangeStart']);
        $this->assertSame(1000, $responseBody['range']['rangeEnd']);
        $this->assertSame(self::$siteId, $responseBody['range']['siteId']);
        $this->assertSame($GLOBALS['id'], $responseBody['range']['creator']);
        $this->assertSame(1, $responseBody['range']['currentNumber']);
        $this->assertSame(0, $responseBody['range']['fullness']);

        // fail
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        $body = [
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body registeredMailType is empty or not a string', $responseBody['errors']);

        $body = [
            'registeredMailType' => 'B01'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body trackerNumber is empty or not a string', $responseBody['errors']);

        $body = [
            'registeredMailType' => 'B01',
            'trackerNumber'      => 'AZPOKF30KDZP',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body rangeStart is empty or not an integer', $responseBody['errors']);

        $body = [
            'registeredMailType' => 'B01',
            'trackerNumber'      => 'AZPOKF30KDZP',
            'rangeStart'         => 1,
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body rangeEnd is empty or not an integer', $responseBody['errors']);

        $body = [
            'registeredMailType' => 'B01',
            'trackerNumber'      => 'AZPOKF30KDZP',
            'rangeStart'         => 1,
            'rangeEnd'           => 1000,
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body siteId is empty or not an integer', $responseBody['errors']);

        $body = [
            'registeredMailType' => 'B01',
            'trackerNumber'      => 'AZPOKF30KDZP',
            'rangeStart'         => 1,
            'rangeEnd'           => 1000,
            'siteId'             => self::$siteId * 1000
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body siteId does not exist', $responseBody['errors']);

        $GLOBALS['login'] = 'bbain';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

        $response = $registeredNumberRangeController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(403, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertSame('Service forbidden', $responseBody['errors']);

        $GLOBALS['login'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testGet()
    {
        $registeredNumberRangeController = new \RegisteredMail\controllers\RegisteredNumberRangeController();

        //  GET
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = $registeredNumberRangeController->get($request, new \Slim\Http\Response());
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($responseBody['ranges']);
        $this->assertNotEmpty($responseBody['ranges']);

        $this->assertIsArray($responseBody['ranges'][0]);
        $this->assertNotEmpty($responseBody['ranges'][0]);

        $this->assertNotEmpty($responseBody['ranges'][0]);
        $this->assertSame(self::$id, $responseBody['ranges'][0]['id']);
        $this->assertSame('B01', $responseBody['ranges'][0]['registeredMailType']);
        $this->assertSame('AZPOKF30KDZP', $responseBody['ranges'][0]['trackerNumber']);
        $this->assertSame(1, $responseBody['ranges'][0]['rangeStart']);
        $this->assertSame(1000, $responseBody['ranges'][0]['rangeEnd']);
        $this->assertSame(self::$siteId, $responseBody['ranges'][0]['siteId']);
        $this->assertSame($GLOBALS['id'], $responseBody['ranges'][0]['creator']);
        $this->assertSame(1, $responseBody['ranges'][0]['currentNumber']);
        $this->assertSame(0, $responseBody['ranges'][0]['fullness']);

        $GLOBALS['login'] = 'bbain';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

        $response = $registeredNumberRangeController->get($request, new \Slim\Http\Response());
        $this->assertSame(403, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Service forbidden', $responseBody['errors']);

        $GLOBALS['login'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testGetById()
    {
        $registeredNumberRangeController = new \RegisteredMail\controllers\RegisteredNumberRangeController();

        //  GET
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = $registeredNumberRangeController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertNotEmpty($responseBody['range']);
        $this->assertSame(self::$id, $responseBody['range']['id']);
        $this->assertSame('B01', $responseBody['range']['registeredMailType']);
        $this->assertSame('AZPOKF30KDZP', $responseBody['range']['trackerNumber']);
        $this->assertSame(1, $responseBody['range']['rangeStart']);
        $this->assertSame(1000, $responseBody['range']['rangeEnd']);
        $this->assertSame(self::$siteId, $responseBody['range']['siteId']);
        $this->assertSame($GLOBALS['id'], $responseBody['range']['creator']);
        $this->assertSame(1, $responseBody['range']['currentNumber']);
        $this->assertSame(0, $responseBody['range']['fullness']);

        $GLOBALS['login'] = 'bbain';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

        $response = $registeredNumberRangeController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(403, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Service forbidden', $responseBody['errors']);

        $GLOBALS['login'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testUpdate()
    {
        $registeredNumberRangeController = new \RegisteredMail\controllers\RegisteredNumberRangeController();

        //  UPDATE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        $body = [
            'registeredMailType' => 'B01',
            'trackerNumber'      => 'AZPOKF30KDZP',
            'rangeStart'         => 1,
            'rangeEnd'           => 2000,
            'siteId'             => self::$siteId
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(204, $response->getStatusCode());

        //  READ
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = $registeredNumberRangeController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertNotEmpty($responseBody['range']);
        $this->assertSame(self::$id, $responseBody['range']['id']);
        $this->assertSame('B01', $responseBody['range']['registeredMailType']);
        $this->assertSame('AZPOKF30KDZP', $responseBody['range']['trackerNumber']);
        $this->assertSame(1, $responseBody['range']['rangeStart']);
        $this->assertSame(2000, $responseBody['range']['rangeEnd']);
        $this->assertSame(self::$siteId, $responseBody['range']['siteId']);
        $this->assertSame($GLOBALS['id'], $responseBody['range']['creator']);
        $this->assertSame(1, $responseBody['range']['currentNumber']);
        $this->assertSame(0, $responseBody['range']['fullness']);

        // fail
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        $body = [
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body registeredMailType is empty or not a string', $responseBody['errors']);

        $body = [
            'registeredMailType' => 'B01'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body trackerNumber is empty or not a string', $responseBody['errors']);

        $body = [
            'registeredMailType' => 'B01',
            'trackerNumber'      => 'AZPOKF30KDZP',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body rangeStart is empty or not an integer', $responseBody['errors']);

        $body = [
            'registeredMailType' => 'B01',
            'trackerNumber'      => 'AZPOKF30KDZP',
            'rangeStart'         => 1,
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body rangeEnd is empty or not an integer', $responseBody['errors']);

        $body = [
            'registeredMailType' => 'B01',
            'trackerNumber'      => 'AZPOKF30KDZP',
            'rangeStart'         => 1,
            'rangeEnd'           => 1000,
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body siteId is empty or not an integer', $responseBody['errors']);

        $body = [
            'registeredMailType' => 'B01',
            'trackerNumber'      => 'AZPOKF30KDZP',
            'rangeStart'         => 1,
            'rangeEnd'           => 1000,
            'siteId'             => self::$siteId * 1000
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body siteId does not exist', $responseBody['errors']);

        $response = $registeredNumberRangeController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id * 1000]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Range not found', $responseBody['errors']);

        $GLOBALS['login'] = 'bbain';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

        $body = [
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(403, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertSame('Service forbidden', $responseBody['errors']);

        $GLOBALS['login'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];
    }

    public function testDelete()
    {
        $registeredNumberRangeController = new \RegisteredMail\controllers\RegisteredNumberRangeController();
        $issuingSiteController = new \RegisteredMail\controllers\IssuingSiteController();

        //  DELETE
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        $response = $registeredNumberRangeController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(204, $response->getStatusCode());

        $response = $registeredNumberRangeController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(204, $response->getStatusCode());

        //  READ
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = $registeredNumberRangeController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertSame('Range not found', $responseBody['errors']);

        // Fail
        $GLOBALS['login'] = 'bbain';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

        $body = [
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($body, $request);

        $response = $registeredNumberRangeController->delete($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(403, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertSame('Service forbidden', $responseBody['errors']);

        $GLOBALS['login'] = 'superadmin';
        $userInfo = \User\models\UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        $GLOBALS['id'] = $userInfo['id'];

        $response = $issuingSiteController->delete($request, new \Slim\Http\Response(), ['id' => self::$siteId]);
        $this->assertSame(204, $response->getStatusCode());
    }
}
