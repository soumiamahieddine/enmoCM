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
$stmt = $dbDatasource->query("SELECT * FROM contacts_res WHERE res_id = ? AND contact_id = ? ", array($doc['res_id'], $res_contact_id));
$datasources['res_letterbox_contact'][] = $stmt->fetch(PDO::FETCH_ASSOC);

if ($datasources['res_letterbox_contact'][0]['contact_id'] <> '') {
        // $datasources['contact'] = array();
    $stmt = $dbDatasource->query("SELECT * FROM view_contacts WHERE contact_id = ? and ca_id = ? ", array($datasources['res_letterbox_contact'][0]['contact_id'], $datasources['res_letterbox_contact'][0]['address_id']));
    $myContact = $stmt->fetch(PDO::FETCH_ASSOC);
    $myContact['contact_title'] = $contacts->get_civility_contact($myContact['contact_title']);
    $myContact['title'] = $contacts->get_civility_contact($myContact['title']);
    $datasources['contact'][] = $myContact;

    // single Contact
}else if (isset($doc['contact_id']) && isset($doc['address_id'])) {
    $stmt = $dbDatasource->query("SELECT * FROM view_contacts WHERE contact_id = ? and ca_id = ? ", array($res_contact_id, $res_address_id));
    $myContact = $stmt->fetch(PDO::FETCH_ASSOC);
    $myContact['contact_title'] = $contacts->get_civility_contact($myContact['contact_title']);
    $myContact['title'] = $contacts->get_civility_contact($myContact['title']);
    $datasources['contact'][] = $myContact;
    
} else {
    $stmt = $dbDatasource->query("SELECT * FROM view_contacts WHERE contact_id = 0");
    $myContact = $stmt->fetch(PDO::FETCH_ASSOC);
    $datasources['contact'][] = $myContact;
}

if (isset($datasources['contact'][0]['title']) && $datasources['contact'][0]['title'] == '')
    $datasources['contact'][0]['title'] = $datasources['contact'][0]['contact_title'];
if (isset($datasources['contact'][0]['firstname']) && $datasources['contact'][0]['firstname'] == '')
    $datasources['contact'][0]['firstname'] = $datasources['contact'][0]['contact_firstname'];
if (isset($datasources['contact'][0]['lastname']) && $datasources['contact'][0]['lastname'] == '')
    $datasources['contact'][0]['lastname'] = $datasources['contact'][0]['contact_lastname'];
// Notes
$datasources['notes'] = array();
$stmt = $dbDatasource->query("SELECT notes.*, users.firstname, users.lastname FROM notes left join users on notes.user_id = users.user_id WHERE coll_id = ? AND identifier = ? ", array($coll_id, $res_id));
while($note = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $datasources['notes'][] = $note;
}

// Attachments
$datasources['attachments'] = array();
$myAttachment['chrono'] = $chronoAttachment;

// Transmissions
$datasources['transmissions'] = [];
if (isset($_SESSION['transmissionContacts'])) {

    if (isset($_SESSION['upfileTransmissionNumber']) && $_SESSION['transmissionContacts'][$_SESSION['upfileTransmissionNumber']]) {
        $curNb = $_SESSION['upfileTransmissionNumber'];
        $datasources['transmissions'][0]['currentContact_title'] = $contacts->get_civility_contact($_SESSION['transmissionContacts'][$curNb]['title']);
        $datasources['transmissions'][0]['currentContact_firstname'] = $_SESSION['transmissionContacts'][$curNb]['firstname'];
        $datasources['transmissions'][0]['currentContact_lastname'] = $_SESSION['transmissionContacts'][$curNb]['lastname'];
    }
    
    $array_Transmission = array();

    for ($nb = 1; $_SESSION['transmissionContacts'][$nb]; $nb++) {

//            $array_Transmission[] = $contacts->get_civility_contact($_SESSION['transmissionContacts'][$nb]['title'])
//                                                            . ' ' . $_SESSION['transmissionContacts'][$nb]['firstname']
//                                                            . ' ' . $_SESSION['transmissionContacts'][$nb]['lastname'];

        $datasources['transmissions'][0]['title' . $nb] = $contacts->get_civility_contact($_SESSION['transmissionContacts'][$nb]['title']);
        $datasources['transmissions'][0]['firstname' . $nb] = $_SESSION['transmissionContacts'][$nb]['firstname'];
        $datasources['transmissions'][0]['lastname' . $nb] = $_SESSION['transmissionContacts'][$nb]['lastname'];
    }
//    $datasources['transmissions'][0]['contacts'] = implode(', ', $array_Transmission);

}

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
