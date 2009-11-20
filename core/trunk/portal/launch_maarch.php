<?php
/**
* File : launch_maarch.php
*
* Maarch launch script
*
* @package  maarch
* @version 2.5
* @since 10/2005
* @license GPL v3
* @author  Laurent Giovannoni  <dev@maarch.org>
*/
include('../core/init.php');

if(trim($_GET['app'])<> "" )
{
	$_SESSION['config']['app_id'] = $_GET['app'];
	header("location: ../apps/".$_GET['app']."/login.php?coreurl=".$_SESSION['config']['coreurl']);
}
else
{
	header("location: ../index.php");
}
exit();
?>
