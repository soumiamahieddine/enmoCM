<?php

switch ($request) {
case 'form_content':
//Affichage du formulaire/interface dans l'administration des notification => Envoi Ajax
	$form_content .= '<p class="sstit">' . _NOTIFICATIONS_COPY_LIST_DIFF_TYPE . '</p>';
	break;

case 'recipients':
	$query = "SELECT distinct us.* "
		. " FROM listinstance li LEFT JOIN users us ON li.item_id = us.user_id " 
		. " WHERE coll_id = 'letterbox_coll' AND res_id = ".$eventObj->record_id
		. " AND listinstance_type='DOC' AND item_mode = 'cc'";
	$dbRecipients = new dbquery();
	$dbRecipients->query($query);
	$dbRecipients->connect();
	$recipients = array();
	while($recipient = $dbRecipients->fetch_object()) {
		$recipients[] = $recipient;
	}
	break;
	
case 'attach':
	$attach = false;
	break;
}
?>
