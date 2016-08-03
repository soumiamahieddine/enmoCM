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
// var_dump($sve_type);

// var_dump($_SESSION['processing_modes']);
// var_dump(count($_SESSION['process_mode_priority']));
// for($i=0;$i <count($_SESSION['process_mode_priority']);$i++){
// var_dump($_SESSION['process_mode_priority'][$i]);
// 	if($sve_type == $_SESSION['process_mode_priority'][$i]){
// 		echo "{status : 0, value : ".$_SESSION['default_sva_priority']."}";
// 		exit;
// 	}elseif($sve_type == $_SESSION['process_mode_priority'][$i]){
// 		echo "{status : 0, value : ".$_SESSION['default_svr_priority']."}";
// 		exit;
// 	}elseif($sve_type == $_SESSION['process_mode_priority'][$i]){
// 		echo "{status : 1, value : ".$_SESSION['default_mail_priority']."}";
// 		exit;
// 	}

// }

foreach ($_SESSION['process_mode_priority'] as $key => $value){
    if($sve_type == $key){
		echo "{status : 0, value : ".$value."}";
		exit;
	}elseif($sve_type == $_SESSION['process_mode_priority'][$i]){
		echo "{status : 0, value : ".$value."}";
		exit;
	}elseif($sve_type == $_SESSION['process_mode_priority'][$i]){
		echo "{status : 1, value : ".$value."}";
		exit;
	}
}



?>