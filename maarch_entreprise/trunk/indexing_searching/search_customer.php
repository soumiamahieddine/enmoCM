<?php
/**
* File : search_customer.php
*
* Advanced search form
*
* @package  Maarch Framework 3.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author Loïc Vinet  <dev@maarch.org>
*/
include('core/init.php');

require_once("core/class/class_functions.php");
require_once("core/class/class_request.php");
require_once("core/class/class_core_tools.php");
require_once("apps/".$_SESSION['businessapps'][0]['appid']."/class".DIRECTORY_SEPARATOR.'class_business_app_tools.php');
$appTools = new business_app_tools();
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$core_tools->test_service('search_customer', 'apps');
$_SESSION['indexation'] = false;
/****************Management of the location bar  ************/
$init = false;
if($_REQUEST['reinit'] == "true")
{
	$init = true;
}
$level = "";
if($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
{
	$level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=search_customer&dir=indexing_searching';
$page_label = _SEARCH_CUSTOMER;
$page_id = "is_search_customer";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
//Definition de la collection
$_SESSION['collection_id_choice'] = $_SESSION['user']['collections'][0] ;
if ($_GET['erase'] == 'true')
{
	$_SESSION['search'] = array();
}
$_SESSION['origin'] = "search_customer";
if($_REQUEST['num_folder'] <> "")
{
	$_REQUEST['num_folder'] = $appTools->control_abo($_REQUEST['num_folder']);
	$_SESSION['search']['chosen_num_folder'] = $_REQUEST['num_folder'];
}
if($_REQUEST['name_folder'] <> "")
{
	$_SESSION['search']['chosen_name_folder'] = $_REQUEST['name_folder'];
}
//$core_tools->show_array($_REQUEST);
?>
<h1><img src="<?php  echo $_SESSION['config']['businessappurl']."img/search_proj_off.gif";?>" alt="" /> <?php  echo _SEARCH_CUSTOMER_TITLE; ?></h1>
<div id="inner_content" align="center">
	<div class="block">
		<table width="100%" border="0">
			<tr>
				<td align="right"><label><?php  echo _PROJECT;?> :</td>
				<td class="indexing_field">
					<input type="text" name="project" id="project" size="50" />
					<div id="show_project" class="autocomplete"></div>
				</td>
				<td align="right"><?php  echo _MARKET;?> :</td>
				<td>
					<input type="text" name="market" id="market" size="50" />
					<div id="show_market" class="autocomplete"></div>
				</td>
				<td>
					<input type="button" value="<?php echo _SEARCH;?>" onclick="javascript:submitForm();" class="button">
				</td>
			</tr>
		</table>
	</div>
	<script language="javascript">
		launch_autocompleter_folders('<?php echo $_SESSION['urltomodules']?>folder/autocomplete_folders.php?mode=project', 'project');
		launch_autocompleter_folders('<?php echo $_SESSION['urltomodules']?>folder/autocomplete_folders.php?mode=market', 'market');
		function submitForm()
		{
			window.frames['show_trees'].location.href='<?php echo $_SESSION['config']['businessappurl'];?>indexing_searching/show_trees.php?project='+window.document.getElementById("project").value+'&market='+window.document.getElementById("market").value;
		}
	</script>
	<div class="clearsearch">
		<br>
		<a href="<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=search_customer&dir=indexing_searching&erase=true"><img src="<?php  echo $_SESSION['config']['businessappurl']."img/reset.gif";?>" alt="" height="15px" width="15px" /><?php  echo _NEW_SEARCH; ?></a>
	</div>
	<!-- Display the layout of search_customer -->
	<table width="100%" height="100%" border="1">
		<tr>
			<td width= "55%" height = "720px">
				<iframe name="show_trees" id="show_trees" width="100%" height="720" frameborder="0" scrolling="auto" src="<?php  echo $_SESSION['config']['businessappurl']."indexing_searching/show_trees.php?num_folder=".$_REQUEST['num_folder']."&name_folder=".$_REQUEST['name_folder'];?>"></iframe>
			</td>
			<td>
				<iframe name="view" id="view" width="100%" height="720" frameborder="0" scrolling="no" src="<?php  echo $_SESSION['config']['businessappurl']."indexing_searching/little_details_invoices.php?status=empty";?>"></iframe>
			</td>
		</tr>
	</table>
</div>
