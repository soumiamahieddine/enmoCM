<?php
/**
* File : folder_history.php
*
* Show the history of a folder (indexing and salary sheet)
*
* @package  Maarch PeopleBox 1.0
* @version 2.0
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
$func = new functions;
$core_tools = new core_tools();
$core_tools->load_lang();
$hist = array();
require_once("modules".DIRECTORY_SEPARATOR."folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
if(isset($_SESSION['current_folder_id']) && !empty($_SESSION['current_folder_id']))
{
	$folder = new folder();
	$folder->load_folder($_SESSION['current_folder_id'],$_SESSION['tablename']['fold_folders'] );
	//print_r($folder);
	$hist = $folder->get_history();
}
$core_tools->load_html();
//here we building the header
$core_tools->load_header();
?>
<body id="hist_iframe">
<?php
if(count($hist) < 1)
{
	echo _PLEASE_SELECT_FOLDER.".";
}
else
{
	?>
	<table width="100%" class="listing" border="0" cellspacing="0">
    	<thead>
        	<tr>
            	<th><?php  echo _DATE;?></th>
                <th><?php  echo _USER;?></th>
                <th><?php  echo _EVENT;?></th>
            </tr>
        </thead>
        <tbody>
	<?php
		$color = "";
		for($cpt_folder_hist=0;$cpt_folder_hist<count($hist);$cpt_folder_hist++)
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
			<tr <?php  echo $color; ?> >
				<td><?php  echo $func->dateformat($hist[$cpt_folder_hist]['DATE']); ?></td>
                <td><?php  echo $func->show_string($hist[$cpt_folder_hist]['USER']); ?></td>
				<td><?php  echo $func->show_string($hist[$cpt_folder_hist]['EVENT']); ?></td>
			</tr>
			<?php
		}
	?></tbody>
	</table>
	<?php
}
?>
</body>
</html>
