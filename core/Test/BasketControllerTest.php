<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class BasketControllerTest extends TestCase
{
    private static $id = null;

    public function testCreate()
    {
        $basketController = new \Basket\controllers\BasketController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'id'                => 'TEST-BASKET123',
            'name'              => 'TEST-BASKET123-NAME',
            'description'       => 'TEST BASKET123 DESCRIPTION',
            'clause'            => '1=2',
            'isSearchBasket'    => true,
            'isFolderBasket'    => true,
            'color'             => '#123456'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $basketController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('TEST-BASKET123', $responseBody->basket);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $basketController->getById($request, new \Slim\Http\Response(), ['id' => 'TEST-BASKET123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('TEST-BASKET123', $responseBody->basket->basket_id);
        $this->assertSame('TEST-BASKET123-NAME', $responseBody->basket->basket_name);
        $this->assertSame('TEST BASKET123 DESCRIPTION', $responseBody->basket->basket_desc);
        $this->assertSame('1=2', $responseBody->basket->basket_clause);
        $this->assertSame('N', $responseBody->basket->is_visible);
        $this->assertSame('Y', $responseBody->basket->is_folder_basket);
        $this->assertSame('N', $responseBody->basket->flag_notif);
        $this->assertSame('#123456', $responseBody->basket->color);
    }

    public function testUpdate()
    {
        $basketController = new \Basket\controllers\BasketController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'name'              => 'TEST-BASKET123-UPDATED',
            'description'       => 'TEST BASKET123 DESCRIPTION UPDATED',
            'clause'            => '1=3',
            'isSearchBasket'    => false,
            'isFolderBasket'    => false,
            'flagNotif'         => true,
            'color'             => '#111222'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $basketController->update($fullRequest, new \Slim\Http\Response(), ['id' => 'TEST-BASKET123']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $basketController->getById($request, new \Slim\Http\Response(), ['id' => 'TEST-BASKET123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('TEST-BASKET123', $responseBody->basket->basket_id);
        $this->assertSame('TEST-BASKET123-UPDATED', $responseBody->basket->basket_name);
        $this->assertSame('TEST BASKET123 DESCRIPTION UPDATED', $responseBody->basket->basket_desc);
        $this->assertSame('1=3', $responseBody->basket->basket_clause);
        $this->assertSame('Y', $responseBody->basket->is_visible);
        $this->assertSame('N', $responseBody->basket->is_folder_basket);
        $this->assertSame('Y', $responseBody->basket->flag_notif);
        $this->assertSame('#111222', $responseBody->basket->color);
    }

    public function testCreateGroup()
    {
        $basketController = new \Basket\controllers\BasketController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'group_id'      => 'AGENT',
            'result_page'   => 'redirect_to_action',
            'groupActions'  => [
                [
                    'id_action'             => '112',
                    'where_clause'          => '1=2',
                    'used_in_basketlist'    => false,
                    'used_in_action_page'   => false,
                    'default_action_list'   => true,
                    'statuses'              => [
                        'NEW',
                        'END'
                    ],
                    'redirects'             => [
                        [
                            'entity_id'     => '',
                            'keyword'       => 'MY_ENTITIES',
                            'redirect_mode' => 'ENTITY'
                        ]
                    ]
                ]
            ]
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $basketController->createGroup($fullRequest, new \Slim\Http\Response(), ['id' => 'TEST-BASKET123']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $basketController->getGroups($request, new \Slim\Http\Response(), ['id' => 'TEST-BASKET123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('AGENT', $responseBody->groups[0]->group_id);
        $this->assertSame('TEST-BASKET123', $responseBody->groups[0]->basket_id);
        $this->assertSame('redirect_to_action', $responseBody->groups[0]->result_page);
        $this->assertInternalType('array', $responseBody->groups[0]->groupActions);
        $this->assertNotNull($responseBody->groups[0]->groupActions);
        $this->assertSame(112, $responseBody->groups[0]->groupActions[0]->id_action);
        $this->assertSame('1=2', $responseBody->groups[0]->groupActions[0]->where_clause);
        $this->assertSame('N', $responseBody->groups[0]->groupActions[0]->used_in_basketlist);
        $this->assertSame('N', $responseBody->groups[0]->groupActions[0]->used_in_action_page);
        $this->assertSame('Y', $responseBody->groups[0]->groupActions[0]->default_action_list);
        $this->assertInternalType('array', $responseBody->groups[0]->groupActions[0]->statuses);
        $this->assertNotNull($responseBody->groups[0]->groupActions[0]->statuses);
        $this->assertSame('NEW', $responseBody->groups[0]->groupActions[0]->statuses[0]);
        $this->assertSame('END', $responseBody->groups[0]->groupActions[0]->statuses[1]);
        $this->assertInternalType('array', $responseBody->groups[0]->groupActions[0]->redirects);
        $this->assertNotNull($responseBody->groups[0]->groupActions[0]->redirects);
        $this->assertSame('', $responseBody->groups[0]->groupActions[0]->redirects[0]->entity_id);
        $this->assertSame('MY_ENTITIES', $responseBody->groups[0]->groupActions[0]->redirects[0]->keyword);
        $this->assertSame('ENTITY', $responseBody->groups[0]->groupActions[0]->redirects[0]->redirect_mode);
    }

    public function testUpdateGroup()
    {
        $basketController = new \Basket\controllers\BasketController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'result_page'   => 'list_with_attachments',
            'groupActions'  => [
                [
                    'id_action'             => '1',
                    'where_clause'          => '1=1',
                    'used_in_basketlist'    => true,
                    'used_in_action_page'   => true,
                    'default_action_list'   => true,
                    'statuses'              => [
                        'END',
                    ],
                    'redirects'             => [
                        [
                            'entity_id'     => '',
                            'keyword'       => 'ALL_ENTITIES',
                            'redirect_mode' => 'ENTITY'
                        ]
                    ]
                ],
                [
                    'id_action'             => '4',
                    'where_clause'          => '1=4',
                    'used_in_basketlist'    => false,
                    'used_in_action_page'   => true,
                    'default_action_list'   => false,
                    'statuses'              => [
                        'NEW',
                        'COU'
                    ],
                    'redirects'             => [
                        [
                            'entity_id'     => 'PSO',
                            'keyword'       => '',
                            'redirect_mode' => 'ENTITY'
                        ],
                        [
                            'entity_id'     => 'PSF',
                            'keyword'       => '',
                            'redirect_mode' => 'USERS'
                        ]
                    ]
                ]
            ]
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $basketController->updateGroup($fullRequest, new \Slim\Http\Response(), ['id' => 'TEST-BASKET123', 'groupId' => 'AGENT']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $basketController->getGroups($request, new \Slim\Http\Response(), ['id' => 'TEST-BASKET123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('AGENT', $responseBody->groups[0]->group_id);
        $this->assertSame('TEST-BASKET123', $responseBody->groups[0]->basket_id);
        $this->assertSame('list_with_attachments', $responseBody->groups[0]->result_page);
        $this->assertInternalType('array', $responseBody->groups[0]->groupActions);
        $this->assertNotNull($responseBody->groups[0]->groupActions);

        $this->assertSame(1, $responseBody->groups[0]->groupActions[0]->id_action);
        $this->assertSame('1=1', $responseBody->groups[0]->groupActions[0]->where_clause);
        $this->assertSame('Y', $responseBody->groups[0]->groupActions[0]->used_in_basketlist);
        $this->assertSame('Y', $responseBody->groups[0]->groupActions[0]->used_in_action_page);
        $this->assertSame('Y', $responseBody->groups[0]->groupActions[0]->default_action_list);
        $this->assertInternalType('array', $responseBody->groups[0]->groupActions[0]->statuses);
        $this->assertNotNull($responseBody->groups[0]->groupActions[0]->statuses);
        $this->assertSame('END', $responseBody->groups[0]->groupActions[0]->statuses[0]);
        $this->assertInternalType('array', $responseBody->groups[0]->groupActions[0]->redirects);
        $this->assertNotNull($responseBody->groups[0]->groupActions[0]->redirects);
        $this->assertSame('', $responseBody->groups[0]->groupActions[0]->redirects[0]->entity_id);
        $this->assertSame('ALL_ENTITIES', $responseBody->groups[0]->groupActions[0]->redirects[0]->keyword);
        $this->assertSame('ENTITY', $responseBody->groups[0]->groupActions[0]->redirects[0]->redirect_mode);

        $this->assertSame(4, $responseBody->groups[0]->groupActions[1]->id_action);
        $this->assertSame('1=4', $responseBody->groups[0]->groupActions[1]->where_clause);
        $this->assertSame('N', $responseBody->groups[0]->groupActions[1]->used_in_basketlist);
        $this->assertSame('Y', $responseBody->groups[0]->groupActions[1]->used_in_action_page);
        $this->assertSame('N', $responseBody->groups[0]->groupActions[1]->default_action_list);
        $this->assertInternalType('array', $responseBody->groups[0]->groupActions[1]->statuses);
        $this->assertNotNull($responseBody->groups[0]->groupActions[1]->statuses);
        $this->assertSame('NEW', $responseBody->groups[0]->groupActions[1]->statuses[0]);
        $this->assertSame('COU', $responseBody->groups[0]->groupActions[1]->statuses[1]);
        $this->assertInternalType('array', $responseBody->groups[0]->groupActions[1]->redirects);
        $this->assertNotNull($responseBody->groups[0]->groupActions[1]->redirects);
        $this->assertSame('PSO', $responseBody->groups[0]->groupActions[1]->redirects[0]->entity_id);
        $this->assertSame('', $responseBody->groups[0]->groupActions[1]->redirects[0]->keyword);
        $this->assertSame('ENTITY', $responseBody->groups[0]->groupActions[1]->redirects[0]->redirect_mode);
        $this->assertSame('PSF', $responseBody->groups[0]->groupActions[1]->redirects[1]->entity_id);
        $this->assertSame('', $responseBody->groups[0]->groupActions[1]->redirects[1]->keyword);
        $this->assertSame('USERS', $responseBody->groups[0]->groupActions[1]->redirects[1]->redirect_mode);
    }

    public function testDeleteGroup()
    {
        $basketController = new \Basket\controllers\BasketController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $basketController->deleteGroup($request, new \Slim\Http\Response(), ['id' => 'TEST-BASKET123', 'groupId' => 'AGENT']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $basketController->getGroups($request, new \Slim\Http\Response(), ['id' => 'TEST-BASKET123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertEmpty($responseBody->groups);
    }

    public function testGetDataForGroup()
    {
        $basketController = new \Basket\controllers\BasketController();

        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $basketController->getDataForGroupById($request, new \Slim\Http\Response(), ['id' => 'TEST-BASKET123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->groups);
        $this->assertNotNull($responseBody->groups);
        $this->assertInternalType('array', $responseBody->pages);
        $this->assertNotNull($responseBody->pages);
        $this->assertInternalType('array', $responseBody->actions);
        $this->assertNotNull($responseBody->actions);
    }

    public function testGet()
    {
        $basketController = new \Basket\controllers\BasketController();

        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $basketController->get($request, new \Slim\Http\Response());
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->baskets);
        $this->assertNotNull($responseBody->baskets);
    }

    public function testDelete()
    {
        $basketController = new \Basket\controllers\BasketController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $basketController->delete($request, new \Slim\Http\Response(), ['id' => 'TEST-BASKET123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->baskets);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $basketController->getById($request, new \Slim\Http\Response(), ['id' => 'TEST-BASKET123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('Basket not found', $responseBody->errors);
    }

    public function testGetSorted()
    {
        $basketController = new \Basket\controllers\BasketController();

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $basketController->getSorted($request, new \Slim\Http\Response());
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->baskets);

        self::$id = $responseBody->baskets[0]->basket_id;
    }

    public function testUpdateSort()
    {
        $basketController = new \Basket\controllers\BasketController();

        //  PUT
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'method'                => 'DOWN',
            'power'                 => 'ALL'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response       = $basketController->updateSort($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->baskets);
        $this->assertSame(self::$id, $responseBody->baskets[count($responseBody->baskets) - 1]->basket_id);

        $aArgs = [
            'method'                => 'UP',
            'power'                 => 'ONE'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response       = $basketController->updateSort($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->baskets);
        $this->assertSame(self::$id, $responseBody->baskets[count($responseBody->baskets) - 2]->basket_id);

        $aArgs = [
            'method'                => 'DOWN',
            'power'                 => 'ONE'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response       = $basketController->updateSort($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->baskets);
        $this->assertSame(self::$id, $responseBody->baskets[count($responseBody->baskets) - 1]->basket_id);

        $aArgs = [
            'method'                => 'UP',
            'power'                 => 'ALL'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response       = $basketController->updateSort($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->baskets);
        $this->assertSame(self::$id, $responseBody->baskets[0]->basket_id);

        //  Errors
        $response       = $basketController->updateSort($fullRequest, new \Slim\Http\Response(), ['id' => 'ABasketWichDoesNotExist']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('string', $responseBody->errors);
        $this->assertSame('Basket not found', $responseBody->errors);

        $response       = $basketController->updateSort($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('string', $responseBody->errors);
        $this->assertSame('Basket is already sorted', $responseBody->errors);

        $aArgs = [
            'method'                => 'UPOUSE',
            'power'                 => 'ALL'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response       = $basketController->updateSort($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('string', $responseBody->errors);
        $this->assertSame('Bad Request', $responseBody->errors);
    }

}
