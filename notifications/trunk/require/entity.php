<?php



require_once 'core/core_tables.php';
require_once 'core/class/class_request.php';


function getContent()
//Affichage du formulaire/interface dans l'administration des notification => Envoi Ajax
{
	//Recuperer l ensemble des utilisateurs valides
	
	
	$entitylist = getEntityList();
	
	$content .= '<input type="hidden" name="diffusion_type" id="diffusion_type" value="entity">';
	$content .= '<p class="sstit">' . _NOTIFICATIONS_ENTITY_DIFF_TYPE . '</p>';
	$content .= '<table>';
		$content .= '<tr>';
			$content .= '<td>';
				$content .= '<select name="entityslist[]" id="entityslist" size="7" 	ondblclick=\'moveclick(document.frmevent.elements["entityslist[]"],document.frmevent.elements["diffusion_properties[]"]);\' multiple="multiple" >';
				foreach ($entitylist as $a_entity){
					$content .=  '<option value="'.$a_entity['entity_id'].'" selected="selected" >'.$a_entity['firstname'].' '.$a_entity['lastname'].'</option>';
				}
				
				$content .= '</select><br/>';
				$content .= '<em><a href=\'javascript:selectall(document.forms["frmevent"].elements["entityslist[]"]);\' >'._SELECT_ALL.'</a></em>';
			$content .= '</td>';
			$content .= '<td>';
			$content .= '<input type="button" class="button" value="'._ADD.'&gt;&gt;" onclick=\'Move(document.frmevent.elements["entityslist[]"],document.frmevent.elements["diffusion_properties[]"]);\' />';
                $content .= '<br />';
                $content .= '<br />';
                $content .= '<input type="button" class="button" value="&lt;&lt;'._REMOVE.'"  onclick=\'Move(document.frmevent.elements["diffusion_properties[]"],document.frmevent.elements["entityslist[]"]);\' />';
			$content .= '</td>';
			$content .= '<td>';
				$content .= '<select name="diffusion_properties[]" id="diffusion_properties" size="7" ondblclick=\'moveclick(document.frmevent.elements["diffusion_properties[]"],document.frmevent.elements["entityslist"]);\' multiple="multiple" >';
				$content .= '</select><br/>';
				$content .= '<em><a href=\'javascript:selectall(document.forms["frmevent"].elements["diffusion_properties[]"]);\' >'._SELECT_ALL.'</a></em>';
			$content .= '</td>';
		$content .= '</tr>';
	$content .= '</table>';
	
	if ($_SESSION['m_admin']['event']['diffusion_properties'] <> '')
	{
		//Retourne les utilisateurs deja choisi si modification
		$content .= '<script type=\'text/javascript\'>alert(\'toto\');</script>';
		//$content .= getSelectedentitys($_SESSION['m_admin']['event']['diffusion_properties']);
	}
	
	return $content;
}

function updatePropertiesSet($diffusion_properties){
	
	$string = '';
	$values = $diffusion_properties;

	foreach($values as $value)
	{
		$string .= $value.',';
	}
	
	$string = substr($string, 0, -1);
	return $string;
	
}

function getExtraProperties(){
	//On découpe la chaine de caractère
	
	//V2 : Par variables de sessions
	$result = $_SESSION['m_admin']['event']['diffusion_properties'];
	
	$myreturn = explode(",", $result);
	?>
	
	<script language="javascript">
	alert('');
	var list = $("frmevent").elements["entityslist[]"];
	for (i=0;i<list.length;i++)
	{
		list[i].selected = false;
    }
	</script>
	
	
	<?php
	
	foreach($myreturn as $return){
	?>
	<script language="javascript">
		var list2 = $("frmevent").elements["entityslist[]"];
		for (i=0;i<list2.length;i++)
		{
			if (list2[i].value == "<?php echo $return; ?>")
			{
				list2[i].selected = true;
			}	
		}		
	</script>
	<?php 
	} 

	echo '<script>Move($("frmevent").elements["entityslist[]"],$("frmevent").elements["diffusion_properties[]"]);</script>';
		
}

function getentityList(){
	
	$query = 'select entity_id, entity_label from entities where enabled <> \'N\'';
	$db = new dbquery();
	$db->connect();
	
	$db->query($query);
	$return = array();
	while ($result = $db->fetch_object()){
		$this_v = array();
		$this_v['entity_id'] = $result->entity_id;
		$this_v['firstname'] = $result->entity_label;
		array_push($return, $this_v);
	}
	
	return $return;
}

function getRecipients($ta, $event) {
	$query = "SELECT distinct us.*" 
		. " FROM users_entities ue "
		. " LEFT JOIN users us ON us.user_id = ue.user_id "
		. " WHERE ue.entity_id in ('".$ta->diffusion_properties."')"
		. " AND us.enabled = 'Y'";
	return $query;
}


