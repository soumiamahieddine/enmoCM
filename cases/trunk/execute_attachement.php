<?php

session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");
require_once($_SESSION['pathtocoreclass']."class_manage_status.php");
require_once($_SESSION['config']['businessapppath'].'class'.DIRECTORY_SEPARATOR."class_indexing_searching_app.php");
require_once($_SESSION['config']['businessapppath'].'class'.DIRECTORY_SEPARATOR."class_types.php");
require_once($_SESSION['pathtomodules']."cases".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR.'class_modules_tools.php');

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$core_tools->test_service('join_res_case', 'cases');
$cases = new cases();

if ($_GET['searched_item'] == "case")
{

		$res_id_to_insert = $_POST['field'];
		$actual_case_id = $_GET['searched_value']; 
		
		if (!empty($res_id_to_insert ) && !empty($actual_case_id))
		{
			if($cases->join_res($actual_case_id, $res_id_to_insert)==true)
			{
				$_SESSION['error'] = _RESSOURCES_LINKED;
			}
			else
			{
				$_SESSION['error'] = _RESSOURCES_NOT_LINKED;
			}
			?>
			<script language="javascript">
			window.opener.top.location.reload();self.close();
			</script>
			<?php
		}
		else
		{
			echo _ERROR_WITH_CASES_ATTACHEMENT;
		}
}
