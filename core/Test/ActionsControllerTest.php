<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ActionsControllerTest
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace MaarchTest;
use PHPUnit\Framework\TestCase;
//require_once __DIR__.'/define.php';

class ActionsControllerTest extends TestCase
{
    public function testCRUD()
    {
        $actionController = new \Core\Controllers\ActionsController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'keyword'    => 'indexing',
            'label_action' => 'TEST-LABEL',
            'id_status'  => '_NOSTATUS_',
            'is_system'  => 'N',
            'is_folder_action'  => 'N',
            'enabled'  => 'Y',
            'action_page'  => 'index_mlb',
            'history'  => 'Y',
            'origin'  => 'apps',
            'create_id'  => 'N'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $actionController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $id = $responseBody->action->id;
        $this->assertInternalType('int', $id);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $actionController->getByIdForAdministration($request, new \Slim\Http\Response(), ['id' => $id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame($id, $responseBody->id);
        $this->assertSame('indexing', $responseBody->keyword);
        $this->assertSame('TEST-LABEL', $responseBody->label_action);
        $this->assertSame('_NOSTATUS_', $responseBody->id_status);
        $this->assertSame('N', $responseBody->is_system);
        $this->assertSame('N', $responseBody->is_folder_action);
        $this->assertSame('Y', $responseBody->enabled);
        $this->assertSame('index_mlb', $responseBody->action_page);
        $this->assertSame('Y', $responseBody->history);
        $this->assertSame('apps', $responseBody->origin);
        $this->assertSame('N', $responseBody->create_id);

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'keyword'    => '',
            'label_action' => 'TEST-LABEL_UPDATED',
            'id_status'  => 'COU',
            'is_system'  => 'Y',
            'is_folder_action'  => 'Y',
            'enabled'  => 'N',
            'action_page'  => 'process',
            'history'  => 'N',
            'origin'  => 'apps',
            'create_id'  => 'Y'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $actionController->update($fullRequest, new \Slim\Http\Response(), ['id' => $id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(_ACTION_UPDATED, $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $actionController->getByIdForAdministration($request, new \Slim\Http\Response(), ['id' => $id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame($id, $responseBody->id);
        $this->assertSame('', $responseBody->keyword);
        $this->assertSame('TEST-LABEL_UPDATED', $responseBody->label_action);
        $this->assertSame('COU', $responseBody->id_status);
        $this->assertSame('Y', $responseBody->is_system);
        $this->assertSame('Y', $responseBody->is_folder_action);
        $this->assertSame('N', $responseBody->enabled);
        $this->assertSame('process', $responseBody->action_page);
        $this->assertSame('N', $responseBody->history);
        $this->assertSame('apps', $responseBody->origin);
        $this->assertSame('Y', $responseBody->create_id);

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $actionController->delete($request, new \Slim\Http\Response(), ['id' => $id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(_ACTION_DELETED, $responseBody->success);

    }
    /*public function testGetList()
    {
        $action = new \Core\Controllers\ActionsController();

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
            ]
        );

        $client = new \GuzzleHttp\Client([
                'base_uri' => '127.0.0.1/MaarchCourrier/rest/actions',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('GET', '', [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);

        $this->assertNotNull((string)$response->getBody());
    }

    public function testGetById()
    {
        $action = new \Core\Controllers\ActionsController();

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
            ]
        );

        $aArgs = [
            'id'=>'1'
        ];

       $client = new \GuzzleHttp\Client([
                'base_uri' => '127.0.0.1/MaarchCourrier/rest/actions/',
                // You can set any number of default request options.
                'timeout'  => 2.0,
                ]);
            $response = $client->request('GET', ''.$aArgs['id'], [
                'auth'=> ['superadmin','superadmin'],
                'form_params' => $aArgs
            ]);

        
        $compare = '[[{"id":1,"keyword":"redirect","label_action":"Rediriger","id_status":"_NOSTATUS_","is_system":"Y","is_folder_action":"N","enabled":"Y","action_page":"redirect","history":"Y","origin":"entities","create_id":"N","category_id":null}]]';

        $this->assertSame((string)$response->getBody(), $compare);
    }*/
}
