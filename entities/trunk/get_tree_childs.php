<?php
session_name('PeopleBox');
session_start();


if(isset($_POST['branch_id'])) {

	require_once($_SESSION['pathtocoreclass']."class_functions.php");
	require_once($_SESSION['pathtocoreclass']."class_db.php");
	require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
	require_once($_SESSION['pathtomodules'].'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_entities.php');
	$core_tools = new core_tools();
	$core_tools->load_lang();

	$ent = new entity();
	$ent->connect();
	$children = array();

	$ent->query("select u.user_id, u.lastname, u.firstname from ".$_SESSION['tablename']['ent_users_entities']." ue,".$_SESSION['tablename']['ent_entities']." e, ".$_SESSION['tablename']['users']." u where e.parent_entity_id = '".$_POST['branch_id']."' and e.parent_entity_id = ue.entity_id and u.user_id = ue.user_id order by u.lastname, u.firstname");
	if($ent->nb_result() > 0)
	{
		while($res = $ent->fetch_object())
		{
			$canhavechildren = 'canhavechildren:false, ';
			array_push($children, array('id' => $res->user_id.'_'.$_POST['branch_id'], 'tree' => $_SESSION['entities_chosen_tree'], 'key_value' => $res->user_id, 'label_value' => $res->user_id.' - '.$ent->show_string($res->lastname.' '.$res->firstname), 'canhavechildren' => '', 'is_entity' => 'false'));

		}
	}
	else
	{
		$ent->query("select u.user_id, u.lastname, u.firstname from ".$_SESSION['tablename']['ent_users_entities']." ue, ".$_SESSION['tablename']['users']." u where ue.entity_id = '".$_POST['branch_id']."'  and u.user_id = ue.user_id order by u.lastname, u.firstname");
		//$ent->show();
		if($ent->nb_result() > 0)
		{
			while($res = $ent->fetch_object())
			{
				$canhavechildren = 'canhavechildren:false, ';
				array_push($children, array('id' => $res->user_id.'_'.$_POST['branch_id'], 'tree' => $_SESSION['entities_chosen_tree'], 'key_value' => $res->user_id, 'label_value' => $res->user_id.' - '.$ent->show_string($res->lastname.' '.$res->firstname), 'canhavechildren' => '', 'is_entity' => 'false'));

			}
		}
	}
	$ent->query("select entity_id, entity_label from ".$_SESSION['tablename']['ent_entities']." where parent_entity_id = '".$_POST['branch_id']."' order by entity_label");
	//$ent->show();
	if($ent->nb_result() > 0)
	{
		while($res = $ent->fetch_object())
		{
			$canhavechildren = '';
			$canhavechildren = 'canhavechildren:true, ';
			array_push($children, array('id' => $res->entity_id, 'tree' => $_SESSION['entities_chosen_tree'], 'key_value' => $res->entity_id, 'label_value' => $ent->show_string($res->entity_id.' - '.$res->entity_label), 'canhavechildren' => $canhavechildren, 'is_entity' => 'true'));

		}

	}


	if(count($children) > 0)
	{
		echo '[';
		for($i=0; $i< count($children)-1; $i++){
			echo "{id:'".$children[$i]['id']."', ".$children[$i]['canhavechildren']."txt:'".addslashes($children[$i]['label_value'])."', is_entity : ".$children[$i]['is_entity']."},";
		}
		// affichage du derniere élément
		echo "{id:'".$children[$i]['id']."', ".$children[$i]['canhavechildren']."txt:'".addslashes($children[$i]['label_value'])."', is_entity : ".$children[$i]['is_entity']."}";
		echo ']';
	}
	else
	{
		echo "[{id:'no_user_".$_POST['branch_id']."', canhavechildren:false, txt:'<em>(".addslashes(_NO_USER).")</em>', is_entity : false}]";
	}
}

?>
