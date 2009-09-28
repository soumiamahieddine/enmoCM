<?php
/**
* File : entity_ban.php
*
* To suspend an entity
*
* @package  Maarch Framework 3.0
* @version 1
* @since 03/2009
* @license GPL
* @author  Cédric Ndoumba  <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");

require($_SESSION['pathtocoreclass']."class_core_tools.php");

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$admin = new core_tools();
$admin->test_admin('manage_entities', 'entities');

require_once($_SESSION['pathtocoreclass']."class_db.php");

$path = $_SESSION['pathtomodules'].'entities'.$_SESSION['slash_env'].'class'.$_SESSION['slash_env'].'class_manage_entities.php';

require($path);

$func = new functions();

if(isset($_GET['id']))
{
	$s_id = addslashes($func->wash($_GET['id'], "alphanum", _ENTITY));
}
else
{
	$s_id = "";
}

$ent = new entity();
$ent->adminentity($s_id,'allow');
?>
