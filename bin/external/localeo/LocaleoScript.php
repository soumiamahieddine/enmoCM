<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Localeo Script
 * @author dev@maarch.org
 */

chdir('../../..');

require 'vendor/autoload.php';

LocaleoScript::sendContact($argv);
LocaleoScript::updateContact($argv);

class LocaleoScript
{
    const MAPPING_CONTACT = [
        'civility'          => 'civility',
        'company'           => 'company',
        'firstname'         => 'firstname',
        'familyName'        => 'lastname',
        'email'             => 'email',
        'phone'             => 'phone',
        'num'               => 'address_number',
        'street'            => 'address_street',
        'ZC'                => 'address_postcode',
        'additionalAddress' => 'address_additional1',
        'city'              => 'address_town',
        'country'           => 'address_country',
        'externalId'        => 'id'
    ];

    public static function sendContact(array $args)
    {
        $customId = null;
        if (!empty($args[1]) && $args[1] == '--customId' && !empty($args[2])) {
            $customId = $args[2];
        }

        $configuration = LocaleoScript::getXmlLoaded(['path' => 'bin/external/localeo/config.xml', 'customId' => $customId]);
        if (empty($configuration)) {
            self::writeLog(['message' => "[SEND_CONTACT] File bin/external/localeo/config.xml does not exist"]);
            exit();
        } elseif (empty($configuration->apiKey) || empty($configuration->appName) || empty($configuration->sendContact)) {
            self::writeLog(['message' => "[SEND_CONTACT] File bin/external/localeo/config.xml is not filled enough"]);
            return;
        }
        if ((string)$configuration->sendContact->enabled == 'false') {
            return;
        }

        $apiKey = (string)$configuration->apiKey;
        $appName = (string)$configuration->appName;
        $url = (string)$configuration->sendContact->url;
        if (empty($url)) {
            self::writeLog(['message' => "[SEND_CONTACT] File bin/external/localeo/config.xml is not filled enough"]);
            return;
        }

        $dataToMerge = [];
        if (!empty($configuration->sendContact->data)) {
            foreach ($configuration->sendContact->data as $value) {
                $dataToMerge[(string)$value->key] = (string)$value->value;
            }
        }

        \SrcCore\models\DatabasePDO::reset();
        new \SrcCore\models\DatabasePDO(['customId' => $customId]);

        $contacts = \Contact\models\ContactModel::get([
            'select'    => ['*'],
            'where'     => ['enabled = ?', "external_id->>'localeoId' is null"],
            'data'      => [true]
        ]);

        foreach ($contacts as $contact) {
            $body = [];
            foreach (self::MAPPING_CONTACT as $key => $value) {
                $body[$key] = $contact[$value] ?? '';
            }
            $body = array_merge($body, $dataToMerge);

            $response = \SrcCore\models\CurlModel::execSimple([
                'url'       => $url,
                'method'    => 'NO-METHOD',
                'headers'   => ["Api-Key: {$apiKey}", "appName: {$appName}"],
                'body'      => ['citoyen' => json_encode($body)],
                'noLogs'    => true
            ]);

            if (!empty($response['errors'])) {
                self::writeLog(['message' => "[SEND_CONTACT] Contact {$contact['id']} : curl call failed"]);
                self::writeLog(['message' => $response['errors']]);
                continue;
            } elseif (empty($response['response']['id'])) {
                self::writeLog(['message' => "[SEND_CONTACT] Contact {$contact['id']} : id is missing"]);
                self::writeLog(['message' => json_encode($response['response'])]);
                continue;
            }

            $externalId = json_decode($contact['external_id'], true);
            $externalId['localeoId'] = $response['response']['id'];
            \Contact\models\ContactModel::update(['set' => ['external_id' => json_encode($externalId)], 'where' => ['id = ?'], 'data' => [$contact['id']]]);

            self::writeLog(['message' => "[SEND_CONTACT] Contact {$contact['id']} : successfully sent to localeo"]);
        }
    }

