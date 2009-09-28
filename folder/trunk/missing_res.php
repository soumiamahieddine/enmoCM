<?php 
/**
* File : missing_res.php
*
* Frame to show a the missing doc of a folder
*
* @package  Maarch PeopleBox 1.0
* @version 1.0
* @since 10/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
session_name('PeopleBox'); 
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php"); 
$core_tools = new core_tools();
$core_tools->load_lang();
require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");
$table ="";
if(isset($_SESSION['collection_choice']) && !empty($_SESSION['collection_choice']))
{
	$table = $_SESSION['collection_choice'];
}
else
{
	$table = $_SESSION['collections'][0]['table'];
}
$missing_res = array();
 
if(isset($_SESSION['current_folder_id']) && !empty($_SESSION['current_folder_id']))
{
	$folder = new folder();
	$folder->load_folder1($_SESSION['current_folder_id'], $_SESSION['tablename']['fold_folders']);
	//$contrat = $folder->get_contract_type();
	//$missing_res = $folder->missing_res($table, $_SESSION['tablename']['contracts_doctypes'], $_SESSION['tablename']['doctypes'], $_SESSION['current_folder_id'], $contrat);	
	$foldertype_id = $folder->get_field('foldertype_id');
	$missing_res = $folder->missing_res($table, $_SESSION['tablename']['fold_foldertypes_doctypes'], $_SESSION['tablename']['doctypes'], $_SESSION['current_folder_id'], $foldertype_id);			
}
$core_tools->load_html();
//here we building the header
$core_tools->load_header();
?>
<body id="missing_iframe">
<?php  
if(count($missing_res) < 1 && isset($_SESSION['current_folder_id'])&& !empty($_SESSION['current_folder_id']))
{
	echo _FOLDER.' '.strtolower(_COMPLETE);
}
else if (count($missing_res) < 1 && !isset($_SESSION['current_folder_id']) )
{
	echo _PLEASE_SELECT_FOLDER.".";
}
else
{
	?>
		<table width="95%" class="listing" border="0" cellspacing="0">
        	<thead>
            	<tr>
                	<th width="30%"><?php  echo _ID;?></th>
                    <th><?php  echo _DESC;?></th>
                </tr>
            </thead>
            <tbody>
			<?php 
				$color = "";
				for($cpt_missing_res=0; $cpt_missing_res < count($missing_res); $cpt_missing_res++)
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
                   		 <td>
							<?php  echo $missing_res[$cpt_missing_res]['ID'];?>
						</td>
						<td>
							<?php  echo $missing_res[$cpt_missing_res]['LABEL'];?>
						</td>
					</tr>
					<?php 
				}
			?>
            </tbody>
		</table>
	<?php 
}
?>
</body>
</html>