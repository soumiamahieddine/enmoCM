<?php
//include('core/init.php');

//require_once("core/class/class_functions.php");
//require_once("core/class/class_db.php");
//require_once("core/class/class_request.php");
require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_entities.php');
//require_once("core/class/class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();
$func = new functions();
//$ent = new dbquery();
//$ent->connect();
$core_tools->load_html();
$core_tools->load_header();
?>
<body>
<?php
if(count($_SESSION['tree_entities']) < 1)
{
	echo _NO_DEFINED_TREES;
}
else
{
	if(isset($_SESSION['entities_chosen_tree']) && !empty($_SESSION['entities_chosen_tree']))
	{
		?>
		<script type="text/javascript" src="<?php  echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>js/scriptaculous.js"></script>
		<script type="text/javascript" src="<?php  echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>Tree.js"></script>
		<?php
		$ent = new entity();
		$ent->connect();
		//$where = "";
		$level1 = array();
		$ent->query("select u.user_id, u.lastname, u.firstname from  ".$_SESSION['tablename']['ent_users_entities']." ue, ".$_SESSION['tablename']['users']." u where ue.entity_id  = '".$_SESSION['entities_chosen_tree']."' and ue.user_id = u.user_id  order by u.lastname, u.firstname");
		while($res = $ent->fetch_object())
		{
			array_push($level1, array('id' => $res->user_id, 'tree' => $_SESSION['entities_chosen_tree'], 'key_value' => $res->user_id, 'label_value' => $ent->show_string($res->lastname.' '.$res->firstname, true), 'is_entity' => false));
		}
		$ent->query("select entity_id, entity_label from ".$_SESSION['tablename']['ent_entities']." where parent_entity_id = '".$_SESSION['entities_chosen_tree']."' and enabled ='Y' order by entity_label");
		//$ent->show();
		$level1 = array();
		while($res = $ent->fetch_object())
		{
			array_push($level1, array('id' => $res->entity_id, 'tree' => $_SESSION['entities_chosen_tree'], 'key_value' => $res->entity_id, 'label_value' => $ent->show_string($res->entity_label, true), 'script' => "", 'is_entity' => true));
		}
		for($i=0;$i<count($_SESSION['tree_entities']);$i++)
		{
			if($_SESSION['tree_entities'][$i]['ID'] == $_SESSION['entities_chosen_tree'])
			{
				$label = "<b>".$_SESSION['tree_entities'][$i]['ID'].' - '.$_SESSION['tree_entities'][$i]['LABEL']."</b>";
			}
		}
		$ent->query("select u.user_id, u.lastname, u.firstname from  ".$_SESSION['tablename']['ent_users_entities']." ue, ".$_SESSION['tablename']['users']." u where ue.entity_id  = '".$_SESSION['entities_chosen_tree']."' and ue.user_id = u.user_id  order by u.lastname, u.firstname");
		while($res = $ent->fetch_object())
		{
			array_push($level1, array('id' => $res->user_id, 'tree' => $_SESSION['entities_chosen_tree'], 'key_value' => $res->user_id, 'label_value' => $ent->show_string($res->lastname.' '.$res->firstname, true), 'is_entity' => false));
		}
		?>
		<script type="text/javascript">
			function funcOpen (branch, response) {
				// Ici tu peux traiter le retour et retourner true si
				// tu veux insérer les enfants, false si tu veux pas
				return true;
			}

			var tree = null;
			function TafelTreeInit () {
				var struct = [
								{
								'id':'<?php  echo $_SESSION['entities_chosen_tree'];?>',
								'txt':'<?php  echo $label;?>',
								'items':[
											<?php
											$ent2 = new entity();
											for($i=0; $i < count($level1)-1;$i++)
											{
												if($level1[$i]['is_entity'])
												{
													$canhavechildren = '\'canhavechildren\' : true,';
												}
												else
												{
													$canhavechildren = '\'canhavechildren\' : false,';
												}
												?>
												{
												'id':'<?php  if($level1[$i]['is_entity'])
												{
													echo $level1[$i]['id'];
												}
												else
												{
													echo $level1[$i]['id'].'_'.$_SESSION['entities_chosen_tree'];
												}
												?>',
												<?php  echo $canhavechildren;?>
												'txt':'<?php  echo addslashes($level1[$i]['id'].' - '.$level1[$i]['label_value']);?>',
												'is_entity' : true
												},
												<?php
											} // fin for($i=0; $i < count($level1)-1;$i++)
											if($level1[$i]['is_entity'])
											{
												$canhavechildren = '\'canhavechildren\' : true,';
											}
											else
											{
												$canhavechildren = '\'canhavechildren\' : false,';
											}
											?>
											{
												'id':'<?php  if($level1[$i]['is_entity'])
												{
													echo $level1[$i]['id'];
												}
												else
												{
													echo $level1[$i]['id'].'_'.$_SESSION['entities_chosen_tree'];
												}
												?>',
												<?php  echo $canhavechildren;?>
												'txt':'<?php  echo addslashes($level1[$i]['id'].' - '.$level1[$i]['label_value']);?>',
												'is_entity' : true
											}
										]
								}
							];
				tree = new TafelTree('trees_div', struct, {
					'generate' : true,
					'imgBase' : '<?php  echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>imgs/',
					'defaultImg' : 'user.gif',
					'defaultImgOpen' : 'folderopen.gif',
					'defaultImgClose' : 'folder.gif',
					'onOpenPopulate' : [funcOpen, '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=entities&page=get_tree_childs']
				});
			}

			function effet () {
				var branch = tree.getBranchById('child1');
				branch.refreshChildren();
			}
		</script>
		<div id="trees_div"></div>
		<?php
	}
	else
	{
		//echo "<div align='left'>&nbsp;&nbsp;&nbsp;"._CHOOSE_FOLDERTYPE."</div>";
	}
}
?>
</body>
</html>
