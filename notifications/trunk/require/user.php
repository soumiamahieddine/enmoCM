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
				$content .= '<select name="completelist[]" id="userslist" size="7" 	ondblclick=\'moveclick(document.frmevent.elements["completelist[]"],document.frmevent.elements["diffusion_properties[]"]);\' multiple="multiple" >';
				foreach ($userslist as $a_user){
					$content .=  '<option value="'.$a_user['user_id'].'" selected="selected" >'.$a_user['firstname'].' '.$a_user['lastname'].'</option>';
				}
				
				$content .= '</select><br/>';
				$content .= '<em><a href=\'javascript:selectall(document.forms["frmevent"].elements["completelist[]"]);\' >'._SELECT_ALL.'</a></em>';
			$content .= '</td>';
			$content .= '<td>';
			$content .= '<input type="button" class="button" value="'._ADD.'&gt;&gt;" onclick=\'Move(document.frmevent.elements["completelist[]"],document.frmevent.elements["diffusion_properties[]"]);\' />';
                $content .= '<br />';
                $content .= '<br />';
                $content .= '<input type="button" class="button" value="&lt;&lt;'._REMOVE.'"  onclick=\'Move(document.frmevent.elements["diffusion_properties[]"],document.frmevent.elements["completelist[]"]);\' />';
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

function getRecipients($ta, $event) {
	$users = "'". str_replace(",", "','", $ta->diffusion_properties) . "'";
	$query = "SELECT us.*" 
		. " FROM users us"
		. " WHERE us.user_id in (".$users.")"
		. " AND us.enabled = 'Y'";
	return $query;
}
