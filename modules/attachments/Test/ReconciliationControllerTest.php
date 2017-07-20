<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

require_once 'core/Test/define.php';

class ReconciliationControllerTest extends PHPUnit_Framework_TestCase
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

}