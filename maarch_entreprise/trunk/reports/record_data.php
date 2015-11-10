<?php

/*
*    Copyright 2008-2015 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_functions.php");
	
	$core_tools = new core_tools();
	$core_tools->test_user();
	$core_tools->test_service('reports', 'reports');

	if(isset($_POST["data"])){
		try{
			$functions = new functions();
			$_POST['data'] = urldecode($_POST['data']);
			$data = json_decode($_POST['data']);
			$contenu = '';
			$fp = fopen('apps/maarch_entreprise/tmp/export_reports_maarch.csv', 'w');
			
			foreach($data as $key => $value){
				//conversion en html
				$value['LABEL'] = $functions->wash_html($value['LABEL'], "UTF-16LE");
				//conversion en UTF-8
				$value['LABEL'] = mb_convert_encoding($value['LABEL'], 'UTF-16LE', 'UTF-8');
				$value['VALUE'] = $functions->wash_html($value['VALUE'], "UTF-8");
				$value['VALUE'] = mb_convert_encoding($value['VALUE'], 'UTF-16LE', 'UTF-8');
				fputcsv($fp, $value, ';');
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