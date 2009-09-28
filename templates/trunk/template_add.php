<?php
/**
* File : template_add.php
*
* Form to add a template
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtomodules']."templates".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_admin_templates.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$admin = new core_tools();
$admin->test_admin('admin_templates', 'templates');
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=template_add&module=templates';
$page_label = _MODIFICATION;
$page_id = "template_add";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/


$mod = new admin_templates();
$mod->formtemplate("add");
?>
