<?php

$error = '';
try{
    require_once "core/class/users_controler.php";
    require_once 'core/core_tables.php';
} catch (Exception $e){
    echo $e->getMessage();
}
$core = new core_tools();
$core->load_lang();
if (! isset($_SESSION['config']['userdefaultpassword']) 
	|| empty($_SESSION['config']['userdefaultpassword'])
) {
    $_SESSION['config']['userdefaultpassword'] = 'maarch';
}
$defaultPassword = md5($_SESSION['config']['userdefaultpassword']);

$userCtrl = new users_controler();
$res = $userCtrl->changePassword(
	$_SESSION['m_admin']['users']['user_id'], $defaultPassword
);
if (!$res) {
	$error = _PASSWORD_NOT_CHANGED;
	 echo "{status : 1, error_txt : '" . $error. "'}";
} else {
    if ($_SESSION['history']['usersup'] == "true") {
        require_once "core/class/class_history.php";
        $hist = new history();
        $hist->add(
        	USERS_TABLE, $_SESSION['m_admin']['users']['user_id'], "UP",'usersup',
        	_NEW_PASSWORD_USER . " : " 
        	. $_SESSION['m_admin']['users']['lastname'] . " "
        	. $_SESSION['m_admin']['users']['firstname'], 
        	$_SESSION['config']['databasetype']
        );
    }
    echo "{status : 0, error_txt : '" . $error . "'}";
}
exit();

