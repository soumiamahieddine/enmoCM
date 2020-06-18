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

use Respect\Validation\Validator;
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
        $errorReporting = (ini_get('error_reporting') >= 22519);

        exec('whereis netcat', $outputNetcat, $returnNetcat);
        $outputNetcat = explode(':', $outputNetcat[0]);
        exec('whereis nmap', $outputNmap, $returnNmap);
        $outputNmap = explode(':', $outputNmap[0]);
        $netcatOrNmap = !empty($outputNetcat[1]) || !empty($outputNmap[1]);

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
            'errorReporting'    => $errorReporting,
            'netcatOrNmap'      => $netcatOrNmap
        ];

        return $response->withJson(['prerequisites' => $prerequisites]);
    }

    public function checkDatabaseConnection(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        if (!Validator::stringType()->notEmpty()->validate($queryParams['server'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body server is empty or not a string']);
        } elseif (!Validator::intVal()->notEmpty()->validate($queryParams['port'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body port is empty or not an integer']);
        } elseif (!Validator::stringType()->notEmpty()->validate($queryParams['user'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body user is empty or not a string']);
        } elseif (!Validator::stringType()->notEmpty()->validate($queryParams['password'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body password is empty or not a string']);
        }
        if (empty($queryParams['name'])) {
            $queryParams['name'] = 'postgres';
        }

        $connection = "host={$queryParams['server']} port={$queryParams['port']} user={$queryParams['user']} password={$queryParams['password']} dbname={$queryParams['name']}";
        if (!@pg_connect($connection)) {
            return $response->withStatus(400)->withJson(['errors' => 'Connexion failed']);
        }

        $request = "select datname from pg_database where datname = '{$queryParams['name']}'";
        $result = @pg_query($request);
        if (!$result) {
            return $response->withStatus(400)->withJson(['errors' => 'Request failed']);
        }

        return $response->withStatus(204);
    }

    public function setDatabase(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['server'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body server is empty or not a string']);
        } elseif (!Validator::intVal()->notEmpty()->validate($body['port'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body port is empty or not an integer']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['user'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body user is empty or not a string']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['password'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body password is empty or not a string']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['name'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body name is empty or not a string']);
        }

        $connection = "host={$body['server']} port={$body['port']} user={$body['user']} password={$body['password']} dbname={$body['name']}";
        if (!@pg_connect($connection)) {
            return $response->withStatus(400)->withJson(['errors' => 'Connexion failed']);
        }

        $request = "CREATE DATABASE '{$body['name']}' WITH TEMPLATE template0 ENCODING = 'UTF8'";
        $result = pg_query($request);
        if (!$result) {
            return $response->withStatus(400)->withJson(['errors' => 'Request failed']);
        }

        @pg_query("ALTER DATABASE '{$body['name']}' SET DateStyle =iso, dmy");
        pg_close();

        $options = [
            \PDO::ATTR_PERSISTENT   => true,
            \PDO::ATTR_ERRMODE      => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_CASE         => \PDO::CASE_NATURAL
        ];

        $dsn = "pgsql:host={$body['server']};port={$body['port']};dbname={$body['name']}";
        $db = new \PDO($dsn, $body['user'], $body['password'], $options);
        $fileContent = file_get_contents('sql/structure.sql');

        $result = $db->query($fileContent, null, true, true);
        if (!$result) {
            return $response->withStatus(400)->withJson(['errors' => 'Request failed : run structure.sql']);
        }

        return $response->withStatus(204);
    }
}
