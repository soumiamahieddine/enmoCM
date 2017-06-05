<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   del_attachment
* @author  dev <dev@maarch.org>
* @ingroup attachments
*/

require_once "core/class/class_security.php";
require_once "core/class/class_request.php";
require_once "core/class/class_resource.php";
require_once "core/class/class_history.php";
require_once 'modules/attachments/attachments_tables.php';
require_once 'modules/attachments/class/attachments_controler.php';
$core = new core_tools();
$core->load_lang();

$func = new functions();
$ac = new attachments_controler();

$db = new Database();

$info_doc = $ac->getAttachmentInfos($_REQUEST['id']);

if ($_REQUEST['relation'] == 1) {
    $stmt = $db->query("UPDATE " . RES_ATTACHMENTS_TABLE . " SET status = 'DEL' WHERE res_id = ?", array($_REQUEST['id']));
    $pdf_id = $ac->getCorrespondingPdf($_REQUEST['id']);
    if (isset($pdf_id) && $pdf_id != 0) $stmt = $db->query("UPDATE " . RES_ATTACHMENTS_TABLE . " SET status = 'DEL' WHERE res_id = ?", array($pdf_id));
    $document = $ac->getCorrespondingDocument($_REQUEST['id']);
    $document_relation = $document->relation;
    $attach_type = $_SESSION['attachment_types'][$document->attachment_type];
    if ($document_relation == 1) {
        $target_table = "res_attachments";
        $document_id = $document->res_id;
        $is_version = 0;

    } else {
        $target_table = "res_version_attachments";
        $document_id = $document->res_id_version;
        $is_version = 1;
    }

    if (isset($document_id) && $document_id != 0) $stmt = $db->query("UPDATE " . $target_table . " SET status = 'A_TRA' WHERE res_id = ?", array($document_id));
    
} else {
    $stmt = $db->query("SELECT attachment_id_master FROM res_version_attachments WHERE res_id = ?", array($_REQUEST['id']));
    $res=$stmt->fetchObject();
    $stmt = $db->query("UPDATE res_version_attachments SET status = 'DEL' WHERE attachment_id_master = ?", array($res->attachment_id_master));
    $stmt = $db->query("UPDATE res_attachments SET status = 'DEL' WHERE res_id = ?", array($res->attachment_id_master));
    
    $pdf_id = $ac->getCorrespondingPdf($_REQUEST['id']);
    if (isset($pdf_id) && $pdf_id != 0) $stmt = $db->query("UPDATE " . RES_ATTACHMENTS_TABLE . " SET status = 'DEL' WHERE res_id = ?", array($pdf_id));
    $document = $ac->getCorrespondingDocument($_REQUEST['id']);
    $document_id = $document->res_id;
    $document_relation = $document->relation;
    $attach_type = $_SESSION['attachment_types'][$document->attachment_type];
    
    if ($document_relation == 1) {
        $target_table = "res_attachments";
        $document_id = $document->res_id;

    } else {
        $target_table = "res_version_attachments";
        $document_id = $document->res_id_version;
    }

    if (isset($document_id) && $document_id != 0) {
        $stmt = $db->query("UPDATE " . $target_table . " SET status = 'A_TRA' WHERE res_id = ?", array($document_id));
    } 
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
        $stmt = $db->query("SELECT res_id_master FROM " . RES_ATTACHMENTS_TABLE . " WHERE res_id = ?", array($_REQUEST['id']));
    } else {
        $stmt = $db->query("SELECT res_id_master FROM res_version_attachments WHERE res_id = ?", array($_REQUEST['id']));
    }

    $res = $stmt->fetchObject();
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

if (!empty($_REQUEST['rest'])) {
    echo '{"status" : "ok"}';
    exit;
}

if ($_REQUEST['relation'] == 1) {
    $stmt = $db->query("SELECT res_id_master FROM " . RES_ATTACHMENTS_TABLE . " WHERE res_id = ?", array($_REQUEST['id']));
} else {
    $stmt = $db->query("SELECT res_id_master FROM res_version_attachments WHERE res_id = ?", array($_REQUEST['id']));
}

$res = $stmt->fetchObject();
$resIdMaster = $res->res_id_master;
$query = "SELECT title FROM res_view_attachments WHERE status NOT IN ('DEL','OBS','TMP') and res_id_master = ?";
if (isset($_REQUEST['fromDetail']) && $_REQUEST['fromDetail'] == 'attachments') {
    $query .= " and (attachment_type <> 'response_project' and attachment_type <> 'outgoing_mail_signed' and attachment_type <> 'signed_response' and attachment_type <> 'converted_pdf' and attachment_type <> 'outgoing_mail' and attachment_type <> 'print_folder' and attachment_type <> 'aihp')";
} else if (isset($_REQUEST['fromDetail']) && $_REQUEST['fromDetail'] == 'response') {
    $query .= " and (attachment_type = 'response_project' or attachment_type = 'outgoing_mail_signed' or attachment_type = 'outgoing_mail' or attachment_type = 'signed_response' or attachment_type = 'aihp')";

} else {
    $query .= " and attachment_type NOT IN ('converted_pdf','print_folder')";
}
$stmt = $db->query($query, array($resIdMaster));
if ($stmt->rowCount() > 0) {
    $new_nb_attach = $stmt->rowCount();
} else {
    $new_nb_attach = 0;
}
?>
<script type="text/javascript">

    var eleframe1 =  parent.document.getElementsByName('list_attach');
    if(eleframe1[0] === undefined){
        eleframe1 =  parent.document.getElementsByName('responses_iframe');
    }
    var nb_attach = '<?php functions::xecho($new_nb_attach);?>';
    <?php if (isset($_REQUEST['fromDetail']) && $_REQUEST['fromDetail'] == 'attachments') { ?>
        eleframe1[0].src = "<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&page=frame_list_attachments&load&attach_type_exclude=response_project,signed_response,outgoing_mail_signed,converted_pdf,outgoing_mail,print_folder,aihp&fromDetail=attachments';?>";
    <?php } else if (isset($_REQUEST['fromDetail']) && $_REQUEST['fromDetail'] == 'response') { ?>
        eleframe1[0].src = "<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&page=frame_list_attachments&load&attach_type=response_project,outgoing_mail_signed,signed_response,outgoing_mail,aihp&fromDetail=response';?>";
    <?php } else { ?>
        parent.document.getElementById('list_attach').src = "<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&template_selected=documents_list_attachments_simple&page=frame_list_attachments&load&attach_type_exclude=converted_pdf,print_folder';?>";
    <?php } ?>

</script>
