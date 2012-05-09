<?php

require_once 'core/core_tables.php';
require_once 'core/class/class_request.php';
require_once 'modules/entities/class/EntityControler.php';

switch($request) {
case 'form_content':
	$entities = new EntityControler();
	$entities->connect();
	$entitylist = $entities->getAllEntities();
		
	$form_content .= '<input type="hidden" name="'.$formId.'" id="'.$formId.'" value="entity">';
	$form_content .= '<p class="sstit">' . _NOTIFICATIONS_ENTITY_DIFF_TYPE . '</p>';
	$form_content .= '<table>';
		$form_content .= '<tr>';
			$form_content .= '<td>';
				$form_content .= '<select name="'.$leftList.'[]" id="'.$leftList.'" size="7" ondblclick=\'moveclick(document.frmevent.elements["'.$leftList.'[]"],document.frmevent.elements["'.$rightList.'[]"]);\' multiple="multiple" >';
				foreach ($entitylist as $entity){
					$form_content .=  '<option value="'.$entity->entity_id.'" selected="selected" >'.$entity->entity_label.'</option>';
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
	$entities = "'". str_replace(",", "','", $templateAssocObj->diffusion_properties) . "'";
	$query = "SELECT distinct us.*" 
		. " FROM users_entities ue "
		. " LEFT JOIN users us ON us.user_id = ue.user_id "
		. " WHERE ue.entity_id in (".$entities.")"
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
	$entities = "'". str_replace(",", "','", $templateAssocObj->attachfor_properties) . "'";
	$query = "SELECT user_id" 
		. " FROM users_entities"
		. " WHERE entity_id in (".$entities.")"
		. " AND user_id = '".$user_id."'";
	$attach = false;
	$dbAttach = new dbquery();
	$dbAttach->connect();
	$dbAttach->query($query);
	if($dbAttach->nb_result() > 0) {
		$attach = true;
	}
	break;
}

?>