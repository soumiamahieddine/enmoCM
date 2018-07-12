<?php

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("apps".DIRECTORY_SEPARATOR."maarch_entreprise".DIRECTORY_SEPARATOR."department_list.php");

$address_id = $_REQUEST['address_id'];

if (is_numeric($address_id)) {
	$db = new Database();

	$stmt = $db->query("SELECT address_postal_code FROM contact_addresses WHERE id = ? AND (address_country ILIKE 'FRANCE' OR address_country = '' OR address_country IS NULL)", array($address_id));

	$res = $stmt->fetchObject();

	$department_id = substr($res->address_postal_code, 0, 2);

	if ((int) $department_id >= 97 || $department_id == '20') {
		$department_id = substr($res->address_postal_code, 0, 3);
		if ((int)$department_id < 202) {
			$department_id = "2A";
		} else if ((int)$department_id >= 202 && (int)$department_id < 970) {
			$department_id = "2B";
		}
	}
	if ($depts[$department_id] <> "") {
		$statusNB = 0;
	} else {
		$statusNB = 1;
	}
}

echo '{ status: '.$statusNB.', departement_name: "'.$department_id.' - '.$depts[$department_id].'", departement_id: "'.$department_id.'"}';
exit;