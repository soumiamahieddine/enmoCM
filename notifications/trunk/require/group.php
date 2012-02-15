<?php
require_once 'core/core_tables.php';
require_once 'core/class/class_request.php';


function getContent()
//Affichage du formulaire/interface dans l'administration des notification => Envoi Ajax
{
	//Recuperer l ensemble des groupes valides
	
	
	$grouplist = getgroupList();
	
	$content .= '<input type="hidden" name="diffusion_type" id="diffusion_type" value="group">';
	$content .= '<p class="sstit">' . _NOTIFICATIONS_GROUP_DIFF_TYPE . '</p>';
	$content .= '<table>';
		$content .= '<tr>';
			$content .= '<td>';
				$content .= '<select name="groupslist[]" id="groupslist" size="7" 	ondblclick=\'moveclick(document.frmevent.elements["groupslist[]"],document.frmevent.elements["diffusion_properties[]"]);\' multiple="multiple" >';
				foreach ($grouplist as $a_group){
					$content .=  '<option value="'.$a_group['group_id'].'" selected="selected" >'.$a_group['firstname'].' '.$a_group['lastname'].'</option>';
				}
				
				$content .= '</select><br/>';
				$content .= '<em><a href=\'javascript:selectall(document.forms["frmevent"].elements["groupslist[]"]);\' >'._SELECT_ALL.'</a></em>';
			$content .= '</td>';
			$content .= '<td>';
			$content .= '<input type="button" class="button" value="'._ADD.'&gt;&gt;" onclick=\'Move(document.frmevent.elements["groupslist[]"],document.frmevent.elements["diffusion_properties[]"]);\' />';
                $content .= '<br />';
                $content .= '<br />';
                $content .= '<input type="button" class="button" value="&lt;&lt;'._REMOVE.'"  onclick=\'Move(document.frmevent.elements["diffusion_properties[]"],document.frmevent.elements["groupslist[]"]);\' />';
			$content .= '</td>';
			$content .= '<td>';
				$content .= '<select name="diffusion_properties[]" id="diffusion_properties" size="7" ondblclick=\'moveclick(document.frmevent.elements["diffusion_properties[]"],document.frmevent.elements["groupslist"]);\' multiple="multiple" >';
				$content .= '</select><br/>';
				$content .= '<em><a href=\'javascript:selectall(document.forms["frmevent"].elements["diffusion_properties[]"]);\' >'._SELECT_ALL.'</a></em>';
			$content .= '</td>';
		$content .= '</tr>';
	$content .= '</table>';
	
	if ($_SESSION['m_admin']['event']['diffusion_properties'] <> '')
	{
		//Retourne les utilisateurs deja choisi si modification
		$content .= '<script type=\'text/javascript\'>alert(\'toto\');</script>';
		//$content .= getSelectedgroups($_SESSION['m_admin']['event']['diffusion_properties']);
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
	var list = $("frmevent").elements["groupslist[]"];
	for (i=0;i<list.length;i++)
	{
		list[i].selected = false;
    }
	</script>
	
	
	<?php
	
	foreach($myreturn as $return){
	?>
	<script language="javascript">
		var list2 = $("frmevent").elements["groupslist[]"];
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

	echo '<script>Move($("frmevent").elements["groupslist[]"],$("frmevent").elements["diffusion_properties[]"]);</script>';
		
}

function getgroupList(){
	
	$query = 'select group_id, group_desc from usergroups where enabled <> \'N\'';
	$db = new dbquery();
	$db->connect();
	
	$db->query($query);
	$return = array();
	while ($result = $db->fetch_object()){
		$this_v = array();
		$this_v['group_id'] = $result->group_id;
		$this_v['firstname'] = $result->group_desc;
		array_push($return, $this_v);
	}
	
	return $return;
}

function getRecipients($ta, $event) {
	$query = "SELECT distinct us.*" 
		. " FROM usergroup_content ug "
		. "	LEFT JOIN users us ON us.user_id = ug.user_id" 
		. " WHERE ug.group_id in ('".$ta->diffusion_properties."')"
		. "	AND us.enabled = 'Y'";
	return $query;
}


