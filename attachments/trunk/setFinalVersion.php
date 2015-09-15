<?php

require_once 'core/class/class_request.php';

$db = new Database();
$js = "";

if ((int)$_REQUEST['relation'] > 1) {
    $column_res = 'res_id_version';
} else {
    $column_res = 'res_id';
}

$stmt = $db->query("SELECT relation, status 
                FROM res_view_attachments 
                WHERE ".$column_res." = ? and res_id_master = ?
                ORDER BY relation desc",array($_REQUEST['id'],$_SESSION['doc_id']));

$res = $stmt->fetchObject();

if($res->status == 'A_TRA' || $res->status == 'TRA'){
	if ($res->status == 'A_TRA') {
		$status = 'TRA';
	} else {
		$status = 'A_TRA';		
	}

	if ($_REQUEST['relation'] == 1) {
		$table = 'res_attachments';
	} else {
		$table = 'res_version_attachments';
	}

	$db->query("UPDATE ".$table." set status = '".$status."' WHERE res_id = ?", array($_REQUEST['id']));
	$status_ajax = 0;

} else {
	$js .= "alert('Ce courrier a déjà été traité');";
	$status_ajax = 1;
}

$js .= 'var eleframe1 =  window.top.document.getElementsByName(\'list_attach\');';
if (isset($_REQUEST['fromDetail']) && $_REQUEST['fromDetail'] == 'attachments') {
	$js .= 'eleframe1[0].src = \''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&page=frame_list_attachments&load';
	$js .= '&attach_type_exclude=response_project,outgoing_mail_signed,converted_pdf,print_folder&fromDetail=attachments\';';
} else if (isset($_REQUEST['fromDetail']) && $_REQUEST['fromDetail'] == 'response'){
	$js .= 'eleframe1[1].src = \''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&page=frame_list_attachments&load';
	$js .= '&attach_type=response_project,outgoing_mail_signed&fromDetail=response\';';
} else {
	$js .= 'eleframe1[0].src = \''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=attachments&page=frame_list_attachments&load&attach_type_exclude=converted_pdf,print_folder\';';
}

echo "{status: ".$status_ajax.", exec_js : '".addslashes($js)."'}";
exit;