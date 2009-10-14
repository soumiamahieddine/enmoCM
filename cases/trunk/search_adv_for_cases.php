<?php
/*
*    Copyright 2008,2009 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  Advanced search form from Cases
*
* @file search_adv.php
* @author Claire Figueras <dev@maarch.org>
* @author Loïc Vinet <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/

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
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
$core_tools->test_service('adv_search_mlb', 'apps');
$type = new types();
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=search_adv&dir=indexing_searching';
$page_label = _SEARCH_ADV_SHORT;
$page_id = "search_adv_mlb";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/

$func = new functions();
$conn = new dbquery();
$conn->connect();
$search_obj = new indexing_searching_app();
$status_obj = new manage_status();
$sec = new security();
// load saved queries for the current user in an array
$conn->query("select query_id, query_name from ".$_SESSION['tablename']['saved_queries']." where user_id = '".$_SESSION['user']['UserId']."' order by query_name");
$queries = array();
while($res = $conn->fetch_object())
{
	array_push($queries, array('ID'=>$res->query_id, 'LABEL' => $res->query_name));
}

$conn->query("select user_id, firstname, lastname, status from ".$_SESSION['tablename']['users']." where enabled = 'Y' and status <> 'DEL' order by lastname asc");
$users_list = array();
while($res = $conn->fetch_object())
{
	array_push($users_list, array('ID' => $conn->show_string($res->user_id), 'NOM' => $conn->show_string($res->lastname), 'PRENOM' => $conn->show_string($res->firstname), 'STATUT' => $res->status));
}

$coll_id = 'letterbox_coll';
$view = $sec->retrieve_view_from_coll_id($coll_id);
$where = $sec->get_where_clause_from_coll_id($coll_id);
if(!empty($where))
{
	$where = ' where '.$where;
}

//Check if web brower is ie_6 or not
if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"])) {
	$browser_ie = 'true';
    $class_for_form = 'form';
    $hr = '<tr><td colspan="2"><hr></td></tr>';
    $size = '';
} elseif(preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]) && !preg_match('/opera/i', $HTTP_USER_AGENT) )
{
	$browser_ie = 'true';
	$class_for_form = 'forms';
	$hr = '';
	 $size = '';
}
else
{
	$browser_ie = 'false';
	$class_for_form = 'forms';
	$hr = '';
	 $size = '';
   // $size = 'style="width:40px;"';
}

// building of the parameters array used to pre-load the category list and the search elements
$param = array();

// Indexes specific to doctype
$indexes = $type->get_all_indexes($coll_id);
for($i=0;$i<count($indexes);$i++)
{
	$field = $indexes[$i]['column'];
	if(preg_match('/^custom_/', $field))
	{
		$field = 'doc_'.$field;
	}
	if($indexes[$i]['type'] == 'date')
	{
		$arr_tmp2 = array('label' => $indexes[$i]['label'], 'type' => 'date_range', 'param' => array('field_label' => $indexes[$i]['label'], 'id1' => $field.'_from', 'id2' =>$field.'_to'));
	}
	else if($indexes[$i]['type'] == 'string')
	{
		$arr_tmp2 = array('label' => $indexes[$i]['label'], 'type' => 'input_text', 'param' => array('field_label' => $indexes[$i]['label'], 'other' => $size));
	}
	else  // integer or float
	{
		$arr_tmp2 = array('label' => $indexes[$i]['label'], 'type' => 'num_range', 'param' => array('field_label' => $indexes[$i]['label'], 'id1' => $field.'_min', 'id2' =>$field.'_max'));
	}
	$param[$field] = $arr_tmp2;
}

//Coming date
$arr_tmp2 = array('label' => _DATE_START, 'type' => 'date_range', 'param' => array('field_label' => _DATE_START, 'id1' => 'admission_date_from', 'id2' =>'admission_date_to'));
$param['admission_date'] = $arr_tmp2;

//Loaded date
$arr_tmp2 = array('label' => _REG_DATE, 'type' => 'date_range', 'param' => array('field_label' => _REG_DATE, 'id1' => 'creation_date_from', 'id2' =>'creation_date_to'));
$param['creation_date'] = $arr_tmp2;

//Closing date
$arr_tmp2 = array('label' => _PROCESS_DATE, 'type' => 'date_range', 'param' => array('field_label' => _PROCESS_DATE, 'id1' => 'closing_date_from', 'id2' =>'closing_date_to'));
$param['closing_date'] = $arr_tmp2;

//Document date
$arr_tmp2 = array('label' => _DOC_DATE, 'type' => 'date_range', 'param' => array('field_label' => _DOC_DATE, 'id1' => 'doc_date_from', 'id2' =>'doc_date_to'));
$param['doc_date'] = $arr_tmp2;

//Process limit date
$arr_tmp2 = array('label' => _LIMIT_DATE_PROCESS, 'type' => 'date_range', 'param' => array('field_label' => _LIMIT_DATE_PROCESS, 'id1' => 'process_limit_date_from', 'id2' =>'process_limit_date_to'));
$param['process_limit_date'] = $arr_tmp2;

//destinataire
$arr_tmp = array();
for($i=0; $i < count($users_list); $i++)
{
	array_push($arr_tmp, array('VALUE' => $users_list[$i]['ID'], 'LABEL' => $users_list[$i]['NOM']." ".$users_list[$i]['PRENOM']));
}
$arr_tmp2 = array('label' => _PROCESS_RECEIPT, 'type' => 'select_multiple', 'param' => array('field_label' => _PROCESS_RECEIPT, 'label_title' => _CHOOSE_RECIPIENT_SEARCH_TITLE,
'id' => 'destinataire','options' => $arr_tmp));
$param['destinataire'] = $arr_tmp2;

//mail_natures
$arr_tmp = array();
foreach(array_keys($_SESSION['mail_natures']) as $nature)
{
	array_push($arr_tmp, array('VALUE' => $nature, 'LABEL' => $_SESSION['mail_natures'][$nature]));
}
$arr_tmp2 = array('label' => _MAIL_NATURE, 'type' => 'select_simple', 'param' => array('field_label' => _MAIL_NATURE,'default_label' => addslashes(_CHOOSE_MAIL_NATURE), 'options' => $arr_tmp));
$param['mail_nature'] = $arr_tmp2;

//priority
$arr_tmp = array();
foreach(array_keys($_SESSION['mail_priorities']) as $priority)
{
	array_push($arr_tmp, array('VALUE' => $priority, 'LABEL' => $_SESSION['mail_priorities'][$priority]));
}
$arr_tmp2 = array('label' => _PRIORITY, 'type' => 'select_simple', 'param' => array('field_label' => _MAIL_PRIORITY,'default_label' => addslashes(_CHOOSE_PRIORITY), 'options' => $arr_tmp));
$param['priority'] = $arr_tmp2;

// dest
$arr_tmp2 = array('label' => _DEST, 'type' => 'input_text', 'param' => array('field_label' => _DEST, 'other' => $size));
$param['dest'] = $arr_tmp2;

//shipper
$arr_tmp2 = array('label' => _SHIPPER, 'type' => 'input_text', 'param' => array('field_label' => _SHIPPER, 'other' => $size));
$param['shipper'] = $arr_tmp2;

if($_SESSION['features']['search_notes'] == 'true')
{
	//annotations
	$arr_tmp2 = array('label' => _NOTES, 'type' => 'textarea', 'param' => array('field_label' => _NOTES, 'other' => $size));
	$param['doc_notes'] = $arr_tmp2;
}

//destination (department)
if($core_tools->is_module_loaded('entities'))
{
	$coll_id = 'letterbox_coll';
	$where = $sec->get_where_clause_from_coll_id($coll_id);
	$table = $sec->retrieve_table_from_coll($coll_id);
	if(empty($table))
	{
		$table = $sec->retrieve_view_from_coll_id($coll_id);
	}
	if(!empty($where))
	{
		$where = ' and '.$where;
	}
	//$conn->query("select distinct r.destination as entity_id, e.entity_label from ".$table." r, ".$_SESSION['tablename']['ent_entities']." e where e.entity_id = r.destination ".$where." group by e.entity_label, r.destination");
	$conn->query("select distinct r.destination, e.short_label from ".$table." r join ".$_SESSION['tablename']['ent_entities']." e on e.entity_id = r.destination ".$where." group by e.short_label, r.destination ");
	$arr_tmp = array();
	while($res = $conn->fetch_object())
	{
		array_push($arr_tmp, array('VALUE' => $res->entity_id, 'LABEL' => $res->entity_label));
	}

	$arr_tmp2 = array('label' => _DESTINATION_SEARCH, 'type' => 'select_multiple', 'param' => array('field_label' => _DESTINATION_SEARCH, 'label_title' => _CHOOSE_ENTITES_SEARCH_TITLE,
'id' => 'services','options' => $arr_tmp));
	$param['destination_mu'] = $arr_tmp2;
}

// Folder
if($core_tools->is_module_loaded('folder'))
{
	$arr_tmp2 = array('label' => _MARKET, 'type' => 'input_text', 'param' => array('field_label' => _MARKET, 'other' => $size));
	$param['market'] = $arr_tmp2;
	$arr_tmp2 = array('label' => _PROJECT, 'type' => 'input_text', 'param' => array('field_label' => _PROJECT, 'other' => $size));
	$param['project'] = $arr_tmp2;
}

//process notes
$arr_tmp2 = array('label' => _PROCESS_NOTES, 'type' => 'textarea', 'param' => array('field_label' => _PROCESS_NOTES, 'other' => $size, 'id' => 'process_notes'));
$param['process_notes'] = $arr_tmp2;

// chrono
$arr_tmp2 = array('label' => _CHRONO_NUMBER, 'type' => 'input_text', 'param' => array('field_label' => _CHRONO_NUMBER, 'other' => $size));
$param['chrono'] = $arr_tmp2;

//status
$status = $status_obj->get_searchable_status();
$arr_tmp = array();
for($i=0; $i < count($status); $i++)
{
	array_push($arr_tmp, array('VALUE' => $status[$i]['ID'], 'LABEL' => $status[$i]['LABEL']));
}
array_push($arr_tmp,  array('VALUE'=> 'REL1', 'LABEL' =>_FIRST_WARNING));
array_push($arr_tmp,  array('VALUE'=> 'REL2', 'LABEL' =>_SECOND_WARNING));
array_push($arr_tmp,  array('VALUE'=> 'LATE', 'LABEL' =>_LATE));

// Sorts the $param['status'] array
function cmp_status($a, $b)
{
   	return strcmp(strtolower($a["LABEL"]), strtolower($b["LABEL"]));
}
usort($arr_tmp, "cmp_status");
$arr_tmp2 = array('label' => _STATUS_PLUR, 'type' => 'select_multiple', 'param' => array('field_label' => _STATUS,'label_title' => _CHOOSE_STATUS_SEARCH_TITLE,'id' => 'status',  'options' => $arr_tmp));
$param['status'] = $arr_tmp2;

//doc_type
$conn->query("select type_id, description  from  ".$_SESSION['tablename']['doctypes']." where enabled = 'Y' order by description asc");
$arr_tmp = array();
while ($res=$conn->fetch_object())
{
	array_push($arr_tmp, array('VALUE' => $res->type_id, 'LABEL' => $conn->show_string($res->description)));
}
$arr_tmp2 = array('label' => _DOCTYPES, 'type' => 'select_multiple', 'param' => array('field_label' => _DOCTYPE,'label_title' => _CHOOSE_DOCTYPES_SEARCH_TITLE, 'id' => 'doctypes', 'options' => $arr_tmp));
$param['doctype'] = $arr_tmp2;

//category
$arr_tmp = array();
array_push($arr_tmp, array('VALUE' => '', 'LABEL' => _CHOOSE_CATEGORY));
foreach(array_keys($_SESSION['mail_categories']) as $cat_id)
{
	array_push($arr_tmp, array('VALUE' => $cat_id, 'LABEL' => $_SESSION['mail_categories'][$cat_id]));
}
$arr_tmp2 = array('label' => _CATEGORY, 'type' => 'select_simple', 'param' => array('field_label' => _CATEGORY,'default_label' => '', 'options' => $arr_tmp));
$param['category'] = $arr_tmp2;//Arbox_id ; for physical_archive
if ($core_tools->is_module_loaded('physical_archive') == true)
{
	//doc_type
	$conn->query("select arbox_id, title  from  ".$_SESSION['tablename']['ar_boxes']." where status <> 'DEL' order by description asc");
	$arr_tmp = array();
	while ($res=$conn->fetch_object())
	{
		array_push($arr_tmp, array('VALUE' => $res->arbox_id, 'LABEL' => $conn->show_string($res->title)));
	}
	$arr_tmp2 = array('label' => _ARBOXES, 'type' => 'select_multiple', 'param' => array('field_label' =>_ARBOXES,'label_title' => _CHOOSE_BOXES_SEARCH_TITLE, 'id' => 'arboxes', 'options' => $arr_tmp));
	$param['arbox_id'] = $arr_tmp2;


	$arr_tmp2 = array('label' => _ARBATCHES, 'type' => 'input_text', 'param' => array('field_label' => _ARBATCHES, 'other' => $size));
	$param['arbatch_id'] = $arr_tmp2;


}

//Answers types
$arr_tmp = array(array('ID' => 'simple_mail','VALUE'=> 'true', 'LABEL' =>_SIMPLE_MAIL),array('ID' => 'AR','VALUE'=> 'true', 'LABEL' =>_REGISTERED_MAIL),array('ID' => 'fax','VALUE'=> 'true', 'LABEL' =>_FAX),array('ID' => 'courriel','VALUE'=> 'true', 'LABEL' =>_MAIL)
,array('ID' => 'direct','VALUE'=> 'true', 'LABEL' =>_DIRECT_CONTACT),array('ID' => 'autre','VALUE'=> 'true', 'LABEL' =>_OTHER),array('ID' => 'norep','VALUE'=> 'true', 'LABEL' =>_NO_ANSWER));
$arr_tmp2 = array('label' => _ANSWER_TYPE, 'type' => 'checkbox', 'param' => array('field_label' => _ANSWER_TYPE, 'checkbox_data' => $arr_tmp));
$param['answer_type'] = $arr_tmp2;

// Sorts the param array
function cmp($a, $b)
{
   	return strcmp(strtolower($a["label"]), strtolower($b["label"]));
}
uasort($param, "cmp");

$tab = $search_obj->send_criteria_data($param);
//$conn->show_array($param);
//$conn->show_array($tab);

// criteria list options
$src_tab = $tab[0];

$string = '';
?>

<script type="text/javascript" src="<?php echo $_SESSION['config']['businessappurl'];?>js/search_adv.js" ></script>
<script type="text/javascript">
<!--
var valeurs = { <?php echo $tab[1];?>};
var loaded_query = <?php if(isset($_SESSION['current_search_query']) && !empty($_SESSION['current_search_query']))
{ echo $_SESSION['current_search_query'];}else{ echo '{}';}?>;

function del_query_confirm()
{
	if(confirm('<?php echo _REALLY_DELETE.' '._THIS_SEARCH.'?';?>'))
	{
		del_query_db($('query').options[$('query').selectedIndex], 'select_criteria', 'frmsearch2', '<?php echo _SQL_ERROR;?>', '<?php echo _SERVER_ERROR;?>', '<?php echo $_SESSION['config']['businessappurl'].'indexing_searching/manage_query.php';?>');
		return false;
	}
}
-->
</script>

<h4><p align="center"><img src="<?php echo $_SESSION['config']['businessappurl'];?>img/picto_search_b.gif" alt="" /> <?php echo _ADV_SEARCH_TITLE; ?></h4></p>
<hr/>
<div id="inner_content">

<?php if (count($queries) > 0)
{?>
<form name="choose_query" id="choose_query" action="#" method="post" >
<div align="center" style="display:block;" id="div_query">

<label for="query"><?php echo _MY_SEARCHES;?> : </label>
<select name="query" id="query" onchange="load_query_db(this.options[this.selectedIndex].value, 'select_criteria', 'frmsearch2', '<?php echo _SQL_ERROR;?>', '<?php echo _SERVER_ERROR;?>', '<?php echo $_SESSION['config']['businessappurl'].'indexing_searching/manage_query.php';?>');return false;" >
	<option id="default_query" value=""><?php echo _CHOOSE_SEARCH;?></option>
	<?php for($i=0; $i< count($queries);$i++)
	{
	?><option value="<?php echo $queries[$i]['ID'];?>" id="query_<?php echo $queries[$i]['ID'];?>"><?php echo $queries[$i]['LABEL'];?></option><?php }?>
</select>

<input name="del_query" id="del_query" value="<?php echo _DELETE_QUERY;?>" type="button"  onclick="del_query_confirm();" class="button" style="display:none" />
</div>
</form>
<?php } ?>
<!--<form name="frmsearch2" method="get" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=search_adv_result&dir=indexing_searching"  id="frmsearch2" class="<?php echo $class_for_form; ?>">-->
<form name="frmsearch2" method="get" action="<?php echo $_SESSION['config']['businessappurl'];?>indexing_searching/search_adv_result.php"  id="frmsearch2" class="<?php echo $class_for_form; ?>">

<input type="hidden" name="specific_case" value="attach_to_case" />



<!-- #########################To search a ressource for this res############################-->
<input type="hidden" name="searched_item" value="<?php echo $_GET['searched_item']; ?>" />
<input type="hidden" name="searched_value" value="<?php echo $_GET['searched_value']; ?>" />
<!-- #############################################################################-->



<table align="center" border="0" width="100%">
    <tr>
    	<td align="left"><a href="#" onclick="clear_search_form('frmsearch2','select_criteria');clear_q_list();"><img src="<?php  echo $_SESSION['config']['businessappurl']."img/reset.gif";?>" alt="<?php echo _CLEAR_SEARCH;?>" /> <?php  echo _CLEAR_SEARCH; ?></a></td>
    	<td  width="75%" align="right" ><span class="bold"><?php echo _SEARCH_COPY_MAIL;?></span>
			<input type="hidden" name="meta[]" value="copies#copies_false,copies_true#radio" />
			<input type="radio" name="copies" id="copies_false" class="check"  value="false" checked="checked" /><?php echo _NO;?>
			<input type="radio" name="copies" id="copies_true" class="check"  value="true"  /><?php echo _YES;?>
        </td>
    </tr>
</table>
<table align="center" border="0" width="100%">
    <tr>
    	<td colspan="2" ><h2><?php echo _LETTER_INFO; ?></h2></td>
    </tr>
    <tr >
    	<td >
			<table border = "0" width="100%">
			<tr>
				<td width="70%"><label for="subject" class="bold" ><?php echo _MAIL_OBJECT;?>:</label>
					<input type="text" name="subject" id="subject" <?php echo $size; ?>  />
					<input type="hidden" name="meta[]" value="subject#subject#input_text" />
				</td>
				<td><em><?php echo _MAIL_OBJECT_HELP; ?></em></td>
			</tr>
			<tr>
				<td width="70%"><label for="fulltext" class="bold" ><?php echo _FULLTEXT;?>:</label>
					<input type="text" name="fulltext" id="fulltext" <?php echo $size; ?>  />
					<input type="hidden" name="meta[]" value="fulltext#fulltext#input_text" />
				</td>
				<td><em><?php echo _FULLTEXT_HELP; ?></em></td>
			</tr>
			<tr>
			<td width="70%"><label for="numged" class="bold"><?php echo _N_GED;?>:</label>
				<input type="text" name="numged" id="numged" <?php echo $size; ?>  />
				<input type="hidden" name="meta[]" value="numged#numged#input_text" />
				</td>
				<td><em><?php echo _N_GED_HELP; ?></em></td>
			</tr>
			<tr>
				<td width="70%"><label for="multifield" class="bold" ><?php echo _MULTI_FIELD;?>:</label>
					<input type="text" name="multifield" id="multifield" <?php echo $size; ?>  />
					<input type="hidden" name="meta[]" value="multifield#multifield#input_text" />
				</td>
				<td><em><?php echo _MULTI_FIELD_HELP; ?></em></td>
			</tr>
			<?php 
			if($core_tools->is_module_loaded("cases") == true)
			{ ?>
				<tr>
					<td width="70%"><label for="numcase" class="bold" ><?php echo _CASE_NUMBER;?>:</label>
						<input type="text" name="numcase" id="numcase" <?php echo $size; ?>  />
						<input type="hidden" name="meta[]" value="numcase#numcase#input_text" />
					</td>
					<td><em><?php echo _CASE_NUMBER_HELP; ?></em></td>
				</tr>
				<?php 
			}	 ?>
			</table>
    	</td>
    	<td>
			<p align="center">
			<input class="button_search_adv" name="imageField" type="button" value="" onclick="valid_search_form('frmsearch2');this.form.submit();"  />
			<input class="button_search_adv_text" name="imageField" type="button" value="<?php echo _SEARCH; ?>" onclick="valid_search_form('frmsearch2');this.form.submit();" /></p>
   		 </td>
    </tr>
    <tr><td colspan="2"><hr/></td></tr>
<tr>
<td  >
 <table border = "0" width="100%">
       <tr>
   	 <td width="70%">
 		<label class="bold"><?php echo _ADD_PARAMETERS; ?>:</label>
 		<select name="select_criteria" id="select_criteria" style="display:inline;" onchange="add_criteria(this.options[this.selectedIndex].id, 'frmsearch2', <?php echo $browser_ie;?>, '<?php echo _ERROR_IE_SEARCH;?>');">
			<?php echo $src_tab; ?>
		</select>
   	 </td>

		<td width="30%"><em><?php echo _ADD_PARAMETERS_HELP; ?></em></td>
		</tr>
 </table>
</td></tr>
</table>

</form>
<br/>
<div align="right">
</div>
 </div>
<script type="text/javascript">
load_query(valeurs, loaded_query, 'frmsearch2', '<?php echo $browser_ie;?>, <?php echo _ERROR_IE_SEARCH;?>');
</script>
