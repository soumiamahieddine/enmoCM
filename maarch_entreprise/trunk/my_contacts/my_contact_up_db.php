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
include('core/init.php');


require_once("core/class/class_functions.php");
require("core/class/class_core_tools.php");

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_service('my_contacts', 'apps');

require_once("core/class/class_db.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_contacts.php");

$contact = new contacts();
$contact->addupcontact($_POST['mode'], false);
?>
