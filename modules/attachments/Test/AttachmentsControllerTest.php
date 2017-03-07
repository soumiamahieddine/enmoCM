<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

require_once 'core/Test/define.php';

class AttachmentsControllerTest extends PHPUnit_Framework_TestCase
{

    public function testPrepareStorage()
    {
        $action = new \Attachments\Controllers\AttachmentsController();

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
            'resId'        => 100,
            'collIdMaster' => 'letterbox_coll',
        ];

        $response = $action->prepareStorage($aArgs);
        
        $this->assertArrayHasKey('column', $response['data'][0]);
    }

    public function testStoreAttachmentResource()
    {
        $action = new \Attachments\Controllers\AttachmentsController();

        $path = $_SESSION['config']['tmppath'] . '/test/';

        if (!is_dir($path)) {
            mkdir($path);    
        }

        $fileSource = 'test_source.txt';

        $fp = fopen($path . $fileSource, 'w');
        fwrite($fp, 'a unit test');
        fclose($fp);

        $fileContent = file_get_contents($path . $fileSource, FILE_BINARY);
        $encodedFile = base64_encode($fileContent);

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
            'resId'         => 100,
            'encodedFile'   => $encodedFile,
            'data'          => $data,
            'collId'        => 'attachments_coll',
            'collIdMaster'  => 'letterbox_coll',
            'table'         => 'res_attachments',
            'fileFormat'    => 'txt',
        ];

        $response = $action->storeAttachmentResource($aArgs);
        
        $this->assertGreaterThanOrEqual(0, $response[0]);
    }
}