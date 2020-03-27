<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class ShippingTemplateControllerTest extends TestCase
{
    private static $id = null;

    public function testCreate()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $shipping    = new \Shipping\controllers\ShippingTemplateController();

        $aArgs = [
            'label'           => 'TEST',
            'description'     => 'description du TEST',
            'options'         => [
                'shaping'    => ['color', 'duplexPrinting', 'addressPage'],
                'sendMode'   => 'fast'
            ],
            'fee'             => ['firstPagePrice' => 1, 'nextPagePrice' => 2, 'postagePrice' => 12],
            'entities'        => [1, 2],
            'account'         => ['id' => 'toto', 'password' => '1234']
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $shipping->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertIsInt($responseBody->shippingId);
        self::$id = $responseBody->shippingId;

        ####### FAIL ##########
        $aArgs = [
            'description'     => 'description du TEST',
            'options'         => [
                'shaping'     => ['color', 'duplexPrinting', 'addressPage'],
                'sendMode'    => 'fast'
            ],
            'fee'             => ['firstPagePrice' => 1, 'nextPagePrice' => 2, 'postagePrice' => 12],
            'account'         => ['id' => 'toto', 'password' => '']
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $shipping->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('account id or password is empty', $responseBody->errors[0]);
        $this->assertSame('label is empty or too long', $responseBody->errors[1]);
    }

    public function testGetById()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $shipping    = new \Shipping\controllers\ShippingTemplateController();

        $response  = $shipping->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody);
        $this->assertSame('TEST', $responseBody->shipping->label);
        $this->assertSame('description du TEST', $responseBody->shipping->description);
        $this->assertSame('color', $responseBody->shipping->options->shaping[0]);
        $this->assertSame('duplexPrinting', $responseBody->shipping->options->shaping[1]);
        $this->assertSame('addressPage', $responseBody->shipping->options->shaping[2]);
        $this->assertSame('fast', $responseBody->shipping->options->sendMode);
        $this->assertSame(1, $responseBody->shipping->fee->firstPagePrice);
        $this->assertSame(2, $responseBody->shipping->fee->nextPagePrice);
        $this->assertSame(12, $responseBody->shipping->fee->postagePrice);
        $this->assertNotNull($responseBody->shipping->entities);
        $this->assertNotNull($responseBody->entities);

        ######## ERROR #############
        $response  = $shipping->getById($request, new \Slim\Http\Response(), ['id' => 999999999]);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Shipping does not exist', $responseBody->errors);
    }

    public function testGetList()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $shipping    = new \Shipping\controllers\ShippingTemplateController();

        $response  = $shipping->get($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotNull($responseBody->shippings);

        foreach ($responseBody->shippings as $value) {
            $this->assertIsInt($value->id);
        }
    }

    public function testUpdate()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'label'           => 'TEST 2',
            'description'     => 'description du test 2',
            'options'         => [
                'shaping'    => ['color', 'address_page'],
                'sendMode'   => 'fast'
            ],
            'fee'             => ['firstPagePrice' => 10, 'nextPagePrice' => 20, 'postagePrice' => 12],
            'account'         => ['id' => 'toto', 'password' => '1234']
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $shipping    = new \Shipping\controllers\ShippingTemplateController();
        $response = $shipping->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);

        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $shipping    = new \Shipping\controllers\ShippingTemplateController();

        $response  = $shipping->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody);
        $this->assertSame('TEST 2', $responseBody->shipping->label);
        $this->assertSame('description du test 2', $responseBody->shipping->description);
        $this->assertSame('color', $responseBody->shipping->options->shaping[0]);
        $this->assertSame('address_page', $responseBody->shipping->options->shaping[1]);
        $this->assertSame('fast', $responseBody->shipping->options->sendMode);
        $this->assertSame(10, $responseBody->shipping->fee->firstPagePrice);
        $this->assertSame(20, $responseBody->shipping->fee->nextPagePrice);
        $this->assertSame(12, $responseBody->shipping->fee->postagePrice);
        $this->assertNotNull($responseBody->entities);
    }

    public function testDelete()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $shipping    = new \Shipping\controllers\ShippingTemplateController();

        $response = $shipping->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertIsArray($responseBody->shippings);

        ##### FAIL ######
        $response = $shipping->delete($request, new \Slim\Http\Response(), ['id' => 'myid']);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('id is not an integer', $responseBody->errors);
    }

    public function testInitShipping()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $shipping    = new \Shipping\controllers\ShippingTemplateController();

        $response  = $shipping->initShipping($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotNull($responseBody->entities);

        foreach ($responseBody->entities as $value) {
            $this->assertNotNull($value->entity_id);
            $this->assertNotNull($value->entity_label);
        }
    }
}
