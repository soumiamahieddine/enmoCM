<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Attachments Controller
* @author dev@maarch.org
* @ingroup attachments
*/

namespace Attachments\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Attachments\Models\AttachmentsModel;
use Core\Controllers\ResController;

require_once 'modules/attachments/Models/AttachmentsModel.php';

class AttachmentsController
{
    
    public function storeAttachmentResource($aArgs)
    {
        if (empty($aArgs['resId'])) {

            return ['errors' => 'resId ' . _EMPTY];
        }

        if (empty($aArgs['encodedFile'])) {

            return ['errors' => 'encodedFile ' . _EMPTY];
        }

        if (empty($aArgs['collId'])) {

            return ['errors' => 'collId ' . _EMPTY];
        }

        if (empty($aArgs['table'])) {

            return ['errors' => 'table ' . _EMPTY];
        }

        if (empty($aArgs['fileFormat'])) {

            return ['errors' => 'fileFormat ' . _EMPTY];
        }

        if (empty($aArgs['title'])) {

            return ['errors' => 'title ' . _EMPTY];
        }

        $resId = $aArgs['resId'];
        $encodedFile = $aArgs['encodedFile'];
        $collId = $aArgs['collId'];
        $table = $aArgs['table'];
        $fileFormat = $aArgs['fileFormat'];
        $title = $aArgs['title'];

        if (!empty($aArgs['data'])) {

            $data = $aArgs['data'];
        } else {
            $data = [];
        }
        
        array_push(
            $data,
            array(
                'column' => "typist",
                'value' => $_SESSION['user']['UserId'],
                'type' => "string",
            )
        );

        array_push(
            $data,
            array(
                'column' => "title",
                'value' => strtolower($title),
                'type' => "string",
            )
        );
        array_push(
            $data,
            array(
                'column' => "coll_id",
                'value' => $collId,
                'type' => "string",
            )
        );
        array_push(
            $data,
            array(
                'column' => "res_id_master",
                'value' => $resId,
                'type' => "integer",
            )
        );
        array_push(
            $data,
            array(
                'column' => "type_id",
                'value' => 0,
                'type' => "int",
            )
        );

        $aArgs = [
            'encodedFile'   => $encodedFile,
            'data'          => $data,
            'collId'        => $collId,
            'table'         => $table,
            'fileFormat'    => $fileFormat,
            'status'        => 'NEW',
        ];

        $res = new \Core\Controllers\ResController();
        $response = $res->storeResource($aArgs);

        print_r($response);exit;

    }

}