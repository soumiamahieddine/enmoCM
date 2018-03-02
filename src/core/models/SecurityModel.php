<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Security Model
* @author dev@maarch.org
*/

namespace SrcCore\models;

class SecurityModel
{
    public static function getPasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function authentication(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'password']);
        ValidatorModel::stringType($args, ['userId', 'password']);

        $aReturn = DatabaseModel::select([
            'select'    => ['password'],
            'table'     => ['users'],
            'where'     => ['user_id = ?', 'status != ?'],
            'data'      => [$args['userId'], 'DEL']
        ]);

        if (empty($aReturn[0])) {
            return false;
        }

        return password_verify($args['password'], $aReturn[0]['password']);
    }

    public static function getCookieAuth()
    {
        $rawCookie = $_COOKIE['maarchCourrierAuth'];
        if (empty($rawCookie)) {
            return [];
        }

        $cookieDecoded = base64_decode($rawCookie);
        $cookie = json_decode($cookieDecoded);

        return (array)$cookie;
    }

    public static function cookieAuthentication(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'cookieKey']);
        ValidatorModel::stringType($args, ['userId', 'cookieKey']);

        $aReturn = DatabaseModel::select([
            'select'    => [1],
            'table'     => ['users'],
            'where'     => ['user_id = ?', 'cookie_key = ?', 'cookie_date > CURRENT_TIMESTAMP'],
            'data'      => [$args['userId'], $args['cookieKey']]
        ]);

        if (empty($aReturn[0])) {
            return false;
        }

        return true;
    }

    public static function setCookieAuth(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId']);
        ValidatorModel::stringType($args, ['userId']);

        $customId = CoreConfigModel::getCustomId();

        if (file_exists("custom/{$customId}/apps/maarch_entreprise/xml/config.xml")) {
            $path = "custom/{$customId}/apps/maarch_entreprise/xml/config.xml";
        } else {
            $path = 'apps/maarch_entreprise/xml/config.xml';
        }

        $cookieTime = 0;
        if (file_exists($path)) {
            $loadedXml = simplexml_load_file($path);
            if ($loadedXml) {
                $cookieTime = (string)$loadedXml->CONFIG->CookieTime;
            }
        }

        $previousCookie = SecurityModel::getCookieAuth();
        if (empty($previousCookie)) {
            $cookieKey = SecurityModel::getPasswordHash($args['userId']);
        } else {
            $cookieKey = $previousCookie['cookieKey'];
        }
        $cookiePath = str_replace(['apps/maarch_entreprise/index.php', 'rest/index.php'], '', $_SERVER['SCRIPT_NAME']);
        $cookieTime = time() + 60 * $cookieTime;

        DatabaseModel::update([
            'table' => 'users',
            'set'   => [
                'cookie_key'    => $cookieKey,
                'cookie_date'   => date('Y-m-d H:i:s', $cookieTime),
            ],
            'where' => ['user_id = ?'],
            'data'  => [$args['userId']]
        ]);

        $cookieData = json_encode(['userId' => $args['userId'], 'cookieKey' => $cookieKey]);
        setcookie('maarchCourrierAuth', base64_encode($cookieData), $cookieTime, $cookiePath, '', false, true);

        return true;
    }

    public static function deleteCookieAuth()
    {
        $previousCookie = SecurityModel::getCookieAuth();

        if (!empty($previousCookie)) {
            DatabaseModel::update([
                'table' => 'users',
                'set'   => [
                    'cookie_key'    => '',
                    'cookie_date'   => date('Y-m-d H:i:s', time() - 1),
                ],
                'where' => ['user_id = ?'],
                'data'  => [$previousCookie['userId']]
            ]);

            $cookiePath = str_replace(['apps/maarch_entreprise/index.php', 'rest/index.php'], '', $_SERVER['SCRIPT_NAME']);
            setcookie('maarchCourrierAuth', '', time() - 1, $cookiePath, '', false, true);
        }

        return true;
    }
}
