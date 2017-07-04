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
use Entities\Models\ListModelsModel;

include_once 'core/class/docservers_controler.php';
include_once 'core/class/class_history.php';


class UserController
{
    public function getCurrentUserInfos(RequestInterface $request, ResponseInterface $response)
    {
        $user = UserModel::getByUserId(['userId' => $_SESSION['user']['UserId'], 'select' => ['id', 'user_id', 'firstname', 'lastname', 'phone', 'mail', 'initials', 'thumbprint']]);
        $user['signatures'] = UserModel::getSignaturesById(['id' => $user['id']]);
        $user['emailSignatures'] = UserModel::getEmailSignaturesById(['userId' => $_SESSION['user']['UserId']]);
        $user['groups'] = UserModel::getGroupsByUserId(['userId' => $_SESSION['user']['UserId']]);
        $user['entities'] = UserModel::getEntitiesById(['userId' => $_SESSION['user']['UserId']]);
        $user['baskets'] = BasketsModel::getBasketsByUserId(['userId' => $_SESSION['user']['UserId']]);
        $user['lang'] = LangModel::getProfileLang();

        return $response->withJson($user);
    }

    public function create(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_users', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['userId', 'firstname', 'lastname']])
            || (!empty($data['mail']) && !filter_var($data['mail'], FILTER_VALIDATE_EMAIL))
            || (!empty($data['phone']) && !preg_match("/^(?:0|\+\d\d\s?)[1-9]([\.\-\s]?\d\d){4}$/", $data['phone']))) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        UserModel::create(['user' => $data]);

