<?php

/*********************************************************************************
** Get aditionnal data to merge template
**
*********************************************************************************/
function getDatasource($res_id, $res_view, $coll_id) {
	$db = new dbquery();
	$db->connect();
	
	$datasource = array();
	
	// Main document resource from view
	$datasource['res'] = array();
	$db->query("SELECT * FROM " . $res_view . " WHERE res_id = " . $res_id . "");
	$datasource['res'] = $db->fetch_array();
	
	// Contact from mail
	$datasource['contact'] = array();
	$db->query("SELECT * FROM contacts WHERE contact_id = '".$datasource['res']['exp_contact_id']."' OR contact_id = '".$datasource['res']['dest_contact_id']."'");
	$datasource['contact'] = $db->fetch_array();
	
	// Notes
	$datasource['notes'] = array();
	$db->query("SELECT * FROM notes WHERE coll_id = '".$coll_id."' AND identifier = ".$datasource['res']['res_id']."");
	while($note = $db->fetch_array()) {
		$datasource['notes'][] = $note;
	}
	
	// Attachments
	$datasource['attachments'] = array();
	$db->query("SELECT * FROM res_attachments WHERE coll_id = '".$coll_id."' AND res_id_master = ".$datasource['res']['res_id']."");
	while($attachment = $db->fetch_array()) {
		$datasource['attachments'][] = $attachment;
	}
	
	return $datasource;
}
?>
