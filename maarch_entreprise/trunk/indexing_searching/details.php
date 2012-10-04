<?php

/*
*   Copyright 2008-2012 Maarch
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
* File : details.php
*
* Detailed informations on an indexed document
*
* @package  indexing_searching
* @version 1.3
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

$core = new core_tools();
$core->test_user();
$core->load_lang();
require_once 'core/manage_bitmask.php';
require_once 'core/class/class_request.php';
require_once 'core/class/class_security.php';
require_once 'apps/' . $_SESSION['config']['app_id'] . '/security_bitmask.php';
require_once 'apps/' . $_SESSION['config']['app_id'] . '/class/class_list_show.php';
require_once 'core/class/class_history.php';
require_once 'core/class/LinkController.php';
require_once 'apps/' . $_SESSION['config']['app_id'] . '/class/class_indexing_searching_app.php';
require_once 'apps/' . $_SESSION['config']['app_id'] . '/class/class_types.php';

if (file_exists(
    $_SESSION['config']['corepath'] . 'custom/apps/' . $_SESSION['config']['app_id']
    . '/definition_mail_categories.php'
)
) {
    $path = $_SESSION['config']['corepath'] . 'custom/apps/' . $_SESSION['config']['app_id']
          . '/definition_mail_categories.php';
} else {
    $path = 'apps/' . $_SESSION['config']['app_id'] . '/definition_mail_categories.php';
}
include_once $path;

//test service print_details
$printDetails = false;
if ($core->test_service('print_details', 'apps', false)) {
    $printDetails = true;
}
//test service put_in_validation
$putInValid = false;
if ($core->test_service('put_in_validation', 'apps', false)) {
    $putInValid = true;
}
//test service view technical infos
$viewTechnicalInfos = false;
if ($core->test_service('view_technical_infos', 'apps', false)) {
    $viewTechnicalInfos = true;
}

//test service add new version
$addNewVersion = false;
if ($core->test_service('add_new_version', 'apps', false)) {
    $addNewVersion = true;
}

if (!isset($_REQUEST['coll_id'])) {
    $_REQUEST['coll_id'] = '';
}
$_SESSION['doc_convert'] = array();

/****************Management of the location bar  ************/
$init = false;
if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == 'true') {
    $init = true;
}
if (isset($_SESSION['indexation'] ) && $_SESSION['indexation'] == true) {
    $init = true;
}
$level = '';
if (
    isset($_REQUEST['level'])
    && (
        $_REQUEST['level'] == 2
        || $_REQUEST['level'] == 3
        || $_REQUEST['level'] == 4
        || $_REQUEST['level'] == 1
    )
) {
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl']
    . 'index.php?page=details&dir=indexing_searching&coll_id='
    . $_REQUEST['coll_id']
    . '&id=' . $_REQUEST['id'];
$page_label = _DETAILS;
$page_id = 'details';
$core->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
$hist = new history();
$security = new security();
$func = new functions();
$request= new request;
$type = new types();
$s_id = '';
$_SESSION['req'] ='details';
$_SESSION['indexing'] = array();
$is = new indexing_searching_app();
$coll_id = '';
$table = '';
if (!isset($_REQUEST['coll_id']) || empty($_REQUEST['coll_id'])) {
    //$_SESSION['error'] = _COLL_ID.' '._IS_MISSING;
    $coll_id = $_SESSION['collections'][0]['id'];
    $table = $_SESSION['collections'][0]['view'];
    $is_view = true;
} else {
    $coll_id = $_REQUEST['coll_id'];
    $table = $security->retrieve_view_from_coll_id($coll_id);
    $is_view = true;
    if (empty($table)) {
        $table = $security->retrieve_table_from_coll($coll_id);
        $is_view = false;
    }
}
$_SESSION['collection_id_choice'] = $coll_id;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $s_id = addslashes($func->wash($_GET['id'], 'num', _THE_DOC));
}

$db = new dbquery();
$db->connect();
$db->query("select res_id from mlb_coll_ext where res_id = " . $s_id);
if ($db->nb_result() <= 0) {
    echo '<div class="error">' . _QUALIFY_FIRST . '</div>';exit;
    ?>
        <script language="javascript" type="text/javascript">window.top.location.href='<?php
            echo $_SESSION['config']['businessappurl']
            . 'index.php';
            ?>';</script>
    <?php
}
$_SESSION['doc_id'] = $s_id;
$right = $security->test_right_doc($coll_id, $s_id);
//$_SESSION['error'] = 'coll '.$coll_id.', res_id : '.$s_id;

if (!$right) {
    ?>
    <script type="text/javascript">
    window.top.location.href = '<?php
        echo $_SESSION['config']['businessappurl'];
        ?>index.php?page=no_right';
    </script>
    <?php
    exit();
}
if (isset($s_id) && !empty($s_id) && $_SESSION['history']['resview'] == 'true') {
    $hist->add(
        $table,
        $s_id ,
        'VIEW',
        'resview',
        _VIEW_DETAILS_NUM . $s_id,
        $_SESSION['config']['databasetype'],
        'apps'
    );
}

$modify_doc = check_right(
    $_SESSION['user']['security'][$coll_id]['DOC']['securityBitmask'],
    DATA_MODIFICATION
);
$delete_doc = check_right(
    $_SESSION['user']['security'][$coll_id]['DOC']['securityBitmask'],
    DELETE_RECORD
);

//update index with the doctype
if (isset($_POST['submit_index_doc'])) {
    if (
        $core->is_module_loaded('entities')
        && is_array($_SESSION['details']['diff_list'])
    ) {
        require_once('modules/entities/class/class_manage_listdiff.php');
        $list = new diffusion_list();
        $params = array(
            'mode'=> 'listinstance',
            'table' => $_SESSION['tablename']['ent_listinstance'],
            'coll_id' => $coll_id,
            'res_id' => $s_id,
            'user_id' => $_SESSION['user']['UserId'],
            'concat_list' => true,
            'only_cc' => false
        );
        $list->load_list_db(
            $_SESSION['details']['diff_list'],
            $params
        ); //pb enchainement avec action redirect
        $_SESSION['details']['diff_list']['key_value'] = md5($res_id);
    }
    $is->update_mail($_POST, 'POST', $s_id, $coll_id);

    if ($core->is_module_loaded('tags')) {
        include_once("modules".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."tags_update.php");
    }
}
//delete the doctype
if (isset($_POST['delete_doc'])) {
    $is ->delete_doc($s_id, $coll_id);
    ?>
        <script type="text/javascript">window.top.location.href='<?php
            echo $_SESSION['config']['businessappurl']
                . 'index.php?page=search_adv&dir=indexing_searching';
            ?>';</script>
    <?php
    exit();
}
if (isset($_POST['put_doc_on_validation'])) {
    $is ->update_doc_status($s_id, $coll_id, 'VAL');
    ?>
        <script language="javascript" type="text/javascript">window.top.location.href='<?php
            echo $_SESSION['config']['businessappurl']
            . 'index.php?page=search_adv&dir=indexing_searching';
            ?>';</script>
    <?php
    exit();
}

