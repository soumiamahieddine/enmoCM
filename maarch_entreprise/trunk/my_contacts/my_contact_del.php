<?php
/**
* File : my_contact_del.php
*
* Delete contact
*
* @package Maarch LetterBox 2.3
* @version 1.0
* @since 10/2007
* @license GPL
* @author  Loic Vinet <dev@maarch.org>
*/
$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->test_service('my_contacts', 'apps');

require("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_contacts.php");

$func = new functions();

if(isset($_GET['id']))
{
	$s_id = addslashes($func->wash($_GET['id'], "alphanum", _THE_CONTACT));
}
else
{
	$s_id = "";
}

$contact = new contacts();
$contact->delcontact($s_id, false);
?>
