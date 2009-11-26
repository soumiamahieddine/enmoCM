<?php
/*
*    Copyright 2008,2009 Maarch
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

require('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR."class_types.php");

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$type = new types();
$content = '';

if(!isset($_REQUEST['coll_id']) || empty($_REQUEST['coll_id']))
{
	echo _COLLECTION.' '._IS_EMPTY;
	exit();
}

$indexes = $type->get_all_indexes($_REQUEST['coll_id']);

if(count($indexes) > 0)
{
	$content .= '<hr/>';
    $content .= '<table>';
        $content .= '<tr>';
            $content .= '<th width="500px">'._FIELD.'</th>';
            $content .= '<th align="center" width="100px">'._USED.'</th>';
            $content .= '<th align="center" width="100px">'._MANDATORY.'</th>';
        $content .= '</tr>';

	for($i=0;$i<count($indexes);$i++)
	{
		$content .= '<tr>';
			$content .= '<td width="150px">	'.$indexes[$i]['label'].'</td>';
			$content .= '<td align="center">';
				$content .= '<input name="fields[]" id="field_'.$indexes[$i]['column'].'" type="checkbox" class="check" value="'.$indexes[$i]['column'].'"';

				if (in_array($indexes[$i]['column'], $_SESSION['m_admin']['doctypes']['indexes']))
				{
					$content .= 'checked="checked"';
				}
				$content .= '/>';
			$content .= '</td>';
			$content.= '<td align="center" width="100px">';
				$content .= '<input name="mandatory_fields[]" id="mandatory_field_'.$indexes[$i]['column'].'" type="checkbox" class="check" value="'.$indexes[$i]['column'].'"';
				if (in_array($indexes[$i]['column'], $_SESSION['m_admin']['doctypes']['mandatory_indexes']) )
				{
					$content .= ' checked="checked"';
				}
				$content .= ' onclick="$(\'field_'.$indexes[$i]['column'].'\').checked=true;"/>';
			$content .= '</td>';
		$content .= '</tr>';
	}
    $content .= '</table>';
    $content .= '<hr/>';
}
echo $content;
