<?php

/**
 * File : details_invoices.php
 *
 * Detailed informations on an indexed document
 *
 * @package  Maarch PeopleBox 1.0
 * @version 2.1
 * @since 10/2005
 * @license GPL
 * @author  Claire Figueras  <dev@maarch.org>
 */

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
require_once ("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_request.php");
require_once ("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_security.php");
require_once ("apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_list_show.php");
require_once ("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_history.php");
require_once ("apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_indexing_searching_app.php");
require_once ("apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_types.php");
if(file_exists('custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . "definition_mail_categories.php")) {
    include ('custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . "definition_mail_categories.php");
} else {
    include ('apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . "definition_mail_categories_invoices.php");
}
if (!isset ($_REQUEST['coll_id'])) {
    $_REQUEST['coll_id'] = "";
}
$_SESSION['doc_convert'] = array ();
/****************Management of the location bar  ************/
$init = false;
if (isset ($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true") {
    $init = true;
}
if (isset ($_SESSION['indexation']) && $_SESSION['indexation'] == true) {
    $init = true;
}
$level = "";
if (isset ($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)) {
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'] . 'index.php?page=detail_invoicess&dir=indexing_searching&coll_id=' . $_REQUEST['coll_id'] . '&id=' . $_REQUEST['id'];
$page_label = _DETAILS;
$page_id = "details_invoices";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
$users = new history();
$security = new security();
$func = new functions();
$request = new request;
$type = new types();
$s_id = "";
$_SESSION['req'] = 'details_invoices';
$_SESSION['indexing'] = array ();
$is = new indexing_searching_app();
$coll_id = '';
$table = '';
if (!isset ($_REQUEST['coll_id']) || empty ($_REQUEST['coll_id'])) {
    //$_SESSION['error'] = _COLL_ID.' '._IS_MISSING;
    $coll_id = "res_coll";
    $table = "res_view";
    $is_view = true;
} else {
    $coll_id = $_REQUEST['coll_id'];
    $table = $security->retrieve_view_from_coll_id($coll_id);
    $is_view = true;
    if (empty ($table)) {
        $table = $security->retrieve_table_from_coll($coll_id);
        $is_view = false;
    }
}
$_SESSION['collection_id_choice'] = $coll_id;
if (isset ($_GET['id']) && !empty ($_GET['id'])) {
    $s_id = addslashes($func->wash($_GET['id'], "num", _THE_DOC));
}
$_SESSION['doc_id'] = $s_id;
if (isset ($_SESSION['origin']) && $_SESSION['origin'] <> "basket") {
    $right = $security->test_right_doc($coll_id, $s_id);
    //$_SESSION['error'] = 'coll '.$coll_id.', res_id : '.$s_id;
} else {
    $right = true;
}
if (!$right) {
    ?>
    <script type="text/javascript">
    window.top.location.href = '<?php  echo $_SESSION['config']['businessappurl'];?>index.php?page=no_right';
    </script>
    <?php
    exit();
}
if (isset ($s_id) && !empty ($s_id) && $_SESSION['history']['resview'] == "true") {
    $users->add($table, $s_id, "VIEW", _VIEW_DETAILS_NUM . $s_id, $_SESSION['config']['databasetype'], 'apps');
}
$modify_doc = $security->collection_user_right($coll_id, "can_update");
$delete_doc = $security->collection_user_right($coll_id, "can_delete");
//update index with the doctype
if (isset ($_POST['submit_index_doc'])) {
    $is->update_mail($_POST, "POST", $s_id, $coll_id);
}
//delete the doctype
if (isset ($_POST['delete_doc'])) {
    $is->delete_doc($s_id, $coll_id);
    ?>
        <script type="text/javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'].'index.php?page=search_adv&dir=indexing_searching';?>';</script>
    <?php
    exit();
}
$db = new dbquery();
$db->connect();
if (empty ($_SESSION['error']) || $_SESSION['indexation']) {
    $comp_fields = '';
    $db->query("select type_id from " . $table . " where res_id = " . $s_id);
    if ($db->nb_result() > 0) {
        $res = $db->fetch_object();
        $type_id = $res->type_id;
        $indexes = $type->get_indexes($type_id, $coll_id, 'minimal');
        for ($i = 0; $i < count($indexes); $i++) {
            if (preg_match('/^custom_/', $indexes[$i])) // In the view all custom from res table begin with doc_
            {
                $comp_field .= ', doc_' . $indexes[$i];
            } else {
                $comp_field .= ', ' . $indexes[$i];
            }
        }
    }
    $db->query("select * from ".$table." where res_id = ".$s_id."");
}
?>
<div id="details_div" >
    <h1 class="titdetail">
        <img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=picto_detail_b.gif" alt="" /><?php  echo _DETAILS." : "._DOC.' '.strtolower(_NUM); ?><?php  echo $s_id; ?> <span>(<?php  echo  $security->retrieve_coll_label_from_coll_id($coll_id); ?>)</span>
    </h1>
    <div id="inner_content" class="clearfix">
        <?php
        if ((!empty ($_SESSION['error']) && !($_SESSION['indexation']))) {
            ?>
            <div class="error">
                <br />
                <br />
                <br />
                <?php  echo $_SESSION['error'];  $_SESSION['error'] = "";?>
                <br />
                <br />
                <br />
            </div>
            <?php
        } else {
            if ($db->nb_result() == 0) {
                ?>
                <div align="center">
                    <br />
                    <br />
                    <?php  echo _NO_DOCUMENT_CORRESPOND_TO_IDENTIFIER; ?>.
                    <br />
                    <br />
                    <br />
                </div>
                <?php
            } else {
                $param_data = array (
                    'img_category_id' => true,
                    'img_priority' => true,
                    'img_type_id' => true,
                    'img_doc_date' => true,
                    'img_admission_date' => true,
                    'img_nature_id' => true,
                    'img_subject' => true,
                    'img_process_limit_date' => true,
                    'img_author' => true,
                    'img_destination' => true,
                    'img_arbox_id' => true,
                    'img_market' => true,
                    'img_project' => true
                );
                $res = $db->fetch_object();
                $typist = $res->typist;
                $format = $res->format;
                $filesize = $res->filesize;
                $creation_date = $db->format_date_db($res->creation_date, false);
                $fingerprint = $res->fingerprint;
                $work_batch = $res->work_batch;
                $page_count = $res->page_count;
                $is_paper = $res->is_paper;
                $scan_date = $db->format_date_db($res->scan_date);
                $scan_user = $res->scan_user;
                $scan_location = $res->scan_location;
                $scan_wkstation = $res->scan_wkstation;
                $scan_batch = $res->scan_batch;
                $doc_language = $res->doc_language;
                $indexes = $type->get_indexes($type_id, $coll_id);
                //print_r($indexes);
                //$db->show_array($indexes);
                foreach (array_keys($indexes) as $key) {
                    if(preg_match('/^custom/', $key)){
                        $tmp = 'doc_' . $key;
                    }
                    else{
                        $tmp = $key;
                    }
                    if ($indexes[$key]['type'] == "date") {
                        $res-> $tmp = $db->format_date_db($res-> $tmp, false);
                    }
                    $indexes[$key]['value'] = $res-> $tmp;
                    $indexes[$key]['show_value'] = $res-> $tmp;
                    if ($indexes[$key]['type'] == "string") {
                        $indexes[$key]['show_value'] = $db->show_string($res-> $tmp);
                    }
                    elseif ($indexes[$key]['type'] == "date") {
                        $indexes[$key]['show_value'] = $db->format_date_db($res-> $tmp, true);
                    }
                }
                //$db->show_array($indexes);
                //$process_data = $is->get_process_data($coll_id, $s_id);
                $status = $res->status;
                if (!empty ($status)) {
                    require_once ("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_manage_status.php");
                    $status_obj = new manage_status();
                    $res_status = $status_obj->get_status_data($status);
                    if ($modify_doc) {
                        $can_be_modified = $status_obj->can_be_modified($status);
                        if (!$can_be_modified) {
                            $modify_doc = false;
                        }
                    }
                }
                $mode_data = 'full';
                if ($modify_doc) {
                    $mode_data = 'form';
                }
                foreach (array_keys($indexes) as $key) {
                    $indexes[$key]['opt_index'] = true;
                    if ($indexes[$key]['type_field'] == 'select') {
                        for ($i = 0; $i < count($indexes[$key]['values']); $i++) {
                            if ($indexes[$key]['values'][$i]['id'] == $indexes[$key]['value']) {
                                $indexes[$key]['show_value'] = $indexes[$key]['values'][$i]['label'];
                                break;
                            }
                        }
                    }
                    if (!$modify_doc) {
                        $indexes[$key]['readonly'] = true;
                        $indexes[$key]['type_field'] = 'input';
                    } else {
                        $indexes[$key]['readonly'] = false;
                    }
                }
                $data = get_general_data($coll_id, $s_id, $mode_data, $param_data);
                //$db->show_array($data);
                ?>
                <div class="block">
                    <b>
                    <p id="back_list">
                    <?php
                    if (!$_POST['up_res_id']) {
                        if ($_SESSION['indexation'] == false) {
                            ?>
                            <a href="#" onclick="history.go(-1);" class="back"><?php  echo _BACK; ?></a>
                            <?php
                        }
                    }
                    ?>
                    </p>
                    <p id="viewdoc">
                        <a href="<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&dir=indexing_searching&page=view_resource_controler&id=<?php  echo $s_id; ?>" target="_blank"><?php  echo _VIEW_DOC; ?></a> &nbsp;| &nbsp;
                    </p></b>&nbsp;
                </div>
                <br/>
                <dl id="tabricator1">
                    <dt><?php  echo _DETAILLED_PROPERTIES;?></dt>
                        <dd>
                            <h2>
                                <span class="date">
                                    <b><?php  echo _FILE_DATA;?></b>
                                </span>
                            </h2>
                            <br/>
                            <form method="post" name="index_doc" id="index_doc" action="index.php?page=details_invoices&dir=indexing_searching&id=<?php  echo $s_id; ?>">
                                <?php
                                //$db->show_array($data);
                                ?>
                                <table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
                                    <?php
            $i = 0;
            foreach (array_keys($data) as $key) {
                if ($key <> "category_id" && $key <> "priority" && $key <> "subject" ){
                    //$func->show_array($data[$key]);
                    if ($i % 2 != 1 || $i == 0) // pair
                    {
                        ?>
                                                <tr class="col">
                                                <?php

                    }
                    ?>
                                            <th align="left" class="picto" >
                                                <?php

                    if (isset ($data[$key]['addon'])) {
                        echo $data[$key]['addon'];
                    }
                    elseif (isset ($data[$key]['img'])) {
                        if ($folder_id <> "") {
                            echo "<a href='" . $_SESSION['config']['businessappurl'] . "index.php?page=show_folder&module=folder&id=" . $folder_id . "'>";
                            ?>
                                                            <img alt="<?php echo $data[$key]['label'];?>" title="<?php echo $data[$key]['label'];?>" src="<?php echo $data[$key]['img'];?>"  /></a>
                                                            <?php

                        } else {
                            ?>
                                                        <img alt="<?php echo $data[$key]['label'];?>" title="<?php echo $data[$key]['label'];?>" src="<?php echo $data[$key]['img'];?>"  /></a>
                                                        <?php

                        }
                        ?>
                        <?php
                    }
                ?>
                                            </th>
                                            <?php
                ?>
                                            <td align="left" width="200px">
                                                <?php
                echo $data[$key]['label'];
                ?> :
                                            </td>
                                            <?php
                ?>
                                            <td>
                                                <?php
                if (!isset ($data[$key]['readonly']) || $data[$key]['readonly'] == true) {
                    if ($data[$key]['display'] == 'textinput') {
                        ?>
                                                    <input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $data[$key]['show_value'];?>" readonly="readonly" class="readonly" size="40"  title="<?php  echo $data[$key]['show_value']; ?>" alt="<?php  echo $data[$key]['show_value']; ?>" />
                                                    <?php

                } else {
                    ?>
                                                    <input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $data[$key]['show_value'];?>" readonly="readonly" class="readonly" size="40" title="<?php  echo $data[$key]['show_value']; ?>" alt="<?php  echo $data[$key]['show_value']; ?>" />
                                                    <?php

                if (isset ($data[$key]['addon'])) {
                    $frm_str .= $data[$key]['addon'];
                }
                }
                } else {
                    if ($data[$key]['field_type'] == 'textfield') {
                        ?>
                                                    <input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $data[$key]['show_value'];?>" size="40"  title="<?php  echo $data[$key]['show_value']; ?>" alt="<?php  echo $data[$key]['show_value']; ?>" />
                                                    <?php

                }  else
                    if ($data[$key]['field_type'] == 'select') {
                        ?>
                                                    <select id="<?php echo $key;?>" name="<?php echo $key;?>" <?php if($key == 'type_id'){echo 'onchange="change_doctype_details(this.options[this.options.selectedIndex].value, \''.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=change_doctype_details\' , \''._DOCTYPE.' '._MISSING.'\');"';}?>>
                                                    <?php

                if ($key == 'type_id') {
                    if ($_SESSION['features']['show_types_tree'] == 'true') {

                        for ($k = 0; $k < count($data[$key]['select']); $k++) {
                            ?><option value="" class="doctype_level1"><?php echo $data[$key]['select'][$k]['label'];?></option><?php

                for ($j = 0; $j < count($data[$key]['select'][$k]['level2']); $j++) {
                    ?><option value="" class="doctype_level2">&nbsp;&nbsp;<?php echo $data[$key]['select'][$k]['level2'][$j]['label'];?></option><?php

                for ($l = 0; $l < count($data[$key]['select'][$k]['level2'][$j]['types']); $l++) {
                    ?><option
                                                                            <?php if($data[$key]['value'] ==$data[$key]['select'][$k]['level2'][$j]['types'][$l]['id']){ echo 'selected="selected"';}?>
                                                                             value="<?php echo $data[$key]['select'][$k]['level2'][$j]['types'][$l]['id'];?>" >&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $data[$key]['select'][$k]['level2'][$j]['types'][$l]['label'];?></option><?php

                }
                }
                }
                } else {
                    for ($k = 0; $k < count($data[$key]['select']); $k++) {
                        ?><option <?php if($data[$key]['value'] ==$data[$key]['select'][$k]['ID']){ echo 'selected="selected"';}?> value="<?php echo $data[$key]['select'][$k]['ID'];?>" ><?php echo $data[$key]['select'][$k]['LABEL'];?></option><?php

                }
                }
                } else {
                    for ($k = 0; $k < count($data[$key]['select']); $k++) {
                        ?><option value="<?php echo $data[$key]['select'][$k]['ID'];?>" <?php if($data[$key]['value'] == $data[$key]['select'][$k]['ID']){echo 'selected="selected"';}?>><?php echo $data[$key]['select'][$k]['LABEL'];?></option><?php

                }
                }
                ?>
                                                    </select>
                                                    <?php

                } else
                    if ($data[$key]['field_type'] == 'autocomplete') {
                        if ($key == 'project') {
                            //$('market').value='';return false;
                            ?><input type="text" name="project" id="project" onblur="" value="<?php echo $data['project']['show_value']; ?>" /><div id="show_project" class="autocomplete"></div><script type="text/javascript">launch_autocompleter_folders('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=folder&page=autocomplete_folders&mode=project', 'project');</script>
                                                    <?php

                } else
                    if ($key == 'market') {
                        ?><input type="text" name="market" id="market" onblur="fill_project('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=folder&page=ajax_get_project');return false;"  value="<?php echo $data['market']['show_value']; ?>"/><div id="show_market" class="autocomplete"></div>
                                                    <script type="text/javascript">launch_autocompleter_folders('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=folder&page=autocomplete_folders&mode=market', 'market');</script>
                                                    <?php

                    }
                }
            }
            }
            ?>
                                        </td>
                                        <?php

            if ($i % 2 == 1 && $i != 0) // impair
            {
                ?>
                                            </tr>
                                            <?php

            } else {
                if ($i +1 == count($data)) {
                    echo '<td  colspan="2">&nbsp;</td></tr>';
                }
            }
            $i++;
            }
            ?>
                                    <tr class="col">
                                        <th align="left" class="picto">
                                            <img alt="<?php echo _STATUS.' : '.$res_status['LABEL'];?>" src="<?php echo $res_status['IMG_SRC'];?>" title="<?php  echo $res_status['LABEL']; ?>" alt="<?php  echo $res_status['LABEL']; ?>"/>
                                        </th>
                                        <td align="left" width="200px">
                                            <?php  echo _STATUS; ?> :
                                        </td>
                                        <td>
                                            <input type="text" class="readonly" readonly="readonly" value="<?php  echo $res_status['LABEL']; ?>" size="40"  />
                                        </td>
                                    </tr>
                                </table>
                                <div id="opt_indexes">
                                <?php

            if (count($indexes) > 0) {
                ?><br/>
                                    <h2>
                                    <span class="date">
                                        <b><?php  echo _OPT_INDEXES;?></b>
                                    </span>
                                    </h2>
                                    <br/>
                                    <table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
                                        <?php

            $i = 0;

            foreach (array_keys($indexes) as $key) {

                if ($i % 2 != 1 || $i == 0) // pair
                {
                    ?>
                                                <tr class="col">
                                                <?php

            }
            ?>
                                            <th align="left" class="picto" >
                                                <?php

            if (isset ($indexes[$key]['img'])) {
                ?>
                                                    <img alt="<?php echo $indexes[$key]['label'];?>" title="<?php echo $indexes[$key]['label'];?>" src="<?php echo $indexes[$key]['img'];?>"  /></a>
                                                    <?php

            }
            ?>
                                            </th>
                                            <?php
            ?>
                                            <td align="left" width="200px">
                                                <?php
            echo $indexes[$key]['label'];
            ?> :
                                            </td>
                                            <?php
            ?>
                                            <td>
                                                <?php
            if ($indexes[$key]['type_field'] == 'input') {
                ?>
                                                    <input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $indexes[$key]['show_value'];?>" <?php if(!isset($indexes[$key]['readonly']) || $indexes[$key]['readonly'] == true){ echo 'readonly="readonly" class="readonly"';}else if($indexes[$key]['type'] == 'date'){echo 'onclick="showCalender(this);"';}?> size="40"  title="<?php  echo $indexes[$key]['show_value']; ?>" alt="<?php  echo $indexes[$key]['show_value']; ?>"   />
                                                    <?php

            } else {
                ?>
                                                    <select name="<?php echo $key;?>" id="<?php echo $key;?>" >
                                                        <option value=""><?php echo _CHOOSE;?>...</option>
                                                        <?php

            for ($i = 0; $i < count($indexes[$key]['values']); $i++) {
                ?>
                                                            <option value="<?php echo $indexes[$key]['values'][$i]['id'];?>" <?php if($indexes[$key]['values'][$i]['id'] == $indexes[$key]['value']){ echo 'selected="selected"';}?>><?php echo $indexes[$key]['values'][$i]['label'];?></option><?php

            }
            ?>
                                                    </select><?php

            }
            ?>
                                            </td>
                                            <?php

            if ($i % 2 == 1 && $i != 0) // impair
            {
                ?>
                                                </tr>
                                                <?php

            } else {
                if ($i +1 == count($indexes)) {
                    echo '<td  colspan="2">&nbsp;</td></tr>';
                }
            }
            $i++;
            }
            ?>
                                    </table>
                                    <?php  } ?>
                                </div>
                                <br/>

                                <h2>
                                <span class="date">
                                    <b><?php  echo _FILE_PROPERTIES;?></b>
                                </span>
                                </h2>
                                <br/>

                                <table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
                                    <tr>
                                        <th align="left" class="picto">
                                            <img alt="<?php echo _TYPIST; ?>" src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_users_entities_b_small.gif" />
                                        </th>
                                        <td align="left" width="200px"><?php  echo _TYPIST; ?> :</td>
                                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $typist; ?>"  /></td>
                                        <th align="left" class="picto">
                                            <img alt="<?php echo _SIZE; ?>" src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=weight.gif" />
                                        </th>
                                        <td align="left" width="200px"><?php  echo _SIZE; ?> :</td>
                                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $filesize." ".$_SESSION['lang']['txt_byte']." ( ".round($filesize/1024,2)."K )"; ?>" /></td>
                                    </tr>
                                    <tr class="col">
                                        <th align="left" class="picto">
                                            <img alt="<?php echo _FORMAT; ?>" src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=mini_type.gif" />
                                        </th>
                                        <td align="left"><?php  echo _FORMAT; ?> :</td>
                                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $format; ?>" size="40"  /></td>
                                        <th align="left" class="picto">
                                            <img alt="<?php echo _CREATION_DATE; ?>" src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=small_calend.gif" />
                                        </th>
                                        <td align="left"><?php  echo _CREATION_DATE; ?> :</td>
                                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $func->format_date_db($creation_date, false); ?>"/></td>
                                    </tr>
                                    <tr>
                                        <th align="left" class="picto">
                                            <img alt="<?php echo _MD5; ?>" src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=md5.gif" />
                                        </th>
                                        <td align="left"><?php  echo _MD5; ?> :</td>
                                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $fingerprint; ?>"  title="<?php  echo $fingerprint; ?>" alt="<?php  echo $fingerprint; ?>" /></td>

                                        <th align="left" class="picto">
                                            <img alt="<?php echo _WORK_BATCH; ?>" src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=lot.gif" />
                                        </th>
                                        <td align="left"><?php  echo _WORK_BATCH; ?> :</td>
                                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $work_batch; ?>" title="<?php  echo $work_batch; ?>" alt="<?php  echo $work_batch; ?>" /></td>
                                    </tr>
                                    <!--
                                    <tr>
                                        <th align="left"><?php  echo _PAGECOUNT; ?> :</th>
                                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $page_count; ?>"  /></td>
                                        <th align="left"><?php  echo _ISPAPER; ?> :</th>
                                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $is_paper; ?>" /></td>
                                    </tr>
                                        <tr class="col">
                                        <th align="left"><?php  echo _SCANUSER; ?> :</th>
                                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $scan_user; ?>"  /></td>
                                        <th align="left"><?php  echo _SCANDATE; ?> :</th>
                                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $scan_date; ?>" /></td>
                                    </tr>
                                    <tr>
                                        <th align="left"><?php  echo _SCANWKSATION; ?> :</th>
                                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $scan_wkstation; ?>" /></td>
                                        <th align="left"><?php  echo _SCANLOCATION; ?> :</th>
                                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $scan_location; ?>" /></td>
                                    </tr>
                                    <tr class="col">
                                        <th align="left"><?php  echo _SCANBATCH; ?> :</th>
                                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $scan_batch; ?>"  /></td>
                                        <th align="right"><?php  echo _SOURCE; ?> :</th>
                                        <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $source; ?>" /></td>
                                    </tr>
                                    -->
                                </table>
                                <br/>
                                <div align="center">
                                    <?php

            if ($delete_doc) {
                ?>
                                    <input type="submit" class="button"  value="<?php  echo _DELETE_DOC;?>" name="delete_doc" onclick="return(confirm('<?php  echo _REALLY_DELETE.' '._THIS_DOC;?> ?\n\r\n\r'));" />
                                    <?php

            }
            if ($modify_doc) {
                ?>
                                    <input type="submit" class="button"  value="<?php  echo _MODIFY_DOC;?>" name="submit_index_doc" />
                                    <?php  } ?>
                                        <input type="button" class="button" name="back_welcome" id="back_welcome" value="<?php echo _BACK_TO_WELCOME;?>" onclick="window.top.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php';" />

                                </div>
                                </form>
                                <?php

            }
            ?>
                            </dd>

                            <dt><?php echo _DOC_HISTORY;?></dt>
                            <dd>
                                <iframe src="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&dir=indexing_searching&page=hist_doc&id=<?php echo $s_id;?>&mode=normal" name="hist_doc_process" width="100%" height="580" align="left" scrolling="auto" frameborder="0" id="hist_doc_process"></iframe>
                            </dd>
                            <?php

            if ($core_tools->is_module_loaded('notes')) {
                $selectNotes = "select id, identifier, user_id, date_note, note_text from " . $_SESSION['tablename']['not_notes'] . " where identifier = " . $s_id . " and coll_id ='" . $_SESSION['collection_id_choice'] . "' order by date_note desc";
                $dbNotes = new dbquery();
                $dbNotes->connect();
                $dbNotes->query($selectNotes);
                //$dbNotes->show();
                $nb_notes_for_title = $dbNotes->nb_result();
                if ($nb_notes_for_title == 0) {
                    $extend_title_for_notes = '';
                } else {
                    $extend_title_for_notes = " (" . $nb_notes_for_title . ") ";
                }
                ?>
                                <dt><?php  echo _NOTES.$extend_title_for_notes;?></dt>
                                <dd>
                                <?php
            $select_notes[$_SESSION['tablename']['users']] = array ();
            array_push($select_notes[$_SESSION['tablename']['users']], "user_id", "lastname", "firstname");
            $select_notes[$_SESSION['tablename']['not_notes']] = array ();
            array_push($select_notes[$_SESSION['tablename']['not_notes']], "id", "date_note", "note_text", "user_id");
            $where_notes = " identifier = " . $s_id . " ";
            $request_notes = new request;
            $tab_notes = $request_notes->select($select_notes, $where_notes, "order by " . $_SESSION['tablename']['not_notes'] . ".date_note desc", $_SESSION['config']['databasetype'], "500", true, $_SESSION['tablename']['not_notes'], $_SESSION['tablename']['users'], "user_id");
            ?>
                                    <div style="text-align:center;">
                                        <img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=modif_note.png&module=notes" border="0" alt="" /><a href="javascript://" onclick="ouvreFenetre('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=notes&page=note_add&size=full&identifier=<?php echo $s_id;?>&coll_id=<?php echo $coll_id;?>', 450, 300)" ><?php echo _ADD_NOTE;?></a>
                                    </div>
                                    <iframe name="list_notes_doc" id="list_notes_doc" src="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=notes&page=frame_notes_doc&size=full" frameborder="0" width="100%" height="520px"></iframe>
                                </dd>
                                <?php

            }
        }
        ?>
        </div>
</div>
<script type="text/javascript">
    var item  = $('details_div');
    var tabricator1 = new Tabricator('tabricator1', 'DT');
    if(item) {
        item.style.display='block';
    }
</script>
