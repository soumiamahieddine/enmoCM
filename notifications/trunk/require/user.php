<?php



require_once 'core/core_tables.php';
require_once 'core/class/class_request.php';


function getContent()
//Affichage du formulaire/interface dans l'administration des notification => Envoi Ajax
{
	//Recuperer l ensemble des utilisateurs valides
	
	
	$userslist = getUserList();
	
	$content .= '<input type="hidden" name="diffusion_type" id="diffusion_type" value="user">';
	$content .= '<p class="sstit">' . _NOTIFICATIONS_USER_DIFF_TYPE . '</p>';
	$content .= '<table>';
		$content .= '<tr>';
			$content .= '<td>';
				$content .= '<select name="userslist[]" id="userslist" size="7" 	ondblclick=\'moveclick(document.frmevent.elements["userslist[]"],document.frmevent.elements["diffusion_properties[]"]);\' multiple="multiple" >';
				foreach ($userslist as $a_user){
					$content .=  '<option value="'.$a_user['user_id'].'" selected="selected" >'.$a_user['firstname'].' '.$a_user['lastname'].'</option>';
				}
				
				$content .= '</select><br/>';
				$content .= '<em><a href=\'javascript:selectall(document.forms["frmevent"].elements["userslist[]"]);\' >'._SELECT_ALL.'</a></em>';
			$content .= '</td>';
			$content .= '<td>';
			$content .= '<input type="button" class="button" value="'._ADD.'&gt;&gt;" onclick=\'Move(document.frmevent.elements["userslist[]"],document.frmevent.elements["diffusion_properties[]"]);\' />';
                $content .= '<br />';
                $content .= '<br />';
                $content .= '<input type="button" class="button" value="&lt;&lt;'._REMOVE.'"  onclick=\'Move(document.frmevent.elements["diffusion_properties[]"],document.frmevent.elements["userslist[]"]);\' />';
			$content .= '</td>';
			$content .= '<td>';
				$content .= '<select name="diffusion_properties[]" id="diffusion_properties" size="7" ondblclick=\'moveclick(document.frmevent.elements["diffusion_properties[]"],document.frmevent.elements["userslist"]);\' multiple="multiple" >';
				$content .= '</select><br/>';
				$content .= '<em><a href=\'javascript:selectall(document.forms["frmevent"].elements["diffusion_properties[]"]);\' >'._SELECT_ALL.'</a></em>';
			$content .= '</td>';
		$content .= '</tr>';
	$content .= '</table>';
	
	if ($_SESSION['m_admin']['event']['diffusion_properties'] <> '')
	{
		//Retourne les utilisateurs deja choisi si modification
		$content .= '<script type=\'text/javascript\'>alert(\'toto\');</script>';
		//$content .= getSelectedUsers($_SESSION['m_admin']['event']['diffusion_properties']);
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
	var list = $("frmevent").elements["userslist[]"];
	for (i=0;i<list.length;i++)
	{
		list[i].selected = false;
    }
	</script>
	
	
	<?php
	
	foreach($myreturn as $return){
	?>
	<script language="javascript">
		var list2 = $("frmevent").elements["userslist[]"];
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

	echo '<script>Move($("frmevent").elements["userslist[]"],$("frmevent").elements["diffusion_properties[]"]);</script>';
		
}

function getUserList(){
	
	$query = 'select firstname, lastname, user_id from '.USERS_TABLE.' where status <> \'DEL\'';
	$db = new dbquery();
	$db->connect();
	
	$db->query($query);
	$return = array();
	while ($result = $db->fetch_object()){
		$this_v = array();
		$this_v['user_id'] = $result->user_id;
		$this_v['firstname'] = $result->firstname;
		$this_v['lastname'] = $result->lastname;
		
		array_push($return, $this_v);
	}
	
	return $return;
}



