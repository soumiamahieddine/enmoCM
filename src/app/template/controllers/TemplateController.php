<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Template Controller
 * @author dev@maarch.org
 */

namespace Template\controllers;

use Docserver\controllers\DocserverController;
use Docserver\models\DocserverModel;
use Group\models\ServiceModel;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;
use Template\models\TemplateAssociationModel;
use Template\models\TemplateModel;
use Attachment\models\AttachmentModel;
use Entity\models\EntityModel;

class TemplateController
{
    public function get(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_templates', 'userId' => $GLOBALS['userId'], 'location' => 'templates', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $templates = TemplateModel::get();

        return $response->withJson(['templates' => $templates]);
    }

    public function getDetailledById(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_templates', 'userId' => $GLOBALS['userId'], 'location' => 'templates', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $template = TemplateModel::getById(['id' => $aArgs['id']]);

        $rawLinkedEntities = TemplateAssociationModel::get(['select' => ['value_field'], 'where' => ['template_id = ?'], 'data' => [$template['template_id']]]);
        $linkedEntities = [];
        foreach ($rawLinkedEntities as $rawLinkedEntity) {
            $linkedEntities[] = $rawLinkedEntity['value_field'];
        }
        $entities = EntityModel::getAllowedEntitiesByUserId(['userId' => 'superadmin']);
        foreach ($entities as $key => $entity) {
            $entities[$key]['selected'] = false;
            if (in_array($entity['id'], $linkedEntities)) {
                $entities[$key]['selected'] = true;
            }
        }
        $template['entities'] = $entities;

        $attachmentModelsTmp = AttachmentModel::getAttachmentsTypesByXML();
        $attachmentTypes = [];
        foreach ($attachmentModelsTmp as $key => $value) {
            $attachmentTypes[] = [
                'label' => $value['label'],
                'id'    => $key
            ];
        }

        return $response->withJson([
            'template'        => $template,
            'templatesModels' => TemplateController::getModels(),
            'attachmentTypes' => $attachmentTypes,
            'datasources'     => ''
        ]);
    }

    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_templates', 'userId' => $GLOBALS['userId'], 'location' => 'templates', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['template_label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['template_comment']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['template_content']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['template_type']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['template_datasource']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['template_target']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['template_attachment_type']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $id = TemplateModel::create($data);
        if (!empty($data['entities']) && is_array($data['entities'])) {
            foreach ($data['entities'] as $entity) {
                TemplateAssociationModel::create(['templateId' => $id, 'entityId' => $entity]);
            }
        }
        HistoryController::add([
            'tableName' => 'templates',
            'recordId'  => $id,
            'eventType' => 'ADD',
            'info'      => _TEMPLATE_ADDED . " : {$data['template_label']}",
            'moduleId'  => 'template',
            'eventId'   => 'templateCreation',
        ]);

        return $response->withJson(['template' => $id]);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_templates', 'userId' => $GLOBALS['userId'], 'location' => 'templates', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['template_label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['template_comment']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['template_content']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['template_type']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['template_datasource']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['template_target']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['template_attachment_type']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $template = TemplateModel::getById(['select' => [1], 'id' => $aArgs['id']]);
        if (empty($template)) {
            return $response->withStatus(400)->withJson(['errors' => 'Template does not exist']);
        }

