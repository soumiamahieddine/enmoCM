<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class ContactControllerTest extends TestCase
{
    private static $id = null;

    public function testCreate()
    {
        $contactController = new \Contact\controllers\ContactController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $args = [
            'civility'          => 'title1',
            'firstname'         => 'Hal',
            'lastname'          => 'Jordan',
            'company'           => 'Green Lantern Corps',
            'department'        => 'Sector 2814',
            'function'          => 'member',
            'addressNumber'     => '1',
            'addressStreet'     => 'somewhere',
            'addressPostcode'   => '99000',
            'addressTown'       => 'Bluehaven',
            'addressCountry'    => 'USA',
            'email'             => 'hal.jordan@glc.com',
            'phone'             => '911',
            'notes'             => 'In brightest day',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);

        $response     = $contactController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertIsInt($responseBody['id']);
        self::$id = $responseBody['id'];


        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response = $contactController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertSame(self::$id, $responseBody['id']);
        $this->assertSame($args['civility'], $responseBody['civility']['id']);
        $this->assertSame($args['firstname'], $responseBody['firstname']);
        $this->assertSame($args['lastname'], $responseBody['lastname']);
        $this->assertSame($args['company'], $responseBody['company']);
        $this->assertSame($args['department'], $responseBody['department']);
        $this->assertSame($args['function'], $responseBody['function']);
        $this->assertSame($args['addressNumber'], $responseBody['addressNumber']);
        $this->assertSame($args['addressStreet'], $responseBody['addressStreet']);
        $this->assertSame($args['addressPostcode'], $responseBody['addressPostcode']);
        $this->assertSame($args['addressTown'], $responseBody['addressTown']);
        $this->assertSame($args['addressCountry'], $responseBody['addressCountry']);
        $this->assertSame($args['email'], $responseBody['email']);
        $this->assertSame($args['phone'], $responseBody['phone']);
        $this->assertSame($args['notes'], $responseBody['notes']);
        $this->assertSame(true, $responseBody['enabled']);
        $this->assertSame($GLOBALS['id'], $responseBody['creator']);
        $this->assertNotNull($responseBody['creatorLabel']);
        $this->assertNotNull($responseBody['creationDate']);
        $this->assertNull($responseBody['modificationDate']);


        //  ERRORS
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $args = [
            'civility'          => 'title1',
            'firstname'         => 'Hal',
            'department'        => 'Sector 2814',
            'function'          => 'member',
            'addressNumber'     => '1',
            'addressStreet'     => 'somewhere',
            'addressPostcode'   => '99000',
            'addressTown'       => 'Bluehaven',
            'addressCountry'    => 'USA',
            'email'             => 'hal.jordan@glc.com',
            'phone'             => '911',
            'notes'             => 'In brightest day',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);

        $response     = $contactController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertSame('Body lastname or company is mandatory', $responseBody['errors']);
    }

    public function testUpdate()
    {
        $contactController = new \Contact\controllers\ContactController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $args = [
            'civility'          => 'title1',
            'lastname'          => 'Sinestro',
            'company'           => 'Yellow Lantern Corps',
            'department'        => 'Sector 2813',
            'function'          => 'Head',
            'addressNumber'     => '666',
            'addressStreet'     => 'anywhere',
            'addressPostcode'   => '98000',
            'addressTown'       => 'Redhaven',
            'addressCountry'    => 'U.S.A',
            'email'             => 'sinestro@ylc.com',
            'phone'             => '919',
            'notes'             => 'In blackest day',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);

        $response = $contactController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(204, $response->getStatusCode());


        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response = $contactController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertSame(self::$id, $responseBody['id']);
        $this->assertSame($args['civility'], $responseBody['civility']['id']);
        $this->assertNull($responseBody['firstname']);
        $this->assertSame($args['lastname'], $responseBody['lastname']);
        $this->assertSame($args['company'], $responseBody['company']);
        $this->assertSame($args['department'], $responseBody['department']);
        $this->assertSame($args['function'], $responseBody['function']);
        $this->assertSame($args['addressNumber'], $responseBody['addressNumber']);
        $this->assertSame($args['addressStreet'], $responseBody['addressStreet']);
        $this->assertSame($args['addressPostcode'], $responseBody['addressPostcode']);
        $this->assertSame($args['addressTown'], $responseBody['addressTown']);
        $this->assertSame($args['addressCountry'], $responseBody['addressCountry']);
        $this->assertSame($args['email'], $responseBody['email']);
        $this->assertSame($args['phone'], $responseBody['phone']);
        $this->assertSame($args['notes'], $responseBody['notes']);
        $this->assertSame(true, $responseBody['enabled']);
        $this->assertSame($GLOBALS['id'], $responseBody['creator']);
        $this->assertNotNull($responseBody['creatorLabel']);
        $this->assertNotNull($responseBody['creationDate']);
        $this->assertNotNull($responseBody['modificationDate']);


        //  ERRORS
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $args = [
            'civility'          => 'title1',
            'firstname'         => 'Hal',
            'department'        => 'Sector 2814',
            'function'          => 'member',
            'addressNumber'     => '1',
            'addressStreet'     => 'somewhere',
            'addressPostcode'   => '99000',
            'addressTown'       => 'Bluehaven',
            'addressCountry'    => 'USA',
            'email'             => 'hal.jordan@glc.com',
            'phone'             => '911',
            'notes'             => 'In brightest day',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);

        $response = $contactController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertSame('Body lastname or company is mandatory', $responseBody['errors']);
    }

    public function testGet()
    {
        $contactController = new \Contact\controllers\ContactController();

        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response = $contactController->get($request, new \Slim\Http\Response());
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertNotNull($responseBody['contacts'][0]['id']);
        $this->assertNotNull($responseBody['contacts'][0]['lastname']);
        $this->assertNotNull($responseBody['contacts'][0]['company']);
    }

    public function testDelete()
    {
        $contactController = new \Contact\controllers\ContactController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response = $contactController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(204, $response->getStatusCode());


        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response = $contactController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertSame('Contact does not exist', $responseBody['errors']);


        //  ERRORS
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response = $contactController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertSame('Contact does not exist', $responseBody['errors']);
    }


    public function testControlLengthNameAfnor()
    {
        $name = \Contact\controllers\ContactController::controlLengthNameAfnor(['civility' => 'title1', 'fullName' => 'Prénom NOM', 'strMaxLength' => 38]);

        $this->assertSame('Monsieur Prénom NOM', $name);

        $name = \Contact\controllers\ContactController::controlLengthNameAfnor(['civility' => 'title3', 'fullName' => 'Prénom NOM TROP LOOOOOOOOOOOOONG', 'strMaxLength' => 38]);

        $this->assertSame('Mlle Prénom NOM TROP LOOOOOOOOOOOOONG', $name);
    }

    public function testGetAvailableDepartments()
    {
        $contactController = new \Contact\controllers\ContactController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response = $contactController->getAvailableDepartments($request, new \Slim\Http\Response());
        $responseBody      = json_decode((string)$response->getBody(), true);

        $this->assertIsArray($responseBody['departments']);
        $this->assertNotEmpty($responseBody['departments']);
    }

    public function testGetContactsParameters()
    {
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $contactController = new \Contact\controllers\ContactController();
        $response          = $contactController->getContactsParameters($request, new \Slim\Http\Response());
        $responseBody      = json_decode((string)$response->getBody());

        $this->assertIsArray((array)$responseBody->contactsFilling);
    }

//    public function testUpdateFilling()
//    {
//        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
//        $request        = \Slim\Http\Request::createFromEnvironment($environment);
//
//        $aArgs = [
//            "enable"            => true,
//            "rating_columns"    => ["society", "function"],
//            "first_threshold"   => 22,
//            "second_threshold"  => 85
//        ];
//        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
//
//        $contactController = new \Contact\controllers\ContactController();
//        $response          = $contactController->updateFilling($fullRequest, new \Slim\Http\Response());
//        $responseBody      = json_decode((string)$response->getBody());
//
//        $this->assertSame('success', $responseBody->success);
//
//        $response          = $contactController->getFilling($request, new \Slim\Http\Response());
//        $responseBody      = json_decode((string)$response->getBody());
//
//        $this->assertSame(true, $responseBody->contactsFilling->enable);
//        $this->assertSame(22, $responseBody->contactsFilling->first_threshold);
//        $this->assertSame(85, $responseBody->contactsFilling->second_threshold);
//        $this->assertSame('society', $responseBody->contactsFilling->rating_columns[0]);
//        $this->assertSame('function', $responseBody->contactsFilling->rating_columns[1]);
//
//        $aArgs = [
//            "enable"            => true,
//            "first_threshold"   => 22,
//            "second_threshold"  => 85
//        ];
//        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
//
//        $response          = $contactController->updateFilling($fullRequest, new \Slim\Http\Response());
//        $responseBody      = json_decode((string)$response->getBody());
//
//        $this->assertSame('Bad Request', $responseBody->errors);
//    }
}
