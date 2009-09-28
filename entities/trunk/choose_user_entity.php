<?php
/**
* File : choose_user_entity.php
*
* Treat the add_users_entities.php form
*
* @package  Maarch Framework 3.0
* @version 1
* @since 03/2009
* @license GPL
* @author  Cédric Ndoumba  <dev@maarch.org>
* @author  Claire Figueras  <dev@maarch.org>
*/
session_name('PeopleBox');
 session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");

$admin = new core_tools();
//$admin->test_admin('manage_entities', 'entities');
//here we loading the lang vars
$admin->load_lang();

if(!empty($_REQUEST['entity']) && isset($_REQUEST['entity']))
{
	require_once($_SESSION['pathtomodules'].'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_users_entities.php');
	$usersent = new users_entities();
	$usersent->connect();

	$usersent->query("select entity_label from ".$_SESSION['tablename']['ent_entities']." where entity_id = '".$_REQUEST['entity']."'");
	$res = $usersent->fetch_object();

	// on retire toute les entités filles de l'entité à ajouter
	$tab = $usersent->getEntityChildren($_REQUEST['entity']);
	$usersent->remove_session($tab);

	$usersent->add_usertmp_to_entity_session( $_REQUEST['entity'], $_REQUEST['role'], $res->entity_label);
}
else
{
	$_SESSION['error'] = _NO_ENTITY_SELECTED."!";
	exit;
}
?>
<script language="javascript">
window.parent.opener.location.href='<?php  echo $_SESSION['urltomodules'].'entities/';?>users_entities_form.php';self.close();
</script>