if (empty($_SESSION['error']) || $_SESSION['indexation']) {
    $comp_fields = '';
    $db->query("select type_id from ".$table." where res_id = ".$s_id);
    if ($db->nb_result() > 0) {
        $res = $db->fetch_object();
        $type_id = $res->type_id;
        $indexes = $type->get_indexes($type_id, $coll_id, 'minimal');
        for($i=0;$i<count($indexes);$i++) {
            // In the view all custom from res table begin with doc_
            if (preg_match('/^custom_/', $indexes[$i])) {
                $comp_fields .= ', doc_'.$indexes[$i];
            } else {
                $comp_fields .= ', '.$indexes[$i];
            }
        }
    }
    $case_sql_complementary = '';
    if ($core->is_module_loaded('cases') == true) {
        $case_sql_complementary = " , case_id";
    }
    $db->query(
        "select status, format, typist, creation_date, fingerprint, filesize, "
        . "res_id, work_batch, page_count, is_paper, scan_date, scan_user, "
        . "scan_location, scan_wkstation, scan_batch, source, doc_language, "
        . "description, closing_date, alt_identifier, entity_label " . $comp_fields
        . $case_sql_complementary . " from " . $table . " where res_id = "
        . $s_id
    );
    //$db->show();

}
?>
<div id="details_div" style="display:none;">
<h1 class="titdetail">
    <img src="<?php
        echo $_SESSION['config']['businessappurl'];
        ?>static.php?filename=picto_detail_b.gif" alt="" /><?php
        echo _DETAILS . " : " . _DOC . ' ' . strtolower(_NUM);
        ?><?php
        echo $s_id;
        ?> <span>(<?php
        echo  $security->retrieve_coll_label_from_coll_id($coll_id);
        ?>)</span>
