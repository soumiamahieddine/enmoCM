<?php 
/**
* File : contract_history.php
*
* Frame to show the contract history of a folder (used in salary_sheet)
*
* @package  Maarch PeopleBox 1.0
* @version 1.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
 session_name('PeopleBox'); 
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
 require($_SESSION['pathtocoreclass']."class_core_tools.php");

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_service('show_contract_history', 'apps');

require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");
$func = new functions();
$hist = array();

 if(isset($_SESSION['current_folder_id']) && !empty($_SESSION['current_folder_id']))
{
	$folder = new folder();
	$folder->load_folder1($_SESSION['current_folder_id'],$_SESSION['tablename']['fold_folders'] );
	$hist = $folder->get_contract_history($_SESSION['tablename']['history'], $_SESSION['tablename']['fold_folders']);
}
$core_tools->load_html();
//here we building the header
$core_tools->load_header( );	
?>

<body id="contract_history_frame">
<?php  if(count($hist) < 1 && (empty($_SESSION['current_folder_id']) || !isset($_SESSION['current_folder_id'])))
{ 
	echo _PLEASE_SELECT_FOLDER.".";
}
else
{
	?>
	<table width="95%" class="listing" border="0" cellspacing="0">
	<?php 
		$color = "";
		for($i=0; $i < count($hist); $i++)
		{
			if($color == ' class="col"')
			{
				$color = '';
			}
			else
			{
				$color = ' class="col"';
			}
			?>
			<tr<?php  echo $color; ?>>
				<td><?php  echo $func->format_date_db($hist[$i]['DATE']); ?></td>
				<td><?php  echo $hist[$i]['EVENT']; ?></td>
			</tr>
			<?php 
		}
	?>
	</table>
	<?php 
}
?>
</body>
</html>