<?php

$db = new dbquery();
$db->connect();

$query = "SELECT contact_id, society, firstname, lastname, is_corporate_person FROM contacts_v2 WHERE enabled = 'Y'";

if ($_REQUEST['type_id'] <> "all") {
	$query .= " AND contact_type = ".$_REQUEST['type_id'];
}

$query .= " ORDER BY is_corporate_person desc, society, lastname";
$db->query($query);

$contact_selected = array();

while($res = $db->fetch_object()){
	$contact = "";
	if ($res->is_corporate_person == "Y") {
		$contact = $res->society;
	} else if ($res->is_corporate_person == "N") {
		$contact = $res->lastname .' '. $res->firstname;
	}
	array_push($contact_selected, array('id' => $res->contact_id, 'name' => $contact ));
}

$frmStr = '';

$countsContact = count($contact_selected);

if ($countsContact == 0) {
	$frmStr .= '<option value="">Aucun contact</option>'; 
} else {
	if ($_REQUEST['mode'] != "view") {
		$frmStr .= '<option value="">Choisissez un contact</option>'; 		
	} else if($_REQUEST['mode'] == "view"){
		$frmStr .= '<option value="">Voir les contacts</option>'; 
	}
}
 
for ($cptsContacts = 0;$cptsContacts< $countsContact;$cptsContacts++) {
	$frmStr .= '<option value="'.$db->show_string($contact_selected[$cptsContacts]['id']).'"';
	if ($_REQUEST['mode'] == "view") {
		$frmStr .= ' disabled ';
	}
	$frmStr .= '>'
	.  $db->show_string($contact_selected[$cptsContacts]['name'])
	. '</option>';
}

echo $frmStr;
exit;
