<?php
/**
* File : entities_list_by_label.php
*
* List of entities for autocompletion
*
* @package  Maarch Framework 3.0
* @version 3
* @since 10/2005
* @license GPL
* @author Cédric Ndoumba <dev@maarch.org>
*/
//include('core/init.php');

//require_once("core/class/class_functions.php");
//require_once("core/class/class_db.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_entities.php');
$ent = new entity();

$select = "select entity_label from ".$_SESSION['tablename']['ent_entities'];
if($_SESSION['config']['databasetype'] == 'POSTGRESQL')
{
	$where = " where entity_label ilike '".$_REQUEST['what']."%' ";
}
else
{
	$where = " where entity_label like '".$_REQUEST['what']."%' ";
}
if($_SESSION['user']['UserId'] != 'superadmin')
{
	$my_tab_entities_id = $ent->get_all_entities_id_user($_SESSION['user']['entities']);
	if (count($my_tab_entities_id)>0)
	{
		$where.= ' and entity_id in ('.join(',', $my_tab_entities_id).')';
	}
}

$sql = $select.$where." order by entity_id";
$ent->connect();
$ent->query($sql);

$entities = array();
while($line = $ent->fetch_object())
{
	array_push($entities, $line->entity_label);
}
echo "<ul>\n";
$authViewList = 0;
foreach($entities as $entity)
{
	if($authViewList >= 10)
	{
		$flagAuthView = true;
	}
    if(stripos($entity, $_REQUEST['what']) === 0)
    {
        echo "<li>".$entity."</li>\n";
		if($flagAuthView)
		{
			echo "<li>...</li>\n";
			break;
		}
		$authViewList++;
    }
}
echo "</ul>";
