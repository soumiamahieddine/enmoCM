<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Installer Controller
 *
 * @author dev@maarch.org
 */

namespace SrcCore\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

class InstallerController
{
    public function getPrerequisites(Request $request, Response $response)
    {
        $phpVersion = phpversion();
        $phpVersionValid = (version_compare(PHP_VERSION, '7.2') >= 0);

        exec('whereis unoconv', $output, $return);
        $output = explode(':', $output[0]);
        $unoconv = !empty($output[1]);

        $pdoPgsql = @extension_loaded('pdo_pgsql');
        $pgsql = @extension_loaded('pgsql');
        $mbstring = @extension_loaded('mbstring');
        $fileinfo = @extension_loaded('fileinfo');
        $gd = @extension_loaded('gd');
        $imagick = @extension_loaded('imagick');
        $imap = @extension_loaded('imap');
        $xsl = @extension_loaded('xsl');
        $gettext = @extension_loaded('gettext');
        $xmlrpc = @extension_loaded('xmlrpc');
        $curl = @extension_loaded('curl');
        $zip = @extension_loaded('zip');

        $writable = is_writable('.') && is_readable('.');

        $displayErrors = (ini_get('display_errors') == '1');
        $shortOpenTag = (ini_get('short_open_tag') == '1');
        $errorReporting = (ini_get('error_reporting') == 22519);

        exec('whereis netcat', $output, $return);
        $output = explode(':', $output[0]);
        exec('whereis nmap', $output2, $return2);
        $output2 = explode(':', $output2[0]);
        $netcatOrNmap = !empty($output[1]) || !empty($output2[1]);

        $prerequisites = [
            'phpVersion'        => $phpVersion,
            'phpVersionValid'   => $phpVersionValid,
            'unoconv'           => $unoconv,
            'pdoPgsql'          => $pdoPgsql,
            'pgsql'             => $pgsql,
            'mbstring'          => $mbstring,
            'fileinfo'          => $fileinfo,
            'gd'                => $gd,
            'imagick'           => $imagick,
            'imap'              => $imap,
            'xsl'               => $xsl,
            'gettext'           => $gettext,
            'xmlrpc'            => $xmlrpc,
            'curl'              => $curl,
            'zip'               => $zip,
            'writable'          => $writable,
            'displayErrors'     => $displayErrors,
            'shortOpenTag'      => $shortOpenTag,
            'errorReporting'    => $errorReporting,
            'netcatOrNmap'      => $netcatOrNmap
        ];

        return $response->withJson(['prerequisites' => $prerequisites]);
    }
}
