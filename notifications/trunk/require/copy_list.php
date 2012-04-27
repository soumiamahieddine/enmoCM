<?php
function getContent()
//Affichage du formulaire/interface dans l'administration des notification => Envoi Ajax
{
	$content .= '<p class="sstit">' . _NOTIFICATIONS_COPY_LIST_DIFF_TYPE . '</p>';
	
	
	return $content;
}


function updatePropertiesSet($diffusion_properties){
	return null;	
}


function getExtraProperties(){
	
}

function getRecipients($ta, $event) {
	$query = "SELECT distinct us.* "
		. " FROM listinstance li LEFT JOIN users us ON li.item_id = us.user_id " 
		. " WHERE coll_id = 'letterbox_coll' AND res_id = ".$event->record_id
		. " AND listinstance_type='DOC' AND item_mode = 'cc'";
	return $query;
}
?>
