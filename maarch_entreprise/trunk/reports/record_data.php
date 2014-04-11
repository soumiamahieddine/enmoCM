<?php
	session_start();
	require_once("..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_functions.php");
	
	
	
	if(isset($_POST["data"])){
		try{
			$functions = new functions();
			$_POST['data']=urldecode($_POST['data']);
			$data=unserialize($_POST['data']);
			$contenu = '';
			$fp = fopen('../../../apps/maarch_entreprise/tmp/export_reports_maarch.csv', 'w');
			
			foreach($data as $key => $value){
				$value['LABEL'] = $functions->wash_html($value['LABEL'], "UTF-8");
				$value['VALUE'] = $functions->wash_html($value['VALUE'], "UTF-8");
				fputcsv($fp, $value);
			}
			
			fclose($fp);
			$return['status'] = 1;
		} catch(Exeption $e){
			$return['response'] = "ERROR : " . $e;
			$return['status'] = 0;
		}
		
	}
	echo json_encode($return);
?>