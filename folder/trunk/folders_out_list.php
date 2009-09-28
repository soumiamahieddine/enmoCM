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
session_name('PeopleBox'); 
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtomodules']."folder".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_modules_tools.php");
require_once($_SESSION['config']['businessapppath']."class".$_SESSION['slash_env']."class_list_show.php");
	
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
	$select[$_SESSION['tablename']['fold_folders']]= array();

	array_push($select[$_SESSION['current_basket']['table']],"folder_out_id","folder_system_id", "last_name", "last_name_folder_out", "put_out_date", "return_date");
	array_push($select[$_SESSION['tablename']['fold_folders']],"folders_system_id", "folder_id");	
	$where = $_SESSION['current_basket']['table'].".folder_system_id = ".$_SESSION['tablename']['fold_folders'].".folders_system_id and ".$_SESSION['current_basket']['clause'];
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
					$tab[$i][$j]["show"]=false;				
				}
				if($tab[$i][$j][$value]=="folder_system_id")
				{
					$tab[$i][$j]["folder_system_id"]=$tab[$i][$j]['value'];
					$tab[$i][$j]["show"]=false;	
				}
				if($tab[$i][$j][$value]=="folder_id")
				{
					$tab[$i][$j]['res_id']=$tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_FOLDER_NUM;
					$tab[$i][$j]["size"]="20";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="folder_out_id")
				{
					$tab[$i][$j]["label"]=_NUM_FILE;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="last_name")
				{
					$tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_LASTNAME;
					$tab[$i][$j]["size"]="20";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="last_name_folder_out")
				{
					$tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
					$tab[$i][$j]["label"]=_FILE_OUT_PERSON;
					$tab[$i][$j]["size"]="25";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="put_out_date")
				{
					$tab[$i][$j]["put_out_date"]=$request->format_date_db($tab[$i][$j]['value']);
					$tab[$i][$j]['value']=$request->format_date_db($tab[$i][$j]['value']);
					$tab[$i][$j]["label"]=_FILE_OUT_DATE;
					$tab[$i][$j]["size"]="20";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="return_date")
				{
					$tab[$i][$j]["return_date"]=$request->format_date_db($tab[$i][$j]['value']);
					$tab[$i][$j]['value']=$request->format_date_db($tab[$i][$j]['value']);
					$tab[$i][$j]["label"]=_RETURN_DATE;
					$tab[$i][$j]["size"]="20";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
			}
		}
	}
	$title = _FILE_OUT_LIST." : ".$i." "._FOUND_FOLDERS;
	
	$list=new list_show();
	$list->list_doc($tab,$i,$title,"folder_out_id","folders_out_list","folder_out_id","folder_detail",false,true,"get","res_folders_out_list.php",_CHOOSE, false, false, true);
	
?>
</body>
</html>