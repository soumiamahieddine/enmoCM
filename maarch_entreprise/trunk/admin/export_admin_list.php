<?php	

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
			array_push($list_row, utf8_decode($_SESSION['export_admin_list'][$i_export][$j_export]['value']));	    	
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