</h1>
<div id="inner_content" class="clearfix">
<?php
if ((!empty($_SESSION['error']) && ! ($_SESSION['indexation'] ))  )
{
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
            $param_data = array(
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
            $chrono_number = $res->alt_identifier;
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
            $closing_date = $db->format_date_db($res->closing_date, false);
            $indexes = $type->get_indexes($type_id, $coll_id);
            $entityLabel = $res->entity_label;

            if ($core->is_module_loaded('cases') == true) {
                require_once('modules/cases/class/class_modules_tools.php');
                $case = new cases();
                if ($res->case_id <> '')
                    $case_properties = $case->get_case_info($res->case_id);
            }

            //$db->show_array($indexes);
            foreach (array_keys($indexes) as $key) {
                if (preg_match('/^custom/', $key)) {
                    $tmp = 'doc_' . $key;
                } else{
                    $tmp = $key;
                }
                if ($indexes[$key]['type'] == "date") {
                    $res->$tmp = $db->format_date_db($res->$tmp, false);
                }
                $indexes[$key]['value'] = $res->$tmp;
                $indexes[$key]['show_value'] = $res->$tmp;
                if ($indexes[$key]['type'] == "string") {
                    $indexes[$key]['show_value'] = $db->show_string($res->$tmp);
                } elseif ($indexes[$key]['type'] == "date") {
                    $indexes[$key]['show_value'] = $db->format_date_db($res->$tmp, true);
                }
            }
            //$db->show_array($indexes);
            $process_data = $is->get_process_data($coll_id, $s_id);
            $status = $res->status;
            if (!empty($status))
            {
                require_once('core/class/class_manage_status.php');
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
                    for($i=0;$i<count($indexes[$key]['values']);$i++) {
                        if ($indexes[$key]['values'][$i]['id'] == $indexes[$key]['value']) {
                            $indexes[$key]['show_value'] = $indexes[$key]['values'][$i]['label'] ;
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
            $data = get_general_data($coll_id, $s_id, $mode_data, $param_data );
            //$data = array_merge($data, $indexes);
            //$db->show_array($indexes);
            $detailsExport = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" >';
            $detailsExport .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">';
            $detailsExport .= "<head><title>Maarch Details</title><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><meta content='fr' http-equiv='Content-Language'/><meta http-equiv='cache-control' content='no-cache'/><meta http-equiv=\"pragma\" content=\"no-cache\"> </head>";
            $detailsExport .= "<body onload='javascript:window.print();' style='font-size:8pt'>";
            ?>
            <div class="block">
                <b>
                <p id="back_list">
                    <?php
                    if (! isset($_POST['up_res_id']) || ! $_POST['up_res_id']) {
                        if ($_SESSION['indexation'] == false) {
                            ?>
                            <a href="#" onclick="history.go(-1);" class="back"><?php  echo _BACK; ?></a>
                            <?php
                        }
                    }
                    ?>
                </p>
                <p id="viewdoc">
                    <!--<a href="<?php
                        echo $_SESSION['config']['businessappurl'];
                        ?>index.php?page=view_baskets&module=basket&baskets=MyBasket&directLinkToAction&resid=<?php
                        echo $s_id;
                        ?>" target="_blank"><img alt="<?php echo _PROCESS_DOC;?>" src="<?php echo
                            $_SESSION['config']['businessappurl'];
                            ?>static.php?filename=lot.gif" border="0" alt="" />&nbsp;<?php
                        echo _PROCESS_DOC;
                        ?></a>-->
                    <a href="<?php
                        echo $_SESSION['config']['businessappurl'];
                        ?>index.php?display=true&dir=indexing_searching&page=view_resource_controler&id=<?php
                        echo $s_id;
                        ?>" target="_blank"><img alt="<?php echo _VIEW_DOC;?>" src="<?php echo
                            $_SESSION['config']['businessappurl'];
                            ?>static.php?filename=picto_dld.gif" border="0" alt="" />&nbsp;<?php
                        echo _VIEW_DOC;
                        ?></a>
                </p>
                </b>&nbsp;
            </div>
            <br/>
            <dl id="tabricator1">
                <?php $detailsExport .= "<h1><center>"._DETAILS_PRINT." : ".$s_id."</center></h1><hr>";?>
                <dt><?php  echo _PROPERTIES;?></dt>
                <dd>
                    <h2>
                        <span class="date">
                            <?php $detailsExport .= "<h2>"._FILE_DATA."</h2>";?>
                            <b><?php  echo _FILE_DATA;?></b>
                        </span>
                    </h2>
                    <br/>
                <form method="post" name="index_doc" id="index_doc" action="index.php?page=details&dir=indexing_searching&id=<?php  echo $s_id; ?>">
                    <?php $detailsExport .= "<table cellpadding='2' cellspacing='0' border='1' class='block forms details' width='100%'>"; ?>
                    <table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
                        <?php
                        $i=0;
                        foreach(array_keys($data) as $key)
                        {

                            if ($i%2 != 1 || $i==0) // pair
                            {
                                $detailsExport .= "<tr class='col'>";
                                ?>
                                <tr class="col">
                                <?php
                            }
                            $folder_id = "";
                            if (($key == "market" || $key == "project") && $data[$key]['show_value'] <> "")
                            {
                                $folderTmp = $data[$key]['show_value'];
                                $find1 = strpos($folderTmp, '(');
                                $folder_id = substr($folderTmp, $find1, strlen($folderTmp));
                                $folder_id = str_replace("(", "", $folder_id);
                                $folder_id = str_replace(")", "", $folder_id);
                            }

                            //$detailsExport .= "<th align='left' width='50px'>";
                            ?>
                            <th align="left" class="picto" >
                                <?php
                                if (isset($data[$key]['addon']))
                                {
                                    echo $data[$key]['addon'];
                                    //$detailsExport .= $data[$key]['addon'];
                                }
                                elseif (isset($data[$key]['img']))
                                {
                                    //$detailsExport .= "<img alt='".$data[$key]['label']."' title='".$data[$key]['label']."' src='".$data[$key]['img']."'  />";
                                    if ($folder_id <> "")
                                    {
                                        echo "<a href='".$_SESSION['config']['businessappurl']."index.php?page=show_folder&module=folder&id=".$folder_id."'>";
                                        ?>
                                        <img alt="<?php echo $data[$key]['label'];?>" title="<?php echo $data[$key]['label'];?>" src="<?php echo $data[$key]['img'];?>"  /></a>
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <img alt="<?php echo $data[$key]['label'];?>" title="<?php echo $data[$key]['label'];?>" src="<?php echo $data[$key]['img'];?>"  /></a>
                                        <?php
                                    }
                                    ?>

                                    <?php
                                }
                                //$detailsExport .= "</th>";
                                ?>
                            </th>
                            <?php
                            $detailsExport .= "<td align='left' width='200px'>";
                            ?>
                            <td align="left" width="200px">
                                <?php
                                $detailsExport .= $data[$key]['label'];
                                echo $data[$key]['label'];?> :
                            </td>
                            <?php
                            $detailsExport .=  "</td>";
                            $detailsExport .=  "<td>";
                            ?>
                            <td>
                                <?php
                                $detailsExport .=  $data[$key]['show_value'];
                            if (!isset($data[$key]['readonly']) || $data[$key]['readonly'] == true)
                            {
                                if ($data[$key]['display'] == 'textinput')
                                {
                                    ?>
                                    <input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $data[$key]['show_value'];?>" readonly="readonly" class="readonly" size="40"  title="<?php  echo $data[$key]['show_value']; ?>" alt="<?php  echo $data[$key]['show_value']; ?>" />
                                    <?php
                                }
                                elseif ($data[$key]['display'] == 'textarea') 
                                {
                                    echo '<textarea name="'.$key.'" id="'.$key.'" rows="3" readonly="readonly" class="readonly" style="width: 200px; max-width: 200px;">'
                                        .$data[$key]['show_value']
                                    .'</textarea>';
                                }
                                else
                                {
                                    ?>
                                    <input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $data[$key]['show_value'];?>" readonly="readonly" class="readonly" size="40" title="<?php  echo $data[$key]['show_value']; ?>" alt="<?php  echo $data[$key]['show_value']; ?>" />
                                    <?php
                                    if (isset($data[$key]['addon']))
                                    {
                                        $frm_str .= $data[$key]['addon'];
                                    }
                                }
                            }
                            else
                            {
                                if ($data[$key]['field_type'] == 'textfield')
                                {
                                    ?>
                                    <input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $data[$key]['show_value'];?>" size="40"  title="<?php  echo $data[$key]['show_value']; ?>" alt="<?php  echo $data[$key]['show_value']; ?>" />
                                    <?php
                                }
                                elseif ($data[$key]['display'] == 'textarea') 
                                {
                                    echo '<textarea name="'.$key.'" id="'.$key.'" rows="3" style="width: 200px; max-width: 200px;">'
                                        .$data[$key]['show_value']
                                    .'</textarea>';
                                }
                                else if ($data[$key]['field_type'] == 'date')
                                {
                                    ?>
                                    <input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $data[$key]['show_value'];?>" size="40"  title="<?php  echo $data[$key]['show_value']; ?>" alt="<?php  echo $data[$key]['show_value']; ?>" onclick="showCalender(this);" />
                                    <?php
                                }
                                else if ($data[$key]['field_type'] == 'select')
                                {
                                    ?>
                                    <select id="<?php echo $key;?>" name="<?php echo $key;?>" <?php if ($key == 'type_id'){echo 'onchange="change_doctype_details(this.options[this.options.selectedIndex].value, \''.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=change_doctype_details\' , \''._DOCTYPE.' '._MISSING.'\');"';}?>>
                                    <?php
                                        if ($key == 'type_id')
                                        {
                                            if ($_SESSION['features']['show_types_tree'] == 'true')
                                            {

                                                for($k=0; $k<count($data[$key]['select']);$k++)
                                                {
                                                ?><option value="" class="doctype_level1"><?php echo $data[$key]['select'][$k]['label'];?></option><?php
                                                    for($j=0; $j<count($data[$key]['select'][$k]['level2']);$j++)
                                                    {
                                                        ?><option value="" class="doctype_level2">&nbsp;&nbsp;<?php echo $data[$key]['select'][$k]['level2'][$j]['label'];?></option><?php
                                                        for($l=0; $l<count($data[$key]['select'][$k]['level2'][$j]['types']);$l++)
                                                        {
                                                            ?><option
                                                            <?php if ($data[$key]['value'] ==$data[$key]['select'][$k]['level2'][$j]['types'][$l]['id']){ echo 'selected="selected"';}?>
                                                             value="<?php echo $data[$key]['select'][$k]['level2'][$j]['types'][$l]['id'];?>" >&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $data[$key]['select'][$k]['level2'][$j]['types'][$l]['label'];?></option><?php
                                                        }
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                for($k=0; $k<count($data[$key]['select']);$k++)
                                                {
                                                    ?><option <?php if ($data[$key]['value'] ==$data[$key]['select'][$k]['ID']){ echo 'selected="selected"';}?> value="<?php echo $data[$key]['select'][$k]['ID'];?>" ><?php echo $data[$key]['select'][$k]['LABEL'];?></option><?php
                                                }
                                            }
                                        }
                                        else
                                        {
                                            for($k=0; $k<count($data[$key]['select']);$k++)
                                            {
                                                ?><option value="<?php echo $data[$key]['select'][$k]['ID'];?>" <?php if ($data[$key]['value'] == $data[$key]['select'][$k]['ID']){echo 'selected="selected"';}?>><?php echo $data[$key]['select'][$k]['LABEL'];?></option><?php
                                            }
                                        }
                                    ?>
                                    </select>
                                    <?php
                                }
                                else if ($data[$key]['field_type'] == 'autocomplete')
                                {
                                    if ($key == 'project')
                                    {
                                        //$('market').value='';return false;
                                    ?><input type="text" name="project" id="project" onblur="" value="<?php echo $data['project']['show_value']; ?>" /><div id="show_project" class="autocomplete"></div><script type="text/javascript">launch_autocompleter_folders('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=folder&page=autocomplete_folders&mode=project', 'project');</script>
                                    <?php
                                    }
                                    else if ($key == 'market')
                                    {
                                    ?><input type="text" name="market" id="market" onblur="fill_project('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=folder&page=ajax_get_project');return false;"  value="<?php echo $data['market']['show_value']; ?>"/><div id="show_market" class="autocomplete"></div>
                                    <script type="text/javascript">launch_autocompleter_folders('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=folder&page=autocomplete_folders&mode=market', 'market');</script>
                                    <?php
                                    }
                                }
                            }
                                $detailsExport .=  "</td>";
                                ?>
                            </td>
                            <?php
                            if ($i%2 == 1 && $i!=0) // impair
                            {
                                $detailsExport .=  "</td>";
                                ?>
                                </tr>
                                <?php
                            }
                            else
                            {
                                if ($i+1 == count($data))
                                {
                                    $detailsExport .= "<td  colspan='2'>&nbsp;</td></tr>";
                                    echo '<td  colspan="2">&nbsp;</td></tr>';
                                }
                            }
                            $i++;
                        }
                        $detailsExport .=  "<tr class='col'>";
                        $detailsExport .=  "<td align='left' width='200px'>";
                        $detailsExport .=  _STATUS;
                        $detailsExport .=  "</td>";
                        $detailsExport .=  "<td>";
                        $detailsExport .=  $res_status['LABEL'];
                        $detailsExport .=  "</td>";
                        $detailsExport .=  "<td align='left' width='200px'>";
                        $detailsExport .=  _CHRONO_NUMBER;
                        $detailsExport .=  "</td>";
                        $detailsExport .=  "<td>";
                        $detailsExport .=  $chrono_number;
                        $detailsExport .=  "</td>";
                        $detailsExport .=  "</tr>";

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
                        <tr class="col">
                            <th align="left" class="picto">
                                <img alt="<?php echo _CHRONO_NUMBER; ?>" src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=chrono.gif" />
                            </th>
                            <td align="left" width="200px">
                                <?php  echo _CHRONO_NUMBER; ?> :
                            </td>
                            <td>
                                <input type="text" class="readonly" readonly="readonly" value="<?php  echo $chrono_number; ?>" size="40" title="<?php  echo $chrono_number; ?>" alt="<?php  echo $chrono_number; ?>" />
                            </td>

                        </tr>

                    </table>
                    <?php
                    $detailsExport .=  "</table>";
                    ?>

                    <?php
                    /*if ($core->is_module_loaded('tags') &&
                        ($core->test_service('tag_view', 'tags',false) == 1))
                    {
                        include_once("modules".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR
                        ."templates/details/index.php");
                    }*/
                    ?>

                    <div id="opt_indexes">
                    <?php if (count($indexes) > 0)
                    {
                        ?><br/>
                        <h2>
                        <span class="date">
                            <b><?php  echo _OPT_INDEXES;?></b>
                        </span>
                        </h2>
                        <br/>
                        <table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
                            <?php
                            $i=0;
                            foreach(array_keys($indexes) as $key)
                            {

                                if ($i%2 != 1 || $i==0) // pair
                                {
                                    $detailsExport .= "<tr class='col'>";
                                    ?>
                                    <tr class="col">
                                    <?php
                                }
                                $detailsExport .= "<th align='left' width='50px'>";
                                ?>
                                <th align="left" class="picto" >
                                    <?php
                                    if (isset($indexes[$key]['img']))
                                    {
                                        //$detailsExport .= "<img alt='".$indexes[$key]['label']."' title='".$indexes[$key]['label']."' src='".$indexes[$key]['img']."'  />";
                                        ?>
                                        <img alt="<?php echo $indexes[$key]['label'];?>" title="<?php echo $indexes[$key]['label'];?>" src="<?php echo $indexes[$key]['img'];?>"  /></a>
                                        <?php
                                    }
                                    $detailsExport .= "</th>";
                                    ?>
                                </th>
                                <?php
                                $detailsExport .= "<td align='left' width='200px'>";
                                ?>
                                <td align="left" width="200px">
                                    <?php
                                    $detailsExport .= $indexes[$key]['label'];
                                    echo $indexes[$key]['label'];?> :
                                </td>
                                <?php
                                $detailsExport .=  "</td>";
                                $detailsExport .=  "<td>";
                                ?>
                                <td>
                                    <?php
                                    $detailsExport .=  $indexes[$key]['show_value'];
                                    if ($indexes[$key]['type_field'] == 'input')
                                    {
                                        ?>
                                        <input type="text" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $indexes[$key]['show_value'];?>" <?php if (!isset($indexes[$key]['readonly']) || $indexes[$key]['readonly'] == true){ echo 'readonly="readonly" class="readonly"';}else if ($indexes[$key]['type'] == 'date'){echo 'onclick="showCalender(this);"';}?> size="40"  title="<?php  echo $indexes[$key]['show_value']; ?>" alt="<?php  echo $indexes[$key]['show_value']; ?>"   />
                                        <?php
                                    }
                                    else
                                    {?>
                                        <select name="<?php echo $key;?>" id="<?php echo $key;?>" >
                                            <option value=""><?php echo _CHOOSE;?>...</option>
                                            <?php
                                            for ($j = 0; $j < count($indexes[$key]['values']); $j ++)
                                            {?>
                                                <option value="<?php echo $indexes[$key]['values'][$j]['id'];?>" <?php
                                                if ($indexes[$key]['values'][$j]['id'] == $indexes[$key]['value']) {
                                                    echo 'selected="selected"';
                                                }?>><?php echo $indexes[$key]['values'][$j]['label'];?></option><?php
                                            }?>
                                        </select><?php
                                    }

                                    $detailsExport .=  "</td>";
                                    ?>
                                </td>
                                <?php
                                if ($i%2 == 1 && $i!=0) // impair
                                {
                                    $detailsExport .=  "</td>";
                                    ?>
                                    </tr>
                                    <?php
                                }
                                else
                                {
                                    if ($i+1 == count($indexes))
                                    {
                                        $detailsExport .= "<td  colspan='2'>&nbsp;</td></tr>";
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

                    <!--<h2>
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
                            <td><input type="text" class="readonly" readonly="readonly" value="<?php  echo $filesize." "._BYTES." ( ".round($filesize/1024,2)."K )"; ?>" /></td>
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
                    </table>-->
                    <br/>
                    <div align="center">
                        <?php if ($printDetails) {
                            /*if (
                              isset($_SESSION['custom_override_id'])
                              && $_SESSION['custom_override_id'] <> ''
                            ) {
                               $path = $_SESSION['config']['coreurl']
                                . '/custom/'
                                . $_SESSION['custom_override_id']
                                . '/apps/'
                                . $_SESSION['config']['app_id'];
                            } else {*/
                              $path = $_SESSION['config']['businessappurl'];
                            //}
                            ?>
                            <input type="button" class="button" name="print_details" id="print_details" value="<?php echo _PRINT_DETAILS;?>" onclick="window.open('<?php echo $path . "/tmp/export_details_".$_SESSION['user']['UserId']."_export.html";?>', '_blank');" />
                            <?php
                            }
                        ?>
                        <?php if ($putInValid) {
                            ?>
                            <input type="submit" class="button"  value="<?php  echo _PUT_DOC_ON_VALIDATION;?>" name="put_doc_on_validation" onclick="return(confirm('<?php  echo _REALLY_PUT_DOC_ON_VALIDATION;?>\n\r\n\r'));" />
                            <?php
                            }
                        ?>
                        <?php if ($delete_doc)
                        {?>
                        <input type="submit" class="button"  value="<?php  echo _DELETE_DOC;?>" name="delete_doc" onclick="return(confirm('<?php  echo _REALLY_DELETE.' '._THIS_DOC;?> ?\n\r\n\r'));" />
                        <?php }
                        if ($modify_doc)
                        {?>
                        <input type="submit" class="button"  value="<?php  echo _MODIFY_DOC;?>" name="submit_index_doc" />
                        <?php  } ?>
                            <input type="button" class="button" name="back_welcome" id="back_welcome" value="<?php echo _BACK_TO_WELCOME;?>" onclick="window.top.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php';" />

                    </div>
                    </form>
                    <?php
        }
        ?>
                </dd>
                <?php
                //SERVICE TO VIEW TECHNICAL INDEX
                if ($viewTechnicalInfos) {
                    include_once('apps/' . $_SESSION['config']['app_id'] . '/view_technical_infos.php');
                }
                //$core->execute_app_services($_SESSION['app_services'], 'details.php');
                $detailsExport .= "<h2>"._NOTES."</h2>";
                $detailsExport .= "<table cellpadding='4' cellspacing='0' border='1' width='100%'>";
                $detailsExport .= "<tr height='130px'>";
                $detailsExport .= "<td width='15%'>";
                $detailsExport .= "<h3>"._NOTES_1."</h3>";
                $detailsExport .= "</td>";
                $detailsExport .= "<td width='85%'>";
                $detailsExport .= "&nbsp;";
                $detailsExport .= "</td>";
                $detailsExport .= "</tr>";
                $detailsExport .= "<tr height='130px'>";
                $detailsExport .= "<td width='15%'>";
                $detailsExport .= "<h3>"._NOTES_2."</h3>";
                $detailsExport .= "</td>";
                $detailsExport .= "<td width='85%'>";
                $detailsExport .= "&nbsp;";
                $detailsExport .= "</td>";
                $detailsExport .= "</tr>";
                $detailsExport .= "<tr height='130px'>";
                $detailsExport .= "<td width='15%'>";
                $detailsExport .= "<h3>"._NOTES_3."</h3>";
                $detailsExport .= "</td>";
                $detailsExport .= "<td width='85%'>";
                $detailsExport .= "&nbsp;";
                $detailsExport .= "</td>";
                $detailsExport .= "</tr>";
                $detailsExport .= "</table>";
                if ($core->is_module_loaded('entities'))
                {
                    $detailsExport .= "<h2>"._DIFF_LIST."</h2>";
                    ?>
                    <dt><?php  echo _DIFF_LIST;?></dt>
                    <dd><?php
                        require_once("modules".DIRECTORY_SEPARATOR."entities".DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_listdiff.php');
                        $diff_list = new diffusion_list();
                        $_SESSION['details']['diff_list'] = array();
                        $_SESSION['details']['diff_list'] = $diff_list->get_listinstance($s_id);
                        //$db->show_array($_SESSION['details']['diff_list']);
                        ?>
                        <h2>
                            <span class="date">
                                <b><?php  echo _DIFF_LIST;?></b>
                            </span>
                        </h2>
                        <br/>
                        <div id="diff_list_div">
                            <?php
                            if (isset($_SESSION['details']['diff_list']['dest']['user_id']) && !empty($_SESSION['details']['diff_list']['dest']['user_id']))
                            {
                                //$detailsExport .= "<p class='sstit'>"._RECIPIENT."</p>";
                                $detailsExport .= "<table cellpadding='4' cellspacing='0' border='1' width='100%'>";
                                $detailsExport .= "<tr class='col'>";
                                $detailsExport .= "<td>"._RECIPIENT."</td>";
                                $detailsExport .= "<td>"._TO_CC."</td>";
                                $detailsExport .= "</tr>";
                                $detailsExport .= "<tr class='col' valign='top'>";
                                //$detailsExport .= "<td>-&nbsp;".$_SESSION['details']['diff_list']['dest']['firstname']."&nbsp;".$_SESSION['details']['diff_list']['dest']['lastname']."&nbsp;".$_SESSION['details']['diff_list']['dest']['entity_label']."</td>";
                                $detailsExport .= "<td>-&nbsp;<b>".$entityLabel."</b><br>-&nbsp;".$_SESSION['details']['diff_list']['dest']['entity_label']."</td>";
                                $detailsExport .= "<td>";
                                for($i=0;$i<count($_SESSION['details']['diff_list']['copy']['entities']);$i++) {
                                    $detailsExport .= "-&nbsp;".$_SESSION['details']['diff_list']['copy']['entities'][$i]['entity_id']."&nbsp;".$_SESSION['details']['diff_list']['copy']['entities'][$i]['entity_label']."<br>";
                                }
                                for($i=0;$i<count($_SESSION['details']['diff_list']['copy']['users']);$i++) {
                                    //$detailsExport .= "-&nbsp;".$_SESSION['details']['diff_list']['copy']['users'][$i]['firstname']."&nbsp;".$_SESSION['details']['diff_list']['copy']['users'][$i]['lastname']."&nbsp;".$_SESSION['details']['diff_list']['copy']['users'][$i]['entity_label']."<br>";
                                    $detailsExport .= "-&nbsp;".$_SESSION['details']['diff_list']['copy']['users'][$i]['entity_label']."<br>";
                                }
                                $detailsExport .= "</td>";
                                $detailsExport .= "</tr>";
                                /*$detailsExport .= "<td>".$_SESSION['details']['diff_list']['dest']['firstname']."&nbsp;&nbsp;</td>";
                                $detailsExport .= "<td>".$_SESSION['details']['diff_list']['dest']['lastname']."&nbsp;&nbsp;</td>";
                                $detailsExport .= "<td>".$_SESSION['details']['diff_list']['dest']['entity_label']."&nbsp;&nbsp;</td>";
                                $detailsExport .= "</tr>";*/
                                $detailsExport .= "</table>";
                                //$detailsExport .= "<br>";
                                ?>
                                <p class="sstit"><?php echo _RECIPIENT;?></p>
                                <table cellpadding="0" cellspacing="0" border="0" class="listing">
                                    <tr class="col">
                                        <td><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_users_entities_b_small.gif&module=entities" alt="<?php echo _USER;?>" title="<?php echo _USER;?>" /></td>
                                        <td><?php echo $_SESSION['details']['diff_list']['dest']['firstname'];?></td>
                                        <td><?php echo $_SESSION['details']['diff_list']['dest']['lastname'];?></td>
                                        <td><?php echo $_SESSION['details']['diff_list']['dest']['entity_label'];?></td>
                                    </tr>
                                </table>
                                <br/>
                                <?php
                            }
                            if (count($_SESSION['details']['diff_list']['copy']['users']) > 0 || count($_SESSION['details']['diff_list']['copy']['entities']) > 0)
                            {
                                //$detailsExport .= "<p class='sstit'>"._TO_CC."</p>";
                                //$detailsExport .= "<table cellpadding='0' cellspacing='0' border='0' class='listing'>";
                                ?>
                                <p class="sstit"><?php echo _TO_CC;?></p>
                                <table cellpadding="0" cellspacing="0" border="0" class="listing">
                                <?php $color = ' class="col"';
                                for($i=0;$i<count($_SESSION['details']['diff_list']['copy']['entities']);$i++)
                                {
                                    if ($color == ' class="col"')
                                    {
                                        $color = '';
                                    }
                                    else
                                    {
                                        $color = ' class="col"';
                                    }
                                    /*$detailsExport .= "<tr ".$color.">";
                                    $detailsExport .= "<td><img src='".$_SESSION['config']['businessappurl']."static.php?filename=manage_entities_b_small.gif&module=entities' alt='"._ENTITY."' title='"._ENTITY."' /></td>";
                                    $detailsExport .= "<td>".$_SESSION['details']['diff_list']['copy']['entities'][$i]['entity_id']."</td>";
                                    $detailsExport .= "<td colspan='2'>".$_SESSION['details']['diff_list']['copy']['entities'][$i]['entity_label']."</td>";
                                    $detailsExport .= "</tr>";*/
                                    ?>
                                    <tr <?php echo $color;?> >
                                        <td><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_entities_b_small.gif&module=entities" alt="<?php echo _ENTITY;?>" title="<?php echo _ENTITY;?>" /></td>
                                        <td ><?php echo $_SESSION['details']['diff_list']['copy']['entities'][$i]['entity_id'];?></td>
                                        <td colspan="2"><?php echo $_SESSION['details']['diff_list']['copy']['entities'][$i]['entity_label'];?></td>
                                    </tr><?php
                                }
                                for($i=0;$i<count($_SESSION['details']['diff_list']['copy']['users']);$i++)
                                {
                                    if ($color == ' class="col"')
                                    {
                                        $color = '';
                                    }
                                    else
                                    {
                                        $color = ' class="col"';
                                    }
                                    /*$detailsExport .= "<tr ".$color.">";
                                    $detailsExport .= "<td><img src='".$_SESSION['config']['businessappurl']."static.php?filename=manage_users_entities_b_small.gif&module=entities' alt='"._USER."' title='"._USER."' /></td>";
                                    $detailsExport .= "<td>".$_SESSION['details']['diff_list']['copy']['users'][$i]['firstname']."</td>";
                                    $detailsExport .= "<td>".$_SESSION['details']['diff_list']['copy']['users'][$i]['lastname']."</td>";
                                    $detailsExport .= "<td>".$_SESSION['details']['diff_list']['copy']['users'][$i]['entity_label']."</td>";
                                    $detailsExport .= "</tr>";*/
                                    ?>
                                    <tr <?php echo $color;?> >
                                        <td><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_users_entities_b_small.gif&module=entities" alt="<?php echo _USER;?>" title="<?php echo _USER;?>" /></td>
                                        <td ><?php echo $_SESSION['details']['diff_list']['copy']['users'][$i]['firstname'];?></td>
                                        <td ><?php echo $_SESSION['details']['diff_list']['copy']['users'][$i]['lastname'];?></td>
                                        <td><?php echo $_SESSION['details']['diff_list']['copy']['users'][$i]['entity_label'];?></td>
                                    </tr><?php
                                }
                                //$detailsExport .= "</table>";
                                ?>
                                </table>
                                <?php
                            }
                            if ($core->test_service('update_list_diff_in_details', 'entities', false)) {
                                echo '<a href="#" onclick="window.open(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=manage_listinstance&origin=details\', \'\', \'scrollbars=yes,menubar=no,toolbar=no,status=no,resizable=yes,width=1280,height=980,location=no\');" title="'._UPDATE_LIST_DIFF.'"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=modif_liste.png" alt="'._UPDATE_LIST_DIFF.'" />'._UPDATE_LIST_DIFF.'</a>';
                            }
                            ?>
                        </div>
                    </dd>
                <?php
                }
                //$detailsExport .= "<h2>"._PROCESS."</h2>";
                $req = new dbquery;
                $req->connect();
                $countAttachments = "select res_id, creation_date, title, format from " 
                    . $_SESSION['tablename']['attach_res_attachments'] 
                    . " where res_id_master = " . $_SESSION['doc_id'] 
                    . " and coll_id ='" . $_SESSION['collection_id_choice'] 
                    . "' and status <> 'DEL'";
                $req->query($countAttachments);
                if ($req->nb_result() > 0) {
                    $nb_attach = $req->nb_result();
                } else {
                    $nb_attach = 0;
                }
                ?>
                <dt><?php echo _PROCESS . '(' . $nb_attach . ')';?></dt>
                <dd>
                    <div>
                        <table width="100%">
                            <tr>
                                <td><label for="answer_types"><?php echo _ANSWER_TYPES_DONE;?> : </label></td>
                                <td>
                                    <?php
                                    /*$detailsExport .= "<table width='100%'>";
                                    $detailsExport .= "<tr>";
                                    $detailsExport .= "<td><label for='answer_types'>"._ANSWER_TYPES_DONE." : </label></td>";*/
                                    $answer_type = "";
                                    if ($process_data['simple_mail'] == true)
                                    {
                                        $answer_type .=  _SIMPLE_MAIL.', ';
                                    }
                                    if ($process_data['registered_mail'] == true)
                                    {
                                        $answer_type .=  _REGISTERED_MAIL.', ';
                                    }
                                    if ($process_data['direct_contact'] == true)
                                    {
                                        $answer_type .=  _DIRECT_CONTACT.', ';
                                    }
                                    if ($process_data['email'] == true)
                                    {
                                        $answer_type .=  _EMAIL.', ';
                                    }
                                    if ($process_data['fax'] == true)
                                    {
                                        $answer_type .=  _FAX.', ';
                                    }
                                    if ($process_data['no_answer'] == true)
                                    {
                                        $answer_type =  _NO_ANSWER.', ';
                                    }
                                    if ($process_data['other'] == true)
                                    {
                                        $answer_type .=  " ".$process_data['other_answer_desc']."".', ';
                                    }
                                    $answer_type = preg_replace('/, $/', '', $answer_type);
                                    //$detailsExport .= $answer_type."</td></tr>";
                                    ?>
                                    <input name="answer_types" type="text" readonly="readonly" class="readonly" value="<?php echo $answer_type;?>" style="width:500px;" />
                                </td>
                            </tr>
                            <?php
                            /*$detailsExport .= "<tr>";
                            $detailsExport .= "<td><label for='process_notes'>"._PROCESS_NOTES." : </label></td>";
                            $detailsExport .= $db->show_string($process_data['process_notes'])."</td></tr>";*/
                            ?>
                            <tr>
                                <td><label for="process_notes"><?php echo _PROCESS_NOTES;?> : </label></td>
                                <td><textarea name="process_notes" id="process_notes" readonly="readonly" style="width:500px;"><?php echo $db->show_string($process_data['process_notes']);?></textarea></td>
                            </tr>
                            <?php
                            if (isset($closing_date) && !empty($closing_date))
                            {
                                /*$detailsExport .= "<tr>";
                                $detailsExport .= "<td><label for='closing_date'>"._CLOSING_DATE." : </label></td>";
                                $detailsExport .= $closing_date."</td></tr>";*/
                                ?>
                                <tr>
                                    <td><label for="closing_date"><?php echo _CLOSING_DATE;?> : </label></td>
                                    <td><input name="closing_date" type="text" readonly="readonly" class="readonly" value="<?php echo $closing_date;?>" /></td></td>
                                </tr>
                                <?php
                            }
                            //$detailsExport .= "</table>";
                            ?>
                        </table>
                    </div>
                    <?php
                    if ($core->is_module_loaded('attachments'))
                    {
                        $detailsExport .= "<h3>"._ATTACHED_DOC." : </h3>";
                        $selectAttachments = "select res_id, creation_date, title, format from ".$_SESSION['tablename']['attach_res_attachments']." where res_id_master = ".$_SESSION['doc_id']." and coll_id ='".$_SESSION['collection_id_choice']."' and status <> 'DEL'";
                        $dbAttachments = new dbquery();
                        $dbAttachments->connect();
                        $dbAttachments->query($selectAttachments);
                        /*$detailsExport .= "<table width='100%'>";
                        $detailsExport .= "<tr>";
                        $detailsExport .= "<td>"._ID."</td>";
                        $detailsExport .= "<td>"._DATE."</td>";
                        $detailsExport .= "<td>"._TITLE."</td>";
                        $detailsExport .= "<td>"._FORMAT."</td>";
                        $detailsExport .= "</tr>";
                        while($resAttachments = $dbAttachments->fetch_object())
                        {
                            $detailsExport .= "<tr>";
                            $detailsExport .= "<td>".$resAttachments->res_id."</td>";
                            $detailsExport .= "<td>".$resAttachments->creation_date."</td>";
                            $detailsExport .= "<td>".$resAttachments->title."</td>";
                            $detailsExport .= "<td>".$resAttachments->format."</td>";
                            $detailsExport .= "</tr>";
                        }
                        $detailsExport .= "</table>";*/
                        ?>
                        <div>
                        <label><?php echo _ATTACHED_DOC;?> : </label>
                        <iframe name="list_attach" id="list_attach" src="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=attachments&page=frame_list_attachments&view_only&mode=normal" frameborder="0" width="100%" height="300px"></iframe>
                        </div>
                        <?php
                    }
                    $detailsExport .= "<br><br><br>";
                    ?>
                </dd>
                <dt><?php echo _DOC_HISTORY;?></dt>
                <dd>
                    <iframe src="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&dir=indexing_searching&page=hist_doc&id=<?php echo $s_id;?>&mode=normal" name="hist_doc_process" width="100%" height="580" align="left" scrolling="auto" frameborder="0" id="hist_doc_process"></iframe>
                </dd>
                <?php
                if ($core->is_module_loaded('notes')) {         
                        $selectNotes = "select id, identifier, user_id, date_note, note_text from "
                            . $_SESSION['tablename']['not_notes']
                            . " where identifier = " . $s_id 
                            . " and coll_id ='"
                            . $_SESSION['collection_id_choice'] . "' order by date_note desc";

                    $dbNotes = new dbquery();
                    $dbNotes->connect();
                    $dbNotes->query($selectNotes);
                    //$dbNotes->show();
                    $not_res = 0;
                    while ($res=$dbNotes->fetch_object())
                    {
                        $dbNotesEntities = new dbquery();
                        $dbNotesEntities->connect();
                        $query = "select id from note_entities where "
                        . "note_id = " .$res->id;
                    
                        $dbNotesEntities->query($query);
                        
                        if($dbNotesEntities->nb_result()==0)
                            $not_res++;
                        else
                        {
                            $dbUser = new dbquery();
                            $dbUser->connect();

                            $query = "select id from notes where id in ("
                            . "select note_id from note_entities where (item_id = '" 
                            . $_SESSION['user']['primaryentity']['id'] . "' and note_id = " . $res->id . "))"
                            . "or (id = " . $res->id . " and user_id = '" . $_SESSION['user']['UserId'] . "')";

                            $dbUser->query($query);
                            //$dbUser->show();
                            if($dbUser->nb_result()<>0)
                            $not_res++;
                        }   
                    }
                    $not_res_title = " (".$not_res.") ";
                    $nb_notes_for_title  = $dbNotes->nb_result();
                    
                    if ($nb_notes_for_title == 0) {
                        $extend_title_for_notes = '';
                    } else {
                        $extend_title_for_notes = " (".$nb_notes_for_title.") ";
                    }
                    ?>
                    <!--<dt><?php  echo _NOTES.$extend_title_for_notes;?></dt>-->
                    <dt><?php  echo _NOTES.$not_res_title;?></dt>
                    <dd>
                    <?php
                        /*$detailsExport .= "<h3>"._NOTES." : </h3>";
                        $detailsExport .= "<table width='100%'>";
                        $detailsExport .= "<tr>";
                        $detailsExport .= "<td>"._ID."</td>";
                        $detailsExport .= "<td>"._DATE."</td>";
                        $detailsExport .= "<td>"._NOTES."</td>";
                        $detailsExport .= "<td>"._USER."</td>";
                        $detailsExport .= "</tr>";
                        while($resNotes = $dbNotes->fetch_object())
                        {
                            $detailsExport .= "<tr>";
                            $detailsExport .= "<td>".$resNotes->id."</td>";
                            $detailsExport .= "<td>".$resNotes->date_note."</td>";
                            $detailsExport .= "<td>".$resNotes->note_text."</td>";
                            $detailsExport .= "<td>".$resNotes->user_id."</td>";
                            $detailsExport .= "</tr>";
                        }
                        $detailsExport .= "</table>";*/
                        $select_notes[$_SESSION['tablename']['users']] = array();
                        array_push($select_notes[$_SESSION['tablename']['users']],"user_id","lastname","firstname");
                        $select_notes[$_SESSION['tablename']['not_notes']] = array();
                        array_push($select_notes[$_SESSION['tablename']['not_notes']],"id", "date_note", "note_text", "user_id");
                        $where_notes = " identifier = ".$s_id." ";
                        $request_notes = new request;
                        $tab_notes=$request_notes->select($select_notes,$where_notes,"order by ".$_SESSION['tablename']['not_notes'].".date_note desc",$_SESSION['config']['databasetype'], "500", true,$_SESSION['tablename']['not_notes'], $_SESSION['tablename']['users'], "user_id" );
                        //$request_notes->show();

                        ?>
                        <div style="text-align:center;">
                            <img src="<?php
                                echo $_SESSION['config']['businessappurl'];
                                ?>static.php?filename=modif_note.png&module=notes" border="0" alt="" /><?php
                                if ($status <> 'END') {
                                    ?><a href="javascript://" onclick="ouvreFenetre('<?php
                                    echo $_SESSION['config']['businessappurl'];
                                    ?>index.php?display=true&module=notes&page=note_add&size=full&identifier=<?php
                                    echo $s_id;
                                    ?>&coll_id=<?php
                                    echo $coll_id;
                                    ?>', 1024, 650)" ><?php
                                    echo _ADD_NOTE;
                                    ?></a><?php
                                } ?>
                        </div>
                        <iframe name="list_notes_doc" id="list_notes_doc" src="<?php
                            echo $_SESSION['config']['businessappurl'];
                            ?>index.php?display=true&module=notes&page=frame_notes_doc&size=full" frameborder="0" width="100%" height="520px"></iframe>
                    </dd>
                    <?php
                }
                if ($core->is_module_loaded('cases') == true)
                {
                    ?>
                    <dt><?php  echo _CASE;?></dt>
                    <dd>
                    <?php
                        include('modules'.DIRECTORY_SEPARATOR.'cases'.DIRECTORY_SEPARATOR.'including_detail_cases.php');
                        if ($core->test_service('join_res_case', 'cases',false) == 1) {
                        ?><div align="center">
                            <input type="button" class="button" name="back_welcome" id="back_welcome" value="<?php if ($res->case_id<>'') echo _MODIFY_CASE; else echo _JOIN_CASE;?>" onclick="window.open('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=cases&page=search_adv_for_cases&searched_item=res_id&searched_value=<?php echo $s_id;?>','', 'scrollbars=yes,menubar=no,toolbar=no,resizable=yes,status=no,width=1020,height=710');"/></div><?php
                        }
                        ?>
                    </dd>
                    <?php
                }

                //VERSIONS
                $versionTable = $security->retrieve_version_table_from_coll_id(
                    $coll_id
                );
                $selectVersions = "select res_id from "
                    . $versionTable . " where res_id_master = "
                    . $s_id . " and status <> 'DEL' order by res_id desc";
                $dbVersions = new dbquery();
                $dbVersions->connect();
                $dbVersions->query($selectVersions);
                $nb_versions_for_title = $dbVersions->nb_result();
                $lineLastVersion = $dbVersions->fetch_object();
                $lastVersion = $lineLastVersion->res_id;
                if ($lastVersion <> '') {
                    $objectId = $lastVersion;
                    $objectTable = $versionTable;
                } else {
                    $objectTable = $security->retrieve_table_from_coll(
                        $coll_id
                    );
                    $objectId = $s_id;
                    $_SESSION['cm']['objectId4List'] = $s_id;
                }
                if ($nb_versions_for_title == 0) {
                    $extend_title_for_versions = '0';
                } else {
                    $extend_title_for_versions = $nb_versions_for_title;
                }
                $_SESSION['cm']['resMaster'] = '';
                ?>
                <dt>
                    <?php
                    echo _VERSIONS . ' (<span id="nbVersions">'
                        . $extend_title_for_versions . '</span>)';
                    ?>
                </dt>
                <dd>
                    <div class="error" id="divError" name="divError"></div>
                    <div style="text-align:center;">
                        <a href="<?php
                            echo $_SESSION['config']['businessappurl'];
                            ?>index.php?display=true&dir=indexing_searching&page=view_resource_controler&id=<?php
                            echo $s_id;
                            ?>&original" target="_blank">
                            <img alt="<?php echo _VIEW_ORIGINAL;?>" src="<?php echo
                                    $_SESSION['config']['businessappurl'];
                                    ?>static.php?filename=picto_dld.gif" border="0" alt="" />&nbsp;<?php
                            echo _VIEW_ORIGINAL;
                            ?></a> &nbsp;|&nbsp;
                        <?php
                        if ($addNewVersion) {
                            $_SESSION['cm']['objectTable'] = $objectTable;
                            ?>
                            <div id="createVersion" style="display: inline;"></div>
                            <?php
                        }
                        ?>
                    </div>
                    <div id="loadVersions"></div>
                    <script language="javascript">
                        showDiv("loadVersions", "nbVersions", "createVersion", "<?php
                            echo $_SESSION['urltomodules'] ;
                            ?>content_management/list_versions.php");
                    </script>
                </dd>
                <?php $Class_LinkController = new LinkController(); ?>
                <?php
                    $nbLink = $Class_LinkController->nbDirectLink(
                        $_SESSION['doc_id'],
                        $_SESSION['collection_id_choice'],
                        'all'
                    );
                    $Links = '';

                    //if ($nbLink > 0) {
                        $Links .= '<dt>';
                            $Links .= _LINK_TAB;
                            $Links .= ' (<span id="nbLinks">';
                            $Links .= $nbLink;
                            $Links .= '</span>)';
                        $Links .= '</dt>';
                        $Links .= '<dd>';
                            $Links .= '<h2>';
                                $Links .= _LINK_TAB;
                            $Links .= '</h2>';
                            $Links .= '<div id="loadLinks">';
                                $nbLinkDesc = $Class_LinkController->nbDirectLink(
                                    $_SESSION['doc_id'],
                                    $_SESSION['collection_id_choice'],
                                    'desc'
                                );
                                if ($nbLinkDesc > 0) {
                                    $Links .= '<img src="static.php?filename=cat_doc_incoming.gif" />';
                                    $Links .= $Class_LinkController->formatMap(
                                        $Class_LinkController->getMap(
                                            $_SESSION['doc_id'],
                                            $_SESSION['collection_id_choice'],
                                            'desc'
                                        ),
                                        'desc'
                                    );
                                    $Links .= '<br />';
                                }

                                $nbLinkAsc = $Class_LinkController->nbDirectLink(
                                    $_SESSION['doc_id'],
                                    $_SESSION['collection_id_choice'],
                                    'asc'
                                );
                                if ($nbLinkAsc > 0) {
                                    $Links .= '<img src="static.php?filename=cat_doc_outgoing.gif" />';
                                    $Links .= $Class_LinkController->formatMap(
                                        $Class_LinkController->getMap(
                                            $_SESSION['doc_id'],
                                            $_SESSION['collection_id_choice'],
                                            'asc'
                                        ),
                                        'asc'
                                    );
                                    $Links .= '<br />';
                                }
                            $Links .= '</div>';

                        if ($core->test_service('add_links', 'apps', false)) {
                            include_once 'apps/'.$_SESSION['config']['app_id'].'/add_links.php';
                        }

                        $Links .= '</dd>';
                    //}

                    echo $Links;
                    
                    if ($core->is_module_loaded('tags') &&
                        ($core->test_service('tag_view', 'tags', false) == 1)) {
                        ?>
                        <dt>
                            <?php
                            echo _TAGS;
                            ?>
                        </dt>
                        <dd>
                            <?php
                            include_once('modules/tags/templates/details/index.php');
                            ?>
                        </dd>
                        <?php
                    }
                ?>
            </dl>
    <?php
}
?>
</div>
</div>
<script type="text/javascript">
    var item  = $('details_div');
    var tabricator1 = new Tabricator('tabricator1', 'DT');
    if (item) {
        item.style.display='block';
    }
</script>
<?php
$detailsExport .= "</body></html>";
$_SESSION['doc_convert'] = array();
$_SESSION['doc_convert']['details_result'] = $detailsExport;
$core = new core_tools();

if ($printDetails) {
    $Fnm = $_SESSION['config']['tmppath']. '/export_details_'
        . $_SESSION['user']['UserId'] . '_export.html';
    if (file_exists($Fnm)) {
        unlink($Fnm);
    }
    $inF = fopen($Fnm,"w");
    fwrite($inF, $detailsExport);
    fclose($inF);
}
