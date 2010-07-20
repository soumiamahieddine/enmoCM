<?php

$error = '';

try{
	require_once("apps/".$_SESSION['config']['app_id']."/class/UserControler.php");
	require_once("apps/".$_SESSION['config']['app_id']."/class/User.php");
} catch (Exception $e){
	echo $e->getMessage();
}

if(!isset($_SESSION['config']['userdefaultpassword']) || empty($_SESSION['config']['userdefaultpassword']))
	$_SESSION['config']['userdefaultpassword'] = 'maarch';
$default_password = md5($_SESSION['config']['userdefaultpassword']);

$val = array('user_id' => $_SESSION['m_admin']['users']['UserId'], 'password' => $default_password, 'change_password' => 'Y');
$user = new User();
$user->setArray($val);

$res = UserControler:: save($user, "up");

if($res)
{
	if($_SESSION['history']['usersup'] == "true")
	{
		require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
		$hist = new history();
		$hist->add($_SESSION['tablename']['users'], $_SESSION['m_admin']['users']['UserId'],"UP",_NEW_PASSWORD_USER." : ".$_SESSION['m_admin']['users']['LastName']." ".$_SESSION['m_admin']['users']['FirstName'], $_SESSION['config']['databasetype']);

	}

	echo "{status : 0, error_txt : '".$error."'}";
}
else
{
	echo "{status : 1, error_txt : '".$error."'}";
}
exit();
?>
