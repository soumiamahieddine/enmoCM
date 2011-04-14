<?php
if (count($folders) > 0) {
	$content .= '<p>';
		$content .= '<label for="folder_parent">'._CHOOSE_PARENT_FOLDER.' :</label>';
		$content .= '<select name="folder_parent" id="folder_parent">';
			$content .= '<option value=""></option>';
			for($i=0; $i< count($folders);$i++)
			{
				$content .= '<option value="'.$folders[$i]['SYS_ID'].'">'.$folders[$i]['ID'].' - '.$folders[$i]['NAME'].'</option>';
			}
		$content .= '</select>';
		$content .= ' <img src = "'.$_SESSION['config']['businessappurl'].'static.php?filename=picto_menu_help.gif" alt="'._FOLDER_PARENT_DESC.'" title="'._FOLDER_PARENT_DESC.'"/>';
	$content .= '</p>';
}