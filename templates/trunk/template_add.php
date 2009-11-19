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
include('core/init.php');


require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require_once("modules/templates".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_admin_templates.php");
require_once("core/class/class_core_tools.php");
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
