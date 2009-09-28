<?php
/**
* File : details_folder_out.php
*
* Detailed informations on an out archive
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2006
* @license GPL
* @author  Laurent Giovannoni
* @author  Claire Figueras  <dev@maarch.org>
*/

require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();
if(!$core_tools->is_module_loaded("folder"))
{
echo "Folder module missing !<br/>Please install this module.";
exit();
}
$core_tools->test_service('folder_out', 'folder');

require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");
require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_folders_show.php");
require_once($_SESSION['config']['businessapppath']."class".$_SESSION['slash_env']."class_types.php");

require_once($_SESSION['config']['businessapppath']."class".$_SESSION['slash_env']."class_list_show.php");
//require_once($_SESSION['pathtocoreclass']."class_docserver.php");

$func = new functions();
$request= new request;

$s_id = "";
if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
{
	$_GET['id'] = $_REQUEST['id'];
}

if(isset($_GET['id']) && !empty($_GET['id']))
{
	$s_id = addslashes($func->wash($_GET['id'], "num", _THE_ID));
}

if($_GET['origin'] == "welcome" && isset($_SESSION['folder_out_id']) && !empty($_SESSION['folder_out_id']))
{
	$s_id = $_SESSION['folder_out_id'];
}

if(trim($_REQUEST['up_last_name_out'])<>"" && trim($_REQUEST['up_first_name_out'])<>"" && trim($_REQUEST['id'])<>"" && trim($_REQUEST['up_motif'])<>"" && trim($_REQUEST['up_date_out'])<>"" && trim($_REQUEST['up_date_return'])<>"" && trim($_REQUEST['up_return_flag'])<>"")
{

	if($_SESSION['config']['databasetype'] == "SQLSERVER")
	{
		 $pattern = "^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$";
	 }
	 else // MYSQL & POSTGRESQL
	{
		 $pattern = "^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$";
	 }
	$data = array();
	array_push($data, array('column' => "last_name_folder_out", 'value' => $func->protect_string_db($_REQUEST['up_last_name_out'],$_SESSION['config']['databasetype']), 'type' => "string"));
	array_push($data, array('column' => "first_name_folder_out", 'value' => $func->protect_string_db($_REQUEST['up_first_name_out'],$_SESSION['config']['databasetype']), 'type' => "string"));

	if(preg_match($pattern,$_REQUEST['up_date_out'])== false )
		{
			$_SESSION['error'] .= _FILE_OUT_DATE2." "._WRONG_FORMAT.".<br/>";
		}
		else
		{

			array_push($data, array('column' => "put_out_date", 'value' => $func->format_date_db($_REQUEST['up_date_out']), 'type' => "date"));
		}

	if(preg_match($pattern,$_REQUEST['up_date_return'])== false )
		{
			$_SESSION['error'] .= _FOLDER_OUT_RETURN_DATE." "._WRONG_FORMAT.".<br/>";
		}
		else
		{

			array_push($data, array('column' => "return_date", 'value' => $func->format_date_db($_REQUEST['up_date_return']), 'type' => "date"));
		}

	array_push($data, array('column' => "put_out_pattern", 'value' => $func->protect_string_db($_REQUEST['up_motif'],"SQLSERVER"), 'type' => "string"));
	array_push($data, array('column' => "return_flag", 'value' => $func->protect_string_db($_REQUEST['up_return_flag'],"SQLSERVER"), 'type' => "string"));

	$request->update($_SESSION['tablename']['fold_folders_out'], $data,"folder_out_id = ".$_REQUEST['id'], $_SESSION['config']['databasetpe']);
	$data_folder = array();
	$flag_folder_out = $_REQUEST['up_return_flag'];
	echo $_REQUEST['folder_system'];

	array_push($data_folder, array('column' => 'custom_t9', 'value' => $flag_folder_out, 'type' => "string"));
	$request->update($_SESSION['tablename']['fold_folders'],$data_folder,"folders_system_id = ".$_REQUEST['up_system_id'], $_SESSION['config']['databasetpe']);

	if(empty($_SESSION['error']))
	{
		echo "<div class=\"error\">"._FOLDER_OUT_SHEET_UP."</div>";
	}
}
else
{
	if($_REQUEST['flag_up'] == "true")
	{
		echo "<div class=\"error\">"._MISSING_FIELDS."</div><br/>";
	}
}

$connexion = new dbquery();
	$connexion->connect();
//if((empty($_SESSION['error']) || $_SESSION['indexation'] )&& !empty($s_id))
if( !empty($s_id))
{

	$connexion->query("SELECT *  FROM ".$_SESSION['tablename']['fold_folders_out']." where folder_out_id  = '".$s_id."'");
	//$connexion->show();
}
if(!empty($_SESSION['error']))
{
	echo '<div class="error">'.$_SESSION["error"].'</div>';
}
?>
<h1><img src="<?php  echo $_SESSION['config']['img'];?>/desarchivage_b.gif" alt="" /><?php  echo _NUM_FOLDER_OUT; ?><?php  echo $s_id; ?></h1>
<div id="inner_content">

<?php

	/*if(!empty($_SESSION['error']) && ! ($_SESSION['indexation'] ))
	{
	?>
	<div class="error">
			<br />
			<br />
			<br />
			<?php  echo $_SESSION['error']; ?>
			<br />
			<br />
			<br />
	</div>
	<?php
	}*/
