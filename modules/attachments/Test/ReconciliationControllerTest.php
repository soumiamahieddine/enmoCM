<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

namespace MaarchTest;
use PHPUnit\Framework\TestCase;
use MaarchTest\DocserverControllerTest;

class ReconciliationControllerTest extends TestCase
{
    public function testPrepareStorage()
    {
        $action = new \Attachments\Controllers\ReconciliationController();

        $data = [];

        array_push(
            $data,
            array(
                'column' => 'title',
                'value' => 'test pj',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'attachment_type',
                'value' => 'response_project',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'status',
                'value' => 'A_TRA',
                'type' => 'string',
            )
        );

        $aArgs = [
            'data'         => $data,
            'resIdMaster'  => 100,
            'collIdMaster' => 'letterbox_coll',
        ];

        $response = $action->prepareStorage($aArgs);

        $this->assertArrayHasKey('column', $response['data'][0]);
    }

    public function testStoreAttachmentResources(){
        $docserverControllerTest = new DocserverControllerTest();
        $action = new \Attachments\Controllers\ReconciliationController();

        $docserverControllerTest -> testStoreResourceOnDocserver();

        $path = $_SESSION['config']['tmppath'] . '/test/';

        if (!is_dir($path)) {
            mkdir($path);
        }

        $fileSource = 'test_source.txt';

        $fp = fopen($path . $fileSource, 'w');
        fwrite($fp, 'a unit test');
        fclose($fp);

        $data = [];

        array_push(
            $data,
            array(
                'column' => 'title',
                'value' => 'test pj',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'attachment_type',
                'value' => 'response_project',
                'type' => 'string',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'status',
                'value' => 'A_TRA',
                'type' => 'string',
            )
        );

        $aArgs = [
            'resId'         => 101,
            'resIdMaster'   => 100,
            'data'          => $data,
            'collId'        => 'attachments_coll',
            'collIdMaster'  => 'letterbox_coll',
            'table'         => 'res_attachments',
            'fileFormat'    => 'txt',
            'filename'      => $fileSource,
            'path'          => $path,
            'docserverPath' => $path,
            'docserverId'   => 'FASTHD_MAN'
        ];

        $response = $action->storeAttachmentResource($aArgs);

        $this->assertTrue($response;
    }
}