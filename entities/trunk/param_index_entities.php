<?php
include('core/init.php');


// Group - Basket Form : actions params
if($_SESSION['service_tag'] == 'group_basket')
{
	// This param is only for the actions with the keyword : indexing
	if( trim($_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['KEYWORD']) == 'indexing') // Indexing case
	{
		$_SESSION['m_admin']['show_where_clause'] = false;
		$is_default_action = false;
		// Is the action the default action for the group on this basket ?
		if( $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'] == $_SESSION['m_admin']['basket']['groups'][$_SESSION['m_admin']['basket']['ind_group']]['DEFAULT_ACTION'])
		{
			$is_default_action = true;
		}
		// indexing entities list
		?>
	<p>
		<label><?php  echo _INDEXING_ENTITIES;?> :</label>
	</p>
	<table align="center" width="100%" id="index_entity_baskets" >
		<tr>
			<td width="40%" align="center">
				<select name="<?php echo $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'];?>_entitieslist[]" id="<?php echo $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'];?>_entitieslist" size="7" ondblclick='moveclick(document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'];?>_entitieslist"),document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'];?>_entities_chosen"));' multiple="multiple"  class="entities_list">
				<?php
				// Browse all the entities
				for($i=0;$i<count($_SESSION['m_admin']['entities']);$i++)
				{
					$state_entity = false;
					if(!$is_default_action)
					{
						for($j=0;$j<count($_SESSION['m_admin']['basket']['groups'][$_SESSION['m_admin']['basket']['ind_group']]['ACTIONS']);$j++)
						{
							for($k=0; $k<count($_SESSION['m_admin']['basket']['groups'][$_SESSION['m_admin']['basket']['ind_group']]['ACTIONS'][$j]['ENTITIES_LIST']);$k++)
							{
								if($_SESSION['m_admin']['entities'][$i]['ID'] == $_SESSION['m_admin']['basket']['groups'][$_SESSION['m_admin']['basket']['ind_group']]['ACTIONS'][$j]['ENTITIES_LIST'][$k]['ID'])
								{
									$state_entity = true;
								}
							}
						}
					}
					else
					{
						for($k=0; $k<count($_SESSION['m_admin']['basket']['groups'][$_SESSION['m_admin']['basket']['ind_group']]['PARAM_DEFAULT_ACTION']['ENTITIES_LIST']);$k++)
						{
							if($_SESSION['m_admin']['entities'][$i]['ID'] == $_SESSION['m_admin']['basket']['groups'][$_SESSION['m_admin']['basket']['ind_group']]['PARAM_DEFAULT_ACTION']['ENTITIES_LIST'][$k]['ID'])
							{
								$state_entity = true;
							}
						}
					}
					if($state_entity == false)
					{
						?>
						<option value="<?php  echo $_SESSION['m_admin']['entities'][$i]['ID']; ?>"><?php  echo $_SESSION['m_admin']['entities'][$i]['LABEL']; ?></option>
					<?php
					}
				}
				?>
				</select>
				<br/>
				<em><a href='javascript:selectall(document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'];?>_entitieslist"));' ><?php  echo _SELECT_ALL; ?></a></em>
			</td>
			<td width="20%" align="center">
				<input type="button" class="button" value="<?php  echo _ADD; ?> &gt;&gt;" onclick='Move(document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'];?>_entitieslist"),document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'];?>_entities_chosen"));' />
				<br />
				<br />
				<input type="button" class="button" value="&lt;&lt; <?php  echo _REMOVE; ?>" onclick='Move(document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'];?>_entities_chosen"),document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'];?>_entitieslist"));' />
			</td>
			<td width="40%" align="center">
				<select name="<?php echo $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'];?>_entities_chosen[]" id="<?php echo $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'];?>_entities_chosen" size="7" ondblclick='moveclick(document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'];?>_entities_chosen"),document.getElementById("<?php echo $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'];?>_entitieslist"));' multiple="multiple"   class="entities_list">
				<?php
				for($i=0;$i<count($_SESSION['m_admin']['entities']);$i++)
				{
					$state_entity = false;
					if(!$is_default_action)
					{
						for($j=0;$j<count($_SESSION['m_admin']['basket']['groups'][$_SESSION['m_admin']['basket']['ind_group']]['ACTIONS']);$j++)
						{
							for($k=0; $k<count($_SESSION['m_admin']['basket']['groups'][$_SESSION['m_admin']['basket']['ind_group']]['ACTIONS'][$j]['ENTITIES_LIST']);$k++)
							{
								if($_SESSION['m_admin']['entities'][$i]['ID'] == $_SESSION['m_admin']['basket']['groups'][$_SESSION['m_admin']['basket']['ind_group']]['ACTIONS'][$j]['ENTITIES_LIST'][$k]['ID'])
								{
									$state_entity = true;
								}
							}
						}
					}
					else
					{
						for($k=0; $k<count($_SESSION['m_admin']['basket']['groups'][$_SESSION['m_admin']['basket']['ind_group']]['PARAM_DEFAULT_ACTION']['ENTITIES_LIST']);$k++)
						{
							if($_SESSION['m_admin']['entities'][$i]['ID'] == $_SESSION['m_admin']['basket']['groups'][$_SESSION['m_admin']['basket']['ind_group']]['PARAM_DEFAULT_ACTION']['ENTITIES_LIST'][$k]['ID'])
							{
								$state_entity = true;
							}
						}
					}
					if($state_entity == true)
					{
					?>
						<option value="<?php  echo $_SESSION['m_admin']['entities'][$i]['ID']; ?>" selected="selected" ><?php  echo $_SESSION['m_admin']['entities'][$i]['LABEL']; ?></option>
					<?php
					}
				}
				?>
				</select>
				<br/>
				<em><a href="javascript:selectall(document.getElementById('<?php echo $_SESSION['m_admin']['basket']['all_actions'][$_SESSION['m_admin']['compteur']]['ID'];?>_entities_chosen'));" >
				<?php  echo _SELECT_ALL; ?></a></em>
			</td>
		</tr>
	</table>
	<?php
	}
}
elseif($_SESSION['service_tag'] == 'manage_groupbasket')
{
	require_once('modules/entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');
	$ent = new entities();
	//$ent->show_array($_SESSION['m_admin']['basket']['groups']);
	$groupe = $_REQUEST['group'];
	if(isset($_REQUEST['old_group']) && !empty($_REQUEST['old_group']))
	{
		$old_group = $_REQUEST['old_group'];
	}
	$ind = -1;
	for($i=0; $i < count($_SESSION['m_admin']['basket']['groups']); $i++)
	{
		$chosen_entities = array();
		for($j=0; $j<count($_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS']);$j++)
		{
			if(isset($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['ID_ACTION'].'_entities_chosen']) && count($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['ID_ACTION'].'_entities_chosen']) > 0)
			{
				for($k=0; $k < count($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['ID_ACTION'].'_entities_chosen']); $k++)
				{
					$arr = $ent->get_info_entity($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['ID_ACTION'].'_entities_chosen'][$k]);
					$label = $arr['label'];
					$keyword = $arr['keyword'];
					array_push($chosen_entities , array( 'ID' =>$_REQUEST[$_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['ID_ACTION'].'_entities_chosen'][$k], 'LABEL' => $label, 'KEYWORD' => $keyword));
				}
			}
			$_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['ENTITIES_LIST'] = $chosen_entities ;
		}
		if($_SESSION['m_admin']['basket']['groups'][$i]['GROUP_ID'] == $groupe)
		{
			$ind = $i;
			$find = true;
			break;
		}
		if($old_group == $_SESSION['m_admin']['basket']['groups'][$i]['GROUP_ID'])
		{
			$ind = $i;
			$find = true;
			break;
		}
	}

	if($find && $ind >= 0)
	{
		$_SESSION['m_admin']['basket']['groups'][$ind]['PARAM_DEFAULT_ACTION'] = array();
		$_SESSION['m_admin']['basket']['groups'][$ind]['PARAM_DEFAULT_ACTION']['ENTITIES_LIST'] = array();

		if(isset($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$ind]['DEFAULT_ACTION'].'_entities_chosen']) && count($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$ind]['DEFAULT_ACTION'].'_entities_chosen']) > 0)
		{

			for($l=0; $l < count($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$ind]['DEFAULT_ACTION'].'_entities_chosen']); $l++)
			{
				$arr = $ent->get_info_entity($_REQUEST[$_SESSION['m_admin']['basket']['groups'][$ind]['DEFAULT_ACTION'].'_entities_chosen'][$l]);
				$label = $arr['label'];
				$keyword = $arr['keyword'];
				array_push($_SESSION['m_admin']['basket']['groups'][$ind]['PARAM_DEFAULT_ACTION']['ENTITIES_LIST'] , array( 'ID' =>$_REQUEST[$_SESSION['m_admin']['basket']['groups'][$ind]['DEFAULT_ACTION'].'_entities_chosen'][$l], 'LABEL' => $label, 'KEYWORD' => $keyword));
			}
		}
	}
}
elseif($_SESSION['service_tag'] == 'load_basket_session')
{
	require_once('modules/entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');
	$entity_tmp = new entities();
	for($i=0; $i < count($_SESSION['m_admin']['basket']['groups'] ); $i++)
	{
		$_SESSION['m_admin']['basket']['groups'][$i]['PARAM_DEFAULT_ACTION'] = array();
		$_SESSION['m_admin']['basket']['groups'][$i]['PARAM_DEFAULT_ACTION']['ENTITIES_LIST'] = array();
		if(!empty($_SESSION['m_admin']['basket']['groups'][$i]['DEFAULT_ACTION'] ))
		{
			$array = $entity_tmp->get_values_redirect_groupbasket_db($_SESSION['m_admin']['basket']['groups'][$i]['GROUP_ID'], $_SESSION['m_admin']['basket']['basketId'],$_SESSION['m_admin']['basket']['groups'][$i]['DEFAULT_ACTION'] );
			$_SESSION['m_admin']['basket']['groups'][$i]['PARAM_DEFAULT_ACTION']['ENTITIES_LIST'] = $array['ENTITY'];
		}
		for($j=0;$j<count($_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS']);$j++)
		{
			$array = $entity_tmp->get_values_redirect_groupbasket_db($_SESSION['m_admin']['basket']['groups'][$i]['GROUP_ID'], $_SESSION['m_admin']['basket']['basketId'],$_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['ID_ACTION'] );
			$_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['ENTITIES_LIST'] = $array['ENTITY'];
		}
	}
}
elseif($_SESSION['service_tag'] == 'load_basket_db')
{
	require_once('modules/entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');
	$ent = new entities();
	$indexing_actions = array();
	for($i=0; $i<count($_SESSION['m_admin']['basket']['all_actions']);$i++ )
	{
		if($_SESSION['m_admin']['basket']['all_actions'][$i]['KEYWORD'] == 'indexing')
		{
			array_push($indexing_actions,$_SESSION['m_admin']['basket']['all_actions'][$i]['ID']);
		}
	}
	for($i=0; $i < count($_SESSION['m_admin']['basket']['groups'] ); $i++)
	{
		if(!empty($_SESSION['m_admin']['basket']['groups'][$i]['DEFAULT_ACTION'] )&& in_array($_SESSION['m_admin']['basket']['groups'][$i]['DEFAULT_ACTION'], $indexing_actions))
		{
			$ent->update_redirect_groupbasket_db($_SESSION['m_admin']['basket']['groups'][$i]['GROUP_ID'],  $_SESSION['m_admin']['basket']['basketId'],$_SESSION['m_admin']['basket']['groups'][$i]['DEFAULT_ACTION'],$_SESSION['m_admin']['basket']['groups'][$i]['PARAM_DEFAULT_ACTION']['ENTITIES_LIST']);
		}
		for($j=0;$j<count($_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS']);$j++)
		{
			if(in_array($_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['ID_ACTION'], $indexing_actions))
			{
				$ent->update_redirect_groupbasket_db($_SESSION['m_admin']['basket']['groups'][$i]['GROUP_ID'],  $_SESSION['m_admin']['basket']['basketId'],$_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['ID_ACTION'],$_SESSION['m_admin']['basket']['groups'][$i]['ACTIONS'][$j]['ENTITIES_LIST']);
			}
		}
	}

}
else if($_SESSION['service_tag'] == 'del_basket' && !empty($_SESSION['temp_basket_id']))
{
	require_once("core/class/class_db.php");
	$db = new dbquery();
	$db->query("delete from ".$_SESSION['tablename']['ent_groupbasket_redirect']." where basket_id = '".$_SESSION['temp_basket_id']."'");
	unset($_SESSION['temp_basket_id']);

}
?>
