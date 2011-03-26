<?php
require_once ('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_db.php');
require_once ('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_request.php');
require_once ('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_security.php');
require_once ('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_manage_status.php');
require_once ('apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_indexing_searching_app.php');
require_once ('apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_types.php');
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$_SESSION['search']['plain_text'] = '';
$type = new types();
$func = new functions();
$conn = new dbquery();
$conn->connect();
$search_obj = new indexing_searching_app();
$status_obj = new manage_status();
$sec = new security();
$_SESSION['indexation'] = false;
$mode = 'normal';
if (isset ($_REQUEST['mode']) && !empty ($_REQUEST['mode'])) {
    $mode = $func->wash($_REQUEST['mode'], 'alphanum', _MODE);
}
$conn->query("select user_id, firstname, lastname, status from " . $_SESSION['tablename']['users'] . " where enabled = 'Y' and status <> 'DEL' order by lastname asc");
$users_list = array ();
while ($res = $conn->fetch_object()) {
    array_push($users_list, array (
        'ID' => $conn->show_string($res->user_id),
        'NOM' => $conn->show_string($res->lastname),
        'PRENOM' => $conn->show_string($res->firstname),
        'STATUT' => $res->status
    ));
}
$coll_id = 'res_coll';
$view = $sec->retrieve_view_from_coll_id($coll_id);
$where = $sec->get_where_clause_from_coll_id($coll_id);
if (!empty ($where)) {
    $where = ' where ' . $where;
}
//Check if web brower is ie_6 or not
if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"])) {
    $browser_ie = 'true';
    $class_for_form = 'form';
    $hr = '<tr><td colspan="2"><hr></td></tr>';
    $size = '';
}
elseif (preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]) && !preg_match('/opera/i', $HTTP_USER_AGENT)) {
    $browser_ie = 'true';
    $class_for_form = 'forms';
    $hr = '';
    $size = '';
} else {
    $browser_ie = 'false';
    $class_for_form = 'forms';
    $hr = '';
    $size = '';
    // $size = 'style="width:40px;"';
}
// building of the parameters array used to pre-load the category list and the search elements
$param = array ();
// Indexes specific to doctype
$indexes = $type->get_all_indexes($coll_id);
for ($i = 0; $i < count($indexes); $i++) {
    $field = $indexes[$i]['column'];
    if (preg_match('/^custom_/', $field)) {
        $field = 'doc_' . $field;
    }
    if ($indexes[$i]['type_field'] == 'select') {
        $arr_tmp = array ();
        array_push($arr_tmp, array (
            'VALUE' => '',
            'LABEL' => _CHOOSE . '...'
        ));
        for ($j = 0; $j < count($indexes[$i]['values']); $j++) {
            array_push($arr_tmp, array (
                'VALUE' => $indexes[$i]['values'][$j]['id'],
                'LABEL' => $indexes[$i]['values'][$j]['label']
            ));
        }
        $arr_tmp2 = array (
            'label' => $indexes[$i]['label'],
            'type' => 'select_simple',
            'param' => array (
                'field_label' => $indexes[$i]['label'],
                'default_label' => '',
                'options' => $arr_tmp
            )
        );
    }
    elseif ($indexes[$i]['type'] == 'date') {
        $arr_tmp2 = array (
            'label' => $indexes[$i]['label'],
            'type' => 'date_range',
            'param' => array (
                'field_label' => $indexes[$i]['label'],
                'id1' => $field . '_from',
                'id2' => $field . '_to'
            )
        );
    } else
        if ($indexes[$i]['type'] == 'string') {
            $arr_tmp2 = array (
                'label' => $indexes[$i]['label'],
                'type' => 'input_text',
                'param' => array (
                    'field_label' => $indexes[$i]['label'],
                    'other' => $size
                )
            );
        } else // integer or float
        {
            $arr_tmp2 = array (
                'label' => $indexes[$i]['label'],
                'type' => 'num_range',
                'param' => array (
                    'field_label' => $indexes[$i]['label'],
                    'id1' => $field . '_min',
                    'id2' => $field . '_max'
                )
            );
        }
    $param[$field] = $arr_tmp2;
}
//Loaded date
$arr_tmp2 = array (
    'label' => _REG_DATE,
    'type' => 'date_range',
    'param' => array (
        'field_label' => _REG_DATE,
        'id1' => 'creation_date_from',
        'id2' => 'creation_date_to'
    )
);
$param['creation_date'] = $arr_tmp2;
//Document date
$arr_tmp2 = array (
    'label' => _DOC_DATE,
    'type' => 'date_range',
    'param' => array (
        'field_label' => _DOC_DATE,
        'id1' => 'doc_date_from',
        'id2' => 'doc_date_to'
    )
);
$param['doc_date'] = $arr_tmp2;
//status
$status = $status_obj->get_searchable_status();
$arr_tmp = array ();
for ($i = 0; $i < count($status); $i++) {
    array_push($arr_tmp, array (
        'VALUE' => $status[$i]['ID'],
        'LABEL' => $status[$i]['LABEL']
    ));
}
// Sorts the $param['status'] array
function cmp_status($a, $b) {
    return strcmp(strtolower($a["LABEL"]), strtolower($b["LABEL"]));
}
usort($arr_tmp, "cmp_status");
$arr_tmp2 = array (
    'label' => _STATUS_PLUR,
    'type' => 'select_multiple',
    'param' => array (
        'field_label' => _STATUS,
        'label_title' => _CHOOSE_STATUS_SEARCH_TITLE,
        'id' => 'status',
        'options' => $arr_tmp
    )
);
$param['status'] = $arr_tmp2;
// Sorts the param array
function cmp($a, $b) {
    return strcmp(strtolower($a["label"]), strtolower($b["label"]));
}
uasort($param, "cmp");
$tab = $search_obj->send_criteria_data($param);
// criteria list options
$src_tab = $tab[0];
$core_tools->load_js();
?>
<script type="text/javascript" src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=search_adv.js" ></script>
<script type="text/javascript">
	<!--
	var valeurs = { <?php echo $tab[1];?>};
	var loaded_query = <?php
	if (isset ($_SESSION['current_search_query']) && !empty ($_SESSION['current_search_query'])) {
		echo $_SESSION['current_search_query'];
	} else {
		echo '{}';
	}
	?>;
	function del_query_confirm()
	{
		if(confirm('<?php echo _REALLY_DELETE.' '._THIS_SEARCH.'?';?>')) {
			del_query_db($('query').options[$('query').selectedIndex], 'select_criteria', 'frmsearch2', '<?php echo _SQL_ERROR;?>', '<?php echo _SERVER_ERROR;?>', '<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=manage_query';?>');
			return false;
		}
	}
	-->
</script>
<h4>
	<img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_search_b.gif" alt="" />
	Rechercher une archive
</h4>
<form name="frmsearch2" method="get" action="index.php?display=true&page=search_result" id="frmsearch2" class="form">
	<input type="hidden" name="dir" value="indexing_searching" />
    <input type="hidden" name="page" value="search_result" />
	<input type="hidden" name="mode" value="<?php echo $mode;?>" />
	<table align="left" border="0" width="100%">
		<tr>
			<td>
				<div class="block">
					<table border = "0" width="100%">
						<tr>
							<td>
								<label for="numged" class="bold">N&deg; de l'archive :</label>
							</td>
							<td>
								<input type="text" name="numged" id="numged" />
								<input type="hidden" name="meta[]" value="numged#numged#input_text" />
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="2"><hr/></td>
		</tr>
		<tr>
			<td>
				<div class="block">
					<table border = "0" width="100%">
						<tr>
							<td>
								<label class="bold">Crit&egrave;res :</label>
							</td>
							<td align='right'>
								<select name="select_criteria" id="select_criteria" style="display:inline;" onchange="add_criteria(this.options[this.selectedIndex].id, 'frmsearch2', <?php echo $browser_ie;?>, '<?php echo _ERROR_IE_SEARCH;?>');">
									<?php echo $src_tab; ?>
								</select>
							</td>
						</tr>
					 </table>
				 </div>
				 <script type="text/javascript">
					load_query(valeurs, loaded_query, 'frmsearch2', '<?php echo $browser_ie;?>, <?php echo _ERROR_IE_SEARCH;?>');
					<?php
					if (isset ($_REQUEST['init_search'])) {
						?>clear_search_form('frmsearch2','select_criteria');clear_q_list(); <?php
					}
					?>
				</script>
			</td>
		</tr>
		 <tr>
			<td>
				<br>
				<p align="right">
					<input class="button_search_adv_text" name="imageField" type="button" value="<?php echo _SEARCH;?>" onclick="valid_search_form('frmsearch2');this.form.submit();" />
				</p>
			 </td>
		 </tr>
		 <div class="block_end">&nbsp;</div>
	</table>
</form>
