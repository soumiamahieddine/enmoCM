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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\UserModel;

class UserController
{
    public function getCurrentUserInfos(RequestInterface $request, ResponseInterface $response)
    {
        if (empty($_SESSION['user']['UserId'])) {
            return $response->withStatus(401)->withJson(['errors' => 'User Not Connected']);
        }

        $user = UserModel::getById(['userId' => $_SESSION['user']['UserId'], 'select' => ['user_id', 'firstname', 'lastname', 'phone', 'mail', 'initials', 'thumbprint']]);
        $user['signatures'] = UserModel::getSignaturesById(['userId' => $_SESSION['user']['UserId']]);
        $user['emailSignatures'] = UserModel::getEmailSignaturesById(['userId' => $_SESSION['user']['UserId']]);
        $user['groups'] = UserModel::getGroupsById(['userId' => $_SESSION['user']['UserId']]);
        $user['entities'] = UserModel::getEntitiesById(['userId' => $_SESSION['user']['UserId']]);

        return $response->withJson($user);
    }

    public function update(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        $data = $request->getParams();

        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['firstname', 'lastname']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $r = UserModel::update(['userId' => $aArgs['id'] ,'user' => $data]);

        if ($r) {
            return $response->withJson([]);
        } else {
            return $response->withStatus(500)->withJson(['errors' => 'User Update Error']);
        }
    }

    public function updateProfile(RequestInterface $request, ResponseInterface $response)
    {
        $data = $request->getParams();

        if (!$this->checkNeededParameters(['data' => $data, 'needed' => ['firstname', 'lastname']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $r = UserModel::update(['userId' => $_SESSION['user']['UserId'], 'user' => $data]);

        if (!$r) {
            return $response->withStatus(500)->withJson(['errors' => 'User Update Error']);
        }

        return $response->withJson([]);
    }

    public function updatePassword(RequestInterface $request, ResponseInterface $response)
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

        return $response->withJson([]);
    }

    private function checkNeededParameters($aArgs = []) {
        foreach ($aArgs['needed'] as $value) {
            if (empty($aArgs['data'][$value])) {
                return false;
            }
        }

        return true;
    }
}
