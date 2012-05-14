<?php

/*********************************************************************************
** Get aditionnal data to merge template
**
*********************************************************************************/
$dbDatasource = new dbquery();
$dbDatasource->connect();

// Main document resource from view
$datasources['res_letterbox'] = array();
$dbDatasource->query("SELECT * FROM " . $res_view . " WHERE res_id = " . $res_id . "");
$datasources['res_letterbox'][] = $dbDatasource->fetch_assoc();

// Contact from mail
if ($datasources['res_letterbox'][0]['exp_contact_id'] <> '') {
    $datasources['contact'] = array();
    $dbDatasource->query("SELECT * FROM contacts WHERE contact_id = ".$datasources['res_letterbox'][0]['exp_contact_id']);
    $myContact = $dbDatasource->fetch_array();
	$myContact['title'] = $_SESSION['mail_titles'][$myContact['title']];
	$datasources['contact'][] = $myContact;
}
if ($datasources['res_letterbox'][0]['dest_contact_id'] <> '') {
    $datasources['contact'] = array();
    $dbDatasource->query("SELECT * FROM contacts WHERE contact_id = ".$datasources['res_letterbox'][0]['dest_contact_id']);
    $myContact = $dbDatasource->fetch_array();
	$myContact['title'] = $_SESSION['mail_titles'][$myContact['title']];
	$datasources['contact'][] = $myContact;
}

// Notes
$datasources['notes'] = array();
$dbDatasource->query("SELECT notes.*, users.firstname, users.lastname FROM notes left join users on notes.user_id = users.user_id WHERE coll_id = '".$coll_id."' AND identifier = ".$res_id."");
while($note = $dbDatasource->fetch_array()) {
    $datasources['notes'][] = $note;
}

// Attachments
$datasources['attachments'] = array();
$dbDatasource->query("SELECT * FROM res_attachments WHERE coll_id = '".$coll_id."' AND res_id_master = ".$res_id."");
while($attachment = $dbDatasource->fetch_array()) {
    $datasources['attachments'][] = $attachment;
}
?>
