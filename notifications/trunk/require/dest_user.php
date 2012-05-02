<?php
function getContent($formId, $leftList, $rightList)
//Affichage du formulaire/interface dans l'administration des notification => Envoi Ajax
{
	$content .= '<p class="sstit">' . _NOTIFICATIONS_DEST_USER_DIFF_TYPE . '</p>';

	return $content;
}

function getRecipients($ta, $event) {
	$query = "SELECT distinct us.* "
		. " FROM listinstance li LEFT JOIN users us ON li.item_id = us.user_id " 
		. " WHERE coll_id = 'letterbox_coll' AND res_id = ".$event->record_id
		. " AND listinstance_type='DOC' AND item_mode = 'dest'";
	return $query;
}
?>
