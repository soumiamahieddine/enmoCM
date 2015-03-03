<?php

require_once "core/class/class_security.php";
require_once "core/class/class_request.php";
require_once "core/class/class_resource.php";
require_once "core/class/class_history.php";
require_once 'modules/attachments/attachments_tables.php';
$core = new core_tools();
$core->load_lang();

$func = new functions();

$db = new dbquery();
$db->connect();

if ($_REQUEST['relation'] == 1) {
    $db->query("UPDATE " . RES_ATTACHMENTS_TABLE . " SET status = 'DEL' WHERE res_id = " . $_REQUEST['id'] );
} else {
    $db->query("SELECT attachment_id_master FROM res_version_attachments WHERE res_id = " . $_REQUEST['id']);
    $res=$db->fetch_object();
    $db->query("UPDATE res_version_attachments SET status = 'DEL' WHERE attachment_id_master = " . $res->attachment_id_master);
    $db->query("UPDATE res_attachments SET status = 'DEL' WHERE res_id = " . $res->attachment_id_master);
}

if ($_SESSION['history']['attachdel'] == "true") {
    $hist = new history();
    if (! isset($_SESSION['collection_id_choice'])
        || empty($_SESSION['collection_id_choice'])
    ) {
        $_SESSION['collection_id_choice'] = $_SESSION['user']['collections'][0];
    }
    $sec = new security();
    $view = $sec->retrieve_view_from_coll_id($_SESSION['collection_id_choice']);
    if ($_REQUEST['relation'] == 1) {
        $db->query("SELECT res_id_master FROM " . RES_ATTACHMENTS_TABLE . " WHERE res_id = " . $_REQUEST['id']);
    } else {
        $db->query("SELECT res_id_master FROM res_version_attachments WHERE res_id = " . $_REQUEST['id']);
    }

    $res = $db->fetch_object();
    $resIdMaster = $res->res_id_master;
    $hist->add(
        $view, $resIdMaster, "DEL", 'attachdel', _ATTACH_DELETED . ' ' . _ON_DOC_NUM
        . $resIdMaster . "  (" . $_REQUEST['id'] . ')',
        $_SESSION['config']['databasetype'], "attachments"
    );
    $hist->add(
        RES_ATTACHMENTS_TABLE, $_REQUEST['id'], "DEL", 'attachdel', _ATTACH_DELETED . " : "
        . $_REQUEST['id'], $_SESSION['config']['databasetype'], "attachments"
    );
}


if ($_REQUEST['relation'] == 1) {
    $db->query("SELECT res_id_master FROM " . RES_ATTACHMENTS_TABLE . " WHERE res_id = " . $_REQUEST['id']);
} else {
    $db->query("SELECT res_id_master FROM res_version_attachments WHERE res_id = " . $_REQUEST['id']);
}

$res = $db->fetch_object();
$resIdMaster = $res->res_id_master;
$query = "SELECT title FROM res_view_attachments WHERE status <> 'DEL' and status <> 'OBS' and res_id_master = " . $resIdMaster;
    if (isset($_REQUEST['fromDetail']) && $_REQUEST['fromDetail'] == 'attachments') {
        $query .= " and (attachment_type <> 'response_project' and attachment_type <> 'outgoing_mail_signed')";
    } else if (isset($_REQUEST['fromDetail']) && $_REQUEST['fromDetail'] == 'response'){
        $query .= " and (attachment_type = 'response_project' or attachment_type = 'outgoing_mail_signed')";
    }
$db->query($query);
if ($db->nb_result() > 0) {
    $new_nb_attach = $db->nb_result();
} else {
    $new_nb_attach = 0;
}
?>
<script type="text/javascript">
    var eleframe1 =  window.top.document.getElementsByName('list_attach');
    var nb_attach = '<?php echo $new_nb_attach;?>';
    <?php if (isset($_REQUEST['fromDetail']) && $_REQUEST['fromDetail'] == 'attachments') { ?>
        eleframe1[0].src = "<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&page=frame_list_attachments&load&attach_type_exclude=response_project,outgoing_mail_signed&fromDetail=attachments';?>";
        window.parent.top.document.getElementById('nb_attach').innerHTML = " ("+nb_attach+")";
    <?php } else if (isset($_REQUEST['fromDetail']) && $_REQUEST['fromDetail'] == 'response'){ ?>
        eleframe1[1].src = "<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&page=frame_list_attachments&load&attach_type=response_project,outgoing_mail_signed&fromDetail=response';?>";
        window.parent.top.document.getElementById('answer_number').innerHTML = " ("+nb_attach+")";
    <?php } else { ?>
        eleframe1[0].src = "<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&page=frame_list_attachments&load';?>";
        window.parent.top.document.getElementById('nb_attach').innerHTML = nb_attach;
    <?php } ?>

    // window.parent.top.document.getElementById('nb_attach').innerHTML = nb_attach;

</script>
