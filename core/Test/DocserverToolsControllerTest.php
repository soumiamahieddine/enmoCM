<?php

namespace MaarchTest;

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

require_once __DIR__.'/define.php';

class DocserverToolsControllerTest extends PHPUnit_Framework_TestCase
{
    public function testSetRights()
    {
        $action = new \Core\Controllers\DocserverToolsController();

        $aArgs = [
            'path' => '/opt/maarch/docservers/'
        ];

        $response = $action->setRights($aArgs);

        $this->assertTrue($response['setRights']);
    }

    public function testDoFingerprint()
    {
        $action = new \Core\Controllers\DocserverToolsController();

        $path = $_SESSION['config']['tmppath'] . '/test/';

        if (!is_dir($path)) {
            mkdir($path);
        }

        $fileSource = 'test_source.txt';

        //creates an empty file
        $fp = fopen($path . $fileSource, 'a');
        fwrite($fp, 'a unit test');
        fclose($fp);

        $aArgs = [
            'path'            => $fileSource,
            'fingerprintMode' => 'NONE',
        ];

        $response = $action->doFingerprint($aArgs);

        $this->assertEquals($response['setRights'], 0);
    }

    public function testControlFingerprint()
    {
        $action = new \Core\Controllers\DocserverToolsController();

        $path = $_SESSION['config']['tmppath'] . '/test/';

        if (!is_dir($path)) {
            mkdir($path);
        }

        $fileSource = 'test_source.txt';

        $fp = fopen($path . $fileSource, 'a');
        fwrite($fp, 'a unit test');
        fclose($fp);

        $aArgs = [
            'pathInit'        => $path . $fileSource,
            'pathTarget'      => $path . $fileSource,
            'fingerprintMode' => 'sha256',
        ];

        $response = $action->controlFingerprint($aArgs);

        $this->assertTrue($response['controlFingerprint']);
    }

    public function testCopyOnDocserver()
    {
        $action = new \Core\Controllers\DocserverToolsController();

        $path = $_SESSION['config']['tmppath'] . '/test/';

        if (!is_dir($path)) {
            mkdir($path);
        }

        $fileSource = 'test_source.txt';
        $fileDest = 'test_dest.txt';

        $fp = fopen($path . $fileSource, 'a');
        fwrite($fp, 'a unit test');
        fclose($fp);

        $aArgs = [
            'sourceFilePath'             => $path . $fileSource,
            'destinationDir'             => $path,
            'fileDestinationName'        => $fileDest,
            'docserverSourceFingerprint' => 'sha256',
        ];

        $response = $action->copyOnDocserver($aArgs);

        $this->assertArrayHasKey('destinationDir', $response['copyOnDocserver']);
    }

    public function testWashTmp()
    {
        $action = new \Core\Controllers\DocserverToolsController();

        $path = $_SESSION['config']['tmppath'] . '/test/';
        
        if (!is_dir($path)) {
            mkdir($path);
        }

        $aArgs = [
            'path'        => $path,
            'contentOnly' => false,
        ];

        $response = $action->washTmp($aArgs);

        $this->assertTrue($response['washTmp']);
    }

    public function testCreatePathOnDocServer()
    {
        $action = new \Core\Controllers\DocserverToolsController();

        $aArgs = [
            'path' => '/opt/maarch/new_docservers/MaarchCourrierGit/manual/'
        ];

        $response = $action->createPathOnDocServer($aArgs);

        $this->assertArrayHasKey('destinationDir', $response['createPathOnDocServer']);
    }
}
