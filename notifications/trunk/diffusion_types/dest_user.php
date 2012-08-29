<?php
switch ($request) {
case 'form_content':
	$form_content .= '<p class="sstit">' . _NOTIFICATIONS_DEST_USER_DIFF_TYPE . '</p>';
	break;

case 'recipients':
	$query = "SELECT distinct us.* "
		. " FROM listinstance li LEFT JOIN users us ON li.item_id = us.user_id " 
		. " WHERE coll_id = 'letterbox_coll' AND listinstance_id = ".$event->record_id
		. " AND listinstance_type='DOC' AND item_type='user_id' AND item_mode = 'dest'";
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

case 'res_id':
    $query = "SELECT res_id "
		. " FROM listinstance WHERE listinstance_id = ".$event->record_id;
	$dbResId = new dbquery();
    $dbResId->connect();
	$dbResId->query($query);
	$res_id_record = $dbResId->fetch_object();
    $res_id = $res_id_record->res_id;
    break;
    
}
?>
