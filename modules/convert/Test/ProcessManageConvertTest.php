<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

namespace MaarchTest;
use PHPUnit\Framework\TestCase;

class ManageProcessConvertTest extends TestCase
{
    public function testmanageConvert ()
    {
        $action = new \Core\Controllers\ResController();

        $environment = \Slim\Http\Environment::mock(
            [
                'REQUEST_METHOD' => 'POST',
            ]
        );

        $path = $_SESSION['config']['tmppath'] . '/test/';

        if (!is_dir($path)) {
            mkdir($path);
        }

        $fileSource = 'test_source.txt';
        
        if (file_exists($path . $fileSource)) {
            unlink($path . $fileSource);
        }

        $fp = fopen($path . $fileSource, 'a');
        fwrite($fp, 'a unit test for PHP CONVERSION lorem ipsum...');
        fclose($fp);

        $fileContent = file_get_contents($path . $fileSource, FILE_BINARY);
        $encodedFile = base64_encode($fileContent);
        //echo $encodedFile . PHP_EOL;exit;
        
        $data = [];

        array_push(
            $data,
            array(
                'column' => 'subject',
                'value' => 'UNIT TEST from slim',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'type_id',
                'value' => 110,
                'type' => 'integer',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'custom_t1',
                'value' => 'TEST',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'custom_t10',
                'value' => 'lgi@maarch.org',
                'type' => 'string',
            )
        );

        $aArgs = [
            'encodedFile'   => $encodedFile,
            'data'          => $data,
            'collId'        => 'letterbox_coll',
            'table'         => 'res_letterbox',
            'fileFormat'    => 'txt',
            'status'        => 'new',
        ];

        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response = $action->create($fullRequest, $response);
        $responseBody = json_decode((string)$response->getBody());
        
        $resId = $responseBody->resId;
        
        if (!defined("_RES_ID_TEST_CONVERT")) {
            define("_RES_ID_TEST_CONVERT", $resId);
        }

        //real test
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $action = new \Convert\Controllers\ProcessManageConvertController();

        $aArgs = [
            'collId' => 'letterbox_coll', 
            'resTable' => 'res_letterbox', 
            'adrTable' => 'adr_letterbox', 
            'resId' => _RES_ID_TEST_CONVERT, 
            'tmpDir' => $_SESSION['config']['tmppath'],
            'createZendIndex' => true
        ];

        $response = new \Slim\Http\Response();
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response = $action->create($fullRequest, $response);
        $responseBody = json_decode((string)$response->getBody());
        $status = $responseBody->status;
        
        $this->assertEquals('0', $status);
    }
}
