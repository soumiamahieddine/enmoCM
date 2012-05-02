<?php
require_once 'core/core_tables.php';
require_once 'core/class/class_request.php';


function getContent($formId, $leftList, $rightList) {
//Affichage du formulaire/interface dans l'administration des notification => Envoi Ajax
	//Recuperer l ensemble des groupes valides
	$grouplist = getgroupList();
	
	$content .= '<input type="hidden" name="'.$formId.'" id="'.$formId.'" value="group">';
	$content .= '<p class="sstit">' . _NOTIFICATIONS_GROUP_DIFF_TYPE . '</p>';
	$content .= '<table>';
		$content .= '<tr>';
			$content .= '<td>';
				$content .= '<select name="'.$leftList.'[]" id="'.$leftList.'" size="7" ondblclick=\'moveclick(document.frmevent.elements["'.$leftList.'[]"],document.frmevent.elements["'.$rightList.'[]"]);\' multiple="multiple" >';
				foreach ($grouplist as $a_group) {
					$content .=  '<option value="'.$a_group['group_id'].'" selected="selected" >'.$a_group['group_desc'].'</option>';
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

function getgroupList(){
	
	$query = 'select group_id, group_desc from usergroups where enabled <> \'N\'';
	$db = new dbquery();
	$db->connect();
	
	$db->query($query);
	$return = array();
	while ($result = $db->fetch_object()){
		$this_v = array();
		$this_v['group_id'] = $result->group_id;
		$this_v['group_desc'] = $result->group_desc;
		array_push($return, $this_v);
	}
	
	return $return;
}

function getRecipients($ta, $event) {
	$groups = "'". str_replace(",", "','", $ta->diffusion_properties) . "'";
	$query = "SELECT distinct us.*" 
		. " FROM usergroup_content ug "
		. "	LEFT JOIN users us ON us.user_id = ug.user_id" 
		. " WHERE ug.group_id in (".$groups.")"
		. "	AND us.enabled = 'Y'";
	return $query;
}


