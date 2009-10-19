<?php
/**
* File : details_cases.php
*
* Detailed informations on an selected cases
*
* @package  Maarch Entreprise 1.0
* @version 1.0
* @since 10/2005
* @license GPL
* @author  Loïc Vinet  <dev@maarch.org>
*/


session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_docserver.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once($_SESSION['pathtocoreclass']."class_history.php");
require_once($_SESSION['pathtocoreclass']."class_manage_status.php");
require_once($_SESSION['pathtomodules']."cases".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR.'class_modules_tools.php');

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
$sec = new security();
$cases = new cases();
$db = new dbquery();
$status_obj = new manage_status();

if ($core_tools->test_service('join_res_case', 'cases') == 1) 
{


	$case_label = $_POST['case_label'];
	$case_description = $_POST['case_description'];
	$actual_res_id = $_POST['searched_value'];
	
	// Mettre du wash


	if ($case_label <> '' && $actual_res_id <> '')
	{
		if (!$cases->create_case($actual_res_id, $case_label, $case_description))
		{
			echo 'CASES ATTACHEMENT ERROR';
			exit();
		}
		else
		{ 
			$_SESSION['error'] = _CASE_CREATED;
		?>
			<script language="javascript">
			window.opener.top.location.reload();self.close();
			</script>
		<?php 
		}
	}

}

?>