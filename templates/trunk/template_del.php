<?php
/**
* File : template_del.php
*
* Delete a template
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

require_once("modules".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_admin_templates.php");

$func = new functions();
$core_tools = new core_tools();

$core_tools->load_lang();
$core_tools->test_admin('admin_templates', 'templates');

if(isset($_GET['id']))
{
	$s_id = addslashes($func->wash($_GET['id'], "alphanum", _THE_TEMPLATE));
}
else
{
	$s_id = "";
}
$lb = new admin_templates();
$lb->deltemplate($s_id);
?>
