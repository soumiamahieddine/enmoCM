<?php
/**
* File : admin.php
*
* Administration summary Page
* {@internal this page calls an admin object (class admin)}}
*
* @package  Maarch Framework v3
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

/**
* include the test admin page
*
* this page tests the user access level, and if the user is an admin or not
*/

require("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_admin.php");
$admin = new admin();

$core_tools2 = new core_tools();
$core_tools2->test_admin('admin', 'apps');
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=admin';
$page_label = _ADMIN;
$page_id = "admin";
$core_tools2->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
unset($_SESSION['m_admin']);
?>
<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_admin_b.gif" alt="" /> <?php  echo _ADMIN;?></h1>
<div id="inner_content" class="clearfix">
<?php
$admin->retrieve_app_admin_services($_SESSION['app_services']);
?>
<!--<hr />-->
<?php
$admin->retrieve_modules_admin_services($_SESSION['modules_services']);
?>
</div>
