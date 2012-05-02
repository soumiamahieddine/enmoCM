<?php



require_once 'core/core_tables.php';
require_once 'core/class/class_request.php';


function getContent($formId, $leftList, $rightList)
//Affichage du formulaire/interface dans l'administration des notification => Envoi Ajax
{
	//Recuperer l ensemble des utilisateurs valides
	$entitylist = getEntityList();
	
	$content .= '<input type="hidden" name="'.$formId.'" id="'.$formId.'" value="entity">';
	$content .= '<p class="sstit">' . _NOTIFICATIONS_ENTITY_DIFF_TYPE . '</p>';
	$content .= '<table>';
		$content .= '<tr>';
			$content .= '<td>';
				$content .= '<select name="'.$leftList.'[]" id="'.$leftList.'" size="7" ondblclick=\'moveclick(document.frmevent.elements["'.$leftList.'[]"],document.frmevent.elements["'.$rightList.'[]"]);\' multiple="multiple" >';
				foreach ($entitylist as $a_entity){
					$content .=  '<option value="'.$a_entity['entity_id'].'" selected="selected" >'.$a_entity['entity_label'].'</option>';
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

function getentityList(){
	
	$query = 'select entity_id, entity_label from entities where enabled <> \'N\'';
	$db = new dbquery();
	$db->connect();
	
	$db->query($query);
	$return = array();
	while ($result = $db->fetch_object()){
		$this_v = array();
		$this_v['entity_id'] = $result->entity_id;
		$this_v['entity_label'] = $result->entity_label;
		array_push($return, $this_v);
	}
	
	return $return;
}

function getRecipients($ta, $event) {
	$entities = "'". str_replace(",", "','", $ta->diffusion_properties) . "'";
	$query = "SELECT distinct us.*" 
		. " FROM users_entities ue "
		. " LEFT JOIN users us ON us.user_id = ue.user_id "
		. " WHERE ue.entity_id in (".$entities.")"
		. " AND us.enabled = 'Y'";
	return $query;
}



