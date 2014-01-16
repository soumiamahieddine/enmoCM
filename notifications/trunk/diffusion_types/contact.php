<?php
switch ($request) {
case 'form_content':
    $form_content .= '<p class="sstit">' . _NOTIFICATIONS_CONTACT_DIFF_TYPE . '</p>';
    $form_content .= '<input type="hidden" name="'.$formId.'" id="'.$formId.'" value="contact">';
    break;

case 'recipients':
    $query = "SELECT contact_id as user_id, contact_email as mail"
        . " FROM res_view_letterbox " 
        . " WHERE (contact_email is not null or contact_email <> '') and res_id = ".$event->record_id;
    $dbRecipients = new dbquery();
    $dbRecipients->query($query);
    $dbRecipients->connect();
    $recipients = array();
    while($recipient = $dbRecipients->fetch_object()) {
        $recipients[] = $recipient;
    }
    break;

case 'attach':
	$query = "SELECT contact_id as user_id, contact_email as mail"
        . " FROM res_view_letterbox " 
        . " WHERE (contact_email is not null or contact_email <> '') and res_id = ".$event->record_id;
	$attach = false;
	$dbAttach = new dbquery();
	$dbAttach->connect();
	$dbAttach->query($query);
	if($dbAttach->nb_result() > 0) {
		$attach = true;
	}
	break;
}