    public static function updateContact(array $args)
    {
        $customId = null;
        if (!empty($args[1]) && $args[1] == '--customId' && !empty($args[2])) {
            $customId = $args[2];
        }

        $configuration = LocaleoScript::getXmlLoaded(['path' => 'bin/external/localeo/config.xml', 'customId' => $customId]);
        if (empty($configuration)) {
            self::writeLog(['message' => "[UPDATE_CONTACT] File bin/external/localeo/config.xml does not exist"]);
            exit();
        } elseif (empty($configuration->apiKey) || empty($configuration->appName) || empty($configuration->updateContact)) {
            self::writeLog(['message' => "[UPDATE_CONTACT] File bin/external/localeo/config.xml is not filled enough"]);
            return;
        }
        if ((string)$configuration->updateContact->enabled == 'false') {
            return;
        }

        $apiKey = (string)$configuration->apiKey;
        $appName = (string)$configuration->appName;
        $url = (string)$configuration->updateContact->url;
        if (empty($url)) {
            self::writeLog(['message' => "[UPDATE_CONTACT] File bin/external/localeo/config.xml is not filled enough"]);
            return;
        }

        $dataToMerge = [];
        if (!empty($configuration->updateContact->data)) {
            foreach ($configuration->updateContact->data as $value) {
                $dataToMerge[(string)$value->key] = (string)$value->value;
            }
        }

        \SrcCore\models\DatabasePDO::reset();
        new \SrcCore\models\DatabasePDO(['customId' => $customId]);

        $where = ['enabled = ?', "external_id->>'localeoId' is not null"];
        $data = [true];
        if (file_exists('bin/external/localeo/updateContact.timestamp')) {
            $time = file_get_contents('bin/external/localeo/updateContact.timestamp');
            $where[] = 'modification_date > ?';
            $data[] = date('Y-m-d H:i:s', $time);

        }
        $file = fopen('bin/external/localeo/updateContact.timestamp', 'w');
        fwrite($file, time());
        fclose($file);

        $contacts = \Contact\models\ContactModel::get([
            'select'    => ['*'],
            'where'     => $where,
            'data'      => $data
        ]);

        foreach ($contacts as $contact) {
            $externalId = json_decode($contact['external_id'], true);

            $body = [];
            foreach (self::MAPPING_CONTACT as $key => $value) {
                $body[$key] = $contact[$value] ?? '';
            }
            $body['id'] = $externalId['localeoId'];
            $body = array_merge($body, $dataToMerge);

            $response = \SrcCore\models\CurlModel::execSimple([
                'url'       => $url,
                'method'    => 'NO-METHOD',
                'headers'   => ["Api-Key: {$apiKey}", "appName: {$appName}"],
                'body'      => ['citoyen' => json_encode($body)],
                'noLogs'    => true
            ]);

            if (!empty($response['errors'])) {
                self::writeLog(['message' => "[UPDATE_CONTACT] Contact {$contact['id']} : curl call failed"]);
                self::writeLog(['message' => $response['errors']]);
                continue;
            } elseif (empty($response['response']['id'])) {
                self::writeLog(['message' => "[UPDATE_CONTACT] Contact {$contact['id']} : id is missing"]);
                self::writeLog(['message' => json_encode($response['response'])]);
                continue;
            }

            self::writeLog(['message' => "[UPDATE_CONTACT] Contact {$contact['id']} : successfully sent to localeo"]);
        }
    }

//    public static function sendResource(array $args)
//    {
//        $customId = null;
//        if (!empty($args[1]) && $args[1] == '--customId' && !empty($args[2])) {
//            $customId = $args[2];
//        }
//
//        $configuration = LocaleoScript::getXmlLoaded(['path' => 'bin/external/localeo/config.xml', 'customId' => $customId]);
//        if (empty($configuration)) {
//            self::writeLog(['message' => "[SEND_RESOURCE] File bin/external/localeo/config.xml does not exist"]);
//            exit();
//        } elseif (empty($configuration->apiKey) || empty($configuration->appName) || empty($configuration->sendResource)) {
//            self::writeLog(['message' => "[SEND_RESOURCE] File bin/external/localeo/config.xml is not filled enough"]);
//            return;
//        }
//        if ((string)$configuration->sendResource->enabled == 'false') {
//            return;
//        }
//
//        $apiKey = (string)$configuration->apiKey;
//        $appName = (string)$configuration->appName;
//        $url = (string)$configuration->sendResource->url;
//        if (empty($url)) {
//            self::writeLog(['message' => "[SEND_RESOURCE] File bin/external/localeo/config.xml is not filled enough"]);
//            return;
//        }
//
//        $dataToMerge = [];
//        if (!empty($configuration->sendResource->data)) {
//            foreach ($configuration->sendResource->data as $value) {
//                $dataToMerge[(string)$value->key] = (string)$value->value;
//            }
//        }
//
//        \SrcCore\models\DatabasePDO::reset();
//        new \SrcCore\models\DatabasePDO(['customId' => $customId]);
//
//        $resources = \Resource\models\ResModel::get([
//            'select'    => ['res_id', 'subject', 'format', 'path', 'filename', 'docserver_id', 'external_id'],
//            'where'     => ["external_id->>'localeoId' is null"]
//        ]);
//
//        foreach ($resources as $resource) {
//            if (empty($resource['filename'])) {
//                self::writeLog(['message' => "[SEND_FILE] Resource {$resource['res_id']} : ({$resource['subject']}) has no file"]);
//                continue;
//            }
//
//            $docserver = \Docserver\models\DocserverModel::getByDocserverId(['docserverId' => $resource['docserver_id'], 'select' => ['path_template']]);
//            $file = file_get_contents($docserver['path_template'] . str_replace('#', '/', $resource['path']) . $resource['filename']);
//            if (empty($file)) {
//                self::writeLog(['message' => "[SEND_FILE] Resource {$resource['res_id']} : ({$resource['subject']}) file is missing"]);
//                continue;
//            }
//
//            $encodedFile = base64_encode($file);
//
//            if (!empty($config['file'])) {
//                $docserver = \Docserver\models\DocserverModel::getByDocserverId(['docserverId' => $_SESSION['indexing']['docserver_id'], 'select' => ['path_template']]);
//                $bodyData[$config['file']] = \SrcCore\models\CurlModel::makeCurlFile(['path' => $docserver['path_template'] . str_replace('#', '/', $_SESSION['indexing']['destination_dir']) . $_SESSION['indexing']['file_destination_name']]);
//            }
//
//            $body = [];
//            foreach (self::MAPPING_CONTACT as $key => $value) {
//                $body[$key] = $contact[$value] ?? '';
//            }
//            $body = array_merge($body, $dataToMerge);
//
//            $response = \SrcCore\models\CurlModel::execSimple([
//                'url'       => $url,
//                'method'    => 'NO-METHOD',
//                'headers'   => ["Api-Key: {$apiKey}", "appName: {$appName}"],
//                'body'      => [$objectName => json_encode($body)],
//                'noLogs'    => true
//            ]);
//
//            if (!empty($response['errors'])) {
//                self::writeLog(['message' => "[SEND_CONTACT] Contact {$contact['id']} : curl call failed"]);
//                self::writeLog(['message' => $response['errors']]);
//                continue;
//            } elseif (empty($response['response']['id'])) {
//                self::writeLog(['message' => "[SEND_CONTACT] Contact {$contact['id']} : id is missing"]);
//                self::writeLog(['message' => json_encode($response['response'])]);
//                continue;
//            }
//
//            $externalId = json_decode($contact['external_id'], true);
//            $externalId['localeoId'] = $response['response']['id'];
//            \Contact\models\ContactModel::update(['set' => ['external_id' => json_encode($externalId)], 'where' => ['id = ?'], 'data' => [$contact['id']]]);
//
//            self::writeLog(['message' => "[SEND_CONTACT] Contact {$contact['id']} : successfully sent to localeo"]);
//        }
//    }

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
        $file = fopen('bin/external/localeo/localeoScript.log', 'a');
        fwrite($file, '[' . date('Y-m-d H:i:s') . '] ' . $args['message'] . PHP_EOL);
        fclose($file);
    }
}
