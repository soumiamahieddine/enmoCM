<?php


require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_request.php');


$res = \Doctype\models\DoctypeModel::getById(['id' => $_POST['type_id']]);

$sve_type = $res['process_mode'];
$_SESSION['process_mode'] = $sve_type;

if (!empty($_SESSION['process_mode_priority'])) {
	foreach ($_SESSION['process_mode_priority'] as $key => $value){
	    if($sve_type == $key){
			echo "{status : 0, value : '".$value."'}";
			exit;
		}elseif($sve_type == $key){
			echo "{status : 0, value : '".$value."'}";
			exit;
		}elseif($sve_type == $key){
			echo "{status : 1, value : '".$value."'}";
			exit;
		}
	}
}


?>