//	else
//	{
		if($connexion->nb_result() == 0)
		{
			?>
			<div class="error">
					<br />
					<br />
					<?php  echo _NO_DOCUMENT_CORRESPOND_TO_IDENTIFIER; ?>.
					<br />
					<br />
					<br />
			</div>
			<?php
		}
		else
		{
			$details = $connexion->fetch_object();
			$folder_system_id = $details->folder_system_id;
			$folder_object=new folder();
			$folder_object->load_folder1($folder_system_id,$_SESSION['tablename']['fold_folders']);
			$folder_array = array();
			$folder_array = $folder_object->get_folder_info();
			$matricule =$folder_array['FOLDER_ID'];
			$last_name = $details->last_name;
			$first_name = $details->first_name;
			$last_name_folder_out = $details->last_name_folder_out;
			$first_name_folder_out = $details->first_name_folder_out;
			$put_out_pattern = $details->put_out_pattern;
			$put_out_date = $func->format_date_db($details->put_out_date);
			$return_date = $func->format_date_db($details->return_date);
			$return_flag = $details->return_flag;
			?>
			<div id="details" >
				<form method='post' action='index.php?page=details_folder_out&module=folder'>
					<table width="90%" align="center">
						<tr>
							<td class="heading"  align="left"><?php  echo _MATRICULE; ?> : </td>
							<td width="300" ><input type="text"  readonly="readonly" value="<?php  echo $matricule; ?>" size="30" class="readonly" />
							<input type='hidden' name='id' value="<?php  echo $s_id;?>" />
							<input type='hidden' name='flag_up' value="true" />
							</td>
							<td class="heading"  align="center"><?php  echo _FOLDER_SYSTEM_NUM;?> : </td>
							<td>&nbsp;<input type="text"  readonly="readonly" name="up_system_id" value="<?php  echo $folder_system_id; ?>" size="30" class="readonly" /></td>
						</tr>
					</table>
					<br />
                    <table cellpadding="0" cellspacing="0" border="0" class="listing detail forms">
				<tr>
					<th class="int"><?php  echo _LASTNAME; ?> :</th>
					<td class="detail"><input type="text"  readonly="readonly" value="<?php  echo $last_name ; ?>"  class="detail_box readonly" /></td>
					<td class="void">&nbsp;</td>
					<th class="int"><?php  echo _FIRSTNAME; ?> :</th>
					<td class="detail"><input type="text"  readonly="readonly" value="<?php  echo $first_name; ?>" class="detail_box readonly" /></td>
				</tr>
				<tr class="col">
					<th class="int"><?php  echo _LASTNAME_FOLDER_OUT; ?> :</th>
					<td class="detail"><input type="text"  name="up_last_name_out" value="<?php  echo $last_name_folder_out; ?>"  class="detail_box" /></td>
					<td class="void">&nbsp;</td>
					<th class="int"><?php  echo _FIRSTNAME_FOLDER_OUT; ?> :</th>
					<td class="detail"><input type="text" name="up_first_name_out" value="<?php  echo $first_name_folder_out; ?>"  class="detail_box" /></td>
				</tr>
			</table>

					<br />
					<table width="90%" align="center" border="0">
						<tr>
							<td width="25%" class="heading" align="left"><?php  echo _FOLDER_OUT_MOTIVE;?> : </td>
							<td width="75%">
								<input type='radio' name='up_motif' value='1' <?php  if($put_out_pattern=='1'){echo 'checked="checked"';}?> />
								<?php  echo _MOTIVE1;?><br/>
								<input type='radio' name='up_motif' value='2' <?php  if($put_out_pattern=='2'){echo 'checked="checked"';}?> />
								<?php  echo _MOTIVE2;?><br/>
								<input type='radio' name='up_motif' value='3' <?php  if($put_out_pattern=='3'){echo 'checked="checked"';}?> />
								<?php  echo _MOTIVE3;?><br/>
							</td>
						</tr>
					</table>
					<br/>
               <table cellpadding="0" cellspacing="0" border="0" class="listing detail forms">
				<tr>
					<th class="int"><?php  echo _FILE_OUT_DATE2; ?> :</th>
					<td class="detail"><input type="text"  name="up_date_out" value="<?php  echo $put_out_date ; ?>" class="detail_box" /></td>
					<td class="void">&nbsp;</td>
					<th class="int"><?php  echo _RETURN_DATE; ?> :</th>
					<td class="detail"><input type="text"  name="up_date_return" value="<?php  echo $return_date; ?>" class="detail_box" /></td>
				</tr>
				<tr class="col">
					<th class="int"><?php  echo _RETURNED_FOLDERS; ?> :</th>
					<td class="detail"><input type='radio' name='up_return_flag' value='Y' <?php  if($return_flag=='Y'){echo 'checked="checked"';}?> />
								<?php  echo _YES;?><br/>
								<input type='radio' name='up_return_flag' value='N' <?php  if($return_flag=='N'){echo 'checked="checked"';}?> />
								<?php  echo _NO;?><br/></td>
					<td class="void" colspan="3">&nbsp;</td>

				</tr>
			</table>
					<br />
					<div align="right"><input type='submit' class="button" value='<?php  echo _MODIFY_CARD;?>' />
					</div>
					<br />
				</form>
			</div>
			<?php
		}

	//}
?></div>