<?php
/*
*
*   Copyright 2013 Maarch
*
*   This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief    List of positions for autocompletion
*
* @file     positions_checked_list_autocompletion.php
* @author   Yves Christian Kpakpo <dev@maarch.org>
* @date     $date$
* @version  $Revision$
* @ingroup  fileplan
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once "modules" . DIRECTORY_SEPARATOR . "fileplan" . DIRECTORY_SEPARATOR
    . "class" . DIRECTORY_SEPARATOR . "class_modules_tools.php";
    
$db     = new dbquery();
$fileplan = new fileplan();

$where = "";
$content = "";
$actual_position_id = "";
$positions_array = $path_array = array();

$path_to_script = $_SESSION['config']['businessappurl']
	."index.php?display=true&module=fileplan&page=fileplan_ajax_script";

if (isset($_REQUEST['res_id']) && !empty($_REQUEST['res_id'])) {
	
	//Build res_array
	$res_array = $fileplan->buildResArray($_REQUEST['res_id']);
	//
    $resIdArray = array();
    $resIdArray = explode (',', $_REQUEST['res_id']);
	//Get uuthorized fileplans
	$authorizedFileplans =  $fileplan->getAuthorizedFileplans();
	
	//For each ressource
	for($i = 0; $i < count($resIdArray); $i++) {
		$allIsChecked = false;
		//Separate coll_id from res_id
		$tmp = explode('@@', $resIdArray[$i]);

		//Search for the fileplans and positions
		$path_array = $fileplan->whereAmISetted($authorizedFileplans, $tmp[0], $tmp[1]);
		
		for($j = 0; $j < count($path_array); $j++) {
			//Get the state of checkbox
			$state = $fileplan->getPositionState($path_array[$j]['FILEPLAN_ID'], $path_array[$j]['POSITION_ID'], $res_array);
			//Set the tate
			$_SESSION['checked_positions'][$path_array[$j]['FILEPLAN_ID']][$path_array[$j]['POSITION_ID']] = $state;
		}
	}
}

if (!empty($_REQUEST['fileplan_id'])) {

	//Selected label (update mode)
	if (isset($_REQUEST['actual_position_id']) && !empty($_REQUEST['actual_position_id'])) {
		$actual_position_id = $_REQUEST['actual_position_id'];
	}
	
	$positions_array = array();
	$fileplan_id = $_REQUEST['fileplan_id'];
	
	if (strlen(trim($_REQUEST['param'])) > 0) {
		$where = "fileplan_id = " . $fileplan_id
			. " and position_enabled = 'Y'"
			. " and lower(position_label) like lower('%"
			. $_REQUEST['param']."%')";
	} else {
		$where = "fileplan_id = "
			. $fileplan_id
			." and position_enabled = 'Y'";
	}

	$db->connect();
	$db->query(
		"select  position_id, position_label, position_enabled from "
		. FILEPLAN_VIEW." where 
		".$where." order by position_label"
		);
		
	if($db->nb_result() > 0) {
		while($line = $db->fetch_object()) {
			array_push(
                $positions_array , 
                array(
                    'ID' => $line->position_id, 
                    'LABEL' => $db->show_string($line->position_label), 
                    'PARENT_ID' => $line->parent_id,
					'COUNT_DOCUMENT' => $line->count_document
                )
			);
		}
	}

	// $content .= 'CHECKED_POSITIONS:'.(print_r($_SESSION['checked_positions'], true)).'<br/>'; //DEBUG

	$content .= "<ul>\n";

	//Show selected positions first
	if (count($_SESSION['checked_positions'][$fileplan_id]) > 0) {
		$js .='<script type="text/javascript">';
		foreach (array_keys($_SESSION['checked_positions'][$fileplan_id]) as $position_id) 
		{	//Description
			$description = $fileplan->getPositionPath($fileplan_id, $position_id); 
			//Checked or partially checked?
			if(isset($_SESSION['checked_positions'][$fileplan_id][$position_id])) {
				$content .= "<li alt=\"".$description
					. "\" title=\"".$description
					. "\"><input type=\"checkbox\" id=\"position_".$position_id."\" name=\"position[]\""
					. " class=\"check\" onClick=\"saveCheckedState('".$path_to_script
					. "&fileplan_id=".$fileplan_id."&mode=checkPosition', this);\" value=\""
					. $position_id."\" checked=\"checked\">". $description."</li>\n";
				//extra javascript
				if ($_SESSION['checked_positions'][$fileplan_id][$position_id] == 'true') {

				} elseif ($_SESSION['checked_positions'][$fileplan_id][$position_id] == 'partial') {
					$js.= "document.getElementById('position_". $position_id."').indeterminate = true;";
				}
			}
		}
		$js .='</script>';
		$content .=  "<li class=\"separator\"></li>";
	}
	 
	 //Show postions
	 for($i=0; $i < count($positions_array); $i++) {
		
		if($i < 100) {
			$id = $positions_array[$i]['ID']; 
			$description = $fileplan->getPositionPath($fileplan_id, $positions_array[$i]['ID']); 
			// $description = $fileplan->truncate($description); 
			
			//Check if position is already selected
			// ($id == $actual_position_id || isset($_SESSION['checked_positions'][$fileplan_id][$id]))? 
				// $checked = ' checked="checked"': $checked =  '';
			
			//Content (only unselected)
			if (!isset($_SESSION['checked_positions'][$fileplan_id][$id])) {
				$content .= "<li alt=\"".$description
						. "\" title=\"".$description
						. "\"><input type=\"checkbox\" id=\"position_".$id."\" name=\"position[]\""
						. " class=\"check\" onClick=\"saveCheckedState('".$path_to_script
						. "&fileplan_id=".$fileplan_id."&mode=checkPosition', "
						. "this);\" value=\"". $id."\">". $description."</li>\n"; 
			}
		} else  {
			$content .= "<li>...</li>\n";
			break;
		}
	 }
	$content .=  "</ul>";
}

echo $js.$content;
