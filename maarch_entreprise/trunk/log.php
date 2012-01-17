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
* @author  Claire Figueras  <dev@maarch.org>
* @author  Laurent Giovannoni  <dev@maarch.org>
*/
if (file_exists('../../core/init.php')) {
    include_once '../../core/init.php';
}
require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_functions.php');
require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_db.php');
require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_core_tools.php');

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
if (isset($_REQUEST['ra_code'])) {
    $ra_code = $func->wash($_REQUEST['ra_code'], 'no', _RA_CODE, 'yes');
} else {
    $ra_code = '';
}
require_once 'core/class/class_security.php';
require_once 'core/class/class_request.php';
require_once 'apps/' . $_SESSION['config']['app_id']
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

if (! empty($_SESSION['error'])) {
    header(
        'location: ' . $_SESSION['config']['businessappurl']
        . 'index.php?display=true&page=login&coreurl='
        . $_SESSION['config']['coreurl']
    );
    exit();
} else {
    if ($_SESSION['config']['ldap'] == 'true' && $login <> 'superadmin') {
        //Extraction de /root/config dans le fichier de conf
        $ldapConf = new DomDocument();
        try {
            if (! @$ldapConf->load(
                'apps/' . $_SESSION['config']['app_id'].'/ldap/config_ldap.xml'
            )
            ) {
                throw new Exception(
                    'Impossible de charger le document : '
                    . $_SESSION['config']['businessappurl']
                    .'ldap/config_ldap.xml'
                );
            }
        } catch(Exception $e) {
            exit($e->getMessage());
        }

        $xpLdapConf = new domxpath($ldapConf);

        foreach ($xpLdapConf->query('/root/config/*') as $cf) {
            ${$cf->nodeName} = $cf->nodeValue;
        }

        //On inclus la class LDAP qui correspond à l'annuaire
        if (! include
            'apps/' . $_SESSION['config']['app_id'] . '/ldap/class_'
            . $ldapType . '.php'
        ) {
            exit('Impossible de charger class_' . $ldapType . '.php\n');
        }

        //Try to create a new ldap instance
        try {
            $ad = new LDAP($domain, $adminLogin, $pass, $ssl);
        } catch(Exception $conFailure) {
            echo $conFailure->getMessage();
            exit;
        }

        if ($ad -> authenticate($login, $password)) {
            $db = new dbquery();
            $db->connect();

            if ($_SESSION['config']['databasetype'] == 'POSTGRESQL') {
                $query = 'select * from ' . USERS_TABLE
                       . " where user_id ilike '"
                       . $this->protect_string_db($login) . "' ";
            } else {
                $query = 'select * from ' . USERS_TABLE
                       . " where user_id like '"
                       . $this->protect_string_db($login) . "' ";
            }

            $db->query($query);
            if ($db->fetch_object()) {
                $_SESSION['error'] = '';
                $pass = md5($password);
                $res = $sec->login($login, $pass, 'ldap');
                $_SESSION['user'] = $res['user'];
                if (empty($_SESSION['error'])) {
                    $_SESSION['error'] = $res['error'];
                }
                $core->load_menu($_SESSION['modules']);
                header(
                    'location: ' . $_SESSION['config']['businessappurl']
                    . $res['url']
                );
                exit();
            } else {
                $_SESSION['error'] = _NO_LOGIN_OR_PSW_BY_LDAP . '...';
                header(
                    'location: ' . $_SESSION['config']['businessappurl']
                    . 'index.php?display=true&page=login&coreurl='
                    . $_SESSION['config']['coreurl']
                );
                exit;
            }
        } else {
            $_SESSION['error'] = _BAD_LOGIN_OR_PSW . '...';
            header(
                'location: ' . $_SESSION['config']['businessappurl']
                . 'index.php?display=true&page=login&coreurl='
                . $_SESSION['config']['coreurl']
            );
            exit;
        }
    }
    elseif(isset($_REQUEST['ra_code'])) {
        if (empty($login) || empty($password) || empty($ra_code)) {
            $_SESSION['error'] = _IP_NOT_ALLOWED;
            header(
                'location: ' . $_SESSION['config']['businessappurl']
                . 'index.php?display=true&page=login&coreurl='
                . $_SESSION['config']['coreurl']
            );
            exit;
        }
        else {
            $_SESSION['error'] = '';
            $pass = md5($password);
            $res = $sec->login($login, $pass, false, $ra_code);
            //$core->show_array($res);
            $_SESSION['user'] = $res['user'];
            if ($res['error'] == '') {
               // $businessAppTools->load_app_var_session($_SESSION['user']);
                //$core->load_var_session($_SESSION['modules'], $_SESSION['user']);
                $core->load_menu($_SESSION['modules']);
               // exit;
            }
            if (empty($_SESSION['error'])) {
                $_SESSION['error'] = $res['error'];
            }
            header(
                'location: ' . $_SESSION['config']['businessappurl'] . $res['url']
            );
            exit();
        }
    }
    else {
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
            //$core->show_array($res);
            $_SESSION['user'] = $res['user'];
            if ($res['error'] == '') {
               // $businessAppTools->load_app_var_session($_SESSION['user']);
                //$core->load_var_session($_SESSION['modules'], $_SESSION['user']);
                $core->load_menu($_SESSION['modules']);
               // exit;
            }
            
            $pathToIPFilter = '';
            if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'ip_filter.xml')){
                $pathToIPFilter = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'ip_filter.xml';
            } elseif (file_exists('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'ip_filter.xml')) {
                $pathToIPFilter = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'ip_filter.xml';
            }
            else {
                $ipArray = array();
                $ipArray['enabled'] = 'false';
                $ipArray['duration'] = '0';
            }
            $ipArray = array();
            $ipArray = functions::object2array(simplexml_load_file($pathToIPFilter));
            //print_r($ipArray);
            if ($ipArray['enabled'] == 'true') {
                $isAllowed = false;
                if($ipArray['IP'] <> '') {
                    $isAllowed = preg_match($ipArray['IP'], $_SERVER['REMOTE_ADDR']);
                }
                
                if (empty($_SESSION['error'])) {
                    $_SESSION['error'] = $res['error'];
                }
                if (!$isAllowed && $res['error'] == '') {
                    if ($ipArray['duration'] == 0) {
                        $_SESSION['error'] = _IP_NOT_ALLOWED_NO_RA_CODE;
                    }
                    else {
                        $_SESSION['error'] = _IP_NOT_ALLOWED;
                    }
                    $res['url'] = 'index.php?display=true&page=login&coreurl=' . $_SESSION['config']['coreurl'];
                }
            }
            header(
                'location: ' . $_SESSION['config']['businessappurl'] . $res['url']
            );
            exit();
        }
    }
}
