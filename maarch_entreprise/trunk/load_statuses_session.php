<?php
require_once 'core' 
	. DIRECTORY_SEPARATOR . 'class'
	. DIRECTORY_SEPARATOR . 'statusControler.php';
	
$sts = new Maarch_Core_Class_StatusControler();
$_SESSION['m_admin']['statuses'] = array();
$_SESSION['m_admin']['statuses'] = $sts->getAllInfos(); 

?>