        if (!empty($data['entities']) && is_array($data['entities'])) {
            TemplateAssociationModel::delete(['where' => ['template_id = ?'], 'data' => [$aArgs['id']]]);
            foreach ($data['entities'] as $entity) {
                TemplateAssociationModel::create(['templateId' => $aArgs['id'], 'entityId' => $entity]);
            }
        }
        unset($data['entities']);
        TemplateModel::update(['set' => $data, 'where' => ['template_id = ?'], 'data' => [$aArgs['id']]]);
        HistoryController::add([
            'tableName' => 'templates',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _TEMPLATE_UPDATED . " : {$data['template_label']}",
            'moduleId'  => 'template',
            'eventId'   => 'templateModification',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_templates', 'userId' => $GLOBALS['userId'], 'location' => 'templates', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $template = TemplateModel::getById(['select' => ['template_label'], 'id' => $aArgs['id']]);
        if (empty($template)) {
            return $response->withStatus(400)->withJson(['errors' => 'Template does not exist']);
        }

        TemplateModel::delete(['where' => ['template_id = ?'], 'data' => [$aArgs['id']]]);
        TemplateAssociationModel::delete(['where' => ['template_id = ?'], 'data' => [$aArgs['id']]]);
        HistoryController::add([
            'tableName' => 'templates',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'info'      => _TEMPLATE_DELETED . " : {$template['template_label']}",
            'moduleId'  => 'template',
            'eventId'   => 'templateSuppression',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function duplicate(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_templates', 'userId' => $GLOBALS['userId'], 'location' => 'templates', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $template = TemplateModel::getById(['id' => $aArgs['id']]);

        if (empty($template)) {
            return $response->withStatus(400)->withJson(['errors' => 'Template not found']);
        }

        if ($template['template_type'] == 'OFFICE') {
            $docserver = DocserverModel::getCurrentDocserver(['typeId' => 'TEMPLATES', 'collId' => 'templates', 'select' => ['path_template']]);

            $pathOnDocserver = DocserverController::createPathOnDocServer(['path' => $docserver['path_template']]);
            $docinfo = DocserverController::getNextFileNameInDocServer(['pathOnDocserver' => $pathOnDocserver['pathToDocServer']]);
            $docinfo['fileDestinationName'] .=  '.' . explode('.', $template['template_file_name'])[1];

            $pathToDocumentToCopy = $docserver['path_template'] . str_replace('#', DIRECTORY_SEPARATOR, $template['template_path']) . $template['template_file_name'];
            $copyResult = DocserverController::copyOnDocServer([
                'sourceFilePath'             => $pathToDocumentToCopy,
                'destinationDir'             => $docinfo['destinationDir'],
                'fileDestinationName'        => $docinfo['fileDestinationName']
            ]);
            if (!empty($copyResult['errors'])) {
                return $response->withStatus(500)->withJson(['errors' => 'Template duplication failed : ' . $copyResult['errors']]);
            }
            $template['template_path'] = str_replace(str_replace(DIRECTORY_SEPARATOR, '#', $docserver['path_template']), '', $copyResult['copyOnDocserver']['destinationDir']);
            $template['template_file_name'] = $copyResult['copyOnDocserver']['fileDestinationName'];
        }

        $template['template_label'] = 'Copie de ' . $template['template_label'];

        $templateId = TemplateModel::create($template);

        return $response->withJson(['id' => $templateId]);
    }

    public function initTemplates(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_templates', 'userId' => $GLOBALS['userId'], 'location' => 'templates', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $attachmentModelsTmp = AttachmentModel::getAttachmentsTypesByXML();
        $attachmentTypes = [];
        foreach ($attachmentModelsTmp as $key => $value) {
            $attachmentTypes[] = [
                'label' => $value['label'],
                'id'    => $key
            ];
        }

        $entities = EntityModel::getAllowedEntitiesByUserId(['userId' => 'superadmin']);
        foreach ($entities as $key => $entity) {
            $entities[$key]['selected'] = false;
        }

        return $response->withJson([
            'templatesModels' => TemplateController::getModels(),
            'attachmentTypes' => $attachmentTypes,
            'datasources'     => '',
            'entities'        => $entities,
        ]);
    }

    public static function getModels()
    {
        $customId = CoreConfigModel::getCustomId();

        $models = [];

        if (is_dir("custom/{$customId}/modules/templates/templates/styles/office/")) {
            $path = "custom/{$customId}/modules/templates/templates/styles/office/";
        } else {
            $path = 'modules/templates/templates/styles/office/';
        }
        $officeModels = scandir($path);
        foreach ($officeModels as $value) {
            if ($value != '.' && $value != '..') {
                $file = explode('.', $value);
                $models[] = [
                    'fileName'  => $file[0],
                    'fileExt'   => strtoupper($file[1]),
                    'filePath'  => $path . $value,
                ];
            }
        }

        if (is_dir("custom/{$customId}/modules/templates/templates/styles/open_document/")) {
            $path = "custom/{$customId}/modules/templates/templates/styles/open_document/";
        } else {
            $path = 'modules/templates/templates/styles/open_document/';
        }
        $openModels = scandir($path);
        foreach ($openModels as $value) {
            if ($value != '.' && $value != '..') {
                $file = explode('.', $value);
                $models[] = [
                    'fileName'  => $file[0],
                    'fileExt'   => strtoupper($file[1]),
                    'filePath'  => $path . $value,
                ];
            }
        }
        if (is_dir("custom/{$customId}/modules/templates/templates/styles/txt/")) {
            $path = "custom/{$customId}/modules/templates/templates/styles/txt/";
        } else {
            $path = 'modules/templates/templates/styles/txt/';
        }

        $txtModels = scandir($path);
        foreach ($txtModels as $value) {
            if ($value != '.' && $value != '..') {
                $file = explode('.', $value);
                $models[] = [
                    'fileName'  => $file[0],
                    'fileExt'   => strtoupper($file[1]),
                    'filePath'  => $path . $value,
                ];
            }
        }

        return $models;
    }
}
