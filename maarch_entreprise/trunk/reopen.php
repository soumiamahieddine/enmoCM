<?php
/**
* File : reopen.php
*
* Identification with cookie
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
include('../../core/init.php');

//$_SESSION['slash_env'] = DIRECTORY_SEPARATOR;

$path_tmp = explode('/',$_SERVER['SCRIPT_FILENAME']);
$path_server = implode('/',array_slice($path_tmp,0,array_search('apps',$path_tmp))).'/';
if (isset($_SESSION['config']['coreurl'])) {
    $_SESSION['urltomodules'] = $_SESSION['config']['coreurl']."/modules/";
}
$_SESSION['config']['corepath'] = $path_server;
chdir($_SESSION['config']['corepath']);
if(!isset($_SESSION['config']['app_id']) || empty($_SESSION['config']['app_id']))
{
	$_SESSION['config']['app_id'] = $path_tmp[count($path_tmp) -2];
}
if(isset($_SESSION['config']['corepath']))
{
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_functions.php");
}
else
{
	require_once("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_functions.php");
}
//require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_db.php");
//require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_core_tools.php");
//require_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_business_app_tools.php");
//$core_tools = new core_tools();
//$business_app_tools = new business_app_tools();
$func = new functions();
$cookie = explode("&", $_COOKIE['maarch']);
$user = explode("=",$cookie[0]);
$thekey = explode("=",$cookie[1]);
$s_UserId = strtolower($func->wash($user[1],"no","","yes"));
$s_key =strtolower($func->wash($thekey[1],"no","","yes"));
$_SESSION['arg_page'] = '';

if(!empty($_SESSION['error']) || ($s_UserId == "1" && $s_key == ""))
{
	header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&page=login&coreurl=".$_SESSION['config']['coreurl']);
	exit();
}
else
{

	if(trim($_SERVER['argv'][0]) <> "")
	{
		$_SESSION['requestUri'] = $_SERVER['argv'][0];
		header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&page=login&coreurl=".$_SESSION['config']['coreurl']);
	}
	else
	{

		header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&page=login&coreurl=".$_SESSION['config']['coreurl']);
	}
	exit();
	/*$pass = md5($s_pass);
	require("core/class/class_security.php");
	$sec = new security();
	//$sec->show_array($_SESSION);
	//$sec->build_config();
	$sec->reopen($s_UserId,$s_key);*/
}
?>
