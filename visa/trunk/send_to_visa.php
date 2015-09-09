<?php
require_once "modules" . DIRECTORY_SEPARATOR . "visa" . DIRECTORY_SEPARATOR
			. "class" . DIRECTORY_SEPARATOR
			. "class_modules_tools.php";
/**
* $confirm  bool true
*/
 
 $error_visa = false;
 
 $visa = new visa();
$curr_visa_wf = $visa->getWorkflow($_SESSION['doc_id'], $_SESSION['current_basket']['coll_id'], 'VISA_CIRCUIT');
if (count($curr_visa_wf['visa']) == 0 && count($curr_visa_wf['sign']) == 0){
	$error_visa = true;
}
$confirm = true;
/**
* $etapes  array Contains only one etap, the status modification
*/
 $etapes = array('empty_error');
 
 function manage_empty_error($arr_id, $history, $id_action, $label_action, $status)
{
	$_SESSION['action_error'] = '';
	$result = '';
	for($i=0; $i<count($arr_id );$i++)
	{
		$result .= $arr_id[$i].'#';
	}
	return array('result' => $result, 'history_msg' => '');
}
