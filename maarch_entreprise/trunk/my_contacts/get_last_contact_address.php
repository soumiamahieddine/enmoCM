<?php

require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_request.php';
$db = new dbquery();

$db->connect();
$db->query("select is_corporate_person, contact_lastname, contact_firstname, society, contact_id, ca_id, creation_date from view_contacts where user_id = '". $_SESSION['user']['UserId'] . "' order by creation_date desc limit 1");

$res = $db->fetch_object();

if($res->is_corporate_person == 'Y'){
	$contact = $db->protect_string_db($res->contact_lastname) . ' ' . $db->protect_string_db($res->contact_firstname);
	if($res->society_short <> ''){
		$contact .= ' (' . $db->protect_string_db($res->society_short) . ')';
	} else if($res->society <> ''){
		$contact .= ' (' . $db->protect_string_db($res->society) . ')';
	}
} else {
	$contact = $db->protect_string_db($res->society);
	if($res->society_short <> ''){
		$contact .= ' (' . $db->protect_string_db($res->society_short) . ')';
	}
}

$contactId = $res->contact_id;
$addressId = $res->ca_id;

echo "{ status: 1, contactName: '" . $contact . "', contactId: '" . $contactId . "', addressId: '" . $addressId . "'}";
exit;