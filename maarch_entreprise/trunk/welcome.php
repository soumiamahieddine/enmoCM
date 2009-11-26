<?php
/**
* File : welcome.php
*
* French welcome page
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
$_SESSION['FOLDER']['SEARCH'] = array();

$core_tools = new core_tools();
$_SESSION['location_bar']['level2']['path']	= "";
$_SESSION['location_bar']['level2']['label'] = "";
$_SESSION['location_bar']['level2']['id'] = "";
$_SESSION['location_bar']['level3']['path'] = "";
$_SESSION['location_bar']['level3']['label'] = "";
$_SESSION['location_bar']['level3']['id'] = "";
$_SESSION['location_bar']['level4']['path'] = "";
$_SESSION['location_bar']['level4']['label'] = "";
$_SESSION['location_bar']['level4']['id'] = "";
$core_tools->manage_location_bar();
?>
<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_welcome_b.gif" alt="" /><?php echo _WELCOME;?></h1>
<div id="inner_content" class="clearfix">
<?php
$core_tools->execute_app_services($_SESSION['app_services'], "welcome.php");
$core_tools->execute_modules_services($_SESSION['modules_services'], 'welcome', "include");
?>
</div>
