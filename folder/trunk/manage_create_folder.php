<?php
include('core/init.php');

require_once("core/class/class_functions.php");
require_once("core/class/class_core_tools.php");
require_once("core/class/class_db.php");
require_once("core/class/class_request.php");
require_once("modules/folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
$core_tools = new core_tools();

$core_tools->load_lang();
$core_tools->test_admin('create_folder', 'folder');
$folder = new folder();
$folder->create_folder();
?>
