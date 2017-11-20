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

class ProcessConvertTest extends TestCase
{
    public function testconvert ()
    {
        $action = new \Convert\Controllers\ProcessConvertController();

        $aArgs = [
            'collId' => 'letterbox_coll', 
            'resTable' => 'res_letterbox', 
            'adrTable' => 'adr_x', 
            'resId' => 100, 
            'tmpDir' => $_SESSION['config']['tmppath']
        ];

        $response = $action->convert($aArgs);
        var_dump($response);

        $this->assertArrayHasKey('status', $response);
    }
}
