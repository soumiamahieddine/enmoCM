<?php

/*
*   Copyright 2008-2013 Maarch
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
* File : details_business.php
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

//if ($_REQUEST['coll_id'] == 'letterbox_coll') {
//    $definitionCategories = 'definition_mail_categories';
//} elseif ($_REQUEST['coll_id'] == 'business_coll') {
    $definitionCategories = 'definition_mail_categories_business';
//}

$_REQUEST['coll_id'] = 'business_coll';
$_SESSION['current_basket']['coll_id'] = 'business_coll';

if (file_exists(
    $_SESSION['config']['corepath'] . 'custom/apps/' . $_SESSION['config']['app_id']
    . '/' . $definitionCategories . '.php')
) {
    $path = $_SESSION['config']['corepath'] . 'custom/apps/' . $_SESSION['config']['app_id']
          . '/' . $definitionCategories . '.php';
} else {
    $path = 'apps/' . $_SESSION['config']['app_id'] . '/' . $definitionCategories . '.php';
}
include_once $path;

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

//test service view_emails_notifs
$viewEmailsNotifs = false;
if ($core->test_service('view_emails_notifs', 'notifications', false)) {
    $viewEmailsNotifs = true;
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
    . 'index.php?page=details_business&dir=indexing_searching&coll_id='
    . $_REQUEST['coll_id']
    . '&id=' . $_REQUEST['id'];
$page_label = _DETAILS;
$page_id = 'details_business';
$core->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
$hist = new history();
$security = new security();
$func = new functions();
$request= new request;
$type = new types();
$s_id = '';
$_SESSION['req'] ='details_business';
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
    $extTable = $security->retrieve_extension_table_from_coll_id($coll_id);
}
$_SESSION['collection_id_choice'] = $coll_id;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $s_id = addslashes($func->wash($_GET['id'], 'num', _THE_DOC));
}
$db = new dbquery();
$db->connect();
$db->query("select res_id from " . $extTable . " where res_id = " . $s_id);
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
    $is->update_business($_POST, 'POST', $s_id, $coll_id);
    if ($core->is_module_loaded('tags')) {
        include_once('modules/tags/tags_update.php');
    }
}
//delete the doctype
if (isset($_POST['delete_doc'])) {
    $is ->delete_doc($s_id, $coll_id);
    ?>
        <script type="text/javascript">window.top.location.href='<?php
            echo $_SESSION['config']['businessappurl']
                . 'index.php?page=search_adv_business&dir=indexing_searching';
            ?>';</script>
    <?php
    exit();
}
if (isset($_POST['put_doc_on_validation'])) {
    $is ->update_doc_status($s_id, $coll_id, 'VAL');
    ?>
        <script language="javascript" type="text/javascript">window.top.location.href='<?php
            echo $_SESSION['config']['businessappurl']
            . 'index.php?page=search_adv_business&dir=indexing_searching';
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
    $db->query(
        "select * from " . $table . " where res_id = "
        . $s_id
    );
    //$db->show();
}
?>
<!--<div id="details_div" style="display:none;">-->
<div id="details_div">
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
if ((!empty($_SESSION['error']) && ! ($_SESSION['indexation'] ))) {
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
                'img_type_id' => true,
                'img_subject' => true,
                'img_contact_id' => true,
                'img_identifier' => true,
                'img_doc_date' => true,
                'img_currency' => true,
                'img_net_sum' => true,
                'img_tax_sum' => true,
                'img_total_sum' => true,
                'img_process_limit_date' => true,
                'img_destination' => true,
                'img_folder' => true
            );

            $res = $db->fetch_object();
            $typist = $res->typist;
            $format = $res->format;
            $filesize = $res->filesize;
            $creation_date = $db->format_date_db($res->creation_date, false);
            
            $initiator = $res->initiator;
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
            $status = $res->status;
            if (!empty($status)) {
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
            $data = get_general_data($coll_id, $s_id, $mode_data, $param_data);
            //$data = array_merge($data, $indexes);
            //$db->show_array($data);
            ?>
            <div class="block">
                <b>
                <p id="back_list">
                    <?php
                    if (! isset($_POST['up_res_id']) || ! $_POST['up_res_id']) {
                        if ($_SESSION['indexation'] == false) {
                            echo '<a href="#" onclick="history.go(';
                            if ($_SESSION['origin'] == 'basket') {
                                echo '-2';
                            } else {
                                echo '-1';
                            }
                            echo ');" class="back">' .  _BACK . '</a>';
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
                <dt><?php  echo _PROPERTIES;?></dt>
                <dd>
                    <h2>
                        <span class="date">
                            <b><?php  echo _FILE_DATA;?></b>
                        </span>
                    </h2>
                    <br/>
                <form method="post" name="index_doc" id="index_doc" action="index.php?page=details_business&dir=indexing_searching&id=<?php  echo $s_id; ?>">
                    <table cellpadding="2" cellspacing="2" border="0" class="block forms details" width="100%">
                        <?php
                        $i=0;
                        if (!$modify_doc) {
                            $data['process_limit_date']['readonly'] = true;
                        }
                        foreach(array_keys($data) as $key)
                        {

                            if ($i%2 != 1 || $i==0) // pair
                            {
                                ?>
                                <tr class="col">
                                <?php
                            }
                            $folder_id = "";
                            if ($key == "folder" && $data[$key]['show_value'] <> "")
                            {
                                $folderTmp = $data[$key]['show_value'];
                                $find1 = strpos($folderTmp, '(');
                                $folder_id = substr($folderTmp, $find1, strlen($folderTmp));
                                $folder_id = str_replace("(", "", $folder_id);
                                $folder_id = str_replace(")", "", $folder_id);
                            }

                            ?>
                            <th align="left" class="picto" >
                                <?php
                                if (isset($data[$key]['addon']))
                                {
                                    echo $data[$key]['addon'];
                                }
                                elseif (isset($data[$key]['img']))
                                {
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
                                ?>
                            </th>
                            <?php
                            ?>
                            <td align="left" width="200px">
                                <?php
                                echo $data[$key]['label'];?> :
                            </td>
                            <?php
                            ?>
                            <td>
                                <?php
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
                                    if ($key == 'folder')
                                    {
                                        ?><input type="text" name="folder" id="folder" onblur="" value="<?php echo $data['folder']['show_value']; 
                                        ?>" /><div id="show_folder" class="autocomplete"></div><script type="text/javascript">launch_autocompleter_folders('<?php 
                                        echo $_SESSION['config']['businessappurl'];
                                        ?>index.php?display=true&module=folder&page=autocomplete_folders&mode=folder', 'folder');</script>
                                        <?php
                                    }
                                }
                            }
                                ?>
                            </td>
                            <?php
                            if ($i%2 == 1 && $i!=0) // impair
                            {
                                ?>
                                </tr>
                                <?php
                            }
                            else
                            {
                                if ($i+1 == count($data))
                                {
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
                        <!--</tr>
                        <tr class="col">-->
                            <th align="left" class="picto">
                                <img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?module=entities&filename=manage_entities_b_small.gif" title="<?php echo _INITIATOR; ?>" alt="<?php  echo _INITIATOR; ?>"/>
                            </th>
                            <td align="left" width="200px">
                                <?php  echo _INITIATOR; ?> :
                            </td>
                            <td>
                                <input type="text" class="readonly" readonly="readonly" value="<?php  echo $initiator; ?>" size="40"  />
                            </td>
                        </tr>
                    </table>

                    <div id="opt_indexes">
                    <?php if (count($indexes) > 0) {
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
                                    ?>
                                    <tr class="col">
                                    <?php
                                }
                                ?>
                                <th align="left" class="picto" >
                                    <?php
                                    if (isset($indexes[$key]['img']))
                                    {
                                        ?>
                                        <img alt="<?php echo $indexes[$key]['label'];?>" title="<?php echo $indexes[$key]['label'];?>" src="<?php echo $indexes[$key]['img'];?>"  /></a>
                                        <?php
                                    }
                                    ?>
                                </th>
                                <td align="left" width="200px">
                                    <?php
                                    echo $indexes[$key]['label'];?> :
                                </td>
                                <td>
                                    <?php
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

                                    ?>
                                </td>
                                <?php
                                if ($i%2 == 1 && $i!=0) // impair
                                {
                                    ?>
                                    </tr>
                                    <?php
                                }
                                else
                                {
                                    if ($i+1 == count($indexes))
                                    {
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
                    <br/>
                    <div align="center">
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
                if ($core->is_module_loaded('entities')) {
                    ?>
                    <dt><?php  echo _DIFF_LIST;?></dt>
                    <dd><?php
                        require_once('modules/entities/class/class_manage_listdiff.php');
                        $diff_list = new diffusion_list();
                        $roles = $diff_list->get_listinstance_roles();
                        $_SESSION['details']['diff_list'] = array();
                        $_SESSION['details']['diff_list'] = $diff_list->get_listinstance($s_id, false, $coll_id);
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
                            foreach($roles as $role_id => $role_config) {
                                if (count($_SESSION['details']['diff_list'][$role_id]['users']) > 0 
                                    || count($_SESSION['details']['diff_list'][$role_id]['entities']) > 0
                                ) { ?>
                                    <p class="sstit"><?php echo $role_config['list_label'];?></p>
                                    <table cellpadding="0" cellspacing="0" border="0" class="listing">
                                    <?php $color = ' class="col"';
                                    for($i=0;$i<count($_SESSION['details']['diff_list'][$role_id]['entities']);$i++)
                                    {
                                        if ($color == ' class="col"') $color = '';
                                        else $color = ' class="col"'; ?>
                                        <tr <?php echo $color;?> >

                                            <td><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_entities_b_small.gif&module=entities" alt="<?php echo _ENTITY;?>" title="<?php echo _ENTITY;?>" /></td>
                                            <td ><?php echo $_SESSION['details']['diff_list'][$role_id]['entities'][$i]['entity_id'];?></td>
                                            <td colspan="2"><?php echo $_SESSION['details']['diff_list'][$role_id]['entities'][$i]['entity_label'];?></td>
                                        </tr><?php
                                    }
                                    for($i=0;$i<count($_SESSION['details']['diff_list'][$role_id]['users']);$i++)
                                    {
                                        if ($color == ' class="col"') $color = '';
                                        else $color = ' class="col"';
                                        ?>
                                        <tr <?php echo $color;?> >
                                            <td><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_users_entities_b_small.gif&module=entities" alt="<?php echo _USER;?>" title="<?php echo _USER;?>" /></td>
                                            <td ><?php echo $_SESSION['details']['diff_list'][$role_id]['users'][$i]['firstname'];?></td>
                                            <td ><?php echo $_SESSION['details']['diff_list'][$role_id]['users'][$i]['lastname'];?></td>
                                            <td><?php echo $_SESSION['details']['diff_list'][$role_id]['users'][$i]['entity_label'];?></td>
                                        </tr><?php
                                    } ?>
                                    </table> <br/>
                                    <?php
                                } 
                            }
                                                        
                            if ($core->test_service('update_list_diff_in_details', 'entities', false)) {
                                echo '<a href="#" onclick="window.open(\''.$_SESSION['config']['businessappurl']
                                    . 'index.php?display=true&module=entities&page=manage_listinstance&origin=details\', \'\', \'scrollbars=yes,menubar=no,toolbar=no,status=no,resizable=yes,width=1280,height=980,location=no\');" title="'._UPDATE_LIST_DIFF.'"><img src="'.$_SESSION['config']['businessappurl'].'static.php?filename=modif_liste.png" alt="'._UPDATE_LIST_DIFF.'" />'._UPDATE_LIST_DIFF.'</a>';
                            }
                            ?>
                        </div>
                    </dd>
                <?php
                }
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
                <dt><?php echo _ATTACHMENTS . ' (' . $nb_attach . ')';?></dt>
                <dd>
                    <?php
                    if ($core->is_module_loaded('attachments'))
                    {
                        $selectAttachments = "select res_id, creation_date, title, format from " 
                            . $_SESSION['tablename']['attach_res_attachments'] 
                            . " where res_id_master = ".$_SESSION['doc_id']." and coll_id ='".$_SESSION['collection_id_choice']."' and status <> 'DEL'";
                        $dbAttachments = new dbquery();
                        $dbAttachments->connect();
                        $dbAttachments->query($selectAttachments);
                        ?>
                        <div>
                        <label><?php echo _ATTACHED_DOC;?> : </label>
                        <iframe name="list_attach" id="list_attach" src="<?php 
                            echo $_SESSION['config']['businessappurl'];
                                ?>index.php?display=true&module=attachments&page=frame_list_attachments&view_only&mode=normal" frameborder="0" width="100%" height="300px"></iframe>
                        </div>
                        <?php
                    }
                    ?>
                </dd>
                <dt><?php echo _DOC_HISTORY;?></dt>
                <dd>
                    <iframe src="<?php echo $_SESSION['config']['businessappurl'];
                    ?>index.php?display=true&dir=indexing_searching&page=document_history&coll_id=<?php echo $coll_id;?>&id=<?php
                    echo $s_id;?>&mode=normal" name="hist_doc_process" width="100%" height="580" 
                    align="left" scrolling="auto" frameborder="0" id="hist_doc_process"></iframe>
                </dd>
                <?php
                if ($core->is_module_loaded('notes')) {
                    require_once 'modules/notes/class/class_modules_tools.php';
                    $notes_tools    = new notes();
                    
                    //Count notes
                    $nbr_notes = $notes_tools->countUserNotes($s_id, $coll_id);
                    if ($nbr_notes > 0 ) $nbr_notes = ' ('.$nbr_notes.')';  else $nbr_notes = '';
                    //Notes iframe
                    ?>
                    <dt><?php  echo _NOTES.$nbr_notes;?></dt>
                    <dd>
                        <iframe name="list_notes_doc" id="list_notes_doc" src="<?php
                            echo $_SESSION['config']['businessappurl'];
                            ?>index.php?display=true&module=notes&page=notes&identifier=<?php 
                            echo $s_id;?>&origin=document&coll_id=<?php echo $coll_id;?>&load&size=full" 
                            frameborder="0" scrolling="no" width="100%" height="560px"></iframe>
                    </dd> 
                    <?php
                }
                //############# NOTIFICATIONS ##############
                $extend_title_for_notifications = 0;
                ?>
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
                    //TAGS
                    ?>
                    <dt><?php echo _TAGS;?></dt>
                    <dd>
                    <?php
                    if ($core->is_module_loaded('tags') && ($core->test_service('tag_view', 'tags', false) == 1)) {
                            include_once('modules/tags/templates/details/index.php');
                    }
                    ?>
                    </dd>
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
