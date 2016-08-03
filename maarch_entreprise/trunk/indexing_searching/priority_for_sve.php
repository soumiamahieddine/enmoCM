<?php
// var_dump('ok');

// var_dump($_POST['type_id']);

require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_request.php');
// if($_POST['status'] != '_NOSTATUS_'){
// $db = new Database();

// $db->query("UPDATE res_letterbox SET status = ? WHERE res_id = ?", array($_POST['status'],$_REQUEST['identifier']));

// }

$db = new Database();

$stmt = $db->query("SELECT sve_type from mlb_doctype_ext WHERE type_id = ?", array($_POST['type_id']));
$res = $stmt->fetchObject();
$sve_type = $res->sve_type;
//var_dump($sve_type);


if($sve_type == 'SVA' or $sve_type == 'SVR'){
	// var_dump($_SESSION['default_sve_priority']);
	// var_dump($_SESSION['mail_priorities'][4]);
	echo "{status : 0, value : ".$_SESSION['default_sve_priority']."}";
}elseif($sve_type == 'NORMAL'){
	echo "{status : 1, value : ".$_SESSION['default_mail_priority']."}";
}


?>