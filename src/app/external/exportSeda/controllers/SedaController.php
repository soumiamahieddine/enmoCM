<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Seda Controller
* @author dev@maarch.org
*/

namespace ExportSeda\controllers;

use Attachment\models\AttachmentModel;
use Doctype\models\DoctypeModel;
use Email\models\EmailModel;
use Entity\models\EntityModel;
use Note\models\NoteModel;
use Resource\models\ResModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;

class SedaController
{
    public function initSeda(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route resId is not an integer']);
        }

        $resource = ResModel::getById(['resId' => $aArgs['resId'], 'select' => ['destination', 'type_id', 'subject']]);
        if (empty($resource)) {
            return $response->withStatus(400)->withJson(['errors' => 'resource does not exists']);
        } elseif (empty($resource['destination'])) {
            return $response->withStatus(400)->withJson(['errors' => 'resource has no destination', 'lang' => 'noDestination']);
        }

        $doctype = DoctypeModel::getById(['id' => $resource['type_id'], 'select' => ['description', 'retention_rule', 'retention_final_disposition']]);
        if (empty($doctype['retention_rule']) || empty($doctype['retention_final_disposition'])) {
            return $response->withStatus(400)->withJson(['errors' => 'retention_rule or retention_final_disposition is empty for doctype', 'lang' => 'noRetentionInfo']);
        }
        $entity = EntityModel::getByEntityId(['entityId' => $resource['destination'], 'select' => ['producer_service', 'entity_label', 'business_id']]);
        if (empty($entity['producer_service'])) {
            return $response->withStatus(400)->withJson(['errors' => 'producer_service is empty for this entity', 'lang' => 'noProducerService']);
        }

        $sedaXml = CoreConfigModel::getXmlLoaded(['path' => 'modules/export_seda/xml/config.xml']);
        if (empty($sedaXml->CONFIG->senderOrgRegNumber)) {
            return $response->withStatus(400)->withJson(['errors' => 'No senderOrgRegNumber found in config.xml (export_seda)']);
        }

        $return = [
            'data' => [
                'entity' => [
                    'label'              => $entity['entity_label'],
                    'siren'              => $entity['business_id'],
                    'archiveEntitySiren' => $sedaXml->CONFIG->senderOrgRegNumber
                ],
                'doctype' => [
                    'label'                     => $doctype['description'],
                    'retentionRule'             => $doctype['retention_rule'],
                    'retentionFinalDisposition' => $doctype['retention_final_disposition']
                ]
            ],
            'archiveUnit' => [
                [
                    'id'    => 'letterbox_' . $aArgs['resId'],
                    'label' => $resource['subject'],
                    'type'  => 'mainDocument'
                ]
            ]
        ];

        $notes = NoteModel::get(['select' => ['note_text', 'id'], 'where' => ['identifier = ?'], 'data' => [$aArgs['resId']]]);
        foreach ($notes as $note) {
            $return['archiveUnit'][] = [
                'id'    => 'note_' . $note['id'],
                'label' => $note['note_text'],
                'type'  => 'note'
            ];
        }

        $emails = EmailModel::get([
            'select'  => ['object', 'id'],
            'where'   => ['document->>\'id\' = ?', 'status = ?'],
            'data'    => [$aArgs['resId'], 'SENT'],
            'orderBy' => ['send_date desc']
        ]);
        foreach ($emails as $email) {
            $return['archiveUnit'][] = [
                'id'    => 'note_' . $email['id'],
                'label' => $email['object'],
                'type'  => 'email'
            ];
        }
        
        return $response->withJson($return);
    }
}
