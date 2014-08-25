<?php	

require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_contacts_v2.php");
$contact = new contacts_v2();
$core_tools = new core_tools();
$core_tools->load_lang();

	$fp = fopen($_SESSION['config']['tmppath'].'admin_list_'.$_SESSION['user']['UserId'].'.csv', 'w');

	$list_row = array();
	$list_header = array();

	for ($i_export=0;$i_export<count($_SESSION['export_admin_list']);$i_export++)
	{
	    for ($j_export=0;$j_export<count($_SESSION['export_admin_list'][$i_export]);$j_export++)
	    {
	    	if ($i_export==0) {
	    		array_push($list_header, utf8_decode($_SESSION['export_admin_list'][$i_export][$j_export]['column']));
	    	}
			if($_SESSION['export_admin_list'][$i_export][$j_export]['column'] == _CONTACT_TYPE){
				array_push($list_row, $contact->get_label_contact(($_SESSION['export_admin_list'][$i_export][$j_export]['value']),$_SESSION['tablename']['contact_types']));
			} else if($_SESSION['export_admin_list'][$i_export][$j_export]['column'] == _IS_CORPORATE_PERSON){
				if($_SESSION['export_admin_list'][$i_export][$j_export]['value'] == 'Y'){
					array_push($list_row, utf8_decode(_YES));
				} else {
					array_push($list_row, utf8_decode(_NO));
				}
			} else {
				array_push($list_row, utf8_decode($_SESSION['export_admin_list'][$i_export][$j_export]['value']));
			}  	
	    }

    	if ($i_export==0) {
    		fputcsv($fp, $list_header, ';', '"');
    	}
	    fputcsv($fp, $list_row, ';', '"');
	    $list_row = array();
	}

	fclose($fp);
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: inline; filename=admin_list_'.$_SESSION['user']['UserId'].'.csv;');
	readfile($_SESSION['config']['tmppath'].'admin_list_'.$_SESSION['user']['UserId'].'.csv');
	exit;