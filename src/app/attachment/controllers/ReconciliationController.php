<?php

namespace Attachment\controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Attachment\models\AttachmentModel;
use Resource\models\ResModel;
use Respect\Validation\Validator;
use History\controllers\HistoryController;
use Resource\controllers\StoreController;
use SrcCore\models\CoreConfigModel;

class ReconciliationController
{
    public function create(Request $request, Response $response)
    {
        $data = $request->getParams();
        $check = Validator::notEmpty()->validate($data['encodedFile']);
        $check = $check && Validator::numeric()->notEmpty()->validate($data['resId']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['chrono']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $resId = ReconciliationController::getWs($data);

        if (empty($resId) || !empty($resId['errors'])) {
            return $response->withStatus(500)->withJson(['errors' => '[ReconciliationController create] ' . $resId['errors']]);
        }

        HistoryController::add([
            'tableName' => 'res_attachments',
            'recordId'  => $resId,
            'eventType' => 'ADD',
            'info'      => _DOC_ADDED,
            'moduleId'  => 'reconciliation',
            'eventId'   => 'attachmentadd',
        ]);

        return $response->withJson(['resId' => $resId]);
    }

    public static function getWs($aArgs)
    {
        $identifier     = $aArgs['chrono'];
        $res_id         = (int)$aArgs['resId'];
        $encodedContent = $aArgs['encodedFile'];

        $info = AttachmentModel::getOnView([
            'select'  => [1],
            'where'   => ['identifier = ?', "status IN ('A_TRA', 'NEW','TMP')"],
            'data'    => [$identifier],
            'orderBy' => ['res_id DESC']
        ])[0];

        if (!$info) {
            return false;
        }

        $title           = $info['title'];
        $fileFormat      = 'pdf';
        $attachment_type = 'outgoing_mail_signed';
        $collId          = 'letterbox_coll';

        $data = [];

        array_push(
            $data,
            array(
                'column' => 'title',
                'value' => $title,
                'type' => 'string',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'identifier',
                'value' => $identifier,
                'type' => 'string',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'attachment_type',
                'value' => $attachment_type,
                'type' => 'string',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'dest_contact_id',
                'value' => $info['dest_contact_id'],
                'type' => 'integer',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'dest_address_id',
                'value' => $info['dest_address_id'],
                'type' => 'integer',
            )
        );
        array_push(
            $data,
            array(
                'column' => 'coll_id',
                'value' => $collId,
                'type' => 'integer',
            )
        );

        array_push(
            $data,
            array(
                'column' => 'res_id_master',
                'value' => $res_id,
                'type' => 'integer',
            )
        );

        $aArgs = [
            'collId'      => $collId,
            'table'       => 'res_attachments',
            'encodedFile' => $encodedContent,
            'fileFormat'  => $fileFormat,
            'data'        => $data,
            'status'      => 'TRA'
        ];

        $resId = StoreController::storeResourceRes($aArgs);

        // Suppression du projet de reponse
        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'modules/attachments/xml/config.xml']);
        if ($loadedXml) {
            $reconciliationConfig    = $loadedXml->RECONCILIATION->CONFIG;
            $delete_response_project = $reconciliationConfig->delete_response_project;
            $close_incoming          = $reconciliationConfig->close_incoming;

            if ($delete_response_project == 'true') {
                AttachmentModel::update([
                    'set'   => ['status' => 'DEL'],
                    'where' => ['res_id = ?'],
                    'data'  => [$info['res_id']],
                ]);
            }

            // Cloture du courrier entrant
            if ($close_incoming == 'true') {
                ResModel::update([
                    'set'   => ['status' => 'END'],
                    'where' => ['res_id = ?'],
                    'data'  => [$res_id],
                ]);
            }
        }

        return $resId;
    }

    public function checkAttachment(Request $request, Response $response)
    {
        $data  = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['chrono']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $attachment = AttachmentModel::getOnView([
            'select'  => [1],
            'where'   => ['identifier = ?', "status IN ('A_TRA', 'NEW','TMP')"],
            'data'    => [$data['chrono']],
            'orderBy' => ['res_id DESC']
        ])[0];

        if ($attachment == false) {
            return $response->withStatus(500)->withJson(['errors' => '[ReconciliationController checkAttachment] ' . _NO_ATTACHMENT_CHRONO]);
        }else{
            return $response->withJson(array('result' => 'OK'));
        }

    }
}
