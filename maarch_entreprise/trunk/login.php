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

##########################################################################
#TODO: Remove this code if no problem if found in a short future
# bca (2011-08-17): This code block has been commented to unify the several
# ways sessions are initialised.
# this is now useless, since session initialisation is now done in
# core/init.php, which is executed by apps/maarch_entreprise/index.php,
# which, in turn, includes this page.
# (besides, it was merely a copy/paste of a method of class_portal...)
##########################################################################
## Block to remove :
#
#if (! isset($_SESSION['config']['corename'])
#    || empty($_SESSION['config']['corename'])
#) {
#    if (isset($_SESSION['config']['corepath'])
#        && ! empty($_SESSION['config']['corepath'] )
#    ) {
#        $path = 'core' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR
#              .'config.xml';
#    } else {
#        $path = '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'core'
#              . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR
#              . 'config.xml';
#    }
#    $xmlconfig = simplexml_load_file($path);
#
#    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
#        $protocol = 'https';
#    } else {
#        $protocol = 'http';
#    }
#
#    foreach ($xmlconfig->CONFIG as $config) {
#        $_SESSION['config']['corename'] = (string) $config->corename;
#        $_SESSION['config']['corepath'] = (string) $config->corepath;
#        $_SESSION['config']['tmppath'] = (string) $config->tmppath;
#        $_SESSION['config']['unixserver'] = (string) $config->unixserver;
#        $_SESSION['config']['defaultpage'] = (string) $config->defaultpage;
#        $_SESSION['config']['defaultlang'] = (string) $config->defaultlanguage;
#        if (isset($config->default_timezone)
#            && ! empty($config->default_timezone)) {
#            $_SESSION['config']['default_timezone'] =
#                (string) $config->default_timezone;
#        } else {
#            $_SESSION['config']['default_timezone'] = 'Europe/Paris';
#        }
#        if (! isset($_SESSION['config']['coreurl'])) {
#            if ($_SERVER['SERVER_PORT'] <> 443 && $protocol == 'https') {
#                $serverPort = ':' . $_SERVER['SERVER_PORT'];
#            } else if ($_SERVER['SERVER_PORT'] <> 80 && $protocol == 'http') {
#                $serverPort = ':' . $_SERVER['SERVER_PORT'];
#            } else {
#                $serverPort = '';
#            }
#            $uriArray = explode('/', $_SERVER['SCRIPT_NAME']);
#            $sliceUri = array_slice($uriArray, 0, -3);
#            $finalUri = implode('/', $sliceUri) . '/';
#            if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])
#               && $_SERVER['HTTP_X_FORWARDED_HOST'] <> ''
#            ) {
#                $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
#            } else {
#                $host = $_SERVER['HTTP_HOST'];
#            }
#            $_SESSION['config']['coreurl'] = $protocol . '://' . $host
#                                           . $serverPort . $finalUri;
#        }
#    }
#    $i = 0;
#    foreach ($xmlconfig->BUSINESSAPPS as $businessApps) {
#        $_SESSION['businessapps'][$i] = array(
#            'appid'   => (string) $businessApps->appid,
#            'comment' => (string) $businessApps->comment,
#        );
#        $i ++;
#    }
#    chdir($_SESSION['config']['corepath']);
#}
#$_SESSION['config']['app_id'] = $_SESSION['businessapps'][0]['appid'];
#
## End of the block to remove
###########################################################################

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
    header('location: smartphone/login.php');
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
