<?php
/*
*
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

/*
* Deprecated file : to adapt to the new basket modifications
*/
include('core/init.php');



require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require_once("core/class/class_request.php");
require_once("core/class/class_core_tools.php");
require_once("modules/folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_list_show.php");

$core_tools = new core_tools();

if(!$core_tools->is_module_loaded("folder"))
{
	echo "Folder module missing !<br/>Please install this module.";
	exit();
}

$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
 ?>
<body>
<br/><br/>
<?php

	$select[$_SESSION['current_basket']['table']]= array();
	$where = " status <> 'DEL' and status <> 'IMP' and ".$_SESSION['current_basket']['clause'];

	array_push($select[$_SESSION['current_basket']['table']],"folders_system_id","folder_id","fold_custom_t1","fold_custom_t2");
	$request= new request;
	$tab=$request->select($select,$where,"",$_SESSION['config']['databasetype']);

	for ($i=0;$i<count($tab);$i++)
	{
		for ($j=0;$j<count($tab[$i]);$j++)
		{
			foreach(array_keys($tab[$i][$j]) as $value)
			{
				if($tab[$i][$j][$value]=="folders_system_id")
				{
					$tab[$i][$j]["folders_system_id"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_GED_NUM;
					$tab[$i][$j]["size"]="4";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=false;

				}
				if($tab[$i][$j][$value]=="folder_id")
				{
					$tab[$i][$j]['res_id']=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_FOLDER_NUM;
					$tab[$i][$j]["size"]="25";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="fold_custom_t1")
				{
					$tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_LASTNAME;
					$tab[$i][$j]["size"]="25";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="fold_custom_t2")
				{
					$tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_FIRSTNAME;
					$tab[$i][$j]["size"]="25";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}

			}
		}
	}
	$title = "";

	if($_SESSION['current_basket']['id'] == "CompleteFolders")
	{
		$title = _PROCESS_FOLDER_LIST." : ".$i." "._FOUND_FOLDERS;
	}
	else if($_SESSION['current_basket']['id'] == "IncompleteFolders")
	{
		$title = _INCOMPLETE_FOLDERS_LIST." : ".$i." "._FOUND_FOLDERS;
	}
	else
	{
		$title = _RESULTS." : ".$i." "._FOUND_FOLDERS;
	}
	$list=new list_show();
	$list->list_doc($tab,$i,$title,"folder_system_id","folders_list","folder_system_id","folder_detail",false,true,"get","res_folders_list.php",_CHOOSE, false, false, true);
?>
</body>
</html>
