<?php
/**
* File : little_details_invoices.php
*
* @package  Maarch Entreprise
* @version 2.1
* @since 05/2011
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

require 'core/class/class_request.php';
require 'apps/' . $_SESSION['config']['app_id'] . '/class/class_list_show.php';
require_once 'core/class/class_security.php';
require_once 'core/class/class_history.php';
require_once 'core/manage_bitmask.php';
require_once "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR  . "security_bitmask.php";
require_once "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
    . "class_indexing_searching_app.php";
require_once "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_types.php";

if (file_exists(
    $_SESSION['config']['corepath'] . 'custom'. DIRECTORY_SEPARATOR
    . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'apps'
    . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR
    . 'definition_mail_categories.php'
)
) {
    $path = $_SESSION['config']['corepath'] . 'custom'. DIRECTORY_SEPARATOR
          . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'apps'
          . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
          . DIRECTORY_SEPARATOR . 'definition_mail_categories.php';
} else {
    $path = 'apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
          . DIRECTORY_SEPARATOR . 'definition_mail_categories.php';
}
include_once $path;
$core = new core_tools();
$core->test_user();
$core->load_lang();

$resId = '';
if (isset($_REQUEST['value']) && !empty($_REQUEST['value'])) {
	$resId = $_REQUEST['value'];
}

$hist = new history();
$security = new security();
$func = new functions();
$request = new request;
$type = new types();
//$_SESSION['req'] ='details';
//$_SESSION['indexing'] = array();
$is = new indexing_searching_app();
$table = '';
$isView = false;
if (isset($_SESSION['collection_id_choice'])
	&& ! empty($_SESSION['collection_id_choice'])
) {
	$collId = $_SESSION['collection_id_choice'];
} else {
	$collId = $_SESSION['user']['collections'][0];
}

$table = $security->retrieve_view_from_coll_id($collId);
$isView = true;
if (empty($table)) {
	$table = $security->retrieve_table_from_coll($collId);
	$isView = false;
}

$_SESSION['id_to_view'] = $resId;
$_SESSION['doc_id'] = $resId;

$right = $security->test_right_doc($collId, $resId);

if (! $right && $resId <> '') {
	include('apps/'.$_SESSION['config']['app_id'].'/no_right.php');
	exit;
}
if ($resId == '') {
    echo '<br><br><center><h2 style="color:#FFC200;">' . _NO_RESULTS
    	. '</h2></center>';
    exit;
}
if (isset($resId) && ! empty($resId)
	&& $_SESSION['history']['resview'] == 'true'
) {
	$hist->add(
    	$table, $resId , 'VIEW','resview', _VIEW_DOC_NUM . $resId,
        $_SESSION['config']['databasetype'], 'apps'
    );
}
$modifyDoc = false;
$deleteDoc = false;
/*
$modifyDoc = check_right(
    $_SESSION['user']['security'][$collId]['DOC']['securityBitmask'],
    DATA_MODIFICATION
);
$deleteDoc = check_right(
    $_SESSION['user']['security'][$collId]['DOC']['securityBitmask'],
    DELETE_RECORD
);

if(isset($_POST['submit_index_doc']))
{
	if($core->is_module_loaded('entities') && is_array($_SESSION['details']['diff_list'])) {
		require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_listdiff.php');
		$list = new diffusion_list();
		$params = array('mode'=> 'listinstance', 'table' => $_SESSION['tablename']['ent_listinstance'], 'coll_id' => $collId, 'res_id' => $resId, 'user_id' => $_SESSION['user']['UserId'], 'concat_list' => true, 'only_cc' => false);
		$list->load_list_db($_SESSION['details']['diff_list'], $params); //pb enchainement avec action redirect
	}
    $is->update_mail($_POST, "POST", $resId, $collId);
}
//delete the doctype
if(isset($_POST['delete_doc']))
{
    $is ->delete_doc( $resId, $collId);
    ?>
        <script type="text/javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'].'index.php?page=search_adv&dir=indexing_searching';?>';</script>
    <?php
    exit();
}
   */
