<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class CustomFieldControllerTest extends TestCase
{
    private static $id = null;

    public function testCreate()
    {
        $customFieldController = new \CustomField\controllers\CustomFieldController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $args = [
            'label'     => 'mon custom',
            'type'      => 'select',
            'values'    => ['one', 'two']
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);

        $response     = $customFieldController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(204, $response->getStatusCode());

        $field = \CustomField\models\CustomFieldModel::get([
            'select' => ['id'], 'where' => ['label = ?'], 'data' => ['mon custom'], 'limit' => 1, 'orderBy' => ['id DESC']
        ]);

        self::$id = $field[0]['id'];

        $field = \CustomField\models\CustomFieldModel::getById(['id' => self::$id]);
        $this->assertSame('mon custom', $field['label']);
        $this->assertSame('select', $field['type']);


        //  Errors
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $args = [
            'label'     => 'mon custom',
            'type'      => 'select',
            'values'    => ['one', 'two']
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);

        $response     = $customFieldController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Custom field with this label already exists', $responseBody->errors);
    }

    public function testUpdate()
    {
        $customFieldController = new \CustomField\controllers\CustomFieldController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $args = [
            'label'     => 'mon custom22',
            'values'    => ['one', 'two', 'trois']
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);

        $response     = $customFieldController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(204, $response->getStatusCode());

        //  Errors
        unset($args['label']);
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);

        $response     = $customFieldController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Body label is empty or not a string', $responseBody->errors);
    }

    public function testDelete()
    {
        $customFieldController = new \CustomField\controllers\CustomFieldController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);


        $response     = $customFieldController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $this->assertSame(204, $response->getStatusCode());
    }
}
