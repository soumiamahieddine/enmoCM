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

        if (empty($aArgs['data'])) {

            return ['errors' => 'data ' . _EMPTY];
        }

        if (empty($aArgs['collId'])) {

            return ['errors' => 'collId ' . _EMPTY];
        }

        if (empty($aArgs['collIdMaster'])) {

            return ['errors' => 'collIdMaster ' . _EMPTY];
        }

        if (empty($aArgs['table'])) {

            return ['errors' => 'table ' . _EMPTY];
        }

        if (empty($aArgs['fileFormat'])) {

            return ['errors' => 'fileFormat ' . _EMPTY];
        }

        $resId = $aArgs['resId'];
        $encodedFile = $aArgs['encodedFile'];
        $collId = $aArgs['collId'];
        $collIdMaster = $aArgs['collIdMaster'];
        $table = $aArgs['table'];
        $fileFormat = $aArgs['fileFormat'];

        $aArgs = [
            'data'   => $aArgs['data'],
            'collIdMaster' => $collIdMaster,
            'resId'  => $resId,
        ];
        
        $returnPrepare = $this->prepareStorage($aArgs);
        
        $aArgs = [
            'encodedFile'   => $encodedFile,
            'data'          => $returnPrepare['data'],
            'collId'        => $collId,
            'table'         => $table,
            'fileFormat'    => $fileFormat,
            'status'        => $returnPrepare['status'],
        ];

        $res = new \Core\Controllers\ResController();
        $response = $res->storeResource($aArgs);

        //return $response;
        if (!is_numeric($response[0])) {

            return ['errors' => 'Pb with SQL insertion : ' . $response[0]];
        } else {
            require_once 'core/class/class_history.php';
            require_once 'core/class/class_security.php';
            $hist = new \history();
            $sec = new \security();
            $view = $sec->retrieve_view_from_coll_id($collIdMaster);
            
            $hist->add(
                $view, $resId, "ADD", 'attachadd',
                ucfirst(_DOC_NUM) . $response[0] . ' '
                . _NEW_ATTACH_ADDED . ' ' . _TO_MASTER_DOCUMENT
                . $resId,
                $_SESSION['config']['databasetype'],
                'apps'
            );
            $hist->add(
                $table, $response[0], "ADD",'attachadd',
                _NEW_ATTACH_ADDED,
                $_SESSION['config']['databasetype'],
                'attachments'
            );
        }

        if ($response[0] == 0) {
            $response[0] = '';
        }

        return [$response[0]];
    }

    /**
     * Prepares storage on database.
     * @param  $data array
     * @param  $resId bigint
     * @param  $collIdMaster string
     * @return $data
     */
    public function prepareStorage($aArgs)
    {
        if (empty($aArgs['data'])) {

            return ['errors' => 'data ' . _EMPTY];
        }

        if (empty($aArgs['resId'])) {

            return ['errors' => 'resId ' . _EMPTY];
        }

        if (empty($aArgs['collIdMaster'])) {

            return ['errors' => 'collIdMaster ' . _EMPTY];
        }

        $statusFound = false;
        $typistFound = false;
        $typeIdFound = false;
        $attachmentTypeFound = false;
        
        $data = $aArgs['data'];
        
        $countD = count($data);
        for ($i=0;$i<$countD;$i++) {

            if (
                strtoupper($data[$i]['type']) == 'INTEGER' || 
                strtoupper($data[$i]['type']) == 'FLOAT'
            ) {
                if ($data[$i]['value'] == '') {
                    $data[$i]['value'] = '0';
                }
            }

            if (strtoupper($data[$i]['type']) == 'STRING') {
               $data[$i]['value'] = $data[$i]['value'];
               $data[$i]['value'] = str_replace(";", "", $data[$i]['value']);
               $data[$i]['value'] = str_replace("--", "", $data[$i]['value']);
            }

            if (strtoupper($data[$i]['column']) == strtoupper('status')) {
                $statusFound = true;
                $status = $data[$i]['value'];
            }

            if (strtoupper($data[$i]['column']) == strtoupper('typist')) {
                $typistFound = true;
            }

            if (strtoupper($data[$i]['column']) == strtoupper('type_id')) {
                $typeIdFound = true;
            }

            if (strtoupper($data[$i]['column']) == strtoupper('attachment_type')) {
                $attachmentTypeFound = true;
            }
        }

        if (!$typistFound) {
            array_push(
                $data,
                array(
                    'column' => 'typist',
                    'value' => $_SESSION['user']['UserId'],
                    'type' => 'string',
                )
            );
        }

        if (!$typeIdFound) {
            array_push(
                $data,
                array(
                    'column' => 'type_id',
                    'value' => 0,
                    'type' => 'int',
                )
            );
        }
        
        if (!$statusFound) {
            array_push(
                $data,
                array(
                    'column' => 'status',
                    'value' => 'NEW',
                    'type' => 'string',
                )
            );
            $status = 'NEW';
        }
        
        if (!$attachmentTypeFound) {
            array_push(
                $data,
                array(
                    'column' => 'attachment_type',
                    'value' => 'response_project',
                    'type' => 'string',
                )
            );
        }

        //BASICS
        array_push(
            $data,
            array(
                'column' => "coll_id",
                'value' => $aArgs['collIdMaster'],
                'type' => "string",
            )
        );

        array_push(
            $data,
            array(
                'column' => "res_id_master",
                'value' => $aArgs['resId'],
                'type' => "integer",
            )
        );

        array_push(
            $data,
            array(
                'column' => "relation",
                'value' => 1,
                'type' => "integer",
            )
        );

        $return = [
            'data'   => $data,
            'status' => $status,
        ];

        return $return;
    }

}