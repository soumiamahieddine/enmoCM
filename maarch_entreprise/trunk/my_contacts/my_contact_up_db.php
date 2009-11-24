<?php
/**
* File : my_contact_up_db.php
*
* Modify the contact in the database after the form
*
* @package Maarch LetterBox 2.3
* @version 2.0
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->test_service('my_contacts', 'apps');

require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_contacts.php");

$contact = new contacts();
$contact->addupcontact($_POST['mode'], false);
?>
