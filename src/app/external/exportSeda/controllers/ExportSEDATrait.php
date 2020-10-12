<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ExportSEDATrait
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace ExportSeda\controllers;

use SrcCore\models\ValidatorModel;

trait ExportSEDATrait
{
    public static function sendToRecordManagement(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        // TODO : CONTROL + GET DATAS
        $data = [];

        $controller = ExportSEDATrait::generateSEDAPackage(['data' => $data]);
        if (!empty($controller['errors'])) {
            return ['errors' => [$controller['errors']]];
        }

        // TODO : SEND PACKAGE TO RM

        return true;
    }

    public static function generateSEDAPackage(array $args)
    {
        $encodedFile = '';
        
        return ['encodedFile' => $encodedFile];
    }
}