if (empty($_SESSION['error'])) {
	$db = new dbquery();
    $db->connect();
    $compFields = '';
    $db->query("select type_id from " . $table . " where res_id = " . $resId);
    if ($db->nb_result() > 0) {
        $res = $db->fetch_object();
        $typeId = $res->type_id;
        $indexes = $type->get_indexes($typeId, $collId, 'minimal');

        for ($i = 0; $i < count($indexes); $i ++) {
            // In the view all custom from res table begin with doc_
            if (preg_match('/^custom_/', $indexes[$i])) {
                $compFields .= ', doc_' . $indexes[$i];
            } else {
                $compFields .= ', ' . $indexes[$i];
            }
        }
    }
    $caseSqlComplementary = '';
    if ($core->is_module_loaded('cases') == true) {
        $caseSqlComplementary = " , case_id";
    }
    $db->query(
    	"select status, format, typist, creation_date, fingerprint, filesize, "
        . "res_id, work_batch, page_count, is_paper, scan_date, scan_user, "
        . "scan_location, scan_wkstation, scan_batch, source, doc_language, "
        . "description, closing_date, alt_identifier, type_id " . $compFields
        . $caseSqlComplementary . " from " . $table . " where res_id = "
        . $resId
    );
}
?>
<div id="" class="clearfix">
<?php
if (! empty($_SESSION['error']) ) {
    ?>
        <div class="error">
            <br />
            <br />
            <br />
            <?php
    echo $_SESSION['error'];
    $_SESSION['error'] = "";
    ?>
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
    	$paramData = array(
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
    		'img_project' => true
        );

        $res = $db->fetch_object();
        $typeId = $res->type_id;
        $typist = $res->typist;
        $format = $res->format;
        $filesize = $res->filesize;
        $creationDate = $db->format_date_db($res->creation_date, false);
        $fingerprint = $res->fingerprint;
        $workBatch = $res->work_batch;
        $pageCount = $res->page_count;
        $isPaper = $res->is_paper;
        $scanDate = $db->format_date_db($res->scan_date);
        $scanUser = $res->scan_user;
        $scanLocation = $res->scan_location;
        $scanWkstation = $res->scan_wkstation;
        $scanBatch = $res->scan_batch;
        $docLanguage = $res->doc_language;
        $closingDate = $db->format_date_db($res->closing_date, false);
        $indexes = $type->get_indexes($typeId, $collId);

        if ($core->is_module_loaded('cases') == true) {
            require_once 'modules/cases/class/class_modules_tools.php';
            $case = new cases();
            if ($res->case_id <> '') {
                $caseProperties = $case->get_case_info($res->case_id);
            }
        }
        //$db->show_array($indexes);
        foreach (array_keys($indexes) as $key) {
            if (preg_match('/^custom/', $key)) {
                $tmp = 'doc_' . $key;
            } else {
                $tmp = $key;
            }
            if ($indexes[$key]['type'] == "date") {
                $res->$tmp = $db->format_date_db($res->$tmp, false);
            }
            $indexes[$key]['value'] = $res->$tmp;
            $indexes[$key]['show_value'] = $res->$tmp;
            if ($indexes[$key]['type'] == "string") {
                $indexes[$key]['show_value'] = $db->show_string($res->$tmp);
            } else if ($indexes[$key]['type'] == "date") {
                $indexes[$key]['show_value'] = $db->format_date_db($res->$tmp, true);
            }
        }
        //$db->show_array($indexes);
        $processData = $is->get_process_data($collId, $resId);
        $status = $res->status;
        if (! empty($status)) {
            require_once 'core/class/class_manage_status.php';
            $statusObj = new manage_status();
            $resStatus = $statusObj->get_status_data($status);
            if ($modifyDoc) {
                $canBeModified = $statusObj->can_be_modified($status);
                if (! $canBeModified) {
                    $modifyDoc = false;
                }
            }
        }
        $dataMode = 'full';
        if ($modifyDoc) {
            $dataMode = 'form';
        }
        foreach (array_keys($indexes) as $key) {
            $indexes[$key]['opt_index'] = true;
            if ($indexes[$key]['type_field'] == 'select') {
                for ($i = 0; $i < count($indexes[$key]['values']); $i ++) {
                    if ($indexes[$key]['values'][$i]['id'] == $indexes[$key]['value']) {
                        $indexes[$key]['show_value'] = $indexes[$key]['values'][$i]['label'] ;
                        break;
                    }
                }
            }
            if (! $modifyDoc) {
                $indexes[$key]['readonly'] = true;
                $indexes[$key]['type_field'] = 'input';
            } else {
                $indexes[$key]['readonly'] = false;
            }
        }
        $data = get_general_data($collId, $resId, $dataMode, $paramData);
        ?>
        <div align="center">
         <form method="post" name="index_doc" action="#" class="forms">
            <div class="block">
                <p align="left">
                    <h3 align="left" onclick="new Effect.toggle('desc3', 'blind');" onmouseover="document.body.style.cursor='pointer';" onmouseout="document.body.style.cursor='auto';" id="h23" class="categorie">
                        <a href="#"><?php echo _SHOW_DETAILS_DOC; ?></a>
                    </h3>
                </p>
            </div>
            <div class="desc block_light admin" id="desc3" style="display:none">
                <div class="ref-unit">
                    <?php echo _MENU." : "; ?>
                    <a href="<?php
         echo $_SESSION['config']['businessappurl'];
         ?>index.php?display=true&page=view_resource_controler&id=<?php
         echo $resId;
         ?>&dir=indexing_searching" target="_blank"><b><?php
         echo _VIEW_DOC_FULL; ?></b> </a>
                                        |
         <a href="<?php
         echo $_SESSION['config']['businessappurl'];
         ?>index.php?page=details&dir=indexing_searching&id=<?php
         echo $resId;
         ?>" target="_blank"><b><?php  echo _DETAILS_DOC_FULL; ?> </b></a>
         <hr/>

         <p>
            <label><?php echo _NUM_GED." : "; ?></label>
            <input type="text" name="resId" id="resId" value="<?php  echo $resId;?>" class="readonly" readonly="readonly" />
         </p>
         <?php
	    $i = 0;
        foreach (array_keys($data) as $key) {
            $folderId = "";
            if (($key == "market" || $key == "project")
                && $data[$key]['show_value'] <> ""
            ) {
                $folderTmp = $data[$key]['show_value'];
                $find1 = strpos($folderTmp, '(');
                $folderId = substr($folderTmp, $find1, strlen($folderTmp));
                $folderId = str_replace("(", "", $folderId);
                $folderId = str_replace(")", "", $folderId);
            }
                ?>
            <p>
                <label><?php
        if (isset($data[$key]['addon'])) {
                echo $data[$key]['addon'];
            } else if (isset($data[$key]['img'])) {
                 if ($folderId <> "") {
                    echo "<a href='" . $_SESSION['config']['businessappurl']
                        . "index.php?page=show_folder&module=folder&id="
                        . $folderId . "'>";
                    ?>
                     <img alt="<?php
                     echo $data[$key]['label'];
                     ?>" title="<?php
                     echo $data[$key]['label'];
                     ?>" src="<?php echo $data[$key]['img'];?>"  /></a>
                     <?php
                } else {
                    ?>
                    <img alt="<?php echo $data[$key]['label'];?>" title="<?php
                    echo $data[$key]['label'];
                    ?>" src="<?php echo $data[$key]['img'];?>" /></a>
                    <?php
                }
            }
                echo $data[$key]['label'];?> :</label><?php
            if (! isset($data[$key]['readonly'])
                || $data[$key]['readonly'] == true
            ) {
                if ($data[$key]['display'] == 'textinput') {
                    ?>
                    <input type="text" name="<?php echo $key;?>" id="<?php
                    echo $key;
                    ?>" value="<?php
                    echo $data[$key]['show_value'];
                    ?>" readonly="readonly" class="readonly" size="40" title="<?php
                    echo $data[$key]['show_value'];
                    ?>" alt="<?php  echo $data[$key]['show_value']; ?>" />
                    <?php
                } else {
                     ?>
                     <input type="text" name="<?php echo $key;?>" id="<?php
                     echo $key;
                     ?>" value="<?php
                     echo $data[$key]['show_value'];
                     ?>" readonly="readonly" class="readonly" size="40" title="<?php
                     echo $data[$key]['show_value'];
                     ?>" alt="<?php  echo $data[$key]['show_value']; ?>" />
                     <?php
                }
            } else {
                if ($data[$key]['field_type'] == 'textfield') {
                    ?>
                    <input type="text" name="<?php echo $key;?>" id="<?php
                    echo $key;
                    ?>" value="<?php
                    echo $data[$key]['show_value'];
                    ?>" size="40"  title="<?php
                    echo $data[$key]['show_value'];
                    ?>" alt="<?php  echo $data[$key]['show_value']; ?>" />
                    <?php
                } else if ($data[$key]['field_type'] == 'date') {
                    ?>
                    <input type="text" name="<?php echo $key;?>" id="<?php
                    echo $key;
                    ?>" value="<?php
                    echo $data[$key]['show_value'];
                    ?>" size="40"  title="<?php
                    echo $data[$key]['show_value'];
                    ?>" alt="<?php
                    echo $data[$key]['show_value'];
                    ?>" onclick="showCalender(this);" />
                    <?php
                } else if ($data[$key]['field_type'] == 'select') {
                    ?>
                    <select id="<?php echo $key;?>" name="<?php
                    echo $key;
                    ?>" <?php
                    if ($key == 'type_id') {
                        echo 'onchange="change_doctype_details'
                            . '(this.options[this.options.selectedIndex].value, \''
                            . $_SESSION['config']['businessappurl']
                            . 'index.php?display=true&dir='
                            . 'indexing_searching&page=change_doctype_details\' , \''
                            ._DOCTYPE.' '._MISSING.'\');"';
                    }
                    ?>>
                    <?php
                    if ($key == 'type_id') {
                        if ($_SESSION['features']['show_types_tree'] == 'true') {
                            for ($k = 0; $k < count($data[$key]['select']);
                                $k ++
                            ) {
                                ?><option value="" class="doctype_level1"><?php
                                echo $data[$key]['select'][$k]['label'];
                                ?></option><?php
                                for ($j = 0; $j < count(
                                    $data[$key]['select'][$k]['level2']
                                ); $j ++
                                ) {
                                    ?><option value="" class="doctype_level2">&nbsp;&nbsp;<?php
                                    echo $data[$key]['select'][$k]['level2'][$j]['label'];
                                    ?></option><?php
                                    for ($l = 0; $l < count(
                                        $data[$key]['select'][$k]['level2'][$j]['types']
                                    ); $l ++
                                    ) {
                                        ?><option <?php
                                        if ($data[$key]['value'] == $data[$key]['select'][$k]['level2'][$j]['types'][$l]['id']) {
                                            echo 'selected="selected"';
                                        }
                                        ?> value="<?php
                                        echo $data[$key]['select'][$k]['level2'][$j]['types'][$l]['id'];
                                        ?>" >&nbsp;&nbsp;&nbsp;&nbsp;<?php echo
                                        $data[$key]['select'][$k]['level2'][$j]['types'][$l]['label'];
                                        ?></option><?php
                                    }
                                }
                            }
                        } else {
                            for ($k = 0; $k < count($data[$key]['select']);
                                $k ++
                            ) {
                                ?><option <?php
                                if ($data[$key]['value'] == $data[$key]['select'][$k]['ID']) {
                                    echo 'selected="selected"';
                                }
                                ?> value="<?php
                                echo $data[$key]['select'][$k]['ID'];
                                ?>" ><?php
                                echo $data[$key]['select'][$k]['LABEL'];
                                ?></option><?php
                            }
                        }
                    } else {
                        for ($k = 0; $k < count($data[$key]['select']); $k ++) {
                            ?><option value="<?php
                            echo $data[$key]['select'][$k]['ID'];
                            ?>" <?php
                            if ($data[$key]['value'] == $data[$key]['select'][$k]['ID']) {
                                echo 'selected="selected"';
                            }
                            ?>><?php
                            echo $data[$key]['select'][$k]['LABEL'];
                            ?></option><?php
                        }
                    }
                    ?>
                    </select>
                    <?php
                } else if ($data[$key]['field_type'] == 'autocomplete') {
                    if ($key == 'project') {
                        //$('market').value='';return false;
                        ?><input type="text" name="project" id="project"  value="<?php
                        echo $data['project']['show_value'];
                        ?>" /><div id="show_project" class="autocomplete"></div><script type="text/javascript">launch_autocompleter_folders('<?php
                        echo $_SESSION['config']['businessappurl'];
                        ?>index.php?display=true&module=folder&page=autocomplete_folders&mode=project', 'project');</script>
                        <?php
                    } else if ($key == 'market') {
                        ?><input type="text" name="market" id="market" onblur="fill_project('<?php
                        echo $_SESSION['config']['businessappurl'];
                        ?>index.php?display=true&module=folder&page=ajax_get_project');return false;"  value="<?php echo $data['market']['show_value']; ?>"/><div id="show_market" class="autocomplete"></div>
                        <script type="text/javascript">launch_autocompleter_folders('<?php
                        echo $_SESSION['config']['businessappurl'];
                        ?>index.php?display=true&module=folder&page=autocomplete_folders&mode=market', 'market');</script>
                        <?php
                    }
                }
            }
            echo '</p>';
            $i ++;
        }
        foreach (array_keys($indexes) as $key) {
            echo '<p>';
            /* if (isset($indexes[$key]['img'])) {
                ?>
                <img alt="<?php echo $indexes[$key]['label'];?>" title="<?php echo $indexes[$key]['label'];?>" src="<?php echo $indexes[$key]['img'];?>"  /></a>
                <?php
            }*/
            ?><label><?php echo $indexes[$key]['label'];?> :</label>
            <?php
            if ($indexes[$key]['type_field'] == 'input') {
                ?>
                <input type="text" name="<?php echo $key;?>" id="<?php
                echo $key;
                ?>" value="<?php echo $indexes[$key]['show_value'];?>" <?php
                if (! isset($indexes[$key]['readonly'])
                    || $indexes[$key]['readonly'] == true
                ) {
                    echo 'readonly="readonly" class="readonly"';
                } else if ($indexes[$key]['type'] == 'date') {
                    echo 'onclick="showCalender(this);"';
                }
                ?> size="40"  title="<?php
                echo $indexes[$key]['show_value'];
                ?>" alt="<?php  echo $indexes[$key]['show_value']; ?>"   />
                <?php
            } else {
                ?>
                <select name="<?php echo $key;?>" id="<?php echo $key;?>" >
                    <option value=""><?php echo _CHOOSE;?>...</option>
                    <?php
                for ($i = 0; $i < count($indexes[$key]['values']); $i ++) {
                    ?>
                    <option value="<?php
                    echo $indexes[$key]['values'][$i]['id'];
                    ?>" <?php
                    if ($indexes[$key]['values'][$i]['id'] == $indexes[$key]['value']) {
                        echo 'selected="selected"';
                    }
                    ?>><?php
                    echo $indexes[$key]['values'][$i]['label'];
                    ?></option><?php
                }
                ?>
                </select><?php
            }
        }
        ?>
        </div>
        </div>
        </form>
        <iframe name="view" id="view" width="100%" height="700" frameborder="0" scrolling="auto" src="<?php
        echo $_SESSION['config']['businessappurl'] . "index.php?display=true"
            . "&dir=indexing_searching&page=view_resource_controler&id="
            . $resId;
        ?>"></iframe>
        <?php

        if (! empty($_SESSION['error_page'])) {
            ?>
            <script type="text/javascript">
                alert("<?php  echo $func->wash_html($_SESSION['error_page']);?>");
                <?php
            if (isset($_POST['delete_doc'])) {
                 ?>
                 window.location.href = 'index.php';
                 <?php
            }
            ?>
            </script>
            <?php
            $_SESSION['error'] = "";
            $_SESSION['error_page'] = "";
        }
        ?>
        </div>
        <?php
    }
}

$core->load_js();
?>
</div>
