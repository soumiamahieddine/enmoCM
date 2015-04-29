<?php
/*********************************************************************************
** Get aditionnal data to merge template
**
*********************************************************************************/
$dbDatasource = new dbquery();
$dbDatasource->connect();

require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_contacts_v2.php';
$contacts = new contacts_v2();

// Main document resource from view
$datasources['res_letterbox'] = array();
$dbDatasource->query("SELECT * FROM " . $res_view . " WHERE res_id = " . $res_id . "");
$doc = $dbDatasource->fetch_array();
$date = new DateTime($doc['doc_date']);
$doc['doc_date']=$date->format('d/m/Y');
$datasources['res_letterbox'][] = $doc;


//multicontact
$dbDatasource->query("SELECT * FROM contacts_res WHERE res_id = " . $res_id . " AND contact_id ='".$res_contact_id."'");
$datasources['res_letterbox_contact'][] = $dbDatasource->fetch_assoc();
if ($datasources['res_letterbox_contact'][0]['contact_id'] <> '') {
    $datasources['contact'] = array();
    $dbDatasource->query("SELECT * FROM view_contacts WHERE contact_id = ".$datasources['res_letterbox_contact'][0]['contact_id']." and ca_id = ".$datasources['res_letterbox_contact'][0]['address_id']);
    $myContact = $dbDatasource->fetch_array();
    $myContact['contact_type'] = $contacts->get_label_contact($myContact['contact_type'], $_SESSION['tablename']['contact_types']);
    $myContact['contact_purpose_id'] = $contacts->get_label_contact($myContact['contact_purpose_id'], $_SESSION['tablename']['contact_purposes']);
    $myContact['contact_title'] = $contacts->get_civility_contact($myContact['contact_title']);
	$myContact['title'] = $contacts->get_civility_contact($myContact['title']);
    $datasources['contact'][] = $myContact;
// single Contact
}else{

    $datasources['contact'] = array();
    $dbDatasource->query("SELECT * FROM view_contacts WHERE contact_id = ".$datasources['res_letterbox'][0]['contact_id']." and ca_id = ".$datasources['res_letterbox'][0]['address_id']);
    $myContact = $dbDatasource->fetch_array();
    $myContact['contact_type'] = $contacts->get_label_contact($myContact['contact_type'], $_SESSION['tablename']['contact_types']);
    $myContact['contact_purpose_id'] = $contacts->get_label_contact($myContact['contact_purpose_id'], $_SESSION['tablename']['contact_purposes']);
    $myContact['contact_title'] = $contacts->get_civility_contact($myContact['contact_title']);
    $myContact['title'] = $contacts->get_civility_contact($myContact['title']);
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
/*$dbDatasource->query("SELECT *, (res_id + 1) as chrono FROM res_attachments WHERE coll_id = '".$coll_id."' AND res_id_master = ".$res_id." order by res_id desc");
while ($attachment = $dbDatasource->fetch_array()) {
    $datasources['attachments'][] = $attachment;
}*/
$myAttachment['chrono'] = $chronoAttachment;
$datasources['attachments'][] = $myAttachment;