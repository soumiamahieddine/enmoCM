<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Tile Controller
 * @author dev@maarch.org
 */

namespace Home\controllers;

use Basket\models\BasketModel;
use Contact\controllers\ContactController;
use Doctype\models\DoctypeModel;
use Folder\controllers\FolderController;
use Folder\models\FolderModel;
use Folder\models\ResourceFolderModel;
use Group\models\GroupModel;
use History\controllers\HistoryController;
use Home\models\TileModel;
use Resource\models\ResModel;
use Resource\models\UserFollowedResourceModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\CurlModel;
use Status\models\StatusModel;
use User\models\UserModel;

class TileController
{
    const TYPES = ['myLastResources', 'basket', 'searchTemplate', 'followedMail', 'folder', 'externalSignatoryBook', 'shortcut'];
    const VIEWS = ['list', 'resume', 'chart'];

    public function get(Request $request, Response $response)
    {
        $tiles = TileModel::get([
            'select'    => ['*'],
            'where'     => ['user_id = ?'],
            'data'      => [$GLOBALS['id']]
        ]);

        foreach ($tiles as $key => $tile) {
            $tiles[$key]['userId'] = $tile['user_id'];
            unset($tiles[$key]['user_id']);
            $tiles[$key]['parameters'] = json_decode($tile['parameters'], true);
            TileController::getShortDetails($tiles[$key]);
        }

        return $response->withJson(['tiles' => $tiles]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        $tile = TileModel::getById([
            'select'    => ['*'],
            'id'        => $args['id']
        ]);
        if (empty($tile) || $tile['user_id'] != $GLOBALS['id']) {
            return $response->withStatus(400)->withJson(['errors' => 'Tile out of perimeter']);
        }

        $tile['parameters'] = json_decode($tile['parameters'], true);

        $control = TileController::getDetails($tile);
        if (!empty($control['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
        }

        return $response->withJson(['tile' => $tile]);
    }

    public function create(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is empty']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['type'] ?? null) || !in_array($body['type'], TileController::TYPES)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body type is empty, not a string or not valid']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['view'] ?? null) || !in_array($body['view'], TileController::VIEWS)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body view is empty, not a string or not valid']);
        } elseif (!Validator::intVal()->validate($body['position'] ?? null)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body position is not set or not an integer']);
        }

        $tiles = TileModel::get([
            'select'    => [1],
            'where'     => ['user_id = ?'],
            'data'      => [$GLOBALS['id']]
        ]);
        if (count($tiles) >= 6) {
            return $response->withStatus(400)->withJson(['errors' => 'Too many tiles (limited to 6)']);
        }
        $control = TileController::controlParameters($body);
        if (!empty($control['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
        }

        $id = TileModel::create([
            'user_id'       => $GLOBALS['id'],
            'type'          => $body['type'],
            'view'          => $body['view'],
            'position'      => $body['position'],
            'color'         => $body['color'] ?? null,
            'parameters'    => empty($body['parameters']) ? '{}' : json_encode($body['parameters'])
        ]);

        HistoryController::add([
            'tableName'    => 'tiles',
            'recordId'     => $id,
            'eventType'    => 'ADD',
            'eventId'      => 'tileCreation',
            'info'         => 'tile creation'
        ]);

        return $response->withJson(['id' => $id]);
    }

    public function update(Request $request, Response $response, array $args)
    {
        $tile = TileModel::getById(['select' => ['user_id'], 'id' => $args['id']]);
        if (empty($tile) || $tile['user_id'] != $GLOBALS['id']) {
            return $response->withStatus(400)->withJson(['errors' => 'Tile out of perimeter']);
        }

        $body = $request->getParsedBody();

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is empty']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['view'] ?? null) || !in_array($body['view'], TileController::VIEWS)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body view is empty, not a string or not valid']);
        }

        if ($body['view'] != 'chart') {
            unset($body['parameters']['chartMode']);
        }

