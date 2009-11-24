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

$admin = new core_tools();

$admin->load_lang();
$admin->test_admin('manage_entities', 'entities');

require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_entities.php');

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
