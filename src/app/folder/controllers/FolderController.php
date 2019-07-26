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

use Attachment\models\AttachmentModel;
use Entity\models\EntityModel;
use Folder\models\EntityFolderModel;
use Folder\models\FolderModel;
use Folder\models\ResourceFolderModel;
use Group\models\ServiceModel;
use History\controllers\HistoryController;
use Resource\controllers\ResController;
use Resource\controllers\ResourceListController;
use Resource\models\ResourceListModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class FolderController
{
    public function get(Request $request, Response $response)
    {
        $folders = FolderController::getScopeFolders(['login' => $GLOBALS['userId']]);

        $userEntities = EntityModel::getEntitiesByUserId([
            'select'  => ['entities.id'],
            'user_id' => $GLOBALS['userId']
        ]);

        $userEntities = array_column($userEntities, 'id');
        if (empty($userEntities)) {
            $userEntities = 0;
        }

        $foldersWithResources = FolderModel::getWithEntitiesAndResources([
            'select'   => ['COUNT(resources_folders.folder_id)', 'resources_folders.folder_id'],
            'where'    => ['(entities_folders.entity_id in (?) OR folders.user_id = ?)'],
            'data'     => [$userEntities, $GLOBALS['id']],
            'groupBy'  => ['resources_folders.folder_id']
        ]);

        $tree = [];
        foreach ($folders as $folder) {
            $key = array_keys(array_column($foldersWithResources, 'folder_id'), $folder['id']);
            $count = 0;
            if (isset($key[0])) {
                $count = $foldersWithResources[$key[0]]['count'];
            }
            $insert = [
                'name'       => $folder['label'],
                'id'         => $folder['id'],
                'label'      => $folder['label'],
                'public'     => $folder['public'],
                'user_id'    => $folder['user_id'],
                'parent_id'  => $folder['parent_id'],
                'level'      => $folder['level'],
                'countResources' => $count
            ];
            if ($folder['level'] == 0) {
                $tree[] = $insert;
            } else {
                $found = false;
                foreach ($tree as $key => $branch) {
                    if ($branch['id'] == $folder['parent_id']) {
                        array_splice($tree, $key + 1, 0, [$insert]);
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $tree[] = $insert;
                }
            }
        }

        return $response->withJson(['folders' => $tree]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        if (!Validator::numeric()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $folder = FolderController::getScopeFolders(['login' => $GLOBALS['userId'], 'folderId' => $args['id']]);
        if (empty($folder[0])) {
            return $response->withStatus(400)->withJson(['errors' => 'Folder not found or out of your perimeter']);
        }

        $folder = $folder[0];

        $folder['sharing']['entities'] = [];
        if ($folder['public']) {
            $entitiesFolder = EntityFolderModel::getByFolderId(['folder_id' => $args['id']]);
            foreach ($entitiesFolder as $value) {
                $folder['sharing']['entities'][] = ['entity_id' => $value['entity_id'], 'edition' => $value['edition']];
            }
        }

        return $response->withJson(['folder' => $folder]);
    }

    public function create(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($data['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string']);
        }
        if (!empty($data['parent_id']) && !Validator::intval()->validate($data['parent_id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body parent_id is not a numeric']);
        }

        if (empty($data['parent_id'])) {
            $data['parent_id'] = 0;
            $owner  = $GLOBALS['id'];
            $public = false;
            $level  = 0;
        } else {
            $folder = FolderController::getScopeFolders(['login' => $GLOBALS['userId'], 'folderId' => $data['parent_id'], 'edition' => true]);
            if (empty($folder[0])) {
                return $response->withStatus(400)->withJson(['errors' => 'Parent Folder not found or out of your perimeter']);
            }
            $owner  = $folder[0]['user_id'];
            $public = $folder[0]['public'];
            $level  = $folder[0]['level'] + 1;
        }

        $id = FolderModel::create([
            'label'     => $data['label'],
            'public'    => $public,
            'user_id'   => $owner,
            'parent_id' => $data['parent_id'],
            'level'     => $level
        ]);

        if ($public && !empty($data['parent_id'])) {
            $entitiesSharing = EntityFolderModel::getByFolderId(['folder_id' => $data['parent_id']]);
            foreach ($entitiesSharing as $entity) {
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
            'info'      => _FOLDER_CREATION . " : {$data['label']}",
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

        $folder = FolderController::getScopeFolders(['login' => $GLOBALS['userId'], 'folderId' => $aArgs['id'], 'edition' => true]);
        if (empty($folder[0])) {
            return $response->withStatus(400)->withJson(['errors' => 'Folder not found or out of your perimeter']);
        }

        if (empty($data['parent_id'])) {
            $data['parent_id'] = 0;
            $level = 0;
        } else {
            $folder = FolderController::getScopeFolders(['login' => $GLOBALS['userId'], 'folderId' => $data['parent_id']]);
            if (empty($folder[0])) {
                return $response->withStatus(400)->withJson(['errors' => 'Parent Folder not found or out of your perimeter']);
            }
            $level = $folder[0]['level'] + 1;
        }

        FolderModel::update([
            'set' => [
                'label'      => $data['label'],
                'parent_id'  => $data['parent_id'],
                'level'      => $level
            ],
            'where' => ['id = ?'],
            'data' => [$aArgs['id']]
        ]);

        HistoryController::add([
            'tableName' => 'folders',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _FOLDER_MODIFICATION . " : {$data['label']}",
            'moduleId'  => 'folder',
            'eventId'   => 'folderModification',
        ]);

        return $response->withStatus(200);
    }

    public function sharing(Request $request, Response $response, array $aArgs)
    {
        $data = $request->getParams();

        if (!Validator::numeric()->notEmpty()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query id is empty or not an integer']);
        }
        if (!Validator::boolVal()->validate($data['public'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body public is empty or not a boolean']);
        }
        if ($data['public'] && !isset($data['sharing']['entities'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body sharing/entities does not exists']);
        }

        DatabaseModel::beginTransaction();
        $sharing = FolderController::folderSharing(['folderId' => $aArgs['id'], 'public' => $data['public'], 'sharing' => $data['sharing']]);
        if (!$sharing) {
            DatabaseModel::rollbackTransaction();
            return $response->withStatus(400)->withJson(['errors' => 'Can not share/unshare folder because almost one folder is out of your perimeter']);
        }
        DatabaseModel::commitTransaction();

        HistoryController::add([
            'tableName' => 'folders',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _FOLDER_SHARING_MODIFICATION . " : {$data['label']}",
            'moduleId'  => 'folder',
            'eventId'   => 'folderModification',
        ]);

        return $response->withStatus(200);
    }

    public function folderSharing($aArgs = [])
    {
        $folder = FolderController::getScopeFolders(['login' => $GLOBALS['userId'], 'folderId' => $aArgs['folderId'], 'edition' => true]);
        if (empty($folder[0])) {
            return false;
        }

        FolderModel::update([
            'set' => [
                'public' => empty($aArgs['public']) ? 'false' : 'true',
            ],
            'where' => ['id = ?'],
            'data' => [$aArgs['folderId']]
        ]);

        EntityFolderModel::deleteByFolderId(['folder_id' => $aArgs['folderId']]);

        if ($aArgs['public'] && !empty($aArgs['sharing']['entities'])) {
            foreach ($aArgs['sharing']['entities'] as $entity) {
                EntityFolderModel::create([
                    'folder_id' => $aArgs['folderId'],
                    'entity_id' => $entity['entity_id'],
                    'edition'   => $entity['edition'],
                ]);
            }
        }

        $folderChild = FolderModel::getChild(['id' => $aArgs['folderId'], 'select' => ['id']]);
        if (!empty($folderChild)) {
            foreach ($folderChild as $child) {
                $sharing = FolderController::folderSharing(['folderId' => $child['id'], 'public' => $aArgs['public'], 'sharing' => $aArgs['sharing']]);
                if (!$sharing) {
                    return false;
                }
            }
        }
        return true;
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::numeric()->notEmpty()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query id is empty or not an integer']);
        }

        $folder = FolderController::getScopeFolders(['login' => $GLOBALS['userId'], 'folderId' => $aArgs['id'], 'edition' => true]);
        
        DatabaseModel::beginTransaction();
        $deletion = FolderController::folderDeletion(['folderId' => $aArgs['id']]);
        if (!$deletion) {
            DatabaseModel::rollbackTransaction();
            return $response->withStatus(400)->withJson(['errors' => 'Can not delete because almost one folder is out of your perimeter']);
        }
        DatabaseModel::commitTransaction();

        HistoryController::add([
            'tableName' => 'folder',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'info'      => _FOLDER_SUPPRESSION . " : {$folder[0]['label']}",
            'moduleId'  => 'folder',
            'eventId'   => 'folderSuppression',
        ]);

        return $response->withStatus(200);
    }

    public static function folderDeletion(array $aArgs = [])
    {
        $folder = FolderController::getScopeFolders(['login' => $GLOBALS['userId'], 'folderId' => $aArgs['folderId'], 'edition' => true]);
        if (empty($folder[0])) {
            return false;
        }

        FolderModel::delete(['where' => ['id = ?'], 'data' => [$aArgs['folderId']]]);
        EntityFolderModel::deleteByFolderId(['folder_id' => $aArgs['folderId']]);
        ResourceFolderModel::delete(['where' => ['folder_id = ?'], 'data' => [$aArgs['folderId']]]);

        $folderChild = FolderModel::getChild(['id' => $aArgs['folderId'], 'select' => ['id']]);
        if (!empty($folderChild)) {
            foreach ($folderChild as $child) {
                $deletion = FolderController::folderDeletion(['folderId' => $child['id']]);
                if (!$deletion) {
                    return false;
                }
            }
        }
        return true;
    }

    public function getResourcesById(Request $request, Response $response, array $args)
    {
        if (!Validator::numeric()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        if (!FolderController::hasFolder(['id' => $args['id'], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Folder out of perimeter']);
        }

        $foldersResources = ResourceFolderModel::get(['select' => ['res_id'], 'where' => ['folder_id = ?'], 'data' => [$args['id']]]);
        $foldersResources = array_column($foldersResources, 'res_id');

        $formattedResources = [];
        $count = 0;
        if (!empty($foldersResources)) {
            $queryParams = $request->getQueryParams();
            $queryParams['offset'] = (empty($queryParams['offset']) || !is_numeric($queryParams['offset']) ? 0 : (int)$queryParams['offset']);
            $queryParams['limit'] = (empty($queryParams['limit']) || !is_numeric($queryParams['limit']) ? 10 : (int)$queryParams['limit']);

            $allQueryData = ResourceListController::getResourcesListQueryData(['data' => $queryParams]);
            if (!empty($allQueryData['order'])) {
                $data['order'] = $allQueryData['order'];
            }

            $rawResources = ResourceListModel::getOnView([
                'select'    => ['res_id'],
                'table'     => $allQueryData['table'],
                'leftJoin'  => $allQueryData['leftJoin'],
                'where'     => array_merge(['res_id in (?)'], $allQueryData['where']),
                'data'      => array_merge([$foldersResources], $allQueryData['queryData']),
                'orderBy'   => empty($data['order']) ? ['creation_date'] : [$data['order']]
            ]);

            $resIds = ResourceListController::getIdsWithOffsetAndLimit(['resources' => $rawResources, 'offset' => $queryParams['offset'], 'limit' => $queryParams['limit']]);

            $formattedResources = [];
            if (!empty($resIds)) {
                $excludeAttachmentTypes = ['converted_pdf', 'print_folder'];
                if (!ServiceModel::hasService(['id' => 'view_documents_with_notes', 'userId' => $GLOBALS['userId'], 'location' => 'attachments', 'type' => 'use'])) {
                    $excludeAttachmentTypes[] = 'document_with_notes';
                }

                $attachments = AttachmentModel::getOnView([
                    'select'    => ['COUNT(res_id)', 'res_id_master'],
                    'where'     => ['res_id_master in (?)', 'status not in (?)', 'attachment_type not in (?)', '((status = ? AND typist = ?) OR status != ?)'],
                    'data'      => [$resIds, ['DEL', 'OBS'], $excludeAttachmentTypes, 'TMP', $GLOBALS['userId'], 'TMP'],
                    'groupBy'   => ['res_id_master']
                ]);

                $select = [
                    'res_letterbox.res_id', 'res_letterbox.subject', 'res_letterbox.barcode', 'mlb_coll_ext.alt_identifier',
                    'status.label_status AS "status.label_status"', 'status.img_filename AS "status.img_filename"', 'priorities.color AS "priorities.color"'
                ];
                $tableFunction = ['status', 'mlb_coll_ext', 'priorities'];
                $leftJoinFunction = ['res_letterbox.status = status.id', 'res_letterbox.res_id = mlb_coll_ext.res_id', 'res_letterbox.priority = priorities.id'];

                $order = 'CASE res_letterbox.res_id ';
                foreach ($resIds as $key => $resId) {
                    $order .= "WHEN {$resId} THEN {$key} ";
                }
                $order .= 'END';

                $resources = ResourceListModel::getOnResource([
                    'select'    => $select,
                    'table'     => $tableFunction,
                    'leftJoin'  => $leftJoinFunction,
                    'where'     => ['res_letterbox.res_id in (?)'],
                    'data'      => [$resIds],
                    'orderBy'   => [$order]
                ]);

                $formattedResources = ResourceListController::getFormattedResources([
                    'resources'     => $resources,
                    'userId'        => $GLOBALS['id'],
                    'attachments'   => $attachments,
                    'checkLocked'   => false
                ]);
            }

            $count = count($rawResources);
        }

        return $response->withJson(['resources' => $formattedResources, 'countResources' => $count]);
    }

    public function addResourcesById(Request $request, Response $response, array $args)
    {
        if (!Validator::numeric()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        if (!FolderController::hasFolder(['id' => $args['id'], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Folder out of perimeter']);
        }

        $foldersResources = ResourceFolderModel::get(['select' => ['res_id'], 'where' => ['folder_id = ?'], 'data' => [$args['id']]]);
        $foldersResources = array_column($foldersResources, 'res_id');

        $body = $request->getParsedBody();
        if (!Validator::arrayType()->notEmpty()->validate($body['resources'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body resources is empty or not an array']);
        }

        $resourcesToClassify = array_diff($body['resources'], $foldersResources);
        if (empty($resourcesToClassify)) {
            return $response->withJson(['countResources' => count($foldersResources)]);
        }

        if (!ResController::hasRightByResId(['resId' => $resourcesToClassify, 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Resources out of perimeter']);
        }

        foreach ($resourcesToClassify as $value) {
            ResourceFolderModel::create(['folder_id' => $args['id'], 'res_id' => $value]);
        }

        return $response->withJson(['countResources' => count($foldersResources) + count($resourcesToClassify)]);
    }

    public function removeResourcesById(Request $request, Response $response, array $args)
    {
        if (!Validator::numeric()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        if (!FolderController::hasFolder(['id' => $args['id'], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Folder out of perimeter']);
        }

        $foldersResources = ResourceFolderModel::get(['select' => ['res_id'], 'where' => ['folder_id = ?'], 'data' => [$args['id']]]);
        $foldersResources = array_column($foldersResources, 'res_id');

        $body = $request->getParsedBody();
        if (!Validator::arrayType()->notEmpty()->validate($body['resources'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body resources is empty or not an array']);
        }

        $resourcesToUnclassify = array_intersect($foldersResources, $body['resources']);
        if (empty($resourcesToUnclassify)) {
            return $response->withJson(['countResources' => count($foldersResources)]);
        }

        if (!ResController::hasRightByResId(['resId' => $resourcesToUnclassify, 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Resources out of perimeter']);
        }

        foreach ($resourcesToUnclassify as $value) {
            ResourceFolderModel::delete(['where' => ['folder_id = ?', 'res_id = ?'], 'data' => [$args['id'], $value]]);
        }

        return $response->withJson(['countResources' => count($foldersResources) - count($resourcesToUnclassify)]);
    }

    // login (string) : Login of user connected
    // folderId (integer) : Check specific folder
    // edition (boolean) : whether user can edit or not
    private static function getScopeFolders(array $aArgs)
    {
        $login = $aArgs['login'];
        $userEntities = EntityModel::getEntitiesByUserId([
            'select'  => ['entities.id'],
            'user_id' => $login
        ]);

        $userEntities = array_column($userEntities, 'id');
        if (empty($userEntities)) {
            $userEntities = 0;
        }

        $user = UserModel::getByLogin(['login' => $login, 'select' => ['id']]);

        if ($aArgs['edition']) {
            $edition = [1];
        } else {
            $edition = [0, 1, null];
        }

        $where = ['(user_id = ? OR (entity_id in (?) AND entities_folders.edition in (?)))'];
        $data = [$user['id'], $userEntities, $edition];

        if (!empty($aArgs['folderId'])) {
            $where[] = 'folders.id = ?';
            $data[]  = $aArgs['folderId'];
        }

        $folders = FolderModel::get([
            'select'   => ['distinct (folders.id)', 'folders.*'],
            'where'    => $where,
            'data'     => $data,
            'order_by' => ['level']
        ]);

        return $folders;
    }

    private static function hasFolder(array $args)
    {
        ValidatorModel::notEmpty($args, ['id', 'userId']);
        ValidatorModel::intVal($args, ['id', 'userId']);


        $user = UserModel::getById(['id' => $args['userId'], 'select' => ['user_id']]);

        $entities = UserModel::getEntitiesById(['userId' => $user['user_id']]);
        $entities = array_column($entities, 'id');

        if (empty($entities)) {
            $entities = [0];
        }

        $folders = FolderModel::getWithEntities([
            'select'   => [1],
            'where'    => ['folders.id = ?', '(user_id = ? OR entity_id in (?))'],
            'data'     => [$args['id'], $args['userId'], $entities]
        ]);

        if (empty($folders)) {
            return false;
        }

        return true;
    }
}
