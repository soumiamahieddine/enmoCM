<?php
/**
* File : autolog_for_activex.php
*
* User identification
*
* @package  Maarch Entreprise 1.0
* @version 1.1
* @since 10/2005
* @license GPL
* @author  Loïc Vinet  <dev@maarch.org>
*/

$core_tools = new core_tools();
$core_tools->load_lang();
$func = new functions();
$_SESSION['error'] = "";
if(isset($_REQUEST['activex_login']))
{
	$s_login = $func->wash($_REQUEST['activex_login'],"no",_THE_ID,"yes");
}
else
{
	$s_login = '';
}

require("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
require("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_business_app_tools.php");
$sec = new security();
$business_app_tools = new business_app_tools();

if(count($_SESSION['config']) <= 0)
{
	//$_SESSION['slash_env'] = DIRECTORY_SEPARATOR;
	$path_tmp = explode(DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR,$_SERVER['SCRIPT_FILENAME']));
	$path_server = implode(DIRECTORY_SEPARATOR,array_slice($path_tmp,0,array_search('apps',$path_tmp))).DIRECTORY_SEPARATOR;
	$core_tools->build_core_config("core".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml");
	$business_app_tools->build_business_app_config();
	$core_tools->load_modules_config($_SESSION['modules']);
	$core_tools->load_menu($_SESSION['modules']);
	$core_tools->load_admin_core_board();
	$core_tools->load_admin_module_board($_SESSION['modules']);
	$core_tools->load_admin_app_board('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR);
}

if(!empty($_SESSION['error']))
{
	header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&page=login&coreurl=".$_SESSION['config']['coreurl']);
	exit();
}
else
{
		$pass = md5($s_pass);
		$sec->login($s_login,$pass, 'activex');
}
?>
