<?php
//include('core/init.php');


if($_SESSION['service_tag'] == 'admin_models')
{?>
	<table align="center" width="100%" id="model_entities" >
		<tr>
			<td colspan="3"><?php  echo _CHOOSE_ENTITY_MODEL;?> :</td>
		</tr>
		<tr>
			<td width="40%" align="center">
				<select name="entitieslist[]" id="entitieslist" size="7" ondblclick='moveclick(document.frmmodel.elements["entitieslist[]"],document.frmmodel.elements["entities_chosen[]"]);' multiple="multiple" >
				<?php
				for($i=0;$i<count($_SESSION['m_admin']['entities']);$i++)
				{
					$state_entity = false;
					for($j=0;$j<count($_SESSION['m_admin']['model']['ENTITIES_LIST']);$j++)
					{
						if($_SESSION['m_admin']['entities'][$i]['ID'] == $_SESSION['m_admin']['model']['ENTITIES_LIST'][$j]['ID'])
						{
							$state_entity = true;
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
				</select><br/>
				<em><a href='javascript:selectall(document.forms["frmmodel"].elements["entitieslist[]"]);' ><?php  echo _SELECT_ALL; ?></a></em>
			</td>
			<td width="20%" align="center">
				<input type="button" class="button" value="<?php  echo _ADD; ?> &gt;&gt;" onclick='Move(document.frmmodel.elements["entitieslist[]"],document.frmmodel.elements["entities_chosen[]"]);' />
				<br />
				<br />
				<input type="button" class="button" value="&lt;&lt; <?php  echo _REMOVE; ?>" onclick='Move(document.frmmodel.elements["entities_chosen[]"],document.frmmodel.elements["entitieslist[]"]);' />
			</td>
			<td width="40%" align="center">
				<select name="entities_chosen[]" id="entities_chosen" size="7" ondblclick='moveclick(document.frmmodel.elements["entities_chosen[]"],document.frmmodel.elements["entitieslist"]);' multiple="multiple" >
				<?php
				for($i=0;$i<count($_SESSION['m_admin']['entities']);$i++)
				{
					$state_entity = false;
					for($j=0;$j<count($_SESSION['m_admin']['model']['ENTITIES_LIST']);$j++)
					{
						if($_SESSION['m_admin']['entities'][$i]['ID'] == $_SESSION['m_admin']['model']['ENTITIES_LIST'][$j]['ID'])
						{
							$state_entity = true;
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
				</select><br/>
				<em><a href="javascript:selectall(document.forms['frmmodel'].elements['entities_chosen[]']);" >
				<?php  echo _SELECT_ALL; ?></a></em>
			</td>
		</tr>
	</table><?php
}
elseif($_SESSION['service_tag'] == 'load_model_session')
{
	require_once('modules/models'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');

	$model = new models();
	$entities = $model->getAllItemsLinkedToModel($_SESSION['m_admin']['model']['ID'], 'destination');
	$_SESSION['m_admin']['model']['ENTITIES_LIST'] = array();
	$model->connect();
	for($i=0; $i<count($entities['destination']);$i++)
	{
		$model->query("select entity_label from ".$_SESSION['tablename']['ent_entities']." where entity_id = '".$entities['destination'][$i]."'" );
		$res = $model->fetch_array();
		array_push($_SESSION['m_admin']['model']['ENTITIES_LIST'], array('ID' => $entities['destination'][$i], 'LABEL' => $res->label));
	}

	$_SESSION['service_tag'] = '';
}
elseif($_SESSION['service_tag'] == 'model_info')
{
	require_once("modules/entities".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_manage_entities.php");
	$ent = new entity();
	$_SESSION['m_admin']['model']['ENTITIES'] = array();
	for($i=0;$i<count($_REQUEST['entities_chosen']); $i++)
	{
		$label = $ent->getentitylabel($_REQUEST['entities_chosen'][$i]);
		if($label <> false)
		{
			array_push($_SESSION['m_admin']['model']['ENTITIES'], array('ID' => $_REQUEST['entities_chosen'][$i], 'LABEL' => $label));
		}
	}
	$_SESSION['service_tag'] = '';
}
elseif($_SESSION['service_tag'] == 'load_model_db')
{
	//require_once("core/class/class_db.php");
	$db = new dbquery();
	$db->connect();
	$db->query("Delete from ".$_SESSION['tablename']['mod_models_association']." where model_id = '".$_SESSION['m_admin']['model']['ID']."' and what = 'destination'");

	for($i=0; $i < count($_SESSION['m_admin']['model']['ENTITIES']);$i++)
	{
		$db->query("insert into ".$_SESSION['tablename']['mod_models_association']." ( model_id, what, value_field, module  ) VALUES (  ".$_SESSION['m_admin']['model']['ID'].", 'destination', '".$_SESSION['m_admin']['model']['ENTITIES'][$i]['ID']."', 'entities')");
	}
	$_SESSION['service_tag'] = '';
}

?>
