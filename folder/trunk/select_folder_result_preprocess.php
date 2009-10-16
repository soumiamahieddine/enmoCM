<?php
/**
* File : select_folder_result_preprocess.php
*
* service of storing the folder selected into var session (preprocess)
*
* @package  Maarch Framework 3.0
* @version 3.0
* @since 10/2005
* @license GPL
* @author  Laurent Giovannoni <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
//require_once($_SESSION['pathtocoreclass']."class_docserver.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();
require_once($_SESSION['pathtomodules']."folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
//echo $_SESSION['origin'];
if($_SESSION['origin'] <> 'store_file' && !$_SESSION['is_store'])
{
	if($_SESSION['current_folder_id'] == "")
	{
		$_SESSION['error'] .= _MUST_CHOOSE_A_FOLDER."<br/>";
	}
	else
	{
		array_push($_SESSION['data'], array('column' => 'folders_system_id', 'value' => $_SESSION['current_folder_id'], 'type' => 'int'));
	}
}
else
{
	array_push($_SESSION['data'], array('column' => 'folders_system_id', 'value' => 0, 'type' => 'int'));
}
?>
