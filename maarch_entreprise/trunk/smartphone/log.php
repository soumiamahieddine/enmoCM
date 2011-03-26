<?php

/**
* File : log.php
*
* User identification
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Laurent Giovannoni  <dev@maarch.org>
*/
$core = new core_tools();
$core->load_lang();
$func = new functions();

$_SESSION['error'] = '';
if (isset($_REQUEST['login'])) {
    $login = $func->wash($_REQUEST['login'], 'no', _THE_ID, 'yes');
} else {
    $login = '';
}
if (isset($_REQUEST['pass'])) {
    $password = $func->wash($_REQUEST['pass'], 'no', _PASSWORD_FOR_USER, 'yes');
} else {
    $password = '';
}
require 'core/class/class_security.php';
require 'core/class/class_request.php';
require 'apps/' . $_SESSION['config']['app_id']
    . '/class/class_business_app_tools.php';
$sec = new security();
$businessAppTools = new business_app_tools();

if (count($_SESSION['config']) <= 0) {
    $tmpPath = explode(
        DIRECTORY_SEPARATOR, str_replace(
            '/', DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME']
        )
    );
    $serverPath = implode(
        DIRECTORY_SEPARATOR, array_slice(
            $tmpPath, 0, array_search('apps', $tmpPath)
        )
    ).DIRECTORY_SEPARATOR;

    $core->build_core_config('core/xml/config.xml');

    $businessAppTools->build_business_app_config();
    $core->load_modules_config($_SESSION['modules']);
    $core->load_menu($_SESSION['modules']);
}

if (!empty($_SESSION['error'])) {
    header(
        'location: ' . $_SESSION['config']['businessappurl']
        . 'index.php?display=true&page=login&coreurl='
        . $_SESSION['config']['coreurl']
    );
    exit();
} else {
	if (empty($login) || empty($password)) {
		$_SESSION['error'] = _BAD_LOGIN_OR_PSW . '...';
		header(
			'location: ' . $_SESSION['config']['businessappurl']
			. 'index.php?display=true&page=login&coreurl='
			. $_SESSION['config']['coreurl']
		);
		exit;
	} else {
		$_SESSION['error'] = '';
		$pass = md5($password);
		$res = $sec->login($login, $pass);
		$_SESSION['user'] = $res['user'];
		//var_dump($_SESSION['user']);exit();
		if (empty($_SESSION['error'])) {
			$_SESSION['error'] = $res['error'];
		}
		header('location: smartphone/index.php?page=search');
		exit();
	}
}
