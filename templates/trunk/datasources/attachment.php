<?php

/*********************************************************************************
** Get aditionnal data to merge template
**
*********************************************************************************/
function getDatasource($paramArr) {
    $db = new dbquery();
    $db->connect();
    
    // Main document resource from view
    $datasource['res'] = array();
    $db->query("SELECT * FROM " . $paramArr['res_view'] . " WHERE res_id = " . $paramArr['res_id'] . "");
    $datasource['res'][] = $db->fetch_assoc();

    // Contact from mail
    if ($datasource['res'][0]['exp_contact_id'] <> '') {
        $datasource['contact'] = array();
        $db->query("SELECT * FROM contacts WHERE contact_id = ".$datasource['res'][0]['exp_contact_id']);
        $datasource['contact'][] = $db->fetch_array();
    }
    if ($datasource['res'][0]['dest_contact_id'] <> '') {
        $datasource['contact'] = array();
        $db->query("SELECT * FROM contacts WHERE contact_id = ".$datasource['res'][0]['dest_contact_id']);
        $datasource['contact'][] = $db->fetch_array();
    }
    
    // Notes
    $datasource['notes'] = array();
    $db->query("SELECT * FROM notes WHERE coll_id = '".$paramArr['coll_id']."' AND identifier = ".$paramArr['res_id']."");
    while($note = $db->fetch_array()) {
        $datasource['notes'][] = $note;
    }
    
    // Attachments
    $datasource['attachments'] = array();
    $db->query("SELECT * FROM res_attachments WHERE coll_id = '".$paramArr['coll_id']."' AND res_id_master = ".$paramArr['res_id']."");
    while($attachment = $db->fetch_array()) {
        $datasource['attachments'][] = $attachment;
    }

    return $datasource;
}
?>
