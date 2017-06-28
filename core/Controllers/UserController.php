<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief User Controller
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Controllers;

use Baskets\Models\BasketsModel;
use Core\Models\GroupModel;
use Core\Models\LangModel;
use Core\Models\ServiceModel;
use Entities\Models\EntityModel;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\UserModel;

include_once 'core/class/docservers_controler.php';
include_once 'core/class/class_history.php';


class UserController
{
    public function getCurrentUserInfos(RequestInterface $request, ResponseInterface $response)
    {
        $user = UserModel::getById(['userId' => $_SESSION['user']['UserId'], 'select' => ['user_id', 'firstname', 'lastname', 'phone', 'mail', 'initials', 'thumbprint']]);
        $user['signatures'] = UserModel::getSignaturesById(['userId' => $_SESSION['user']['UserId']]);
        $user['emailSignatures'] = UserModel::getEmailSignaturesById(['userId' => $_SESSION['user']['UserId']]);
        $user['groups'] = UserModel::getGroupsById(['userId' => $_SESSION['user']['UserId']]);
        $user['entities'] = UserModel::getEntitiesById(['userId' => $_SESSION['user']['UserId']]);
        $user['lang'] = LangModel::getProfileLang();
        $user['baskets'] = BasketsModel::getBasketsByUserId(['userId' => $_SESSION['user']['UserId']]);

        return $response->withJson($user);
    }

