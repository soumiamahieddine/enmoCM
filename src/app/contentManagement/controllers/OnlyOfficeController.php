<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Only Office Controller
 *
 * @author dev@maarch.org
 */

namespace ContentManagement\controllers;

use Attachment\models\AttachmentModel;
use Docserver\models\DocserverModel;
use Resource\controllers\ResController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\UrlController;
use SrcCore\models\CoreConfigModel;
use Template\models\TemplateModel;

class OnlyOfficeController
{
    public static function getConfiguration(Request $request, Response $response)
    {
        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/documentEditorsConfig.xml']);

        if (empty($loadedXml) || empty($loadedXml->onlyoffice->enabled) || $loadedXml->onlyoffice->enabled == 'false' || empty($loadedXml->onlyoffice->server_uri)) {
            return $response->withJson(['enabled' => false]);
        }

        $coreUrl = str_replace('rest/', '', UrlController::getCoreUrl());

        return $response->withJson(['enabled' => true, 'serverUri' => (string)$loadedXml->onlyoffice->server_uri, 'coreUrl' => $coreUrl]);
    }

    public static function saveMergedFile(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['onlyOfficeKey'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query params onlyOfficeKey is empty']);
        } elseif (!preg_match('/[A-Za-z0-9]/i', $body['onlyOfficeKey'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query params onlyOfficeKey is forbidden']);
        }

        if ($body['objectType'] == 'templateCreation') {
            $path = null;
            $fileContent = null;
        } elseif ($body['objectType'] == 'templateModification') {
            $docserver = DocserverModel::getCurrentDocserver(['typeId' => 'TEMPLATES', 'collId' => 'templates', 'select' => ['path_template']]);
            $template = TemplateModel::getById(['id' => $body['objectId'], 'select' => ['template_path', 'template_file_name']]);
            if (empty($template)) {
                return $response->withStatus(400)->withJson(['errors' => 'Template does not exist']);
            }

            $path = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $template['template_path']) . $template['template_file_name'];
            $fileContent = file_get_contents($path);
        } elseif ($body['objectType'] == 'resourceCreation' || $body['objectType'] == 'attachmentCreation') {
            $docserver = DocserverModel::getCurrentDocserver(['typeId' => 'TEMPLATES', 'collId' => 'templates', 'select' => ['path_template']]);
            $template = TemplateModel::getById(['id' => $body['objectId'], 'select' => ['template_path', 'template_file_name']]);
            if (empty($template)) {
                return $response->withStatus(400)->withJson(['errors' => 'Template does not exist']);
            }

            $path = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $template['template_path']) . $template['template_file_name'];

            $dataToMerge = ['userId' => $GLOBALS['id']];
            if (!empty($body['data']) && is_array($body['data'])) {
                $dataToMerge = array_merge($dataToMerge, $body['data']);
            }
            $mergedDocument = MergeController::mergeDocument([
                'path' => $path,
                'data' => $dataToMerge
            ]);
            $fileContent = base64_decode($mergedDocument['encodedDocument']);
        } elseif ($body['objectType'] == 'attachmentModification') {
            $attachment = AttachmentModel::getById(['id' => $body['objectId'], 'select' => ['docserver_id', 'path', 'filename', 'res_id_master']]);
            if (empty($attachment)) {
                return $response->withStatus(400)->withJson(['errors' => 'Attachment does not exist']);
            }
            if (!ResController::hasRightByResId(['resId' => [$attachment['res_id_master']], 'userId' => $GLOBALS['id']])) {
                return $response->withStatus(400)->withJson(['errors' => 'Attachment out of perimeter']);
            }

            $docserver  = DocserverModel::getByDocserverId(['docserverId' => $attachment['docserver_id'], 'select' => ['path_template']]);

            $path = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $attachment['path']) . $attachment['filename'];
            $fileContent = file_get_contents($path);
        } else {
            return $response->withStatus(400)->withJson(['errors' => 'Query param objectType does not exist']);
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $tmpPath = CoreConfigModel::getTmpPath();
        $filename = "onlyOffice_{$GLOBALS['id']}_{$body['onlyOfficeKey']}.{$extension}";

        $put = file_put_contents($tmpPath . $filename, $fileContent);
        if ($put === false) {
            return $response->withStatus(400)->withJson(['errors' => 'File put contents failed']);
        }

        $halfFilename = substr($filename, 11);
        return $response->withJson(['filename' => $halfFilename]);
    }

    public static function getMergedFile(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        if (!Validator::stringType()->notEmpty()->validate($queryParams['filename'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query params filename is empty']);
        } elseif (substr_count($queryParams['filename'], '\\') > 0 || substr_count($queryParams['filename'], '.') != 1) {
            return $response->withStatus(400)->withJson(['errors' => 'Query params filename forbidden']);
        }

        $tmpPath = CoreConfigModel::getTmpPath();
        $filename = "onlyOffice_{$queryParams['filename']}";

        $fileContent = file_get_contents($tmpPath . $filename);
        if ($fileContent == false) {
            return $response->withStatus(400)->withJson(['errors' => 'No content found']);
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($fileContent);
        $extension = pathinfo($tmpPath . $filename, PATHINFO_EXTENSION);
        unlink($tmpPath . $filename);

        $response->write($fileContent);
        $response = $response->withAddedHeader('Content-Disposition', "attachment; filename=maarch.{$extension}");

        return $response->withHeader('Content-Type', $mimeType);
    }

    public static function getEncodedFileFromUrl(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        if (!Validator::stringType()->notEmpty()->validate($queryParams['url'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query params url is empty']);
        }

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/documentEditorsConfig.xml']);
        if (empty($loadedXml) || empty($loadedXml->onlyoffice->enabled) || $loadedXml->onlyoffice->enabled == 'false' || empty($loadedXml->onlyoffice->server_uri)) {
            return $response->withStatus(400)->withJson(['errors' => 'Onlyoffice is not enabled']);
        }

        if (strpos($queryParams['url'], (string)$loadedXml->onlyoffice->server_uri .'/cache/files/') !== 0) {
            return $response->withStatus(400)->withJson(['errors' => 'Query params url is not allowed']);
        }

        $fileContent = file_get_contents($queryParams['url']);
        if ($fileContent == false) {
            return $response->withStatus(400)->withJson(['errors' => 'No content found']);
        }

        return $response->withJson(['encodedFile' => base64_encode($fileContent)]);
    }
}
