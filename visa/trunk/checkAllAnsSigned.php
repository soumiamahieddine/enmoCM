<?php
	require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR 
        . 'class_request.php');
	require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR 
        . 'class_db.php');
		
	require_once 'modules/attachments/attachments_tables.php';
	
	$db = new Database();
	$stmt = $db->query("SELECT status from res_view_attachments where attachment_type= ? and res_id_master = ? ", array('response_project', $_REQUEST['res_id']));
	while($line = $stmt->fetchObject()){
		if ($line->status == 'TRA' || $line->status == 'A_TRA' ){
			echo "{status:0}";	
			exit();			
		}
	}
	
	echo "{status:1}";	
	exit();	
	
?>