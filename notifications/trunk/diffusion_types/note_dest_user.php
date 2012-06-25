<?php
switch ($request) {
case 'form_content':
	$form_content .= '<p class="sstit">' . _NOTIFICATIONS_DEST_USER_DIFF_TYPE . '</p>';
	break;

case 'recipients':
	$query = "SELECT distinct us.* "
		. " FROM listinstance li JOIN users us ON li.item_id = us.user_id "
        . " LEFT JOIN notes ON notes.coll_id = li.coll_id AND notes.identifier = li.res_id "
		. " WHERE notes.coll_id = 'letterbox_coll' AND notes.id = ".$event->record_id
		. " AND li.listinstance_type='DOC' AND li.item_mode = 'dest'"
        . " AND li.item_id != notes.user_id";
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
