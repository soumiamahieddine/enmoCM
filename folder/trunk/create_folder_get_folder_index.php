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

/**
* File : create_folder_get_folder_index.php
*
* Ajax script used to get folder index during folder creation
*
* @package  Folder
* @version 1.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
include('core/init.php');


require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require("core/class/class_core_tools.php");
require('modules/folder'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR."class_admin_foldertypes.php");

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

		if($indexes[$key]['type_field'] == 'input')
		{
			if($indexes[$key]['type'] == 'date')
			{
				$content .='<input name="'.$key.'" type="text" id="'.$key.'" value="';
				if(!empty($_SESSION['m_admin']['folder']['indexes'][$key]))
				{
					$content .= $_SESSION['m_admin']['folder']['indexes'][$key];
				}
				else if($indexes[$key]['default_value'] <> false)
				{
					$content .= $foldertype->format_date_db($indexes[$key]['default_value'], true);
				}
				$content .= '" onclick="showCalender(this);"/>';
			}
			else
			{
				$content .= '<input name="'.$key.'" type="text" id="'.$key.'" value="';
				if(!empty($_SESSION['m_admin']['folder']['indexes'][$key]))
				{
					$content .= $_SESSION['m_admin']['folder']['indexes'][$key];
				}
				else if($indexes[$key]['default_value'] <> false)
				{
					$content .= $foldertype->protect_string_db($indexes[$key]['default_value'], true);
				}
				$content .= '"  />';
			}
		}
		else
		{
			$content .= '<select name="'.$key.'" id="'.$key.'" >';
				$content .= '<option value="">'._CHOOSE.'...</option>';
				for($i=0; $i<count($indexes[$key]['values']);$i++)
				{
					$content .= '<option value="'.$indexes[$key]['values'][$i]['id'].'"';
					if($indexes[$key]['values'][$i]['id'] == $_SESSION['m_admin']['folder']['indexes'][$key])
					{
						$content .= 'selected="selected"';
					}
					else if($indexes[$key]['default_value'] <> false && $indexes[$key]['values'][$i]['id'] == $indexes[$key]['default_value'])
					{
						$content .= 'selected="selected"';
					}
					$content .= ' >'.$indexes[$key]['values'][$i]['label'].'</option>';
				}
			$content .= '</select>';
		}

		if(in_array($key, $mandatory))
		{
			$content .= ' <span class="red_asterisk">*</span>';
		}
		$content .= '</p>';
	}
}

$db = new dbquery();
$db->connect();
//$db->query("select folders_system_id, folder_id, folder_name from ".$_SESSION['tablename']['fold_folders']." where foldertype_id = ".$_REQUEST['foldertype_id']." and folder_level = 1");
$db->query("select folders_system_id, folder_id, folder_name from ".$_SESSION['tablename']['fold_folders']." where folder_level = 1");

$folders = array();
while($res = $db->fetch_object())
{
	array_push($folders, array('SYS_ID' => $res->folders_system_id, 'ID' => $res->folder_id, 'NAME' => $res->folder_name));
}

if(count($folders) > 0)
{
	$content .= '<p>';
		$content .= '<label for="folder_parent">'._CHOOSE_PARENT_FOLDER.' :</label>';
		$content .= '<select name="folder_parent" id="folder_parent">';
			$content .= '<option value=""></option>';
			for($i=0; $i< count($folders);$i++)
			{
				$content .= '<option value="'.$folders[$i]['SYS_ID'].'">'.$folders[$i]['ID'].' - '.$folders[$i]['NAME'].'</option>';
			}
		$content .= '</select>';
		$content .= ' <img src = "'.$_SESSION['config']['businessappurl'].$_SESSION['config']['img'].'/picto_menu_help.gif" alt="'._FOLDER_PARENT_DESC.'" title="'._FOLDER_PARENT_DESC.'"/>';
	$content .= '</p>';
}
echo $content;

