<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*/

/**
 * @brief Tag Controller
 * @author dev@maarch.org
 */

namespace Tag\controllers;

use Group\controllers\PrivilegeController;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use Tag\models\TagModel;
use Tag\models\TagResModel;

class TagController
{
    public function get(Request $request, Response $response)
    {
        $tags = TagModel::get();

        return $response->withJson(['tags' => $tags]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id must be an integer val']);
        }

        $tag = TagModel::getById(['id' => $args['id']]);
        if (empty($tag)) {
            return $response->withStatus(404)->withJson(['errors' => 'id not found']);
        }

        return $response->withJson($tag);
    }

    public function create(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_tag', 'userId' => $GLOBALS['id']])
            && !PrivilegeController::hasPrivilege(['privilegeId' => 'manage_tags_application', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string']);
        }

        if (!Validator::length(1, 128)->validate($body['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label has more than 128 characters']);
        }

        $id = TagModel::create([
            'label' => $body['label']
        ]);

        HistoryController::add([
            'tableName' => 'tags',
            'recordId'  => $id,
            'eventType' => 'ADD',
            'info'      =>  _TAG_ADDED . " : {$body['label']}",
            'eventId'   => 'tagCreation',
        ]);

        return $response->withJson(['id' => $id]);
    }

    public function update(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_tag', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id must be an integer val']);
        }

        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string']);
        }

        if (!Validator::length(1, 128)->validate($body['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label has more than 128 characters']);
        }

        TagModel::update([
            'set' => [
                'label' => $body['label']
            ],
            'where' => ['id = ?'],
            'data' => [$args['id']]
        ]);

        HistoryController::add([
            'tableName' => 'tags',
            'recordId'  => $args['id'],
            'eventType' => 'UP',
            'info'      =>  _TAG_UPDATED . " : {$body['label']}",
            'eventId'   => 'tagModification',
        ]);

        return $response->withStatus(204);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_tag', 'userId' => $GLOBALS['id']])
            && !PrivilegeController::hasPrivilege(['privilegeId' => 'manage_tags_application', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id must be an integer val']);
        }


        $tag = TagModel::getById(['select' => ['label'], 'id' => $args['id']]);
        if (empty($tag)) {
            return $response->withStatus(400)->withJson(['errors' => 'Tag does not exist']);
        }

        TagModel::delete([
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'tableName' => 'tags',
            'recordId'  => $args['id'],
            'eventType' => 'DEL',
            'info'      =>  _TAG_DELETED . " : {$tag['label']}",
            'eventId'   => 'tagSuppression',
        ]);

        return $response->withStatus(204);
    }

    public function merge(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_tag', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $body = $request->getParsedBody();

        if (!Validator::intVal()->notEmpty()->validate($body['idMaster'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body idMaster must be an integer val']);
        }

        $tagMaster = TagModel::getById(['id' => $body['idMaster']]);
        if (empty($tagMaster)) {
            return $response->withStatus(404)->withJson(['errors' => 'Master tag not found']);
        }

        if (!Validator::intVal()->notEmpty()->validate($body['idMerge'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body idMerge must be an integer val']);
        }

        $tagMerge = TagModel::getById(['id' => $body['idMerge']]);
        if (empty($tagMerge)) {
            return $response->withStatus(404)->withJson(['errors' => 'Merge tag not found']);
        }

        $tagResMaster = TagResModel::get([
           'where'  => ['tag_id = ?'],
            'data'  => [$tagMaster['id']]
        ]);
        $tagResMaster = array_column($tagResMaster, 'res_id');

        TagResModel::update([
           'set'    => [
               'tag_id' => $tagMaster['id']
           ],
           'where'  => ['tag_id = ?', 'res_id not in (?)'],
           'data'   => [$tagMerge['id'], $tagResMaster]
        ]);

        TagResModel::delete([
           'where'  => ['tag_id = ?'],
           'data'   => [$tagMerge['id']]
        ]);

        TagModel::delete([
            'where' => ['id = ?'],
            'data'  => [$tagMerge['id']]
        ]);

        HistoryController::add([
            'tableName' => 'tags',
            'recordId'  => $tagMaster['id'],
            'eventType' => 'DEL',
            'info'      =>  _TAG_MERGED . " : {$tagMerge['label']} vers {$tagMaster['label']}",
            'eventId'   => 'tagSuppression',
        ]);

        return $response->withStatus(204);
    }
}
