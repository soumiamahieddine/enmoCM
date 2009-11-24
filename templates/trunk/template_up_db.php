<?php
/**
* File : template_up_db.php
*
*  Modify the template in the database after the form
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
require_once("modules".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_admin_templates.php");

$admin = new core_tools();
$admin->load_lang();
$admin->test_admin('admin_templates', 'templates');

$users = new admin_templates();

$users->uptemplate();
?>
