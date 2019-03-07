<?php
if (file_exists('../../../core/init.php')) {
    include_once '../../../core/init.php';
}
if (!isset($_SESSION['config']['corepath'])) {
    header('location: ../../../');
}
require_once 'core/class/class_functions.php';
require_once 'core/class/class_core_tools.php';
require_once 'core/class/class_db_pdo.php';
require_once 'core/core_tables.php';
require_once 'apps/maarch_entreprise/apps_tables.php';
require_once 'core/class/class_security.php';
require_once 'core/class/class_history.php';
require_once 'apps/'.$_SESSION['config']['app_id'].'/class/class_types.php';
if ($_SESSION['collection_id_choice'] == 'res_coll') {
    $catPhp = 'definition_mail_categories_invoices.php';
} else {
    $catPhp = 'definition_mail_categories.php';
}
if (file_exists(
    $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR
    .$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'
    .DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR
    .$catPhp
)
) {
    $path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR
          .$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'
          .DIRECTORY_SEPARATOR.$_SESSION['config']['app_id']
          .DIRECTORY_SEPARATOR.$catPhp;
} else {
    $path = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id']
          .DIRECTORY_SEPARATOR.$catPhp;
}
include_once $path;
$core->load_lang();
$users = new history();
$sec = new security();
$type = new types();
$coll_id = $_SESSION['collection_id_choice'];
$view = $sec->retrieve_view_from_coll_id($_SESSION['collection_id_choice']);
if (isset($_REQUEST['res_id_master'])) {
    $s_id = $_REQUEST['res_id_master'];
    $att_id = $_REQUEST['id'];
} else {
    $s_id = $_REQUEST['id'];
}
$_SESSION['doc_id'] = $s_id;
//to change
$right = true;
if (isset($_SESSION['origin']) && $_SESSION['origin'] != 'basket') {
    $right = $sec->test_right_doc($coll_id, $s_id);
} else {
    $right = true;
}
if (!$right) {
    ?>
    <script type="text/javascript">
        window.top.location.href = "<?php  echo $_SESSION['config']['businessappurl']; ?>index.php?page=no_right";
    </script>
    <?php
    exit();
}
/*
if (isset($s_id) && !empty($s_id) && $_SESSION['history']['resview'] == "true") {
    $users->add($table, $s_id ,"VIEW", _VIEW_DETAILS_NUM.$s_id, $_SESSION['config']['databasetype'],'apps');
}
*/
$db = new Database();
$comp_fields = '';
$stmt = $db->query('SELECT type_id FROM '.$view.' WHERE res_id = ? ', array($s_id));

if ($stmt->rowCount() > 0) {
    $res = $stmt->fetchObject();
    $type_id = $res->type_id;
    $indexes = $type->get_indexes($type_id, $coll_id, 'minimal');
    for ($i = 0; $i < count($indexes); ++$i) {
        if (preg_match('/^custom_/', $indexes[$i])) {
            $comp_fields .= ', doc_'.$indexes[$i];
        } else {
            $comp_fields .= ', '.$indexes[$i];
        }
    }
}
$case_sql_complementary = '';
if ($core->is_module_loaded('cases') == true) {
    $case_sql_complementary = ' , case_id';
}

$res_db = $db->query('SELECT * FROM '.$view.' WHERE res_id = ? ', array($s_id));
//$db->show();
if ($_SESSION['collection_id_choice'] == 'res_coll') {
}

$res = $res_db->fetchObject();
$typist = $res->typist;
$format = $res->format;
$filesize = $res->filesize;
$creation_date = $db->format_date_db($res->creation_date, false);
$chrono_number = $res->alt_identifier;
$fingerprint = $res->fingerprint;
$closing_date = $db->format_date_db($res->closing_date, false);
$indexes = $type->get_indexes($type_id, $coll_id);
$indexesData = '';
foreach (array_keys($indexes) as $key) {
    if (preg_match('/^custom/', $key)) {
        $tmp = 'doc_'.$key;
    } else {
        $tmp = $key;
    }
    if ($indexes[$key]['type'] == 'date') {
        $res->$tmp = functions::format_date_db($res->$tmp, false);
    }
    $indexes[$key]['value'] = $res->$tmp;
    $indexes[$key]['show_value'] = $res->$tmp;
    if ($indexes[$key]['type'] == 'string') {
        $indexes[$key]['show_value'] = functions::show_string($res->$tmp);
    } elseif ($indexes[$key]['type'] == 'date') {
        $indexes[$key]['show_value'] = functions::format_date_db($res->$tmp, true);
    }
    $indexesData .=
        '<div class="row">
            <label>'.functions::xssafe($indexes[$key]['label']).'</label><br><br>
            <input name="Department" readonly="readonly" value="'
            .functions::xssafe($indexes[$key]['show_value']).'"/>
        </div>';
}
$data = get_general_data($coll_id, $s_id, 'full', $param_data);
$generalData = '';
foreach (array_keys($data) as $key) {
    $view = true;
    if (
        ($key == 'category_id' || $key == 'priority')
        && $_SESSION['collection_id_choice'] == 'res_coll'
    ) {
        $view = false;
    }
    if ($view) {
        $generalData .=
            '<div class="row">
                <label>'.functions::xssafe($data[$key]['label']).'</label><br><br>
                <input name="Department" readonly="readonly" value="'
                .functions::xssafe($data[$key]['show_value']).'"/>
            </div>';
    }
}

