<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Security Model Abstract
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Models;

class SecurityModelAbstract
{

    public static function getPasswordHash($password)
    {
        return hash('sha512', $password);
    }

    public static function checkAuthentication(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'password']);
        ValidatorModel::stringType($args, ['userId', 'password']);

        $aReturn = DatabaseModel::select([
            'select'    => ['password'],
            'table'     => ['users'],
            'where'     => ['user_id = ?'],
            'data'      => [$args['userId']]
        ]);

        return $aReturn[0]['password'] === $args['password'];
    }

    public static function setCookieAuth(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'password']);
        ValidatorModel::stringType($args, ['userId', 'password']);


        if (file_exists("custom/{$_SESSION['custom_override_id']}/apps/maarch_entreprise/xml/config.xml")) { //Todo No Session
            $path = "custom/{$_SESSION['custom_override_id']}/apps/maarch_entreprise/xml/config.xml";
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

        $t = str_replace('core/Models', '', dirname(__file__));
        $y = basename($t);


        $cookieData = json_encode(['userId' => $args['userId'], 'password' => $args['password']]);
        $cookieDataEncrypted = openssl_encrypt ($cookieData, 'aes-256-ctr', '12345678910');
        setcookie('maarchCourrierAuth', base64_encode($cookieDataEncrypted), time() + 60 * $cookieTime, '/', '', false, true);

        return true;
    }

    public static function getCookieAuth()
    {
        $rawCookie = $_COOKIE['maarchCourrierAuth'];
        if (empty($rawCookie)) {
            return [];
        }
        $cookieDecrypted = openssl_decrypt(base64_decode($rawCookie), 'aes-256-ctr', '12345678910');
        $cookie = json_decode($cookieDecrypted);

        return $cookie;
    }
}
