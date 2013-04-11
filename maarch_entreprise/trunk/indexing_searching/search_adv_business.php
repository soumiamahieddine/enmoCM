<?php
/*
*   Copyright 2008, 2013 Maarch
*
*   This file is part of Maarch Framework.
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  Advanced search form
*
* @file search_adv_business.php
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching
*/

require_once('core/class/class_request.php');
require_once('core/class/class_security.php');
require_once('core/class/class_manage_status.php');
require_once('apps/' . $_SESSION['config']['app_id'] . '/class/class_indexing_searching_app.php');
require_once('apps/' . $_SESSION['config']['app_id'] . '/class/class_types.php');
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();

$_SESSION['search']['plain_text'] = "";
$type = new types();

$func = new functions();
$conn = new dbquery();
$conn->connect();
$search_obj = new indexing_searching_app();
$status_obj = new manage_status();
$sec = new security();
$_SESSION['indexation'] = false;

if (isset($_REQUEST['exclude'])) {
    $_SESSION['excludeId'] = $_REQUEST['exclude'];
}

$mode = 'normal';
if (isset($_REQUEST['mode'])&& !empty($_REQUEST['mode'])) {
    $mode = $func->wash($_REQUEST['mode'], "alphanum", _MODE);
}
if ($mode == 'normal')
{
    $core_tools->test_service('adv_search_business', 'apps');
/****************Management of the location bar  ************/
$init = false;
if (isset($_REQUEST['reinit']) && $_REQUEST['reinit']  == "true") {
    $init = true;
}
$level = "";
if (isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)) {
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=search_adv_business&dir=indexing_searching';
$page_label = _SEARCH_ADV_SHORT;
$page_id = "search_adv_business";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
} elseif ($mode == 'popup' || $mode == 'frame') {
    $core_tools->load_html();
    $core_tools->load_header('', true, false);
    $time = $core_tools->get_session_time_expire();
    ?><body>
    <div id="container">
        <div class="error" id="main_error">
            <?php  echo $_SESSION['error'];?>
        </div>
        <div class="info" id="main_info">
            <?php  echo $_SESSION['info'];?>
        </div><?php
}

// load saved queries for the current user in an array
$conn->query("select query_id, query_name from " 
    . $_SESSION['tablename']['saved_queries']
    . " where user_id = '".$_SESSION['user']['UserId']."' order by query_name");
$queries = array();
while ($res = $conn->fetch_object()) {
    array_push($queries, array('ID'=>$res->query_id, 'LABEL' => $res->query_name));
}

$conn->query("select user_id, firstname, lastname, status from "
    . $_SESSION['tablename']['users'] 
    . " where enabled = 'Y' and status <> 'DEL' order by lastname asc");
$users_list = array();
while ($res = $conn->fetch_object()) {
    array_push($users_list, 
        array('ID' => $conn->show_string($res->user_id), 
        'NOM' => $conn->show_string($res->lastname), 
        'PRENOM' => $conn->show_string($res->firstname), 
        'STATUT' => $res->status)
    );
}

$coll_id = 'business_coll';
$view = $sec->retrieve_view_from_coll_id($coll_id);
$where = $sec->get_where_clause_from_coll_id($coll_id);
if (!empty($where)) {
    $where = ' where '.$where;
}