    public function update(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_users', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }
        if ($_SESSION['user']['UserId'] != 'superadmin') {
            $entities = EntityModel::getAllEntitiesByUserId(['userId' => $_SESSION['user']['UserId']]);
            $users = UserModel::getByEntities([
                'select'    => ['users.user_id'],
                'entities'  => $entities
            ]);
            $allowed = false;
            foreach ($users as $value) {
                if ($value['user_id'] == $aArgs['userId']) {
                    $allowed = true;
                }
            }
            if (!$allowed) {
                return $response->withStatus(403)->withJson(['errors' => 'UserId out of perimeter']);
            }
        } else {
            $user = UserModel::getById(['userId' => $aArgs['userId']]);
            if (empty($user)) {
                return $response->withStatus(400)->withJson(['errors' => 'User not found']);
            }
        }
        $data = $request->getParams();

        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['firstname', 'lastname']])
            || (!empty($data['mail']) && !filter_var($data['mail'], FILTER_VALIDATE_EMAIL))
            || (!empty($data['phone']) && !preg_match("/^(?:0|\+\d\d\s?)[1-9]([\.\-\s]?\d\d){4}$/", $data['phone']))) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $r = UserModel::update(['userId' => $aArgs['userId'], 'user' => $data]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Update Error']);
        }

        return $response->withJson(['success' => _USER_UPDATED]);
    }

    public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $error = $this->hasUsersRights(['userId' => $aArgs['userId']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }

        $r = UserModel::delete(['userId' => $aArgs['userId']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Delete Error']);
        }

        return $response->withJson(['success' => _DELETED_USER]);
    }

    public function updateProfile(RequestInterface $request, ResponseInterface $response)
    {
        $data = $request->getParams();

        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['firstname', 'lastname']])
            || (!empty($data['mail']) && !filter_var($data['mail'], FILTER_VALIDATE_EMAIL))
            || (!empty($data['phone']) && !preg_match("/^(?:0|\+\d\d\s?)[1-9]([\.\-\s]?\d\d){4}$/", $data['phone']))) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $r = UserModel::update(['userId' => $_SESSION['user']['UserId'], 'user' => $data]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Update Error']);
        }

        return $response->withJson(['success' => _UPDATED_PROFILE]);
    }

    public function resetPassword(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_users', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }
        if ($_SESSION['user']['UserId'] != 'superadmin') {
            $entities = EntityModel::getAllEntitiesByUserId(['userId' => $_SESSION['user']['UserId']]);
            $users = UserModel::getByEntities([
                'select'    => ['users.user_id'],
                'entities'  => $entities
            ]);
            $allowed = false;
            foreach ($users as $value) {
                if ($value['user_id'] == $aArgs['userId']) {
                    $allowed = true;
                }
            }
            if (!$allowed) {
                return $response->withStatus(403)->withJson(['errors' => 'UserId out of perimeter']);
            }
        } else {
            $user = UserModel::getById(['userId' => $aArgs['userId']]);
            if (empty($user)) {
                return $response->withStatus(400)->withJson(['errors' => 'User not found']);
            }
        }

        $r = UserModel::resetPassword(['userId' => $aArgs['userId']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'Password Update Error']);
        }

        return $response->withJson(['success' => _RESET_PASSWORD]);
    }

    public function updateCurrentUserPassword(RequestInterface $request, ResponseInterface $response)
    {
        $data = $request->getParams();

        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['currentPassword', 'newPassword', 'reNewPassword']])) {
            return $response->withJson(['errors' => _EMPTY_PSW_FORM]);
        }

        if ($data['newPassword'] != $data['reNewPassword']) {
            return $response->withJson(['errors' => _WRONG_SECOND_PSW]);
        } elseif (!UserModel::checkPassword(['userId' => $_SESSION['user']['UserId'],'password' => $data['currentPassword']])) {
            return $response->withJson(['errors' => _WRONG_PSW]);
        }

        $r = UserModel::updatePassword(['userId' => $_SESSION['user']['UserId'], 'password' => $data['newPassword']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'Password Update Error']);
        }

        return $response->withJson(['success' => _UPDATED_PASSWORD]);
    }

    public function setCurrentUserBasketsRedirectionForAbsence(RequestInterface $request, ResponseInterface $response) {
        if (empty($_SESSION['user']['UserId'])) {
            return $response->withStatus(401)->withJson(['errors' => 'User Not Connected']);
        }

        $data = $request->getParams();

        foreach ($data as $value) {
            if (empty($value['newUser']) || empty($value['basketId']) || empty($value['basketOwner']) || empty($value['virtual'])) {
                return $response->withStatus(400)->withJson(['errors' => _FORM_ERROR]);
            }

            $check = UserModel::getById(['userId' => $value['newUser'], 'select' => ['1']]);

            if (empty($check)) {
                return $response->withStatus(400)->withJson(['errors' => _UNDEFINED_USER]);
            }
        }

        if (!empty($data)) {
            BasketsModel::setBasketsRedirection(['userId' => $_SESSION['user']['UserId'], 'data' => $data]);
        }

        UserModel::activateAbsenceById(['userId' => $_SESSION['user']['UserId']]);
        if($_SESSION['history']['userabs'] == "true") { //TODO No Session
            $history = new \history();
            $currentUser = UserModel::getLabelledUserById(['id' => $_SESSION['user']['UserId']]);
            $history->add($_SESSION['tablename']['users'], $_SESSION['user']['UserId'], 'ABS', 'userabs', _ABS_USER.' : '.$currentUser, $_SESSION['config']['databasetype']);
        }

        return $response->withJson(['success' => 'success']);
    }

    public function createCurrentUserSignature(RequestInterface $request, ResponseInterface $response)
    {
        $data = $request->getParams();

        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['base64', 'name', 'label']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $file     = base64_decode($data['base64']);
        $tmpName  = 'tmp_file_' .$_SESSION['user']['UserId']. '_' .rand(). '_' .$data['name'];

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($file);
        $size     = strlen($file);
        $type     = explode('/', $mimeType);
        $ext      = strtoupper(substr($data['name'], strrpos($data['name'], '.') + 1));

        if (file_exists('custom/' .$_SESSION['custom_override_id']. '/apps/maarch_entreprise/xml/extensions.xml')) {
            $path = 'custom/' .$_SESSION['custom_override_id']. '/apps/maarch_entreprise/xml/extensions.xml';
        } else {
            $path = 'apps/maarch_entreprise/xml/extensions.xml';
        }

        $xmlfile  = simplexml_load_file($path);

        $fileAccepted = false;
        if (count($xmlfile->FORMAT) > 0) {
            foreach ($xmlfile->FORMAT as $value) {
                if(strtoupper($value->name) == $ext && strtoupper($value->mime) == strtoupper($mimeType)){
                    $fileAccepted = true;
                    break;
                }
            }
        }

        if (!$fileAccepted || $type[0] != 'image') {
            return $response->withJson(['errors' => _WRONG_FILE_TYPE]);
        } elseif ($size > 2000000){
            return $response->withJson(['errors' => _MAX_SIZE_UPLOAD_REACHED . ' (2 MB)']);
        }

        file_put_contents($_SESSION['config']['tmppath'] . $tmpName, $file);

        $docservers_controler = new \docservers_controler();
        $storeInfos = $docservers_controler->storeResourceOnDocserver(
            'templates',
            [
                'tmpDir'      => $_SESSION['config']['tmppath'],
                'size'        => $data['size'],
                'format'      => $ext,
                'tmpFileName' => $tmpName
            ]
        );

        if (!file_exists($storeInfos['path_template']. str_replace('#', '/', $storeInfos['destination_dir']) .$storeInfos['file_destination_name'])) {
            return $response->withJson(['errors' => $storeInfos['error'] .' templates']);
        }

        $r = UserModel::createSignature([
            'userId'            => $_SESSION['user']['UserId'],
            'signatureLabel'    => $data['label'],
            'signaturePath'     => $storeInfos['destination_dir'],
            'signatureFileName' => $storeInfos['file_destination_name'],
        ]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'Signature Create Error']);
        }

        return $response->withJson([
            'success' => _NEW_SIGNATURE,
            'signatures' => UserModel::getSignaturesById(['userId' => $_SESSION['user']['UserId']])
        ]);
    }

    public function updateCurrentUserSignature(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $data = $request->getParams();

        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['label']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $r = UserModel::updateSignature([
            'id'        => $aArgs['id'],
            'userId'    => $_SESSION['user']['UserId'],
            'label'     => $data['label']
        ]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'Signature Update Error']);
        }

        return $response->withJson([
            'success' => _UPDATED_SIGNATURE,
            'signature' => UserModel::getSignatureWithSignatureIdById(['userId' => $_SESSION['user']['UserId'], 'signatureId' => $aArgs['id']])
        ]);
    }

    public function deleteCurrentUserSignature(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $r = UserModel::deleteSignature(['signatureId' => $aArgs['id'], 'userId' => $_SESSION['user']['UserId']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'Signature Creation Error']);
        }

        return $response->withJson([
            'success' => _DELETED_SIGNATURE,
            'signatures' => UserModel::getSignaturesById(['userId' => $_SESSION['user']['UserId']])
        ]);
    }

    public function createCurrentUserEmailSignature(RequestInterface $request, ResponseInterface $response)
    {
        $data = $request->getParams();

        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['title', 'htmlBody']])) {
            return $response->withJson(['errors' => _EMPTY_EMAIL_SIGNATURE_FORM]);
        }

        $r = UserModel::createEmailSignature([
            'userId'    => $_SESSION['user']['UserId'],
            'title'     => $data['title'],
            'htmlBody'  => $data['htmlBody']
        ]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'Email Signature Creation Error']);
        }

        return $response->withJson([
            'success' => _NEW_EMAIL_SIGNATURE,
            'emailSignatures' => UserModel::getEmailSignaturesById(['userId' => $_SESSION['user']['UserId']])
        ]);
    }

    public function updateCurrentUserEmailSignature(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $data = $request->getParams();

        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['title', 'htmlBody']])) {
            return $response->withJson(['errors' => _EMPTY_EMAIL_SIGNATURE_FORM]);
        }

        $r = UserModel::updateEmailSignature([
            'id'        => $aArgs['id'],
            'userId'    => $_SESSION['user']['UserId'],
            'title'     => $data['title'],
            'htmlBody'  => $data['htmlBody']
        ]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'Email Signature Update Error']);
        }

        return $response->withJson([
            'success' => _UPDATED_EMAIL_SIGNATURE,
            'emailSignature' => UserModel::getEmailSignatureWithSignatureIdById(['userId' => $_SESSION['user']['UserId'], 'signatureId' => $aArgs['id']])
        ]);
    }

    public function deleteCurrentUserEmailSignature(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $r = UserModel::deleteEmailSignature([
            'id'        => $aArgs['id'],
            'userId'    => $_SESSION['user']['UserId']
        ]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'Email Signature Delete Error']);
        }

        return $response->withJson([
            'success' => _DELETED_EMAIL_SIGNATURE,
            'emailSignatures' => UserModel::getEmailSignaturesById(['userId' => $_SESSION['user']['UserId']])
        ]);
    }

    public function getUsersForAutocompletion(RequestInterface $request, ResponseInterface $response)
    {
        $users = UserModel::get([
            'select'    => ['user_id', 'firstname', 'lastname'],
            'where'     => ['enabled = ?', 'status != ?', 'user_id != ?'],
            'data'      => ['Y', 'DEL', 'superadmin']
        ]);

        foreach ($users as $key => $value) {
            $users[$key]['formattedUser'] = "{$value['firstname']} {$value['lastname']} ({$value['user_id']})";
        }

        return $response->withJson($users);
    }

    public function getUsersForAdministration(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_users', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }
        if ($_SESSION['user']['UserId'] == 'superadmin') {
            $users = UserModel::get([
                'select'    => ['user_id', 'firstname', 'lastname', 'status', 'enabled', 'mail'],
                'where'     => ['user_id != ?', 'status != ?'],
                'data'      => ['superadmin', 'DEL']
            ]);
        } else {
            $entities = EntityModel::getAllEntitiesByUserId(['userId' => $_SESSION['user']['UserId']]);
            $users = UserModel::getByEntities([
                'select'    => ['DISTINCT users.user_id', 'firstname', 'lastname', 'status', 'enabled', 'mail'],
                'entities'  => $entities
            ]);
        }
        $return['lang'] = LangModel::getUsersForAdministrationLang();
        $return['users'] = $users;        
        return $response->withJson($return);
    }

    public function getUserForAdministration(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $error = $this->hasUsersRights(['userId' => $aArgs['userId']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }
        $user = UserModel::getById(['userId' => $aArgs['userId'], 'select' => ['user_id', 'firstname', 'lastname', 'status', 'enabled', 'phone', 'mail', 'initials', 'thumbprint']]);
        $user['signatures'] = UserModel::getSignaturesById(['userId' => $aArgs['userId']]);
        $user['emailSignatures'] = UserModel::getEmailSignaturesById(['userId' => $aArgs['userId']]);
        $user['groups'] = UserModel::getGroupsById(['userId' => $aArgs['userId']]);
        $user['entities'] = UserModel::getEntitiesById(['userId' => $aArgs['userId']]);
        $user['lang'] = LangModel::getUserAdministrationLang();
        $user['baskets'] = BasketsModel::getBasketsByUserId(['userId' => $aArgs['userId']]);

        return $response->withJson($user);
    }

    public function addGroup(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $error = $this->hasUsersRights(['userId' => $aArgs['userId']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }
        if (empty(GroupModel::getById(['groupId' => $aArgs['groupId']]))) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }
        $data = $request->getParams();
        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['role']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $r = UserModel::addGroup(['userId' => $aArgs['userId'], 'groupId' => $aArgs['groupId'], 'role' => $data['role']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Group Update Error']);
        }

        return $response->withJson(['success' => _ADDED_GROUP]);
    }

    public function updateGroup(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $error = $this->hasUsersRights(['userId' => $aArgs['userId']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }
        if (empty(GroupModel::getById(['groupId' => $aArgs['groupId']]))) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        $data = $request->getParams();
        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['role']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $r = UserModel::updateGroup(['userId' => $aArgs['userId'], 'groupId' => $aArgs['groupId'], 'role' => $data['role']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Group Update Error']);
        }

        return $response->withJson(['success' => _ADDED_GROUP]);
    }

    public function deleteGroup(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $error = $this->hasUsersRights(['userId' => $aArgs['userId']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }
        if (empty(GroupModel::getById(['groupId' => $aArgs['groupId']]))) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        $r = UserModel::deleteGroup(['userId' => $aArgs['userId'], 'groupId' => $aArgs['groupId']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Group Update Error']);
        }

        return $response->withJson(['success' => _ADDED_GROUP, 'groups' => UserModel::getGroupsById(['userId' => $aArgs['userId']])]);
    }

    public function addEntity(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $error = $this->hasUsersRights(['userId' => $aArgs['userId']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }
        if (empty(EntityModel::getById(['entityId' => $aArgs['entityId']]))) {
            return $response->withStatus(400)->withJson(['errors' => 'Entity not found']);
        }
        $data = $request->getParams();
        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['role']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $r = UserModel::addEntity(['userId' => $aArgs['userId'], 'entityId' => $aArgs['groupId'], 'role' => $data['role']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Entity Update Error']);
        }

        return $response->withJson(['success' => _ADDED_GROUP]);
    }

    public function updateEntity(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $error = $this->hasUsersRights(['userId' => $aArgs['userId']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }
        if (empty(EntityModel::getById(['entityId' => $aArgs['entityId']]))) {
            return $response->withStatus(400)->withJson(['errors' => 'Entity not found']);
        }

        $data = $request->getParams();
        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['user_role']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request : Missing Argument']);
        }

        $r = UserModel::updateEntity(['userId' => $aArgs['userId'], 'entityId' => $aArgs['entityId'], 'role' => $data['user_role']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Entity Update Error']);
        }

        return $response->withJson(['success' => _ADDED_GROUP]);
    }

    public function updatePrimaryEntity(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $error = $this->hasUsersRights(['userId' => $aArgs['userId']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }
        if (empty(EntityModel::getById(['entityId' => $aArgs['entityId']]))) {
            return $response->withStatus(400)->withJson(['errors' => 'Entity not found']);
        }

        $r = UserModel::updatePrimaryEntity(['userId' => $aArgs['userId'], 'entityId' => $aArgs['entityId']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Entity Update Error']);
        }

        return $response->withJson(['success' => _ADDED_GROUP, 'entities' => UserModel::getEntitiesById(['userId' => $aArgs['userId']])]);
    }

    public function deleteEntity(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $error = $this->hasUsersRights(['userId' => $aArgs['userId']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }
        if (empty(EntityModel::getById(['entityId' => $aArgs['entityId']]))) {
            return $response->withStatus(400)->withJson(['errors' => 'Entity not found']);
        }

        $primaryEntity = UserModel::getPrimaryEntityById(['userId' => $aArgs['userId']]);
        $r = UserModel::deleteEntity(['userId' => $aArgs['userId'], 'entityId' => $aArgs['entityId']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Entity Update Error']);
        }
        if (!empty($primaryEntity['entity_id']) && $primaryEntity['entity_id'] == $aArgs['entityId']) {
            UserModel::reassignPrimaryEntity(['userId' => $aArgs['userId']]);
        }

        return $response->withJson(['success' => _ADDED_GROUP, 'entities' => UserModel::getEntitiesById(['userId' => $aArgs['userId']])]);
    }

    private function hasUsersRights(array $aArgs = [])
    {
        $error = [
            'status'    => 200,
            'error'     => ''
        ];
        if (!ServiceModel::hasService(['id' => 'admin_users', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            $error['status'] = 403;
            $error['error'] = 'Service forbidden';
        }
        if ($_SESSION['user']['UserId'] != 'superadmin') {
            $entities = EntityModel::getAllEntitiesByUserId(['userId' => $_SESSION['user']['UserId']]);
            $users = UserModel::getByEntities([
                'select'    => ['users.user_id'],
                'entities'  => $entities
            ]);
            $allowed = false;
            foreach ($users as $value) {
                if ($value['user_id'] == $aArgs['userId']) {
                    $allowed = true;
                }
            }
            if (!$allowed) {
                $error['status'] = 403;
                $error['error'] = 'UserId out of perimeter';
            }
        } else {
            $user = UserModel::getById(['userId' => $aArgs['userId']]);
            if (empty($user)) {
                $error['status'] = 400;
                $error['error'] = 'User not found';
            }
        }

        return $error;
    }

    private function checkNeededParameters(array $aArgs = [])
    {
        foreach ($aArgs['needed'] as $value) {
            if (empty($aArgs['data'][$value])) {
                return false;
            }
        }

        return true;
    }
}
