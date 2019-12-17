<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Online Editor Controller
 *
 * @author dev@maarch.org
 */

namespace ContentManagement\controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;

class DocumentEditorController
{
    const DOCUMENT_EDITION_METHODS = ['java', 'onlyoffice'];

    public static function get(Request $request, Response $response)
    {
        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/documentEditorsConfig.xml']);

        $allowedMethods = [];
        foreach (self::DOCUMENT_EDITION_METHODS as $method) {
            if (!empty($loadedXml->$method->enabled) || $loadedXml->$method->enabled == 'true') {
                $allowedMethods[] = $method;
            }
        }

        return $response->withJson($allowedMethods);
    }
}