//Check if web brower is ie_6 or not
if (preg_match("/MSIE 6.0/", $_SERVER["HTTP_USER_AGENT"])) {
    $browser_ie = 'true';
    $class_for_form = 'form';
    $hr = '<tr><td colspan="2"><hr></td></tr>';
    $size = '';
} elseif (preg_match('/msie/i', $_SERVER["HTTP_USER_AGENT"]) && !preg_match('/opera/i', $HTTP_USER_AGENT) ) {
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
$param = array();

// Indexes specific to doctype
$indexes = $type->get_all_indexes($coll_id);

for ($i=0;$i<count($indexes);$i++) {
    $field = $indexes[$i]['column'];
    if (preg_match('/^custom_/', $field)) {
        $field = 'doc_'.$field;
    }
    if ($indexes[$i]['type_field'] == 'select') {
        $arr_tmp = array();
        array_push($arr_tmp, array('VALUE' => '', 'LABEL' => _CHOOSE.'...'));
        for ($j=0; $j<count($indexes[$i]['values']);$j++) {
            array_push($arr_tmp, array('VALUE' => $indexes[$i]['values'][$j]['id'], 'LABEL' => $indexes[$i]['values'][$j]['label']));
        }
        $arr_tmp2 = array('label' => $indexes[$i]['label'], 'type' => 'select_simple', 'param' => array('field_label' => $indexes[$i]['label'],'default_label' => '', 'options' => $arr_tmp));
    } elseif ($indexes[$i]['type'] == 'date') {
        $arr_tmp2 = array('label' => $indexes[$i]['label'], 'type' => 'date_range', 'param' => array('field_label' => $indexes[$i]['label'], 'id1' => $field.'_from', 'id2' =>$field.'_to'));
    } else if ($indexes[$i]['type'] == 'string') {
        $arr_tmp2 = array('label' => $indexes[$i]['label'], 'type' => 'input_text', 'param' => array('field_label' => $indexes[$i]['label'], 'other' => $size));
    } else {
        //integer or float
        $arr_tmp2 = array('label' => $indexes[$i]['label'], 'type' => 'num_range', 'param' => array('field_label' => $indexes[$i]['label'], 'id1' => $field.'_min', 'id2' =>$field.'_max'));
    }
    $param[$field] = $arr_tmp2;
}

//Loaded date
$arr_tmp2 = array('label' => _REG_DATE, 'type' => 'date_range', 'param' => array('field_label' => _REG_DATE, 'id1' => 'creation_date_from', 'id2' =>'creation_date_to'));
$param['creation_date'] = $arr_tmp2;

//Closing date
$arr_tmp2 = array('label' => _CLOSING_DATE, 'type' => 'date_range', 'param' => array('field_label' => _CLOSING_DATE, 'id1' => 'closing_date_from', 'id2' =>'closing_date_to'));
$param['closing_date'] = $arr_tmp2;

//Document date
$arr_tmp2 = array('label' => _DOC_DATE, 'type' => 'date_range', 'param' => array('field_label' => _DOC_DATE, 'id1' => 'doc_date_from', 'id2' =>'doc_date_to'));
$param['doc_date'] = $arr_tmp2;

//Process limit date
$arr_tmp2 = array('label' => _LIMIT_DATE_PROCESS, 'type' => 'date_range', 'param' => array('field_label' => _LIMIT_DATE_PROCESS, 'id1' => 'process_limit_date_from', 'id2' =>'process_limit_date_to'));
$param['process_limit_date'] = $arr_tmp2;

//destinataire
$arr_tmp = array();
for ($i=0; $i < count($users_list); $i++) {
    array_push($arr_tmp, array('VALUE' => $users_list[$i]['ID'], 'LABEL' => $users_list[$i]['NOM']." ".$users_list[$i]['PRENOM']));
}
$arr_tmp2 = array('label' => _PROCESS_RECEIPT, 'type' => 'select_multiple', 'param' => array('field_label' => _PROCESS_RECEIPT, 'label_title' => _CHOOSE_RECIPIENT_SEARCH_TITLE,
'id' => 'destinataire','options' => $arr_tmp));
$param['destinataire'] = $arr_tmp2;

if ($_SESSION['features']['search_notes'] == 'true') {
    //annotations
    $arr_tmp2 = array('label' => _NOTES, 'type' => 'textarea', 'param' => array('field_label' => _NOTES, 'other' => $size));
    $param['doc_notes'] = $arr_tmp2;
}

//tags 
if ($core_tools->is_module_loaded('tags')) {
    $arr_tmptag = array();
    require_once 'modules/tags/class/TagControler.php' ;
    require_once 'modules/tags/tags_tables_definition.php';
    $tag = new tag_controler;
    $tag_return_value = $tag -> get_all_tags($coll_id);
    if ($tag_return_value) {
        foreach($tag_return_value as $tagelem) {
            array_push($arr_tmptag, array('VALUE' => $tagelem['tag_label'], 'LABEL' => $tagelem['tag_label']));
        }
    } else {
        array_push($arr_tmptag, array('VALUE' => '', 'LABEL' => _TAGNONE));
    }
    $param['tag_mu'] = array('label' => _TAG_SEARCH, 'type' => 'select_multiple', 'param' => array('field_label' => _TAG_SEARCH, 'label_title' => _CHOOSE_TAG,
    'id' => 'tags','options' => $arr_tmptag));
}

//destination (department)
if ($core_tools->is_module_loaded('entities')) {
    $where = $sec->get_where_clause_from_coll_id($coll_id);
    $table = $sec->retrieve_view_from_coll_id($coll_id);
    if (empty($table)) {
        $table = $sec->retrieve_table_from_coll($coll_id);
    }
    if (!empty($where)) {
        $where = ' where '.$where;
    }
    //$conn->query("select distinct r.destination from ".$table." r join ".$_SESSION['tablename']['ent_entities']." e on e.entity_id = r.destination ".$where." group by r.destination ");
    $conn->query("select distinct r.destination, e.short_label from ".$table." r join "
        . $_SESSION['tablename']['ent_entities']." e on e.entity_id = r.destination ".$where." group by e.short_label, r.destination order by e.short_label");
    //$conn->show();
    $arr_tmp = array();
    while ($res = $conn->fetch_object()) {
        array_push($arr_tmp, array('VALUE' => $res->destination, 'LABEL' => $res->short_label));
    }
    $param['destination_mu'] = array('label' => _DESTINATION_SEARCH, 'type' => 'select_multiple', 'param' => array('field_label' => _DESTINATION_SEARCH, 'label_title' => _CHOOSE_ENTITES_SEARCH_TITLE,
        'id' => 'services','options' => $arr_tmp));
}

// Folder
if ($core_tools->is_module_loaded('folder')) {
    $arr_tmp2 = array('label' => _FOLDER, 'type' => 'input_text', 'param' => array('field_label' => _FOLDER, 'other' => $size));
    $param['market'] = $arr_tmp2;
}

//status
$status = $status_obj->get_searchable_status();
$arr_tmp = array();
for ($i=0; $i < count($status); $i++) {
    array_push($arr_tmp, array('VALUE' => $status[$i]['ID'], 'LABEL' => $status[$i]['LABEL']));
}
array_push($arr_tmp,  array('VALUE'=> 'REL1', 'LABEL' =>_FIRST_WARNING));
array_push($arr_tmp,  array('VALUE'=> 'REL2', 'LABEL' =>_SECOND_WARNING));
array_push($arr_tmp,  array('VALUE'=> 'LATE', 'LABEL' =>_LATE));

// Sorts the $param['status'] array
function cmp_status($a, $b) {
    return strcmp(strtolower($a["LABEL"]), strtolower($b["LABEL"]));
}
usort($arr_tmp, "cmp_status");
$arr_tmp2 = array('label' => _STATUS_PLUR, 'type' => 'select_multiple', 'param' => array('field_label' => _STATUS,'label_title' => _CHOOSE_STATUS_SEARCH_TITLE,'id' => 'status',  'options' => $arr_tmp));
$param['status'] = $arr_tmp2;

//doc_type
$conn->query("select type_id, description from  " 
    . $_SESSION['tablename']['doctypes'] . " where enabled = 'Y' and coll_id = '" 
    . $coll_id . "' order by description asc"
);
$arr_tmp = array();
while ($res=$conn->fetch_object()) {
    array_push($arr_tmp, array('VALUE' => $res->type_id, 'LABEL' => $conn->show_string($res->description)));
}
$arr_tmp2 = array('label' => _DOCTYPES, 'type' => 'select_multiple', 'param' => array('field_label' => _DOCTYPE,'label_title' => _CHOOSE_DOCTYPES_SEARCH_TITLE, 'id' => 'doctypes', 'options' => $arr_tmp));
$param['doctype'] = $arr_tmp2;

//currency
$arr_tmp2 = array('label' => _CURRENCY, 'type' => 'input_text', 'param' => array('field_label' => _CURRENCY, 'other' => $size));
$param['currency'] = $arr_tmp2;

//net_sum
$arr_tmp2 = array('label' => _NET_SUM, 'type' => 'input_text', 'param' => array('field_label' => _NET_SUM, 'other' => $size));
$param['net_sum'] = $arr_tmp2;

//tax_sum
$arr_tmp2 = array('label' => _TAX_SUM, 'type' => 'input_text', 'param' => array('field_label' => _TAX_SUM, 'other' => $size));
$param['tax_sum'] = $arr_tmp2;

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

$core_tools->load_js();
?>
<?php // echo $_SESSION['current_search_query'];?>
<script type="text/javascript" src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=search_adv.js" ></script>
<script type="text/javascript">
<!--
var valeurs = { <?php echo $tab[1];?>};
var loaded_query = <?php if (isset($_SESSION['current_search_query']) && !empty($_SESSION['current_search_query']))
{ echo $_SESSION['current_search_query'];}else{ echo '{}';}?>;

function del_query_confirm()
{
    if (confirm('<?php echo _REALLY_DELETE.' '._THIS_SEARCH.'?';?>')) {
        del_query_db($('query').options[$('query').selectedIndex], 'select_criteria', 'frmsearch2', '<?php echo _SQL_ERROR;?>', '<?php echo _SERVER_ERROR;?>', '<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=manage_query';?>');
        return false;
    }
}
-->
</script>

<h1><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_search_b.gif" alt="" /> <?php echo _ADV_SEARCH_BUSINESS; ?></h1>
<div id="inner_content">

<?php 
if (count($queries) > 0) {
        ?>
<form name="choose_query" id="choose_query" action="#" method="post" >
<div align="center" style="display:block;" id="div_query">

<label for="query"><?php echo _MY_SEARCHES;?> : </label>
<select name="query" id="query" onchange="load_query_db(this.options[this.selectedIndex].value, 'select_criteria', 'frmsearch2', '<?php echo _SQL_ERROR;?>', '<?php echo _SERVER_ERROR;?>', '<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=manage_query';?>');return false;" >
    <option id="default_query" value=""><?php echo _CHOOSE_SEARCH;?></option>
    <?php 
    for ($i=0; $i< count($queries);$i++) {
        ?><option value="<?php echo $queries[$i]['ID'];?>" id="query_<?php echo $queries[$i]['ID'];?>"><?php echo $queries[$i]['LABEL'];?></option>
        <?php 
    }
    ?>
</select>
<input name="del_query" id="del_query" value="<?php echo _DELETE_QUERY;?>" type="button"  onclick="del_query_confirm();" class="button" style="display:none" />
</div>
</form>
<?php 
} 
?>
<form name="frmsearch2" method="get" action="<?php if ($mode == 'normal') {echo $_SESSION['config']['businessappurl'].'index.php'; } elseif ($mode == 'frame' || $mode == 'popup') { echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=search_adv_result_business';}?>"  id="frmsearch2" class="<?php echo $class_for_form; ?>">
<input type="hidden" name="dir" value="indexing_searching" />
    <input type="hidden" name="page" value="search_adv_result_business" />
<input type="hidden" name="mode" value="<?php echo $mode;?>" />
<?php if ($mode == 'frame' || $mode == 'popup') {?>
    <input type="hidden" name="display" value="true" />
    <input type="hidden" name="action_form" value="<?php echo $_REQUEST['action_form'];?>" />
    <input type="hidden" name="modulename" value="<?php echo $_REQUEST['modulename'];?>" />
<?php
}
if (isset($_REQUEST['nodetails'])) {
    ?>
    <input type="hidden" name="nodetails" value="true" />
    <?php
}
?>
<table align="center" border="0" width="100%">
    <tr>
        <td><a href="#" onclick="clear_search_form('frmsearch2','select_criteria');clear_q_list();changeCategory('purchase');"><img src="<?php  echo $_SESSION['config']['businessappurl']."static.php?filename=reset.gif";?>" alt="<?php echo _CLEAR_SEARCH;?>" /> <?php  echo _CLEAR_SEARCH; ?></a></td>
        <td align="right">
            <input class="button_search_adv" name="imageField" type="submit" value="" onclick="valid_search_form('frmsearch2');this.form.submit();" /><br/>
            <input class="button_search_adv_text" name="imageField" type="button" value="<?php echo _SEARCH; ?>" onclick="valid_search_form('frmsearch2');this.form.submit();" />
        </td>
    </tr>
</table>

<table align="center" border="0" width="100%">
<?php
            if ($core_tools->is_module_loaded("basket") == true) { ?>
             <tr>
                <td colspan="2" ><h2><?php echo _SEARCH_SCOPE; ?></h2></td>
            </tr>
            <tr>
                <td>
                    <div class="block">
                    <table border="0" width="100%">
                        <tr>
                            <td style="width:30px;align:center;">
                                <img src="<?php 
                                        echo $_SESSION['config']['businessappurl'] ;
                                        ?>static.php?filename=manage_baskets_off.gif&module=basket" alt="<?php 
                                        echo  _BASKET;
                                    ?>"/>
                            </td>
                            <td width="70%">
                                <label for="baskets" class="bold" ><?php echo _SPREAD_SEARCH_TO_BASKETS;?>:</label>
                                <input type="hidden" name="meta[]" value="baskets_clause#baskets_clause#select_simple" />
                                <select name="baskets_clause" id="baskets_clause">
                                    <option id="true" value="true"><?php echo _ALL_BASKETS;?></option>
                                    <option id="false" value="false"><?php echo _NO;?></option>
                                    <?php 
                                    if ($_REQUEST['mode'] != 'popup') {
                                        for ($i=0; $i< count($_SESSION['user']['baskets']);$i++) {
                                             if (
                                                $_SESSION['user']['baskets'][$i]['coll_id'] == $coll_id 
                                                && $_SESSION['user']['baskets'][$i]['is_folder_basket'] == 'N'
                                                && $_SESSION['user']['baskets'][$i]['id'] <> 'BusinessQualify'
                                                && $_SESSION['user']['baskets'][$i]['id'] <> 'BusinessQualifyRet'
                                                && $_SESSION['user']['baskets'][$i]['id'] <> 'BusinessIndexation'
                                            ) {
                                                ?><option id="<?php 
                                                    echo $_SESSION['user']['baskets'][$i]['id'];
                                                    ?>" value="<?php 
                                                    echo $_SESSION['user']['baskets'][$i]['id'];
                                                    ?>" ><?php 
                                                    echo $_SESSION['user']['baskets'][$i]['desc'];
                                                ?></option>
                                                <?php
                                            }
                                        }
                                    } ?>
                                </select>
                            </td>
                            <td><em><?php echo _SEARCH_SCOPE_HELP; ?></em></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="width:30px;align:center;"><span id="imgCat" name="imgCat" /></td>
                            <td width="70%">
                                <label for="baskets" class="bold" ><?php echo _CATEGORY;?>:</label>
                                <input type="hidden" name="meta[]" value="category#category#select_simple" />
                                <select name="category" id="category" onchange="changeCategory(this.options[this.selectedIndex].value);">
                                    <?php 
                                    foreach (array_keys($_SESSION['coll_categories']['business_coll']) as $cat_id) {
                                        if ($cat_id <> 'default_category') {
                                            ?>
                                            <option id="<?php 
                                                    echo $cat_id;
                                                    ?>" value="<?php 
                                                    echo $cat_id;
                                                    ?>" ><?php 
                                                    echo $_SESSION['coll_categories']['business_coll'][$cat_id];
                                                ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><em><?php echo _CATEGORY_HELP; ?></em></td>
                            <td>&nbsp;</td>
                        </tr>
                    </table>
                    </div>
                    <div class ="block_end">&nbsp;</div>
                </td>
                <td>
                    <p align="center">
                    </p>
                </td>
            </tr>
            <tr><td colspan="2"><hr/></td></tr>
            <?php
            }
    ?>
    <tr>
        <td colspan="2"><h2><?php echo _DOCUMENT_INFO; ?></h2></td>
    </tr>
    <tr>
        <td>
        <div class="block">
            <table border = "0" width="100%">
                <tr>
                    <td style="width:30px;align:center;"><span id="imgContact" name="imgContact" /></td>
                    <td width="70%"><label for="contactid" class="bold"><span id="labelContact" name="labelContact" />:</label>
                        <input type="text" name="contactid" id="contactid" />
                        <input type="hidden" name="meta[]" value="contactid#contactid#input_text" />
                        <div id="contactListByName" class="autocomplete"></div>
                        <script type="text/javascript">
                            initList(
                                'contactid', 'contactListByName', 
                                '<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=contact_list_by_name', 
                                'what', 
                                '2'
                            );
                        </script>
                    </td>
                    <td><em><?php echo _CONTACT_HELP; ?></em></td>
                </tr>
                <tr>
                    <td style="width:30px;align:center;">
                        <img src="<?php 
                            echo $_SESSION['config']['businessappurl'] ;
                            ?>static.php?filename=subject.png" alt="<?php 
                            echo  _SUBJECT;
                        ?>"/>
                    </td>
                    <td width="70%"><label for="subject" class="bold"><?php echo _SUBJECT;?>:</label>
                        <input type="text" name="subject" id="subject" <?php echo $size; ?>  />
                        <input type="hidden" name="meta[]" value="subject#subject#input_text" />
                    </td>
                    <td><em><?php echo _SUBJECT_HELP; ?></em></td>
                </tr>
                <tr>
                    <td style="width:30px;align:center;">
                        <img src="<?php 
                            echo $_SESSION['config']['businessappurl'] ;
                            ?>static.php?filename=identifier.png" alt="<?php 
                            echo  _IDENTIFIER;
                        ?>"/>
                    </td>
                    <td width="70%"><label for="identifier" class="bold"><?php echo _IDENTIFIER;?>:</label>
                        <input type="text" name="identifier" id="identifier" <?php echo $size; ?>  />
                        <input type="hidden" name="meta[]" value="identifier#identifier#input_text" />
                    </td>
                    <td><em><?php echo _IDENTIFIER_HELP; ?></em></td>
                </tr>
                <tr id="totalSumMin">
                    <td style="width:30px;align:center;">
                        <img src="<?php 
                                echo $_SESSION['config']['businessappurl'] ;
                                ?>static.php?filename=amount.png" alt="<?php 
                                echo  _TOTAL_SUM;
                            ?>"/>
                    </td>
                    <td width="70%"><label for="total_sum_min" class="bold"><?php echo _TOTAL_SUM_MIN;?>:</label>
                        <input type="text" name="total_sum_min" id="total_sum_min" <?php echo $size; ?>  />
                        <input type="hidden" name="meta[]" value="total_sum_min#total_sum_min#input_text" />
                    </td>
                    <td><em><?php echo _TOTAL_SUM_MIN_HELP; ?></em></td>
                </tr>
                <tr id="totalSumMax">
                    <td style="width:30px;align:center;">
                        <img src="<?php 
                                echo $_SESSION['config']['businessappurl'] ;
                                ?>static.php?filename=amount.png" alt="<?php 
                                echo  _TOTAL_SUM;
                            ?>"/>
                    </td>
                    <td width="70%"><label for="total_sum_max" class="bold"><?php echo _TOTAL_SUM_MAX;?>:</label>
                        <input type="text" name="total_sum_max" id="total_sum_max" <?php echo $size; ?>  />
                        <input type="hidden" name="meta[]" value="total_sum_max#total_sum_max#input_text" />
                    </td>
                    <td><em><?php echo _TOTAL_SUM_MAX_HELP; ?></em></td>
                </tr>
                <tr>
                    <td style="width:30px;align:center;">
                        <a href="javascript::" onclick="window.open('<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=fulltext_search_help&mode=popup','modify','toolbar=no,status=no,width=500,height=550,left=300,top=300,scrollbars=auto,location=no,menubar=no,resizable=yes');">
                            <img src = "<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_menu_search.gif" alt="<?php echo _HELP_FULLTEXT_SEARCH; ?>" title="<?php echo _HELP_FULLTEXT_SEARCH; ?>" /></a>
                    </td>
                    <td width="70%"><label for="fulltext" class="bold"><?php echo _FULLTEXT;?>:</label>
                        <input type="text" name="fulltext" id="fulltext" <?php echo $size; ?>  />
                        <input type="hidden" name="meta[]" value="fulltext#fulltext#input_text" />
                        
                    </td>
                    <td><em><?php echo _FULLTEXT_HELP; ?></em></td>
                </tr>
                <tr>
                    <td style="width:30px;align:center;"><span id="imgContact" name="imgContact"></span></td>
                    <td width="70%"><label for="numged" class="bold"><?php echo _N_GED;?>:</label>
                        <input type="text" name="numged" id="numged" <?php echo $size; ?>  />
                        <input type="hidden" name="meta[]" value="numged#numged#input_text" />
                    </td>
                    <td><em><?php echo _N_GED_HELP; ?></em></td>
                </tr>
                </table>
            </div>
            <div class="block_end">&nbsp;</div>
        </td>
    </tr>
    <tr><td colspan="2"><hr/></td></tr>
<tr>
<td>
<div class="block">
<h2 id="bottom">&nbsp;</h2>
 <table border = "0" width="100%">
    <tr>
        <td style="width:30px;align:center;">
            <img src="<?php 
                echo $_SESSION['config']['businessappurl'] ;
                ?>static.php?filename=picto_add_b.gif" alt="<?php 
                echo  _ADD_PARAMETERS;
            ?>"/>
        </td>
        <td width="70%">
            <label class="bold"><?php echo _ADD_PARAMETERS; ?>:</label>
            <select name="select_criteria" id="select_criteria" style="display:inline;" onchange="add_criteria(this.options[this.selectedIndex].id, 'frmsearch2', <?php 
                echo $browser_ie;?>, '<?php echo _ERROR_IE_SEARCH;?>');window.location.href = '#bottom';">
                <?php echo $src_tab; ?>
            </select>
        </td>
        <td><em><?php echo _ADD_PARAMETERS_HELP; ?></em></td>
    </tr>
</table>
</div>
<div class="block_end">&nbsp;</div>
</td></tr>
</table>
<table align="center" border="0" width="100%">
    <tr>
        <td><a href="#" onclick="clear_search_form('frmsearch2','select_criteria');clear_q_list();changeCategory('purchase');">
                <img src="<?php  echo $_SESSION['config']['businessappurl']."static.php?filename=reset.gif";?>" alt="<?php echo _CLEAR_SEARCH;?>" /> <?php  echo _CLEAR_SEARCH; ?></a></td>
        <td align="right">
            <input class="button_search_adv" name="imageField" type="submit" value="" onclick="valid_search_form('frmsearch2');this.form.submit();" /><br/>
            <input class="button_search_adv_text" name="imageField" type="button" value="<?php echo _SEARCH; ?>" onclick="valid_search_form('frmsearch2');this.form.submit();" />
        </td>
    </tr>
</table>
</form>

<br/>
<div align="right">
</div>
 </div>
<script type="text/javascript">
load_query(valeurs, loaded_query, 'frmsearch2', '<?php echo $browser_ie;?>, <?php echo _ERROR_IE_SEARCH;?>');
<?php 
if (isset($_REQUEST['init_search'])) {
    ?>clear_search_form('frmsearch2','select_criteria');clear_q_list();changeCategory('purchase'); <?php
}
?>

//ON LOAD
changeCategory($('category').value);

function changeCategory(catId)
{
    if (catId == 'purchase') {
        $('totalSumMin').style.display = '';
        $('totalSumMax').style.display = '';
        $('imgCat').innerHTML = '<img src="<?php 
            echo $_SESSION['config']['businessappurl'] ;
            ?>static.php?filename=cat_doc_purchase.png" alt="<?php 
            echo  _PURCHASE;
        ?>"/>';
        $('imgContact').innerHTML = '<img src="<?php 
            echo $_SESSION['config']['businessappurl'] ;
            ?>static.php?filename=supplier.png" alt="<?php 
            echo  _SUPPLIER;
        ?>"/>';
        $('labelContact').innerHTML = '<?php echo  _SUPPLIER;?>';
    } else if (catId == 'sell') {
        $('totalSumMin').style.display = '';
        $('totalSumMax').style.display = '';
        $('imgCat').innerHTML = '<img src="<?php 
            echo $_SESSION['config']['businessappurl'] ;
            ?>static.php?filename=cat_doc_sell.png" alt="<?php 
            echo  _SELL;
        ?>"/>';
        $('imgContact').innerHTML = '<img src="<?php 
            echo $_SESSION['config']['businessappurl'] ;
            ?>static.php?filename=purchaser.png" alt="<?php 
            echo  _PURCHASER;
        ?>"/>';
        $('labelContact').innerHTML = '<?php echo  _PURCHASER;?>';
    } else if (catId == 'enterprise_document') {
        $('totalSumMin').style.display = 'none';
        $('totalSumMax').style.display = 'none';
        $('imgCat').innerHTML = '<img src="<?php 
            echo $_SESSION['config']['businessappurl'] ;
            ?>static.php?filename=cat_doc_enterprise_document.png" alt="<?php 
            echo  _ENTERPRISE_DOCUMENT;
        ?>"/>';
        $('imgContact').innerHTML = '<img src="<?php 
            echo $_SESSION['config']['businessappurl'] ;
            ?>static.php?filename=my_contacts_off.gif" alt="<?php 
            echo  _CONTACT;
        ?>"/>';
        $('labelContact').innerHTML = '<?php echo  _CONTACT;?>';
    } else if (catId == 'human_resources') {
        $('totalSumMin').style.display = 'none';
        $('totalSumMax').style.display = 'none';
        $('imgCat').innerHTML = '<img src="<?php 
            echo $_SESSION['config']['businessappurl'] ;
            ?>static.php?filename=cat_doc_human_resources.png" alt="<?php 
            echo  _PURCHASE;
        ?>"/>';
        $('imgContact').innerHTML = '<img src="<?php 
            echo $_SESSION['config']['businessappurl'] ;
            ?>static.php?filename=employee.png" alt="<?php 
            echo  _HUMAN_RESOURCES;
        ?>"/>';
        $('labelContact').innerHTML = '<?php echo  _EMPLOYEE;?>';
    }
}
</script>

<?php 
if ($mode == 'popup' || $mode == 'frame') {
    echo '</div>';
    if ($mode == 'popup') {
        ?><br/><div align="center"><input type="button" name="close" class="button" value="<?php echo _CLOSE_WINDOW;?>" onclick="self.close();" /></div> <?php
    }
    echo '</body></html>';
}

