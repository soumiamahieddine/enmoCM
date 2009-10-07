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
	foreach(array_keys($indexes) as $key)
	{
		$content .= '<p>';
		$content .= '<label for="'.$key.'">	'.$indexes[$key]['label'].' :</label>';

		if($indexes[$key]['type'] == 'string' || $indexes[$key]['type'] == 'integer' || $indexes[$key]['type'] == 'float')
		{
			$content .= '<input type="text" name="'.$key.'" id="'.$key.'" value=""  />';
		}
		else if($indexes[$key]['type'] == 'date')
		{

			$content .= '<input type="text" name="'.$key.'" id="'.$key.'" value=""  onclick="showCalender(this);" />';
		}
		if(in_array($key, $mandatory))
		{
			$content .= ' <span class="red_asterisk">*</span>';
		}
		$content .= '</p>';
	}
}
echo $content;

