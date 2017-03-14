<?php

namespace MaarchTest;

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

require_once __DIR__.'/define.php';

class ResExtControllerTest extends \PHPUnit_Framework_TestCase
{
    /*public function testCreate()
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

        $fp = fopen($path . $fileSource, 'a');
        fwrite($fp, 'a unit test');
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
        //print_r(json_encode($data));
        //sample in json : [{"column":"subject","value":"UNIT T
        //EST from slim","type":"string"},{"column":"type_id","value":110,"type":"integer"},
        //{"column":"custom_t1","value":"TES
        //T","type":"string"},{"column":"custom_t10","value":"lgi@maarch.org","type":"string"}]

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
        $response = $action->create($request, $response, $aArgs);
        //print_r($response);exit;
        $this->assertGreaterThan(1, json_decode($response->getBody())[0]);
    }*/

    public function testPrepareStorageExt()
    {
        $action = new \Core\Controllers\ResExtController();

        $data = [];

        array_push(
            $data,
            array(
                'column' => 'process_limit_date',
                'value' => '29/03/2017',
                'type' => 'date',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'process_notes',
                'value' => '50,workingDay',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'category_id',
                'value' => 'incoming',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'alt_identifier',
                'value' => '',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'exp_contact_id',
                'value' => 'jeanlouis.ercolani@maarch.org',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'address_id',
                'value' => 'jeanlouis.ercolani@maarch.org',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'nature_id',
                'value' => 'simple_mail',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'admission_date',
                'value' => date('d/m/Y'),
                'type' => 'date',
            )
        );

        $aArgs = [
            'resId' => 100,
            'data'  => $data,
            'table' => 'mlb_coll_ext',
        ];

        $response = $action->prepareStorageExt($aArgs);

        $this->assertArrayHasKey('res_id', $response);
    }

    public function testStoreExtResource()
    {
        $action = new \Core\Controllers\ResExtController();
        
        $data = [];

        array_push(
            $data,
            array(
                'column' => 'process_limit_date',
                'value' => '29/03/2017',
                'type' => 'date',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'process_notes',
                'value' => '50,workingDay',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'category_id',
                'value' => 'incoming',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'alt_identifier',
                'value' => '',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'exp_contact_id',
                'value' => 'jeanlouis.ercolani@maarch.org',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'address_id',
                'value' => 'jeanlouis.ercolani@maarch.org',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'nature_id',
                'value' => 'simple_mail',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'admission_date',
                'value' => date('d/m/Y'),
                'type' => 'date',
            )
        );

        $aArgs = [
            'resId'    => 100,
            'data'     => $data,
            'table'    => 'mlb_coll_ext',
            'resTable' => 'res_letterbox',
        ];
        
        $response = $action->storeExtResource($aArgs);
        
        $this->assertTrue($response);
    }
}