        return $response->withJson(['success' => _USER_ADDED]);
    }

    public function update(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $error = $this->hasUsersRights(['userId' => $aArgs['userId']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
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
    
    public function suspendUser(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $error = $this->hasUsersRights(['userId' => $aArgs['userId']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }

        $data = $request->getParams();
        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['firstname', 'lastname']])
            || (!empty($data['mail']) && !filter_var($data['mail'], FILTER_VALIDATE_EMAIL))
            || (!empty($data['phone']) && !preg_match("/^(?:0|\+\d\d\s?)[1-9]([\.\-\s]?\d\d){4}$/", $data['phone']))) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        //update user
        $r = UserModel::update(['userId' => $aArgs['userId'], 'user' => $data]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Update Error']);
        }

        return $response->withJson(['success' => _USER_UPDATED]);
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
        $error = $this->hasUsersRights(['userId' => $aArgs['userId']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
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
            return $response->withStatus(400)->withJson(['errors' => _WRONG_SECOND_PSW]);
        } elseif (!UserModel::checkPassword(['userId' => $_SESSION['user']['UserId'],'password' => $data['currentPassword']])) {
            return $response->withJson(['errors' => _WRONG_PSW]);
        }

        $r = UserModel::updatePassword(['userId' => $_SESSION['user']['UserId'], 'password' => $data['newPassword']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'Password Update Error']);
        }

        return $response->withJson(['success' => _UPDATED_PASSWORD]);
    }

    public function setBasketsRedirectionForAbsence(RequestInterface $request, ResponseInterface $response, $aArgs) {
        $error = $this->hasUsersRights(['userId' => $aArgs['userId'], 'himself' => true]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }

        $data = $request->getParams();

        foreach ($data as $value) {
            if (empty($value['newUser']) || empty($value['basketId']) || empty($value['basketOwner']) || empty($value['virtual'])) {
                return $response->withStatus(400)->withJson(['errors' => _FORM_ERROR]);
            }

            $check = UserModel::getByUserId(['userId' => $value['newUser'], 'select' => ['1']]);

            if (empty($check)) {
                return $response->withStatus(400)->withJson(['errors' => _UNDEFINED_USER]);
            }
        }

        if (!empty($data)) {
            BasketsModel::setBasketsRedirection(['userId' => $aArgs['userId'], 'data' => $data]);
        }

        UserModel::activateAbsenceById(['userId' => $aArgs['userId']]);
        if($_SESSION['history']['userabs'] == "true") { //TODO No Session
            $history = new \history();
            $absenceUser = UserModel::getLabelledUserById(['id' => $aArgs['userId']]);
            $history->add($_SESSION['tablename']['users'], $_SESSION['user']['UserId'], 'ABS', 'userabs', _ABS_USER.' : '.$absenceUser, $_SESSION['config']['databasetype']);
        }

        return $response->withJson([
            'success'   => _ABSENCE_ACTIVATED,
            'user'      => UserModel::getByUserId(['userId' => $aArgs['userId'], 'select' => ['status']])
        ]);
    }

    public function addSignature(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $userCheck = UserModel::getById(['id' => $aArgs['id'], 'select' => ['user_id']]); // TODO changez le hasUsersRights avec id
        if (empty($userCheck)) {
            return $response->withStatus(400)->withJson(['errors' => 'User not found']);
        }

        $error = $this->hasUsersRights(['userId' => $userCheck['user_id'], 'himself' => true]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }

        $data = $request->getParams();

        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['base64', 'name', 'label']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $file     = base64_decode($data['base64']);
        $tmpName  = "tmp_file_{$aArgs['id']}_" .rand(). "_{$data['name']}";

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
            return $response->withStatus(400)->withJson(['errors' => _WRONG_FILE_TYPE]);
        } elseif ($size > 2000000){
            return $response->withStatus(400)->withJson(['errors' => _MAX_SIZE_UPLOAD_REACHED . ' (2 MB)']);
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
            return $response->withStatus(500)->withJson(['errors' => $storeInfos['error'] .' templates']);
        }

        UserModel::createSignature([
            'userSerialId'      => $aArgs['id'],
            'signatureLabel'    => $data['label'],
            'signaturePath'     => $storeInfos['destination_dir'],
            'signatureFileName' => $storeInfos['file_destination_name'],
        ]);

        return $response->withJson([
            'success' => _NEW_SIGNATURE,
            'signatures' => UserModel::getSignaturesById(['id' => $aArgs['id']])
        ]);
    }

    public function updateSignature(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $userCheck = UserModel::getById(['id' => $aArgs['id'], 'select' => ['user_id']]); // TODO changez le hasUsersRights avec id
        if (empty($userCheck)) {
            return $response->withStatus(400)->withJson(['errors' => 'User not found']);
        }

        $error = $this->hasUsersRights(['userId' => $userCheck['user_id'], 'himself' => true]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }

        $data = $request->getParams();

        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['label']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        UserModel::updateSignature([
            'signatureId'   => $aArgs['signatureId'],
            'userSerialId'  => $aArgs['id'],
            'label'         => $data['label']
        ]);

        return $response->withJson([
            'success' => _UPDATED_SIGNATURE,
            'signature' => UserModel::getSignatureWithSignatureIdById(['id' => $aArgs['id'], 'signatureId' => $aArgs['signatureId']])
        ]);
    }

    public function deleteSignature(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $userCheck = UserModel::getById(['id' => $aArgs['id'], 'select' => ['user_id']]); // TODO changez le hasUsersRights avec id
        if (empty($userCheck)) {
            return $response->withStatus(400)->withJson(['errors' => 'User not found']);
        }

        $error = $this->hasUsersRights(['userId' => $userCheck['user_id'], 'himself' => true]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }

        UserModel::deleteSignature(['signatureId' => $aArgs['signatureId'], 'userSerialId' => $aArgs['id']]);

        return $response->withJson([
            'success' => _DELETED_SIGNATURE,
            'signatures' => UserModel::getSignaturesById(['id' => $aArgs['id']])
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

    public function getUsersForAutocompletionWithExclusion(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $excludeUsers = ['superadmin',$aArgs['userId']];
        $users = UserModel::get([
            'select'    => ['user_id', 'firstname', 'lastname'],
            'where'     => ['enabled = ?', 'status != ?', 'user_id not in (?)'],
            'data'      => ['Y', 'DEL', $excludeUsers]
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

        $usersId = [];
        foreach ($users as $value) {
            $usersId[] = $value['user_id'];
        }

        $listModels = ListModelsModel::getDiffListByUsersId(['select' => 'item_id', 'users_id' => $usersId, 'object_type' => 'entity_id', 'item_mode' => 'dest']); 
        
        $usersListModels = [];
        foreach ($listModels as $value) {
            $usersListModels[] = $value['item_id'];
        }
        
        foreach ($users as $key => $value) {

            if (in_array($value['user_id'], $usersListModels)) {
                $users[$key]['inDiffListDest'] = 'Y';
            } else {
                $users[$key]['inDiffListDest'] = 'N';
            }
        }

        $return['lang'] = LangModel::getUsersAdministrationLang();
        $return['users'] = $users;        
        return $response->withJson($return);
    }

    public function getUserForAdministration(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $userCheck = UserModel::getById(['id' => $aArgs['id'], 'select' => ['user_id']]); // TODO changer le hasUsersRights avec id
        if (empty($userCheck)) {
            return $response->withStatus(400)->withJson(['errors' => 'User not found']);
        }
        $error = $this->hasUsersRights(['userId' => $userCheck['user_id']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }

        $user = UserModel::getById(['id' => $aArgs['id'], 'select' => ['id', 'user_id', 'firstname', 'lastname', 'status', 'enabled', 'phone', 'mail', 'initials', 'thumbprint']]);
        $user['signatures'] = UserModel::getSignaturesById(['id' => $aArgs['id']]);
        $user['emailSignatures'] = UserModel::getEmailSignaturesById(['userId' => $user['user_id']]);
        $user['groups'] = UserModel::getGroupsByUserId(['userId' => $user['user_id']]);
        $user['allGroups'] = GroupModel::getAvailableGroupsByUserId(['userId' => $user['user_id']]);
        $user['entities'] = UserModel::getEntitiesById(['userId' => $user['user_id']]);
        $user['allEntities'] = EntityModel::getAvailableEntitiesForAdministratorByUserId(['userId' => $user['user_id'], 'administratorUserId' => $_SESSION['user']['UserId']]);
        $user['baskets'] = BasketsModel::getBasketsByUserId(['userId' => $user['user_id']]);
        $user['lang'] = LangModel::getUsersAdministrationLang();

        return $response->withJson($user);
    }

    public function getNewUserForAdministration(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_users', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $user = [];
        $user['lang'] = LangModel::getUsersAdministrationLang();

        return $response->withJson($user);
    }

    public function addGroup(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $error = $this->hasUsersRights(['userId' => $aArgs['userId']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }

        $data = $request->getParams();
        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['groupId']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }
        if (empty(GroupModel::getById(['groupId' => $data['groupId']]))) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        } elseif (UserModel::hasGroup(['userId' => $aArgs['userId'], 'groupId' => $data['groupId']])) {
            return $response->withStatus(400)->withJson(['errors' => 'User is already linked to this group']);
        }
        if (empty($data['role'])) {
            $data['role'] = '';
        }

        $r = UserModel::addGroup(['userId' => $aArgs['userId'], 'groupId' => $data['groupId'], 'role' => $data['role']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Group Update Error']);
        }

        return $response->withJson([
            'success'   => _ADDED_GROUP,
            'groups'    => UserModel::getGroupsByUserId(['userId' => $aArgs['userId']]),
            'allGroups' => GroupModel::getAvailableGroupsByUserId(['userId' => $aArgs['userId']])
        ]);
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
        if (empty($data['role'])) {
            $data['role'] = '';
        }

        $r = UserModel::updateGroup(['userId' => $aArgs['userId'], 'groupId' => $aArgs['groupId'], 'role' => $data['role']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Group Update Error']);
        }

        return $response->withJson(['success' => _GROUP_UPDATED]);
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

        return $response->withJson([
            'success'   => _DELETED_GROUP,
            'groups'    => UserModel::getGroupsByUserId(['userId' => $aArgs['userId']]),
            'allGroups' => GroupModel::getAvailableGroupsByUserId(['userId' => $aArgs['userId']])
        ]);
    }

    public function addEntity(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $error = $this->hasUsersRights(['userId' => $aArgs['userId']]);
        if (!empty($error['error'])) {
            return $response->withStatus($error['status'])->withJson(['errors' => $error['error']]);
        }

        $data = $request->getParams();
        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['entityId']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }
        if (empty(EntityModel::getById(['entityId' => $data['entityId']]))) {
            return $response->withStatus(400)->withJson(['errors' => 'Entity not found']);
        } elseif (UserModel::hasEntity(['userId' => $aArgs['userId'], 'entityId' => $data['entityId']])) {
            return $response->withStatus(400)->withJson(['errors' => 'User is already linked to this entity']);
        }
        if (empty($data['role'])) {
            $data['role'] = '';
        }
        $primaryEntity = UserModel::getPrimaryEntityById(['userId' => $aArgs['userId']]);
        $pEntity = 'N';
        if (empty($primaryEntity)) {
            $pEntity = 'Y';
        }

        $r = UserModel::addEntity(['userId' => $aArgs['userId'], 'entityId' => $data['entityId'], 'role' => $data['role'], 'primaryEntity' => $pEntity]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Entity Update Error']);
        }

        return $response->withJson([
            'success'       => _ADDED_ENTITY,
            'entities'      => UserModel::getEntitiesById(['userId' => $aArgs['userId']]),
            'allEntities'   => EntityModel::getAvailableEntitiesForAdministratorByUserId(['userId' => $aArgs['userId'], 'administratorUserId' => $_SESSION['user']['UserId']])
        ]);
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
        if (empty($data['user_role'])) {
            $data['user_role'] = '';
        }

        $r = UserModel::updateEntity(['userId' => $aArgs['userId'], 'entityId' => $aArgs['entityId'], 'role' => $data['user_role']]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Entity Update Error']);
        }

        return $response->withJson(['success' => _UPDATED_ENTITY]);
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

        return $response->withJson(['success' => _UPDATED_ENTITY, 'entities' => UserModel::getEntitiesById(['userId' => $aArgs['userId']])]);
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

        return $response->withJson([
            'success'       => _DELETED_ENTITY,
            'entities'      => UserModel::getEntitiesById(['userId' => $aArgs['userId']]),
            'allEntities'   => EntityModel::getAvailableEntitiesForAdministratorByUserId(['userId' => $aArgs['userId'], 'administratorUserId' => $_SESSION['user']['UserId']])
        ]);
    }

    private function hasUsersRights(array $aArgs = [])
    {
        $error = [
            'status'    => 200,
            'error'     => ''
        ];
        if (empty($aArgs['himself']) || $_SESSION['user']['UserId'] != $aArgs['userId']) {
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
                $user = UserModel::getByUserId(['userId' => $aArgs['userId']]);
                if (empty($user)) {
                    $error['status'] = 400;
                    $error['error'] = 'User not found';
                }
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
