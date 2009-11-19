<?php
/**
* File : verif_pass.php
*
* Treat the user modification (new password)
*
* @package  Maarch PeopleBox 1.0
* @version 2.0
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
include('core/init.php');

require_once("core/class/class_functions.php");
//require_once("core/class/class_db.php");
require("core/class/class_core_tools.php");

$core_tools = new core_tools();
$core_tools->load_lang();
	$func = new functions();

	$_SESSION['error'] ="";
	$_SESSION['user']['pass'] =  $func->wash($_REQUEST['pass1'], "no", _THE_PSW);

	$pass2 = $func->wash($_REQUEST['pass2'], "no", _THE_PSW_VALIDATION);

	if($_SESSION['user']['pass'] <> $pass2)
	{
		$_SESSION['error'] = _WRONG_SECOND_PSW.".<br />";
	}
	else
	{
		$_SESSION['user']['pass'] = md5($pass2);
	}

	$_SESSION['user']['FirstName'] = $func->wash($_REQUEST['FirstName'], "no", _THE_LASTNAME);
	$_SESSION['user']['LastName'] = $func->wash($_REQUEST['LastName'], "no", _THE_FIRSTNAME);

	if(isset($_REQUEST['Department']) && !empty($_REQUEST['Department']))
	{
		$_SESSION['user']['department']  = $func->wash($_REQUEST['Department'], "no", _THE_DEPARTMENT);
	}

	if(isset($_REQUEST['Phone']) && !empty($_REQUEST['Phone']))
	{
		$_SESSION['user']['Phone']  = $_REQUEST['Phone'];
	}
	$_SESSION['user']['Mail']  = '';
	$tmp=$func->wash($_REQUEST['Mail'], "mail", _MAIL);
	if($tmp <> false)
	{
		$_SESSION['user']['Mail'] = $tmp;
	}
	if(!empty($_SESSION['error']))
	{
		header("location: ".$_SESSION['config']['businessappurl']."change_pass.php");
		exit();
	}
	else
	{
		require_once("core/class/class_db.php");
		$db = new dbquery();
		$db->connect();

		$tmp_fn = $db->protect_string_db($_SESSION['user']['FirstName']);
		$tmp_ln = $db->protect_string_db($_SESSION['user']['LastName']);
		$tmp_dep = $db->protect_string_db($_SESSION['user']['department']);

		$db->query("update ".$_SESSION['tablename']['users']." set password = '".$_SESSION['user']['pass']."' ,firstname = '".$tmp_fn."', lastname = '".$tmp_ln."', phone = '".$_SESSION['user']['Phone']."', mail = '".$_SESSION['user']['Mail']."' , department = '".$tmp_dep."' , change_password = 'N' where user_id = '".$_SESSION['user']['UserId']."'");
		//$db->show();
		header("location: ".$_SESSION['config']['businessappurl']."index.php");
		exit();

	}

?>
