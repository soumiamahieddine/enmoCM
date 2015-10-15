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
echo "<script language=\"JavaScript\" type=\"text/javascript\" src=".$_SESSION['config']['businessappurl']."smartphone/js/maarch_functions.js></script>";
echo "<script language=\"JavaScript\" type=\"text/javascript\" src=".$_SESSION['config']['businessappurl']."smartphone/js/prototype.js></script>";




echo "<script language=\"JavaScript\" type=\"text/javascript\">modify_contact_smartphone(
'".$_SESSION['config']['businessappurl']."index.php?display=true&dir=smartphone&page=doUpdate','".utf8_encode ($_REQUEST['lastname']).
"','".utf8_encode ($_REQUEST['firstname']).
"','".utf8_encode ($_REQUEST['phone']).
"','".utf8_encode ($_REQUEST['email']).
"','".utf8_encode ($_REQUEST['address_num']).
"','".utf8_encode ($_REQUEST['address_street']).
"','".utf8_encode ($_REQUEST['address_complement']).
"','".utf8_encode ($_REQUEST['address_town']).
"','".utf8_encode ($_REQUEST['address_postal_code']).
"','".utf8_encode ($_REQUEST['address_country']).
"','".utf8_encode ($_REQUEST['id'])."')
</script>";


?>