        TileModel::update([
            'set'   => [
                'view'          => $body['view'],
                'color'         => $body['color'] ?? null,
                'parameters'    => empty($body['parameters']) ? '{}' : json_encode($body['parameters'])
            ],
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'tableName'    => 'tiles',
            'recordId'     => $args['id'],
            'eventType'    => 'UP',
            'eventId'      => 'tileModification',
            'info'         => 'tile modification'
        ]);

        return $response->withStatus(204);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        $tile = TileModel::getById(['select' => ['user_id'], 'id' => $args['id']]);
        if (empty($tile) || $tile['user_id'] != $GLOBALS['id']) {
            return $response->withStatus(400)->withJson(['errors' => 'Tile out of perimeter']);
        }

        TileModel::delete([
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'tableName'    => 'tiles',
            'recordId'     => $args['id'],
            'eventType'    => 'DEL',
            'eventId'      => 'tileSuppression',
            'info'         => 'tile suppression'
        ]);

        return $response->withStatus(204);
    }

    public function updatePositions(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is empty']);
        } elseif (!Validator::arrayType()->notEmpty()->validate($body['tiles'] ?? null)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body tiles is empty not not an array']);
        }

        $userTiles = TileModel::get(['select' => ['id'], 'where' => ['user_id = ?'], 'data' => [$GLOBALS['id']]]);
        if (count($userTiles) != count($body['tiles'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body tiles do not match user tiles']);
        }
        $allTiles = array_column($userTiles, 'id');
        foreach ($body['tiles'] as $tile) {
            if (!in_array($tile['id'], $allTiles)) {
                return $response->withStatus(400)->withJson(['errors' => 'Tiles out of perimeter']);
            }
        }

        foreach ($body['tiles'] as $tile) {
            TileModel::update([
                'set'   => [
                    'position' => $tile['position'],
                ],
                'where' => ['id = ?'],
                'data'  => [$tile['id']]
            ]);
        }

        return $response->withStatus(204);
    }

    private static function controlParameters(array $args)
    {
        if ($args['type'] == 'basket') {
            if (!Validator::arrayType()->notEmpty()->validate($args['parameters'] ?? null)) {
                return ['errors' => 'Body parameters is empty or not an array'];
            } elseif (!Validator::intVal()->validate($args['parameters']['basketId'] ?? null)) {
                return ['errors' => 'Body[parameters] basketId is empty or not an integer'];
            } elseif (!Validator::intVal()->validate($args['parameters']['groupId'] ?? null)) {
                return ['errors' => 'Body[parameters] groupId is empty or not an integer'];
            }
            $basket = BasketModel::getById(['select' => ['basket_id'], 'id' => $args['parameters']['basketId']]);
            $group = GroupModel::getById(['select' => ['group_id'], 'id' => $args['parameters']['groupId']]);
            if (empty($basket) || empty($group)) {
                return ['errors' => 'Basket or group do not exist'];
            } elseif (!BasketModel::hasGroup(['id' => $basket['basket_id'], 'groupId' => $group['group_id']])) {
                return ['errors' => 'Basket is not linked to this group'];
            } elseif (!UserModel::hasGroup(['id' => $GLOBALS['id'], 'groupId' => $group['group_id']])) {
                return ['errors' => 'User is not linked to this group'];
            }
        } elseif ($args['type'] == 'folder') {
            if (!Validator::arrayType()->notEmpty()->validate($args['parameters'] ?? null)) {
                return ['errors' => 'Body parameters is empty or not an array'];
            } elseif (!Validator::intVal()->validate($args['parameters']['folderId'] ?? null)) {
                return ['errors' => 'Body[parameters] folderId is empty or not an integer'];
            }

            $folder = FolderController::getScopeFolders(['login' => $GLOBALS['login'], 'folderId' => $args['parameters']['folderId']]);
            if (empty($folder[0])) {
                return ['errors' => 'Folder not found or out of your perimeter'];
            }
        }

        return true;
    }

    private static function getShortDetails(array &$tile)
    {
        if ($tile['type'] == 'basket') {
            $basket = BasketModel::getById(['select' => ['basket_clause', 'basket_name', 'basket_id'], 'id' => $tile['parameters']['basketId']]);
            $group = GroupModel::getById(['select' => ['group_desc', 'group_id'], 'id' => $tile['parameters']['groupId']]);
            $tile['label'] = "{$basket['basket_name']} ({$group['group_desc']})";
        } elseif ($tile['type'] == 'folder') {
            $folder = FolderModel::getById(['id' => $tile['parameters']['folderId']]);
            $tile['label'] = "{$folder['label']}";
        }

        return true;
    }

    private static function getDetails(array &$tile)
    {
        if ($tile['type'] == 'basket') {
            $control = TileController::getBasketDetails($tile);
            if (!empty($control['errors'])) {
                return ['errors' => $control['errors']];
            }
        } elseif ($tile['type'] == 'myLastResources') {
            TileController::getLastResourcesDetails($tile);
        } elseif ($tile['type'] == 'searchTemplate') {
        } elseif ($tile['type'] == 'followedMail') {
            $followedResources = UserFollowedResourceModel::get([
                'select' => ['res_id'],
                'where'  => ['user_id = ?'],
                'data'   => [$GLOBALS['id']]
            ]);
            TileController::getResourcesDetails($tile, $followedResources);
        } elseif ($tile['type'] == 'folder') {
            if (!FolderController::hasFolders(['folders' => [$tile['parameters']['folderId']], 'userId' => $GLOBALS['id']])) {
                return ['errors' => 'Folder out of perimeter'];
            }
            $foldersResources = ResourceFolderModel::get(['select' => ['res_id'], 'where' => ['folder_id = ?'], 'data' => [$tile['parameters']['folderId']]]);
            TileController::getResourcesDetails($tile, $foldersResources);
        } elseif ($tile['type'] == 'externalSignatoryBook') {
            $control = TileController::getMaarchParapheurDetails($tile);
            if (!empty($control['errors'])) {
                return ['errors' => $control['errors']];
            }
        } elseif ($tile['type'] == 'shortcut') {
        }

        return true;
    }

    private static function getBasketDetails(array &$tile)
    {
        $basket = BasketModel::getById(['select' => ['basket_clause', 'basket_id'], 'id' => $tile['parameters']['basketId']]);
        $group = GroupModel::getById(['select' => ['group_id'], 'id' => $tile['parameters']['groupId']]);
        if (!BasketModel::hasGroup(['id' => $basket['basket_id'], 'groupId' => $group['group_id']])) {
            return ['errors' => 'Basket is not linked to this group'];
        } elseif (!UserModel::hasGroup(['id' => $GLOBALS['id'], 'groupId' => $group['group_id']])) {
            return ['errors' => 'User is not linked to this group'];
        }

        if ($tile['view'] == 'resume') {
            $tile['resourcesNumber'] = BasketModel::getResourceNumberByClause(['userId' => $GLOBALS['id'], 'clause' => $basket['basket_clause']]);
        } elseif ($tile['view'] == 'list') {
            $resources = ResModel::getOnView([
                'select'    => ['subject', 'creation_date', 'res_id'],
                'where'     => [PreparedClauseController::getPreparedClause(['userId' => $GLOBALS['id'], 'clause' => $basket['basket_clause']])],
                'orderBy'   => ['creation_date'],
                'limit'     => 5
            ]);
            $tile['resources'] = [];
            foreach ($resources as $resource) {
                $senders = ContactController::getFormattedContacts(['resId' => $resource['res_id'], 'mode' => 'sender', 'onlyContact' => true]);
                $recipients = ContactController::getFormattedContacts(['resId' => $resource['res_id'], 'mode' => 'recipient', 'onlyContact' => true]);

                $tile['resources'][] = [
                    'resId'         => $resource['res_id'],
                    'subject'       => $resource['subject'],
                    'creationDate'  => $resource['creation_date'],
                    'senders'       => $senders ,
                    'recipients'    => $recipients
                ];
            }
        } elseif ($tile['view'] == 'chart') {
            if (!empty($tile['parameters']['chartMode']) && $tile['parameters']['chartMode'] == 'status') {
                $type = 'status';
            } else {
                $type = 'type_id';
            }
            $resources = ResModel::getOnView([
                'select'    => ["COUNT({$type})", $type],
                'where'     => [PreparedClauseController::getPreparedClause(['userId' => $GLOBALS['id'], 'clause' => $basket['basket_clause']])],
                'groupBy'   => [$type]
            ]);
            $tile['resources'] = [];
            foreach ($resources as $resource) {
                if ($type == 'status') {
                    $status['label_status'] = '';
                    if (!empty($resource['status'])) {
                        $status = StatusModel::getById(['select' => ['label_status'], 'id' => $resource['status']]);
                    }
                    $tile['resources'][] = ['name' => $status['label_status'], 'value' => $resource['count']];
                } else {
                    $doctype = DoctypeModel::getById(['select' => ['description'], 'id' => $resource['type_id']]);
                    $tile['resources'][] = ['name' => $doctype['description'], 'value' => $resource['count']];
                }
            }
        }

        return true;
    }

    private static function getResourcesDetails(array &$tile, $allResources)
    {
        $allResources = array_column($allResources, 'res_id');
        if ($tile['view'] == 'resume') {
            $tile['resourcesNumber'] = count($allResources);
        } elseif ($tile['view'] == 'list') {
            $resources = ResModel::get([
                'select'  => ['subject', 'creation_date', 'res_id'],
                'where'   => ['res_id in (?)'],
                'data'    => [$allResources],
                'orderBy' => ['modification_date'],
                'limit'   => 5
            ]);
            $tile['resources'] = [];
            foreach ($resources as $resource) {
                $senders    = ContactController::getFormattedContacts(['resId' => $resource['res_id'], 'mode' => 'sender', 'onlyContact' => true]);
                $recipients = ContactController::getFormattedContacts(['resId' => $resource['res_id'], 'mode' => 'recipient', 'onlyContact' => true]);

                $tile['resources'][] = [
                    'resId'        => $resource['res_id'],
                    'subject'      => $resource['subject'],
                    'creationDate' => $resource['creation_date'],
                    'senders'      => $senders ,
                    'recipients'   => $recipients
                ];
            }
        } elseif ($tile['view'] == 'chart') {
            if (!empty($tile['parameters']['chartMode']) && $tile['parameters']['chartMode'] == 'status') {
                $type = 'status';
            } else {
                $type = 'type_id';
            }
            $resources = ResModel::get([
                'select'  => ["COUNT({$type})", $type],
                'where'   => ['res_id in (?)'],
                'data'    => [$allResources],
                'groupBy' => [$type]
            ]);
            $tile['resources'] = [];
            foreach ($resources as $resource) {
                if ($type == 'status') {
                    $status['label_status'] = '';
                    if (!empty($resource['status'])) {
                        $status = StatusModel::getById(['select' => ['label_status'], 'id' => $resource['status']]);
                    }
                    $tile['resources'][] = ['name' => $status['label_status'], 'value' => $resource['count']];
                } else {
                    $doctype = DoctypeModel::getById(['select' => ['description'], 'id' => $resource['type_id']]);
                    $tile['resources'][] = ['name' => $doctype['description'], 'value' => $resource['count']];
                }
            }
        }

        return true;
    }

    private static function getLastResourcesDetails(array &$tile)
    {
        $resources = ResModel::getLastResources([
            'select'    => [
                'res_letterbox.res_id',
                'res_letterbox.creation_date',
                'res_letterbox.subject',
                'res_letterbox.type_id',
                'res_letterbox.status'
            ],
            'limit'     => 5,
            'userId'    => $GLOBALS['id']
        ]);
        if ($tile['view'] == 'resume') {
            $tile['resourcesNumber'] = count($resources);
        } elseif ($tile['view'] == 'list') {
            $tile['resources'] = [];
            foreach ($resources as $resource) {
                $senders = ContactController::getFormattedContacts(['resId' => $resource['res_id'], 'mode' => 'sender', 'onlyContact' => true]);
                $recipients = ContactController::getFormattedContacts(['resId' => $resource['res_id'], 'mode' => 'recipient', 'onlyContact' => true]);

                $tile['resources'][] = [
                    'resId'         => $resource['res_id'],
                    'subject'       => $resource['subject'],
                    'creationDate'  => $resource['creation_date'],
                    'senders'       => $senders ,
                    'recipients'    => $recipients
                ];
            }
        } elseif ($tile['view'] == 'chart') {
            if (!empty($tile['parameters']['chartMode']) && $tile['parameters']['chartMode'] == 'status') {
                $type = 'status';
            } else {
                $type = 'type_id';
            }
            $tile['resources'] = [];
            $chartTypes = [];
            foreach ($resources as $resource) {
                if ($type == 'status') {
                    $status['label_status'] = '';
                    if (!empty($resource['status'])) {
                        $status = StatusModel::getById(['select' => ['label_status'], 'id' => $resource['status']]);
                    }
                    if (!in_array($status['label_status'], $chartTypes)) {
                        $chartTypes[] = $status['label_status'];
                        $tile['resources'][] = ['name' => $status['label_status'], 'value' => 1];
                    } else {
                        foreach ($tile['resources'] as $key => $tileResource) {
                            if ($tileResource['name'] == $status['label_status']) {
                                $tile['resources'][$key]['value']++;
                            }
                        }
                    }
                } else {
                    $doctype = DoctypeModel::getById(['select' => ['description'], 'id' => $resource['type_id']]);
                    if (!in_array($doctype['description'], $chartTypes)) {
                        $chartTypes[] = $doctype['description'];
                        $tile['resources'][] = ['name' => $doctype['description'], 'value' => 1];
                    } else {
                        foreach ($tile['resources'] as $key => $tileResource) {
                            if ($tileResource['name'] == $doctype['description']) {
                                $tile['resources'][$key]['value']++;
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    private static function getMaarchParapheurDetails(array &$tile)
    {
        $user = UserModel::getById(['id' => $GLOBALS['id'], 'select' => ['external_id']]);

        $externalId = json_decode($user['external_id'], true);
        if (empty($externalId['maarchParapheur'])) {
            return ['errors' => 'User is not linked to Maarch Parapheur'];
        }

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'modules/visa/xml/remoteSignatoryBooks.xml']);
        if (empty($loadedXml)) {
            return ['errors' => 'SignatoryBooks configuration file missing'];
        }

        $url      = '';
        $userId   = '';
        $password = '';
        foreach ($loadedXml->signatoryBook as $value) {
            if ($value->id == "maarchParapheur") {
                $url      = rtrim($value->url, '/');
                $userId   = $value->userId;
                $password = $value->password;
                break;
            }
        }

        if (empty($url)) {
            return ['errors' => 'Maarch Parapheur configuration missing'];
        }

        $curlResponse = CurlModel::execSimple([
            'url'           => rtrim($url, '/') . '/rest/documents',
            'basicAuth'     => ['user' => $userId, 'password' => $password],
            'headers'       => ['content-type:application/json'],
            'method'        => 'GET',
            'queryParams'   => ['userId' => $externalId['maarchParapheur'], 'limit' => 5]
        ]);

        if ($curlResponse['code'] != '200') {
            if (!empty($curlResponse['response']['errors'])) {
                $errors =  $curlResponse['response']['errors'];
            } else {
                $errors =  $curlResponse['errors'];
            }
            if (empty($errors)) {
                $errors = 'An error occured. Please check your configuration file.';
            }
            return ['errors' => $errors];
        }

        $tile['maarchParapheurUrl'] = $url;
        if ($tile['view'] == 'resume') {
            $tile['resourcesNumber'] = $curlResponse['response']['count']['visa'] + $curlResponse['response']['count']['sign'] + $curlResponse['response']['count']['note'];
        } elseif ($tile['view'] == 'list') {
            $tile['resources'] = [];
            foreach ($curlResponse['response']['documents'] as $resource) {
                $tile['resources'][] = [
                    'id'            => $resource['id'],
                    'subject'       => $resource['title'],
                    'creationDate'  => $resource['creationDate'],
                    'senders'       => [$resource['sender']]
                ];
            }
        }

        return true;
    }
}
