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
use Tag\models\ResourceTagModel;

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

        $parent = null;
        if (!empty($body['parentId'])) {
            if (!Validator::intVal()->validate($body['parentId'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Body parentId is not an integer']);
            }
            $parent = TagModel::getById(['id' => $body['parentId'], 'select' => ['id']]);
            if (empty($parent)) {
                return $response->withStatus(400)->withJson(['errors' => 'Parent tag does not exist']);
            }
            $parent = $parent['id'];
        }

        $links = json_encode([]);
        if (!empty($body['links'])) {
            if (!Validator::arrayType()->validate($body['links'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Body links is not an integer']);
            }
            $listTags = [];
            foreach ($body['links'] as $link) {
                if (!Validator::intVal()->validate($link)) {
                    return $response->withStatus(400)->withJson(['errors' => 'Body links element is not an integer']);
                }
                $listTags[] = (string)$link;
            }
            $tags = TagModel::get([
                'select' => ['count(1)'],
                'where'  => ['id in (?)'],
                'data'   => [$body['links']]
            ]);
            if ($tags[0]['count'] != count($body['links'])) {
                return $response->withStatus(404)->withJson(['errors' => 'Tag(s) not found']);
            }

            $links = json_encode($listTags);
        }

        $id = TagModel::create([
            'label'       => $body['label'],
            'description' => $body['description'] ?? null,
            'parentId'    => $parent,
            'links'       => $links,
            'usage'       => $body['usage'] ?? null
        ]);

        if (!empty($body['links'])) {
            TagModel::update([
                'postSet' => ['links' => "jsonb_insert(links, '{0}', '\"{$id}\"')"],
                'where'   => ['id in (?)', "(links @> ?) = false"],
                'data'    => [$body['links'], "\"{$id}\""]
            ]);
        }

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

        $parent = null;
        if (!empty($body['parentId'])) {
            if (!Validator::intVal()->validate($body['parentId'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Body parentId is not an integer']);
            }
            $parent = TagModel::getById(['id' => $body['parentId'], 'select' => ['id']]);
            if (empty($parent)) {
                return $response->withStatus(400)->withJson(['errors' => 'Parent tag does not exist']);
            }
            $parent = $parent['id'];
        }

        TagModel::update([
            'set' => [
                'label'       => $body['label'],
                'description' => $body['description'] ?? null,
                'parent_id'   => $parent,
                'usage'       => $body['usage'] ?? null
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

        $tagResMaster = ResourceTagModel::get([
           'where'  => ['tag_id = ?'],
            'data'  => [$tagMaster['id']]
        ]);
        $tagResMaster = array_column($tagResMaster, 'res_id');

        ResourceTagModel::update([
           'set'    => [
               'tag_id' => $tagMaster['id']
           ],
           'where'  => ['tag_id = ?', 'res_id not in (?)'],
           'data'   => [$tagMerge['id'], $tagResMaster]
        ]);

        ResourceTagModel::delete([
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

    public static function link(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_tag', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $body = $request->getParsedBody();

        if (!Validator::arrayType()->notEmpty()->validate($body['links'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body links is empty or not an array']);
        } elseif (in_array($args['id'], $body['links'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body links contains tag']);
        }

        $tag = TagModel::getById(['id' => $args['id']]);
        $linkedTags = json_decode($tag['links'], true);
        $linkedTags = array_merge($linkedTags, $body['links']);
        $linkedTags = array_unique($linkedTags);
        foreach ($linkedTags as $key => $linkedTag) {
            $linkedTags[$key] = (string)$linkedTag;
        }

        TagModel::update([
            'set'       => ['links' => json_encode($linkedTags)],
            'where'     => ['id = ?'],
            'data'      => [$args['id']]
        ]);
        TagModel::update([
            'postSet'   => ['links' => "jsonb_insert(links, '{0}', '\"{$args['id']}\"')"],
            'where'     => ['id in (?)', "(links @> ?) = false"],
            'data'      => [$body['links'], "\"{$args['id']}\""]
        ]);

        $linkedTagsInfo = TagModel::get([
            'select' => ['label', 'id'],
            'where'  => ['id in (?)'],
            'data'   => [$body['links']]
        ]);
        $linkedTagsInfo = array_column($linkedTagsInfo, 'label', 'id');

        foreach ($body['linkedResources'] as $value) {
            HistoryController::add([
                'tableName' => 'tags',
                'recordId'  => $args['resId'],
                'eventType' => 'UP',
                'info'      => _LINK_ADDED . " : {$linkedTagsInfo[$value]}",
                'eventId'   => 'tagModification'
            ]);
            HistoryController::add([
                'tableName' => 'tags',
                'recordId'  => $value,
                'eventType' => 'UP',
                'info'      => _LINK_ADDED . " : {$tag['label']}",
                'eventId'   => 'tagModification'
            ]);
        }

        return $response->withStatus(204);
    }

    public static function unLink(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_tag', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->validate($args['tagId']) || !Validator::intVal()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route tagId or id is not an integer']);
        }

        TagModel::update([
            'postSet'   => ['links' => "links - '{$args['id']}'"],
            'where'     => ['id = ?'],
            'data'      => [$args['tagId']]
        ]);
        TagModel::update([
            'postSet'   => ['links' => "links - '{$args['tagId']}'"],
            'where'     => ['id = ?'],
            'data'      => [$args['id']]
        ]);

        $linkedTagsInfo = TagModel::get([
            'select' => ['label', 'id'],
            'where'  => ['id in (?)'],
            'data'   => [[$args['tagId'], $args['id']]]
        ]);
        $linkedTagsInfo = array_column($linkedTagsInfo, 'label', 'id');

        HistoryController::add([
            'tableName' => 'tags',
            'recordId'  => $args['tagId'],
            'eventType' => 'UP',
            'info'      => _LINK_DELETED . " : {$linkedTagsInfo[$args['id']]}",
            'eventId'   => 'tagModification'
        ]);
        HistoryController::add([
            'tableName' => 'tags',
            'recordId'  => $args['id'],
            'eventType' => 'UP',
            'info'      => _LINK_DELETED . " : {$linkedTagsInfo[$args['tagId']]}",
            'eventId'   => 'tagModification'
        ]);

        return $response->withStatus(204);
    }
}
