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


session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require($_SESSION['pathtocoreclass']."class_core_tools.php");
require($_SESSION['pathtomodules'].'folder'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR."class_admin_foldertypes.php");

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$foldertype = new foldertype();
$content = '';

if(!isset($_REQUEST['foldertype_id']) || empty($_REQUEST['foldertype_id']))
{
	echo _FOLDERTYPE.' '._IS_EMPTY;
	exit();
}

$indexes = $foldertype->get_indexes($_REQUEST['foldertype_id']);
$mandatory = $foldertype->get_mandatory_indexes($_REQUEST['foldertype_id']);
if(count($indexes) > 0)
{
	$content .= '<table width="91%" border="0">';
	$i=0;
	foreach(array_keys($indexes) as $key)
	{
		if($indexes[$key]['type'] == 'string')
		{
			if($i%2 != 1 || $i==0) // pair
			{
				$content .= '<tr >';
			}

			$content .= '<td width="25%" align="right"><label for="'.$key.'">'.$indexes[$key]['label'].' :</label></td>';
			$content .= '<td  width="24%">';
			if($indexes[$key]['type'] == 'date')
			{
				$content .= '<input type="text" name="'.$key.'" id="'.$key.'" value="" onclick="showCalender(this);" />';
			}
			else
			{
				$content .= '<input type="text" name="'.$key.'" id="'.$key.'" value="" />';
			}
			$content .= '</td>';
			$content .= '<td width="2%">&nbsp;</td>';
			if($i%2 == 1 && $i!=0) // impair
			{
				$content .=  '</tr>';
			}
			else
			{
				if($i+1 == count($indexes))
				{
					$content .= '<td  colspan="2">&nbsp;</td></tr>';
				}
			}
			$i++;
		}
		else
		{
			if($i%2 != 1 || $i==0) // pair
			{
				$content .= '<tr >';
			}
			else
			{
					$content .= '<td  colspan="2">&nbsp;</td>';
				$content .= '</tr>';
				$content .= '<tr>';
				$i++;
			}
			if($indexes[$key]['type'] == 'date')
			{
					$content .= '<td width="25%" align="right"><label for="'.$key.'_start">'.$indexes[$key]['label'].' '._SINCE.':</label></td>';
					$content .= '<td  width="24%">';
						$content .= '<input type="text" name="'.$key.'_start" id="'.$key.'_start" value="" onclick="showCalender(this);" />';
					$content .= '</td>';
					$content .= '<td width="2%">&nbsp;</td>';
					$content .= '<td width="25%" align="right"><label for="'.$key.'_end">'.$indexes[$key]['label'].' '._FOR.' :</label></td>';
					$content .= '<td  width="24%">';
						$content .= '<input type="text" name="'.$key.'_end" id="'.$key.'_end" value="" onclick="showCalender(this);" />';
					$content .= '</td>';
				$content .= '</tr>';
			}
			else
			{
				$content .= '<td width="25%" align="right"><label for="'.$key.'_min">'.$indexes[$key]['label'].' '._MIN.':</label></td>';
					$content .= '<td  width="24%">';
						$content .= '<input type="text" name="'.$key.'_min" id="'.$key.'_min" value=""  />';
					$content .= '</td>';
					$content .= '<td width="2%">&nbsp;</td>';
					$content .= '<td width="25%" align="right"><label for="'.$key.'_max">'.$indexes[$key]['label'].' '._MAX.' :</label></td>';
					$content .= '<td  width="24%">';
						$content .= '<input type="text" name="'.$key.'_max" id="'.$key.'_max" value=""  />';
					$content .= '</td>';
				$content .= '</tr>';
			}


		}
	}

}
echo $content;


