<?php
/**
* File : entity_del.php
*
* Delete an entity
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

$admin = new core_tools();
//here we loading the lang vars
$admin->load_lang();
$admin->test_admin('manage_entities', 'entities');
require_once($_SESSION['pathtocoreclass']."class_db.php");

//$path = $_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_usergroups.php";
require_once($_SESSION['pathtomodules'].'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_entities.php');

$func = new functions();

if(isset($_GET['id']))
{
	$s_id = addslashes($func->wash($_GET['id'], "alphanum", _THE_GROUP));
}
else
{
	$s_id = "";
}


$ent = new entity();
$ent->adminentity($s_id,'del');
?>
