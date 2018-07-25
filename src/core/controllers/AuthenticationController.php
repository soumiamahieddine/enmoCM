<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Authentication Controller
 *
 * @author dev@maarch.org
 */

namespace SrcCore\controllers;

use SrcCore\models\AuthenticationModel;
use SrcCore\models\PasswordModel;
use SrcCore\models\SecurityModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class AuthenticationController
{
    public static function authentication()
    {
        $userId = null;
        if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
            if (AuthenticationModel::authentication(['userId' => $_SERVER['PHP_AUTH_USER'], 'password' => $_SERVER['PHP_AUTH_PW']])) {
                $userId = $_SERVER['PHP_AUTH_USER'];
            }
        } else {
            $cookie = SecurityModel::getCookieAuth();
            if (!empty($cookie) && SecurityModel::cookieAuthentication($cookie)) {
                SecurityModel::setCookieAuth(['userId' => $cookie['userId']]);
                $userId = $cookie['userId'];
            }
        }

        return $userId;
    }

    public static function handleFailedAuthentication(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $passwordRules = PasswordModel::getEnabledRules();

        if (!empty($passwordRules['lockAttempts'])) {
            $user = UserModel::getByUserId(['select' => ['failed_authentication', 'locked_until'], 'userId' => $aArgs['userId']]);

            if (!empty($user)) {
                if (!empty($user['locked_until'])) {
                    $lockedDate = new \DateTime($user['locked_until']);
                    $currentDate = new \DateTime();
                    if ($currentDate > $lockedDate) {
                        AuthenticationModel::resetFailedAuthentication(['userId' => $aArgs['userId']]);
                        $user['failed_authentication'] = 0;
                    } else {
                        return _ACCOUNT_LOCKED_UNTIL . " {$lockedDate->format('d/m/Y H:i')}";
                    }
                }

                AuthenticationModel::increaseFailedAuthentication(['userId' => $aArgs['userId'], 'tentatives' => $user['failed_authentication'] + 1]);

                if (!empty($user['failed_authentication']) && ($user['failed_authentication'] + 1) >= $passwordRules['lockAttempts'] && !empty($passwordRules['lockTime'])) {
                    $lockedUntil = time() + 60 * $passwordRules['lockTime'];
                    AuthenticationModel::lockUser(['userId' => $aArgs['userId'], 'lockedUntil' => $lockedUntil]);
                    return _ACCOUNT_LOCKED_FOR . " {$passwordRules['lockTime']} mn";
                }
            }
        }

        return _BAD_LOGIN_OR_PSW;
    }
}
