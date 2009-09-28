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
session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");

$core_tools = new core_tools();
$core_tools->load_lang();
$func = new functions();

$_SESSION['error'] = "";
$s_login = $func->wash($_REQUEST['login'],"no",_THE_ID,"yes");
$s_pass =$func->wash($_REQUEST['pass'],"no",_PASSWORD_FOR_USER,"yes");
require($_SESSION['pathtocoreclass']."class_security.php");
require($_SESSION['pathtocoreclass']."class_request.php");
require($_SESSION['config']['businessapppath']."class".$_SESSION['slash_env']."class_business_app_tools.php");
$sec = new security();
$business_app_tools = new business_app_tools();

if(count($_SESSION['config']) <= 0)
{
	if( strtoupper(substr(PHP_OS, 0, 3)) != "WIN")
	{
		$_SESSION['slash_env'] = "/";
	}
	else
	{
		$_SESSION['slash_env'] = "\\";
	}

	/*$path_server = $_SERVER['DOCUMENT_ROOT'];
	if(!preg_match("/[/\\]$/",$path_server))
	{
		$path_server = $path_server.$_SESSION['slash_env'];
	}*/

	$path_tmp = explode(DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR,$_SERVER['SCRIPT_FILENAME']));
	$path_server = implode(DIRECTORY_SEPARATOR,array_slice($path_tmp,0,array_search('apps',$path_tmp))).DIRECTORY_SEPARATOR;

	$core_tools->build_core_config($path_server."core".$_SESSION['slash_env']."xml".$_SESSION['slash_env']."config.xml");
	$business_app_tools->build_business_app_config();
	$core_tools->load_modules_config($_SESSION['modules']);
	$core_tools->load_menu($_SESSION['modules']);
	$core_tools->load_admin_core_board();
	$core_tools->load_admin_module_board($_SESSION['modules']);
	//loading app admin board
	$core_tools->load_admin_app_board($_SESSION['config']['businessapppath']);
	//$func->show_array($_SESSION['config']);
	//$func->show_array($_SESSION['ressources']);
	//$func->show_array($_SESSION['history']);
	//$func->show_array($_SESSION['modules']);
	//$func->show_array($_SESSION['modules_loaded']);
	//$func->show_array($_SESSION['menu']);
	//$func->show_array($_SESSION['tablename']);
	//$func->show_array($_SESSION['core_admin_board']);
	//$func->show_array($_SESSION['modules_admin_board']);
}

if(!empty($_SESSION['error']))
{
	header("location: ".$_SESSION['config']['businessappurl']."login.php?coreurl=".$_SESSION['config']['coreurl']);
	exit();
}
else
{
	if ($_SESSION['config']['ldap'] == "true")
	{
		//Extraction de /root/config dans le fichier de conf
		$ldap_conf = new DomDocument();
		try
		{
			if(!@$ldap_conf->load($_SESSION['config']['businessapppath']."ldap".DIRECTORY_SEPARATOR."config_ldap.xml"))
			{
				throw new Exception("Impossible de charger le document : ".$_SESSION['config']['businessappurl']."ldap".DIRECTORY_SEPARATOR."config_ldap.xml");
			}
		}
		catch(Exception $e)
		{
			exit($e->getMessage());
		}

		$xp_ldap_conf = new domxpath($ldap_conf);

		foreach($xp_ldap_conf->query("/root/config/*") as $cf)
		{
			${$cf->nodeName} = $cf->nodeValue;
		}

		//On inclus la class LDAP qui correspond à l'annuaire
		if(!include($_SESSION['config']['businessapppath']."ldap".DIRECTORY_SEPARATOR."class_".$type_ldap.".php"))
		{
			exit("Impossible de charger class_".$type_ldap.".php\n");
		}

		//Try to create a new ldap instance
		try
		{	
			$ad = new LDAP($domain,$login_admin,$pass,$ssl);
		}
		catch(Exception $con_failure)
		{ 
			echo $con_failure->getMessage();
			exit;
		}
		
		if($ad -> authenticate($s_login, $s_pass))
		{
			$db = new dbquery();
			$db->connect();	
			$db->query("SELECT * From users WHERE user_id ='".$s_login."'");
			if($db->fetch_object())
			{
				$pass = md5($s_pass);
				$sec->login($s_login,$pass);
			}
			else
			{
				$_SESSION['error'] =  _NO_LOGIN_OR_PSW_BY_LDAP."...";
				header("location: login.php");
				exit;
			}
		}
		else 
		{
			$_SESSION['error'] =  _BAD_LOGIN_OR_PSW."...";
			header("location: login.php");
			exit;
		}
	}
	else
	{
		$pass = md5($s_pass);
		$sec->login($s_login,$pass);
	}
}
?>
