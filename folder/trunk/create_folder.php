<?php
/**
* File : create_folder.php
*
* Result of the creation folder form
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
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();
require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");

$func = new functions();
$folder = new folder();
$_SESSION['type'] = '';
$_SESSION['error'] = '';
//$_SESSION['new_folder'] = array();
$data = array();

if($database_type == "SQLSERVER")
{
	$_SESSION['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
}
else // MYSQL & POSTGRESQL
{
	 $_SESSION['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
}
foreach(array_keys($_GET) as $value)
{
	if($value <> "submit" && !$folder->is_mandatory_field($value))
	{
		//echo $value." ".$_GET[$value]."<br/>";
		if($folder->is_mandatory($value))
		{
			if(empty($_GET[$value]))
			{
				$_SESSION['error'] .= $folder->retrieve_index_label($value)." "._MANDATORY.".<br/>";
				$_SESSION['field_error'][$value] = true;
				//echo $_SESSION['error'];
			}
		}
		if(!empty($_GET[$value]))
		{
			$data = $folder->user_exit($value, $data);
		}
		$_SESSION['create_folder'][$value] = $_GET[$value];
	}
}

if(!isset($_SESSION['foldertype']) || empty($_SESSION['foldertype']))
{
	$_SESSION['error'] .= _FOLDERTYPE_MANDATORY.".<br/>";
}
else
{
	array_push($data, array('column' => "foldertype_id", 'value' => $_SESSION['foldertype'], 'type' => "string"));
}

if(!empty($_SESSION['error']) || $data == array())
{
	?>
	<script language="javascript" type="text/javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=create_folder_form&module=folder';</script>
	<?php
	//header("location: ".$_SESSION['config']['businessappurl']."index.php?page=create_folder_form&module=folder");
	//exit;
}
else
{
	//$func->show_array($data);
	$folder_id = $folder->create_folder($_SESSION['tablename']['param'], $_SESSION['tablename']['fold_folders'], $data, $_SESSION['config']['databasetype']);
 	$folder->load_folder2($folder_id, $_SESSION['tablename']['fold_folders']);
	$_SESSION['current_folder_id'] = $folder->get_field('folders_system_id');

	$folder->modify_default_folder_in_db($_SESSION['current_folder_id'], $_SESSION['user']['UserId'], $_SESSION['tablename']['users']);

	if($_SESSION['history']['folderadd'] == "true")
	{
		require_once($_SESSION['pathtocoreclass']."class_history.php");
		$hist = new history();
		$tmp_id = $folder->get_field('folder_id');
		$hist->add($_SESSION['tablename']['fold_folders'], $_SESSION['current_folder_id'],"ADD", _FOLDER_CREATION." : ".$tmp_id, $_SESSION['config']['databasetype'],'folder');
		//$contrat_id = $folder->get_contract_type();
		$contrat_id = $folder->get_field('custom_t4', true);
		$hist->connect();
		//$hist->query("select contract_label as label from ".$_SESSION['tablename']['contracts']." where contract_id = ".$contrat_id);

		//$res = $hist->fetch_object();
		//$contrat = $res->label;
		//$hist->add($_SESSION['tablename']['fold_folders'],$_SESSION['current_folder_id'] ,"UP_CONTRACT", $contrat, $_SESSION['config']['databasetype'],'folder');
	}
	// $_SESSION['new_folder'] = array();
	unset($_SESSION['foldertype']);
	unset($_SESSION['create_folder']);
	unset($_SESSION['folder_index_to_use']);
	unset($_SESSION['datepattern']);
	?>
    <script language="javascript" type="text/javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=show_folder&module=folder';</script>
    <?php
	exit();
 }
?>