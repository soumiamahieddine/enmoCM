<?php
//ONLY FOR TESTS FROM JAVA EDITOR !

$_SESSION['config']['app_id'] = 'maarch_entreprise';

//load Maarch session vars
$portal = new portal();
$portal->unset_session();
$portal->build_config();
$coreTools = new core_tools();
$_SESSION['custom_override_id'] = $coreTools->get_custom_id();
if (isset($_SESSION['custom_override_id'])
    && ! empty($_SESSION['custom_override_id'])
    && isset($_SESSION['config']['corepath'])
    && ! empty($_SESSION['config']['corepath'])
) {
    $path = $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
        . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR;
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
$coreTools->build_core_config('core' . DIRECTORY_SEPARATOR . 'xml' 
    . DIRECTORY_SEPARATOR . 'config.xml'
);

$_SESSION['config']['app_id'] = $_SESSION['businessapps'][0]['appid'];
require_once('apps/' . $_SESSION['businessapps'][0]['appid'] 
    . '/class/class_business_app_tools.php'
);

$businessAppTools = new business_app_tools();
$businessAppTools->build_business_app_config();
$coreTools->load_modules_config($_SESSION['modules']);
$businessAppTools->load_app_var_session();

$_SESSION['config']['coreurl'] = str_replace('modules/content_management/',
    '',
    $_SESSION['config']['coreurl']
);

$_SESSION['user']['UserId'] = $_REQUEST['user_id'];
