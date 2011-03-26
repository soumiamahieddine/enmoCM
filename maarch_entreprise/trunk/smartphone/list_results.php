<?php
require_once ('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_db.php');
require_once ('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_request.php');
require_once ('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_security.php');
require_once ('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_manage_status.php');
require_once ('apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_list_show.php');
require_once ('apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_contacts.php');
include_once ('apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . 'definition_mail_categories.php');
$status_obj = new manage_status();
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$sec = new security();
$status_obj = new manage_status();
$contact = new contacts();
$mode = 'normal';
if (isset ($_REQUEST['mode']) && !empty ($_REQUEST['mode'])) {
    $mode = $core_tools->wash($_REQUEST['mode'], 'alphanum', _MODE);
}
//$_SESSION['collection_id_choice'] = $_SESSION['searching']['coll_id'];
$_SESSION['collection_id_choice'] = 'res_coll';
$view = $sec->retrieve_view_from_coll_id($_SESSION['collection_id_choice']);
$select = array ();
//$select[$_SESSION['searching']['coll_view']]= array();
$select[$view] = array ();
$where_request = $_SESSION['searching']['where_request'];
array_push($select[$view], "res_id", "status", "doc_custom_t1", "doc_custom_t4", "doc_custom_t2", "doc_custom_t3", "doc_custom_t5", "title",  "creation_date");
$status = $status_obj->get_not_searchable_status();
$status_str = '';
for ($i = 0; $i < count($status); $i++) {
    $status_str .= "'" . $status[$i]['ID'] . "',";
}
$status_str = preg_replace('/,$/', '', $status_str);
$where_request .= "  status not in (" . $status_str . ") ";
if (isset ($_SESSION['searching']['comp_query']) && trim($_SESSION['searching']['comp_query']) <> '') {
    $add_security = false;
    $where_clause = $sec->get_where_clause_from_coll_id($_SESSION['collection_id_choice']);
    if (trim($where_request) <> '') {
        $where_request = '(' . $where_request . ') and ((' . $where_clause . ') or (' . $_SESSION['searching']['comp_query'] . '))';
    } else {
        $where_request = '(' . $where_clause . ' or ' . $_SESSION['searching']['comp_query'] . ')';
    }
} else {
    $add_security = true;
}
$where_request = str_replace(" ()", "(1=-1)", $where_request);
$where_request = str_replace("and ()", "", $where_request);
$list = new list_show();
$order = '';
if (isset ($_REQUEST['order']) && !empty ($_REQUEST['order'])) {
    $order = trim($_REQUEST['order']);
}
$field = '';
if (isset ($_REQUEST['order_field']) && !empty ($_REQUEST['order_field'])) {
    $field = trim($_REQUEST['order_field']);
}
$orderstr = $list->define_order($order, $field);
if (($_REQUEST['template'] == 'group_case') && ($core_tools->is_module_loaded('cases'))) {
    unset ($select);
    $select = array ();
    $select[$_SESSION['tablename']['cases']] = array ();
    $select[$view] = array ();
    array_push($select[$_SESSION['tablename']['cases']], "case_id", "case_label", "case_description", "case_typist", "case_creation_date");
    $where = " " . $_SESSION['tablename']['cases'] . ".case_id = " . $view . ".case_id  and ";
    $request = new request();
    $tab = $request->select($select, $where . $where_request, $orderstr, $_SESSION['config']['databasetype'], "default", false, "", "", "", true, false, true);
} else {
    $request = new request();
    $tab = $request->select($select, $where_request, $orderstr, $_SESSION['config']['databasetype'], "default", false, "", "", "", $add_security);
    //$request->show();
}
//$request->show();
//exit();
$_SESSION['error_page'] = '';
//Manage of template list
//###################
//Defines template allowed for this list
$template_list = array ();
array_push($template_list, array (
    "name" => "search_adv",
    "img" => "extend_list.gif",
    "label" => _ACCESS_LIST_EXTEND
));

if (!$_REQUEST['template'])
    $template_to_use = $template_list[0]["name"];
if (isset ($_REQUEST['template']) && empty ($_REQUEST['template']))
    $template_to_use = '';
if ($_REQUEST['template'])
    $template_to_use = $_REQUEST['template'];

//For status icon
$extension_icon = '';
if ($template_to_use <> '')
    $extension_icon = "_big";
//###################

//#########################
//build the tab with right format for list_doc function
//$request->show_array($tab);
if (count($tab) > 0) {
    //Specific View for group_case_template, we don' need to load the standard list_result_mlb
    //#########################
    if (($_REQUEST['template'] == 'group_case') && ($core_tools->is_module_loaded('cases'))) {
        include ("modules" . DIRECTORY_SEPARATOR . "cases" . DIRECTORY_SEPARATOR . 'mlb_list_group_case_addon.php');
    } else {
        for ($i = 0; $i < count($tab); $i++) {
            for ($j = 0; $j < count($tab[$i]); $j++) {
                foreach (array_keys($tab[$i][$j]) as $value) {
                    if ($tab[$i][$j][$value] == 'res_id') {
                        $tab[$i][$j]['res_id'] = $tab[$i][$j]['value'];
                        $tab[$i][$j]["label"] = _GED_NUM;
                        $tab[$i][$j]["size"] = "4";
                        $tab[$i][$j]["label_align"] = "left";
                        $tab[$i][$j]["align"] = "center";
                        $tab[$i][$j]["valign"] = "bottom";
                        $tab[$i][$j]["show"] = true;
                        $tab[$i][$j]["order"] = 'res_id';
                        $_SESSION['mlb_search_current_res_id'] = $tab[$i][$j]['value'];
                    }
                    if ($tab[$i][$j][$value] == "doc_custom_t1") {
                        $tab[$i][$j]["label"] = _IDENTIFIER;
                        $tab[$i][$j]['value'] = $request->show_string($tab[$i][$j]['value']);
                        $tab[$i][$j]["size"] = "10";
                        $tab[$i][$j]["label_align"] = "left";
                        $tab[$i][$j]["align"] = "left";
                        $tab[$i][$j]["valign"] = "bottom";
                        $tab[$i][$j]["show"] = true;
                        $tab[$i][$j]["order"] = "doc_custom_t1";
                    }
                    /*if ($tab[$i][$j][$value] == "doc_custom_t2") {
                        $tab[$i][$j]["label"] = _CONTACT_NAME;
                        $tab[$i][$j]['value'] = $request->show_string($tab[$i][$j]['value']);
                        $tab[$i][$j]["size"] = "15";
                        $tab[$i][$j]["label_align"] = "left";
                        $tab[$i][$j]["align"] = "left";
                        $tab[$i][$j]["valign"] = "bottom";
                        $tab[$i][$j]["show"] = true;
                        $tab[$i][$j]["order"] = "doc_custom_t2";
                    }
                    if ($tab[$i][$j][$value] == "doc_custom_t3") {
                        $tab[$i][$j]["label"] = _COUNTRY;
                        $tab[$i][$j]['value'] = $request->show_string($tab[$i][$j]['value']);
                        $tab[$i][$j]["size"] = "15";
                        $tab[$i][$j]["label_align"] = "left";
                        $tab[$i][$j]["align"] = "left";
                        $tab[$i][$j]["valign"] = "bottom";
                        $tab[$i][$j]["show"] = true;
                        $tab[$i][$j]["order"] = "doc_custom_t3";
                    }*/
                    if ($tab[$i][$j][$value] == "doc_custom_t4") {
                        $tab[$i][$j]["label"] = _CUSTOMER;
                        $tab[$i][$j]['value'] = $request->show_string($tab[$i][$j]['value']);
                        $tab[$i][$j]["size"] = "15";
                        $tab[$i][$j]["label_align"] = "left";
                        $tab[$i][$j]["align"] = "left";
                        $tab[$i][$j]["valign"] = "bottom";
                        $tab[$i][$j]["show"] = true;
                        $tab[$i][$j]["order"] = "doc_custom_t4";
                    }
                    /*if ($tab[$i][$j][$value] == "doc_custom_t5") {
                        $tab[$i][$j]["label"] = _PO_NUMBER;
                        $tab[$i][$j]['value'] = $tab[$i][$j]['value'];
                        $tab[$i][$j]["size"] = "15";
                        $tab[$i][$j]["label_align"] = "left";
                        $tab[$i][$j]["align"] = "left";
                        $tab[$i][$j]["valign"] = "bottom";
                        $tab[$i][$j]["show"] = false;
                        $tab[$i][$j]["order"] = "doc_custom_t5";
                    }*/
                    /*if ($tab[$i][$j][$value] == "title") {
                        $tab[$i][$j]["label"] = _TITLE;
                        $tab[$i][$j]['value'] = $request->show_string($tab[$i][$j]['value']);
                        $tab[$i][$j]["size"] = "15";
                        $tab[$i][$j]["label_align"] = "left";
                        $tab[$i][$j]["align"] = "left";
                        $tab[$i][$j]["valign"] = "bottom";
                        $tab[$i][$j]["show"] = true;
                        $tab[$i][$j]["order"] = "title";
                    }*/
                    /*if($tab[$i][$j][$value]=="creation_date")
                    {
                        $tab[$i][$j]["label"]=_REG_DATE;
                        $tab[$i][$j]["size"]="10";
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="left";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=true;
                        $tab[$i][$j]["value"] = $request->format_date_db($tab[$i][$j]['value'], false);
                        $tab[$i][$j]["order"]="creation_date";
                    }*/
                    /*if ($tab[$i][$j][$value] == "status") {
                        $tab[$i][$j]["label"] = _STATUS;
                        $res_status = $status_obj->get_status_data($tab[$i][$j]['value'], $extension_icon);
                        $tab[$i][$j]['value'] = "<img src = '" . $res_status['IMG_SRC'] . "' alt = '" . $res_status['LABEL'] . "' title = '" . $res_status['LABEL'] . "'>";
                        $tab[$i][$j]["size"] = "5";
                        $tab[$i][$j]["label_align"] = "left";
                        $tab[$i][$j]["align"] = "left";
                        $tab[$i][$j]["valign"] = "bottom";
                        $tab[$i][$j]["show"] = true;
                        $tab[$i][$j]["order"] = "status";
                    }*/
                }
            }
        }
    }
    $found_type = _FOUND_DOC;
    ?>
	<h4><img src="<?php  echo $_SESSION['config']['businessappurl']."static.php?filename=picto_search_b.gif";?>" alt="" /> <?php  echo count($tab)." ".$found_type;?></h4>
		<div id="inner_content">
		<?php
	if (!isset ($_REQUEST['action_form']) || empty ($_REQUEST['action_form'])) {
		$bool_radio_form = false;
		$method = '';
		$action = '';
		$button_label = '';
		$hidden_fields = '';
	} else {
		$bool_radio_form = true;
		$method = 'get';
		$button_label = _VALIDATE;
		$hidden_fields = '<input type="hidden" name="display" value="true" /><input type="hidden" name="page" value="' . $_REQUEST['action_form'] . '" />';
		if (isset ($_REQUEST['modulename']) && !empty ($_REQUEST['modulename'])) {
			//$action = $_SESSION['urltomodules'].$_REQUEST['module'].'/'.$_REQUEST['action_form'].'.php';
			$action = $_SESSION['config']['businessappurl'] . "index.php?display=true&page=" . $_REQUEST['action_form'] . "&module=" . $_REQUEST['modulename'];
			$hidden_fields .= '<input type="hidden" name="module" value="' . $_REQUEST['modulename'] . '" />';
		} else {
			//$action = $_SESSION['config']['businessappurl'].$_REQUEST['action_form'].'.php';
			$action = $_SESSION['config']['businessappurl'] . "index.php?display=true&page=" . $_REQUEST['action_form'];

		}
	}
	$show_close = false;
	if (isset ($_REQUEST['nodetails'])) {
		$show_details = false;
	} else {
		$show_details = true;
	}
    $export = true;
    $save_mode = true;
    $special = false;
    $name = 'list_results&dir=smartphone';
    $use_template = true;
	$comp_link = '&mode=' . $mode;
	$template_to_use = 'search_smartphone';
	$list->list_doc($tab, $i, '', 'res_id', $name, 'res_id', 'details&dir=smartphone', true, $bool_radio_form, $method, $action, $button_label, $show_details, true, $special, $export, $show_close, false, true, false, '', '', false, '', '', 'listing spec', $comp_link, false, false, array (), $hidden_fields, '{}', false, '', true, array (), $use_template, $template_list, $template_to_use);
	?></div><?php
} else {
	if ($mode == 'normal') {
		$_SESSION['error_search'] = '<p class="error"><img src="' . $_SESSION['config']['businessappurl'] . 'static.php?filename=noresult.gif" /><br />' . _NO_RESULTS . '</p><br/><br/><div align="center"><strong><a href="' . $_SESSION['config']['businessappurl'] . 'index.php?page=search_adv_invoices&dir=indexing_searching&init_search">' . _MAKE_NEW_SEARCH . '</a></strong></div>';
		?>
		<script type="text/javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'].'index.php?page=search_adv_error_invoices&dir=indexing_searching';?>';</script>
		<?php
	} else {
		$_SESSION['error_search'] = '<p class="error"><img src="' . $_SESSION['config']['businessappurl'] . 'static.php?filename=noresult.gif" /><br />' . _NO_RESULTS . '</p><br/><br/><div align="center"><strong><a href="' . $_SESSION['config']['businessappurl'] . 'index.php?display=true&dir=indexing_searching&page=search_adv_invoices&mode=' . $mode . '&init_search">' . _MAKE_NEW_SEARCH . '</a></strong></div>';
		?>
		<script  type="text/javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=search_adv_error_invoices&mode='.$mode;?>';</script>
		<?php
	}
}
?>
    <script type="text/javascript">
    var form_txt='<form name="frm_save_query" id="frm_save_query" action="#" method="post" class="forms addforms" onsubmit="send_request(this.id);" ><h2><?php echo _SAVE_QUERY_TITLE;?></h2><p><label for="query_name"><?php echo _QUERY_NAME;?></label><input type="text" name="query_name" id="query_name" value=""/></p><p class="buttons"><input type="submit" name="submit" id="submit" value="<?php echo _VALIDATE;?>" class="button"/> <input type="button" name="cancel" id="cancel" value="<?php echo _CANCEL;?>" class="button" onclick="destroyModal();"/></p></form>';
    function send_request(form_id)
    {
        var form = $(form_id);
        if(form)
        {
            var q_name = form.query_name.value;
            $('modal').innerHTML = '<img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=loading.gif" />';

            new Ajax.Request('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&dir=indexing_searching&page=manage_query',
            {
                method:'post',
                parameters: {name: q_name,
                            action : "creation"},
                onSuccess: function(answer){
                    eval("response = "+answer.responseText)
                    if(response.status == 0)
                    {
                        $('modal').innerHTML ='<h2><?php echo _QUERY_SAVED;?></h2><br/><input type="button" name="close" value="<?php echo _CLOSE_WINDOW;?>" onclick="destroyModal();" class="button" />';
                    }
                    else if(response.status == 2)
                    {
                        $('modal').innerHTML = '<div class="error"><?php echo _SQL_ERROR;?></div>'+form_txt;
                        form.query_name.value = this.name;
                    }
                    else if(response.status == 3)
                    {
                        $('modal').innerHTML = '<div class="error"><?php echo _QUERY_NAME.' '._IS_EMPTY;?></div>'+form_txt;
                        form.query_name.value = this.name;
                    }
                    else
                    {
                        $('modal').innerHTML = '<div class="error"><?php echo _SERVER_ERROR;?></div>'+form_txt;
                        form.query_name.value = this.name;
                    }
                },
                onFailure: function(){
                    $('modal').innerHTML = '<div class="error"><?php echo _SERVER_ERROR;?></div>'+form_txt;
                    form.query_name.value = this.name;
                   }
            });
        }
    }
    </script>
    <br>
	<input class="button_search_adv_text" name="imageField" type="button" value="Nouvelle recherche" onclick="window.document.location.href='index.php?page=search';" />
	<br>
