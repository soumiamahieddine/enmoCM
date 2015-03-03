<?php

require 'modules/templates/class/templates_controler.php';
$templatesControler = new templates_controler();
$templates = array();
$templates = $templatesControler->getAllTemplatesForProcess($_SESSION['destination_entity']);

$frmStr ="";
$frmStr .= '<option value="">S&eacute;lectionnez le mod&egrave;le</option>';  
for ($i=0;$i<count($templates);$i++) {
    if ($templates[$i]['TYPE'] == 'OFFICE' 
    	&& ($templates[$i]['TARGET'] == 'attachments' || $templates[$i]['TARGET'] == '') 
    	&& ($templates[$i]['ATTACHMENT_TYPE'] == $_REQUEST['attachment_type'] || $templates[$i]['ATTACHMENT_TYPE'] == 'all')) {
	       	$frmStr .= '<option value="'. $templates[$i]['ID'].'">';
	        $frmStr .= $templates[$i]['LABEL'];
	        	$frmStr .= '</option>';
    }
}

echo $frmStr;
