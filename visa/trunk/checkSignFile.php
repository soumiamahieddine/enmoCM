<?php
	require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR 
        . 'class_request.php');
	require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR 
        . 'class_db.php');
		
	require_once 'modules/attachments/attachments_tables.php';
	
	$db = new dbquery();
	$db->connect();
	$db->query("SELECT attachment_type from ".RES_ATTACHMENTS_TABLE." where res_id = ".$_REQUEST['res_id']);
	$type = $db->fetch_object()->attachment_type;
	if ($type == 'signed_response'){
		echo "{status:1}";		
	}
	else echo "{status:0}";		
?>