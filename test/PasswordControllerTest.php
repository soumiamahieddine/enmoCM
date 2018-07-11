<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class PasswordControllerTest extends TestCase
{
    public function testGetRules(){
        $passwordController = new \SrcCore\controllers\PasswordController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $passwordController->getRules($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertInternalType('array', $responseBody->rules);
        $this->assertNotNull($responseBody->rules);
    }

    public function testUpdateRules(){
        $passwordController = new \SrcCore\controllers\PasswordController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs  =    [
            'rules' =>  [
                [
                    'id'        =>  1,
                    'value'     =>  5,
                    'enabled'   =>  'true',
                ]
            ]
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $passwordController->updateRules($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertSame($responseBody->success, 'success');
    }

    public function testIsPasswordValid(){
        $passwordController = new \SrcCore\controllers\PasswordController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'password'           => 'notValidPassword',
        ];

        $response     = $passwordController->isPasswordValid($aArgs);

        $this->assertSame($response,false);

        $aArgs = [
            'password'           => 'validPassword123&',
        ];
        
        $response     = $passwordController->isPasswordValid($aArgs);
        $this->assertSame($response,true);
    }
}