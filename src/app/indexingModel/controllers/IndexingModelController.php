<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ParametersController
* @author  dev <dev@maarch.org>
* @ingroup core
*/

/**
 * @brief Indexing Model Controller
 * @author dev@maarch.org
 */

namespace IndexingModel\controllers;

use Group\models\ServiceModel;
use IndexingModel\models\IndexingModelFieldModel;
use IndexingModel\models\IndexingModelModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class IndexingModelController
{
    public function get(Request $request, Response $response, array $args)
    {
        $models = IndexingModelModel::get(['where' => ['owner = ? OR private = ?'], 'data' => [$GLOBALS['id'], false]]);

        foreach ($models as $key => $model) {
            $fields = IndexingModelFieldModel::get(['select' => ['label', 'mandatory', 'value', 'unit'], 'where' => ['model_id = ?'], 'data' => [$model['id']]]);
            $models[$key]['fields'] = $fields;
        }

        return $response->withJson(['indexingModels' => $models]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        $model = IndexingModelModel::getById(['id' => $args['id']]);
        if (empty($model)) {
            return $response->withStatus(400)->withJson(['errors' => 'Model not found']);
        } elseif ($model['private'] && $model['owner'] != $GLOBALS['id']) {
            return $response->withStatus(400)->withJson(['errors' => 'Model out of perimeter']);
         }

        $fields = IndexingModelFieldModel::get(['select' => ['label', 'mandatory', 'value', 'unit'], 'where' => ['model_id = ?'], 'data' => [$args['id']]]);
        $model['fields'] = $fields;

        return $response->withJson(['indexingModel' => $model]);
    }

    public function create(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string']);
        }
        if (ServiceModel::hasService(['id' => 'admin_indexing_models', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            $body['private'] = empty($body['private']) ? 'false' : 'true';
        } else {
            $body['private'] = true;
        }

        $modelId = IndexingModelModel::create([
            'label'     => $body['label'],
            'default'   => 'false',
            'owner'     => $GLOBALS['id'],
            'private'   => $body['private']
        ]);

        foreach ($body['fields'] as $field) {
            IndexingModelFieldModel::create([
                'model_id'  => $modelId,
                'label'     => $field['label'],
                'mandatory' => empty($field['mandatory']) ? 'false' : 'true',
                'value'     => $field['value'] ?? null,
                'unit'      => empty($field['unit']) ? null : $field['unit']
            ]);
        }

        return $response->withStatus(204);
    }

    public function update(Request $request, Response $response, array $args)
    {
        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string']);
        }

        $model = IndexingModelModel::getById(['select' => ['owner', 'private'], 'id' => $args['id']]);
        if (empty($model)) {
            return $response->withStatus(400)->withJson(['errors' => 'Model not found']);
        } elseif ($model['private'] && $model['owner'] != $GLOBALS['id']) {
            return $response->withStatus(400)->withJson(['errors' => 'Model out of perimeter']);
        } elseif (!$model['private'] && !ServiceModel::hasService(['id' => 'admin_indexing_models', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Model out of perimeter']);
        }

        IndexingModelModel::update([
            'set'   => [
                'label'     => $body['label']
            ],
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        IndexingModelFieldModel::delete(['where' => ['model_id = ?'], 'data' => [$args['id']]]);

        foreach ($body['fields'] as $field) {
            IndexingModelFieldModel::create([
                'model_id'  => $args['id'],
                'label'     => $field['label'],
                'mandatory' => empty($field['mandatory']) ? 'false' : 'true',
                'value'     => $field['value'] ?? null,
                'unit'      => empty($field['unit']) ? null : $field['unit']
            ]);
        }

        return $response->withStatus(204);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        $model = IndexingModelModel::getById(['select' => ['owner', 'private'], 'id' => $args['id']]);
        if (empty($model)) {
            return $response->withStatus(400)->withJson(['errors' => 'Model not found']);
        } elseif ($model['private'] && $model['owner'] != $GLOBALS['id']) {
            return $response->withStatus(400)->withJson(['errors' => 'Model out of perimeter']);
        } elseif (!$model['private'] && !ServiceModel::hasService(['id' => 'admin_indexing_models', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Model out of perimeter']);
        }

        IndexingModelModel::delete([
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        IndexingModelFieldModel::delete(['where' => ['model_id = ?'], 'data' => [$args['id']]]);

        return $response->withStatus(204);
    }
}
