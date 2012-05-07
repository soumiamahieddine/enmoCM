<?php

/*********************************************************************************
** Get aditionnal data to merge template
**
*********************************************************************************/
$dbDatasource = new dbquery();
$dbDatasource->connect();

// Main document resource from view
$datasources['res'] = array();
$dbDatasource->query("SELECT * FROM " . $res_view . " WHERE res_id = " . $res_id . "");
$datasources['res'][] = $dbDatasource->fetch_assoc();

// Contact from mail
if ($datasources['res'][0]['exp_contact_id'] <> '') {
    $datasources['contact'] = array();
    $dbDatasource->query("SELECT * FROM contacts WHERE contact_id = ".$datasources['res'][0]['exp_contact_id']);
    $datasources['contact'][] = $dbDatasource->fetch_array();
}
if ($datasources['res'][0]['dest_contact_id'] <> '') {
    $datasources['contact'] = array();
    $dbDatasource->query("SELECT * FROM contacts WHERE contact_id = ".$datasources['res'][0]['dest_contact_id']);
    $datasources['contact'][] = $dbDatasource->fetch_array();
}

// Notes
$datasources['notes'] = array();
$dbDatasource->query("SELECT * FROM notes WHERE coll_id = '".$coll_id."' AND identifier = ".$res_id."");
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
