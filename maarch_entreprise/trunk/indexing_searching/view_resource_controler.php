<?php

/*
*    Copyright 2008,2009,2010 Maarch
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

/**
* @brief  controler of the view resource page
*
* @file view_resource_controler.php
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching
*/

try {
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_resource.php");
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."docservers_controler.php");
} catch (Exception $e) {
	echo $e->getMessage();
}
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$function = new functions();
$sec = new security();
$mode = "";
//1:test the request ID
if(isset($_REQUEST['id'])) {
	$s_id = $_REQUEST['id'];
} else {
	$s_id = "";
}
if($s_id == '') {
	$_SESSION['error'] = _THE_DOC.' '._IS_EMPTY;
	header("location: ".$_SESSION['config']['businessappurl']."index.php");
	exit();
} else {
	//2:retrieve the view
	$table = "";
	if(isset($_SESSION['collection_id_choice']) && !empty($_SESSION['collection_id_choice'])) {
		$table = $sec->retrieve_view_from_coll_id($_SESSION['collection_id_choice']);
		if(!$table) {
			$table = $sec->retrieve_table_from_coll($_SESSION['collection_id_choice']);
		}
	} else {
		if(isset($_SESSION['collections'][0]['view']) && !empty($_SESSION['collections'][0]['view'])) {
			$table = $_SESSION['collections'][0]['view'];
		} else {
			$table = $_SESSION['collections'][0]['table'];
		}
	}
	$docserverControler = new docservers_controler();
	$viewResourceArr = array();
	$docserverLocation = array();
	$docserverLocation = $docserverControler->retrieveDocserverNetLinkOfResource($s_id, $table);
	if($docserverLocation['value'] <> "" && $_SESSION['config']['coreurl'] <> $docserverLocation['value']) {
		?>
		<iframe frameborder="0" scrolling="no" width="100%" height="100%" src="<?php echo $docserverLocation['value']."apps/maarch_entreprise/index.php?display=true&dir=indexing_searching&page=view_resource_controler&id=".$s_id;?>"></iframe>
		<?php
	} else {
		$viewResourceArr = $docserverControler->viewResource($s_id, $table);
		if($viewResourceArr['error'] <> "") {
			//...
		} else {
			$fileContent = base64_decode($viewResourceArr['file_content']);
			$fileNameOnTmp = 'tmp_file_'.rand().'_'.md5($fileContent).'.'.strtolower($viewResourceArr['ext']);
			$filePathOnTmp = $_SESSION['config']['tmppath'].DIRECTORY_SEPARATOR.$fileNameOnTmp;
			$inF = fopen($filePathOnTmp,"w");
			fwrite($inF,$fileContent);
			fclose($inF);
			if(strtolower($viewResourceArr['mime_type']) == "application/maarch") {
				$myfile = fopen($filePathOnTmp, "r");
				$data = fread($myfile, filesize($filePathOnTmp));
				fclose($myfile);
				$content = stripslashes($data);
			}
		}
		include('view_resource.php');
	}
}
?>
