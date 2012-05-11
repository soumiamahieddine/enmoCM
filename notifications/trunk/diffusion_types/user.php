<?php



require_once 'core/core_tables.php';
require_once 'core/class/class_request.php';
require_once 'core/class/users_controler.php';

switch($request) {
case 'form_content':
	//Recuperer l ensemble des utilisateurs valides
	$usrCtl = new users_controler();
	$userslist = $usrCtl->getAllUsers();
	
	$form_content .= '<input type="hidden" name="'.$formId.'" id="'.$formId.'" value="user">';
	$form_content .= '<p class="sstit">' . _NOTIFICATIONS_USER_DIFF_TYPE . '</p>';
	$form_content .= '<table>';
		$form_content .= '<tr>';
			$form_content .= '<td>';
				$form_content .= '<select name="'.$leftList.'[]" id="'.$leftList.'" size="7" 	ondblclick=\'moveclick(document.frmevent.elements["'.$leftList.'[]"],document.frmevent.elements["'.$rightList.'[]"]);\' multiple="multiple" >';
				foreach ($userslist as $user){
					$form_content .=  '<option value="'.$user->user_id.'" selected="selected" >'.$user->firstname.' '.$user->lastname.'</option>';
				}
				
				$form_content .= '</select><br/>';
				$form_content .= '<em><a href=\'javascript:selectall(document.forms["frmevent"].elements["'.$leftList.'[]"]);\' >'._SELECT_ALL.'</a></em>';
			$form_content .= '</td>';
			$form_content .= '<td>';
			$form_content .= '<input type="button" class="button" value="'._ADD.'&gt;&gt;" onclick=\'Move(document.frmevent.elements["'.$leftList.'[]"],document.frmevent.elements["'.$rightList.'[]"]);\' />';
                $form_content .= '<br />';
                $form_content .= '<br />';
                $form_content .= '<input type="button" class="button" value="&lt;&lt;'._REMOVE.'"  onclick=\'Move(document.frmevent.elements["'.$rightList.'[]"],document.frmevent.elements["'.$leftList.'[]"]);\' />';
			$form_content .= '</td>';
			$form_content .= '<td>';
				$form_content .= '<select name="'.$rightList.'[]" id="'.$rightList.'" size="7" ondblclick=\'moveclick(document.frmevent.elements["'.$rightList.'[]"],document.frmevent.elements["'.$leftList.'"]);\' multiple="multiple" >';
				$form_content .= '</select><br/>';
				$form_content .= '<em><a href=\'javascript:selectall(document.forms["frmevent"].elements["'.$rightList.'[]"]);\' >'._SELECT_ALL.'</a></em>';
			$form_content .= '</td>';
		$form_content .= '</tr>';
	$form_content .= '</table>';
	
	break;

case 'recipients':	
	$users = "'". str_replace(",", "','", $notification->diffusion_properties) . "'";
	$query = "SELECT us.*" 
		. " FROM users us"
		. " WHERE us.user_id in (".$users.")"
		. " AND us.enabled = 'Y'";
	$dbRecipients = new dbquery();
	$dbRecipients->connect();
	$dbRecipients->query($query);
	$recipients = array();
	while($recipient = $dbRecipients->fetch_object()) {
		$recipients[] = $recipient;
	}
	break;

case 'attach':
	$users = "'". str_replace(",", "','", (string)$notification->attachfor_properties) . "'";
	$query = "SELECT user_id" 
		. " FROM users"
		. " WHERE '".$user_id."' in (".$users.")";
	$attach = false;
	$dbAttach = new dbquery();
	$dbAttach->connect();
	$dbAttach->query($query);
	if($dbAttach->nb_result() > 0) {
		$attach = true;
	}
	break;	
}
