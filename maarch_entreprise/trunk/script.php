<?php 
/**
* File : script.php
*
* Maarch script link
*
* @package  Maarch Framework 3.0
* @version 3.0
* @since 10/2005
* @license GPL
* @author  Laurent Giovannoni
*/
include('core/init.php'); 


require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require_once("core/class/class_core_tools.php");

$core_tools = new core_tools();
$core_tools->insert_page();
?>
