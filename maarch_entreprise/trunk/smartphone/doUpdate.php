<?php
require_once('core/class/class_functions.php');
require_once('core/class/class_core_tools.php');
require_once('core/class/class_db.php');
require_once('core/class/class_security.php');
require_once('core/core_tables.php');
require_once('apps/maarch_entreprise/apps_tables.php');
require_once('modules/notes/notes_tables.php');
require_once('modules/notes/lang/fr.php');

$core = new core_tools();
$core->load_lang();
$db = new dbquery();
$db->connect();


if($_REQUEST['request'] == 'ok'){

	$query = "UPDATE " .APPS_CONTACTS_ADDRESSES. " SET ";
	$query .= "lastname = '" .$db->protect_string_db($_REQUEST['lastname']). "', ";
	$query .= "firstname = '" .$db->protect_string_db($_REQUEST['firstname']). "', ";
	$query .= "phone = '" .$db->protect_string_db($_REQUEST['phone']). "', ";
	$query .= "email = '" .$db->protect_string_db($_REQUEST['email']). "', ";
	$query .= "address_num = '" .$db->protect_string_db($_REQUEST['address_num']). "', ";
	$query .= "address_street = '" .$db->protect_string_db($_REQUEST['address_street']). "', ";
	$query .= "address_complement = '" .$db->protect_string_db($_REQUEST['address_complement']). "', ";
	$query .= "address_town = '" .$db->protect_string_db($_REQUEST['address_town']). "', ";
	$query .= "address_postal_code = '" .$db->protect_string_db($_REQUEST['address_postal_code']). "', ";
	$query .= "address_country = '" .$db->protect_string_db($_REQUEST['address_country']). "' ";
	$query .= "WHERE id = " .$db->protect_string_db($_REQUEST['id']);
	$result = $db->query($query);
	echo "{'status' : 'ok' , 'content' : '".$_SESSION['config']['businessappurl']."', 'error' : ''}";

}elseif($_REQUEST['request'] == 'cancel'){

	echo "{'status' : 'cancel' , 'content' : '".$_SESSION['config']['businessappurl']."', 'error' : ''}";

}







?>