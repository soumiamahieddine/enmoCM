<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Ozwillo Script
 * @author dev@maarch.org
 */

chdir('../../..');

require 'vendor/autoload.php';

OzwilloScript::sendFile($argv);
OzwilloScript::sendData($argv);

class OzwilloScript
{
    public static function sendFile(array $args)
    {
        $customId = null;
        if (!empty($args[1]) && $args[1] == '--customId' && !empty($args[2])) {
            $customId = $args[2];
        }

        $configuration = OzwilloScript::getXmlLoaded(['path' => 'bin/external/ozwillo/config.xml', 'customId' => $customId]);
        if (empty($configuration)) {
            self::writeLog(['message' => "[ERROR] [SEND_FILE] File bin/external/ozwillo/config.xml does not exist"]);
            exit();
        } elseif (empty($configuration->user) || empty($configuration->password) || empty($configuration->sendFile->uri) || empty($configuration->sendFile->status)) {
            self::writeLog(['message' => "[ERROR] [SEND_FILE] File bin/external/ozwillo/config.xml is not filled enough"]);
            return;
        }
        $user = (string)$configuration->user;
        $password = (string)$configuration->password;
        $uri = (string)$configuration->sendFile->uri;
        $status = (string)$configuration->sendFile->status;

        \SrcCore\models\DatabasePDO::reset();
        new \SrcCore\models\DatabasePDO(['customId' => $customId]);


        $resources = \Resource\models\ResModel::get([
            'select'    => ['res_id', 'subject', 'format', 'path', 'filename', 'docserver_id', 'external_id'],
            'where'     => ['status = ?', "external_id->>'publikId' is null"],
            'data'      => [$status]
        ]);
        foreach ($resources as $resource) {
            if (empty($resource['filename'])) {
                self::writeLog(['message' => "[INFO] [SEND_FILE] Resource {$resource['res_id']} : ({$resource['subject']}) has no file"]);
                continue;
            }

            $docserver = \Docserver\models\DocserverModel::getByDocserverId(['docserverId' => $resource['docserver_id'], 'select' => ['path_template']]);
            $file = file_get_contents($docserver['path_template'] . str_replace('#', '/', $resource['path']) . $resource['filename']);
            if (empty($file)) {
                self::writeLog(['message' => "[ERROR] [SEND_FILE] Resource {$resource['res_id']} : ({$resource['subject']}) file is missing"]);
                continue;
            }

            $encodedFile = base64_encode($file);

            $body = [
                'resId'         => $resource['res_id'],
                'encodedFile'   => $encodedFile,
                'fileFormat'    => $resource['format']
            ];
            $response = \SrcCore\models\CurlModel::execSimple([
                'url'       => $uri,
                'method'    => 'POST',
                'headers'   => ['content-type:application/json'],
                'basicAuth' => ['user' => $user, 'password' => $password],
                'body'      => json_encode($body),
                'noLogs'    => true
            ]);

            if (!empty($response['errors'])) {
                self::writeLog(['message' => "[ERROR] [SEND_FILE] Resource {$resource['res_id']} : ({$resource['subject']}) curl call failed"]);
                self::writeLog(['message' => $response['errors']]);
                continue;
            } elseif (empty($response['response']['publikId'])) {
                self::writeLog(['message' => "[ERROR] [SEND_FILE] Resource {$resource['res_id']} : ({$resource['subject']}) publikId is missing"]);
                self::writeLog(['message' => json_encode($response['response'])]);
                continue;
            }

            $externalId = json_decode($resource['external_id'], true);
            $externalId['publikId'] = $response['response']['publikId'];
            \Resource\models\ResModel::update(['set' => ['external_id' => json_encode($externalId)], 'where' => ['res_id = ?'], 'data' => [$resource['res_id']]]);

            self::writeLog(['message' => "[SUCCESS] [SEND_FILE] Resource {$resource['res_id']} : ({$resource['subject']}) successfully sent to ozwillo"]);
        }
    }

    public static function sendData(array $args)
    {
        $customId = null;
        if (!empty($args[1]) && $args[1] == '--customId' && !empty($args[2])) {
            $customId = $args[2];
        }

        $configuration = OzwilloScript::getXmlLoaded(['path' => 'bin/external/ozwillo/config.xml', 'customId' => $customId]);
        if (empty($configuration)) {
            self::writeLog(['message' => "[ERROR] [SEND_DATA] File bin/external/ozwillo/config.xml does not exist"]);
            exit();
        } elseif (empty($configuration->user) || empty($configuration->password) || empty($configuration->sendData->uri) || empty($configuration->sendData->status)) {
            self::writeLog(['message' => "[ERROR] [SEND_DATA] File bin/external/ozwillo/config.xml is not filled enough"]);
            return;
        }
        $user = (string)$configuration->user;
        $password = (string)$configuration->password;
        $uri = (string)$configuration->sendData->uri;
        $status = (string)$configuration->sendData->status;

        \SrcCore\models\DatabasePDO::reset();
        new \SrcCore\models\DatabasePDO(['customId' => $customId]);


        $resources = \Resource\models\ResModel::get([
            'select'    => ['res_id', 'subject', 'external_id'],
            'where'     => ['status = ?', "external_id->>'publikId' is not null"],
            'data'      => [$status]
        ]);
        foreach ($resources as $resource) {
            $externalId = json_decode($resource['external_id'], true);

            $body = [
                'publikId'  => $externalId['publikId'],
            ];
            $response = \SrcCore\models\CurlModel::execSimple([
                'url'       => $uri,
                'method'    => 'PUT',
                'headers'   => ['content-type:application/json'],
                'basicAuth' => ['user' => $user, 'password' => $password],
                'body'      => json_encode($body),
                'noLogs'    => true
            ]);

            if (!empty($response['errors'])) {
                self::writeLog(['message' => "[ERROR] [SEND_DATA] Resource {$resource['res_id']} : ({$resource['subject']}) curl call failed"]);
                self::writeLog(['message' => $response['errors']]);
                continue;
            }

            unset($externalId['publikId']);
            \Resource\models\ResModel::update(['set' => ['external_id' => json_encode($externalId)], 'where' => ['res_id = ?'], 'data' => [$resource['res_id']]]);

            self::writeLog(['message' => "[SUCCESS] [SEND_DATA] Resource {$resource['res_id']} : ({$resource['subject']}) successfully sent to ozwillo"]);
        }
    }

    public static function getXmlLoaded(array $args)
    {
        if (!empty($args['customId']) && file_exists("custom/{$args['customId']}/{$args['path']}")) {
            $path = "custom/{$args['customId']}/{$args['path']}";
        }
        if (empty($path)) {
            $path = $args['path'];
        }

        $xmlfile = null;
        if (file_exists($path)) {
            $xmlfile = simplexml_load_file($path);
        }

        return $xmlfile;
    }

    public static function writeLog(array $args)
    {
        $file = fopen('bin/external/ozwillo/ozwilloScript.log', 'a');
        fwrite($file, '[' . date('Y-m-d H:i:s') . '] ' . $args['message'] . PHP_EOL);
        fclose($file);
    }
}
