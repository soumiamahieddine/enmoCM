<?php
/**
* File : entity_up.php
*
* To update an entity
*
* @package  Maarch Framework 3.0
* @version 1
* @since 03/2009
* @license GPL
* @author  Cédric Ndoumba  <dev@maarch.org>
*/
//include('core/init.php');

$admin = new core_tools();
$admin->test_admin('manage_entities', 'entities');
 /****************Management of the location bar  ************/
$init = false;
if($_REQUEST['reinit'] == "true")
{
	$init = true;
}
$level = "";
if($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
{
	$level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=entity_add&module=entities';
$page_label = _MODIFICATION;
$page_id = "entity_up";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/

require_once('modules/entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_entities.php');

$func = new functions();
$ent = new entity();

if(isset($_GET['id']))
{
	$id = addslashes($func->wash($_GET['id'], "alphanum", _THE_ID));
}
else
{
	$id = "";
}

$ent->formentity("up",$id);
?>
