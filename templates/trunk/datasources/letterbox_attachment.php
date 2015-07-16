<?php
/*********************************************************************************
** Get aditionnal data to merge template
**
*********************************************************************************/
$dbDatasource = new Database();

require_once 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_contacts_v2.php';
$contacts = new contacts_v2();

// Main document resource from view
$datasources['res_letterbox'] = array();
$stmt = $dbDatasource->query("SELECT * FROM " . $res_view . " WHERE res_id = ? ", array($res_id));
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

$date = new DateTime($doc['doc_date']);
$doc['doc_date']=$date->format('d/m/Y');

$admission_date = new DateTime($doc['admission_date']);
$doc['admission_date']=$admission_date->format('d/m/Y');

$creation_date = new DateTime($doc['creation_date']);
$doc['creation_date']=$creation_date->format('d/m/Y');

$process_limit_date = new DateTime($doc['process_limit_date']);
$doc['process_limit_date']=$process_limit_date->format('d/m/Y');

$doc['category_id'] = html_entity_decode($_SESSION['coll_categories']['letterbox_coll'][$doc['category_id']]);

$doc['nature_id'] = $_SESSION['mail_natures'][$doc['nature_id']];

$datasources['res_letterbox'][] = $doc;


//multicontact
$stmt = $dbDatasource->query("SELECT * FROM contacts_res WHERE res_id = ? AND contact_id = ? ", array($res_id, $res_contact_id));
$datasources['res_letterbox_contact'][] = $stmt->fetch(PDO::FETCH_ASSOC);
if ($datasources['res_letterbox_contact'][0]['contact_id'] <> '') {
    $datasources['contact'] = array();
    $stmt = $dbDatasource->query("SELECT * FROM view_contacts WHERE contact_id = ? and ca_id = ? ", array($datasources['res_letterbox_contact'][0]['contact_id'], $datasources['res_letterbox_contact'][0]['address_id']));
    $myContact = $stmt->fetch(PDO::FETCH_ASSOC);
    $myContact['contact_type'] = $contacts->get_label_contact($myContact['contact_type'], $_SESSION['tablename']['contact_types']);
    $myContact['contact_purpose_id'] = $contacts->get_label_contact($myContact['contact_purpose_id'], $_SESSION['tablename']['contact_purposes']);
    $myContact['contact_title'] = $contacts->get_civility_contact($myContact['contact_title']);
	$myContact['title'] = $contacts->get_civility_contact($myContact['title']);
    $datasources['contact'][] = $myContact;
// single Contact
}else if (isset($datasources['res_letterbox'][0]['contact_id']) && isset($datasources['res_letterbox'][0]['address_id'])) {

    $datasources['contact'] = array();
    $stmt = $dbDatasource->query("SELECT * FROM view_contacts WHERE contact_id = ? and ca_id = ? ", array($datasources['res_letterbox'][0]['contact_id'], $datasources['res_letterbox'][0]['address_id']));
    $myContact = $stmt->fetch(PDO::FETCH_ASSOC);
    $myContact['contact_type'] = $contacts->get_label_contact($myContact['contact_type'], $_SESSION['tablename']['contact_types']);
    $myContact['contact_purpose_id'] = $contacts->get_label_contact($myContact['contact_purpose_id'], $_SESSION['tablename']['contact_purposes']);
    $myContact['contact_title'] = $contacts->get_civility_contact($myContact['contact_title']);
    $myContact['title'] = $contacts->get_civility_contact($myContact['title']);
    $datasources['contact'][] = $myContact;
} else {
    $datasources['contact'] = array();
    $stmt = $dbDatasource->query("SELECT * FROM view_contacts WHERE contact_id = 0");
    $myContact = $stmt->fetch(PDO::FETCH_ASSOC);
    $datasources['contact'][] = $myContact;
}
// Notes
$datasources['notes'] = array();
$stmt = $dbDatasource->query("SELECT notes.*, users.firstname, users.lastname FROM notes left join users on notes.user_id = users.user_id WHERE coll_id = ? AND identifier = ? ", array($coll_id, $res_id));
while($note = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $datasources['notes'][] = $note;
}

// Attachments
$datasources['attachments'] = array();
$myAttachment['chrono'] = $chronoAttachment;

$img_file_name = $_SESSION['config']['tmppath'].$_SESSION['user']['UserId'].time().rand()."_barcode_attachment.png";

require_once('apps/maarch_entreprise/tools/pdfb/barcode/pi_barcode.php');
$objCode = new pi_barcode();

$objCode->setCode($chronoAttachment);
$objCode->setType('C128');
$objCode->setSize(30, 50);
  
$objCode->setText($chronoAttachment);
  
$objCode->hideCodeType();
  
$objCode->setFiletype('PNG');               

$objCode->writeBarcodeFile($img_file_name);

$myAttachment['chronoBarCode'] = $img_file_name;
$datasources['attachments'][] = $myAttachment;