<?php
include('core/init.php');

if(isset($_REQUEST['branch_id']) && !empty($_REQUEST['branch_id']) && isset($_REQUEST['IdTree']) && !empty($_REQUEST['IdTree']))
{
	//print_r($_REQUEST['branch']);//exit;
	$string = $_REQUEST['branch'];
	$search="'branch_level_id'";
	$search="#branch_level_id\":(.*)\,#U";
	preg_match($search,$string,$out);
	$count=count($out[0]);
	if($count == 1)
	{
		$find = true;
	}
	//echo "alert('trouve:".$out[0]."');";
	$branch_level_id = str_replace("branch_level_id\":", "", $out[0]);
	$branch_level_id = str_replace(",", "", $branch_level_id);
	$branch_level_id = str_replace("\"", "", $branch_level_id);
	//echo "alert('branch_level_id:".$branch_level_id."');";
	//exit;
	require_once("core/class/class_functions.php");
	require_once("core/class/class_db.php");
	require_once("core/class/class_core_tools.php");
	$core_tools = new core_tools();
	$core_tools->load_lang();
	$func = new functions();
	$tree_id = $_REQUEST['IdTree'];
	$db = new dbquery();
	$db->connect();
	$where = "";
	if($branch_level_id == "1")
	{
		$db->query("select * from ".$_SESSION['tablename']['ent_entitis']." where parent_entity_id = '".$_REQUEST['branch_id']."' and enabled ='Y' order by entity_label");
		$children = array();
		while($res = $db->fetch_object())
		{
			array_push($children, array('id' => $res->entity_id, 'tree' => $_SESSION['entities_chosen_tree'], 'key_value' => $res->entity_id, 'label_value' => $db->show_string($res->entity_label), 'script' => "show_doctypes"));
		}
		if(count($children) > 0)
		{
			echo "[";
			for($i=0; $i< count($children); $i++)
			{
				echo "{'id':'".$children[$i]['id']."', 'txt':'".trim(addslashes($children[$i]['label_value']))."', 'canhavechildren' : true, '".$children[$i]['script']."' : 'other', 'key_value' : '".trim(addslashes($children[$i]['key_value']))."', 'onbeforeopen' : MyBeforeOpen}";
				if(isset($children[$i+1]['id']) && !empty($children[$i+1]['id']))
				{
					echo ',';
				}
			}
			echo "]";
		}
	}
	if($branch_level_id == "2")
	{
		//echo "alert('LEVEL 2');";
		$db->query("select * from ".$_SESSION['tablename']['doctypes']." where doctypes_second_level_id = '".$_REQUEST['branch_id']."' and enabled ='Y' order by description");
		//echo "alert('id:".$db->show()."');";
		$children = array();
		while($res = $db->fetch_object())
		{
			array_push($children, array('id' => $res->type_id, 'tree' => $_SESSION['entities_chosen_tree'], 'key_value' => $res->type_id, 'label_value' => $db->show_string($res->description), 'script' => "other"));
			//array_push($children, array('id' => $res->type_id, 'tree' => $_SESSION['entities_chosen_tree'], 'key_value' => $res->type_id, 'label_value' => $db->show_string($res->description), 'script' => "default"));
		}
		if(count($children) > 0)
		{
			//echo "alert('id:".$children[$i]['id']."');";
			//echo "alert(txt'".$children[$i]['label_value']."');";
			echo "[";
			for($i=0; $i< count($children); $i++)
			{
				echo "{'id':'".$children[$i]['id']."', 'txt':'".trim(addslashes($children[$i]['label_value']))."', 'canhavechildren' : false, '".$children[$i]['script']."' : 'default', 'key_value' : '".trim(addslashes($children[$i]['key_value']))."', 'img' : 'page.gif'}";
				if(isset($children[$i+1]['id']) && !empty($children[$i+1]['id']))
				{
					echo ',';
				}
			}
			echo "]";
		}
	}
}
?>
