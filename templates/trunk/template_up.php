<?php
/**
* File : template_up.php
*
* Form to modify a template
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();

$admin = new core_tools();
$admin->test_admin('admin_templates', 'templates');
require_once($_SESSION['pathtomodules']."templates".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_admin_templates.php");
/****************Management of the location bar  ************/
$init = false;
if($_REQUEST['reinit'] == "true")
{
	$init = true;
}
$level = "";
if($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
{
	$level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=template_up&module=templates';
$page_label = _MODIFICATION;
$page_id = "template_up";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
$func = new functions();
if(isset($_GET['id']))
{
	$id = addslashes($func->wash($_GET['id'], "alphanum", _THE_TEMPLATE));
}
else
{
	$id = "";
}
$users = new admin_templates();
$users->formtemplate("up",$id);
?>
