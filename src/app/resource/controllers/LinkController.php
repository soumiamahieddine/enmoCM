<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Link Controller
* @author dev@maarch.org
*/

namespace Resource\controllers;

use Entity\models\EntityModel;
use Entity\models\ListInstanceModel;
use Resource\models\ResModel;
use Resource\models\ResourceContactModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use Status\models\StatusModel;
use User\models\UserModel;

class LinkController
{
    public function getLinkedResources(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['resId']) || !ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Resource out of perimeter']);
        }

        $resource = ResModel::getById(['resId' => $args['resId'], 'select' => ['linked_resources']]);
        $linkedResourcesIds = json_decode($resource['linked_resources'], true);

        $linkedResources = [];
        if (!empty($linkedResourcesIds)) {
            $linkedResources = ResModel::get([
                'select'    => ['res_id as "resId"', 'subject', 'doc_date as "documentDate"', 'status', 'dest_user as "destUser"', 'destination', 'alt_identifier as chrono', 'category_id as "categoryId"'],
                'where'     => ['res_id in (?)'],
                'data'      => [$linkedResourcesIds]
            ]);

            foreach ($linkedResources as $key => $value) {
                if (!empty($value['status'])) {
                    $status = StatusModel::getById(['id' => $value['status'], 'select' => ['label_status', 'img_filename']]);
                    $linkedResources[$key]['statusLabel'] = $status['label_status'];
                    $linkedResources[$key]['statusImage'] = $status['img_filename'];
                }

                if (!empty($value['destUser'])) {
                    $linkedResources[$key]['destUserLabel'] = UserModel::getLabelledUserById(['login' => $value['destUser']]);
                }
                if (!empty($value['destination'])) {
                    $linkedResources[$key]['destinationLabel'] = EntityModel::getByEntityId(['entityId' => $value['destination'], 'select' => ['short_label']])['short_label'];
                }

                $contacts = ResourceContactModel::get([
                    'select'    => ['item_id as id', 'type', 'mode'],
                    'where'     => ['res_id = ?'],
                    'data'      => [$value['resId']]
                ]);

                $linkedResources[$key]['senders'] = [];
                $linkedResources[$key]['recipients'] = [];
                foreach ($contacts as $contact) {
                    $linkedResources[$key]["{$contact['mode']}s"][] = $contact;
                }

                $linkedResources[$key]['visaCircuit'] = ListInstanceModel::get(['select' => ['item_id', 'item_mode'], 'where' => ['res_id = ?', 'difflist_type = ?'], 'data' => [$value['resId'], 'VISA_CIRCUIT']]);
                foreach ($linkedResources[$key]['visaCircuit'] as $keyCircuit => $valueCircuit) {
                    $linkedResources[$key]['visaCircuit'][$keyCircuit]['userLabel'] = UserModel::getLabelledUserById(['login' => $valueCircuit['item_id']]);
                }
            }
        }

        return $response->withJson(['linkedResources' => $linkedResources]);
    }

    public function linkResources(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['resId']) || !ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Resource out of perimeter']);
        }

        $body = $request->getParsedBody();

        if (!Validator::arrayType()->notEmpty()->validate($body['linkedResources'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Body linkedResources is empty or not an array']);
        } elseif (!ResController::hasRightByResId(['resId' => $body['linkedResources'], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Body linkedResources out of perimeter']);
        } elseif (in_array($args['resId'], $body['linkedResources'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Body linkedResources contains resource']);
        }

        $resource = ResModel::getById(['resId' => $args['resId'], 'select' => ['linked_resources']]);
        $linkedResources = json_decode($resource['linked_resources'], true);
        $linkedResources = array_merge($linkedResources, $body['linkedResources']);
        $linkedResources = array_unique($linkedResources);
        foreach ($linkedResources as $key => $value) {
            $linkedResources[$key] = (string)$value;
        }

        ResModel::update([
            'set'       => ['linked_resources' => json_encode($linkedResources)],
            'where'     => ['res_id = ?'],
            'data'      => [$args['resId']]
        ]);
        ResModel::update([
            'postSet'   => ['linked_resources' => "jsonb_insert(linked_resources, '{0}', '\"{$args['resId']}\"')"],
            'where'     => ['res_id in (?)', "(linked_resources @> ?) = false"],
            'data'      => [$body['linkedResources'], "\"{$args['resId']}\""]
        ]);

        return $response->withStatus(204);
    }

    public function unlinkResources(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['resId']) || !ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Resource out of perimeter']);
        }

        if (!Validator::intVal()->validate($args['id']) || !ResController::hasRightByResId(['resId' => [$args['id']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Resource to unlink out of perimeter']);
        }

        ResModel::update([
            'postSet'   => ['linked_resources' => "linked_resources - '{$args['id']}'"],
            'where'     => ['res_id = ?'],
            'data'      => [$args['resId']]
        ]);
        ResModel::update([
            'postSet'   => ['linked_resources' => "linked_resources - '{$args['resId']}'"],
            'where'     => ['res_id = ?'],
            'data'      => [$args['id']]
        ]);

        return $response->withStatus(204);
    }
}
