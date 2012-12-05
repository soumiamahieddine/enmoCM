<?php

require_once dirname(__file__) . '/class/Url.php';
//dynamic session name
session_name(base64_encode(dirname(__file__)));
session_start();

if (!isset($_SESSION['config']) || !isset($_SESSION['businessapps'][0]['appid'])) {
    require_once('class/class_portal.php');
    $portal = new portal();
    $portal->unset_session();
    $portal->build_config();
}
if (isset($_SESSION['config']['default_timezone'])
    && ! empty($_SESSION['config']['default_timezone'])
) {
    ini_set('date.timezone', $_SESSION['config']['default_timezone']);
    date_default_timezone_set($_SESSION['config']['default_timezone']);
} else {
    ini_set('date.timezone', 'Europe/Paris');
    date_default_timezone_set('Europe/Paris');
}

if (isset($_SESSION['config']['corepath'])
    && ! empty($_SESSION['config']['corepath'])
) {
    chdir($_SESSION['config']['corepath']);
}
//ini_set('error_reporting', E_ALL);
if (isset($_SESSION['custom_override_id'])
    && ! empty($_SESSION['custom_override_id'])
    && isset($_SESSION['config']['corepath'])
    && ! empty($_SESSION['config']['corepath'])
) {
    $path = $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
        . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR;
    //echo $path;
    set_include_path(
        $path . PATH_SEPARATOR . $_SESSION['config']['corepath']
        . PATH_SEPARATOR . get_include_path()
    );
} else if (isset($_SESSION['config']['corepath'])
    && ! empty($_SESSION['config']['corepath'])
) {
    set_include_path(
        $_SESSION['config']['corepath'] . PATH_SEPARATOR . get_include_path()
    );
}
