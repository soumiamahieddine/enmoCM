<?php
/**
* File : login.php
*
* Identification form : Login page
*
* @package  Maarch PeopleBox 1.1
* @version 1.1
* @since 02/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
* @author  Laurent Giovannoni  <dev@maarch.org>
*/

//print_r($_SERVER);
if (trim($_GET['coreurl']) <> '') {
    $_SESSION['config']['coreurl'] = $_GET['coreurl'];
}

//print_r($_REQUEST);
if (isset($_GET['target_page']) && trim($_GET['target_page']) <> '') {
    $_SESSION['target_page'] = $_GET['target_page'];
    if (trim($_GET['target_module']) <> '') {
        $_SESSION['target_module'] = $_GET['target_module'];
    } else if (trim($_GET['target_admin']) <> '') {
        $_SESSION['target_admin'] = $_GET['target_admin'];
    }
}

$serverPath = '';

if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN'
    && strtoupper(substr(PHP_OS, 0, 3)) != 'WINNT'
) {
    $serverPath = str_replace('\\', DIRECTORY_SEPARATOR, $serverPath);
} else {
    $serverPath = str_replace('/', DIRECTORY_SEPARATOR, $serverPath);
}
$_SESSION['slash_env'] = DIRECTORY_SEPARATOR;
$tmpPath = explode(
    DIRECTORY_SEPARATOR, str_replace(
        '/', DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME']
    )
);
$serverPath = implode(
    DIRECTORY_SEPARATOR, array_slice(
        $tmpPath, 0, array_search('apps', $tmpPath)
    )
) . DIRECTORY_SEPARATOR;

$_SESSION['urltomodules'] = $_SESSION['config']['coreurl'] . 'modules/';
$_SESSION['urltocore'] = $_SESSION['config']['coreurl'] . 'core/';

if (isset($_SESSION['config']['corepath'])
    && ! empty($_SESSION['config']['corepath'] )
) {
    require
        'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
        . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
        . 'class_business_app_tools.php';
    require
        'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
        . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
        . 'class_login.php';
    $configCorePath = 'core' . DIRECTORY_SEPARATOR . 'xml'
                      . DIRECTORY_SEPARATOR . 'config.xml';
} else {
    require 'class' . DIRECTORY_SEPARATOR . 'class_business_app_tools.php';
    require 'class' . DIRECTORY_SEPARATOR . 'class_login.php';
    $configCorePath = '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                      . 'core' . DIRECTORY_SEPARATOR . 'xml'
                      . DIRECTORY_SEPARATOR . 'config.xml';
}

$core = new core_tools();
$businessAppTools = new business_app_tools();
$func = new functions();

$core->build_core_config($configCorePath);
$businessAppTools->build_business_app_config();

$core->load_modules_config($_SESSION['modules']);
$core->load_lang();
//$func->show_array($_SESSION);
$core->load_app_services();
$core->load_modules_services($_SESSION['modules']);
//$core->load_menu($_SESSION['modules']);
// transfer in class_security (login + reopen)

//Reading base version
$businessAppTools->compare_base_version(
    'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'database_version.xml'
);

//LGI TEST FOR SMARTPHONE
if ($core->detectSmartphone()) {
    header('location: smartphone/hello.php');
    exit;
}

$core->load_html();
$core->load_header('', true, false);
$time = $core->get_session_time_expire();

$loginObj = new login();
$loginMethods = array();
$loginMethods = $loginObj->build_login_method();
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
} else {
    $error = '';
}

if (isset($_SESSION['HTTP_REQUEST']['withRA_CODE']) && empty($_SESSION['HTTP_REQUEST']['withRA_CODE'])) {
    $_SESSION['error'] = _IP_NOT_ALLOWED;
    $_SESSION['withRA_CODE'] = 'ok';
    $_SESSION['HTTP_REQUEST'] = array();
    header(
        'location: ' . $_SESSION['config']['businessappurl']
        . 'index.php?display=true&page=login&coreurl='
        . $_SESSION['config']['coreurl']
    );
    exit;
}
?>
<?php $core->load_js();?>
<body id="bodylogin" onload="session_expirate(<?php echo $time;?>, '<?php  echo $_SESSION['config']['coreurl'];?>');">
    <div id="loginpage">
        <p id="logo"><img src="<?php
            echo $_SESSION['config']['businessappurl'];
        ?>static.php?filename=default_maarch.gif" alt="Maarch" /></p>
        <div align="center">
            <h3>
                <?php echo$_SESSION['config']['applicationname'] ?>
            </h3>
        </div>
        <?php
        $loginObj->execute_login_script($loginMethods);
        ?>
    </div>

</body>
</html>
