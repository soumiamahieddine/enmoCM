<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class ListTemplateControllerTest extends TestCase
{
    private static $id = null;

    public function testCreate()
    {
        $listTemplateController = new \Entity\controllers\ListTemplateController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'type'              => 'visaCircuit',
            'title'             => 'TEST-LISTTEMPLATE123-TITLE',
            'description'       => 'TEST LISTTEMPLATE123 DESCRIPTION',
            'items'             => [
                [
                    'id'   => 5,
                    'type' => 'user',
                    'mode' => 'visa'
                ],
                [
                    'id'   => 10,
                    'type' => 'user',
                    'mode' => 'visa'
                ],
                [
                    'id'   => 17,
                    'type' => 'user',
                    'mode' => 'sign'
                ]
            ],
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $aArgs = [
            'admin'  => true
        ];
        $fullRequest = $fullRequest->withQueryParams($aArgs);

        $response     = $listTemplateController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertIsInt($responseBody->id);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $listTemplateController->get($request, new \Slim\Http\Response());
        $responseBody   = json_decode((string)$response->getBody());

        foreach ($responseBody->listTemplates as $listTemplate) {
            if ($listTemplate->title == 'TEST-LISTTEMPLATE123-TITLE') {
                self::$id = $listTemplate->id;
                $this->assertSame('visaCircuit', $listTemplate->type);
                $this->assertSame('TEST-LISTTEMPLATE123-TITLE', $listTemplate->title);
                $this->assertSame('TEST LISTTEMPLATE123 DESCRIPTION', $listTemplate->description);
            }
        }

        $this->assertNotEmpty(self::$id);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $listTemplateController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('TEST-LISTTEMPLATE123-TITLE', $responseBody->listTemplate->title);
        $this->assertSame('TEST LISTTEMPLATE123 DESCRIPTION', $responseBody->listTemplate->description);
        $this->assertSame('visaCircuit', $responseBody->listTemplate->type);

        $this->assertSame(0, $responseBody->listTemplate->items[0]->sequence);
        $this->assertSame(5, $responseBody->listTemplate->items[0]->item_id);
        $this->assertSame('user', $responseBody->listTemplate->items[0]->item_type);
        $this->assertSame('visa', $responseBody->listTemplate->items[0]->item_mode);

        $this->assertSame(1, $responseBody->listTemplate->items[1]->sequence);
        $this->assertSame(10, $responseBody->listTemplate->items[1]->item_id);
        $this->assertSame('user', $responseBody->listTemplate->items[1]->item_type);
        $this->assertSame('visa', $responseBody->listTemplate->items[1]->item_mode);

        $this->assertSame(2, $responseBody->listTemplate->items[2]->sequence);
        $this->assertSame(17, $responseBody->listTemplate->items[2]->item_id);
        $this->assertSame('user', $responseBody->listTemplate->items[2]->item_type);
        $this->assertSame('sign', $responseBody->listTemplate->items[2]->item_mode);
    }

    public function testUpdate()
    {
        $listTemplateController = new \Entity\controllers\ListTemplateController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'title'             => 'TEST-LISTTEMPLATE123-TITLE-UPDATED',
            'description'       => 'TEST LISTTEMPLATE123 DESCRIPTION UPDATED',
            'items'             => [
                [
                    'id'   => 10,
                    'type' => 'user',
                    'mode' => 'visa'
                ],
                [
                    'id'   => 17,
                    'type' => 'user',
                    'mode' => 'sign'
                ]
            ],
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $listTemplateController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(204, $response->getStatusCode());

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $listTemplateController->get($request, new \Slim\Http\Response());
        $responseBody   = json_decode((string)$response->getBody());

        self::$id = null;
        foreach ($responseBody->listTemplates as $listTemplate) {
            if ($listTemplate->title == 'TEST-LISTTEMPLATE123-TITLE-UPDATED') {
                self::$id = $listTemplate->id;
                $this->assertSame('visaCircuit', $listTemplate->type);
                $this->assertSame('TEST-LISTTEMPLATE123-TITLE-UPDATED', $listTemplate->title);
                $this->assertSame('TEST LISTTEMPLATE123 DESCRIPTION UPDATED', $listTemplate->description);
            }
        }
        $this->assertNotEmpty(self::$id);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $listTemplateController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('TEST-LISTTEMPLATE123-TITLE-UPDATED', $responseBody->listTemplate->title);
        $this->assertSame('TEST LISTTEMPLATE123 DESCRIPTION UPDATED', $responseBody->listTemplate->description);
        $this->assertSame('visaCircuit', $responseBody->listTemplate->type);

        $this->assertSame(0, $responseBody->listTemplate->items[0]->sequence);
        $this->assertSame(10, $responseBody->listTemplate->items[0]->item_id);
        $this->assertSame('user', $responseBody->listTemplate->items[0]->item_type);
        $this->assertSame('visa', $responseBody->listTemplate->items[0]->item_mode);

        $this->assertSame(1, $responseBody->listTemplate->items[1]->sequence);
        $this->assertSame(17, $responseBody->listTemplate->items[1]->item_id);
        $this->assertSame('user', $responseBody->listTemplate->items[1]->item_type);
        $this->assertSame('sign', $responseBody->listTemplate->items[1]->item_mode);

        $this->assertSame(null, $responseBody->listTemplate->items[2]);
    }

    public function testDelete()
    {
        $listTemplateController = new \Entity\controllers\ListTemplateController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $listTemplateController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame(204, $response->getStatusCode());

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $listTemplateController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('List template not found', $responseBody->errors);
    }

    public function testGetTypesRoles()
    {
        $listTemplateController = new \Entity\controllers\ListTemplateController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $listTemplateController->getTypeRoles($request, new \Slim\Http\Response(), ['typeId' => 'entity_id']);
        $responseBody   = json_decode((string)$response->getBody());

        foreach ($responseBody->roles as $value) {
            $this->assertNotEmpty($value->id);
            $this->assertNotEmpty($value->label);
            $this->assertIsBool($value->available);
        }
    }
}
