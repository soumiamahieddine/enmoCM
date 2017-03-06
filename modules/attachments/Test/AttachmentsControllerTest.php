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

    public function testStoreAttachmentResource()
    {
        $action = new \Attachments\Controllers\AttachmentsController();

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

        $aArgs = [
            'resId'         => 100,
            'encodedFile'   => $encodedFile,
            'collId'        => 'attachments_coll',
            'table'         => 'res_attachments',
            'fileFormat'    => 'txt',
            'title'         => 'test pj',
        ];

        $response = $action->storeAttachmentResource($aArgs);
        print_r($response);exit;
        $this->assertGreaterThanOrEqual(0, $response);
    }
}