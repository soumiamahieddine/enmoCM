<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Indexing Controller
* @author dev@maarch.org
*/

namespace Resource\controllers;

use Action\models\ActionModel;
use Doctype\models\DoctypeModel;
use Entity\models\EntityModel;
use Group\models\GroupModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;

class IndexingController
{
    public function getIndexingActions(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->notEmpty()->validate($aArgs['groupId'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Param groupId must be an integer val']);
        }

        $indexingParameters = IndexingController::getIndexingParameters(['login' => $GLOBALS['userId'], 'groupId' => $aArgs['groupId']]);
        if (!empty($indexingParameters['errors'])) {
            return $response->withStatus(403)->withJson($indexingParameters);
        }

        $actions = [];
        foreach ($indexingParameters['indexingParameters']['actions'] as $value) {
            $actions[] = ActionModel::getById(['id' => $value, 'select' => ['id', 'label_action', 'component']]);
        }

        return $response->withJson(['actions' => $actions]);
    }

    public function getIndexingEntities(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->notEmpty()->validate($aArgs['groupId'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Param groupId must be an integer val']);
        }

        $indexingParameters = IndexingController::getIndexingParameters(['login' => $GLOBALS['userId'], 'groupId' => $aArgs['groupId']]);
        if (!empty($indexingParameters['errors'])) {
            return $response->withStatus(403)->withJson($indexingParameters);
        }

        $keywords = [
            'ALL_ENTITIES'          => '@all_entities',
            'ENTITIES_JUST_BELOW'   => '@immediate_children[@my_primary_entity]',
            'ENTITIES_BELOW'        => '@subentities[@my_entities]',
            'ALL_ENTITIES_BELOW'    => '@subentities[@my_primary_entity]',
            'ENTITIES_JUST_UP'      => '@parent_entity[@my_primary_entity]',
            'MY_ENTITIES'           => '@my_entities',
            'MY_PRIMARY_ENTITY'     => '@my_primary_entity',
            'SAME_LEVEL_ENTITIES'   => '@sisters_entities[@my_primary_entity]'
        ];

        $allowedEntities = [];
        $clauseToProcess = '';

        foreach ($indexingParameters['indexingParameters']['keywords'] as $keywordValue) {
            if (!empty($clauseToProcess)) {
                $clauseToProcess .= ', ';
            }
            $clauseToProcess .= $keywords[$keywordValue];
        }

        if (!empty($clauseToProcess)) {
            $preparedClause = PreparedClauseController::getPreparedClause(['clause' => $clauseToProcess, 'login' => $GLOBALS['userId']]);
            $preparedEntities = EntityModel::get(['select' => ['id'], 'where' => ['enabled = ?', "entity_id in {$preparedClause}"], 'data' => ['Y']]);
            $allowedEntities = array_column($preparedEntities, 'id');
        }

        $allowedEntities = array_merge($indexingParameters['indexingParameters']['entities'], $allowedEntities);
        $allowedEntities = array_unique($allowedEntities);

        $entitiesTmp = EntityModel::get([
            'select'   => ['id', 'entity_label', 'entity_id'], 
            'where'    => ['enabled = ?', '(parent_entity_id is null OR parent_entity_id = \'\')'], 
            'data'     => ['Y'],
            'orderBy'  => ['entity_label']
        ]);
        if (!empty($entitiesTmp)) {
            foreach ($entitiesTmp as $key => $value) {
                $entitiesTmp[$key]['level'] = 0;
            }
            $entitiesId = array_column($entitiesTmp, 'entity_id');
            $entitiesChild = IndexingController::getEntitiesChildrenLevel(['entitiesId' => $entitiesId, 'level' => 1]);
            $entitiesTmp = array_merge([$entitiesTmp], $entitiesChild);
        }

        $entities = [];
        foreach ($entitiesTmp as $keyLevel => $levels) {
            foreach ($levels as $entity) {
                if (in_array($entity['id'], $allowedEntities)) {
                    $entity['enabled'] = true;
                } else {
                    $entity['enabled'] = false;
                }
                if ($keyLevel == 0) {
                    $entities[] = $entity;
                    continue;
                } else {
                    foreach ($entities as $key => $oEntity) {
                        if ($oEntity['entity_id'] == $entity['parent_entity_id']) {
                            array_splice($entities, $key+1, 0, [$entity]);
                            continue;
                        }
                    }
                }
            }
        }

        return $response->withJson(['entities' => $entities]);
    }

    public function getProcessLimitDate(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        if (!empty($queryParams['doctype'])) {
            $obj['doctype'] = DoctypeModel::getById(['id' => $queryParams['doctype']]);
        }


        return $response->withJson(['actions' => $actions]);
    }

    public static function getEntitiesChildrenLevel($aArgs = [])
    {
        $entities = EntityModel::getEntityChildrenSubLevel([
            'entitiesId' => $aArgs['entitiesId'],
            'select'     => ['id', 'entity_label', 'entity_id', 'parent_entity_id'],
            'orderBy'    => ['entity_label desc']
        ]);
        if (!empty($entities)) {
            foreach ($entities as $key => $value) {
                $entities[$key]['level'] = $aArgs['level'];
            }
            $entitiesId = array_column($entities, 'entity_id');
            $entitiesChild = IndexingController::getEntitiesChildrenLevel(['entitiesId' => $entitiesId, 'level' => $aArgs['level']+1]);
            $entities = array_merge([$entities], $entitiesChild);
        }

        return $entities;
    }

    public static function getIndexingParameters($aArgs = [])
    {
        $group = GroupModel::getGroupByLogin(['login' => $aArgs['login'], 'groupId' => $aArgs['groupId'], 'select' => ['can_index', 'indexation_parameters']]);
        if (empty($group)) {
            return ['errors' => 'This user is not in this group'];
        }
        if (!$group[0]['can_index']) {
            return ['errors' => 'This group can not index document'];
        }

        $group[0]['indexation_parameters'] = json_decode($group[0]['indexation_parameters'], true);

        return ['indexingParameters' => $group[0]['indexation_parameters']];
    }
}
