<?php
	$chosen_entities = array();
	$chosen_groups = array();
	$basketlist = "";
	$grouplist = "";

	if(isset($_REQUEST['services_chosen']) && count($_REQUEST['services_chosen']) > 0)
	{
		if($_REQUEST['services_chosen'][0] <>'')
		{
			$basketlist =  "'".$_REQUEST['services_chosen'][0]."'";
			$db->query("select entity_label from ".$_SESSION['tablename']['bask_entity']." where entity_id = '".$_REQUEST['services_chosen'][0]."' and enabled = 'Y' ");
			$res = $db->fetch_object();
			array_push($chosen_entities , array( 'ID' =>$_REQUEST['services_chosen'][0], 'LABEL' => $db->show_string($res->entity_label)));
		}
		for($i=1; $i < count($_REQUEST['services_chosen']); $i++)
		{
			$basketlist .= ",'".$_REQUEST['services_chosen'][$i]."'";
			$db->query("select entity_label from ".$_SESSION['tablename']['bask_entity']." where entity_id = '".$_REQUEST['services_chosen'][$i]."' and enabled = 'Y' ");
			$res = $db->fetch_object();
			array_push($chosen_entities , array( 'ID' =>$_REQUEST['services_chosen'][$i], 'LABEL' => $db->show_string($res->entity_label)));
		}
	}
	if(isset($_REQUEST['groups']) && count($_REQUEST['groups']) > 0)
	{
		$tmp = array();
		for($i=0; $i < count($_REQUEST['groups']); $i++)
		{
			array_push($tmp, "'".$_REQUEST['groups'][$i]."'");
			array_push($chosen_groups,$_REQUEST['groups'][$i]);
		}
		if(count( $_REQUEST['groups']) == 1)
		{
			$grouplist =  "'".$_REQUEST['groups'][0]."'";
		}
		else
		{
			$grouplist =  implode(",", $tmp);
		}
	}
?>
