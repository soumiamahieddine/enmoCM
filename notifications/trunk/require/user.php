<?php



require_once 'core/core_tables.php';
require_once 'core/class/class_request.php';


function getContent($formId, $leftList, $rightList)
//Affichage du formulaire/interface dans l'administration des notification => Envoi Ajax
{
	//Recuperer l ensemble des utilisateurs valides
	$userslist = getUserList();
	
	$content .= '<input type="hidden" name="'.$formId.'" id="'.$formId.'" value="user">';
	$content .= '<p class="sstit">' . _NOTIFICATIONS_USER_DIFF_TYPE . '</p>';
	$content .= '<table>';
		$content .= '<tr>';
			$content .= '<td>';
				$content .= '<select name="'.$leftList.'[]" id="'.$leftList.'" size="7" 	ondblclick=\'moveclick(document.frmevent.elements["'.$leftList.'[]"],document.frmevent.elements["'.$rightList.'[]"]);\' multiple="multiple" >';
				foreach ($userslist as $a_user){
					$content .=  '<option value="'.$a_user['user_id'].'" selected="selected" >'.$a_user['firstname'].' '.$a_user['lastname'].'</option>';
				}
				
				$content .= '</select><br/>';
				$content .= '<em><a href=\'javascript:selectall(document.forms["frmevent"].elements["'.$leftList.'[]"]);\' >'._SELECT_ALL.'</a></em>';
			$content .= '</td>';
			$content .= '<td>';
			$content .= '<input type="button" class="button" value="'._ADD.'&gt;&gt;" onclick=\'Move(document.frmevent.elements["'.$leftList.'[]"],document.frmevent.elements["'.$rightList.'[]"]);\' />';
                $content .= '<br />';
                $content .= '<br />';
                $content .= '<input type="button" class="button" value="&lt;&lt;'._REMOVE.'"  onclick=\'Move(document.frmevent.elements["'.$rightList.'[]"],document.frmevent.elements["'.$leftList.'[]"]);\' />';
			$content .= '</td>';
			$content .= '<td>';
				$content .= '<select name="'.$rightList.'[]" id="'.$rightList.'" size="7" ondblclick=\'moveclick(document.frmevent.elements["'.$rightList.'[]"],document.frmevent.elements["'.$leftList.'"]);\' multiple="multiple" >';
				$content .= '</select><br/>';
				$content .= '<em><a href=\'javascript:selectall(document.forms["frmevent"].elements["'.$rightList.'[]"]);\' >'._SELECT_ALL.'</a></em>';
			$content .= '</td>';
		$content .= '</tr>';
	$content .= '</table>';
	
	return $content;
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
