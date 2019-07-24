<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Folder Controller
 *
 * @author dev@maarch.org
 */

namespace Folder\controllers;

use Folder\models\EntityFolderModel;
use Folder\models\FolderModel;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use User\models\UserModel;

class FolderController
{
    public function get(Request $request, Response $response)
    {
        $folders = FolderModel::get();

        foreach ($folders as $key => $value) {
            $folders[$key]['icon'] = "fa fa-folder-open";
            if (empty($value['parent_id'])) {
                $folders[$key]['parent'] = '#';
            } else {
                $folders[$key]['parent'] = $value['parent_id'];
            }

            $folders[$key]['state']['opened'] = true;
            $folders[$key]['text'] = $value['label'];
        }

        return $response->withJson(['folders' => $folders]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::numeric()->notEmpty()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query id is empty or not an integer']);
        }

        //TODO Check rights

        $folder = FolderModel::getById(['id' => $aArgs['id']]);
        if (empty($folder)) {
            return $response->withStatus(400)->withJson(['errors' => 'Folder not found']);
        }

        $folder['sharing']['entities'] = [];
        if ($folder['public']) {
            $entitiesFolder = EntityFolderModel::getByFolderId(['folder_id' => $aArgs['id']]);
            foreach ($entitiesFolder as $value) {
                $folder['sharing']['entities'][] = ['entity_id' => $value['entity_id'], 'edition' => $value['edition']];
            }
        }

        return $response->withJson(['folder' => $folder]);
    }

    public function create(Request $request, Response $response)
    {
        $data = $request->getParams();

        if (!Validator::stringType()->notEmpty()->validate($data['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string']);
        }
        if (!empty($data['parent_id']) && !Validator::intval()->validate($data['parent_id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body parent_id is not a numeric']);
        }
        if (!Validator::boolVal()->validate($data['public'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body public is empty or not a boolean']);
        }

        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        //TODO Check rights

        $id = FolderModel::create([
            'label'      => $data['label'],
            'public'     => $data['public'],
            'user_id'    => $currentUser['id'],
            'parent_id'  => $data['parent_id']
        ]);

        if (!empty($data['sharing']['entities'])) {
            //TODO check entities exists

            foreach ($data['sharing']['entities'] as $entity) {
                EntityFolderModel::create([
                    'folder_id' => $id,
                    'entity_id' => $entity['entity_id'],
                    'edition'   => $entity['edition'],
                ]);
            }
        }

        HistoryController::add([
            'tableName' => 'folders',
            'recordId'  => $id,
            'eventType' => 'ADD',
            'info'      => _FOLDER_CREATION . " : {$id}",
            'moduleId'  => 'folder',
            'eventId'   => 'folderCreation',
        ]);

        return $response->withJson(['folder' => $id]);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        $data = $request->getParams();

        if (!Validator::numeric()->notEmpty()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query id is empty or not an integer']);
        }
        if (!Validator::stringType()->notEmpty()->validate($data['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string']);
        }
        if (!empty($data['parent_id']) &&!Validator::intval()->validate($data['parent_id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body parent_id is not a numeric']);
        }
        if (!Validator::boolVal()->validate($data['public'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body public is empty or not a boolean']);
        }

        //TODO Check rights

        FolderModel::update([
            'set' => [
                'label'      => $data['label'],
                'public'     => empty($data['public']) ? 'false' : 'true',
                'parent_id'  => $data['parent_id']
            ],
            'where' => ['id = ?'],
            'data' => [$aArgs['id']]
        ]);

        if ($data['public']) {
            if (!empty($data['sharing']['entities'])) {
                //TODO check entities exists

                foreach ($data['sharing']['entities'] as $entity) {
                    EntityFolderModel::deleteByFolderId(['folder_id' => $aArgs['id']]);
                    EntityFolderModel::create([
                        'folder_id' => $aArgs['id'],
                        'entity_id' => $entity['entity_id'],
                        'edition'   => $entity['edition'],
                    ]);
                }
                // TODO share subfolders
            }
        } else {
            EntityFolderModel::deleteByFolderId(['folder_id' => $aArgs['id']]);
            // TODO unshare subfolders
        }

        HistoryController::add([
            'tableName' => 'folders',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _FOLDER_MODIFICATION . " : {$aArgs['id']}",
            'moduleId'  => 'folder',
            'eventId'   => 'folderModification',
        ]);

        return $response->withStatus(200);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::numeric()->notEmpty()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query id is empty or not an integer']);
        }

        //TODO Check rights

        FolderModel::delete(['id' => $aArgs['id']]);
        EntityFolderModel::deleteByFolderId(['folder_id' => $aArgs['id']]);
        
        //TODO Delete sub folders
        //TODO Delete resources folders

        HistoryController::add([
            'tableName' => 'folder',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'info'      => _BASKET_SUPPRESSION . " : {$aArgs['id']}",
            'moduleId'  => 'folder',
            'eventId'   => 'folderSuppression',
        ]);

        return $response->withStatus(200);
    }
}
