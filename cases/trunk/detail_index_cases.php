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

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_docserver.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
require_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_manage_status.php");
require_once("modules".DIRECTORY_SEPARATOR."cases".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR.'class_modules_tools.php');

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
$sec = new security();
$cases = new cases();
$db = new dbquery();
$status_obj = new manage_status();



if($_POST['update'] == true)
{
	$update_id = $_POST['id'];
	$update_field = array();
	$update_field['case_label'] = $_POST['case_label'];
	$update_field['case_description'] = $_POST['case_description'];

	$cases->update_case($update_id, $update_field);
}


//Before display this page, we need to control if this case can be viewed for the user.
//A case can be viewed only if one ressouce is allowed for this user

$docs_library = $cases->get_res_id($_SESSION['cases']['actual_case_id']);
$case_id =  $_SESSION['cases']['actual_case_id'];
$case_indexes = $cases->get_case_info($case_id);
$ressources_status = $cases->get_ressources_status($case_id);
$ressources_header = array();


if(count($docs_library) ==0)
{
		echo _CANT_SHOW_THIS_CASE;
		exit();
}
else
{

}

?>
<body id="tabricator_frame">
<form method="post" name="update_case" id="update_case" action="#">
<div>


	<table border = "0" width="100%">
		<tr>
			<td width="50%">
				<table width="100%" border ="0">
					<tr>
						<td><p align="center"><img src="<?php echo $_SESSION['urltomodules']."cases/img/case_big.gif"; ?>" width="250px"> </td></p>
					</tr>
					<tr>
						<td>
							<h2 style="color:#1B99C4"><p align="center"><?php echo _NUM_CASE." ".$case_id; ?></p></h2>
							<p style="color:#1B99C4" align="center"><?php echo $db->show_string($case_indexes['case_description']); ?> </p>
						</td>
					</tr>
				</table>
			</td>
			<td>
			<div class="">
			<h2><?php echo _CASES_INDEXES; ?> : </h2>
			</div>

			<table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
			<tr class="col">
				<th align="left" class="picto">
					<img alt="<?php echo _CASE_ID.' : '.$case_indexes['case_id'];?>" src="<?php echo $_SESSION['urltomodules']."cases/img/case_id.gif"; ?>" title="<?php  echo _CASE_ID; ?>" alt="<?php  echo _CASE_ID; ?>"/>
				</th>
				<td align="left" width="200px">
					<?php  echo _CASE_ID; ?> :
				</td>
				<td>
					<input type="text" class="readonly" readonly="readonly" value="<?php  echo $case_indexes['case_id']; ?>" size="40"  />
				</td>
			</tr>

			<tr class="col">
				<th align="left" class="picto">
					<img alt="<?php echo _CASE_LABEL.' : '.$case_indexes['case_label'];?>" src="<?php echo $_SESSION['urltomodules']."cases/img/case_label.gif"; ?>" title="<?php  echo _CASE_LABEL; ?>" alt="<?php  echo _CASE_LABEL; ?>"/>
				</th>
				<td align="left" width="200px">
					<?php  echo _CASE_LABEL; ?> :
				</td>
				<td>
					<?php
					if ($core_tools->test_service('update_case', 'cases',false) == 0)
						echo '<input type="text"  class="readonly" readonly="readonly" value="'.$db->show_string($case_indexes['case_label']).'" size="40"  />';
					else
						echo '<input type="text" name="case_label" id="case_label" class=""  value="'.$db->show_string($case_indexes['case_label']).'" size="40"  />';  ?>
				</td>
			</tr>

			<tr class="col">
				<th align="left" class="picto">
					<img alt="<?php echo _CASE_DESCRIPTION.' : '.$case_indexes['case_description'];?>" src="<?php echo $_SESSION['urltomodules']."cases/img/case_description.gif"; ?>" title="<?php  echo _CASE_DESCRIPTION; ?>" alt="<?php  echo _CASE_DESCRIPTION; ?>"/>
				</th>
				<td align="left" width="200px">
					<?php  echo _CASE_DESCRIPTION; ?> :
				</td>
				<td>
				<?php
				if ($core_tools->test_service('update_case', 'cases',false) == 0)
					echo '<textarea name="case_description" id="case_description" class ="readonly" readonly="readonly" rows="4" >'.$db->show_string($case_indexes['case_description']).'</textarea>';
				else
					echo '<textarea name="case_description" id="case_description"  rows="4" >'.$db->show_string($case_indexes['case_description']).'</textarea>';
				?>
				</td>
			</tr>

			<tr class="col">
				<th align="left" class="picto">
					<img alt="<?php echo _CASE_TYPIST.' : '.$case_indexes['case_typist'];?>" src="<?php echo $_SESSION['urltomodules']."cases/img/case_author.gif"; ?>" title="<?php  echo _CASE_TYPIST; ?>" alt="<?php  echo _CASE_TYPIST; ?>"/>
				</th>
				<td align="left" width="200px">
					<?php  echo _CASE_TYPIST; ?> :
				</td>
				<td>
					<input type="text" class="readonly" readonly="readonly" value="<?php  echo $case_indexes['case_typist']; ?>" size="40"  />
				</td>
			</tr>


			<tr class="col">
				<th align="left" class="picto">
					<img alt="<?php echo _CASE_CREATION_DATE.' : '.$case_indexes['case_creation_date'];?>" src="<?php echo $_SESSION['urltomodules']."cases/img/case_creation_date.gif"; ?>" title="<?php  echo _CASE_CREATION_DATE; ?>" alt="<?php  echo _CASE_CREATION_DATE; ?>"/>
				</th>
				<td align="left" width="200px">
					<?php  echo _CASE_CREATION_DATE; ?> :
				</td>
				<td>
					<input type="text" class="readonly" readonly="readonly" value="<?php  echo $db->format_date_db($case_indexes['case_creation_date']); ?>" size="40"  />
				</td>
			</tr>


			<tr class="col">
				<th align="left" class="picto">
					<img alt="<?php echo _CASE_LAST_UPDATE_DATE.' : '.$case_indexes['case_last_update_date'];?>" src="<?php echo $_SESSION['urltomodules']."cases/img/case_last_update.gif"; ?>" title="<?php  echo _CASE_LAST_UPDATE_DATE; ?>" alt="<?php  echo _CASE_LAST_UPDATE_DATE; ?>"/>
				</th>
				<td align="left" width="200px">
					<?php  echo _CASE_LAST_UPDATE_DATE; ?> :
				</td>
				<td>
					<input type="text" class="readonly" readonly="readonly" value="<?php  echo $db->format_date_db($case_indexes['case_last_update_date']); ?>" size="40"  />
				</td>
			</tr>


			<tr class="col">
				<th align="left" class="picto">
					<img alt="<?php echo _CASE_CLOSING_DATE.' : '.$case_indexes['case_closing_date'];?>" src="<?php echo $_SESSION['urltomodules']."cases/img/case_closing_date.gif"; ?>" title="<?php  echo _CASE_CLOSING_DATE; ?>" alt="<?php  echo _CASE_CLOSING_DATE; ?>"/>
				</th>
				<td align="left" width="200px">
					<?php  echo _CASE_CLOSING_DATE; ?> :
				</td>
				<td>
					<input type="text" class="readonly" readonly="readonly" value="<?php  echo $db->format_date_db($case_indexes['case_closing_date']); ?>" size="40"  />
				</td>
			</tr>


			 </td>
		</tr>
	</table>
	</table>
	<br/>
	<h2><?php echo _RESSOURCES_REPORTS; ?> : </h2>
	<div class="block" style="height:120px">

		<table border = "0">
			<tr>
			<?php
			foreach($ressources_status as $r)
			{
				$temp =  $status_obj->get_status_data($r['status']);
				echo '<td><img src="'.$temp['IMG_SRC'].'"></td>';
				echo '<td>'.$temp['LABEL'].' : </td>';
				echo '<td><b>'.$r['nb_docs'].'</b></td>';
				echo '<td width="40px;">&nbsp;</td>';
			}
			?>
			</tr>
		</table>
	</div>
	<input type="hidden" name="update" id="update" value="true" />
	<input type="hidden" name="id" id="id" value="<?php echo $case_id; ?>" />
	<p align="center">
	<?php
	if ($core_tools->test_service('update_case', 'cases',false) == 1)
	{ ?>
		<input type="submit" class="button"  value="<?php  echo _MODIFY_DOC;?>" name="submit_index_doc" />
	<?php
	} ?>
	<?php
	if ($core_tools->test_service('join_res_case', 'cases',false) == 1)
	{ ?>
		<input type="button" class="button"  value="<?php  echo _JOIN_RES_ON_THIS_CASE;?>" name="join_res" onclick="window.open('search_adv_for_cases.php?searched_item=case&searched_value=<? echo $case_indexes['case_id'];?>','', 'scrollbars=yes,menubar=no,toolbar=no,resizable=yes,status=no,width=1020,height=710');"/>
	<?php
	}
	if ($core_tools->test_service('close_case', 'cases',false) == 1)
	{ ?>
		<input type="button" class="button"  value="<?php  echo _CLOSE_THIS_CASE;?>" name="join_res" onclick="window.location.href='db_close_case.php?case_id=<? echo $case_indexes['case_id'];?>' "/>
	<?php
	} ?>
	</p>
</form>
</div>

