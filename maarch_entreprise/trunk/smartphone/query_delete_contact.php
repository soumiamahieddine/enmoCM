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
$db->query(
		"DELETE FROM " .APPS_CONTACTS_ADDRESSES. " WHERE id=" .$db->protect_string_db($_REQUEST['id'])
	);

//$db->show();
header("location:".  $_SERVER['HTTP_REFERER']); 
//header('Location:' . $_SESSION['config']['businessappurl']. 'smartphone/index.php?display=true&page=welcome#_contacts');

?>