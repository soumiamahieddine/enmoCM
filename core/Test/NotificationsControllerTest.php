<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

namespace MaarchTest;

//use Core\Models\DatabaseModel;

use PHPUnit\Framework\TestCase;

class NotificationControllerTest extends TestCase
{
    private static $id = null;
    public function testReadAll()
    {
        //  TEST GET // READ // NEED TO HAVE RED IN BDD 
        $NotificationController = new \Notifications\Controllers\NotificationController();
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $NotificationController->get($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('RED', $responseBody->notifications[6]->notification_id);
    }


    public function testCreate()
    {
        //  CREATE
        $NotificationController = new \Notifications\Controllers\NotificationController();
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'notification_id'    => 'warning5',
            'description' => 'Alerte aux gogoles',
            'is_enabled'  => 'Y',
            'event_id'  => 'users%',
            'notification_mode'  => 'EMAIL',
            'template_id'  => '4',
            'rss_url_template'  => 'http://localhost/maarch_entreprise',
            'diffusion_type'  => 'user',
            'diffusion_properties'  => 'superadmin',
            'attachfor_type'  => 'zz',
            'attachfor_properties'  => 'cc' 

        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $NotificationController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertInternalType("int", $responseBody->notification_sid);
        $notification_sid = $responseBody->notification_sid;
        self::$id = $notification_sid;
        unset($responseBody->notification_sid);
        $aCompare = json_decode(json_encode($compare), false);

        $this->assertSame('warning5', $responseBody->notification_id);
        $this->assertSame('Alerte aux gogoles', $responseBody->description);
        $this->assertSame('Y', $responseBody->is_enabled);
        $this->assertSame('users%', $responseBody->event_id);
        $this->assertSame('EMAIL', $responseBody->notification_mode);
        $this->assertSame(4, $responseBody->template_id);
        $this->assertSame('http://localhost/maarch_entreprise', $responseBody->rss_url_template);
        $this->assertSame('user', $responseBody->diffusion_type);
        $this->assertSame('superadmin', $responseBody->diffusion_properties);
        $this->assertSame('zz', $responseBody->attachfor_type);
        $this->assertSame('cc', $responseBody->attachfor_properties);

    }

    public function testCreateFail1()
    {
        //Fail Create 1
        $NotificationController = new \Notifications\Controllers\NotificationController();
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'notification_id'    => '',
            'description' => 'Alerte aux gogoles',
            'is_enabled'  => 'Y',
            'event_id'  => 'users%',
            'notification_mode'  => 'EMAIL',
            'template_id'  => '4',
            'rss_url_template'  => 'http://localhost/maarch_entreprise',
            'diffusion_type'  => 'user',
            'diffusion_properties'  => 'superadmin',
            'attachfor_type'  => 'zz',
            'attachfor_properties'  => 'cc' 

        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $NotificationController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Notification error : notification_id is empty', $responseBody->errors);
    }

    public function testCreateFail2()
    {
        //Fail Create 2
        $NotificationController = new \Notifications\Controllers\NotificationController();
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'notification_id'    => 'warning5',
            'description' => 'Alerte aux gogoles',
            'is_enabled'  => 'Y',
            'event_id'  => 'users%',
            'notification_mode'  => 'EMAIL',
            'template_id'  => '4',
            'rss_url_template'  => 'http://localhost/maarch_entreprise',
            'diffusion_type'  => 'user',
            'diffusion_properties'  => 'superadmin',
            'attachfor_type'  => 'zz',
            'attachfor_properties'  => 'cc' 

        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $NotificationController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Notification error : id already exist', $responseBody->errors);
    }


    public function testUpdate()
    {
        //  UPDATE
        $NotificationController = new \Notifications\Controllers\NotificationController();
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'notification_id'    => 'warning5',
            'description' => 'BOUBOUP',
            'is_enabled'  => 'Y',
            'event_id'      => 'users%',
            'notification_mode'     => 'EMAIL',
            'template_id'  => 4,
            'rss_url_template'   => 'http://localhost/maarch_entreprise',
            'diffusion_type'   => 'user',
            'diffusion_properties'   => 'superadmin',
            'attachfor_type'   => 'NN',
            'attachfor_type'   => 'NC',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $NotificationController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());
        //$this->assertSame(_NOTIFICATION_UPDATED, $responseBody->success);
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $NotificationController->getById($request, new \Slim\Http\Response(), ['id' => 'warning5']);
        $responseBody = json_decode((string)$response->getBody());;
        $this->assertSame(self::$id, $responseBody->notifications->notification_sid);
        $this->assertSame('warning5', $responseBody->notifications->notification_id);
        $this->assertSame('BOUBOUP', $responseBody->notifications->description);
        $this->assertSame('Y', $responseBody->notifications->is_enabled);
        $this->assertSame('users%', $responseBody->notifications->event_id);
        $this->assertSame('EMAIL', $responseBody->notifications->notification_mode);
        $this->assertSame(4, $responseBody->notifications->template_id);
        $this->assertSame('user', $responseBody->notifications->diffusion_type);

    }
    public function testRead(){
        //READ
        $NotificationController = new \Notifications\Controllers\NotificationController();
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $NotificationController->getById($request, new \Slim\Http\Response(), ['id' => 'warning5']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->notifications->notification_sid);
        $this->assertSame('warning5', $responseBody->notifications->notification_id);
        $this->assertSame('BOUBOUP', $responseBody->notifications->description);
        $this->assertSame('Y', $responseBody->notifications->is_enabled);
        $this->assertSame('users%', $responseBody->notifications->event_id);
        $this->assertSame('EMAIL', $responseBody->notifications->notification_mode);
        $this->assertSame(4, $responseBody->notifications->template_id);
        $this->assertSame('user', $responseBody->notifications->diffusion_type);
    }

    // public function testReadFail(){
    //     //I CANT READ BECAUSE NO ID
    //     $NotificationController = new \Notifications\Controllers\NotificationController();
    //     $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
    //     $request        = \Slim\Http\Request::createFromEnvironment($environment);
    //     $response     = $NotificationController->getById($request, new \Slim\Http\Response(), ['id' => '']);
    //     $responseBody = json_decode((string)$response->getBody());
    //     $this->assertSame('notification_id is empty', $responseBody->errors);
    // }

    public function testReadFail2(){
        //I CANT READ BECAUSE NO EXIST
        $NotificationController = new \Notifications\Controllers\NotificationController();
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $NotificationController->getById($request, new \Slim\Http\Response(), ['id' => 'BamBam']);
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Notification not found', $responseBody->errors);
    }

    // public function testDeleteFail()
    // {
    //     $NotificationController = new \Notifications\Controllers\NotificationController();

    //     //  DELETE
    //     $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
    //     $request        = \Slim\Http\Request::createFromEnvironment($environment);
    //     $response       = $NotificationController->deleteNotification($request, new \Slim\Http\Response(), ['id' => '2245']);
    //     $responseBody   = json_decode((string)$response->getBody());
    //     var_dump($responseBody);
    //     //$this->assertSame(_DELETED_NOTIFICATION, $responseBody->success);
    // }

    public function testDelete()
    {
        $NotificationController = new \Notifications\Controllers\NotificationController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $NotificationController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());
        $this->assertSame(_DELETED_NOTIFICATION, $responseBody->success);
    }
        
}
