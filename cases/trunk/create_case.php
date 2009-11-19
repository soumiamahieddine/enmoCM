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


include('core/init.php');

require_once("core/class/class_functions.php");
require_once("core/class/class_core_tools.php");
require_once("core/class/class_db.php");
require_once("core/class/class_request.php");
require_once("core/class/class_docserver.php");
require_once("core/class/class_security.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once("core/class/class_history.php");
require_once("core/class/class_manage_status.php");
require_once("modules/cases".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR.'class_modules_tools.php');

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
$sec = new security();
$cases = new cases();
$db = new dbquery();
$status_obj = new manage_status();

if (($core_tools->test_service('join_res_case', 'cases', false) == 1) || ($core_tools->test_service('join_res_case_in_process', 'cases', false) == 1))
{


	$case_label = $db->protect_string_db($_POST['case_label']);
	$case_description = $db->protect_string_db($_POST['case_description']);
	$actual_res_id = $db->protect_string_db($_POST['searched_value']);
	
	if ($case_label <> '' && $actual_res_id <> '')
	{
		if (!$cases->create_case($actual_res_id, $case_label, $case_description))
		{
			echo 'CASES ATTACHEMENT ERROR';
			
		}
		else
		{ 
			
			if($_POST['searched_item'] == 'res_id_in_process')
			{
				$case_redemption = new cases();
				$case_id_newest = $case_redemption->get_case_id($actual_res_id);
				
				
				?>
				<script language="javascript">
					
				var case_id = window.opener.$('case_id');
				var case_label = window.opener.$('case_label');
				var case_description = window.opener.$('case_description');
				
				if(case_id)
				{
					case_id.value = '<?php echo $case_id_newest ;?>';
					case_label.value = '<?php echo $case_label ;?>';
					case_description.value = '<?php echo $case_description ;?>';
					
				}
				self.close();
			
				</script>
				<?php	
			
			}
			else
			{	
				$error = _CASE_CREATED;
				
				?>
				<script language="javascript">
				window.opener.top.location.reload();
				var error_div = window.opener.$('main_error');
				if(error_div)
				{
					error_div.update('<?php echo $error ;?>');
				}
				self.close();
				</script>
							
				<?php 
		
			}
		
		}
	}

}

?>
