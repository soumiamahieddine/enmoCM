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
* @author  Cedric Ndoumba  <dev@maarch.org>
* @author  Claire Figueras  <dev@maarch.org>
* @author  Laurent Giovannoni  <dev@maarch.org>
*/

$admin = new core_tools();
$admin->test_admin('manage_entities', 'entities');
$_SESSION['m_admin']= array();
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
$page_path = '';
$page_label = _ENTITY_DELETION;
$page_id = "entity_del";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_entities.php');
$admin = new core_tools();
$ent = new entity();
$db = new dbquery();
$db->connect();
$admin->load_lang();
$admin->test_admin('manage_entities', 'entities');
$entities = $ent->getShortEntityTree(array());
$order = $_REQUEST['order'];
$order_field = $_REQUEST['order_field'];
$start = $_REQUEST['start'];
$what = $_REQUEST['what'];
$label = '';
if(isset($_REQUEST['id']))
{
	$s_id = addslashes($db->wash($_REQUEST['id'], "alphanum", _THE_ENTITY));
	$label = $ent->getentitylabel($s_id);
}
else
{
	$s_id = "";
}
if($_REQUEST['valid'])
{
	$documents = true;
	if(!empty($_REQUEST['doc_entity_id']))
	{
		for($i=0;$i<count($_SESSION['collections']);$i++)
		{
			if(isset($_SESSION['collections'][$i]['table']) && !empty($_SESSION['collections'][$i]['table']))
			{
				$db->query("update ".$_SESSION['collections'][$i]['table']." set destination = '".$db->protect_string_db($_REQUEST['doc_entity_id'])."' where destination = '".$db->protect_string_db($s_id)."' and status <> 'DEL'");
				//$db->show();
			}
		}
		$db->query("update ".$_SESSION['tablename']['ent_users_entities']." set entity_id = '".$db->protect_string_db($_REQUEST['doc_entity_id'])."' where entity_id = '".$db->protect_string_db($s_id)."'");
		//$db->show();
		$db->query("select entity_id from ".$_SESSION['tablename']['ent_entities']." where parent_entity_id = '".$db->protect_string_db($s_id)."'");
		$db2 = new dbquery();
		$db2->connect();
		while($lineEnt=$db->fetch_object())
		{
			//si la nouvelle entité (l'entité remplaçante) est une entité fille de l'entité à supprimer alors l'entité remplaçante récupère l'entité mère de l'entité à supprimer
			if($lineEnt->entity_id == $db2->protect_string_db($_REQUEST['doc_entity_id']))
			{
				$db2->query("select parent_entity_id from ".$_SESSION['tablename']['ent_entities']." where entity_id = '".$db2->protect_string_db($s_id)."'");
				$lineParentEnt = $db2->fetch_object();
				$db2->query("update ".$_SESSION['tablename']['ent_entities']." set parent_entity_id = '".$db2->protect_string_db($lineParentEnt->parent_entity_id)."' where entity_id = '".$lineEnt->entity_id."'");
				//$db2->show();
			}
			else
			{
				$db2->query("update ".$_SESSION['tablename']['ent_entities']." set parent_entity_id = '".$db2->protect_string_db($_REQUEST['doc_entity_id'])."' where entity_id = '".$lineEnt->entity_id."'");
				//$db2->show();
			}
		}
		//exit;
	}
	elseif(empty($_REQUEST['doc_entity_id']))
 	{
 		$_SESSION['error'] .= _ENTITY_MANDATORY_FOR_REDIRECTION."<br>";
 		$documents = false;
 	}
 	if($documents)
 	{
		if($_REQUEST['doc_entity_id'] <> "")
		{
			$entity_id_up = $_REQUEST['doc_entity_id'];
		}
		//groupbasket_redirect
		$db->query("update ".$_SESSION['tablename']['ent_groupbasket_redirect']." set entity_id = '".$db->protect_string_db($entity_id_up)."' where entity_id = '".$db->protect_string_db($s_id)."'");
		//$db->show();
		//templates_association
		$db->query("update ".$_SESSION['tablename']['temp_templates_association']." set value_field = '".$db->protect_string_db($entity_id_up)."' where value_field = '".$db->protect_string_db($s_id)."' and what = 'destination'");
		//$db->show();
		//listinstance
		$db->query("update ".$_SESSION['tablename']['ent_listinstance']." set item_id = '".$db->protect_string_db($entity_id_up)."' where item_id = '".$db->protect_string_db($s_id)."' and item_type = 'entity_id'");
		//$db->show();
		//listmodels
		$db->query("delete from ".$_SESSION['tablename']['ent_listmodels']." where object_id = '".$db->protect_string_db($s_id)."'");
		//$db->show();
		if($admin->is_module_loaded('advanced_physical_archive'))
		{
			//ar_boxes
			$db->query("update ".$_SESSION['tablename']['apa_boxes']." set entity_id = '".$db->protect_string_db($entity_id_up)."' where entity_id = '".$db->protect_string_db($s_id)."'");
			//$db->show();
			//ar_containers
			$db->query("update ".$_SESSION['tablename']['apa_containers']." set entity_id = '".$db->protect_string_db($entity_id_up)."' where entity_id = '".$db->protect_string_db($s_id)."'");
			//$db->show();
			//ar_header
			$db->query("update ".$_SESSION['tablename']['apa_header']." set entity_id = '".$db->protect_string_db($entity_id_up)."' where entity_id = '".$db->protect_string_db($s_id)."'");
			//$db->show();
			//ar_natures
			$db->query("update ".$_SESSION['tablename']['apa_natures']." set entity_id = '".$db->protect_string_db($entity_id_up)."' where entity_id = '".$db->protect_string_db($s_id)."'");
			//$db->show();
			//ar_sites
			$db->query("update ".$_SESSION['tablename']['apa_sites']." set entity_id = '".$db->protect_string_db($entity_id_up)."' where entity_id = '".$db->protect_string_db($s_id)."'");
			//$db->show();
		}
		elseif($admin->is_module_loaded('physical_archive'))
		{
			//ar_boxes
			$db->query("update ".$_SESSION['tablename']['ar_boxes']." set entity_id = '".$db->protect_string_db($entity_id_up)."' where entity_id = '".$db->protect_string_db($s_id)."'");
			//$db->show();
		}
		//exit;
		$ent->adminentity($s_id, 'del');
 	}
 	else
 	{
 		$ent->formDeleteEntity($s_id, $label, $entities, $admin);
 	}
}
else
{
	$ent->formDeleteEntity($s_id, $label, $entities, $admin);
}
?>
