<?php
switch ($request) {
case 'form_content':
	$form_content .= '<p class="sstit">' . _NOTIFICATIONS_DEST_USER_DIFF_TYPE . '</p>';
	break;

case 'recipients':
	$query = "SELECT distinct us.* "
		. " FROM listinstance li LEFT JOIN users us ON li.item_id = us.user_id " 
		. " WHERE coll_id = 'letterbox_coll' AND res_id = ".$event->record_id
		. " AND listinstance_type='DOC' AND item_mode = 'dest'";
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
