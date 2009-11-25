<?php
/**
* File : deco.php
*
* use this to terminate your session
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");

setcookie("maarch", "",time()-3600000);
$_SESSION['error'] = _NOW_LOGOUT;
if(isset($_GET['abs_mode']))
{
	$_SESSION['error'] .= ', '._ABS_LOG_OUT;
}


if($_SESSION['history']['userlogout'] == "true")
{
	$hist = new history();
	$ip = $_SERVER['REMOTE_ADDR'];
	$navigateur = addslashes($_SERVER['HTTP_USER_AGENT']);
	//$host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	$host = $_SERVER['REMOTE_ADDR'];
	$hist->add($_SESSION['tablename']['users'],$_SESSION['user']['UserId'],"LOGOUT","IP : ".$ip.", BROWSER : ".$navigateur.", HOST : ".$host, $_SESSION['config']['databasetype']);
}

$_SESSION = array();
header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&page=login&coreurl=".$_GET['coreurl']);
exit();
?>
