<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief List Template Controller
 * @author dev@maarch.org
 */

namespace Entity\controllers;

use Entity\models\EntityModel;
use Entity\models\ListTemplateModel;
use Group\controllers\PrivilegeController;
use History\controllers\HistoryController;
use Resource\models\ResModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\CurlModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class ListTemplateController
{
    public function get(Request $request, Response $response)
    {
        $rawListTemplates = ListTemplateModel::get(['select' => ['id', 'object_id', 'object_type', 'title', 'description']]);

        $listTemplates = [];
        $tmpTemplates = [];
        foreach ($rawListTemplates as $rawListTemplate) {
            if (empty($tmpTemplates[$rawListTemplate['object_type']][$rawListTemplate['object_id']])) {
                $listTemplates[] = $rawListTemplate;
                $tmpTemplates[$rawListTemplate['object_type']][$rawListTemplate['object_id']] = 1;
            }
        }

        return $response->withJson(['listTemplates' => $listTemplates]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        $listTemplates = ListTemplateModel::getById(['id' => $aArgs['id']]);
        if (empty($listTemplates)) {
            return $response->withStatus(400)->withJson(['errors' => 'List template not found']);
        }

        foreach ($listTemplates as $key => $value) {
            if ($value['item_type'] == 'entity_id') {
                $listTemplates[$key]['idToDisplay'] = entitymodel::getByEntityId(['entityId' => $value['item_id'], 'select' => ['entity_label']])['entity_label'];
                $listTemplates[$key]['descriptionToDisplay'] = '';
            } else {
                $listTemplates[$key]['idToDisplay'] = UserModel::getLabelledUserById(['login' => $value['item_id']]);
                $listTemplates[$key]['descriptionToDisplay'] = UserModel::getPrimaryEntityByUserId(['userId' => $value['item_id']])['entity_label'];
            }
        }

        $roles = EntityModel::getRoles();
        $listTemplateTypes = ListTemplateModel::getTypes(['select' => ['difflist_type_roles'], 'where' => ['difflist_type_id = ?'], 'data' => [$listTemplates[0]['object_type']]]);
        $rolesForService = empty($listTemplateTypes[0]['difflist_type_roles']) ? [] : explode(' ', $listTemplateTypes[0]['difflist_type_roles']);
        foreach ($roles as $key => $role) {
            if (!in_array($role['id'], $rolesForService)) {
                unset($roles[$key]);
            } elseif ($role['id'] == 'copy') {
                $entity['roles'][$key]['id'] = 'cc';
            }
        }

        $listTemplate = [
            'object_id'     => $listTemplates[0]['object_id'],
            'object_type'   => $listTemplates[0]['object_type'],
            'title'         => $listTemplates[0]['title'],
            'description'   => $listTemplates[0]['description'],
            'diffusionList' => $listTemplates,
            'roles'         => array_values($roles)
        ];

        return $response->withJson(['listTemplate' => $listTemplate]);
    }

    public function create(Request $request, Response $response)
    {
        $data = $request->getParams();

        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'manage_entities', 'userId' => $GLOBALS['id']]) && !strstr($data['object_id'], 'VISA_CIRCUIT_') && !strstr($data['object_id'], 'AVIS_CIRCUIT_')) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_listmodels', 'userId' => $GLOBALS['id']]) && (strstr($data['object_id'], 'VISA_CIRCUIT_') || strstr($data['object_id'], 'AVIS_CIRCUIT_'))) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $allowedObjectTypes = ['entity_id', 'VISA_CIRCUIT', 'AVIS_CIRCUIT'];
        $check = Validator::stringType()->notEmpty()->validate($data['object_type']) && in_array($data['object_type'], $allowedObjectTypes);
        $check = $check && (Validator::stringType()->notEmpty()->validate($data['object_id']) || $data['object_type'] != 'entity_id');
        $check = $check && Validator::arrayType()->notEmpty()->validate($data['items']);
        $check = $check && (Validator::stringType()->notEmpty()->validate($data['title']) || Validator::stringType()->notEmpty()->validate($data['description']));
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        if (!empty($data['object_id']) && $data['object_type'] != 'AVIS_CIRCUIT') {
            $listTemplate = ListTemplateModel::get(['select' => [1], 'where' => ['object_id = ?', 'object_type = ?'], 'data' => [$data['object_id'], $data['object_type']]]);
            if (!empty($listTemplate)) {
                return $response->withStatus(400)->withJson(['errors' => 'Entity is already linked to this type of template']);
            }
            $aEntities = EntityModel::getAllowedEntitiesByUserId(['userId' => $GLOBALS['userId']]);
            foreach ($aEntities as $aEntity) {
                if ($aEntity['entity_id'] == $data['object_id'] && $aEntity['allowed'] == false) {
                    return $response->withStatus(403)->withJson(['errors' => 'Entity out of perimeter']);
                }
            }
        } else {
            $data['object_id'] = $data['object_type'] . '_' . CoreConfigModel::uniqueId();
        }

        $checkItems = ListTemplateController::checkItems(['items' => $data['items']]);
        if (!empty($checkItems['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $checkItems['errors']]);
        }

        $listTemplateId = null;
        foreach ($data['items'] as $item) {
            $listTemplateId = ListTemplateModel::create([
                'object_id'     => $data['object_id'],
                'object_type'   => $data['object_type'],
                'title'         => $data['title'],
                'description'   => $data['description'],
                'sequence'      => $item['sequence'],
                'item_id'       => $item['item_id'],
                'item_type'     => $item['item_type'],
                'item_mode'     => $item['item_mode'],
            ]);
        }

        HistoryController::add([
            'tableName' => 'listmodels',
            'recordId'  => $data['object_id'],
            'eventType' => 'ADD',
            'info'      => _LIST_TEMPLATE_CREATION . " : {$data['title']} {$data['description']}",
            'moduleId'  => 'listTemplate',
            'eventId'   => 'listTemplateCreation',
        ]);

        return $response->withJson(['id' => $listTemplateId]);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        $data = $request->getParams();
        $check = Validator::arrayType()->notEmpty()->validate($data['items']);
        $check = $check && (Validator::stringType()->notEmpty()->validate($data['title']) || Validator::stringType()->notEmpty()->validate($data['description']));
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $listTemplates = ListTemplateModel::getById(['id' => $aArgs['id'], 'select' => ['object_id', 'object_type']]);
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'manage_entities', 'userId' => $GLOBALS['id']]) && !strstr($listTemplates[0]['object_id'], 'VISA_CIRCUIT_') && !strstr($listTemplates[0]['object_id'], 'AVIS_CIRCUIT_')) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_listmodels', 'userId' => $GLOBALS['id']]) && (strstr($listTemplates[0]['object_id'], 'VISA_CIRCUIT_') || strstr($listTemplates[0]['object_id'], 'AVIS_CIRCUIT_'))) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }
        if (empty($listTemplates)) {
            return $response->withStatus(400)->withJson(['errors' => 'List template not found']);
        }

        if (!strstr($listTemplates[0]['object_id'], 'VISA_CIRCUIT_') && !strstr($listTemplates[0]['object_id'], 'AVIS_CIRCUIT_')) {
            $aEntities = EntityModel::getAllowedEntitiesByUserId(['userId' => $GLOBALS['userId']]);
            foreach ($aEntities as $aEntity) {
                if ($aEntity['entity_id'] == $listTemplates[0]['object_id'] && $aEntity['allowed'] == false) {
                    return $response->withStatus(403)->withJson(['errors' => 'Entity out of perimeter']);
                }
            }
        }

        $checkItems = ListTemplateController::checkItems(['items' => $data['items']]);
        if (!empty($checkItems['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $checkItems['errors']]);
        }

        ListTemplateModel::delete([
            'where' => ['object_id = ?', 'object_type = ?'],
            'data'  => [$listTemplates[0]['object_id'], $listTemplates[0]['object_type']]
        ]);

        $listTemplateId = null;
        foreach ($data['items'] as $item) {
            $listTemplateId = ListTemplateModel::create([
                'object_id'     => $listTemplates[0]['object_id'],
                'object_type'   => $listTemplates[0]['object_type'],
                'title'         => $data['title'],
                'description'   => $data['description'],
                'sequence'      => $item['sequence'],
                'item_id'       => $item['item_id'],
                'item_type'     => $item['item_type'],
                'item_mode'     => $item['item_mode'],
            ]);
        }

        HistoryController::add([
            'tableName' => 'listmodels',
            'recordId'  => $listTemplates[0]['object_id'],
            'eventType' => 'UP',
            'info'      => _LIST_TEMPLATE_MODIFICATION . " : {$data['title']} {$data['description']}",
            'moduleId'  => 'listTemplate',
            'eventId'   => 'listTemplateModification',
        ]);

        return $response->withJson(['id' => $listTemplateId]);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        $listTemplates = ListTemplateModel::getById(['id' => $aArgs['id'], 'select' => ['object_id', 'object_type']]);
        
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'manage_entities', 'userId' => $GLOBALS['id']]) && !strstr($listTemplates[0]['object_id'], 'VISA_CIRCUIT_') && !strstr($listTemplates[0]['object_id'], 'AVIS_CIRCUIT_')) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_listmodels', 'userId' => $GLOBALS['id']]) && (strstr($listTemplates[0]['object_id'], 'VISA_CIRCUIT_') || strstr($listTemplates[0]['object_id'], 'AVIS_CIRCUIT_'))) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (empty($listTemplates)) {
            return $response->withStatus(400)->withJson(['errors' => 'List template not found']);
        }

        if (!strstr($listTemplates[0]['object_id'], 'VISA_CIRCUIT_') && !strstr($listTemplates[0]['object_id'], 'AVIS_CIRCUIT_')) {
            $aEntities = EntityModel::getAllowedEntitiesByUserId(['userId' => $GLOBALS['userId']]);
            foreach ($aEntities as $aEntity) {
                if ($aEntity['entity_id'] == $listTemplates[0]['object_id'] && $aEntity['allowed'] == false) {
                    return $response->withStatus(403)->withJson(['errors' => 'Entity out of perimeter']);
                }
            }
        }

        ListTemplateModel::delete([
            'where' => ['object_id = ?', 'object_type = ?'],
            'data'  => [$listTemplates[0]['object_id'], $listTemplates[0]['object_type']]
        ]);
        HistoryController::add([
            'tableName' => 'listmodels',
            'recordId'  => $listTemplates[0]['object_id'],
            'eventType' => 'DEL',
            'info'      => _LIST_TEMPLATE_SUPPRESSION . " : {$listTemplates[0]['object_id']} {$listTemplates[0]['object_type']}",
            'moduleId'  => 'listTemplate',
            'eventId'   => 'listTemplateSuppression',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function getByEntityId(Request $request, Response $response, array $args)
    {
        $entity = EntityModel::getById(['select' => ['entity_id'], 'id' => $args['entityId']]);
        if (empty($entity)) {
            return $response->withStatus(400)->withJson(['errors' => 'Entity does not exist']);
        }

        $listTemplates = ListTemplateModel::get(['select' => ['*'], 'where' => ['object_id = ?'], 'data' => [$entity['entity_id']]]);

        foreach ($listTemplates as $key => $value) {
            if ($value['item_id'] == '') {
                unset($listTemplates[$key]);
                continue;
            }
            if ($value['item_type'] == 'entity_id') {
                $listTemplates[$key]['labelToDisplay'] = Entitymodel::getByEntityId(['entityId' => $value['item_id'], 'select' => ['entity_label']])['entity_label'];
                $listTemplates[$key]['descriptionToDisplay'] = '';
            } else {
                $listTemplates[$key]['labelToDisplay'] = UserModel::getLabelledUserById(['login' => $value['item_id']]);
                $listTemplates[$key]['descriptionToDisplay'] = UserModel::getPrimaryEntityByUserId(['userId' => $value['item_id']])['entity_label'];

                $userInfos = UserModel::getByLowerLogin(['login' => $value['item_id'], 'select' => ['id']]);
                $listTemplates[$key]['userId'] = $userInfos['id'];
            }
        }

        return $response->withJson(['listTemplate' => $listTemplates]);
    }

    public function getByEntityIdWithMaarchParapheur(Request $request, Response $response, array $args)
    {
        $entity = EntityModel::getById(['select' => ['entity_id'], 'id' => $args['entityId']]);
        if (empty($entity)) {
            return $response->withStatus(400)->withJson(['errors' => 'Entity does not exist']);
        }

        $listTemplates = ListTemplateModel::get(['select' => ['*'], 'where' => ['object_id = ?'], 'data' => [$entity['entity_id']]]);

        foreach ($listTemplates as $key => $value) {
            if ($value['item_type'] == 'entity_id') {
                $listTemplates[$key]['labelToDisplay'] = Entitymodel::getByEntityId(['entityId' => $value['item_id'], 'select' => ['entity_label']])['entity_label'];
                $listTemplates[$key]['descriptionToDisplay'] = '';
            } else {
                $listTemplates[$key]['labelToDisplay'] = UserModel::getLabelledUserById(['login' => $value['item_id']]);
                $listTemplates[$key]['descriptionToDisplay'] = UserModel::getPrimaryEntityByUserId(['userId' => $value['item_id']])['entity_label'];

                $userInfos = UserModel::getByLowerLogin(['login' => $value['item_id'], 'select' => ['external_id']]);
                $listTemplates[$key]['externalId'] = json_decode($userInfos['external_id'], true);
                if (!empty($listTemplates[$key]['externalId']['maarchParapheur'])) {
                    $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'modules/visa/xml/remoteSignatoryBooks.xml']);
                    if ($loadedXml->signatoryBookEnabled == 'maarchParapheur') {
                        foreach ($loadedXml->signatoryBook as $signatoryBook) {
                            if ($signatoryBook->id == "maarchParapheur") {
                                $url      = $signatoryBook->url;
                                $userId   = $signatoryBook->userId;
                                $password = $signatoryBook->password;
                                break;
                            }
                        }
                        $curlResponse = CurlModel::execSimple([
                            'url'           => rtrim($url, '/') . '/rest/users/'.$listTemplates[$key]['externalId']['maarchParapheur'],
                            'basicAuth'     => ['user' => $userId, 'password' => $password],
                            'headers'       => ['content-type:application/json'],
                            'method'        => 'GET'
                        ]);
                        if (empty($curlResponse['response']['user'])) {
                            unset($listTemplates[$key]['externalId']['maarchParapheur']);
                        }
                    }
                }
            }
        }

        return $response->withJson(['listTemplate' => $listTemplates]);
    }

    public function updateByUserWithEntityDest(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_users', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }
        
        $data = $request->getParams();

        DatabaseModel::beginTransaction();

        foreach ($data['redirectListModels'] as $listModel) {
            $user = UserModel::getByLogin(['login' => $listModel['redirectUserId']]);
            if (empty($user) || $user['status'] != "OK") {
                DatabaseModel::rollbackTransaction();
                return $response->withStatus(400)->withJson(['errors' => 'User not found or not active']);
            }

            ListTemplateModel::update([
                'set'   => ['item_id' => $listModel['redirectUserId']],
                'where' => ['item_id = ?', 'object_id = ?', 'object_type = ?', 'item_mode = ?'],
                'data'  => [$data['user_id'], $listModel['object_id'], 'entity_id', 'dest']
            ]);
        }

        DatabaseModel::commitTransaction();

        return $response->withJson(['success' => 'success']);
    }

    public function getTypeRoles(Request $request, Response $response, array $aArgs)
    {
        $unneededRoles = [];
        if ($aArgs['typeId'] == 'entity_id') {
            $unneededRoles = ['visa', 'sign'];
        }
        $roles = EntityModel::getRoles();
        $listTemplateTypes = ListTemplateModel::getTypes(['select' => ['difflist_type_roles'], 'where' => ['difflist_type_id = ?'], 'data' => [$aArgs['typeId']]]);
        $rolesForType = empty($listTemplateTypes[0]['difflist_type_roles']) ? [] : explode(' ', $listTemplateTypes[0]['difflist_type_roles']);
        foreach ($roles as $key => $role) {
            if ($role['id'] == 'dest') {
                $roles[$key]['label'] = _ASSIGNEE;
            }
            if (in_array($role['id'], $unneededRoles)) {
                unset($roles[$key]);
                continue;
            }
            if (in_array($role['id'], $rolesForType)) {
                $roles[$key]['available'] = true;
            } else {
                $roles[$key]['available'] = false;
            }
            if ($role['id'] == 'copy') {
                $roles[$key]['id'] = 'cc';
            }

            $roles[$key]['usedIn'] = [];
            $listTemplates = ListTemplateModel::get(['select' => ['object_id'], 'where' => ['object_type = ?', 'item_mode = ?'], 'data' => [$aArgs['typeId'], $roles[$key]['id']]]);
            foreach ($listTemplates as $listTemplate) {
                $entity = Entitymodel::getByEntityId(['select' => ['short_label'], 'entityId' => $listTemplate['object_id']]);
                $roles[$key]['usedIn'][] = $entity['short_label'];
            }
        }

        return $response->withJson(['roles' => array_values($roles)]);
    }

    public function updateTypeRoles(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'manage_entities', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        $check = Validator::arrayType()->notEmpty()->validate($data['roles']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $roles = '';
        foreach ($data['roles'] as $role) {
            if ($role['available'] === true) {
                if ($role['id'] == 'cc') {
                    $role['id'] = 'copy';
                }

                if (!empty($roles)) {
                    $roles .= ' ';
                }
                $roles .= $role['id'];
            }
        }

        ListTemplateModel::updateTypes([
            'set'   => ['difflist_type_roles' => $roles],
            'where' => ['difflist_type_id = ?'],
            'data'  => [$aArgs['typeId']]
        ]);
        if (empty($roles)) {
            ListTemplateModel::delete([
                'where' => ['object_type = ?'],
                'data'  => [$aArgs['typeId']]
            ]);
        } else {
            ListTemplateModel::delete([
                'where' => ['object_type = ?', 'item_mode not in (?)'],
                'data'  => [$aArgs['typeId'], explode(' ', str_replace('copy', 'cc', $roles))]
            ]);
        }

        return $response->withJson(['success' => 'success']);
    }

    public function getRoles(Request $request, Response $response)
    {
        $data = $request->getQueryParams();

        $canUpdateDiffusionRecipient = false;
        $canUpdateDiffusionRoles = false;
        $triggerContext = false;

        if ($data['context'] == 'indexation') {
            $serviceRecipient = 'update_diffusion_indexing';
            $serviceRoles = 'update_diffusion_except_recipient_indexing';
            $triggerContext = true;
        } elseif ($data['context'] == 'details') {
            $serviceRecipient = 'update_diffusion_indexing';
            $serviceRoles = 'update_diffusion_except_recipient_indexing';
            $triggerContext = true;
        }

        if ($data['context'] == 'redirect') {
            $triggerContext = true;
            $canUpdateDiffusionRecipient = true;
        } elseif ($triggerContext) {
            if (PrivilegeController::hasPrivilege(['privilegeId' => $serviceRecipient, 'userId' => $GLOBALS['id']])) {
                $canUpdateDiffusionRecipient = true;
            }
            if (!$canUpdateDiffusionRecipient && PrivilegeController::hasPrivilege(['privilegeId' => $serviceRoles, 'userId' => $GLOBALS['id']])) {
                $canUpdateDiffusionRoles = true;
            }
        }

        $listTemplateTypes = ListTemplateModel::getTypes(['select' => ['difflist_type_roles'], 'where' => ['difflist_type_id = ?'], 'data' => ['entity_id']]);
        $availableRoles = empty($listTemplateTypes[0]['difflist_type_roles']) ? [] : explode(' ', $listTemplateTypes[0]['difflist_type_roles']);
        $roles = EntityModel::getRoles();
        foreach ($roles as $key => $role) {
            if (!in_array($role['id'], $availableRoles)) {
                unset($roles[$key]);
                continue;
            }
            if ($role['id'] == 'dest') {
                $roles[$key]['label'] = _ASSIGNEE;
                if ($triggerContext) {
                    $roles[$key]['canUpdate'] = $canUpdateDiffusionRecipient;
                }
            } else {
                if ($triggerContext) {
                    $roles[$key]['canUpdate'] = $canUpdateDiffusionRecipient || $canUpdateDiffusionRoles;
                }
            }
            if ($role['id'] == 'copy') {
                $roles[$key]['id'] = 'cc';
            }
        }

        return $response->withJson(['roles' => array_values($roles)]);
    }

    public function getAvailableCircuitsByResId(Request $request, Response $response, array $args)
    {
        $queryParams = $request->getQueryParams();

        if (!Validator::stringType()->notEmpty()->validate($queryParams['circuit'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query params circuit is empty']);
        }

        $circuit = $queryParams['circuit'] == 'opinion' ? 'AVIS_CIRCUIT' : 'VISA_CIRCUIT';
        $resource = ResModel::getById(['resId' => $args['resId'], 'select' => ['destination']]);

        $where = ['object_type = ?'];
        $data = [$circuit];
        if (!empty($resource['destination'])) {
            $where[] = '(object_id = ? OR object_id like ?)';
            $data[] = $resource['destination'];
            $data[] = "{$circuit}_%";
            $orderBy = ["object_id='{$resource['destination']}' DESC", 'title'];
        } else {
            $where[] = 'object_id like ?';
            $data[] = "{$circuit}_%";
            $orderBy = ['title'];
        }

        $circuits = [];
        $listTemplates = ListTemplateModel::get(['select' => ['*'], 'where' => $where, 'data' => $data, 'orderBy' => $orderBy]);
        foreach ($listTemplates as $value) {
            $circuits[$value['object_id']] = ['id' => $value['id'], 'title' => $value['title']];
        }

        $circuits = array_values($circuits);

        return $response->withJson(['circuits' => $circuits]);
    }

    private static function checkItems(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['items']);
        ValidatorModel::arrayType($aArgs, ['items']);

        $destFound = false;
        foreach ($aArgs['items'] as $item) {
            if ($destFound && $item['item_mode'] == 'dest') {
                return ['errors' => 'More than one dest not allowed'];
            }
            if (empty($item['item_id'])) {
                return ['errors' => 'Item_id is empty'];
            }
            if (empty($item['item_type'])) {
                return ['errors' => 'Item_type is empty'];
            }
            if (empty($item['item_mode'])) {
                return ['errors' => 'Item_mode is empty'];
            }
            if ($item['item_mode'] == 'dest') {
                $destFound = true;
            }
        }

        return ['success' => 'success'];
    }
}
