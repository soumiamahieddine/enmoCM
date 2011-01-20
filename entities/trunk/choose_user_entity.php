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


//core_tools::test_admin('manage_entities', 'entities');
core_tools::load_lang();
require("modules/entities/entities_tables.php");
if(!empty($_REQUEST['entity']) && isset($_REQUEST['entity']))
{
    require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_users_entities.php');
    $usersent = new users_entities();
    $usersent->connect();

    $usersent->query("select entity_label from ".ENT_ENTITIES." where entity_id = '".$_REQUEST['entity']."'");
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
<script type="text/javascript">
window.parent.opener.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=entities&page=users_entities_form';self.close();
</script>
