<?php
if (file_exists('../../../core/init.php')) {
    include_once '../../../core/init.php';
}
if (!isset($_SESSION['config']['coreurl'])) {
    header('location: ../../../');
}
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



$query = "INSERT INTO " .APPS_CONTACTS_ADDRESSES. " (";
if($_REQUEST['lastname'] != ""){
	$query .= "lastname, ";
	echo $_REQUEST['lastname'];
	$query2 = "'".$db->protect_string_db($_REQUEST['lastname']) . "', ";
}
if($_REQUEST['firstname'] != ""){
	$query .= "firstname, ";
	echo $_REQUEST['firstname'];
	$query2 .= "'".$db->protect_string_db($_REQUEST['firstname']) . "', ";
}
if($_REQUEST['contact_purpose_id'] != ""){
	$query .= "contact_purpose_id, ";
	echo $_REQUEST['contact_purpose_id'];
	$query2 .= "'".$db->protect_string_db($_REQUEST['contact_purpose_id']) . "', ";
}
if($_REQUEST['society'] != ""){
	$query .= "contact_id, ";
	echo $_REQUEST['society'];
	$query2 .= "'".$db->protect_string_db($_REQUEST['society']) . "', ";
}

if($_REQUEST['phone'] != ""){
	$query .= "phone, ";
	$query2 .= "'".$db->protect_string_db($_REQUEST['phone']) . "', ";
}
if($_REQUEST['mail'] != ""){
	$query .= "email, ";
	$query2 .= "'".$db->protect_string_db($_REQUEST['mail']) . "', ";
}
if($_REQUEST['address_num'] != ""){
	$query .= "address_num, ";
	$query2 .= "'".$db->protect_string_db($_REQUEST['address_num']) . "', ";
}
if($_REQUEST['address_street'] != ""){
	$query .= "address_street, ";
	$query2 .= "'".$db->protect_string_db($_REQUEST['address_street']) . "', ";
}
if($_REQUEST['address_complement'] != ""){
	$query .= "address_complement, ";
	$query2 .= "'".$db->protect_string_db($_REQUEST['address_complement']) . "', ";
}
if($_REQUEST['address_town'] != ""){
	$query .= "address_town, ";
	$query2 .= "'".$db->protect_string_db($_REQUEST['address_town']) . "', ";
}
if($_REQUEST['address_postal_code'] != ""){
	$query .= "address_postal_code, ";
	$query2 .= "'".$db->protect_string_db($_REQUEST['address_postal_code']). "', ";
}
if($_REQUEST['address_country'] != ""){
	$query .= "address_country, ";
	$query2 .= "'".$db->protect_string_db($_REQUEST['address_country']) . "', ";
}
if($_REQUEST['other_data'] != ""){
	$query .= "other_data, ";
	$query2 .= "'".$db->protect_string_db($_REQUEST['other_data']) . "', ";
}


$query .= "user_id, ";
$query2 .= "'".$db->protect_string_db($_SESSION['user']['UserId']) . "',";

$query .= "entity_id) VALUES (";
$query2 .= "'".$db->protect_string_db($_SESSION['user']['primaryentity']['id']) . "')";

$db->query($query . $query2);

//$db->show();
   
//header('Location: index.php?page=welcome#_contacts');   
header("location:".  $_SERVER['HTTP_REFERER']); 
?>