$getAttach = '';

$db = new Database();
$tnlAdr = \Convert\models\AdrModel::getTypedDocumentAdrByResId([
    'select'    => [1],
    'resId'     => $s_id,
    'type'      => 'TNL'
]);
?>
<div id="details" title="Détails" class="panel">
    <?php
        if (empty($tnlAdr)) {
            echo 'pdf : '.$pdf_id; ?>
    <div align="center">
        <input type="button" class="whiteButton" value="<?php 
        echo _VIEW_DOC; ?>" onclick="window.open('../index.php?display=true&editingMode=true&dir=indexing_searching&page=view_resource_controler&id=<?php echo $s_id; ?>', '_blank');">
    </div>
    <?php

        } else {
            ?>
    <div id="frameThumb">
        <iframe id="ifrm" frameborder="0" scrolling="no" width="0" height="0" src="<?php echo '../../../rest/res/'.$s_id.'/thumbnail' ?>"></iframe>
    </div>

    <?php

        }
        ?>

    <hr/>
    <?php
    if ($core->is_module_loaded('notes')) {
        require_once 'modules/notes/notes_tables.php';
        $selectNotes = 'SELECT * FROM '.NOTES_TABLE.' WHERE identifier = ? ORDER BY creation_date DESC';
        $dbNotes = new Database();
        $stmtNote = $dbNotes->query($selectNotes, array($s_id));
        $nbNotes = $stmtNote->rowCount(); ?>
    <!--<ul>
            <li>-->
    <a href="view_notes.php?id=<?php functions::xecho($s_id); ?>&collId=<?php
                    functions::xecho($_SESSION['collection_id_choice']); ?>&tableName=<?php
                    functions::xecho($_SESSION['res_table']); ?>">
        <!--<span class="fa fa-edit"></span>
                    &nbsp;<?php echo _NOTES; ?>-->
        <span class="bubble">
            <i class="fa fa-pencil-alt fa-2x mCdarkGrey"></i>&nbsp;
            <?php echo $nbNotes; ?>
        </span>
    </a>
    <!--</li>
        </ul>-->
    <?php

    }
    if(!empty($_SESSION['current_basket']['default_action'])){
        $action = \Action\models\ActionModel::getById(['id' => $_SESSION['current_basket']['default_action'], 'select' => ['action_page']]);
        if (!empty($action) && $action['action_page'] == 'visa_mail' && $infos_attach['attachment_type'] != 'signed_response') {
            ?>
            <a href="signature_main_panel.php?id=<?php functions::xecho($s_id); ?>&collId=<?php
                            functions::xecho($_SESSION['collection_id_choice']); ?>&tableName=<?php
                    functions::xecho($_SESSION['res_table']); ?>&res_id_attach=<?php
                    functions::xecho($att_id); ?>">
                <span class="bubble" style="cursor: pointer;margin-right: 5px;">
                    <i class="fa fa-hand-point-up fa-2x mCdarkGrey" aria-hidden="true"></i>
                </span>
            </a>
            <input type="hidden" id="type_doc_show" value="attach" />
            <?php

        }        
    }
    
    ?>
    <br/>
    <br/>
    <hr/>
    <h2>
        <span class="fa fa-exclamation-circle" style="margin-right:10px;"></span>
        <?php echo _GENERAL_INFO; ?>
    </h2>
    <fieldset>
        <?php
        echo $generalData;
        ?>
    </fieldset>
    <?php
    if (count($indexes) > 0) {
        ?>
    <h2>
        <span class="fa fa-exclamation-circle" style="margin-right:10px;"></span>
        <?php echo _OPT_INDEXES; ?>
    </h2>
    <fieldset>
        <?php
            echo $indexesData; ?>
    </fieldset>
    <?php

    }
    ?>
</div>