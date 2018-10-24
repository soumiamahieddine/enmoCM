<?php

//require 'vendor/autoload.php';
//$shibbolethConfig = \SrcCore\models\CoreConfigModel::getShibbolethConfiguration();

//test if no shibboleth authentication
//$_SERVER['REMOTE_USER'] = '';
//test if user doesn't exists
//$_SERVER['REMOTE_USER'] = 'aUserTest';

if ($_SERVER['REMOTE_USER'] <> '' && $_SERVER['AUTH_TYPE'] = 'shibboleth') {
    $login = $_SERVER['REMOTE_USER'];
    $password = 'aFakePass';

    require_once('core/class/class_core_tools.php');
    require_once 'core/class/class_security.php';
    require_once 'core/class/class_db_pdo.php';
    $core = new core_tools();
    $sec = new security();

    $database = new Database();
    $stmt = $database->query("SELECT 1 FROM users WHERE user_id ILIKE ?", array($login));
    $result = $stmt->fetch();

    if ($result) {
        $_SESSION['error'] = '';

        $res = $sec->login($login, $password, 'shibboleth');

        $_SESSION['user'] = $res['user'];

        if (empty($_SESSION['error'])) {
            $_SESSION['error'] = $res['error'];
    }

    if ($res['error'] == '') {
            \SrcCore\models\AuthenticationModel::setCookieAuth(['userId' => $login]);
            $core->load_menu($_SESSION['modules']);
            //login OK
            $trace = new history();
            header('location: ' . $_SESSION['config']['businessappurl']. $res['url']);
            exit();
        } else {
            $_SESSION['error'] = $res['error'];
            echo $_SESSION['error'];
            exit;
        }
    } else {
        $_SESSION['error'] = _USER_NOT_EXIST . ' ' . $login;
        echo $_SESSION['error'];
        exit;
    }
}
